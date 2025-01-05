<?php

declare(strict_types=1);

namespace DCO_CA\AdminActions;

use DCO_CA\Comment;
use DCO_CA\Services\CommentService;
use DCO_CA\Services\SettingsService;

defined( 'ABSPATH' ) || die;

final class DeleteCommentAttachmentBulkAdminAction {

	protected const ACTION_NAME = 'delete_comment_attachment_bulk';

	protected bool $is_delete_attachment;

	public function __construct(
		private CommentService $comment_service,
		private SettingsService $settings_service
	) {

		$this->is_delete_attachment = $this->settings_service->is_delete_attachment_from_media_library();

		add_filter( 'bulk_actions-edit-comments', $this->add_delete_comment_attachment_bulk_action( ... ) );

		add_action( 'admin_action_' . self::ACTION_NAME, $this->handle_delete_comment_attachment_bulk_action( ... ) );

		add_filter( 'ngettext', $this->show_bulk_action_success_message( ... ), 10, 5 );

		add_filter( 'removable_query_args', $this->add_bulk_action_name_to_removable_query_args( ... ) );
	}

	public function add_delete_comment_attachment_bulk_action( array $actions ): array {

		$text = __( 'Detach Attachments', 'dco-comment-attachment' );
		if ( $this->is_delete_attachment ) {
			$text = __( 'Delete Attachments', 'dco-comment-attachment' );
		}

		$actions[ self::ACTION_NAME ] = $text;

		return $actions;
	}

	public function handle_delete_comment_attachment_bulk_action(): never {

		check_admin_referer( 'bulk-comments' );

		$comment_ids = $this->get_request_comment_ids();

		wp_defer_comment_counting( true );

		$count = 0;
		foreach ( $comment_ids as $comment_id ) {

			$comment = $this->comment_service->get_comment_instance( $comment_id );

			if ( ! $this->process_bulk_action_checks( $comment ) ) {
				continue;
			}

			if ( $this->is_delete_attachment ) {
				$comment->delete_attachments_files();
			} else {
				$comment->detach_attachments();
			}

			++$count;
		}

		wp_defer_comment_counting( false );

		$this->handle_success( $count );
	}

	public function show_bulk_action_success_message(
		string $translation,
		string $single,
		string $plural,
		int $number,
		string $domain
	): string {

		/**
		 * There is no hook in WordPress to add updated message for custom comments bulk action.
		 * So we override the approval message if attachment deletion was triggered.
		 */
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_REQUEST[ self::ACTION_NAME ] ) ) {
			return $translation;
		}

		if ( '%s comment approved.' === $single && '%s comments approved.' === $plural && 'default' === $domain ) {

			/* translators: %s: Number of comments. */
			$text = _n( 'Attachments detached from %s comment.', 'Attachments detached from %s comments.', $number, 'dco-comment-attachment' );
			if ( $this->is_delete_attachment ) {
				/* translators: %s: Number of comments. */
				$text = _n( 'Attachments deleted from %s comment.', 'Attachments deleted from %s comments.', $number, 'dco-comment-attachment' );
			}

			return $text;
		}

		return $translation;
	}

	public function add_bulk_action_name_to_removable_query_args( array $removable_query_args ): array {

		$removable_query_args[] = self::ACTION_NAME;

		return $removable_query_args;
	}

	private function get_request_comment_ids(): array {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$ids = wp_unslash( $_REQUEST['delete_comments'] ?? [] );
		if ( ! is_array( $ids ) ) {
			return [];
		}

		return array_map(
			fn( string $id ): int => intval( $id ),
			$ids
		);
	}

	private function process_bulk_action_checks( ?Comment $comment ): bool {

		if ( ! $comment ) {
			return false;
		}

		if ( ! current_user_can( 'edit_comment', $comment->id ) ) {
			return false;
		}

		if ( ! $comment->has_attachments() ) {
			return false;
		}

		return true;
	}

	private function handle_success( int $count_processed_comments ): never {

		$redirect_to = add_query_arg( self::ACTION_NAME, $count_processed_comments, wp_get_referer() );

		// @see DeleteCommentAttachmentBulkAdminAction::show_bulk_action_success_message() for details.
		$redirect_to = add_query_arg( 'approved', $count_processed_comments, $redirect_to );

		wp_safe_redirect( $redirect_to );
		exit;
	}
}
