<?php

declare(strict_types=1);

namespace DCO_CA\SettingControls;

use DCO_CA\Interfaces\SettingControl;

defined( 'ABSPATH' ) || die;

final class CheckboxSettingControl implements SettingControl {

	public function __construct(
		private string $name,
		private string $id,
		private bool $checked = false,
	) {
	}

	public function render(): void {

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
