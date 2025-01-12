<?php
/**
 * Entities: Attachment
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA\Entities;

use DCO_CA\Enums\AttachmentEmbedType;
use DCO_CA\Enums\LinkThumbnailType;
use DCO_CA\Services\SettingsService;
use RuntimeException;

defined( 'ABSPATH' ) || die;

/**
 * Represents an attachment entity and provides functionality to render its markup.
 *
 * @since 3.0.0
 */
final class AttachmentEntity {

	/**
	 * The file path for the attachment file.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	public readonly string $file_path;

	/**
	 * The URL to the attachment file.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	public readonly string $file_url;

	/**
	 * The extension of the attachment file. (e.g., 'jpg', 'pdf').
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	public readonly string $extension;

	/**
	 * The title of the attachment.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	public readonly string $title;

	/**
	 * The link of the attachment.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	public readonly string $link;

	/**
	 * The embed type of the attachment (e.g., 'image', 'video').
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	public readonly string $embed_type;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param SettingsService $settings_service  Service functions for settings.
	 * @param int             $id                The attachment id.
	 *
	 * @throws RuntimeException If the file path for the attachment is invalid.
	 */
	public function __construct(
		private SettingsService $settings_service,
		public readonly int $id,
	) {

		$this->file_path = (string) get_attached_file( $this->id );

		if ( empty( $this->file_path ) ) {

			throw new RuntimeException(
				sprintf(
					/* translators: %d: the attachment id */
					esc_html__( 'File path for attachment id %d is invalid.', 'dco-comment-attachment' ),
					intval( $this->id )
				)
			);
		}

		$this->file_url  = (string) wp_get_attachment_url( $this->id );
		$this->extension = (string) wp_check_filetype( $this->file_path )['ext'];
		$this->title     = get_the_title( $this->id );

		$this->init_embed_type();
		$this->init_link();
	}

	/**
	 * Generates the appropriate attachment markup, based on its embed type.
	 *
	 * @since 3.0.0
	 *
	 * @return string The HTML attachment markup.
	 */
	public function generate_markup(): string {

		return match ( $this->embed_type ) {
			AttachmentEmbedType::IMAGE->value => $this->generate_image_markup(),
			AttachmentEmbedType::VIDEO->value => $this->generate_video_markup(),
			AttachmentEmbedType::AUDIO->value => $this->generate_audio_markup(),
			AttachmentEmbedType::MISC->value => $this->generate_misc_markup(),
		};
	}

	/**
	 * Generates the image markup for a gallery view.
	 *
	 * @since 3.0.0
	 *
	 * @param int $gallery_id The gallery id the image attachment belongs to.
	 *
	 * @return string The HTML image attachment markup for a gallery view.
	 */
	public function get_gallery_image_markup( int $gallery_id ): string {

		return $this->generate_image_markup(
			$this->settings_service->get_gallery_image_size(),
			$gallery_id
		);
	}

	/**
	 * Whether the attachment is an image.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if the attachment is an image, false otherwise.
	 */
	public function is_image(): bool {

		return AttachmentEmbedType::IMAGE->value === $this->embed_type;
	}

	/**
	 * Generates the image attachment markup.
	 *
	 * @since 3.0.0
	 *
	 * @param string   $image_size The image thumbnail size (optional).
	 * @param int|null $gallery_id The gallery id the image attachment belongs to (optional).
	 *
	 * @return string The HTML image attachment markup.
	 */
	private function generate_image_markup( string $image_size = '', ?int $gallery_id = null ): string {

		$attachment_content = sprintf(
			'<p class="dco-attachment dco-image-attachment">%s</p>',
			$this->generate_img_tag_markup( $image_size, $gallery_id )
		);

		/**
		* Filters the HTML markup for the image attachment.
		*
		* @since 2.1.1
		*
		* @param string $attachment_content HTML markup for the attachment.
		* @param int $attachment_id The attachment id.
		*/
		return apply_filters(
			'dco_ca_get_attachment_preview_image',
			$attachment_content,
			$this->id,
		);
	}

	/**
	 * Generates the video attachment markup.
	 *
	 * @since 3.0.0
	 *
	 * @return string The HTML video attachment markup.
	 */
	private function generate_video_markup(): string {

		$video_shortcode = sprintf(
			'[video src="%s"]',
			esc_url( $this->link )
		);

		return sprintf(
			'<div class="dco-attachment dco-video-attachment">%s</div>',
			do_shortcode( $video_shortcode )
		);
	}

	/**
	 * Generates the audio attachment markup.
	 *
	 * @since 3.0.0
	 *
	 * @return string The HTML audio attachment markup.
	 */
	private function generate_audio_markup(): string {

		$audio_shortcode = sprintf(
			'[audio src="%s"]',
			esc_url( $this->link )
		);

		return sprintf(
			'<div class="dco-attachment dco-audio-attachment">%s</div>',
			do_shortcode( $audio_shortcode )
		);
	}

	/**
	 * Generates the misc attachment markup.
	 *
	 * @since 3.0.0
	 *
	 * @return string The HTML misc attachment markup.
	 */
	private function generate_misc_markup(): string {

		/**
		* Filters whether to force download misc attachments.
		*
		* @since 2.3.0
		*
		* @param bool $force_download Whether to force download misc attachments.
		*/
		$force_download = (bool) apply_filters( 'dco_ca_force_download_misc_attachments', false );

		return sprintf(
			'<p class="dco-attachment dco-misc-attachment"><a href="%s"%s>%s</a></p>',
			esc_url( $this->link ),
			$force_download ? ' download' : '',
			esc_html( $this->title )
		);
	}

