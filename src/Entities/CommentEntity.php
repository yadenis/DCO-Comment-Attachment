<?php

declare(strict_types=1);

namespace DCO_CA\Entities;

use DCO_CA\Services\AttachmentService;
use DCO_CA\Services\PluginService;
use DCO_CA\Services\SettingsService;
use WP_Comment;

defined( 'ABSPATH' ) || die;

final class CommentEntity {

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

	public function get_attachments(): array {

		return $this->attachments;
	}

	public function render_attachments(): void {

		if ( ! $this->has_attachments() ) {
			return;
		}

		$this->attachment_service->render_attachments( $this->get_attachments(), $this->id );
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

	public function detach_attachments(): void {

		$this->attachments = [];
	}

	public function delete_attachments_files(): void {

		$this->attachments_to_delete = $this->attachments;

		$this->attachments = [];
	}

	public function save(): void {

		$this->handle_attachments_to_delete();

		if ( ! $this->has_attachments() ) {
			$attachments = '';
		} else {

			$attachments = wp_list_pluck( $this->attachments, 'id' );

			// Compatibility with 1.x version.
			if ( $this->has_one_attachment() ) {
				$attachments = current( $attachments );
			}
		}

		if ( $attachments ) {

			update_comment_meta(
				$this->id,
				PluginService::ATTACHMENT_ID_META_KEY,
				$attachments
			);
		} else {

			delete_comment_meta(
				$this->id,
				PluginService::ATTACHMENT_ID_META_KEY
			);
		}
	}

	private function handle_attachments_to_delete(): void {

		foreach ( $this->attachments_to_delete as $attachment ) {

			wp_delete_attachment( $attachment->id );
		}
	}

	private function init_attachments(): void {

		$ids = get_comment_meta( $this->id, PluginService::ATTACHMENT_ID_META_KEY, single: true );

		if ( ! $ids ) {

			$this->attachments = [];
			return;
		}

		$this->set_attachment_ids( (array) $ids );
	}
}
