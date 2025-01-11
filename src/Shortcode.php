<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\Services\AttachmentService;
use DCO_CA\Services\CommentService;
use DCO_CA\Services\PostService;

defined( 'ABSPATH' ) || die;

final class Shortcode {

	private const NAME = 'dco_ca';

	public function __construct(
		private PostService $post_service,
		private CommentService $comment_service,
		private AttachmentService $attachment_service,
	) {

		add_shortcode( self::NAME, self::handle( ... ) );
	}

	/**
	 * The dco_ca shortcode handler.
	 *
	 * @since 3.0.0
	 *
	 * @param array $atts {
	 *     An array of shortcode attributes.
	 *
	 *     @type int $post_id Optional. The post id. Default current post id.
	 *     @type string $type Optional. Attachment types separated by comma.
	 *                                  Accepts: all, image, video, audio, misc.
	 *                                  Default: all.
	 * }
	 */
	public function handle( $atts ): string {

		$atts = shortcode_atts(
			[
				'post_id' => $this->post_service->get_current_post_id(),
				'type'    => 'all',
			],
			$atts,
			'dco_ca'
		);

		$post_id = (int) $atts['post_id'];

		$comments = $this->comment_service->get_post_comments_with_attachments( $post_id );

		$types = 'all';
		if ( 'all' !== $atts['type'] ) {
			$types = array_map( trim( ... ), explode( ',', $atts['type'] ) );
		}

		$attachments = [];
		foreach ( $comments as $comment ) {

			foreach ( $comment->get_attachments() as $attachment ) {

				if ( 'all' !== $types && ! in_array( $attachment->embed_type, $types, true ) ) {
					continue;
				}

				$attachments[] = $attachment;
			}
		}

		ob_start();

		$this->attachment_service->render_attachments( $attachments, $post_id );

		return sprintf(
			'<div class="dco-attachments-list">%s</div>',
			ob_get_clean()
		);
	}
}
