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

	public function get_current_post_id(): ?int {

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return null;
		}

		return $post_id;
	}
}
