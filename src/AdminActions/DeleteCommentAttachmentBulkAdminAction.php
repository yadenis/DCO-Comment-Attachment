<?php

declare(strict_types=1);

namespace DCO_CA\AdminActions;

defined( 'ABSPATH' ) || die;

final class DeleteCommentAttachmentBulkAdminAction {

	protected const ACTION_NAME = 'delete_comment_attachment_bulk';

	public function __construct() {

		add_filter( 'bulk_actions-edit-comments', $this->add_delete_comment_attachment_bulk_action( ... ) );

		add_action( 'admin_action_' . self::ACTION_NAME, $this->handle_delete_comment_attachment_bulk_action( ... ) );
	}

	public function add_delete_comment_attachment_bulk_action( array $actions ): array {

		$actions[ self::ACTION_NAME ] = __( 'Delete Attachments', 'dco-comment-attachment' );

		return $actions;
	}

	public function handle_delete_comment_attachment_bulk_action() {

		return;

		check_admin_referer( 'bulk-comments' );

		if ( isset( $_REQUEST['delete_comments'] ) && is_array( $_REQUEST['delete_comments'] ) ) {
			$comment_ids = array_map( 'absint', $_REQUEST['delete_comments'] );
		} else {
			return;
		}

		$redirect_to = remove_query_arg( array( 'trashed', 'untrashed', 'deleted', 'spammed', 'unspammed', 'approved', 'unapproved', 'ids', self::ACTION_NAME ), wp_get_referer() );

		wp_defer_comment_counting( true );

		$count = 0;
		foreach ( $comment_ids as $comment_id ) {
			if ( ! current_user_can( 'edit_comment', $comment_id ) ) {
				continue;
			}

			$comment = get_comment( $comment_id );
			if ( ! $comment ) {
				continue;
			}

			$delete = $this->get_option( 'delete_attachment_action' );
			if ( $this->delete_attachment( $comment_id, $delete ) ) {
				++$count;
			}
		}

		wp_defer_comment_counting( false );

		$redirect_to = add_query_arg( 'deletedattachment', $count, $redirect_to );

		// @see DCO_CA_Admin::show_bulk_action_message() for details.
		$redirect_to = add_query_arg( 'approved', $count, $redirect_to );

		wp_safe_redirect( $redirect_to );
		exit;
	}
}
