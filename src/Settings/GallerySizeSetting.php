<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\DTO\SettingsFieldDTO;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Helpers\OptionsHelper;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingsControls\DescriptionSettingsControl;
use DCO_CA\SettingsControls\ImageSizeSettingsControl;

defined( 'ABSPATH' ) || die;

final class GallerySizeSetting implements Setting {

	private const OPTION_NAME   = 'gallery_size';
	private const DEFAULT_VALUE = 'thumbnail';

	private string $title;
	private string $description;

	public function __construct(
		private OptionsHelper $options_helper,
	) {

		$this->title = __( 'Gallery image size', 'dco-comment-attachment' );

		$this->description = __(
			'The size of the thumbnail for attached images.',
			'dco-comment-attachment'
		);
	}

	public function get_settings_value(): string {

		return $this->options_helper->get_string_option( self::OPTION_NAME ) ?? self::DEFAULT_VALUE;
	}

	public function get_settings_field_dto(): SettingsFieldDTO {

		return new SettingsFieldDTO(
			id: self::OPTION_NAME,
			title: $this->title,
			callback: $this->render_settings_field( ... ),
			section: SettingsSection::MULTIPLE_UPLOAD,
		);
	}

	public function render_settings_field( array $args ): void {

		(
			new ImageSizeSettingsControl(
				name: $args['name'],
				id: $args['id'],
				selected_size: $this->get_settings_value(),
			)
		)->render();

		(
			new DescriptionSettingsControl(
				text: $this->description,
			)
		)->render();
	}
}
