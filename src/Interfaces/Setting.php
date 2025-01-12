<?php

declare(strict_types=1);

namespace DCO_CA\Interfaces;

use DCO_CA\DTO\SettingsFieldDTO;

defined( 'ABSPATH' ) || die;

interface Setting {

	public function get_settings_value(): mixed;

	public function get_settings_field_dto(): SettingsFieldDTO;

	public function render_settings_field( array $args ): void;
}
