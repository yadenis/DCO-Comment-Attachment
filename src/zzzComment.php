<?php

declare(strict_types=1);

namespace DCO_CA;

use WP_Comment;

defined( 'ABSPATH' ) || die;

final class Comment1 extends Instance {

	public const ATTACHMENT_META_KEY = 'attachment_id';

	private int $id;
	private ?array $attachment_ids = null;

	private function __construct(
		private WP_Comment $comment
	) {

		$this->id = $this->comment->comment_ID;

		$this->attachment_ids = $this->get_attachment_ids();
	}

	public static function get_instance(
		int|string|WP_Comment $comment_id = 0
	): static|null {

		if ( $comment_id instanceof WP_Comment ) {
			return new static( $comment_id );
		}

		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return null;
		}

		return new static( $comment );
	}

	public static function get_comments( int|string $post_id ): array {

		$args = [
			'post_id'  => $post_id,
			'meta_key' => static::ATTACHMENT_META_KEY,
			'status'   => 'approve',
		];

		$comments = get_comments( $args );

		return static::get_bulk_instances( $comments );
	}

	private function get_attachment_ids(): array {

		if ( null !== $this->attachment_ids ) {
			return $this->attachment_ids;
		}

		$attachment_ids = get_comment_meta(
			$this->id,
			static::ATTACHMENT_META_KEY,
			true
		);

		if ( ! $attachment_ids ) {
			return [];
		}

		return (array) $attachment_ids;
	}

	public function get_attachments( string|array $type = 'all' ): array {

		$attachments = Attachment::get_bulk_instances( $this->attachment_ids );
	}
}
