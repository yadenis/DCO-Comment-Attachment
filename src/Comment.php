<?php

declare(strict_types=1);

namespace DCO_CA;

use WP_Comment;

defined( 'ABSPATH' ) || die;

final class Comment {

	private const ATTACHMENT_META_KEY = 'attachment_id';

	public readonly int $id;
	public array $attachment_ids = [];

	private function __construct(
		private WP_Comment $comment
	) {

		$this->id = (int) $this->comment->comment_ID;
	}

	public static function get_instance( int $comment_id ): ?self {

		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return null;
		}

		return new self( $comment );
	}

	public function save(): bool {

		// Compatibility with 1.x version.
		$attachments = $this->attachment_ids;
		if ( 1 === count( $attachments ) ) {
			$attachments = current( $this->attachment_ids );
		}

		return (bool) update_comment_meta(
			$this->id,
			self::ATTACHMENT_META_KEY,
			$attachments
		);
	}
}
