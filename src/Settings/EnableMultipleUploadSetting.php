<?php
/**
 * Settings: Enable Multiple Upload
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
use DCO_CA\SettingsControls\CheckboxSettingsControl;
use DCO_CA\SettingsControls\DescriptionSettingsControl;

defined( 'ABSPATH' ) || die;

/**
 * Provides functionality for the Enable Multiple Upload plugin setting.
 *
 * @since 3.0.0
 */
final class EnableMultipleUploadSetting implements Setting {

	private const OPTION_NAME   = 'autoembed_links';
	private const DEFAULT_VALUE = false;

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

		$this->title = __( 'Enable multiple upload?', 'dco-comment-attachment' );

		$this->description = __(
			'If checked, users will be able to upload multiple attachments at once.',
			'dco-comment-attachment'
		);
	}

	/**
	 * Whether multiple upload is enabled.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if multiple upload is enabled, false otherwise.
	 */
	public function get_setting_value(): bool {

		return $this->options_helper->get_bool_option( self::OPTION_NAME ) ?? self::DEFAULT_VALUE;
	}

	/**
	 * Returns the settings field DTO for rendering the Enable Multiple Upload setting.
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
			section: SettingsSection::MULTIPLE_UPLOAD
		);
	}

	/**
	 * Renders the Enable Multiple Upload settings field in the plugin settings page.
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
			new CheckboxSettingsControl(
				name: $args['name'],
				id: $args['id'],
				checked: $this->get_setting_value(),
			)
		)->render_control();

		(
			new DescriptionSettingsControl(
				text: $this->description,
			)
		)->render_control();
	}
}
