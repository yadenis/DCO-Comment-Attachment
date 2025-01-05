<?php

declare(strict_types=1);

namespace DCO_CA\AdminActions;

use DCO_CA\Attachment;
use DCO_CA\Services\CommentService;
use DCO_CA\Services\PluginService;

defined( 'ABSPATH' ) || die;

final class EditCommentAttachmentAdminAction {

	public function __construct(
		private PluginService $plugin_service,
		private CommentService $comment_service,
	) {

		add_action( 'add_meta_boxes_comment', $this->add_edit_attachment_action_metabox( ... ) );
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

		?>

		<div class="dco-edit-attachment-item">
			<div class="dco-edit-attachment-item__markup dco-edit-attachment-item-markup">
			<div class="dco-edit-attachment-item__notice dco-edit-attachment-item-notice">
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
			<div class="dco-edit-attachment-item__actions">
				<a href="#" class="dco-set-attachment button">
					<?php esc_html_e( 'Replace Attachment', 'dco-comment-attachment' ); ?>
				</a>
				<a href="#" class="dco-detach-attachment">
					<?php esc_html_e( 'Detach Attachment', 'dco-comment-attachment' ); ?>
				</a>
			</div>
			<input type="hidden" name="dco_attachment_id[]" class="dco-edit-attachment-item-id" value="">
		</div>

		<?php
	}
}
