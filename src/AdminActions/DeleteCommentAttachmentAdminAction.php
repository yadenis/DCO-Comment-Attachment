<?php
/**
 * Admin Actions: Delete Comment Attachment
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA\AdminActions;

use DCO_CA\Entities\CommentEntity;
use DCO_CA\Services\CommentService;
use DCO_CA\Services\SettingsService;
use WP_Comment;
use WP_Error;

defined( 'ABSPATH' ) || die;

/**
 * Provides functionality to delete or detach comment attachments
 * in the Comments admin screen.
 *
 * @since 3.0.0
 */
final class DeleteCommentAttachmentAdminAction {

	protected const ACTION_NAME = 'delete_comment_attachment';

	/**
	 * Whether comment attachments should be deleted or detached.
	 *
	 * @since 3.0.0
	 *
	 * @var bool $is_delete_attachment True for deletion, false for detachment.
	 */
	protected bool $is_delete_attachment;

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 *
	 * @param CommentService  $comment_service Service functions for comments.
	 * @param SettingsService $settings_service Service functions for settings.
	 */
	public function __construct(
		private CommentService $comment_service,
		private SettingsService $settings_service
	) {

		$this->load_dependencies();

		$this->is_delete_attachment = $this->settings_service->is_delete_attachment_from_media_library();

		add_filter( 'comment_row_actions', $this->add_delete_comment_attachment_action_link( ... ), 10, 2 );

		add_action( 'admin_action_' . self::ACTION_NAME, $this->handle_delete_comment_attachment_action( ... ) );
		add_action( 'wp_ajax_' . self::ACTION_NAME, $this->handle_delete_comment_attachment_action( ... ) );

		add_action( 'wp_ajax_undo_' . self::ACTION_NAME, $this->handle_undo_delete_comment_attachment_action( ... ) );
	}

