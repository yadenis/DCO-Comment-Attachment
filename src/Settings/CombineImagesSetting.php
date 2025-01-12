<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\DTO\SettingsFieldDTO;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Helpers\OptionsHelper;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingsControls\CheckboxSettingsControl;
use DCO_CA\SettingsControls\DescriptionSettingsControl;

defined( 'ABSPATH' ) || die;

final class CombineImagesSetting implements Setting {

	private const OPTION_NAME   = 'combine_images';
	private const DEFAULT_VALUE = true;

	private string $title;
	private string $description;

	public function __construct(
		private OptionsHelper $options_helper,
	) {

		$this->title = __( 'Combine images to gallery?', 'dco-comment-attachment' );

		$this->description = __(
			'If checked, attached images will be combined to a gallery. Otherwise, the images will be displayed as a list.',
			'dco-comment-attachment'
		);
	}

	public function get_settings_value(): bool {

		return $this->options_helper->get_bool_option( self::OPTION_NAME ) ?? self::DEFAULT_VALUE;
	}

	public function get_settings_field_dto(): SettingsFieldDTO {

		return new SettingsFieldDTO(
			id: self::OPTION_NAME,
			title: $this->title,
			callback: $this->render_settings_field( ... ),
			section: SettingsSection::MULTIPLE_UPLOAD
		);
	}

	public function render_settings_field( array $args ): void {

		(
			new CheckboxSettingsControl(
				name: $args['name'],
				id: $args['id'],
				checked: $this->get_settings_value(),
			)
		)->render();

		(
			new DescriptionSettingsControl(
				text: $this->description
			)
		)->render();
	}
}
