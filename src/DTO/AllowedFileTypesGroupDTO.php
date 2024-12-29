<?php

declare(strict_types=1);

namespace DCO_CA\DTO;

defined( 'ABSPATH' ) || die;

final class AllowedFileTypesGroupDTO {

	public function __construct(
		public readonly string $name,
		public readonly string $title,
		public readonly array $extensions,
	) {
	}
}
