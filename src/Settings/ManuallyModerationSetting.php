<?php
/**
 * Settings: Manually Moderation
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
 * Provides functionality for the Manually Moderation plugin setting.
 *
 * @since 3.0.0
 */
final class ManuallyModerationSetting implements Setting {

	private const OPTION_NAME   = 'manually_moderation';
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

		$this->title = __( 'Manually moderate comments with attachments', 'dco-comment-attachment' );

		$this->description = __(
			'If checked, all comments with attachments must be manually approved before they appear on the site.',
			'dco-comment-attachment'
		);
	}

	/**
	 * Whether manual moderation is enabled.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if manual moderation is enabled, false otherwise.
	 */
	public function get_setting_value(): bool {

		return $this->options_helper->get_bool_option( self::OPTION_NAME ) ?? self::DEFAULT_VALUE;
	}

	/**
	 * Returns the settings field DTO for rendering the Manually Moderation setting.
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
			section: SettingsSection::PERMISSIONS
		);
	}

	/**
	 * Renders the Manually Moderation settings field in the plugin settings page.
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
