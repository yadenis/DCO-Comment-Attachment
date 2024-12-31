<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\DTO\SettingFieldDTO;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Options;
use DCO_CA\Interfaces\Setting;
use DCO_CA\Services\AttachmentService;
use DCO_CA\SettingControls\DescriptionSettingControl;
use DCO_CA\SettingControls\SelectSettingControl;
use DCO_CA\SettingControls\SelectOptionSettingControl;

defined( 'ABSPATH' ) || die;

final class ThumbnailSizeSetting implements Setting {

	private const OPTION_NAME   = 'thumbnail_size';
	private const DEFAULT_VALUE = 'medium';

	private string $title;
	private string $description;

	public function __construct(
		private Options $options,
		private AttachmentService $attachment_service,
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
			new SelectSettingControl(
				name: $args['name'],
				id: $args['id'],
				options: $this->build_options(),
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

	private function build_options(): array {

		$options = [];

		$sizes = $this->attachment_service->get_image_sizes();

		foreach ( $sizes as $name => $title ) {

			$options[] = new SelectOptionSettingControl(
				value: $name,
				text: $title,
				selected: $name === $this->get_value(),
			);
		}

		return $options;
	}
}
