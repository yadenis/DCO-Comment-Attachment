<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\Services\AttachmentService;
use DCO_CA\Services\PluginService;
use DCO_CA\Services\SettingsService;
use WP_Comment;

defined( 'ABSPATH' ) || die;

final class Comment {

	private const ATTACHMENT_ID_META_KEY = 'attachment_id';

	private array $attachments;
	private array $attachments_to_delete = [];

	public readonly int $id;
	public readonly int $post_id;

	public function __construct(
		private PluginService $plugin_service,
		private SettingsService $settings_service,
		private AttachmentService $attachment_service,
		private WP_Comment $comment
	) {

		$this->id      = (int) $this->comment->comment_ID;
		$this->post_id = (int) $this->comment->comment_post_ID;

		$this->init_attachments();
	}

	public function render_attachments(): void {

		if ( ! $this->has_attachments() ) {
			return;
		}

		if ( ! $this->has_one_attachment() && $this->settings_service->is_combined_images() ) {

			$this->render_attachments_gallery();
			return;
		}

		$this->render_attachments_list();
	}

	public function has_attachments(): bool {

		return (bool) count( $this->attachments );
	}

	public function has_one_attachment(): bool {

		return 1 === count( $this->attachments );
	}

	public function set_attachment_ids( array $ids ): void {

		$this->attachments = [];

		foreach ( $ids as $attachment_id ) {

			$attachment = $this->attachment_service->get_attachment_instance( (int) $attachment_id );

			if ( ! $attachment ) {
				continue;
			}

			$this->attachments[] = $attachment;
		}
	}

	public function remove_attachments(): void {

		$this->attachments = [];
	}

	public function delete_attachments_with_media_files(): void {

		$this->attachments_to_delete = $this->attachments;

		$this->attachments = [];
	}

	public function save(): void {

		$this->handle_attachments_to_delete();

		if ( ! $this->has_attachments() ) {
			$attachments = '';
		} else {

			// Compatibility with 1.x version.
			$attachments = wp_list_pluck( $this->attachments, 'id' );

			if ( $this->has_one_attachment() ) {
				$attachments = current( $attachments );
			}
		}

		update_comment_meta(
			$this->id,
			self::ATTACHMENT_ID_META_KEY,
			$attachments
		);
	}

	private function init_attachments(): void {

		$ids = get_comment_meta( $this->id, self::ATTACHMENT_ID_META_KEY, single: true );

		if ( ! $ids ) {

			$this->attachments = [];
			return;
		}

		$this->set_attachment_ids( (array) $ids );
	}

	private function render_attachments_list(): void {

		$attachments_content = [];

		foreach ( $this->attachments as $attachment ) {

			$attachments_content[] = $attachment->get_markup();
		}

		$this->plugin_service->the_kses_post(
			implode( '', $attachments_content )
		);
	}

	private function render_attachments_gallery(): void {

		$images     = [];
		$not_images = [];

		foreach ( $this->attachments as $attachment ) {

			if ( $attachment->is_image() ) {
				$images[] = $attachment->get_gallery_image_markup();
			} else {
				$not_images[] = $attachment->get_markup();
			}
		}

		// wraps gallery.
		array_unshift( $images, '<div class="dco-attachment-gallery">' );
		$images[] = '</div>';

		$this->plugin_service->the_kses_post(
			implode( '', array_merge( $images, $not_images ) )
		);
	}

	private function handle_attachments_to_delete(): void {

		foreach ( $this->attachments_to_delete as $attachment ) {

			wp_delete_attachment( $attachment->id );
		}
	}
}