	/**
	 * Adds a delete/detach attachment action link in the comment row actions.
	 *
	 * The action link is displayed only for comments with attachments.
	 * The action behavior (delete or detach) is determined by plugin settings.
	 *
	 * @since 3.0.0
	 *
	 * @param array      $actions The actions for the comment row.
	 * @param WP_Comment $wp_comment The comment for which the actions are displayed.
	 *
	 * @return array Modified actions with the delete/detach link.
	 */
	public function add_delete_comment_attachment_action_link( array $actions, WP_Comment $wp_comment ): array {

		$comment = $this->comment_service->get_comment_instance( $wp_comment );
		if ( ! $comment ) {
			return $actions;
		}

		if ( ! $comment->has_attachments() ) {
			return $actions;
		}

		$nonce = wp_create_nonce( "delete-comment-attachment_{$comment->id}" );

		$action_url = add_query_arg(
			[
				'action'   => self::ACTION_NAME,
				'c'        => $comment->id,
				'_wpnonce' => $nonce,
			],
			'comment.php'
		);

		$attachment_ids_attribute = '';
		if ( ! $this->is_delete_attachment ) {

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

	/**
	 * Handles the delete comment attachment action.
	 *
	 * Deletes or detaches comment attachments based on plugin settings.
	 *
	 * @since 3.0.0
	 */
	public function handle_delete_comment_attachment_action(): never {

		$comment_id = $this->get_request_comment_id();

		$this->check_referer( $comment_id );

		$comment = $this->comment_service->get_comment_instance( $comment_id );

		$this->process_delete_attachment_action_checks( $comment );

		if ( $this->is_delete_attachment ) {
			$comment->delete_attachments_files();
		} else {
			$comment->detach_attachments();
		}

		$comment->save();

		$this->handle_success( $comment );
	}

	/**
	 * Handles the undo delete comment attachment action.
	 *
	 * Reattaches previously detached attachments to a comment.
	 *
	 * @since 3.0.0
	 */
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

	/**
	 * Loads dependencies if they are not already available.
	 *
	 * @since 3.0.0
	 */
	private function load_dependencies(): void {

		if ( function_exists( 'comment_footer_die' ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/comment.php';
	}

	/**
	 * Retrieves the appropriate text for the action link.
	 *
	 * @since 3.0.0
	 *
	 * @param CommentEntity $comment The comment entity.
	 *
	 * @return string The action link text.
	 */
	private function get_action_link_text( CommentEntity $comment ): string {

		$singular_text = __( 'Detach Attachment', 'dco-comment-attachment' );
		$plural_text   = __( 'Detach Attachments', 'dco-comment-attachment' );

		if ( $this->is_delete_attachment ) {
			$singular_text = __( 'Delete Attachment', 'dco-comment-attachment' );
			$plural_text   = __( 'Delete Attachments', 'dco-comment-attachment' );
		}

		$text = $plural_text;
		if ( $comment->has_one_attachment() ) {
			$text = $singular_text;
		}

		return $text;
	}

	/**
	 * Retrieves the comment ID from the request.
	 *
	 * @since 3.0.0
	 *
	 * @return int Comment ID from the request, or 0 if not available.
	 */
	private function get_request_comment_id(): int {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return intval( $_REQUEST['c'] ?? 0 );
	}

	/**
	 * Retrieves attachment IDs for undo action from the request.
	 *
	 * @since 3.0.0
	 *
	 * @return array Attachment IDs from the request,
	 *               or empty array if not available.
	 */
	private function get_request_undo_attachment_ids(): array {

		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing

		$field_name = 'undo_attachment_ids';

		if ( ! isset( $_POST[ $field_name ] ) || ! is_array( $_POST[ $field_name ] ) ) {
			return [];
		}

		return array_map(
			intval( ... ),
			$_POST[ $field_name ]
		);

		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Validates the nonce for the current action.
	 *
	 * @since 3.0.0
	 *
	 * @param int $comment_id The comment ID associated with the request.
	 */
	private function check_referer( int $comment_id ): void {

		$action = "delete-comment-attachment_{$comment_id}";

		if ( wp_doing_ajax() ) {
			check_ajax_referer( $action );
		} else {
			check_admin_referer( $action );
		}
	}

	/**
	 * Checks prerequisites for a delete comment attachment action.
	 *
	 * @since 3.0.0
	 *
	 * @param CommentEntity|null $comment The comment entity to check.
	 */
	private function process_delete_attachment_action_checks( ?CommentEntity $comment ): void {

		$this->check_comment_exist( $comment );

		$this->check_edit_comment_capability( $comment );

		if ( ! $comment->has_attachments() ) {

			$this->error(
				'comment_without_attachments',
				__( 'The comment has no attachments to delete or detach.', 'dco-comment-attachment' )
			);
		}
	}

	/**
	 * Checks prerequisites for undoing a delete comment attachment action.
	 *
	 * @since 3.0.0
	 *
	 * @param CommentEntity|null $comment The comment entity to check.
	 * @param array              $undo_attachment_ids The attachment IDs to reattach.
	 */
	private function process_undo_delete_attachment_action_checks( ?CommentEntity $comment, array $undo_attachment_ids ): void {

		if ( $this->is_delete_attachment ) {

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

	/**
	 * Ensures the comment exists.
	 *
	 * @since 3.0.0
	 *
	 * @param CommentEntity|null $comment The comment entity to check.
	 */
	private function check_comment_exist( ?CommentEntity $comment ): void {

		if ( ! $comment ) {

			$this->error(
				'comment_not_exist',
				__( 'Comment does not exist.', 'dco-comment-attachment' )
			);
		}
	}

	/**
	 * Ensures the user has permission to edit the comment.
	 *
	 * @since 3.0.0
	 *
	 * @param CommentEntity|null $comment The comment entity to check.
	 */
	private function check_edit_comment_capability( ?CommentEntity $comment ): void {

		if ( ! current_user_can( 'edit_comment', $comment?->id ) ) {

			$this->error(
				'invalid_capability',
				__( 'Sorry, you are not allowed to edit comments on this post.', 'dco-comment-attachment' )
			);
		}
	}

	/**
	 * Redirects or sends a success response after successful action.
	 *
	 * If the request is an AJAX request, a JSON success response is sent.
	 * Otherwise, redirects to the Comments admin page.
	 *
	 * @since 3.0.0
	 *
	 * @param CommentEntity $comment The comment entity.
	 */
	private function handle_success( CommentEntity $comment ): never {

		if ( wp_doing_ajax() ) {

			wp_send_json_success();
		}

		$redirect_url = admin_url( 'edit-comments.php?p=' . $comment->post_id );
		$redirect_url = add_query_arg( 'attachmentdeleted', 1, $redirect_url );

		wp_safe_redirect( $redirect_url . "#comment-{$comment->id}" );
		exit();
	}

	/**
	 * Sends an error response and terminates the execution.
	 *
	 * @since 3.0.0
	 *
	 * @param string $code    The error code.
	 * @param string $message The error message.
	 */
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
