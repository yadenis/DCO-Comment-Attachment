<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\DTO\SettingFieldDTO;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Helpers\OptionsHelper;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingControls\DescriptionSettingControl;
use DCO_CA\SettingControls\ImageSizeControl;

defined( 'ABSPATH' ) || die;

final class ThumbnailSizeSetting implements Setting {

	private const OPTION_NAME   = 'thumbnail_size';
	private const DEFAULT_VALUE = 'medium';

	private string $title;
	private string $description;

	public function __construct(
		private OptionsHelper $options,
	) {

		$this->title = __( 'Attachment image size', 'dco-comment-attachment' );

		$this->description = __(
			'The size of the thumbnail for attached images.',
			'dco-comment-attachment'
		);
	}

	public function get_value(): string {

		return $this->options->get_string_option( self::OPTION_NAME ) ?? $this->get_default_value();
	}

	public function get_setting_field_dto(): SettingFieldDTO {

		return new SettingFieldDTO(
			id: self::OPTION_NAME,
			title: $this->title,
			callback: $this->render_setting_field( ... ),
			section: SettingsSection::IMAGES
		);
	}

	public function render_setting_field( array $args ): void {

		(
			new ImageSizeControl(
				name: $args['name'],
				id: $args['id'],
				selected_size: $this->get_value(),
			)
		)->render();

		(
			new DescriptionSettingControl(
				text: $this->description,
			)
		)->render();
	}

	private function get_default_value(): string {

		return self::DEFAULT_VALUE;
	}
}
