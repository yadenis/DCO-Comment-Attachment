<?php
/**
 * Basic functions: DCO_CA_Base class
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
 * Class with basic functions.
 *
 * @since 1.0
 */
class DCO_CA_Base {

	/**
	 * The meta key of the attachment ID for comment meta.
	 *
	 * @since 1.0
	 *
	 * @var string $attachment_meta_key The attachment ID meta key.
	 */
	private $attachment_meta_key = 'attachment_id';

	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init_hooks' ) );
	}

	/**
	 * Initializes hooks.
	 *
	 * @since 1.0
	 */
	public function init_hooks() {
		add_action( 'delete_comment', array( $this, 'delete_attachment' ) );
	}

	/**
	 * Deletes an assigned attachment immediately before a comment is deleted from the database.
	 *
	 * @since 1.0
	 *
	 * @param int $comment_id The comment ID.
	 * @return bool true on success, false on failure.
	 */
	public function delete_attachment( $comment_id ) {
		if ( ! $this->has_attachment( $comment_id ) ) {
			return false;
		}

		$attachment_id = $this->get_attachment_id( $comment_id );

		if ( ! wp_delete_attachment( $attachment_id, true ) ) {
			return false;
		}

		$meta_key = $this->get_attachment_meta_key();
		if ( ! delete_comment_meta( $comment_id, $meta_key ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Gets an assigned attachment ID.
	 *
	 * @since 1.0
	 *
	 * @param int $comment_id Optional. The comment ID.
	 * @return int|string The assigned attachment ID on success, empty string on failure.
	 */
	public function get_attachment_id( $comment_id = 0 ) {
		$meta_key = $this->get_attachment_meta_key();

		if ( ! $comment_id ) {
			$comment_id = get_comment_ID();
		}

		return get_comment_meta( $comment_id, $meta_key, true );
	}

	/**
	 * Checks if a comment has an attachment.
	 *
	 * @since 1.0
	 *
	 * @param int $comment_id Optional. The comment ID.
	 * @return bool Whether the comment has an attachment.
	 */
	public function has_attachment( $comment_id = 0 ) {
		if ( ! $comment_id ) {
			$comment_id = get_comment_ID();
		}

		if ( $this->get_attachment_id( $comment_id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Gets the meta key of the attachment ID for comment meta.
	 *
	 * @since 1.0
	 *
	 * return string The attachment ID meta key.
	 */
	public function get_attachment_meta_key() {
		return $this->attachment_meta_key;
	}

}

$GLOBALS['dco_ca_base'] = new DCO_CA_Base();
