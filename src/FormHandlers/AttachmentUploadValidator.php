<?php
/**
 * Form Handlers: Attachment Upload Validator
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA\FormHandlers;

use DCO_CA\Services\SettingsService;
use WP_Error;

defined( 'ABSPATH' ) || die;

/**
 * Validates uploaded attachments.
 *
 * The validation process ensures that files uploaded through a form comply with the plugin settings
 * and WordPress file upload restrictions. If an error is found, a detailed message is returned
 * to help the user understand the reason behind the failure.
 *
 * @since 3.0.0
 */
final class AttachmentUploadValidator {

	/**
	 * The `$_FILES`-like array of uploaded attachments.
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	private array $uploaded_attachments;

	/**
	 * Whether multiple upload is enabled.
	 *
	 * @since 3.0.0
	 *
	 * @var bool
	 */
	private bool $is_enabled_multiple_upload;

	/**
	 * Whether attachment is required.
	 *
	 * @since 3.0.0
	 *
	 * @var bool
	 */
	private bool $is_required_attachment;

	/**
	 * Maximum allowed upload size in bytes.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	private int $max_upload_size;

	/**
	 * The formatted maximum upload file size.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private string $max_upload_size_formatted;

	/**
	 * List of upload error messages.
	 *
	 * @since 3.0.0
	 *
	 * @var array<int, string>
	 */
	private array $upload_errors;

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 *
	 * @param SettingsService $settings_service Service functions for settings.
	 */
	public function __construct(
		private SettingsService $settings_service,
	) {

		$this->is_enabled_multiple_upload = $this->settings_service->is_enabled_multiple_upload();
		$this->is_required_attachment     = $this->settings_service->is_required_attachment();
		$this->max_upload_size            = $this->settings_service->get_max_upload_size_in_bytes();
		$this->max_upload_size_formatted  = $this->settings_service->get_formatted_max_upload_size();

		$this->init_upload_errors();
	}

	/**
	 * Validates the uploaded attachments.
	 *
	 * @since 3.0.0
	 *
	 * @param array $uploaded_attachments The `$_FILES`-like array of uploaded attachments.
	 *
	 * @return null|bool|WP_Error Null if validation is not needed,
	 *                            true on success, WP_Error on failure.
	 */
	public function validate( array $uploaded_attachments ): null|bool|WP_Error {

		$this->uploaded_attachments = $uploaded_attachments;

		if ( ! $this->is_need_validate_attachments() ) {
			return null;
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

	/**
	 * Determines if attachments need validation.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if validation is needed, false otherwise.
	 */
	private function is_need_validate_attachments(): bool {

		return $this->is_attachments_uploaded() || $this->is_required_attachment;
	}

	/**
	 * Checks if any attachments have been uploaded.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if any attachments have been uploaded, false otherwise.
	 */
	private function is_attachments_uploaded(): bool {

		if ( empty( $this->uploaded_attachments['tmp_name'] ) ) {
			return false;
		}

		foreach ( (array) $this->uploaded_attachments['tmp_name'] as $tmp_name ) {

			if ( is_uploaded_file( $tmp_name ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Validates if the attachment is required.
	 *
	 * If attachments are required but none are uploaded, it returns a WP_Error.
	 *
	 * @since 3.0.0
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	private function check_required_attachment(): bool|WP_Error {

		if ( ! $this->is_attachments_uploaded() && $this->is_required_attachment ) {

			return new WP_Error(
				'dco-comment-attachment',
				esc_html__( 'Attachment is required.', 'dco-comment-attachment' ),
				[ 'status' => 400 ]
			);
		}

		return true;
	}

	/**
	 * Validates if multiple file upload is allowed.
	 *
	 * If the multiple upload is not enabled but an array of files is uploaded,
	 * it returns a WP_Error.
	 *
	 * @since 3.0.0
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	private function check_multiple_upload(): bool|WP_Error {

		if ( ! $this->is_enabled_multiple_upload && is_array( $this->uploaded_attachments['name'] ) ) {

			return new WP_Error(
				'dco-comment-attachment',
				esc_html__( 'Uploading multiple files is forbidden!', 'dco-comment-attachment' ),
				[ 'status' => 403 ]
			);
		}

		return true;
	}

	/**
	 * Validates upload error codes.
	 *
	 * Checks if there were any errors during the file upload process.
	 *
	 * @since 3.0.0
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	private function check_error_codes(): bool|WP_Error {

		$error_codes = (array) $this->uploaded_attachments['error'];

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

	/**
	 * Validates the uploaded file size.
	 *
	 * If the uploaded files exceed the maximum allowed size, it returns a WP_Error.
	 *
	 * @since 3.0.0
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	private function check_upload_size(): bool|WP_Error {

		$sizes      = (array) $this->uploaded_attachments['size'];
		$total_size = array_sum( $sizes );

		if ( $total_size > $this->max_upload_size ) {

			$error_code   = 1;
			$upload_error = $this->get_upload_error( $error_code );

			return new WP_Error(
				"dco-comment-attachment-$error_code",
				esc_html( $upload_error ),
				[ 'status' => 413 ]
			);
		}

		return true;
	}

	/**
	 * Validates the uploaded file types.
	 *
	 * Checks if the file types are allowed by the plugin settings.
	 *
	 * @since 3.0.0
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	private function check_file_types(): bool|WP_Error {

		$names = (array) $this->uploaded_attachments['name'];

		foreach ( $names as $name ) {

			$filetype = $this->settings_service->apply_file_types_filter_to_function(
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

	/**
	 * Retrieves the error message for a specific upload error code.
	 *
	 * @since 3.0.0
	 *
	 * @param int $error_code The error code.
	 *
	 * @return string|null The error message,
	 *                     or null if the error code is not recognized.
	 */
	private function get_upload_error( int $error_code ): ?string {

		return $this->upload_errors[ $error_code ] ?? null;
	}

	/**
	 * Initializes the upload error messages.
	 *
	 * @since 3.0.0
	 */
	private function init_upload_errors(): void {

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
}
