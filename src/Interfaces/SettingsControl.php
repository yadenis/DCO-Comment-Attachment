<?php
/**
 * Interfaces: Settings Control
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

defined( 'ABSPATH' ) || die;

interface SettingsControl {

	/**
	 * Renders the settings control.
	 *
	 * @since 3.0.0
	 */
	public function render_control(): void;
}
