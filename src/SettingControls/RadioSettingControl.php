<?php

declare(strict_types=1);

namespace DCO_CA\SettingControls;

use DCO_CA\Interfaces\SettingControl;

defined( 'ABSPATH' ) || die;

final class RadioSettingControl implements SettingControl {

	public function __construct(
		private array $choices,
	) {
	}

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

		echo implode( '<br>', $choices );

		echo '</fieldset>';
	}
}
