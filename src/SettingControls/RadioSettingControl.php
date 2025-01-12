<?php
/**
 * SettingControls: Radio
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
 * Renders the radio setting control in the admin panel.
 *
 * @since 3.0.0
 */
final class RadioSettingControl implements SettingControl {

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param RadioChoiceSettingControl[] $choices The list of radio choices.
	 */
	public function __construct(
		private array $choices,
	) {
	}

	/**
	 * Renders the radio setting control.
	 *
	 * @since 3.0.0
	 */
	public function render(): void {

		echo '<fieldset>';

		$choices = [];

		foreach ( $this->choices as $choice ) {

			if ( ! $choice instanceof RadioChoiceSettingControl ) {
				continue;
			}

			ob_start();

			$choice->render();

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
