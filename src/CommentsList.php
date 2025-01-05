<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\Services\CommentService;
use DCO_CA\Services\SettingsService;
use WP_Comment;

defined( 'ABSPATH' ) || die;

final class CommentsList {

	private bool $is_autoembed_links;

	public function __construct(
		private CommentService $comment_service,
		private SettingsService $settings_service,
	) {

		$this->is_autoembed_links = $this->settings_service->is_autoembed_links();

		add_filter( 'comment_text', $this->display_comment_attachments( ... ), 10, 2 );
		add_filter( 'comment_text', $this->autoembed_links_in_comment_text( ... ), 5 );
	}

	public function display_comment_attachments( string $comment_text, ?WP_Comment $wp_comment ): string {

		if ( ! $this->is_attachments_displayed() ) {
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

	public function autoembed_links_in_comment_text( string $comment_text ): string {

		if ( ! $this->is_autoembed_links ) {
			return $comment_text;
		}

		return $GLOBALS['wp_embed']->autoembed( $comment_text );
	}

	private function is_attachments_displayed(): bool {

		return ! (bool) apply_filters( 'dco_ca_disable_display_attachments', false );
	}
}
