<?php
/**
 * Comments List
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\Services\CommentService;
use DCO_CA\Services\SettingsService;
use WP_Comment;

defined( 'ABSPATH' ) || die;

/**
 * Handles the comments list functionality.
 *
 * @since 3.0.0
 */
final class CommentsList {

	/**
	 * Whether auto-embedding of links is enabled.
	 *
	 * @since 3.0.0
	 *
	 * @var bool True if auto-embedding of links is enabled, false otherwise.
	 */
	private bool $is_autoembed_links;

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 *
	 * @param SettingsService $settings_service Service functions for settings.
	 * @param CommentService  $comment_service  Service functions for comments.
	 */
	public function __construct(
		private SettingsService $settings_service,
		private CommentService $comment_service,
	) {

		$this->is_autoembed_links = $this->settings_service->is_autoembed_links();

		add_filter( 'comment_text', $this->display_comment_attachments( ... ), 10, 2 );
		add_filter( 'comment_text', $this->autoembed_links_in_comment_text( ... ), 5 );
	}

	/**
	 * Displays comment attachments below the comment text.
	 *
	 * @since 3.0.0
	 *
	 * @param string          $comment_text The original comment text.
	 * @param WP_Comment|null $wp_comment The comment object. Null if not found.
	 *
	 * @return string The modified comment text with the attachments rendered at the end.
	 */
	public function display_comment_attachments( string $comment_text, ?WP_Comment $wp_comment ): string {

		if ( ! $wp_comment || ! $this->is_attachments_displayed() ) {
			return $comment_text;
		}

		$comment = $this->comment_service->get_comment_instance( $wp_comment );
		if ( ! $comment ) {
			return $comment_text;
		}

		ob_start();

		$comment->render_attachments();

		return $comment_text . ob_get_clean();
	}

	/**
	 * Automatically embed links in comment text.
	 *
	 * @since 3.0.0
	 *
	 * @param string $comment_text The original comment text.
	 *
	 * @return string The comment text with embedded links.
	 */
	public function autoembed_links_in_comment_text( string $comment_text ): string {

		if ( ! $this->is_autoembed_links ) {
			return $comment_text;
		}

		return $GLOBALS['wp_embed']->autoembed( $comment_text );
	}

	/**
	 * Checks if comment attachments should be displayed.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if attachments should be displayed, false otherwise.
	 */
	private function is_attachments_displayed(): bool {

		return ! (bool) apply_filters( 'dco_ca_disable_display_attachments', false );
	}
}
