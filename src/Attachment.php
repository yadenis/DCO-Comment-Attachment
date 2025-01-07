<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\Enums\AttachmentEmbedType;
use DCO_CA\Enums\LinkThumbnailType;
use DCO_CA\Services\SettingsService;
use RuntimeException;

defined( 'ABSPATH' ) || die;

final class Attachment {

	public readonly string $file_path;
	public readonly string $file_url;
	public readonly string $extension;
	public readonly string $title;
	public readonly string $link;
	public readonly AttachmentEmbedType $embed_type;

	public function __construct(
		private SettingsService $settings_service,
		public readonly int $id
	) {

		$this->file_path = (string) get_attached_file( $this->id );

		if ( empty( $this->file_path ) ) {
			throw new RuntimeException( esc_html( "File path for attachment ID {$this->id} is invalid." ) );
		}

		$this->file_url  = (string) wp_get_attachment_url( $this->id );
		$this->extension = (string) wp_check_filetype( $this->file_path )['ext'];
		$this->title     = get_the_title( $this->id );

		$this->init_embed_type();
		$this->init_link();
	}

	public function get_markup(): string {

		return match ( $this->embed_type ) {
			AttachmentEmbedType::IMAGE => $this->get_image_markup(),
			AttachmentEmbedType::VIDEO => $this->get_video_markup(),
			AttachmentEmbedType::AUDIO => $this->get_audio_markup(),
			AttachmentEmbedType::MISC => $this->get_misc_markup(),
		};
	}

	public function get_gallery_image_markup(): string {

		return $this->get_image_markup( $this->settings_service->get_gallery_image_size() );
	}

	public function is_image(): bool {

		return AttachmentEmbedType::IMAGE === $this->embed_type;
	}

	private function get_image_markup( string $image_size = '' ): string {

		if ( ! $this->is_image() ) {
			return '';
		}

		$img_tag = $this->get_img_tag_markup( $image_size );

		$link_thumbnail_type = $this->settings_service->get_link_thumbnail_type();

		if ( ! is_admin() && $this->link ) {

			$img_tag = sprintf(
				'<a href="%s" class="dco-attachment-link dco-image-attachment-link"%s>%s</a>',
				esc_url( $this->link ),
				( LinkThumbnailType::IMAGE_NEW_TAB->value === $link_thumbnail_type ) ? ' target="_blank"' : '',
				$img_tag
			);
		}

		$attachment_content = sprintf(
			'<p class="dco-attachment dco-image-attachment">%s</p>',
			$img_tag
		);

		/**
		* Filters the HTML markup for the image attachment.
		*
		* @since 2.1.1
		*
		* @param string $attachment_content HTML markup for the attachment.
		* @param int $attachment_id The attachment ID.
		*/
		return apply_filters(
			'dco_ca_get_attachment_preview_image',
			$attachment_content,
			$this->id,
		);
	}

	private function get_video_markup(): string {

		if ( AttachmentEmbedType::VIDEO !== $this->embed_type ) {
			return '';
		}

		return sprintf(
			'<div class="dco-attachment dco-video-attachment">%s</div>',
			do_shortcode(
				'[video src="' . esc_url( $this->link ) . '"]'
			)
		);
	}

	private function get_audio_markup(): string {

		if ( AttachmentEmbedType::AUDIO !== $this->embed_type ) {
			return '';
		}

		return sprintf(
			'<div class="dco-attachment dco-audio-attachment">%s</div>',
			do_shortcode(
				'[audio src="' . esc_url( $this->link ) . '"]'
			)
		);
	}

	private function get_misc_markup(): string {

		if ( AttachmentEmbedType::MISC !== $this->embed_type ) {
			return '';
		}

		/**
		* Filters whether to force download misc attachments.
		*
		* @since 2.3.0
		*
		* @param bool $force_download Whether to force download misc attachments.
		*/
		$download = apply_filters( 'dco_ca_force_download_misc_attachments', false ) ? ' download' : '';

		return sprintf(
			'<p class="dco-attachment dco-misc-attachment"><a href="%s"%s>%s</a></p>',
			esc_url( $this->link ),
			$download,
			esc_html( $this->title )
		);
	}

	private function init_embed_type(): void {

		$embed_type = AttachmentEmbedType::MISC;

		if ( ! $this->settings_service->is_embeded_attachment() ) {

			$this->embed_type = $embed_type;
			return;
		}

		$types = [
			[
				'name'       => AttachmentEmbedType::IMAGE,
				'extensions' => $this->settings_service->get_image_extensions(),
			],
			[
				'name'       => AttachmentEmbedType::VIDEO,
				'extensions' => wp_get_video_extensions(),
			],
			[
				'name'       => AttachmentEmbedType::AUDIO,
				'extensions' => wp_get_audio_extensions(),
			],
		];

		foreach ( $types as $type ) {

			if ( in_array( $this->extension, $type['extensions'], true ) ) {
				$embed_type = $type['name'];
			}
		}

		$this->embed_type = $embed_type;
	}

	private function init_link(): void {

		if ( $this->is_image() ) {

			$attachment_image_url = (string) wp_get_attachment_image_url( $this->id, 'full' );

			$link_thumbnail_type = $this->settings_service->get_link_thumbnail_type();

			$this->link = match ( $link_thumbnail_type ) {
				LinkThumbnailType::NO_LINK->value         => '',
				LinkThumbnailType::IMAGE_LIGHTBOX->value  => $attachment_image_url,
				LinkThumbnailType::IMAGE_NEW_TAB->value   => $attachment_image_url,
				LinkThumbnailType::ATTACHMENT_PAGE->value => get_attachment_link( $this->id ),
			};
		} else {
			$this->link = wp_get_attachment_url( $this->id );
		}
	}

	private function get_img_tag_markup( string $image_size = '' ): string {

		if ( ! $this->is_image() ) {
			return '';
		}

		$image_size = ! empty( $image_size ) ? $image_size : $this->settings_service->get_thumbnail_image_size();

		return wp_get_attachment_image(
			$this->id,
			$image_size
		);
	}
}
