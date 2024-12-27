<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\Options;
use DCO_CA\DTO\SettingFieldDTO;
use DCO_CA\Enums\MaxUploadSizeFormat;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Interfaces\Setting;

defined( 'ABSPATH' ) || die;

final class MaxUploadSize implements Setting {

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
			title: esc_html__( 'Maximum upload file size', 'dco-comment-attachment' ),
			callback: $this->render_setting_field( ... ),
			section: SettingsSection::GENERAL
		);
	}

	public function render_setting_field( array $args ): void {

		printf(
			'<input type="number" name="%s" class="regular-text" id="%s" value="%d" min="1" max="%d">',
			esc_attr( $args['name'] ),
			esc_attr( $args['id'] ),
			intval( $this->get_value( MaxUploadSizeFormat::IN_MEGABYTES ) ),
			intval( $this->get_system_value( MaxUploadSizeFormat::IN_MEGABYTES ) )
		);

		$description = sprintf(
			/* translators: %s: the maximum allowed upload file size */
			esc_html__( 'Set the value in megabytes. Currently your server allows you to upload files up to %s.', 'dco-comment-attachment' ),
			$this->get_system_value( MaxUploadSizeFormat::FORMATTED )
		);

		printf(
			'<p class="description">%s</p>',
			wp_kses( $description, [ 'br' => [] ] )
		);
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
