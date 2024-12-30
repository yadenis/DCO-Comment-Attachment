<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\Options;
use DCO_CA\DTO\SettingFieldDTO;
use DCO_CA\Enums\MaxUploadSizeFormat;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingControls\DescriptionSettingControl;
use DCO_CA\SettingControls\NumberSettingControl;

defined( 'ABSPATH' ) || die;

final class MaxUploadSizeSetting implements Setting {

	private const OPTION_NAME = 'max_upload_size';

	public function __construct(
		private Options $options,
	) {
	}

	public function get_value( MaxUploadSizeFormat $format = MaxUploadSizeFormat::IN_MEGABYTES ): int|string {

		$value = $this->options->get_int_option( self::OPTION_NAME );
		if ( null === $value ) {
			return $this->get_system_value( $format );
		}

		return match ( $format ) {
			MaxUploadSizeFormat::IN_BYTES => $value * MB_IN_BYTES,
			MaxUploadSizeFormat::IN_MEGABYTES => $value,
			MaxUploadSizeFormat::FORMATTED => (string) size_format( $value )
		};
	}

	public function get_setting_field_dto(): SettingFieldDTO {

		return new SettingFieldDTO(
			id: self::OPTION_NAME,
			title: __( 'Maximum upload file size', 'dco-comment-attachment' ),
			callback: $this->render_setting_field( ... ),
			section: SettingsSection::GENERAL
		);
	}

	public function render_setting_field( array $args ): void {

		(
			new NumberSettingControl(
				name: $args['name'],
				id: $args['id'],
				value:  $this->get_value( MaxUploadSizeFormat::IN_MEGABYTES ),
				max: $this->get_system_value( MaxUploadSizeFormat::IN_MEGABYTES ),
			)
		)->render();

		$description = sprintf(
			/* translators: %s: the maximum allowed upload file size */
			__(
				'Set the value in megabytes. Currently your server allows you to upload files up to %s.',
				'dco-comment-attachment'
			),
			$this->get_system_value( MaxUploadSizeFormat::FORMATTED )
		);

		(
			new DescriptionSettingControl(
				text: $description,
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
