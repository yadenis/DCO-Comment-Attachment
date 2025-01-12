<?php
/**
 * SettingsControls: Checkbox
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
 * Renders the checkbox settings control in the admin panel.
 *
 * @since 3.0.0
 */
final class CheckboxSettingsControl implements SettingsControl {

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name    The setting name.
	 * @param string $id      The setting id.
	 * @param bool   $checked The initial checked state of the checkbox (optional).
	 *                        Default false.
	 */
	public function __construct(
		private string $name,
		private string $id,
		private bool $checked = false,
	) {
	}

	/**
	 * Renders the checkbox settings control.
	 *
	 * @since 3.0.0
	 */
	public function render_control(): void {

		printf(
			'<input type="hidden" name="%s" value="0">',
			esc_attr( $this->name ),
		);

		printf(
			'<input type="checkbox" name="%s" id="%s" value="1"%s>',
			esc_attr( $this->name ),
			esc_attr( $this->id ),
			checked(
				checked: true,
				current: $this->checked,
				display: false
			),
		);
	}
}
