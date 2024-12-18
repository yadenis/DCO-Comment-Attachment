<?php

declare(strict_types=1);

namespace DCO_CA;

defined( 'ABSPATH' ) || die;

final class Shortcode {

	private const NAME = 'dco_ca';

	public static function init(): void {

		add_shortcode( static::NAME, static::handle( ... ) );
	}

	/**
	 * The dco_ca shortcode handler.
	 *
	 * @since 3.0.0
	 *
	 * @param array $atts {
	 *     An array of shortcode attributes.
	 *
	 *     @type int $post_id Optional. The post ID. Default current post ID.
	 *     @type string $type Optional. Attachment types separated by comma.
	 *                                  Accepts: all, image, video, audio, misc.
	 *                                  Default: all.
	 * }
	 */
	public function handle( $atts ): string {

		$atts = shortcode_atts(
			pairs: [
				'post_id' => get_the_ID(),
				'type'    => 'all',
			],
			atts: $atts,
			shortcode: static::NAME
		);

		$types = array_map( 'trim', explode( ',', $atts['type'] ) );

		$comments = Comment::get_comments( $atts['post_id'] );

		$markup = '';

		foreach ( $comments as $comment ) {

			$attachments = $comment->get_attachments( $types );
		}

		if ( ! $markup ) {
			return '';
		}

		return '<div class="dco-attachments-list">' . $markup . '</div>';

		$ids = [];

		foreach ( $comments as $comment ) {

			$comment       = new Comment( $comment->comment_ID );
			$attachment_id = (array) $comment->get_attachment_id();

			if ( 'all' === $atts['type'] ) {

				$ids = array_merge( $ids, $attachment_id );
			} else {

				$types = array_map( 'trim', explode( ',', $atts['type'] ) );
				foreach ( $attachment_id as $attach_id ) {
					$type = $this->get_embed_type( $attach_id );
					if ( in_array( $type, $types, true ) ) {
						$ids[] = $attach_id;
					}
				}
			}
		}
	}
}
