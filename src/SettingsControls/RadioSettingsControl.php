<?php
/**
 * SettingsControls: Radio
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
 * Renders the radio settings control in the admin panel.
 *
 * @since 3.0.0
 */
final class RadioSettingsControl implements SettingsControl {

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param RadioChoiceSettingsControl[] $choices The list of radio choices.
	 */
	public function __construct(
		private array $choices,
	) {
	}

	/**
	 * Renders the radio settings control.
	 *
	 * @since 3.0.0
	 */
	public function render_control(): void {

		echo '<fieldset>';

		$choices = [];

		foreach ( $this->choices as $choice ) {

			if ( ! $choice instanceof RadioChoiceSettingsControl ) {
				continue;
			}

			ob_start();

			$choice->render_control();

			$choices[] = ob_get_clean();
		}

		$allowed_tags = [
			'br'    => [],
			'label' => [],
			'input' => [
				'type'    => true,
				'name'    => true,
				'value'   => true,
				'checked' => true,
			],
		];

		echo wp_kses( implode( '<br>', $choices, ), $allowed_tags );

		echo '</fieldset>';
	}
}
