<?php

declare(strict_types=1);

namespace DCO_CA\Services;

use DCO_CA\Comment;

defined( 'ABSPATH' ) || die;

final class CommentService {

	public function __construct() {
	}

	public function attach_attachments_to_comment( int $comment_id, array $attachment_ids ): bool {

		$comment = Comment::get_instance( $comment_id );
		if ( ! $comment ) {
			return false;
		}

		$comment->attachment_ids = $attachment_ids;

		return $comment->save();
	}
}
