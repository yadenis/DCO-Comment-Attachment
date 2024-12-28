<?php

declare(strict_types=1);

namespace DCO_CA\SettingControls;

use DCO_CA\Interfaces\SettingControl;

defined( 'ABSPATH' ) || die;

final class Option implements SettingControl {

	public function __construct(
		private string $value,
		private string $text,
		private bool $selected = false,
	) {
	}

	public function render(): void {

		printf(
			'<option value="%s"%s>%s</option>',
			esc_attr( $this->value ),
			selected(
				selected: true,
				current: $this->selected,
				display: false
			),
			esc_html( $this->text )
		);
	}
}
