<?php

declare(strict_types=1);

namespace DCO_CA\Services;

defined( 'ABSPATH' ) || die;

final class AttachmentService {

	private const IMAGE_EXTENSIONS         = [ 'jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp' ];
	private const ADMINISTRATOR_EXTENSIONS = [ 'htm', 'html', 'js' ];

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

	public function is_administrator_extension( string $extension ): bool {

		return in_array( $extension, self::ADMINISTRATOR_EXTENSIONS, true );
	}

	public function is_embedded_extension( string $extension ): bool {

		return in_array( $extension, $this->get_embedded_extensions(), true );
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

	private function get_embedded_extensions(): array {

		return array_merge(
			wp_get_video_extensions(),
			wp_get_audio_extensions(),
			self::IMAGE_EXTENSIONS,
		);
	}
}
