<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\DTO\SettingFieldDTO;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Options;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingControls\Description;
use DCO_CA\SettingControls\Select;
use DCO_CA\SettingControls\SelectOption;

defined( 'ABSPATH' ) || die;

final class ThumbnailSize implements Setting {

	private const OPTION_NAME   = 'thumbnail_size';
	private const DEFAULT_VALUE = 'medium';

	public function __construct(
		private Options $options,
	) {
	}

	public function get_value(): string {

		return $this->options->get_string_option( self::OPTION_NAME ) ?? $this->get_default_value();
	}

	public function get_setting_field_dto(): SettingFieldDTO {

		return new SettingFieldDTO(
			id: self::OPTION_NAME,
			title: esc_html__( 'Attachment image size', 'dco-comment-attachment' ),
			callback: $this->render_setting_field( ... ),
			section: SettingsSection::IMAGES
		);
	}

	public function render_setting_field( array $args ): void {

		(
			new Select(
				name: $args['name'],
				id: $args['id'],
				options: $this->build_options(),
			)
		)->render();

		(
			new Description(
				text:  __( 'The size of the thumbnail for attached images.', 'dco-comment-attachment' ),
			)
		)->render();
	}

	private function get_default_value(): string {

		return self::DEFAULT_VALUE;
	}

	private function build_options(): array {

		$options = [];

		$sizes = wp_get_registered_image_subsizes();

		foreach ( $sizes as $name => $attributes ) {

			$text = $this->build_option_text( $name, $attributes );

			$options[] = new SelectOption(
				value: $name,
				text: $text,
				selected: $name === $this->get_value(),
			);
		}

		$options[] = new SelectOption(
			value: 'full',
			text: __( 'Full (original image)', 'dco-comment-attachment' ),
			selected: 'full' === $this->get_value(),
		);

		return $options;
	}

	private function build_option_text( string $name, array $attributes ): string {

		$width  = $attributes['width'];
		$height = $attributes['height'];
		$size   = __( 'Size', 'dco-comment-attachment' ) . ": {$width}x{$height}";

		$crop = $attributes['crop'] ? __( 'Yes', 'dco-comment-attachment' ) : __( 'No', 'dco-comment-attachment' );
		$crop = __( 'Crop', 'dco-comment-attachment' ) . ": $crop";

		$name = ucfirst( $name );

		return "$name, $size, $crop";
	}
}
