<?php
/**
 * SettingsControls: Number
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
 * Renders the number settings control in the admin panel.
 *
 * @since 3.0.0
 */
final class NumberSettingsControl implements SettingsControl {

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name  The setting name.
	 * @param string $id    The setting id.
	 * @param int    $value The input value.
	 * @param int    $max   The input max attribute value.
	 */
	public function __construct(
		private string $name,
		private string $id,
		private int $value,
		private int $max,
	) {
	}

	/**
	 * Renders the number settings control.
	 *
	 * @since 3.0.0
	 */
	public function render_control(): void {

		printf(
			'<input type="number" name="%s" class="regular-text" id="%s" value="%d" min="1" max="%d">',
			esc_attr( $this->name ),
			esc_attr( $this->id ),
			intval( $this->value ),
			intval( $this->max )
		);
	}
}
