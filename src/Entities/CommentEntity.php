<?php
/**
 * Entities: Comment
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

use DCO_CA\Services\AttachmentService;
use DCO_CA\Services\PluginService;
use DCO_CA\Services\SettingsService;
use WP_Comment;

defined( 'ABSPATH' ) || die;

/**
 * Represents a comment entity and provides functionality to manipulate it.
 *
 * @since 3.0.0
 */
final class CommentEntity {

	/**
	 * List of comment attachments.
	 *
	 * @since 3.0.0
	 *
	 * @var array $attachments
	 */
	private array $attachments;

	/**
	 * List of attachments to be deleted.
	 *
	 * @since 3.0.0
	 *
	 * @var array $attachments_to_delete
	 */
	private array $attachments_to_delete = [];

	/**
	 * The comment id.
	 *
	 * @since 3.0.0
	 *
	 * @var int $id
	 */
	public readonly int $id;

	/**
	 * The post id the comment belongs to.
	 *
	 * @since 3.0.0
	 *
	 * @var int $post_id
	 */
	public readonly int $post_id;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param PluginService     $plugin_service      Service functions for the plugin.
	 * @param SettingsService   $settings_service    Service functions for settings.
	 * @param AttachmentService $attachment_service  Service functions for attachments.
	 * @param WP_Comment        $wp_comment          The WordPress comment instance.
	 */
	public function __construct(
		private PluginService $plugin_service,
		private SettingsService $settings_service,
		private AttachmentService $attachment_service,
		private WP_Comment $wp_comment
	) {

		$this->id      = (int) $this->wp_comment->comment_ID;
		$this->post_id = (int) $this->wp_comment->comment_post_ID;

		$this->init_attachments();
	}

	/**
	 * Gets the list of comment attachments.
	 *
	 * @since 3.0.0
	 *
	 * @return array The list of comment attachments.
	 */
	public function get_attachments(): array {

		return $this->attachments;
	}

	/**
	 * Renders the comment attachments.
	 *
	 * @since 3.0.0
	 */
	public function render_attachments(): void {

		if ( ! $this->has_attachments() ) {
			return;
		}

		$this->attachment_service->render_attachments( $this->get_attachments(), $this->id );
	}

	/**
	 * Checks if the comment has attachments.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if the comment has attachments, false otherwise.
	 */
	public function has_attachments(): bool {

		return (bool) count( $this->attachments );
	}

	/**
	 * Checks if the comment has exactly one attachment.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if the comment has exactly one attachment, false otherwise.
	 */
	public function has_one_attachment(): bool {

		return 1 === count( $this->attachments );
	}

	/**
	 * Sets the comment attachments.
	 *
	 * @since 3.0.0
	 *
	 * @param array $attachment_ids The list of attachment IDs.
	 */
	public function set_attachment_ids( array $attachment_ids ): void {

		$this->attachments = array_filter(
			array_map(
				fn( mixed $attachment_id ): ?AttachmentEntity => $this->attachment_service->get_attachment_instance( (int) $attachment_id ),
				$attachment_ids
			),
			fn( ?AttachmentEntity $attachment ): bool => null !== $attachment
		);
	}

	/**
	 * Detaches all comment attachments.
	 *
	 * The attachments are detached, but the changes will not be saved to the database
	 * until the `save()` method is called.
	 *
	 * @since 3.0.0
	 */
	public function detach_attachments(): void {

		$this->attachments = [];
	}

	/**
	 * Deletes all comment attachments.
	 *
	 * The attachments are detached and marked for deletion,
	 * but the changes will not be saved to the database nor the attachments deleted
	 * until the `save()` method is called.
	 *
	 * @since 3.0.0
	 */
	public function delete_attachments(): void {

		$this->attachments_to_delete = $this->attachments;

		$this->attachments = [];
	}

	/**
	 * Saves the current state of the comment instance to the database.
	 *
	 * Deletes the attachments that are marked for deletion and
	 * updates the comment attachments list.
	 *
	 * @since 3.0.0
	 */
	public function save(): void {

		$this->handle_attachments_to_delete();

		$attachments = $this->has_attachments() ? wp_list_pluck( $this->attachments, 'id' ) : '';

		// Compatibility with 1.x version.
		if ( $this->has_one_attachment() ) {
			$attachments = current( $attachments );
		}

		if ( $attachments ) {
			update_comment_meta( $this->id, PluginService::ATTACHMENT_ID_META_KEY, $attachments );
		} else {
			delete_comment_meta( $this->id, PluginService::ATTACHMENT_ID_META_KEY );
		}
	}

	/**
	 * Handles the deletion of attachments that were marked for deletion.
	 *
	 * @since 3.0.0
	 */
	private function handle_attachments_to_delete(): void {

		foreach ( $this->attachments_to_delete as $attachment ) {

			wp_delete_attachment( $attachment->id );
		}
	}

	/**
	 * Initializes the comment attachments list.
	 *
	 * @since 3.0.0
	 */
	private function init_attachments(): void {

		$ids = get_comment_meta( $this->id, PluginService::ATTACHMENT_ID_META_KEY, single: true );

		if ( ! $ids ) {

			$this->attachments = [];
			return;
		}

		$this->set_attachment_ids( (array) $ids );
	}
}
