<?php
/**
 * Plugin settings: DCO_CA_Settings class
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
 * Class with plugin settings.
 *
 * @since 1.0.0
 *
 * @see DCO_CA_Base
 */
class DCO_CA_Settings extends DCO_CA_Base {

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

		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'create_menu' ) );
	}

	/**
	 * Registers plugin settings.
	 *
	 * @since 1.0.0
	 */
	public function register_settings() {
		register_setting( parent::ID, parent::ID );

		$sections = $this->get_sections();
		foreach ( $sections as $key => $title ) {
			add_settings_section(
				$key,
				$title,
				array( $this, 'section_render' ),
				parent::ID
			);
		}

		$fields = $this->get_fields();
		foreach ( $fields as $key => $field ) {
			$args = array(
				'label_for' => $key,
				'name'      => $key,
				'desc'      => $field['desc'],
				'type'      => $field['type'],
			);

			if ( 'dropdown' === $field['type'] ) {
				$args['choices'] = $field['choices'];
			}

			if ( isset( $field['atts'] ) ) {
				$args['atts'] = $field['atts'];
			}

			add_settings_field(
				$key,
				$field['label'],
				array( $this, 'field_render' ),
				parent::ID,
				$field['section'],
				$args
			);
		}
	}

	/**
	 * Adds an options page to the settings section in the admin menu.
	 *
	 * @since 1.0.0
	 */
	public function create_menu() {
		add_options_page( __( 'DCO Comment Attachment Settings', 'dco-comment-attachment' ), esc_html__( 'DCO Comment Attachment', 'dco-comment-attachment' ), 'manage_options', 'dco-comment-attachment', array( $this, 'render' ) );
	}

	/**
	 * Outputs the plugin settings page markup.
	 *
	 * @since 1.0.0
	 */
	public function render() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'DCO Comment Attachment Settings', 'dco-comment-attachment' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( parent::ID );
				do_settings_sections( parent::ID );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Gets plugin settings sections.
	 *
	 * @since 1.0.0
	 *
	 * @return array Settings sections.
	 */
	public function get_sections() {
		$sections = array(
			'general' => esc_html__( 'General', 'dco-comment-attachment' ),
		);

		return $sections;
	}

	/**
	 * Gets plugin settings fields.
	 *
	 * @since 1.0.0
	 *
	 * @return array Settings fields.
	 */
	public function get_fields() {
		$fields = array(
			'attachment_size' => array(
				'label'   => esc_html__( 'Attachment size', 'dco-comment-attachment' ),
				'desc'    => __( 'The size of the thumbnail for attached images.', 'dco-comment-attachment' ),
				'section' => 'general',
				'type'    => 'dropdown',
				'choices' => $this->get_thumbnail_sizes(),
			),
		);

		return $fields;
	}

	/**
	 * Outputs the settings section content.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Section arguments.
	 */
	public function section_render( $args ) {
	}

	/**
	 * Outputs the settings field markup.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Field arguments.
	 */
	public function field_render( $args ) {
		$id           = parent::ID;
		$name         = $args['name'];
		$setting_val  = $this->get_option( $name );
		$control_name = "{$id}[$name]";

		switch ( $args['type'] ) {
			case 'dropdown':
				$choices = $args['choices'];
				echo '<select name="' . esc_attr( $control_name ) . '" class="dco-field">';
				foreach ( $choices as $val => $choice ) {
					$text = $val;

					if ( 'attachment_size' === $args['name'] ) {
						$width  = $choice['width'];
						$height = $choice['height'];
						$size   = __( 'Size', 'dco-comment-attachment' ) . ": {$width}x{$height}";

						$crop = __( 'No', 'dco-comment-attachment' );
						if ( $choice['crop'] ) {
							$crop = __( 'Yes', 'dco-comment-attachment' );
						}
						$crop = __( 'Crop', 'dco-comment-attachment' ) . ": $crop";

						$title = ucfirst( $val );
						$text  = "$title, $size, $crop";
					}

					echo '<option value="' . esc_attr( $val ) . '" ' . selected( $val, $setting_val ) . '>' . esc_html( $text ) . '</option>';
				}
				$val  = 'full';
				$text = __( 'Full (original image)', 'dco-comment-attachment' );
				echo '<option value="' . esc_attr( $val ) . '" ' . selected( $val, $setting_val ) . '>' . esc_html( $text ) . '</option>';
				echo '</select>';
				break;
		}
		echo '<div class="dco-field-desc">' . esc_html( $args['desc'] ) . '</div>';
	}

	/**
	 * Gets thumbnail sizes registered on the site.
	 *
	 * @since 1.0.0
	 *
	 * @return array Thumbnail sizes.
	 */
	public function get_thumbnail_sizes() {
		$standard_sizes = array(
			'thumbnail',
			'medium',
			'medium_large',
			'large',
		);

		foreach ( $standard_sizes as $size ) {
			$sizes[ $size ] = array(
				'width'  => get_option( "{$size}_size_w" ),
				'height' => get_option( "{$size}_size_h" ),
				'crop'   => get_option( "{$size}_crop" ),
			);
		}

		return array_merge( $sizes, wp_get_additional_image_sizes() );
	}

}

$GLOBALS['dco_ca_settings'] = new DCO_CA_Settings();