	/**
	 * Generates the img tag attachment markup.
	 *
	 * Wraps the img tag with an attachment link if wrapping is possible.
	 *
	 * @since 3.0.0
	 *
	 * @param string   $image_size The image thumbnail size (optional).
	 * @param int|null $gallery_id The gallery id the image attachment belongs to (optional).
	 *
	 * @return string The HTML img tag attachment markup, wrapped with a link if available,
	 *                or the plain img tag.
	 */
	private function generate_img_tag_markup( string $image_size = '', ?int $gallery_id = null ): string {

		$image_size = $image_size ?: $this->settings_service->get_thumbnail_image_size();

		$img_tag = wp_get_attachment_image( $this->id, $image_size );

		$img_tag = $this->wrap_img_tag_with_link( $img_tag, $gallery_id );

		return $img_tag;
	}

	/**
	 * Wraps the img tag attachment markup with an attachment link.
	 *
	 * Wraps the img tag with a link only in the non-admin area and if a link is available.
	 * Otherwise, returns the plain img tag.
	 *
	 * @since 3.0.0
	 *
	 * @param string   $img_tag    The img tag attachment markup.
	 * @param int|null $gallery_id The gallery id the image attachment belongs to (optional).
	 *
	 * @return string The img tag attachment markup wrapped in a link,
	 *                or the plain img tag if wrapping is not possible.
	 */
	private function wrap_img_tag_with_link( string $img_tag, ?int $gallery_id = null ): string {

		if ( ! $this->link || is_admin() ) {
			return $img_tag;
		}

		$link_thumbnail_type = $this->settings_service->get_link_thumbnail_type();

		$linked_img_tag = sprintf(
			'<a href="%s" class="dco-attachment-link dco-image-attachment-link"%s>%s</a>',
			esc_url( $this->link ),
			( LinkThumbnailType::IMAGE_NEW_TAB->value === $link_thumbnail_type ) ? ' target="_blank"' : '',
			$img_tag
		);

		if ( LinkThumbnailType::IMAGE_LIGHTBOX->value === $link_thumbnail_type ) {
			$linked_img_tag = $this->add_lightbox_attributes( $linked_img_tag, $gallery_id );
		}

		return $linked_img_tag;
	}

	/**
	 * Adds lightbox attributes to the linked img tag attachment markup.
	 *
	 * @since 3.0.0
	 *
	 * @param string   $linked_img_tag The linked img tag attachment markup.
	 * @param int|null $gallery_id The gallery id the image attachment belongs to (optional).
	 *
	 * @return string The img tag attachment markup with lightbox attributes.
	 */
	private function add_lightbox_attributes( string $linked_img_tag, ?int $gallery_id = null ): string {

		$gallery_id ??= $this->id;

		// Simple Lightbox.
		if ( function_exists( 'slb_activate' ) ) {
			return slb_activate( $linked_img_tag, $gallery_id );
		}

		// Responsive Lightbox & Gallery.
		if ( function_exists( 'Responsive_Lightbox' ) ) {
			$selector = Responsive_Lightbox()->options['settings']['selector'];
			$rel      = "{$selector}-gallery-{$gallery_id}";
			return str_replace( '<a', '<a data-rel="' . $rel . '"', $linked_img_tag );
		}

		// Other lightbox plugins.
		$rel            = "dco-ca-gallery-{$gallery_id}";
		$linked_img_tag = str_replace( '<a', '<a rel="' . $rel . '"', $linked_img_tag );

		// FooBox Image Lightbox.
		if ( class_exists( 'FooBox' ) ) {
			$linked_img_tag = str_replace( '<a', '<a class="foobox"', $linked_img_tag );
		}

		return $linked_img_tag;
	}

	/**
	 * Initializes the attachment embed type based on its extension and the plugin settings.
	 *
	 * @since 3.0.0
	 */
	private function init_embed_type(): void {

		// The $this->embed_type property is read-only,
		// so the $embed_type variable is used to store the default value.
		$embed_type = AttachmentEmbedType::MISC->value;

		if ( ! $this->settings_service->is_embeded_attachment() ) {

			$this->embed_type = $embed_type;
			return;
		}

		$types = [
			AttachmentEmbedType::IMAGE->value => $this->settings_service->get_image_extensions(),
			AttachmentEmbedType::VIDEO->value => wp_get_video_extensions(),
			AttachmentEmbedType::AUDIO->value => wp_get_audio_extensions(),
		];

		foreach ( $types as $name => $extensions ) {

			if ( in_array( $this->extension, $extensions, true ) ) {
				$this->embed_type = $name;
				return;
			}
		}

		$this->embed_type = $embed_type;
	}

	/**
	 * Initializes the attachment link based on its embed type and the plugin settings.
	 *
	 * @since 3.0.0
	 */
	private function init_link(): void {

		if ( $this->is_image() ) {

			$attachment_image_url = (string) wp_get_attachment_image_url( $this->id, 'full' );

			$link_thumbnail_type = $this->settings_service->get_link_thumbnail_type();

			$this->link = match ( $link_thumbnail_type ) {
				LinkThumbnailType::NO_LINK->value         => '',
				LinkThumbnailType::IMAGE_LIGHTBOX->value,
				LinkThumbnailType::IMAGE_NEW_TAB->value   => $attachment_image_url,
				LinkThumbnailType::ATTACHMENT_PAGE->value => get_attachment_link( $this->id ),
			};
		} else {
			$this->link = wp_get_attachment_url( $this->id );
		}
	}
}
