<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\DTO\SettingFieldDTO;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Helpers\OptionsHelper;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingControls\CheckboxSettingControl;
use DCO_CA\SettingControls\DescriptionSettingControl;

defined( 'ABSPATH' ) || die;

final class AutoembedLinksSetting implements Setting {

	private const OPTION_NAME   = 'autoembed_links';
	private const DEFAULT_VALUE = true;

	private string $title;
	private string $description;

	public function __construct(
		private OptionsHelper $options,
	) {

		$this->title = __( 'Autoembed links in comment text?', 'dco-comment-attachment' );

		$this->description = __(
			'If checked, links (like YouTube, Facebook, Twitter, etc.) in the comment text will be automatically turned into embedded content.',
			'dco-comment-attachment'
		);
	}

	public function get_value(): bool {

		return $this->options->get_bool_option( self::OPTION_NAME ) ?? self::DEFAULT_VALUE;
	}

	public function get_setting_field_dto(): SettingFieldDTO {

		return new SettingFieldDTO(
			id: self::OPTION_NAME,
			title: $this->title,
			callback: $this->render_setting_field( ... ),
			section: SettingsSection::GENERAL
		);
	}

	public function render_setting_field( array $args ): void {

		(
			new CheckboxSettingControl(
				name: $args['name'],
				id: $args['id'],
				checked: $this->get_value(),
			)
		)->render();

		(
			new DescriptionSettingControl(
				text: $this->description
			)
		)->render();
	}
}
