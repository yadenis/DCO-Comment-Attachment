<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\Services\PluginService;
use DCO_CA\Settings\AllowedFileTypesSetting;

defined( 'ABSPATH' ) || die;

final class AttachmentUploader {

	public function __construct(
		private AllowedFileTypesSetting $allowed_file_types_setting,
	) {
	}

	public function upload( array $attachments, int $comment_post_id ): array {

		$this->require_dependencies();

		$field_name             = PluginService::UPLOAD_FIELD_NAME;
		$attachments_for_upload = $this->prepare_attachments_for_upload( $attachments );
		$comment_post_id        = $this->filter_comment_post_id( $comment_post_id );

		$attachments_ids = [];

		foreach ( $attachments_for_upload as $attachment ) {

			$_FILES[ $field_name ] = $attachment;

			$attachment_id = $this->allowed_file_types_setting->apply_file_types_filter_to_function(
				media_handle_upload( ... ),
				[
					$field_name,
					$comment_post_id,
				]
			);

			if ( ! is_wp_error( $attachment_id ) ) {
				$attachments_ids[] = $attachment_id;
			}
		}

		$_FILES[ $field_name ] = $attachments;

		return $attachments_ids;
	}

	/**
	 * The `media_handle_upload` function is only loaded by default in the WordPress admin area,
	 * so let's make sure it's available on the frontend.
	 */
	private function require_dependencies(): void {

		if ( function_exists( 'media_handle_upload' ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
	}

	/**
	 * Emulates the upload of each file separately, because the `media_handle_upload`
	 * function doesn't support uploading multiple files.
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
	 * @param array $attachments Attachments to upload as a `$_FILES`-like array.
	 *
	 * @return array $attachments_for_upload An array of attachments, where each attachment
	 *                                       is an associative array.
	 */
	private function prepare_attachments_for_upload( array $attachments ): array {

		$attachments_for_upload = [];

		array_walk(
			$attachments,
			// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
			fn( &$value ) => $value = (array) $value
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

	private function filter_comment_post_id( int $comment_post_id ): int {

		/**
		 * Filters whether to attach the attachment to the commented post.
		 *
		 * @since 2.2.0
		 *
		 * @param bool $attach_to_post Whether to attach the attachment to the commented post.
		 */
		return (int) apply_filters( 'dco_ca_attach_to_post', true ) ? $comment_post_id : 0;
	}
}
