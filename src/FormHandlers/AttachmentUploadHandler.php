<?php
/**
 * Form Handlers: Attachment Upload Handler
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

use DCO_CA\Services\PluginService;
use DCO_CA\Services\SettingsService;

defined( 'ABSPATH' ) || die;

/**
 * Handles the process of uploading attachments to comments.
 *
 * @since 3.0.0
 */
final class AttachmentUploadHandler {

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
	}

	/**
	 * Handles the attachments upload process.
	 *
	 * @since 3.0.0
	 *
	 * @param array $uploaded_attachments The `$_FILES`-like array of uploaded attachments.
	 * @param int   $comment_post_id The comment post id.
	 *
	 * @return int[] List of attachment ids.
	 */
	public function handle( array $uploaded_attachments, int $comment_post_id ): array {

		if ( ! $uploaded_attachments ) {
			return [];
		}

		$this->load_dependencies();

		$field_name        = PluginService::UPLOAD_FIELD_NAME;
		$split_attachments = $this->split_attachments( $uploaded_attachments );
		$post_id           = $this->determine_post_id_for_attachment( $comment_post_id );

		$attachment_ids = [];

		foreach ( $split_attachments as $attachment ) {

			$_FILES[ $field_name ] = $attachment;

			$attachment_id = $this->settings_service->apply_file_types_filter_to_function(
				media_handle_upload( ... ),
				[
					$field_name,
					$post_id,
				]
			);

			if ( ! is_wp_error( $attachment_id ) ) {
				$attachment_ids[] = $attachment_id;
			}
		}

		$_FILES[ $field_name ] = $uploaded_attachments;

		return $attachment_ids;
	}

	/**
	 * The `media_handle_upload` function is only loaded by default in the WordPress admin area,
	 * so let's make sure it's available on the frontend.
	 *
	 * @since 3.0.0
	 */
	private function load_dependencies(): void {

		if ( function_exists( 'media_handle_upload' ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
	}

	/**
	 * Splits a `$_FILES`-like array of attachments into individual attachment arrays.
	 * This is necessary because the `media_handle_upload` function only supports handling
	 * one file at a time.
	 *
	 * Example input:
	 * ```php
	 * [
	 *     'name' => ['file1.jpg', 'file2.jpg'],
	 *     'type' => ['image/jpeg', 'image/jpeg'],
	 *     'tmp_name' => ['/tmp/phpYzdqkD', '/tmp/phpYzdqkE'],
	 *     'error' => [0, 0],
	 *     'size' => [12345, 67890],
	 * ]
	 * ```
	 *
	 * Example output:
	 * ```php
	 * [
	 *     [
	 *         'name' => 'file1.jpg',
	 *         'type' => 'image/jpeg',
	 *         'tmp_name' => '/tmp/phpYzdqkD',
	 *         'error' => 0,
	 *         'size' => 12345,
	 *     ],
	 *     [
	 *         'name' => 'file2.jpg',
	 *         'type' => 'image/jpeg',
	 *         'tmp_name' => '/tmp/phpYzdqkE',
	 *         'error' => 0,
	 *         'size' => 67890,
	 *     ],
	 * ]
	 * ```
	 *
	 * @since 3.0.0
	 *
	 * @param array $attachments Attachments to split as a `$_FILES`-like array.
	 *
	 * @return array $attachments_for_upload The list of attachments, where each attachment
	 *                                       is an associative array.
	 */
	private function split_attachments( array $attachments ): array {

		if ( ! $attachments ) {
			return [];
		}

		$attachments_for_upload = [];

		array_walk(
			$attachments,
			// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
			static fn( int|string|array &$value ): array => $value = (array) $value
		);

		$keys  = array_keys( $attachments );
		$count = count( current( $attachments ) );

		for ( $i = 0; $i < $count; $i++ ) {

			$attachments_for_upload[] = array_combine(
				$keys,
				array_column( $attachments, $i )
			);
		}

		return $attachments_for_upload;
	}

	/**
	 * Defines whether the attachment will be attached to the post.
	 *
	 * If post_id is 0, it will not be attached.
	 *
	 * @since 3.0.0
	 *
	 * @param int $post_id The post id.
	 *
	 * @return int The filtered post id.
	 */
	private function determine_post_id_for_attachment( int $post_id ): int {

		/**
		 * Filters whether to attach the attachment to the commented post.
		 *
		 * @since 2.2.0
		 *
		 * @param bool $attach_to_post Whether to attach the attachment to the commented post.
		 */
		return (int) apply_filters( 'dco_ca_attach_to_post', true ) ? $post_id : 0;
	}
}
