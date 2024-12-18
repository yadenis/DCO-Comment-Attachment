<?php

declare(strict_types=1);

namespace DCO_CA\Settings\Interfaces;

defined( 'ABSPATH' ) || die;

interface Setting {

	public function get_value(): mixed;
}
