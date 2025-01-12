<?php
/**
 * SettingsControls: Radio Choice
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
 * Renders the radio choice settings control in the admin panel.
 *
 * @since 3.0.0
 */
final class RadioChoiceSettingsControl implements SettingsControl {

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name    The setting name.
	 * @param string $value   The input value.
	 * @param string $text    The text label to be displayed next to the radio button.
	 * @param bool   $checked The initial checked state of the radio choice (optional).
	 *                        Default false.
	 */
	public function __construct(
		private string $name,
		private string $value,
		private string $text,
		private bool $checked = false,
	) {
	}

	/**
	 * Renders the radio choice settings control.
	 *
	 * @since 3.0.0
	 */
	public function render_control(): void {

		printf(
			'<label><input type="radio" name="%s" value="%s"%s> %s</label>',
			esc_attr( $this->name ),
			esc_attr( $this->value ),
			checked(
				checked: true,
				current: $this->checked,
				display: false
			),
			wp_kses( $this->text, [ 'a' => [ 'href' => true ] ] ),
		);
	}
}
