<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\DTO\SettingFieldDTO;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Options;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingControls\Checkbox;
use DCO_CA\SettingControls\Description;

defined( 'ABSPATH' ) || die;

final class CombineImages implements Setting {

	private const OPTION_NAME   = 'combine_images';
	private const DEFAULT_VALUE = true;

	public function __construct(
		private Options $options,
	) {
	}

	public function get_value(): bool {

		return $this->options->get_bool_option( self::OPTION_NAME ) ?? $this->get_default_value();
	}

	public function get_setting_field_dto(): SettingFieldDTO {

		return new SettingFieldDTO(
			id: self::OPTION_NAME,
			title: __( 'Combine images to gallery?', 'dco-comment-attachment' ),
			callback: $this->render_setting_field( ... ),
			section: SettingsSection::MULTIPLE_UPLOAD
		);
	}

	public function render_setting_field( array $args ): void {

		(
			new Checkbox(
				name: $args['name'],
				id: $args['id'],
				checked: true === $this->get_value(),
			)
		)->render();

		(
			new Description(
				text:  __( 'If checked, attached images will be combined to a gallery. Otherwise, the images will be displayed as a list.', 'dco-comment-attachment' ),
			)
		)->render();
	}

	private function get_default_value(): bool {

		return self::DEFAULT_VALUE;
	}
}
