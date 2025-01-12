<?php
/**
 * Admin Actions: Edit Comment Attachment
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA\AdminActions;

use DCO_CA\Helpers\RequestHelper;
use DCO_CA\Services\CommentService;
use DCO_CA\Services\PluginService;
use DCO_CA\Services\SettingsService;

defined( 'ABSPATH' ) || die;

/**
 * Provides functionality to managing comment attachments
 * on the Edit Comment admin screen.
 *
 * @since 3.0.0
 */
final class EditCommentAttachmentAdminAction {

	protected const ATTACHMENT_IDS_FIELD_NAME = 'dco_attachment_id';

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 *
	 * @param PluginService   $plugin_service    Service functions for the plugin.
	 * @param SettingsService $settings_service  Service functions for settings.
	 * @param CommentService  $comment_service   Service functions for comments.
	 * @param RequestHelper   $request_helper    Helper functions for request.
	 */
	public function __construct(
		private PluginService $plugin_service,
		private SettingsService $settings_service,
		private CommentService $comment_service,
		private RequestHelper $request_helper,
	) {

		add_action( 'add_meta_boxes_comment', $this->add_edit_attachment_action_metabox( ... ) );
		add_action( 'admin_footer-comment.php', $this->add_comment_attachment_editor_template( ... ) );

		add_action( 'edit_comment', $this->update_comment_attachments( ... ) );

		add_action( 'delete_comment', $this->delete_comment_attachments( ... ) );
	}

	/**
	 * Adds a meta box for managing comment attachments on the Edit Comment admin screen.
	 *
	 * @since 3.0.0
	 */
	public function add_edit_attachment_action_metabox(): void {

		add_meta_box(
			id: 'dco-comment-attachment',
			title: esc_html__( 'Attachments', 'dco-comment-attachment' ),
			callback: $this->render_edit_attachment_action_metabox( ... ),
			screen: 'comment',
			context: 'normal'
		);
	}

	/**
	 * Renders the content of the comment attachments meta box.
	 *
	 * @since 3.0.0
	 */
	public function render_edit_attachment_action_metabox(): void {

		echo '<div class="dco-comment-attachment-editors"></div>';
	}

	/**
	 * Adds a template for the comment attachment editor.
	 *
	 * This template used to render comment attachment editors via JavaScript.
	 *
	 * @since 3.0.0
	 */
	public function add_comment_attachment_editor_template(): void {

		?>

		<template id="dco-comment-attachment-editor">
			<div class="dco-comment-attachment-editor">
				<div class="dco-comment-attachment-editor__markup dco-comment-attachment-editor-markup"></div>
				<div class="dco-comment-attachment-editor__notice dco-comment-attachment-editor-notice dco-comment-attachment-editor-notice--hidden">
					<?php
					echo wp_kses(
						__(
							'Update the comment to see a preview of <a href="#" target="_blank">the selected attachment</a>.',
							'dco-comment-attachment'
						),
						[
							'a' => [
								'href'   => true,
								'target' => true,
							],
						]
					);
					?>
				</div>
				<div class="dco-comment-attachment-editor__actions">
					<a href="#" class="dco-set-attachment button">
						<?php esc_html_e( 'Replace Attachment', 'dco-comment-attachment' ); ?>
					</a>
					<a href="#" class="dco-detach-attachment">
						<?php esc_html_e( 'Detach Attachment', 'dco-comment-attachment' ); ?>
					</a>
				</div>
				<input type="hidden" name="dco_attachment_id[]" class="dco-comment-attachment-editor-id" value="">
			</div>
		</template>

		<?php
	}

	/**
	 * Updates comment attachments after comment is updated.
	 *
	 * @since 3.0.0
	 *
	 * @param int $comment_id The id of the updated commment.
	 */
	public function update_comment_attachments( int $comment_id ): void {

		check_admin_referer( "update-comment_{$comment_id}" );

		$attachment_ids = $this->get_request_attachment_ids();
		if ( ! $attachment_ids ) {
			return;
		}

		// We need to delete the last empty element, because it's used
		// as a placeholder in the attachments edit form.
		array_pop( $attachment_ids );

		$this->comment_service->attach_attachments_to_comment( $comment_id, $attachment_ids );
	}

	/**
	 * Deletes comment attachments when the comment is deleted.
	 *
	 * Deletes or skips deleting comment attachments based on the plugin settings.
	 *
	 * @since 3.0.0
	 *
	 * @param int $comment_id The id of the deleted comment.
	 */
	public function delete_comment_attachments( int $comment_id ): void {

		if ( ! $this->settings_service->is_delete_attachments_with_comment() ) {
			return;
		}

		$this->comment_service->delete_comment_attachments( $comment_id );
	}

	/**
	 * Retrieves attachment ids from the request.
	 *
	 * @since 3.0.0
	 *
	 * @return int[]|null Attachment ids from the request,
	 *                    or null if not available.
	 */
	private function get_request_attachment_ids(): ?array {

		$delete_comments_ids = $this->request_helper->get_array_field( self::ATTACHMENT_IDS_FIELD_NAME );
		if ( ! $delete_comments_ids ) {
			return null;
		}

		return array_map(
			intval( ... ),
			$delete_comments_ids
		);
	}
}
