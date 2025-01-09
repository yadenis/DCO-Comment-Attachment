<?php

declare(strict_types=1);

namespace DCO_CA\Services;

use DCO_CA\Entities\CommentEntity;
use WP_Comment;

defined( 'ABSPATH' ) || die;

final class CommentService {

	private array $instances = [];

	public function __construct(
		private PluginService $plugin_service,
		private AttachmentService $attachment_service,
		private SettingsService $settings_service,
	) {
	}

	public function get_comment_instance( int|WP_Comment $comment_id ): ?CommentEntity {

		if ( isset( $this->instances[ $comment_id ] ) ) {
			return $this->instances[ $comment_id ];
		}

		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return null;
		}

		$this->instances[ $comment_id ] = new CommentEntity(
			$this->plugin_service,
			$this->settings_service,
			$this->attachment_service,
			$comment
		);

		return $this->instances[ $comment_id ];
	}

	public function get_current_comment_instance(): ?CommentEntity {

		$current_comment_id = (int) get_comment_ID();
		if ( ! $current_comment_id ) {
			return null;
		}

		if ( isset( $this->instances[ $current_comment_id ] ) ) {
			return $this->instances[ $current_comment_id ];
		}

		$this->instances[ $current_comment_id ] = $this->get_comment_instance( $current_comment_id );

		return $this->instances[ $current_comment_id ];
	}

	public function get_post_comments_with_attachments( int $post_id ): array {

		$args = [
			'post_id'  => $post_id,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_key' => PluginService::ATTACHMENT_ID_META_KEY,
			'status'   => 'approve',
		];

		$comments = get_comments( $args );

		return array_map(
			$this->get_comment_instance( ... ),
			$comments
		);
	}

	public function get_comment_post_id( int $comment_id ): ?int {

		return $this->get_comment_instance( $comment_id )?->post_id;
	}

	public function attach_attachments_to_comment( int $comment_id, array $attachment_ids ): void {

		$comment = $this->get_comment_instance( $comment_id );
		if ( ! $comment ) {
			return;
		}

		$comment->set_attachment_ids( $attachment_ids );

		$comment->save();
	}

	public function delete_comment_attachments( int $comment_id ): void {

		$comment = $this->get_comment_instance( $comment_id );
		if ( ! $comment ) {
			return;
		}

		$comment->delete_attachments();

		$comment->save();
	}

	public function detach_comment_attachments( int $comment_id ): void {

		$comment = $this->get_comment_instance( $comment_id );
		if ( ! $comment ) {
			return;
		}

		$comment->detach_attachments();

		$comment->save();
	}
}
