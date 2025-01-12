<?php
/**
 * SettingsControls: Image Size
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA\SettingsControls;

use DCO_CA\Interfaces\SettingsControl;

defined( 'ABSPATH' ) || die;

/**
 * Renders the image size settings control in the admin panel.
 *
 * @since 3.0.0
 */
final class ImageSizeSettingsControl implements SettingsControl {

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name          The setting name.
	 * @param string $id            The setting id.
	 * @param string $selected_size The selected image size.
	 */
	public function __construct(
		private string $name,
		private string $id,
		private string $selected_size,
	) {
	}

	/**
	 * Renders the image size settings control.
	 *
	 * @since 3.0.0
	 */
	public function render_control(): void {

		printf(
			'<select name="%s" id="%s">',
			esc_attr( $this->name ),
			esc_attr( $this->id ),
		);

		foreach ( $this->get_image_sizes() as $name => $title ) {

			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $name ),
				selected(
					selected: $this->selected_size,
					current: $name,
					display: false
				),
				esc_html( $title ),
			);
		}

		echo '</select>';
	}

	/**
	 * Retrieves the available image sizes, including 'full' size.
	 *
	 * @since 3.0.0
	 *
	 * @return array<string, string> The available image sizes with titles.
	 */
	private function get_image_sizes(): array {

		$sizes = wp_get_registered_image_subsizes();

		array_walk(
			$sizes,
			// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
			fn( array &$attributes, string $name ): string => $attributes = $this->build_image_size_title( $name, $attributes )
		);

		$sizes['full'] = __( 'Full (original image)', 'dco-comment-attachment' );

		return $sizes;
	}

	/**
	 * Builds the human-readable title for the image size.
	 *
	 * Takes the image size name and its attributes (such as width, height, and crop)
	 * and returns a formatted string to display as the title in the select dropdown.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name       The image size name.
	 * @param array  $attributes The attributes of the image size (width, height, crop).
	 *
	 * @return string The formatted title for the image size.
	 */
	private function build_image_size_title( string $name, array $attributes ): string {

		$width  = $attributes['width'];
		$height = $attributes['height'];
		$size   = __( 'Size', 'dco-comment-attachment' ) . ": {$width}x{$height}";

		$crop = $attributes['crop'] ? __( 'Yes', 'dco-comment-attachment' ) : __( 'No', 'dco-comment-attachment' );
		$crop = __( 'Crop', 'dco-comment-attachment' ) . ": {$crop}";

		$name = mb_ucfirst( $name );

		return "{$name}, {$size}, {$crop}";
	}
}
