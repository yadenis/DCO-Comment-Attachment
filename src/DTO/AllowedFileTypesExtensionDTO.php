<?php

declare(strict_types=1);

namespace DCO_CA\DTO;

defined( 'ABSPATH' ) || die;

final class AllowedFileTypesExtensionDTO {

	public function __construct(
		public readonly string $extension,
		public readonly string $group,
		public readonly bool $is_allowed,
		public readonly bool $is_embedded,
		public readonly bool $is_for_administrators,
	) {
	}
}
