<?php
/**
 * Services: Post
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA\Services;

defined( 'ABSPATH' ) || die;

/**
 * Service for handling post-related operations.
 *
 * @since 3.0.0
 */
final class PostService {

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
	}

	/**
	 * Checks if the current post supports comments.
	 *
	 * Determines whether the query is for an existing single post of any post type
	 * and if comments are open for it.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True If the current post supports comments, false otherwise.
	 */
	public function is_current_post_used_comments(): bool {

		return is_single() && comments_open();
	}

	/**
	 * Retrieves the id of the current post.
	 *
	 * @since 3.0.0
	 *
	 * @return int|null The current post id, or null if no post is available.
	 */
	public function get_current_post_id(): ?int {

		return get_the_ID() ?: null;
	}
}
