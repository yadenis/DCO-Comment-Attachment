<?php

declare(strict_types=1);

namespace DCO_CA\Interfaces;

defined( 'ABSPATH' ) || die;

interface SettingControl {

	public function render(): void;
}
