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
	 * Assigns an attachment for the comment.
	 *
	 * @since 1.0
	 *
	 * @param int $comment_id The comment ID.
	 * @param int $attachment_id The attachment ID.
	 * @return int|bool Meta ID on success, false on failure.
	 */
	public function assign_attachment( $comment_id, $attachment_id ) {
		$meta_key = $this->get_attachment_meta_key();
		return update_comment_meta( $comment_id, $meta_key, $attachment_id );
	}

	/**
	 * Generates HTML markup for the attachment based on it type.
	 *
	 * @since 1.0
	 *
	 * @param int $attachment_id The attachment ID.
	 * @return string HTML markup for the attachment.
	 */
	public function get_attachment_preview( $attachment_id ) {
		$url = wp_get_attachment_url( $attachment_id );

		if ( wp_attachment_is_image( $attachment_id ) ) {
			$attachment_content = '<div class="dco-attachment dco-image-attachment">' . wp_get_attachment_image( $attachment_id, 'medium' ) . '</div>';
		} elseif ( wp_attachment_is( 'video', $attachment_id ) ) {
			$attachment_content = '<div class="dco-attachment dco-video-attachment">' . do_shortcode( '[video src="' . esc_url( $url ) . '"]' ) . '</div>';
		} elseif ( wp_attachment_is( 'audio', $attachment_id ) ) {
			$attachment_content = '<div class="dco-attachment dco-audio-attachment">' . do_shortcode( '[audio src="' . esc_url( $url ) . '"]' ) . '</div>';
		} else {
			$title              = get_the_title( $attachment_id );
			$attachment_content = '<div class="dco-attachment dco-misc-attachment"><a href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a></div>';
		}

		return $attachment_content;
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
