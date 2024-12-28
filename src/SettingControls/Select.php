<?php

declare(strict_types=1);

namespace DCO_CA\SettingControls;

use DCO_CA\Interfaces\SettingControl;

defined( 'ABSPATH' ) || die;

final class Select implements SettingControl {

	public function __construct(
		private string $name,
		private string $id,
		private array $choices,
	) {
	}

	public function render(): void {

		printf(
			'<select name="%s" id="%s">',
			esc_attr( $this->name ),
			esc_attr( $this->id ),
		);

		foreach ( $this->choices as $choice ) {

			if ( ! $choice instanceof Option ) {
				continue;
			}

			$choice->render();
		}

		echo '</select>';
	}
}
