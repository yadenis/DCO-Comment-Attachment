<?php
/**
 * Public functions: DCO_CA class
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
 * Class with public functions.
 *
 * @since 1.0
 */
class DCO_CA {

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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'comment_form_submit_field', array( $this, 'add_attachment_field' ) );
		add_action( 'comment_post', array( $this, 'save_attachment' ) );
		add_action( 'delete_comment', array( $this, 'delete_attachment' ) );
		add_filter( 'comment_text', array( $this, 'display_attachment' ) );
	}

	/**
	 * Enqueues scripts and styles.
	 *
	 * @since 1.0
	 */
	public function enqueue_scripts() {
		// Only when comments is used.
		if ( is_singular() && comments_open() ) {
			wp_enqueue_script( 'dco-comment-attachment', DCO_CA_URL . 'dco-comment-attachment.js', array( 'jquery' ), DCO_CA_VERSION, true );
		}
	}

	/**
	 * Adds a file upload field to the form.
	 *
	 * @since 1.0
	 *
	 * @param string $submit_field HTML markup for the submit field.
	 * @return string $submit_field_with_file_field HTML markup for the file field and the submit field.
	 */
	public function add_attachment_field( $submit_field ) {
		ob_start();
		?>
		<p class="comment-form-attachment">
			<label for="attachment"><?php esc_html_e( 'Attachment', 'dco-comment-attachment' ); ?></label>
			<input id="attachment" name="attachment" type="file" />
		</p>
		<?php
		$file_field = ob_get_clean();

		return $file_field . $submit_field;
	}

	/**
	 * Saves attachment after comment is posted.
	 *
	 * @since 1.0
	 *
	 * @param int $comment_id The comment ID.
	 */
	public function save_attachment( $comment_id ) {
		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		$attachment_id = media_handle_upload( 'attachment', 0 );

		if ( ! is_wp_error( $attachment_id ) ) {
			$this->assign_attachment( $comment_id, $attachment_id );
		}
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
	private function assign_attachment( $comment_id, $attachment_id ) {
		return add_comment_meta( $comment_id, 'attachment_id', $attachment_id );
	}

	/**
	 * Deletes an assigned attachment immediately before a comment is deleted from the database.
	 *
	 * @since 1.0
	 *
	 * @param int $comment_id The comment ID.
	 */
	public function delete_attachment( $comment_id ) {
		$attachment_id = $this->get_attachment_id( $comment_id );
		if ( $attachment_id ) {
			wp_delete_attachment( $attachment_id, true );
		}
	}

	/**
	 * Gets an assigned attachment ID.
	 *
	 * @since 1.0
	 *
	 * @param int $comment_id The comment ID.
	 * @return int|string $attachment_id The assigned attachment ID on success, empty string on failure.
	 */
	private function get_attachment_id( $comment_id ) {
		return get_comment_meta( $comment_id, 'attachment_id', true );
	}

	/**
	 * Displays an assigned attachment.
	 *
	 * @since 1.0
	 *
	 * @param string $comment_content Text of the comment.
	 * @return string $comment_content_with_attachment Text of the comment with an assigned attachment.
	 */
	public function display_attachment( $comment_content ) {
		$attachment_id = $this->get_attachment_id( get_comment_ID() );
		if ( ! $attachment_id ) {
			return $comment_content;
		}

		$url = wp_get_attachment_url( $attachment_id );

		if ( wp_attachment_is_image( $attachment_id ) ) {
			$attachment_content = '<div class="dco-attachment dco-image-attachment">' . wp_get_attachment_image( $attachment_id ) . '</div>';
		} elseif ( wp_attachment_is( 'video', $attachment_id ) ) {
			$attachment_content = '<div class="dco-attachment dco-video-attachment">' . do_shortcode( '[video src="' . esc_url( $url ) . '"]' ) . '</div>';
		} elseif ( wp_attachment_is( 'audio', $attachment_id ) ) {
			$attachment_content = '<div class="dco-attachment dco-audio-attachment">' . do_shortcode( '[audio src="' . esc_url( $url ) . '"]' ) . '</div>';
		} else {
			$title              = get_the_title( $attachment_id );
			$attachment_content = '<div class="dco-attachment dco-misc-attachment"><a href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a></div>';
		}

		return $comment_content . $attachment_content;
	}

}

$GLOBALS['dco_ca'] = new DCO_CA();
