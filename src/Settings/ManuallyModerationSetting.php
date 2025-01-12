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

final class ManuallyModerationSetting implements Setting {

	private const OPTION_NAME   = 'manually_moderation';
	private const DEFAULT_VALUE = false;

	private string $title;
	private string $description;

	public function __construct(
		private OptionsHelper $options_helper,
	) {

		$this->title = __( 'Manually moderate comments with attachments', 'dco-comment-attachment' );

		$this->description = __(
			'If checked, all comments with attachments must be manually approved before they appear on the site.',
			'dco-comment-attachment'
		);
	}

	public function get_setting_value(): bool {

		return $this->options_helper->get_bool_option( self::OPTION_NAME ) ?? self::DEFAULT_VALUE;
	}

	public function get_settings_field_dto(): SettingsFieldDTO {

		return new SettingsFieldDTO(
			id: self::OPTION_NAME,
			title: $this->title,
			callback: $this->render_settings_field( ... ),
			section: SettingsSection::PERMISSIONS
		);
	}

	public function render_settings_field( array $args ): void {

		(
			new CheckboxSettingsControl(
				name: $args['name'],
				id: $args['id'],
				checked: $this->get_setting_value(),
			)
		)->render_control();

		(
			new DescriptionSettingsControl(
				text: $this->description,
			)
		)->render_control();
	}
}
