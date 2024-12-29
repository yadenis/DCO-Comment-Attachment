<?php

declare(strict_types=1);

namespace DCO_CA\Services;

defined( 'ABSPATH' ) || die;

final class AttachmentService {

	public function __construct() {
	}

	public function get_image_sizes(): array {

		$sizes = [];

		$registered_sizes = wp_get_registered_image_subsizes();

		foreach ( $registered_sizes as $name => $attributes ) {

			$sizes[ $name ] = $this->build_image_size_title( $name, $attributes );
		}

		$sizes['full'] = __( 'Full (original image)', 'dco-comment-attachment' );

		return $sizes;
	}

	private function build_image_size_title( string $name, array $attributes ): string {

		$width  = $attributes['width'];
		$height = $attributes['height'];
		$size   = __( 'Size', 'dco-comment-attachment' ) . ": {$width}x{$height}";

		$crop = $attributes['crop'] ? __( 'Yes', 'dco-comment-attachment' ) : __( 'No', 'dco-comment-attachment' );
		$crop = __( 'Crop', 'dco-comment-attachment' ) . ": {$crop}";

		$name = ucfirst( $name );

		return "{$name}, {$size}, {$crop}";
	}
}
