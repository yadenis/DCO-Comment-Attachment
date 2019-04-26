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

}

$GLOBALS['dco_ca'] = new DCO_CA();
