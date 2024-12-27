<?php

declare(strict_types=1);

namespace DCO_CA\Interfaces;

defined( 'ABSPATH' ) || die;

interface FormElement {

	public function render(): void;
}
