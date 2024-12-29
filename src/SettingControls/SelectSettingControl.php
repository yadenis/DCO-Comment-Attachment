<?php

declare(strict_types=1);

namespace DCO_CA\SettingControls;

use DCO_CA\Interfaces\SettingControl;

defined( 'ABSPATH' ) || die;

final class SelectSettingControl implements SettingControl {

	public function __construct(
		private string $name,
		private string $id,
		private array $options,
	) {
	}

	public function render(): void {

		printf(
			'<select name="%s" id="%s">',
			esc_attr( $this->name ),
			esc_attr( $this->id ),
		);

		foreach ( $this->options as $option ) {

			if ( ! $option instanceof SelectOptionSettingControl ) {
				continue;
			}

			$option->render();
		}

		echo '</select>';
	}
}
