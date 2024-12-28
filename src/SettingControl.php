<?php

declare(strict_types=1);

namespace DCO_CA;

defined( 'ABSPATH' ) || die;

final class SettingControl {

	public function __construct(
		private string $name,
		private string $id,
		private bool $value,
		private string $description = '',
	) {
	}

	public function render_checkbox(): void {

		printf(
			'<input type="hidden" name="%s" value="0">',
			esc_attr( $this->name )
		);

		printf(
			'<input type="checkbox" name="%s" id="%s" value="1" %s>',
			esc_attr( $this->name ),
			esc_attr( $this->id ),
			checked(
				checked: true,
				current: $this->value,
				display: false
			)
		);

		$this->render_description();
	}

	private function render_description(): void {

		if ( ! $this->description ) {
			return;
		}

		printf(
			'<p class="description">%s</p>',
			wp_kses( $this->description, [ 'br' => [] ] )
		);
	}
}
