<?php
/**
 * Services: Comment
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA\Services;

use DCO_CA\Entities\CommentEntity;
use WP_Comment;

defined( 'ABSPATH' ) || die;

/**
 * Service for handling comment-related operations.
 *
 * @since 3.0.0
 */
final class CommentService {

	/**
	 * Cache of comment instances by id.
	 *
	 * Stores the created CommentEntity instances
	 * to avoid repeated creation for the same comment id.
	 *
	 * @since 3.0.0
	 *
	 * @var array<int, \DCO_CA\Entities\CommentEntity>
	 */
	private array $instances = [];

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 *
	 * @param PluginService     $plugin_service     Service functions for the plugin.
	 * @param AttachmentService $attachment_service Service functions for attachments.
	 * @param SettingsService   $settings_service   Service functions for settings.
	 */
	public function __construct(
		private PluginService $plugin_service,
		private AttachmentService $attachment_service,
		private SettingsService $settings_service,
	) {
	}

	/**
	 * Retrieves an instance of comment for the given comment id or WP_Comment object.
	 *
	 * If the instance has already been created, it will be returned from the cache.
	 *
	 * @since 3.0.0
	 *
	 * @param int|WP_Comment $wp_comment The comment id or WP_Comment object.
	 *
	 * @return CommentEntity|null The comment instance, or null if it doesn't exist.
	 */
	public function get_comment_instance( int|WP_Comment $wp_comment ): ?CommentEntity {

		$comment_id = $wp_comment instanceof WP_Comment ? (int) $wp_comment->comment_ID : $wp_comment;

		if ( isset( $this->instances[ $comment_id ] ) ) {
			return $this->instances[ $comment_id ];
		}

		$comment = get_comment( $wp_comment );
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

	/**
	 * Retrieves the current comment instance.
	 *
	 * This is used to get the comment instance for the comment currently being processed
	 * (e.g., the comment being viewed or edited).
	 *
	 * If the instance has already been created, it will be returned from the cache.
	 *
	 * @since 3.0.0
	 *
	 * @return CommentEntity|null The comment instance, or null if there is no current comment.
	 */
	public function get_current_comment_instance(): ?CommentEntity {

		$current_comment = get_comment();
		if ( ! $current_comment ) {
			return null;
		}

		return $this->get_comment_instance( $current_comment );
	}

	/**
	 * Retrieves all comments with attachments for a given post.
	 *
	 * @since 3.0.0
	 *
	 * @param int $post_id The post id to fetch comments for.
	 *
	 * @return \DCO_CA\Entities\CommentEntity[] The list of comments.
	 */
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

	/**
	 * Retrieves the post id associated with a specific comment.
	 *
	 * @since 3.0.0
	 *
	 * @param int $comment_id The comment id.
	 *
	 * @return int|null The post id, or null if the comment doesn't exist.
	 */
	public function get_comment_post_id( int $comment_id ): ?int {

		return $this->get_comment_instance( $comment_id )?->post_id;
	}

	/**
	 * Attaches attachments to a comment.
	 *
	 * @since 3.0.0
	 *
	 * @param int   $comment_id The comment id.
	 * @param int[] $attachment_ids The list of attachment ids to associate with the comment.
	 */
	public function attach_attachments_to_comment( int $comment_id, array $attachment_ids ): void {

		$comment = $this->get_comment_instance( $comment_id );

		if ( ! $comment ) {
			return;
		}

		$comment->set_attachment_ids( $attachment_ids );

		$comment->save();
	}

	/**
	 * Deletes attachments from a comment.
	 *
	 * @since 3.0.0
	 *
	 * @param int $comment_id The comment id.
	 */
	public function delete_comment_attachments( int $comment_id ): void {

		$comment = $this->get_comment_instance( $comment_id );

		if ( ! $comment ) {
			return;
		}

		$comment->delete_attachments();

		$comment->save();
	}

	/**
	 * Detaches attachments from a comment.
	 *
	 * @since 3.0.0
	 *
	 * @param int $comment_id The comment id.
	 */
	public function detach_comment_attachments( int $comment_id ): void {

		$comment = $this->get_comment_instance( $comment_id );

		if ( ! $comment ) {
			return;
		}

		$comment->detach_attachments();

		$comment->save();
	}

	/**
	 * Checks if a comment exists.
	 *
	 * @since 3.0.0
	 *
	 * @param int $comment_id The comment id to check.
	 *
	 * @return bool True if the comment exists, false otherwise.
	 */
	public function is_comment_exists( int $comment_id ): bool {

		return (bool) $this->get_comment_instance( $comment_id );
	}

	/**
	 * Checks if a comment has attachments.
	 *
	 * @since 3.0.0
	 *
	 * @param int $comment_id The comment id to check.
	 *
	 * @return bool True if the comment has attachments, false otherwise.
	 */
	public function is_comment_has_attachments( int $comment_id ): bool {

		return (bool) $this->get_comment_instance( $comment_id )?->has_attachments();
	}
}
