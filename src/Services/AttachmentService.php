<?php

declare(strict_types=1);

namespace DCO_CA\Services;

use DCO_CA\Entities\AttachmentEntity;
use RuntimeException;

defined( 'ABSPATH' ) || die;

final class AttachmentService {

	public function __construct(
		private PluginService $plugin_service,
		private SettingsService $settings_service,
	) {
	}

	public function get_attachment_instance( int $attachment_id ): ?AttachmentEntity {

		if ( ! $this->is_attachment_exists( $attachment_id ) ) {
			return null;
		}

		try {
			return new AttachmentEntity(
				$this->settings_service,
				$attachment_id
			);
		} catch ( RuntimeException $e ) {
			return null;
		}
	}

	public function render_attachments( array $attachments, ?int $gallery_id = null ): void {

		$attachments = array_filter(
			$attachments,
			fn( mixed $attachment ): bool => $attachment instanceof AttachmentEntity
		);

		if ( ! $attachments ) {
			return;
		}

		if ( count( $attachments ) > 1 && $this->settings_service->is_combined_images() ) {

			$this->render_attachments_gallery( $attachments, $gallery_id );
			return;
		}

		$this->render_attachments_list( $attachments );
	}

	private function render_attachments_list( array $attachments ): void {

		$attachments_content = [];

		foreach ( $attachments as $attachment ) {

			$attachments_content[] = $attachment->generate_markup();
		}

		$this->plugin_service->the_kses_post(
			implode( '', $attachments_content )
		);
	}

	private function render_attachments_gallery( array $attachments, ?int $gallery_id = null ): void {

		$images     = [];
		$not_images = [];

		foreach ( $attachments as $attachment ) {

			if ( $attachment->is_image() ) {
				$images[] = $attachment->get_gallery_image_markup( $gallery_id );
			} else {
				$not_images[] = $attachment->generate_markup();
			}
		}

		// wraps gallery.
		array_unshift( $images, '<div class="dco-attachment-gallery">' );
		$images[] = '</div>';

		$this->plugin_service->the_kses_post(
			implode( '', array_merge( $images, $not_images ) )
		);
	}

	private function is_attachment_exists( int $attachment_id ): bool {

		return (bool) wp_get_attachment_url( $attachment_id );
	}
}
