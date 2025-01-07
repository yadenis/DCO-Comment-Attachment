<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\DTO\SettingFieldDTO;
use DCO_CA\Enums\DeleteAttachmentActionType;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Helpers\OptionsHelper;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingControls\RadioChoiceSettingControl;
use DCO_CA\SettingControls\RadioSettingControl;

defined( 'ABSPATH' ) || die;

final class DeleteAttachmentActionSetting implements Setting {

	private const OPTION_NAME   = 'delete_attachment_action';
	private const DEFAULT_VALUE = DeleteAttachmentActionType::DELETE;

	private string $title;
	private array $types;

	public function __construct(
		private OptionsHelper $options,
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

	public function get_value(): string {

		return $this->options->get_string_option( self::OPTION_NAME ) ?? $this->get_default_value();
	}

	public function is_delete_attachment_from_media_library(): bool {

		if ( DeleteAttachmentActionType::DELETE->value === $this->get_value() ) {
			return true;
		}

		return false;
	}

	public function get_setting_field_dto(): SettingFieldDTO {

		return new SettingFieldDTO(
			id: self::OPTION_NAME,
			title: $this->title,
			callback: $this->render_setting_field( ... ),
			section: SettingsSection::IN_ADMIN
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
}
