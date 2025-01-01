<?php

declare(strict_types=1);

namespace DCO_CA\FormHandlers;

use DCO_CA\Services\PluginService;
use DCO_CA\Settings\AllowedFileTypesSetting;

defined( 'ABSPATH' ) || die;

final class AttachmentUploadHandler {

	public function __construct(
		private AllowedFileTypesSetting $allowed_file_types_setting,
	) {
	}

	public function handle( array $uploaded_attachments, int $comment_post_id ): array {

		if ( ! $uploaded_attachments ) {
			return [];
		}

		$this->load_dependencies();

		$field_name        = PluginService::UPLOAD_FIELD_NAME;
		$split_attachments = $this->split_attachments( $uploaded_attachments );
		$comment_post_id   = $this->filter_comment_post_id( $comment_post_id );

		$attachment_ids = [];

		foreach ( $split_attachments as $attachment ) {

			$_FILES[ $field_name ] = $attachment;

			$attachment_id = $this->allowed_file_types_setting->apply_file_types_filter_to_function(
				media_handle_upload( ... ),
				[
					$field_name,
					$comment_post_id,
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
	 * @param array $attachments Attachments to split as a `$_FILES`-like array.
	 *
	 * @return array $attachments_for_upload An array of attachments, where each attachment
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
