<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\Services\AttachmentService;
use DCO_CA\Services\SettingsService;
use WP_Comment;

defined( 'ABSPATH' ) || die;

final class Comment {

	private const ATTACHMENT_ID_META_KEY = 'attachment_id';

	private array $attachments;

	public readonly int $id;
	public array $attachment_ids = [];

	public function __construct(
		private AttachmentService $attachment_service,
		private SettingsService $settings_service,
		private WP_Comment $comment
	) {

		$this->id             = (int) $this->comment->comment_ID;
		$this->attachment_ids = $this->get_attachment_ids();
	}

	public function get_attachments(): array {

		if ( isset( $this->attachments ) ) {
			return $this->attachments;
		}

		$this->attachments = [];

		foreach ( $this->attachment_ids as $attachment_id ) {

			$attachment = $this->attachment_service->get_attachment_instance( $attachment_id );

			if ( ! $attachment ) {
				continue;
			}

			$this->attachments[] = $attachment;
		}

		return $this->attachments;
	}

	public function render_attachments(): void {

		if ( ! $this->has_attachments() ) {
			return;
		}

		if ( $this->has_one_attachment() ) {
			$this->render_single_attachment();
		} else {
			$this->render_multiple_attachments();
		}
	}

	public function has_attachments(): bool {

		return (bool) count( $this->attachment_ids );
	}

	public function has_one_attachment(): bool {

		return 1 === count( $this->attachment_ids );
	}

	public function save(): bool {

		if ( ! $this->has_attachments() ) {
			$attachments = '';
		} else {

			// Compatibility with 1.x version.
			$attachments = $this->attachment_ids;
			if ( 1 === count( $attachments ) ) {
				$attachments = current( $this->attachment_ids );
			}
		}

		return (bool) update_comment_meta(
			$this->id,
			self::ATTACHMENT_ID_META_KEY,
			$attachments
		);
	}

	private function get_attachment_ids(): array {

		$ids = get_comment_meta( $this->id, self::ATTACHMENT_ID_META_KEY, single: true );

		if ( ! $ids ) {
			return [];
		}

		return array_map(
			fn( string $id ): int => intval( $id ),
			(array) $ids
		);
	}

	private function render_single_attachment(): void {
	}

	private function render_multiple_attachments(): void {

		$attachments_content = [];

		foreach ( $this->get_attachments() as $attachment ) {

			$attachments_content[] = $attachment->get_render();

		}
	}
}
