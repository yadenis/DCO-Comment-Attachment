<?php
/**
 * Admin Actions: Delete Comment Attachment Bulk
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

defined( 'ABSPATH' ) || die;

/**
 * Provides functionality to bulk delete or detach comment attachments
 * on the Comments admin screen.
 *
 * @since 3.0.0
 */
final class DeleteCommentAttachmentBulkAdminAction {

	protected const ACTION_NAME = 'delete_comment_attachment_bulk';

	protected const DELETE_COMMENT_IDS_FIELD_NAME = 'delete_comments';

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
	 * @param CommentService  $comment_service   Service functions for comments.
	 * @param SettingsService $settings_service  Service functions for settings.
	 * @param RequestHelper   $request_helper    Helper functions for request.
	 */
	public function __construct(
		private CommentService $comment_service,
		private SettingsService $settings_service,
		private RequestHelper $request_helper,
	) {

		$this->is_delete_attachment = $this->settings_service->is_delete_attachment_from_media_library();

		add_filter( 'bulk_actions-edit-comments', $this->add_bulk_action( ... ) );

		add_action( 'admin_action_' . self::ACTION_NAME, $this->handle_bulk_action( ... ) );

		add_filter( 'ngettext', $this->show_bulk_action_success_message( ... ), 10, 5 );

		add_filter( 'removable_query_args', $this->add_bulk_action_name_to_removable_query_args( ... ) );
	}

	/**
	 * Adds a delete/detach attachment bulk action to the comments bulk actions dropdown.
	 *
	 * Deletes or detaches comment attachments based on plugin settings.
	 *
	 * @since 3.0.0
	 *
	 * @param array $actions Bulk actions.
	 *
	 * @return array Modified bulk actions with the delete/detach action.
	 */
	public function add_bulk_action( array $actions ): array {

		$text = __( 'Detach Attachments', 'dco-comment-attachment' );
		if ( $this->is_delete_attachment ) {
			$text = __( 'Delete Attachments', 'dco-comment-attachment' );
		}

		$actions[ self::ACTION_NAME ] = $text;

		return $actions;
	}

	/**
	 * Handles the delete comment attachment bulk action.
	 *
	 * Deletes or detaches comment attachments based on plugin settings.
	 *
	 * @since 3.0.0
	 */
	public function handle_bulk_action(): never {

		check_admin_referer( 'bulk-comments' );

		$comment_ids = $this->get_request_comment_ids();

		$count = 0;
		foreach ( $comment_ids as $comment_id ) {

			if ( ! $this->process_bulk_action_checks( $comment_id ) ) {
				continue;
			}

			if ( $this->is_delete_attachment ) {
				$this->comment_service->delete_comment_attachments( $comment_id );
			} else {
				$this->comment_service->detach_comment_attachments( $comment_id );
			}

			++$count;
		}

		$this->handle_success( $count );
	}

	/**
	 * Show the success message for the delete comment attachment bulk action.
	 *
	 * There is no hook in WordPress to add updated message for custom comments bulk action.
	 * So we override the approval message if attachment deletion was triggered.
	 *
	 * @since 3.0.0
	 *
	 * @param string $translation The success message.
	 * @param string $single      Singular form of the message.
	 * @param string $plural      Plural form of the message.
	 * @param int    $number      The number of comments processed.
	 * @param string $domain      Text domain for translation.
	 *
	 * @return string The modified success message, if applicable.
	 */
	public function show_bulk_action_success_message(
		string $translation,
		string $single,
		string $plural,
		int $number,
		string $domain
	): string {

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

	/**
	 * Adds the delete comment attachment bulk action name to the list of removable query arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param array $removable_query_args Removable query arguments.
	 *
	 * @return array Modified removable query arguments with
	 *               the delete comment attachment bulk action name.
	 */
	public function add_bulk_action_name_to_removable_query_args( array $removable_query_args ): array {

		$removable_query_args[] = self::ACTION_NAME;

		return $removable_query_args;
	}

	/**
	 * Retrieves comment IDs from the request.
	 *
	 * @since 3.0.0
	 *
	 * @return array Comment IDs from the request,
	 *               or null if not available.
	 */
	private function get_request_comment_ids(): ?array {

		$delete_comments_ids = $this->request_helper->get_array_field( self::DELETE_COMMENT_IDS_FIELD_NAME );
		if ( ! $delete_comments_ids ) {
			return null;
		}

		return array_map(
			intval( ... ),
			$delete_comments_ids
		);
	}

	/**
	 * Checks prerequisites for a delete comment attachment bulk action.
	 *
	 * @since 3.0.0
	 *
	 * @param int $comment_id The comment id to check.
	 *
	 * @return bool True if the comment id is valid for processing, false otherwise.
	 */
	private function process_bulk_action_checks( int $comment_id ): bool {

		if ( ! $this->comment_service->is_comment_exists( $comment_id ) ) {
			return false;
		}

		if ( ! current_user_can( 'edit_comment', $comment_id ) ) {
			return false;
		}

		if ( ! $this->comment_service->is_comment_has_attachments( $comment_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Redirects to the Comments admin page after successful action
	 *
	 * @since 3.0.0
	 *
	 * @param int $count_processed_comments Number of comments successfully processed.
	 */
	private function handle_success( int $count_processed_comments ): never {

		$redirect_to = add_query_arg( self::ACTION_NAME, $count_processed_comments, wp_get_referer() );

		// @see DeleteCommentAttachmentBulkAdminAction::show_bulk_action_success_message() for details.
		$redirect_to = add_query_arg( 'approved', $count_processed_comments, $redirect_to );

		wp_safe_redirect( $redirect_to );
		exit;
	}
}
