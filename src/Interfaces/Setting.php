<?php
/**
 * Interfaces: Setting
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA\Interfaces;

use DCO_CA\DTO\SettingsFieldDTO;

defined( 'ABSPATH' ) || die;

interface Setting {

	/**
	 * Retrieves the value of the setting.
	 *
	 * @since 3.0.0
	 *
	 * @return mixed The value of the setting.
	 */
	public function get_setting_value(): mixed;

	/**
	 * Returns the settings field DTO for rendering setting.
	 *
	 * @since 3.0.0
	 *
	 * @return SettingsFieldDTO The settings field DTO.
	 */
	public function get_settings_field_dto(): SettingsFieldDTO;

	/**
	 * Renders the settings field in the plugin settings page.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args The arguments list for rendering.
	 */
	public function render_settings_field( array $args ): void;
}
