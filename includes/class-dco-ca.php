<?php
/**
 * Public functions: DCO_CA class
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || die;

/**
 * Class with public functions.
 *
 * @since 1.0.0
 *
 * @see DCO_CA_Base
 */
class DCO_CA extends DCO_CA_Base {

	/**
	 * The name of the upload field used in the commenting form.
	 *
	 * @since 1.0.0
	 *
	 * @var string $upload_field_name The name of the upload input.
	 */
	private $upload_field_name = 'attachment';

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'init', array( $this, 'init_hooks' ) );
	}

	/**
	 * Initializes hooks.
	 *
	 * @since 1.0.0
	 */
	public function init_hooks() {
		parent::init_hooks();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'comment_form_submit_field', array( $this, 'add_attachment_field' ) );
		add_filter( 'preprocess_comment', array( $this, 'check_attachment' ) );
		add_action( 'comment_post', array( $this, 'save_attachment' ) );
		add_filter( 'comment_text', array( $this, 'display_attachment' ) );
	}

	/**
	 * Enqueues scripts and styles.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		// Only when comments is used.
		if ( is_singular() && comments_open() ) {
			wp_enqueue_script( 'dco-comment-attachment', DCO_CA_URL . 'assets/dco-comment-attachment.js', array( 'jquery' ), DCO_CA_VERSION, true );
		}
	}

	/**
	 * Adds a file upload field to the form.
	 *
	 * @since 1.0.0
	 *
	 * @param string $submit_field HTML markup for the submit field.
	 * @return string HTML markup for the file field and the submit field.
	 */
	public function add_attachment_field( $submit_field ) {
		$name            = $this->get_upload_field_name();
		$max_upload_size = $this->get_max_upload_size( true );
		ob_start();
		?>
		<p class="comment-form-attachment">
			<label for="attachment">
				<?php
				esc_html_e( 'Attachment', 'dco-comment-attachment' );
				if ( $this->get_option( 'required_attachment' ) ) {
					echo ' <span class="required">*</span>';
				}
				?>
			</label>
			<input id="attachment" name="<?php echo esc_attr( $name ); ?>" type="file" /><br>
			<?php
			/* translators: %s: the maximum allowed upload file size */
			printf( esc_html__( 'The maximum upload file size: %s.', 'dco-comment-attachment' ), esc_html( $max_upload_size ) );
			?>
			<br>
			<?php
			$types     = $this->get_allowed_upload_types();
			$types_str = implode( ', ', $types );
			/* translators: %s: the allowed file types list */
			printf( esc_html__( 'You can upload: %s.', 'dco-comment-attachment' ), esc_html( $types_str ) );
			?>
		</p>
		<?php
		$file_field = ob_get_clean();

		return $file_field . $submit_field;
	}

	/**
	 * Gets allowed upload file types.
	 *
	 * @since 1.0.0
	 *
	 * @return array File types allowed for upload.
	 */
	public function get_allowed_upload_types() {
		$types = array();

		$mimes = array_keys( get_allowed_mime_types() );
		foreach ( $mimes as $mime ) {
			$ext = explode( '|', $mime );
			foreach ( $ext as $ex ) {
				$type = wp_ext2type( $ex );
				if ( $type ) {
					$types[] = $type;
				}
			}
		}

		return array_unique( $types );
	}

	/**
	 * Checks the attachment before posting a comment.
	 *
	 * @since 1.0.0
	 *
	 * @param array $commentdata Comment data.
	 * @return array Comment data on success.
	 */
	public function check_attachment( $commentdata ) {
		$field_name = $this->get_upload_field_name();

		if ( ! isset( $_FILES[ $field_name ] ) ) {
			return $commentdata;
		}

		$attachment = $_FILES[ $field_name ]; // phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$tmp_name   = $attachment['tmp_name'];
		$name       = $attachment['name'];
		$error_code = $attachment['error'];

		$upload_error = $this->get_upload_error( $error_code );
		if ( $upload_error ) {
			$this->display_error( $upload_error );
		}

		// Check that the file has been uploaded.
		if ( ! isset( $tmp_name ) || ! is_uploaded_file( $tmp_name ) ) {
			if ( $this->get_option( 'required_attachment' ) ) {
				$this->display_error( __( 'Attachment is required.', 'dco-comment-attachment' ) );
			} else {
				return $commentdata;
			}
		}

		// We need to do this check, because the maximum allowed upload file size in WordPress can be less than the specified on the server.
		if ( $attachment['size'] > $this->get_max_upload_size() ) {
			$upload_error = $this->get_upload_error( 1 );
			$this->display_error( $upload_error );
		}

		$filetype = wp_check_filetype( $name );
		if ( ! $filetype['ext'] ) {
			$this->display_error( __( "WordPress doesn't allow this type of uploads.", 'dco-comment-attachment' ) );
		}

		return $commentdata;
	}

	/**
	 * Displays the text of the error uploading attachment when sending a comment.
	 *
	 * @since 1.0.0
	 *
	 * @param string $error The text of error uploading attachment.
	 */
	public function display_error( $error ) {
		if ( $error ) {
			$err_title = __( 'ERROR', 'dco-comment-attachment' );
			wp_die( '<p><strong>' . esc_html( $err_title ) . '</strong>: ' . esc_html( $error ) . '</p>', esc_html__( 'Comment Submission Failure', 'dco-comment-attachment' ), array( 'back_link' => true ) );
		}
	}

	/**
	 * Gets the upload error message by the PHP upload error code.
	 *
	 * @since 1.0.0
	 *
	 * @param int $error_code The PHP upload error code.
	 * @return string|false The error message if an error occurred, false if upload success.
	 */
	public function get_upload_error( $error_code ) {
		$upload_errors = array(
			/* translators: %s: the maximum allowed upload file size */
			1 => sprintf( __( 'The file is too large. Allowed attachments up to %s.', 'dco-comment-attachment' ), $this->get_max_upload_size( true ) ),
			2 => __( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', 'dco-comment-attachment' ),
			3 => __( 'The uploaded file was only partially uploaded.', 'dco-comment-attachment' ),
			6 => __( 'Missing a temporary folder.', 'dco-comment-attachment' ),
			7 => __( 'Failed to write file to disk.', 'dco-comment-attachment' ),
			8 => __( 'A PHP extension stopped the file upload.', 'dco-comment-attachment' ),
		);

		if ( isset( $upload_errors[ $error_code ] ) ) {
			return $upload_errors[ $error_code ];
		}

		return false;
	}

	/**
	 * Saves attachment after comment is posted.
	 *
	 * @since 1.0.0
	 *
	 * @param int $comment_id The comment ID.
	 */
	public function save_attachment( $comment_id ) {
		$field_name = $this->get_upload_field_name();

		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		$attachment_id = media_handle_upload( $field_name, 0 );

		if ( ! is_wp_error( $attachment_id ) ) {
			$this->assign_attachment( $comment_id, $attachment_id );
		}
	}

	/**
	 * Displays an assigned attachment.
	 *
	 * @since 1.0.0
	 *
	 * @param string $comment_content Text of the comment.
	 * @return string Text of the comment with an assigned attachment.
	 */
	public function display_attachment( $comment_content ) {
		if ( ! $this->has_attachment() ) {
			return $comment_content;
		}

		$attachment_id      = $this->get_attachment_id();
		$attachment_content = $this->get_attachment_preview( $attachment_id );

		return $comment_content . $attachment_content;
	}

	/**
	 * Gets the name of the upload field used in the commenting form.
	 *
	 * @since 1.0.0
	 *
	 * @return string The name of the upload input.
	 */
	public function get_upload_field_name() {
		return $this->upload_field_name;
	}

}

$GLOBALS['dco_ca'] = new DCO_CA();
