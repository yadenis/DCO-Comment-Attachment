<?php

declare(strict_types=1);

namespace DCO_CA\SettingControls;

use DCO_CA\Interfaces\SettingControl;

defined( 'ABSPATH' ) || die;

final class NumberSettingControl implements SettingControl {

	public function __construct(
		private string $name,
		private string $id,
		private int $value,
		private int $max,
	) {
	}

	public function render(): void {

		printf(
			'<input type="number" name="%s" class="regular-text" id="%s" value="%d" min="1" max="%d">',
			esc_attr( $this->name ),
			esc_attr( $this->id ),
			intval( $this->value ),
			intval( $this->max )
		);
	}
}
