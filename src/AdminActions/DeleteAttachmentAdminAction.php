<?php

declare(strict_types=1);

namespace DCO_CA\AdminActions;

use DCO_CA\Comment;
use DCO_CA\Services\CommentService;
use DCO_CA\Services\SettingsService;
use WP_Comment;
use WP_Error;

defined( 'ABSPATH' ) || die;

final class DeleteAttachmentAdminAction {

	protected const ACTION_NAME = 'deletecommentattachment';

	public function __construct(
		private CommentService $comment_service,
		private SettingsService $settings_service
	) {

		$this->load_dependencies();

		add_filter( 'comment_row_actions', $this->add_delete_attachment_action_link( ... ), 10, 2 );

		add_action( 'admin_action_' . self::ACTION_NAME, $this->handle_delete_attachment_action( ... ) );
		add_action( 'wp_ajax_delete_attachment', $this->handle_delete_attachment_action( ... ) );
	}

	public function add_delete_attachment_action_link( array $actions, WP_Comment $comment ): array {

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

		$actions[ self::ACTION_NAME ] = sprintf(
			'<a href="%s" class="dco-delete-attachment" data-id="%d" data-nonce="%s">%s</a>',
			esc_url( $action_url ),
			intval( $comment->id ),
			esc_attr( $nonce ),
			esc_html( $this->get_action_link_text( $comment ) )
		);

		return $actions;
	}

	public function handle_delete_attachment_action(): void {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$comment_id = intval( $_REQUEST['c'] ?? 0 );

		$this->check_referer( $comment_id );

		$comment = $this->comment_service->get_comment_instance( $comment_id );

		$this->process_delete_attachment_action_checks( $comment );

		if ( $this->settings_service->is_delete_attachment_from_media_library() ) {
			$comment->delete_attachments_with_media_files();
		} else {
			$comment->remove_attachments();
		}

		$comment->save();

		$this->handle_success( $comment );
	}

	private function load_dependencies(): void {

		if ( function_exists( 'comment_footer_die' ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/comment.php';
	}

	private function get_action_link_text( Comment $comment ): string {

		$singular_text = __( 'Remove Attachment', 'dco-comment-attachment' );
		$plural_text   = __( 'Remove Attachments', 'dco-comment-attachment' );

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

	private function check_referer( int $comment_id ): void {

		if ( wp_doing_ajax() ) {
			check_ajax_referer( "delete-comment-attachment_{$comment_id}" );
		} else {
			check_admin_referer( "delete-comment-attachment_{$comment_id}" );
		}
	}

	private function process_delete_attachment_action_checks( ?Comment $comment ): void {

		if ( ! $comment ) {

			$this->error(
				'invalid_comment_id',
				__( 'Invalid comment ID.', 'dco-comment-attachment' )
			);
		}

		if ( ! current_user_can( 'edit_comment', $comment->id ) ) {

			$this->error(
				'invalid_capability',
				__( 'Sorry, you are not allowed to edit comments on this post.', 'dco-comment-attachment' )
			);
		}

		if ( ! $comment->has_attachments() ) {

			$this->error(
				'comment_without_attachments',
				__( 'The comment has no attachments.', 'dco-comment-attachment' )
			);
		}
	}

	private function handle_success( Comment $comment ): never {

		if ( wp_doing_ajax() ) {

			wp_send_json_success();
		}

		$redir = admin_url( 'edit-comments.php?p=' . $comment->post_id );
		$redir = add_query_arg( 'attachmentdeleted', 1, $redir );

		wp_safe_redirect( $redir . "#comment-{$comment->id}" );
		exit();
	}

	private function error( string $code, string $message ): void {

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
