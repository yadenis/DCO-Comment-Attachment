<?php

declare(strict_types=1);

namespace DCO_CA\Services;

defined( 'ABSPATH' ) || die;

final class PostService {

	public function __construct() {
	}

	public function is_current_post_used_comments(): bool {

		return is_singular() && comments_open();
	}
}
