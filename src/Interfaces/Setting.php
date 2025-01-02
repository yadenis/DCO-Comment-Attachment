<?php

declare(strict_types=1);

namespace DCO_CA\Interfaces;

use DCO_CA\DTO\SettingFieldDTO;

defined( 'ABSPATH' ) || die;

interface Setting {

	public function get_value(): mixed;

	public function get_setting_field_dto(): SettingFieldDTO;

	public function render_setting_field( array $args ): void;
}
