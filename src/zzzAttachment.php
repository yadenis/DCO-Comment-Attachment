<?php

declare(strict_types=1);

namespace DCO_CA;

defined( 'ABSPATH' ) || die;

final class Attachment extends Instance {

	public const IMAGE_EXTENSIONS = [ 'jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp' ];

	private int $id;
	private string $filepath;
	private string $extension;
	private ?string $embed_type = null;

	private function __construct( int $id ) {

		$this->id         = $id;
		$this->filepath   = (string) get_attached_file( $this->id );
		$this->extension  = (string) wp_check_filetype( $this->filepath )['ext'];
		$this->embed_type = $this->get_embed_type();
	}

	public static function get_instance(
		int|string $attachment_id = 0
	): static|null {

		$attachment_id = (int) $attachment_id;

		$url = wp_get_attachment_url( $attachment_id );
		if ( ! $url ) {
			return null;
		}

		return new static( $attachment_id );
	}

	private function get_embed_type() {

		if ( null !== $this->embed_type ) {
			return $this->embed_type;
		}

		if ( ! Options::get_bool_option( 'embed_attachment' ) ) {
			return 'misc';
		}

		if ( in_array( $this->extension, static::IMAGE_EXTENSIONS, true ) ) {
			return 'image';
		}

		if ( in_array( $this->extension, wp_get_video_extensions(), true ) ) {
			return 'video';
		}

		if ( in_array( $this->extension, wp_get_audio_extensions(), true ) ) {
			return 'audio';
		}

		return 'misc';
	}

	private function get_image( string $size ): string {

		return wp_get_attachment_image( $this->id, $size );
	}

	public function generate_markup() {

		switch ( $this->embed_type ) {
			case 'image':
				$thumbnail_size = Options::get_string_option( 'thumbnail_size' );
				if ( is_admin() ) {
					/**
					 * Filters the attachment image size for the admin panel.
					 *
					 * @since 2.0.0
					 *
					 * @param string $size The thumbnail size of the attachment image.
					 */
					$thumbnail_size = apply_filters( 'dco_ca_admin_thumbnail_size', 'medium' );
				}

				$img = $this->get_image( $thumbnail_size );

				/**
				 * 0 — No link
				 * 1 — Link to a full-size image with lightbox plugins support
				 * 2 — Link to a full-size image in a new tab
				 * 3 — Link to the attachment page
				 */
				$link_thumbnail = Options::get_int_option( 'link_thumbnail' );
				if ( ! is_admin() && $link_thumbnail ) {
					$tab = '';
					if ( 2 === $link_thumbnail ) {
						$tab = ' target="_blank"';
					}

					if ( in_array( $link_thumbnail, array( 1, 2 ), true ) ) {
						$link = wp_get_attachment_image_url( $attachment_id, 'full' );
					} else {
						$link = get_attachment_link( $attachment_id );
					}

					$img = '<a href="' . esc_url( $link ) . '" class="dco-attachment-link dco-image-attachment-link"' . $tab . '>' . $img . '</a>';
					if ( 1 === $link_thumbnail ) {
						$img = $this->activate_lightbox( $img );
					}
				}

				$attachment_content = '<p class="dco-attachment dco-image-attachment">' . $img . '</p>';

				/**
				* Filters the HTML markup for the image attachment.
				*
				* @since 2.1.1
				*
				* @param string $attachment_content HTML markup for the attachment.
				* @param int $attachment_id The attachment ID.
				* @param string $thumbnail_size The thumbnail size of the attachment image.
				*/
				$attachment_content = apply_filters( 'dco_ca_get_attachment_preview_image', $attachment_content, $attachment_id, $thumbnail_size );

				break;
			case 'video':
				$attachment_content = '<div class="dco-attachment dco-video-attachment">' . do_shortcode( '[video src="' . esc_url( $url ) . '"]' ) . '</div>';
				break;
			case 'audio':
				$attachment_content = '<div class="dco-attachment dco-audio-attachment">' . do_shortcode( '[audio src="' . esc_url( $url ) . '"]' ) . '</div>';
				break;
			case 'misc':
				$download = '';

				/**
				* Filters whether to force download misc attachments.
				*
				* @since 2.3.0
				*
				* @param bool $force_download Whether to force download misc attachments.
				*/
				if ( apply_filters( 'dco_ca_force_download_misc_attachments', false ) ) {
					$download = ' download';
				}

				$title              = get_the_title( $attachment_id );
				$attachment_content = '<p class="dco-attachment dco-misc-attachment"><a href="' . esc_url( $url ) . '"' . $download . '>' . esc_html( $title ) . '</a></p>';
		}
	}
}
