<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\DTO\SettingFieldDTO;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Options;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingControls\CheckboxSettingControl;
use DCO_CA\SettingControls\DescriptionSettingControl;

defined( 'ABSPATH' ) || die;

final class ManuallyModerationSetting implements Setting {

	private const OPTION_NAME   = 'manually_moderation';
	private const DEFAULT_VALUE = false;

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
			title: __( 'Manually moderate comments with attachments', 'dco-comment-attachment' ),
			callback: $this->render_setting_field( ... ),
			section: SettingsSection::PERMISSIONS
		);
	}

	public function render_setting_field( array $args ): void {

		(
			new CheckboxSettingControl(
				name: $args['name'],
				id: $args['id'],
				checked: true === $this->get_value(),
			)
		)->render();

		(
			new DescriptionSettingControl(
				text: __(
					'If checked, all comments with attachments must be manually approved before they appear on the site.',
					'dco-comment-attachment'
				),
			)
		)->render();
	}

	private function get_default_value(): bool {

		return self::DEFAULT_VALUE;
	}
}
