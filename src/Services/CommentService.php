<?php

declare(strict_types=1);

namespace DCO_CA\Services;

use DCO_CA\Comment;
use WP_Comment;

defined( 'ABSPATH' ) || die;

final class CommentService {

	public function __construct(
		private AttachmentService $attachment_service,
		private SettingsService $settings_service,
	) {
	}

	public function get_comment_instance( int|WP_Comment $comment_id ): ?Comment {

		if ( $comment_id instanceof WP_Comment ) {

			return new Comment(
				$this->attachment_service,
				$this->settings_service,
				$comment_id
			);
		}

		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return null;
		}

		return new Comment(
			$this->attachment_service,
			$this->settings_service,
			$comment
		);
	}

	public function attach_attachments_to_comment( int $comment_id, array $attachment_ids ): bool {

		$comment = $this->get_comment_instance( $comment_id );
		if ( ! $comment ) {
			return false;
		}

		$comment->set_attachment_ids( $attachment_ids );

		return $comment->save();
	}

	public function get_current_comment( ?WP_Comment $wp_comment ): ?Comment {

		$current_wp_comment = get_comment( $wp_comment );
		if ( ! $current_wp_comment ) {
			return null;
		}

		return $this->get_comment_instance( $current_wp_comment );
	}
}
