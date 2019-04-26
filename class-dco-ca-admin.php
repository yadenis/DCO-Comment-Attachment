<?php
/**
 * Admin functions: DCO_CA_Admin class
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 1.0
 */

defined( 'ABSPATH' ) || die;

/**
 * Class with admin functions.
 *
 * @since 1.0
 *
 * @see DCO_CA_Base
 */
class DCO_CA_Admin extends DCO_CA_Base {

	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'init', array( $this, 'init_hooks' ) );
	}

	/**
	 * Initializes hooks.
	 *
	 * @since 1.0
	 */
	public function init_hooks() {
		parent::init_hooks();

		add_filter( 'comment_row_actions', array( $this, 'add_comment_action_links' ), 10, 2 );
		add_action( 'admin_action_deleteattachment', array( $this, 'delete_attachment_action' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_delete_attachment', array( $this, 'delete_attachment_ajax' ) );
	}

	/**
	 * Adds additional comment action links.
	 *
	 * @since 1.0
	 *
	 * @param array      $actions An array of comment actions.
	 * @param WP_Comment $comment The comment object.
	 * @return array An array with standard comment actions and attachment actions if attachment exists.
	 */
	public function add_comment_action_links( $actions, $comment ) {
		if ( $this->has_attachment() ) {
			$comment_id = $comment->comment_ID;
			$nonce      = wp_create_nonce( "delete-comment-attachment_$comment_id" );

			$del_attach_nonce = esc_html( '_wpnonce=' . $nonce );
			$url              = esc_url( "comment.php?c=$comment_id&action=deleteattachment&$del_attach_nonce" );

			$title                       = esc_html__( 'Delete Attachment', 'dco-comment-attachment' );
			$actions['deleteattachment'] = "<a href='$url' class='dco-del-attachment' data-id='$comment_id' data-nonce='$nonce'>$title</a>";
		}

		return $actions;
	}

	/**
	 * Handles a request to delete an attachment on the comments page.
	 *
	 * @since 1.0
	 */
	public function delete_attachment_action() {
		$comment_id = isset( $_GET['c'] ) ? (int) $_GET['c'] : 0;

		check_admin_referer( 'delete-comment-attachment_' . $comment_id );

		if ( ! function_exists( 'comment_footer_die' ) ) {
			require_once ABSPATH . 'wp-admin/includes/comment.php';
		}

		$comment = get_comment( $comment_id );

		// Check the comment exists.
		if ( ! $comment ) {
			comment_footer_die( esc_html__( 'Invalid comment ID.', 'dco-comment-attachment' ) . sprintf( ' <a href="%s">' . esc_html__( 'Go back', 'dco-comment-attachment' ) . '</a>.', 'edit-comments.php' ) );
		}

		if ( ! current_user_can( 'edit_comment', $comment_id ) ) {
			comment_footer_die( esc_html__( 'Sorry, you are not allowed to edit comments on this post.', 'dco-comment-attachment' ) );
		}

		if ( ! $this->has_attachment( $comment_id ) ) {
			comment_footer_die( esc_html__( 'The comment has no attachment.', 'dco-comment-attachment' ) . sprintf( ' <a href="%s">' . esc_html__( 'Go back', 'dco-comment-attachment' ) . '</a>.', 'edit-comments.php' ) );
		}

		if ( ! $this->delete_attachment( $comment_id ) ) {
			comment_footer_die( esc_html__( 'An error occurred while deleting the attachment.', 'dco-comment-attachment' ) );
		}

		$redir = admin_url( 'edit-comments.php?p=' . (int) $comment->comment_post_ID );
		$redir = add_query_arg( array( 'attachmentdeleted' => 1 ), $redir );

		wp_safe_redirect( $redir );
		exit();
	}

	/**
	 * Enqueues scripts and styles.
	 *
	 * @since 1.0
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_scripts( $hook_suffix ) {
		// Only on comments page.
		if ( 'edit-comments.php' === $hook_suffix ) {
			wp_enqueue_script( 'dco-comment-attachment-admin', DCO_CA_URL . 'dco-comment-attachment-admin.js', array( 'jquery' ), DCO_CA_VERSION, true );
		}
	}

	/**
	 * Handles an ajax request to delete an attachment on the comments page.
	 *
	 * @since 1.0
	 */
	public function delete_attachment_ajax() {
		$comment_id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;

		check_ajax_referer( "delete-comment-attachment_$comment_id" );

		$comment = get_comment( $comment_id );

		// Check the comment exists.
		if ( ! $comment ) {
			/* translators: %d: The comment ID */
			wp_send_json_error( new WP_Error( 'invalid_comment', sprintf( esc_html__( 'Comment %d does not exist', 'dco-comment-attachment' ), $comment_id ) ) );
		}

		if ( ! current_user_can( 'edit_comment', $comment_id ) ) {
			wp_send_json_error( new WP_Error( 'invalid_capability', esc_html__( 'Sorry, you are not allowed to edit comments on this post.', 'dco-comment-attachment' ) ) );
		}

		if ( ! $this->has_attachment( $comment_id ) ) {
			wp_send_json_error( new WP_Error( 'attachment_not_exists', esc_html__( 'The comment has no attachment.', 'dco-comment-attachment' ) ) );
		}

		if ( ! $this->delete_attachment( $comment_id ) ) {
			wp_send_json_error( new WP_Error( 'deleting_error', esc_html__( 'An error occurred while deleting the attachment.', 'dco-comment-attachment' ) ) );
		}

		wp_send_json_success();
	}

}

$GLOBALS['dco_ca_admin'] = new DCO_CA_Admin();
