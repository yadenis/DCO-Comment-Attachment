<?php
/**
 * SettingsControls: Description
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA\SettingsControls;

use DCO_CA\Interfaces\SettingsControl;

defined( 'ABSPATH' ) || die;

/**
 * Renders the description settings control in the admin panel.
 *
 * @since 3.0.0
 */
final class DescriptionSettingsControl implements SettingsControl {

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param string $text The description text.
	 */
	public function __construct(
		private string $text,
	) {
	}

	/**
	 * Renders the description settings control.
	 *
	 * @since 3.0.0
	 */
	public function render_control(): void {

		printf(
			'<p class="dco-description">%s</p>',
			wp_kses( $this->text, [ 'br' => [] ] )
		);
	}
}
