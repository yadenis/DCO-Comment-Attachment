<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\Options;
use DCO_CA\DTO\SettingFieldDTO;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingControls\Checkbox;
use DCO_CA\SettingControls\Description;

defined( 'ABSPATH' ) || die;

final class RequiredAttachment implements Setting {

	private const OPTION_NAME   = 'required_attachment';
	private const DEFAULT_VALUE = false;

	public function __construct(
		private Options $options,
	) {
	}

	public function get_value(): bool {

		$value = $this->options->get_bool_option( self::OPTION_NAME );
		if ( null === $value ) {
			return $this->get_default_value();
		}

		return $value;
	}

	public function get_setting_field_dto(): SettingFieldDTO {

		return new SettingFieldDTO(
			id: self::OPTION_NAME,
			title: esc_html__( 'Is attachment required?', 'dco-comment-attachment' ),
			callback: $this->render_setting_field( ... ),
			section: SettingsSection::GENERAL
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
				text: __( 'If checked, the user will not be able to post a comment without attaching an attachment.', 'dco-comment-attachment' ),
			)
		)->render();
	}

	private function get_default_value(): bool {

		return self::DEFAULT_VALUE;
	}
}
