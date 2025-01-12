<?php
/**
 * SettingControls: Description
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA\SettingControls;

use DCO_CA\Interfaces\SettingControl;

defined( 'ABSPATH' ) || die;

/**
 * Renders the description setting control in the admin panel.
 *
 * @since 3.0.0
 */
final class DescriptionSettingControl implements SettingControl {

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
	 * Renders the description setting control.
	 *
	 * @since 3.0.0
	 */
	public function render(): void {

		printf(
			'<p class="dco-description">%s</p>',
			wp_kses( $this->text, [ 'br' => [] ] )
		);
	}
}
