<?php

declare(strict_types=1);

namespace DCO_CA\Enums;

defined( 'ABSPATH' ) || die;

enum OptionType {

	case INT;
	case BOOL;
	case STRING;
	case ARRAY;
}
