<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\DTO\SettingFieldDTO;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Helpers\OptionsHelper;
use DCO_CA\Enums\WhoCanUploadType;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingControls\RadioChoiceSettingControl;
use DCO_CA\SettingControls\RadioSettingControl;

defined( 'ABSPATH' ) || die;

final class WhoCanUploadSetting implements Setting {

	private const OPTION_NAME   = 'who_can_upload';
	private const DEFAULT_VALUE = WhoCanUploadType::ALL_USERS;

	private const LEGACY_VALUES_MAP = [
		'1' => WhoCanUploadType::ALL_USERS,
		'2' => WhoCanUploadType::ONLY_LOGGED_USERS,
	];

	private string $title;
	private array $types;

	public function __construct(
		private OptionsHelper $options,
	) {

		$this->title = __( 'Who can upload attachment?', 'dco-comment-attachment' );

		$this->types = [
			WhoCanUploadType::ALL_USERS->value         => __( 'All users', 'dco-comment-attachment' ),
			WhoCanUploadType::ONLY_LOGGED_USERS->value => __( 'Only logged users', 'dco-comment-attachment' ),
		];
	}

	public function get_value(): string {

		$value = $this->options->get_string_option( self::OPTION_NAME );

		return $this->ensure_backward_compatibility( $value ) ?? self::DEFAULT_VALUE->value;
	}

	public function is_can_upload_all_users(): bool {

		if ( WhoCanUploadType::ALL_USERS->value === $this->get_value() ) {
			return true;
		}

		return false;
	}

	public function is_can_upload_only_logged_users(): bool {

		if ( WhoCanUploadType::ONLY_LOGGED_USERS->value === $this->get_value() ) {
			return true;
		}

		return false;
	}

	public function get_setting_field_dto(): SettingFieldDTO {

		return new SettingFieldDTO(
			id: self::OPTION_NAME,
			title: $this->title,
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

	private function build_choices( array $args ): array {

		$choices = [];

		foreach ( $this->types as $value => $text ) {

			$choices[] = new RadioChoiceSettingControl(
				name: $args['name'],
				value: $value,
				text: $text,
				checked: $value === $this->get_value(),
			);
		}

		return $choices;
	}

	private function ensure_backward_compatibility( ?string $value ): ?string {

		return self::LEGACY_VALUES_MAP[ $value ]->value ?? $value;
	}
}
