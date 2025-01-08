<?php

declare(strict_types=1);

namespace DCO_CA\AdminActions;

use DCO_CA\Services\CommentService;
use DCO_CA\Services\PluginService;
use DCO_CA\Services\SettingsService;

defined( 'ABSPATH' ) || die;

final class EditCommentAttachmentAdminAction {

	public function __construct(
		private PluginService $plugin_service,
		private SettingsService $settings_service,
		private CommentService $comment_service,
	) {

		add_action( 'add_meta_boxes_comment', $this->add_edit_attachment_action_metabox( ... ) );
		add_action( 'admin_footer-comment.php', $this->add_comment_attachment_editor_template( ... ) );

		add_action( 'edit_comment', $this->update_comment_attachments( ... ) );

		add_action( 'delete_comment', $this->delete_comment_attachments( ... ) );
	}

	public function add_edit_attachment_action_metabox(): void {

		add_meta_box(
			id: 'dco-comment-attachment',
			title: esc_html__( 'Attachments', 'dco-comment-attachment' ),
			callback: $this->render_edit_attachment_action_metabox( ... ),
			screen: 'comment',
			context: 'normal'
		);
	}

	public function render_edit_attachment_action_metabox(): void {

		echo '<div class="dco-comment-attachment-editors"></div>';
	}

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

	public function update_comment_attachments( int $comment_id ): void {

		check_admin_referer( "update-comment_{$comment_id}" );

		if ( ! isset( $_POST['dco_attachment_id'] ) || ! is_array( $_POST['dco_attachment_id'] ) ) {
			return;
		}

		$attachment_ids = array_map(
			intval( ... ),
			$_POST['dco_attachment_id']
		);

		// We need to delete the last empty element, because it's used
		// as a placeholder in the attachments edit form.
		array_pop( $attachment_ids );

		$this->comment_service->attach_attachments_to_comment( $comment_id, $attachment_ids );
	}

	public function delete_comment_attachments( int $comment_id ): void {

		if ( ! $this->settings_service->is_delete_attachments_with_comment() ) {
			return;
		}

		$this->comment_service->delete_comment_attachments( $comment_id );
	}
}
