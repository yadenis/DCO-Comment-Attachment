<?php

declare(strict_types=1);

namespace DCO_CA\DTO;

use Closure;
use DCO_CA\Enums\SettingsSection;

defined( 'ABSPATH' ) || die;

final class SettingFieldDTO {

	public function __construct(
		public readonly string $id,
		public readonly string $title,
		public readonly Closure $callback,
		public readonly SettingsSection $section,
	) {
	}
}
