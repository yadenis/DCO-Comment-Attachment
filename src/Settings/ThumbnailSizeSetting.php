<?php
/**
 * Settings: Thumbnail Size
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

use DCO_CA\DTO\SettingsFieldDTO;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Helpers\OptionsHelper;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingsControls\DescriptionSettingsControl;
use DCO_CA\SettingsControls\ImageSizeSettingsControl;

defined( 'ABSPATH' ) || die;

/**
 * Provides functionality for the Thumbnail Size plugin setting.
 *
 * @since 3.0.0
 */
final class ThumbnailSizeSetting implements Setting {

	private const OPTION_NAME   = 'thumbnail_size';
	private const DEFAULT_VALUE = 'medium';

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

		$this->title = __( 'Attachment image size', 'dco-comment-attachment' );

		$this->description = __(
			'The size of the thumbnail for attached images.',
			'dco-comment-attachment'
		);
	}

	/**
	 * Retrieves the thumbnail size for images.
	 *
	 * @since 3.0.0
	 *
	 * @return string The thumbnail size (e.g., 'medium', 'large').
	 */
	public function get_setting_value(): string {

		return $this->options_helper->get_string_option( self::OPTION_NAME ) ?? self::DEFAULT_VALUE;
	}

	/**
	 * Returns the settings field DTO for rendering the Thumbnail Size setting.
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
			section: SettingsSection::IMAGES
		);
	}

	/**
	 * Renders the Thumbnail Size settings field in the plugin settings page.
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
			new ImageSizeSettingsControl(
				name: $args['name'],
				id: $args['id'],
				selected_size: $this->get_setting_value(),
			)
		)->render_control();

		(
			new DescriptionSettingsControl(
				text: $this->description,
			)
		)->render_control();
	}
}
