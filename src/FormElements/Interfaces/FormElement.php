<?php

declare(strict_types=1);

namespace DCO_CA\FormElements\Interfaces;

defined( 'ABSPATH' ) || die;

interface FormElement {

	public function render(): void;
}
