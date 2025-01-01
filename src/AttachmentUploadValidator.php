<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\Settings\AllowedFileTypesSetting;
use DCO_CA\Settings\EnableMultipleUploadSetting;
use DCO_CA\Enums\MaxUploadSizeFormat;
use DCO_CA\Settings\MaxUploadSizeSetting;
use DCO_CA\Settings\RequiredAttachmentSetting;
use WP_Error;

defined( 'ABSPATH' ) || die;

final class AttachmentUploadValidator {

	private array $attachments;
	private bool $is_enabled_multiple_upload;
	private bool $is_required_attachment;
	private int $max_upload_size;
	private string $max_upload_size_formatted;
	private array $upload_errors;

	public function __construct(
		private EnableMultipleUploadSetting $enable_multiple_upload_setting,
		private RequiredAttachmentSetting $required_attachment_setting,
		private MaxUploadSizeSetting $max_upload_size_setting,
		private AllowedFileTypesSetting $allowed_file_types_setting,
	) {

		$this->is_enabled_multiple_upload = $this->enable_multiple_upload_setting->get_value();
		$this->is_required_attachment     = $this->required_attachment_setting->get_value();
		$this->max_upload_size            = $this->max_upload_size_setting->get_value( MaxUploadSizeFormat::IN_BYTES );
		$this->max_upload_size_formatted  = $this->max_upload_size_setting->get_value( MaxUploadSizeFormat::FORMATTED );

		$this->upload_errors = [
			1 => sprintf(
				/* translators: %s: the maximum allowed upload file size */
				__( 'The file is too large. Allowed attachments up to %s.', 'dco-comment-attachment' ),
				$this->max_upload_size_formatted
			),
			2 => __(
				'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
				'dco-comment-attachment'
			),
			3 => __( 'The uploaded file was only partially uploaded.', 'dco-comment-attachment' ),
			6 => __( 'Missing a temporary folder.', 'dco-comment-attachment' ),
			7 => __( 'Failed to write file to disk.', 'dco-comment-attachment' ),
			8 => __( 'A PHP extension stopped the file upload.', 'dco-comment-attachment' ),
		];
	}

	public function validate( array $attachments ): bool|WP_Error {

		$this->attachments = $attachments;

		if ( ! $this->is_need_validate_attachment() ) {
			return false;
		}

		foreach ( [
			$this->check_required_attachment( ... ),
			$this->check_error_codes( ... ),
			$this->check_multiple_upload( ... ),
			$this->check_upload_size( ... ),
			$this->check_file_types( ... ),
		] as $check ) {

			$result = $check();

			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		return true;
	}

	private function is_need_validate_attachment(): bool {

		return $this->is_attachment_uploaded() || $this->is_required_attachment;
	}

	private function is_attachment_uploaded(): bool {

		if ( ! $this->attachments ) {
			return false;
		}

		$tmp_names = (array) $this->attachments['tmp_name'];

		if ( ! isset( $tmp_names[0] ) || ! is_uploaded_file( $tmp_names[0] ) ) {
			return false;
		}

		return true;
	}

	private function check_required_attachment(): bool|WP_Error {

		if ( ! $this->is_attachment_uploaded() && $this->is_required_attachment ) {

			return new WP_Error(
				'dco-comment-attachment',
				esc_html__( 'Attachment is required.', 'dco-comment-attachment' ),
				[ 'status' => 400 ]
			);
		}

		return true;
	}

	private function check_multiple_upload(): bool|WP_Error {

		if ( ! $this->is_enabled_multiple_upload && is_array( $this->attachments['name'] ) ) {

			return new WP_Error(
				'dco-comment-attachment',
				esc_html__( 'Uploading multiple files is forbidden!', 'dco-comment-attachment' ),
				[ 'status' => 403 ]
			);
		}

		return true;
	}

	private function check_error_codes(): bool|WP_Error {

		$error_codes = (array) $this->attachments['error'];

		foreach ( $error_codes as $error_code ) {

			$upload_error = $this->get_upload_error( $error_code );

			if ( $upload_error ) {

				return new WP_Error(
					"dco-comment-attachment-$error_code",
					esc_html( $upload_error ),
					[ 'status' => 500 ]
				);
			}
		}

		return true;
	}

	private function check_upload_size(): bool|WP_Error {

		$sizes = (array) $this->attachments['size'];
		$size  = array_sum( $sizes );

		if ( $size > $this->max_upload_size ) {

			$error_code   = 1;
			$upload_error = $this->get_upload_error( $error_code );

			return new WP_Error(
				"dco-comment-attachment-$error_code",
				esc_html( $upload_error ),
				[ 'status' => 500 ]
			);
		}

		return true;
	}

	private function check_file_types(): bool|WP_Error {

		$names = (array) $this->attachments['name'];

		foreach ( $names as $name ) {

			$filetype = $this->allowed_file_types_setting->apply_file_types_filter_to_function(
				wp_check_filetype( ... ),
				[ $name ]
			);

			if ( ! $filetype['ext'] ) {

				return new WP_Error(
					'dco-comment-attachment',
					esc_html__( "WordPress doesn't allow this type of uploads.", 'dco-comment-attachment' ),
					[ 'status' => 400 ]
				);
			}
		}

		return true;
	}

	private function get_upload_error( int $error_code ): ?string {

		return $this->upload_errors[ $error_code ] ?? null;
	}
}
