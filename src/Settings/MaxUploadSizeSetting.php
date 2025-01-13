<?php
/**
 * Settings: Max Upload Size
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

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

/**
 * Provides functionality for the Max Upload Size plugin setting.
 *
 * @since 3.0.0
 */
final class MaxUploadSizeSetting implements Setting {

	private const OPTION_NAME = 'max_upload_size';

	/**
	 * The settings field title.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private string $title;

	/**
	 * The settings field description.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private string $description;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param OptionsHelper $options_helper Helper functions for options.
	 */
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
			$this->get_wp_value( MaxUploadSizeFormat::FORMATTED )
		);
	}

	/**
	 * Retrieves the max upload size from the plugin settings in the specified format.
	 *
	 * @since 3.0.0
	 *
	 * @param MaxUploadSizeFormat $format The format for the output value (optional).
	 *                                    Default IN_MEGABYTES.
	 *
	 * @return int|string The formatted max upload size.
	 */
	public function get_setting_value( MaxUploadSizeFormat $format = MaxUploadSizeFormat::IN_MEGABYTES ): int|string {

		$value = $this->options_helper->get_int_option( self::OPTION_NAME );
		if ( null === $value ) {
			return $this->get_wp_value( $format );
		}

		return match ( $format ) {
			MaxUploadSizeFormat::IN_BYTES => $value * MB_IN_BYTES,
			MaxUploadSizeFormat::IN_MEGABYTES => $value,
			MaxUploadSizeFormat::FORMATTED => (string) size_format( $value * MB_IN_BYTES )
		};
	}

	/**
	 * Returns the settings field DTO for rendering the Max Upload Size setting.
	 *
	 * @since 3.0.0
	 *
	 * @return SettingsFieldDTO The settings field DTO.
	 */
	public function get_settings_field_dto(): SettingsFieldDTO {

		return new SettingsFieldDTO(
			id: self::OPTION_NAME,
			title: $this->title,
			callback: $this->render_settings_field( ... ),
			section: SettingsSection::GENERAL
		);
	}

	/**
	 * Renders the Max Upload Size settings field in the plugin settings page.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args The arguments list for rendering.
	 */
	public function render_settings_field( array $args ): void {

		if ( empty( $args['name'] ) || empty( $args['id'] ) ) {
			return;
		}

		(
			new NumberSettingsControl(
				name: $args['name'],
				id: $args['id'],
				value:  $this->get_setting_value( MaxUploadSizeFormat::IN_MEGABYTES ),
				max: $this->get_wp_value( MaxUploadSizeFormat::IN_MEGABYTES ),
			)
		)->render_control();

		(
			new DescriptionSettingsControl(
				text: $this->description,
			)
		)->render_control();
	}

	/**
	 * Retrieves the max upload size from WordPress in the specified format.
	 *
	 * @since 3.0.0
	 *
	 * @param MaxUploadSizeFormat $format The format for the output value (optional).
	 *                                    Default IN_MEGABYTES.
	 *
	 * @return int|string The formatted max upload size.
	 */
	private function get_wp_value( MaxUploadSizeFormat $format = MaxUploadSizeFormat::IN_MEGABYTES ): int|string {

		$value = wp_max_upload_size();

		return match ( $format ) {
			MaxUploadSizeFormat::IN_BYTES => $value,
			MaxUploadSizeFormat::IN_MEGABYTES => $value / MB_IN_BYTES,
			MaxUploadSizeFormat::FORMATTED => (string) size_format( $value ),
		};
	}
}
