<?php

declare(strict_types=1);

namespace DCO_CA\SettingControls;

use DCO_CA\Interfaces\SettingControl;

defined( 'ABSPATH' ) || die;

final class DescriptionSettingControl implements SettingControl {

	public function __construct(
		private string $text,
	) {
	}

	public function render(): void {

		printf(
			'<p class="dco-description">%s</p>',
			wp_kses( $this->text, [ 'br' => [] ] )
		);
	}
}
