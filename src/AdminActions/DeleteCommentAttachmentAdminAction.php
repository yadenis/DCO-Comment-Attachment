<?php

declare(strict_types=1);

namespace DCO_CA\AdminActions;

use DCO_CA\Comment;
use DCO_CA\Services\CommentService;
use DCO_CA\Services\SettingsService;
use WP_Comment;
use WP_Error;

defined( 'ABSPATH' ) || die;

final class DeleteCommentAttachmentAdminAction {

	protected const ACTION_NAME = 'delete_comment_attachment';

	public function __construct(
		private CommentService $comment_service,
		private SettingsService $settings_service
	) {

		$this->load_dependencies();

		add_filter( 'comment_row_actions', $this->add_delete_comment_attachment_action_link( ... ), 10, 2 );

		add_action( 'admin_action_' . self::ACTION_NAME, $this->handle_delete_comment_attachment_action( ... ) );
		add_action( 'wp_ajax_' . self::ACTION_NAME, $this->handle_delete_comment_attachment_action( ... ) );

		add_action( 'wp_ajax_undo_' . self::ACTION_NAME, $this->handle_undo_delete_comment_attachment_action( ... ) );
	}

	public function add_delete_comment_attachment_action_link( array $actions, WP_Comment $comment ): array {

		$comment = $this->comment_service->get_comment_instance( $comment );
		if ( ! $comment ) {
			return $actions;
		}

		if ( ! $comment->has_attachments() ) {
			return $actions;
		}

		$nonce = wp_create_nonce( "delete-comment-attachment_{$comment->id}" );

		$action_url = sprintf(
			'comment.php?action=%s&c=%d&%s',
			esc_attr( self::ACTION_NAME ),
			intval( $comment->id ),
			esc_attr( '_wpnonce=' . $nonce )
		);

		$attachment_ids_attribute = '';
		if ( ! $this->settings_service->is_delete_attachment_from_media_library() ) {

			$attachment_ids_list      = implode( ',', wp_list_pluck( $comment->get_attachments(), 'id' ) );
			$attachment_ids_attribute = sprintf(
				' data-attachment-ids="%s"',
				esc_attr( $attachment_ids_list )
			);
		}

		$actions[ self::ACTION_NAME ] = sprintf(
			'<a href="%s" class="dco-delete-attachment" data-comment-id="%d" data-nonce="%s"%s>%s</a>',
			esc_url( $action_url ),
			intval( $comment->id ),
			esc_attr( $nonce ),
			$attachment_ids_attribute,
			esc_html( $this->get_action_link_text( $comment ) )
		);

		return $actions;
	}

	public function handle_delete_comment_attachment_action(): never {

		$comment_id = $this->get_request_comment_id();

		$this->check_referer( $comment_id );

		$comment = $this->comment_service->get_comment_instance( $comment_id );

		$this->process_delete_attachment_action_checks( $comment );

		if ( $this->settings_service->is_delete_attachment_from_media_library() ) {
			$comment->delete_attachments_files();
		} else {
			$comment->detach_attachments();
		}

		$comment->save();

		$this->handle_success( $comment );
	}

	public function handle_undo_delete_comment_attachment_action(): never {

		$comment_id          = $this->get_request_comment_id();
		$undo_attachment_ids = $this->get_request_undo_attachment_ids();

		$this->check_referer( $comment_id );

		$comment = $this->comment_service->get_comment_instance( $comment_id );

		$this->process_undo_delete_attachment_action_checks( $comment, $undo_attachment_ids );

		$comment->set_attachment_ids( $undo_attachment_ids );

		$comment->save();

		wp_send_json_success();
	}

	private function load_dependencies(): void {

		if ( function_exists( 'comment_footer_die' ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/comment.php';
	}

	private function get_action_link_text( Comment $comment ): string {

		$singular_text = __( 'Detach Attachment', 'dco-comment-attachment' );
		$plural_text   = __( 'Detach Attachments', 'dco-comment-attachment' );

		if ( $this->settings_service->is_delete_attachment_from_media_library() ) {
			$singular_text = __( 'Delete Attachment', 'dco-comment-attachment' );
			$plural_text   = __( 'Delete Attachments', 'dco-comment-attachment' );
		}

		$text = $plural_text;
		if ( $comment->has_one_attachment() ) {
			$text = $singular_text;
		}

		return $text;
	}

	private function get_request_comment_id(): int {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return intval( $_REQUEST['c'] ?? 0 );
	}

	private function get_request_undo_attachment_ids(): array {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$ids = wp_unslash( $_REQUEST['undo_attachment_ids'] ?? [] );
		if ( ! is_array( $ids ) ) {
			return [];
		}

		return array_map(
			fn( string $id ): int => intval( $id ),
			$ids
		);
	}

	private function check_referer( int $comment_id ): void {

		if ( wp_doing_ajax() ) {
			check_ajax_referer( "delete-comment-attachment_{$comment_id}" );
		} else {
			check_admin_referer( "delete-comment-attachment_{$comment_id}" );
		}
	}

	private function process_delete_attachment_action_checks( ?Comment $comment ): void {

		$this->check_comment_exist( $comment );

		$this->check_edit_comment_capability( $comment );

		if ( ! $comment->has_attachments() ) {

			$this->error(
				'comment_without_attachments',
				__( 'The comment has no attachments to delete or detach.', 'dco-comment-attachment' )
			);
		}
	}

	private function process_undo_delete_attachment_action_checks( ?Comment $comment, array $undo_attachment_ids ): void {

		if ( $this->settings_service->is_delete_attachment_from_media_library() ) {

			$this->error(
				'detaching_comment_attachment_disabled',
				__( 'Detaching comment attachments is disabled in the plugin settings.', 'dco-comment-attachment' )
			);
		}

		$this->check_comment_exist( $comment );

		$this->check_edit_comment_capability( $comment );

		if ( $comment->has_attachments() ) {

			$this->error(
				'comment_with_attachments',
				__( 'The comment already has attachments.', 'dco-comment-attachment' )
			);
		}

		if ( ! $undo_attachment_ids ) {

			$this->error(
				'empty_undo_attachment_ids',
				__( 'The undo attachment ids are empty.', 'dco-comment-attachment' )
			);
		}
	}

	private function check_comment_exist( ?Comment $comment ): void {

		if ( ! $comment ) {

			$this->error(
				'comment_not_exist',
				__( 'Comment does not exist.', 'dco-comment-attachment' )
			);
		}
	}

	private function check_edit_comment_capability( ?Comment $comment ): void {

		if ( ! current_user_can( 'edit_comment', $comment?->id ) ) {

			$this->error(
				'invalid_capability',
				__( 'Sorry, you are not allowed to edit comments on this post.', 'dco-comment-attachment' )
			);
		}
	}

	private function handle_success( Comment $comment ): never {

		if ( wp_doing_ajax() ) {

			wp_send_json_success();
		}

		$redirect_url = admin_url( 'edit-comments.php?p=' . $comment->post_id );
		$redirect_url = add_query_arg( 'attachmentdeleted', 1, $redirect_url );

		wp_safe_redirect( $redirect_url . "#comment-{$comment->id}" );
		exit();
	}

	private function error( string $code, string $message ): never {

		if ( wp_doing_ajax() ) {

			wp_send_json_error(
				new WP_Error(
					$code,
					esc_html( $message ),
				)
			);
		}

		comment_footer_die(
			sprintf(
				'%s <a href="edit-comments.php">%s</a>.',
				esc_html( $message ),
				esc_html__( 'Go back', 'dco-comment-attachment' )
			)
		);
	}
}
