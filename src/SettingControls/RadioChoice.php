<?php

declare(strict_types=1);

namespace DCO_CA\SettingControls;

use DCO_CA\Interfaces\SettingControl;

defined( 'ABSPATH' ) || die;

final class RadioChoice implements SettingControl {

	public function __construct(
		private string $name,
		private string $value,
		private string $text,
		private bool $checked = false,
	) {
	}

	public function render(): void {

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
