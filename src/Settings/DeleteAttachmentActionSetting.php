<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\DTO\SettingsFieldDTO;
use DCO_CA\Enums\DeleteAttachmentActionType;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Helpers\OptionsHelper;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingsControls\RadioChoiceSettingsControl;
use DCO_CA\SettingsControls\RadioSettingsControl;

defined( 'ABSPATH' ) || die;

final class DeleteAttachmentActionSetting implements Setting {

	private const OPTION_NAME   = 'delete_attachment_action';
	private const DEFAULT_VALUE = DeleteAttachmentActionType::DELETE;

	private const LEGACY_VALUES_MAP = [
		'1' => DeleteAttachmentActionType::DELETE,
		'0' => DeleteAttachmentActionType::DETACH,
	];

	private string $title;
	private array $types;

	public function __construct(
		private OptionsHelper $options_helper,
	) {

		$this->title = __( 'Delete Attachment action on Edit Comments page', 'dco-comment-attachment' );

		$this->types = [
			DeleteAttachmentActionType::DELETE->value => __(
				'Delete attachment from Media Library',
				'dco-comment-attachment'
			),
			DeleteAttachmentActionType::DETACH->value => __(
				'Detach attachment from comment',
				'dco-comment-attachment'
			),
		];
	}

	public function get_setting_value(): string {

		$value = $this->options_helper->get_string_option( self::OPTION_NAME );

		return $this->ensure_backward_compatibility( $value ) ?? self::DEFAULT_VALUE->value;
	}

	public function is_delete_attachment_from_media_library(): bool {

		if ( DeleteAttachmentActionType::DELETE->value === $this->get_setting_value() ) {
			return true;
		}

		return false;
	}

	public function get_settings_field_dto(): SettingsFieldDTO {

		return new SettingsFieldDTO(
			id: self::OPTION_NAME,
			title: $this->title,
			callback: $this->render_settings_field( ... ),
			section: SettingsSection::IN_ADMIN
		);
	}

	public function render_settings_field( array $args ): void {

		(
			new RadioSettingsControl(
				choices: $this->build_choices( $args ),
			)
		)->render_control();
	}

	private function build_choices( array $args ): array {

		$choices = [];

		foreach ( $this->types as $value => $text ) {

			$choices[] = new RadioChoiceSettingsControl(
				name: $args['name'],
				value: $value,
				text: $text,
				checked: $value === $this->get_setting_value(),
			);
		}

		return $choices;
	}

	private function ensure_backward_compatibility( ?string $value ): ?string {

		return self::LEGACY_VALUES_MAP[ $value ]->value ?? $value;
	}
}
