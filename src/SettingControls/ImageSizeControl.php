<?php

declare(strict_types=1);

namespace DCO_CA\SettingControls;

use DCO_CA\Interfaces\SettingControl;

defined( 'ABSPATH' ) || die;

final class ImageSizeControl implements SettingControl {

	public function __construct(
		private string $name,
		private string $id,
		private string $selected_size,
	) {
	}

	public function render(): void {

		printf(
			'<select name="%s" id="%s">',
			esc_attr( $this->name ),
			esc_attr( $this->id ),
		);

		foreach ( $this->get_sizes() as $name => $title ) {

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

	private function get_sizes(): array {

		$sizes = wp_get_registered_image_subsizes();

		array_walk(
			$sizes,
			// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
			fn( array &$attributes, string $name ): string => $attributes = $this->build_size_title( $name, $attributes )
		);

		$sizes['full'] = __( 'Full (original image)', 'dco-comment-attachment' );

		return $sizes;
	}

	private function build_size_title( string $name, array $attributes ): string {

		$width  = $attributes['width'];
		$height = $attributes['height'];
		$size   = __( 'Size', 'dco-comment-attachment' ) . ": {$width}x{$height}";

		$crop = $attributes['crop'] ? __( 'Yes', 'dco-comment-attachment' ) : __( 'No', 'dco-comment-attachment' );
		$crop = __( 'Crop', 'dco-comment-attachment' ) . ": {$crop}";

		$name = mb_ucfirst( $name );

		return "{$name}, {$size}, {$crop}";
	}
}
