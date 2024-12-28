<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\DTO\SettingFieldDTO;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Options;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingControl;
use DCO_CA\SettingControlOption;
use DCO_CA\SettingControls\Description;
use DCO_CA\SettingControls\Option;
use DCO_CA\SettingControls\Select;

defined( 'ABSPATH' ) || die;

final class ThumbnailSize implements Setting {

	private const OPTION_NAME   = 'thumbnail_size';
	private const DEFAULT_VALUE = 'medium';

	public function __construct(
		private Options $options,
	) {
	}

	public function get_value(): string {

		$value = $this->options->get_string_option( self::OPTION_NAME );
		if ( null === $value ) {
			return $this->get_default_value();
		}

		return $value;
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
				choices: $this->build_choices(),
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

	private function build_choices(): array {

		$choices = [];

		$sizes = wp_get_registered_image_subsizes();

		foreach ( $sizes as $name => $attributes ) {

			$text = $this->build_choice_text( $name, $attributes );

			$choices[] = new Option(
				value: $name,
				text: $text,
				selected: $name === $this->get_value(),
			);
		}

		$choices[] = new Option(
			value: 'full',
			text: __( 'Full (original image)', 'dco-comment-attachment' ),
			selected: 'full' === $this->get_value(),
		);

		return $choices;
	}

	private function build_choice_text( string $name, array $attributes ): string {

		$width  = $attributes['width'];
		$height = $attributes['height'];
		$size   = __( 'Size', 'dco-comment-attachment' ) . ": {$width}x{$height}";

		$crop = $attributes['crop'] ? __( 'Yes', 'dco-comment-attachment' ) : __( 'No', 'dco-comment-attachment' );
		$crop = __( 'Crop', 'dco-comment-attachment' ) . ": $crop";

		$name = ucfirst( $name );

		return "$name, $size, $crop";
	}
}
