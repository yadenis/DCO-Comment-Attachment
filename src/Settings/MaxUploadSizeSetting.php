<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\Helpers\OptionsHelper;
use DCO_CA\DTO\SettingsFieldDTO;
use DCO_CA\Enums\MaxUploadSizeFormat;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingsControls\DescriptionSettingsControl;
use DCO_CA\SettingsControls\NumberSettingsControl;

defined( 'ABSPATH' ) || die;

final class MaxUploadSizeSetting implements Setting {

	private const OPTION_NAME = 'max_upload_size';

	private string $title;
	private string $description;

	public function __construct(
		private OptionsHelper $options_helper,
	) {

		$this->title = __( 'Maximum upload file size', 'dco-comment-attachment' );

		$this->description = sprintf(
			/* translators: %s: the maximum allowed upload file size */
			__(
				'Set the value in megabytes. Currently your server allows you to upload files up to %s.',
				'dco-comment-attachment'
			),
			$this->get_system_value( MaxUploadSizeFormat::FORMATTED )
		);
	}

	public function get_settings_value( MaxUploadSizeFormat $format = MaxUploadSizeFormat::IN_MEGABYTES ): int|string {

		$value = $this->options_helper->get_int_option( self::OPTION_NAME );
		if ( null === $value ) {
			return $this->get_system_value( $format );
		}

		return match ( $format ) {
			MaxUploadSizeFormat::IN_BYTES => $value * MB_IN_BYTES,
			MaxUploadSizeFormat::IN_MEGABYTES => $value,
			MaxUploadSizeFormat::FORMATTED => (string) size_format( $value * MB_IN_BYTES )
		};
	}

	public function get_settings_field_dto(): SettingsFieldDTO {

		return new SettingsFieldDTO(
			id: self::OPTION_NAME,
			title: $this->title,
			callback: $this->render_settings_field( ... ),
			section: SettingsSection::GENERAL
		);
	}

	public function render_settings_field( array $args ): void {

		(
			new NumberSettingsControl(
				name: $args['name'],
				id: $args['id'],
				value:  $this->get_settings_value( MaxUploadSizeFormat::IN_MEGABYTES ),
				max: $this->get_system_value( MaxUploadSizeFormat::IN_MEGABYTES ),
			)
		)->render();

		(
			new DescriptionSettingsControl(
				text: $this->description,
			)
		)->render();
	}

	private function get_system_value( MaxUploadSizeFormat $format = MaxUploadSizeFormat::IN_MEGABYTES ): int|string {

		$value = wp_max_upload_size();

		return match ( $format ) {
			MaxUploadSizeFormat::IN_BYTES => $value,
			MaxUploadSizeFormat::IN_MEGABYTES => $value / MB_IN_BYTES,
			MaxUploadSizeFormat::FORMATTED => (string) size_format( $value ),
		};
	}
}
