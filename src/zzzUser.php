<?php

declare(strict_types=1);

namespace DCO_CA;

use WP_User;

defined( 'ABSPATH' ) || die;

final class User {

	protected function __construct(
		private WP_User $user,
	) {
	}

	public static function get_instance( WP_User $user ): ?self {

		return new self( $user );
	}
}
