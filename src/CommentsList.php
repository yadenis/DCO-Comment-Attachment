<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\Services\CommentService;
use WP_Comment;

defined( 'ABSPATH' ) || die;

final class CommentsList {

	public function __construct(
		private CommentService $comment_service,
	) {

		add_filter( 'comment_text', $this->display_attachments( ... ), 10, 2 );
	}

	public function display_attachments( string $comment_text, ?WP_Comment $wp_comment ): string {

		if ( ! $this->is_attachments_displayed() ) {
			return $comment_text;
		}

		$comment = $this->comment_service->get_current_comment( $wp_comment );
		if ( ! $comment ) {
			return $comment_text;
		}

		ob_start();

		$comment->render_attachments();

		return $comment_text . ob_get_clean();
	}

	private function is_attachments_displayed(): bool {

		return ! (bool) apply_filters( 'dco_ca_disable_display_attachments', false );
	}
}
