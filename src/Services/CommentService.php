<?php

declare(strict_types=1);

namespace DCO_CA\Services;

use DCO_CA\Comment;
use WP_Comment;

defined( 'ABSPATH' ) || die;

final class CommentService {

	public function __construct(
		private PluginService $plugin_service,
		private AttachmentService $attachment_service,
		private SettingsService $settings_service,
	) {
	}

	public function get_comment_instance( int|WP_Comment $comment_id ): ?Comment {

		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return null;
		}

		return new Comment(
			$this->plugin_service,
			$this->settings_service,
			$this->attachment_service,
			$comment
		);
	}

	public function attach_attachments_to_comment( int $comment_id, array $attachment_ids ): void {

		$comment = $this->get_comment_instance( $comment_id );
		if ( ! $comment ) {
			return;
		}

		$comment->set_attachment_ids( $attachment_ids );

		$comment->save();
	}

	public function get_current_comment(): ?Comment {

		$current_wp_comment = get_comment();
		if ( ! $current_wp_comment ) {
			return null;
		}

		return $this->get_comment_instance( $current_wp_comment );
	}

	/*
		public static function get_comments( int|string $post_id ): array {

		$args = [
			'post_id'  => $post_id,
			'meta_key' => static::ATTACHMENT_META_KEY,
			'status'   => 'approve',
		];

		$comments = get_comments( $args );

		return static::get_bulk_instances( $comments );
	}
	*/
}
