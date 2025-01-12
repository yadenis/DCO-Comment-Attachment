<?php
/**
 * Services: Attachment
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

use DCO_CA\Entities\AttachmentEntity;
use RuntimeException;

defined( 'ABSPATH' ) || die;

/**
 * Service for handling attachment-related operations.
 *
 * @since 3.0.0
 */
final class AttachmentService {

	/**
	 * Cache of attachment instances by id.
	 *
	 * Stores the created AttachmentEntity instances
	 * to avoid repeated creation for the same attachment id.
	 *
	 * @since 3.0.0
	 *
	 * @var array<int, \DCO_CA\Entities\AttachmentEntity>
	 */
	private array $instances = [];

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 *
	 * @param PluginService   $plugin_service   Service functions for the plugin.
	 * @param SettingsService $settings_service Service functions for settings.
	 */
	public function __construct(
		private PluginService $plugin_service,
		private SettingsService $settings_service,
	) {
	}

	/**
	 * Retrieves an instance of attachment for the given attachment id.
	 *
	 * If the instance has already been created, it will be returned from the cache.
	 *
	 * @since 3.0.0
	 *
	 * @param int $attachment_id The attachment id.
	 *
	 * @return AttachmentEntity|null The attachment instance,
	 *                               or null if it doesn't exist or failed to initialize.
	 */
	public function get_attachment_instance( int $attachment_id ): ?AttachmentEntity {

		if ( isset( $this->instances[ $attachment_id ] ) ) {
			return $this->instances[ $attachment_id ];
		}

		if ( ! $this->is_attachment_exists( $attachment_id ) ) {
			return null;
		}

		try {
			$this->instances[ $attachment_id ] = new AttachmentEntity(
				$this->settings_service,
				$attachment_id
			);
		} catch ( RuntimeException $e ) {
			return null;
		}

		return $this->instances[ $attachment_id ];
	}

	/**
	 * Renders the attachments.
	 *
	 * @since 3.0.0
	 *
	 * @param AttachmentEntity[] $attachments The list of attachments to render.
	 * @param int|null           $gallery_id Gallery id for grouping attachments (optional).
	 */
	public function render_attachments( array $attachments, ?int $gallery_id = null ): void {

		$attachments = $this->filter_valid_attachments( $attachments );

		if ( ! $attachments ) {
			return;
		}

		if ( $this->should_render_attachments_as_gallery( $attachments ) ) {

			$this->render_attachments_gallery( $attachments, $gallery_id );
			return;
		}

		$this->render_attachments_list( $attachments );
	}

	/**
	 * Renders attachments as a list.
	 *
	 * @since 3.0.0
	 *
	 * @param AttachmentEntity[] $attachments The list of attachments to render.
	 */
	private function render_attachments_list( array $attachments ): void {

		if ( ! $attachments ) {
			return;
		}

		$attachments_content = array_map(
			static fn( AttachmentEntity $attachment ): string => $attachment->generate_markup(),
			$attachments
		);

		$this->plugin_service->the_kses_post(
			implode( '', $attachments_content )
		);
	}

	/**
	 * Renders attachments as a gallery.
	 *
	 * @since 3.0.0
	 *
	 * @param AttachmentEntity[] $attachments The list of attachments to render.
	 * @param int|null           $gallery_id Gallery id for grouping attachments (optional).
	 */
	private function render_attachments_gallery( array $attachments, ?int $gallery_id = null ): void {

		if ( ! $attachments ) {
			return;
		}

		$images     = [];
		$not_images = [];

		foreach ( $attachments as $attachment ) {

			if ( $attachment->is_image() ) {
				$images[] = $attachment->get_gallery_image_markup( $gallery_id );
			} else {
				$not_images[] = $attachment->generate_markup();
			}
		}

		$images = $this->wrap_images_in_gallery( $images );

		$this->plugin_service->the_kses_post(
			implode( '', array_merge( $images, $not_images ) )
		);
	}

	/**
	 * Checks if an attachment exists by its id.
	 *
	 * @since 3.0.0
	 *
	 * @param int $attachment_id The attachment id.
	 *
	 * @return bool True if the attachment exists, false otherwise.
	 */
	private function is_attachment_exists( int $attachment_id ): bool {

		return (bool) wp_get_attachment_url( $attachment_id );
	}

	/**
	 * Filters the list of attachments to include only valid AttachmentEntity instances.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed[] $attachments The list of attachments to filter.
	 *
	 * @return AttachmentEntity[] Filtered array containing only valid AttachmentEntity instances.
	 */
	private function filter_valid_attachments( array $attachments ): array {

		return array_filter(
			$attachments,
			static fn( mixed $attachment ): bool => $attachment instanceof AttachmentEntity
		);
	}

	/**
	 * Determines whether attachments should be rendered as a gallery.
	 *
	 * @since 3.0.0
	 *
	 * @param array $attachments The list of attachments to render.
	 *
	 * @return bool True if attachments should be rendered as a gallery, false otherwise.
	 */
	private function should_render_attachments_as_gallery( array $attachments ): bool {

		return count( $attachments ) > 1 && $this->settings_service->is_combined_images();
	}

	/**
	 * Wraps the provided images in a gallery container.
	 *
	 * @since 3.0.0
	 *
	 * @param array $images The list of image attachment HTML markup to be wrapped.
	 *
	 * @return array The list of images wrapped inside a gallery container.
	 */
	private function wrap_images_in_gallery( array $images ): array {

		if ( ! $images ) {
			return [];
		}

		return [
			'<div class="dco-attachment-gallery">',
			...$images,
			'</div>',
		];
	}
}
