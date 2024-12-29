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

final class EmbedAttachmentSetting implements Setting {

	private const OPTION_NAME   = 'embed_attachment';
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
			title: __( 'Embed attachment?', 'dco-comment-attachment' ),
			callback: $this->render_setting_field( ... ),
			section: SettingsSection::GENERAL
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
				text: __( 'If checked, the attachment is displayed as an image, video, audio, or file link. Otherwise, all attachments will be displayed as links to files.', 'dco-comment-attachment' ),
			)
		)->render();
	}

	private function get_default_value(): bool {

		return self::DEFAULT_VALUE;
	}
}
