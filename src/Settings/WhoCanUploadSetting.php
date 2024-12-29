<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\DTO\SettingFieldDTO;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Options;
use DCO_CA\Enums\WhoCanUploadType;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingControls\RadioChoiceSettingControl;
use DCO_CA\SettingControls\RadioSettingControl;

defined( 'ABSPATH' ) || die;

final class WhoCanUploadSetting implements Setting {

	private const OPTION_NAME   = 'who_can_upload';
	private const DEFAULT_VALUE = WhoCanUploadType::ALL_USERS;

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
			title:  __( 'Who can upload attachment?', 'dco-comment-attachment' ),
			callback: $this->render_setting_field( ... ),
			section: SettingsSection::PERMISSIONS
		);
	}

	public function render_setting_field( array $args ): void {

		(
			new RadioSettingControl(
				choices: $this->build_choices( $args ),
			)
		)->render();
	}

	private function get_default_value(): string {

		return self::DEFAULT_VALUE->value;
	}

	private function build_choices( array $args ): array {

		$types = [
			WhoCanUploadType::ALL_USERS->value    => __( 'All users', 'dco-comment-attachment' ),
			WhoCanUploadType::LOGGED_USERS->value => __( 'Only logged users', 'dco-comment-attachment' ),
		];

		$choices = [];

		foreach ( $types as $value => $text ) {

			$choices[] = new RadioChoiceSettingControl(
				name: $args['name'],
				value: $value,
				text: $text,
				checked: $value === $this->get_value(),
			);
		}

		return $choices;
	}
}
