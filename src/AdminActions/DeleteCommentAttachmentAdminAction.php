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
use DCO_CA\Helpers\RequestHelper;
use DCO_CA\Services\CommentService;
use DCO_CA\Services\SettingsService;
use WP_Comment;
use WP_Error;

defined( 'ABSPATH' ) || die;

/**
 * Provides functionality to delete or detach comment attachments
 * on the Comments admin screen.
 *
 * @since 3.0.0
 */
final class DeleteCommentAttachmentAdminAction {

	protected const ACTION_NAME = 'delete_comment_attachment';

	protected const COMMENT_ID_FIELD_NAME          = 'c';
	protected const UNDO_ATTACHMENT_IDS_FIELD_NAME = 'undo_attachment_ids';

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
	 * @param RequestHelper $request_helper Helper functions for request.
	 */
	public function __construct(
		private CommentService $comment_service,
		private SettingsService $settings_service,
		private RequestHelper $request_helper,
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
	 * Deletes or detaches comment attachments based on plugin settings.
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

		$actions[ self::ACTION_NAME ] = sprintf(
			'<a href="%s" class="dco-delete-attachment" data-comment-id="%d" data-nonce="%s"%s>%s</a>',
			esc_url( $this->get_action_link_url( $comment, $nonce ) ),
			intval( $comment->id ),
			esc_attr( $nonce ),
			$this->get_action_link_attachment_ids_attribute( $comment ),
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

		$this->check_edit_comment_capability( $comment_id );

		if ( $this->is_delete_attachment ) {
			$this->comment_service->delete_comment_attachments( $comment_id );
		} else {
			$this->comment_service->detach_comment_attachments( $comment_id );
		}

		$this->handle_success( $comment_id );
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

		$this->process_undo_delete_attachment_action_checks( $comment_id, $undo_attachment_ids );

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

	private function get_action_link_url( CommentEntity $comment, string $nonce ): string {

		return add_query_arg(
			[
				'action'   => self::ACTION_NAME,
				'c'        => $comment->id,
				'_wpnonce' => $nonce,
			],
			'comment.php'
		);
	}

	private function get_action_link_attachment_ids_attribute( CommentEntity $comment ): string {

		if ( $this->is_delete_attachment ) {
			return '';
		}

		$attachment_ids_list = implode( ',', wp_list_pluck( $comment->get_attachments(), 'id' ) );

		return sprintf(
			' data-attachment-ids="%s"',
			esc_attr( $attachment_ids_list )
		);
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
	 * @return int|null Comment ID from the request, or null if not available.
	 */
	private function get_request_comment_id(): ?int {

		return $this->request_helper->get_int_field( self::COMMENT_ID_FIELD_NAME );
	}

	/**
	 * Retrieves attachment IDs for undo action from the request.
	 *
	 * @since 3.0.0
	 *
	 * @return array Attachment IDs from the request, or null if not available.
	 */
	private function get_request_undo_attachment_ids(): ?array {

		$undo_attachment_ids = $this->request_helper->get_array_field( self::UNDO_ATTACHMENT_IDS_FIELD_NAME );
		if ( ! $undo_attachment_ids ) {
			return null;
		}

		return array_map(
			intval( ... ),
			$undo_attachment_ids
		);
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
	 * Checks prerequisites for undoing a delete comment attachment action.
	 *
	 * @since 3.0.0
	 *
	 * @param CommentEntity|null $comment The comment entity to check.
	 * @param array              $undo_attachment_ids The attachment IDs to reattach.
	 */
	private function process_undo_delete_attachment_action_checks( int $comment_id, array $undo_attachment_ids ): void {

		if ( $this->is_delete_attachment ) {

			$this->error(
				'detaching_comment_attachment_disabled',
				__( 'Detaching comment attachments is disabled in the plugin settings.', 'dco-comment-attachment' )
			);
		}

		if ( ! $undo_attachment_ids ) {

			$this->error(
				'empty_undo_attachment_ids',
				__( 'The undo attachment ids are empty.', 'dco-comment-attachment' )
			);
		}

		$this->check_edit_comment_capability( $comment_id );

		if ( $comment->has_attachments() ) {

			$this->error(
				'comment_with_attachments',
				__( 'The comment already has attachments.', 'dco-comment-attachment' )
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
	private function check_edit_comment_capability( int $comment_id ): void {

		if ( ! current_user_can( 'edit_comment', $comment_id ) ) {

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
	private function handle_success( int $comment_id ): never {

		if ( wp_doing_ajax() ) {

			wp_send_json_success();
		}

		$post_id = $this->comment_service->get_comment_post_id( $comment_id );

		$redirect_url = admin_url( "edit-comments.php?p={$post_id}" );
		$redirect_url = add_query_arg( 'attachmentdeleted', 1, $redirect_url );

		wp_safe_redirect( $redirect_url . "#comment-{$comment_id}" );
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
