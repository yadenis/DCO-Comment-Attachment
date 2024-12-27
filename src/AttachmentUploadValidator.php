<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\Services\PluginService;
use DCO_CA\Settings\AllowedFileTypes;
use DCO_CA\Settings\EnableMultipleUpload;
use DCO_CA\Settings\Enums\MaxUploadSizeFormat;
use DCO_CA\Settings\MaxUploadSize;
use DCO_CA\Settings\RequiredAttachment;
use WP_Error;

defined( 'ABSPATH' ) || die;

final class AttachmentUploadValidator {

	private array $attachments;
	private bool $is_enabled_multiple_upload;
	private bool $is_required_attachment;
	private int $max_upload_size_value;
	private string $max_upload_size_formatted_value;

	public function __construct(
		private PluginService $plugin_service,
		private EnableMultipleUpload $enable_multiple_upload,
		private RequiredAttachment $required_attachment,
		private MaxUploadSize $max_upload_size,
		private AllowedFileTypes $allowed_file_types,
	) {

		$this->attachments = $this->get_attachments();

		$this->is_enabled_multiple_upload      = $this->enable_multiple_upload->get_value();
		$this->is_required_attachment          = $this->required_attachment->get_value();
		$this->max_upload_size_value           = $this->max_upload_size->get_value();
		$this->max_upload_size_formatted_value = $this->max_upload_size->get_value( MaxUploadSizeFormat::FORMATTED );
	}

	public function validate(): bool|WP_Error {

		$check_required_attachment = $this->check_required_attachment();
		if ( is_wp_error( $check_required_attachment ) ) {
			return $check_required_attachment;
		}

		$check_attachment_uploaded = $this->check_attachment_uploaded();
		if ( ! $check_attachment_uploaded ) {
			return false;
		}

		$check_error_codes = $this->check_error_codes();
		if ( is_wp_error( $check_error_codes ) ) {
			return $check_error_codes;
		}

		$check_multiple_upload = $this->check_multiple_upload();
		if ( is_wp_error( $check_multiple_upload ) ) {
			return $check_multiple_upload;
		}

		$check_upload_size = $this->check_upload_size();
		if ( is_wp_error( $check_upload_size ) ) {
			return $check_upload_size;
		}

		$check_file_types = $this->check_file_types();
		if ( is_wp_error( $check_file_types ) ) {
			return $check_file_types;
		}

		// $this->attachment_checked = true;

		return true;
	}

	private function get_attachments(): array {

		$field_name = $this->plugin_service->get_upload_field_name();

		return $_FILES[ $field_name ] ?? [];
	}

	private function check_required_attachment(): bool|WP_Error {

		$uploaded = $this->check_attachment_uploaded();

		if ( ! $uploaded && $this->is_required_attachment ) {

			return new WP_Error(
				'dco-comment-attachment',
				esc_html__( 'Attachment is required.', 'dco-comment-attachment' ),
				[ 'status' => 400 ]
			);
		}

		return true;
	}

	private function check_attachment_uploaded(): bool {

		if ( ! $this->attachments ) {
			return false;
		}

		$tmp_names = (array) $this->attachments['tmp_name'];

		if ( ! isset( $tmp_names[0] ) || ! is_uploaded_file( $tmp_names[0] ) ) {
			return false;
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
					$upload_error,
					[ 'status' => 500 ]
				);
			}
		}

		return true;
	}

	private function check_upload_size(): bool|WP_Error {

		$sizes = (array) $this->attachments['size'];
		$size  = array_sum( $sizes );

		if ( $size > $this->max_upload_size_value ) {

			$error_code   = 1;
			$upload_error = $this->get_upload_error( $error_code );

			return new WP_Error(
				"dco-comment-attachment-$error_code",
				$upload_error,
				[ 'status' => 500 ]
			);
		}

		return true;
	}

	private function check_file_types(): bool|WP_Error {

		$names = (array) $this->attachments['name'];

		foreach ( $names as $name ) {

			$filetype = $this->allowed_file_types->apply_file_types_filter_to_function(
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

		$upload_errors = [
			1 => sprintf(
				/* translators: %s: the maximum allowed upload file size */
				esc_html__( 'The file is too large. Allowed attachments up to %s.', 'dco-comment-attachment' ),
				$this->max_upload_size_formatted_value
			),
			2 => esc_html__(
				'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
				'dco-comment-attachment'
			),
			3 => esc_html__( 'The uploaded file was only partially uploaded.', 'dco-comment-attachment' ),
			6 => esc_html__( 'Missing a temporary folder.', 'dco-comment-attachment' ),
			7 => esc_html__( 'Failed to write file to disk.', 'dco-comment-attachment' ),
			8 => esc_html__( 'A PHP extension stopped the file upload.', 'dco-comment-attachment' ),
		];

		return $upload_errors[ $error_code ] ?? null;
	}
}
