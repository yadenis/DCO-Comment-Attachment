<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\AdminActions\DeleteCommentAttachmentAdminAction;
use DCO_CA\AdminActions\DeleteCommentAttachmentBulkAdminAction;
use DCO_CA\AdminActions\EditCommentAttachmentAdminAction;
use DCO_CA\Services\CommentService;
use DCO_CA\Services\PluginService;
use DCO_CA\Services\SettingsService;

defined( 'ABSPATH' ) || die;

final class Admin {

	protected const ADMIN_PAGES = [
		'edit-comments.php',
		'comment.php',
		'settings_page_dco-comment-attachment',
	];

	public function __construct(
		private PluginService $plugin_service,
		private CommentService $comment_service,
		private SettingsService $settings_service,
		private DeleteCommentAttachmentAdminAction $delete_attachment_admin_action,
		private DeleteCommentAttachmentBulkAdminAction $delete_comment_attachment_bulk_admin_action,
		private EditCommentAttachmentAdminAction $edit_comment_attachment_admin_action,
	) {

		add_action( 'admin_enqueue_scripts', $this->enqueue_scripts( ... ) );

		add_filter( 'plugin_action_links_' . PluginService::BASENAME, $this->add_plugin_action_links( ... ) );
	}

	public function enqueue_scripts( string $hook_suffix ): void {

		if ( $this->is_admin_page( $hook_suffix ) ) {
			$this->plugin_service->enqueue_style( 'dco-comment-attachment-admin' );
		}

		if ( $this->is_edit_comments_page( $hook_suffix ) ) {
			$this->enqueue_edit_comments_page_scripts();
		}

		if ( $this->is_comment_page( $hook_suffix ) ) {
			$this->enqueue_comment_page_scripts();
		}

		if ( $this->is_plugin_settings_page( $hook_suffix ) ) {
			$this->enqueue_plugin_settings_page_scripts();
		}
	}

	public function add_plugin_action_links( array $actions ): array {

		array_unshift(
			$actions,
			sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( admin_url( 'options-general.php?page=dco-comment-attachment' ) ),
				esc_html__( 'Settings', 'dco-comment-attachment' )
			)
		);

		return $actions;
	}

	private function enqueue_edit_comments_page_scripts(): void {

		$this->plugin_service->enqueue_script(
			script_name: 'dco-comment-attachment-admin-edit-comments',
			script_data: [
				'delete_attachment_confirm_text'  => esc_attr__(
					'This action will delete the attachment from the Media Library and cannot be undone. Continue?',
					'dco-comment-attachment'
				),
				'delete_attachments_confirm_text' => esc_attr__(
					'This action will delete the attachments from the Media Library and cannot be undone. Continue?',
					'dco-comment-attachment'
				),
				'is_delete_attachment'            => boolval(
					$this->settings_service->is_delete_attachment_from_media_library()
				),
				'detach_attachment_notice'        => wp_kses(
					__( 'Attachment detached. <a href="#">Undo</a>', 'dco-comment-attachment' ),
					[ 'a' => [ 'href' => true ] ]
				),
				'detach_attachments_notice'       => wp_kses(
					__( 'Attachments detached. <a href="#">Undo</a>', 'dco-comment-attachment' ),
					[ 'a' => [ 'href' => true ] ]
				),
			]
		);
	}

	private function enqueue_comment_page_scripts(): void {

		wp_enqueue_media();

		$name = 'dco-comment-attachment-admin-comment';

		$this->plugin_service->enqueue_style( $name );

		$this->plugin_service->enqueue_script(
			script_name: $name,
			script_data: [
				'set_attachment_title'     => esc_attr__( 'Set Comment Attachment', 'dco-comment-attachment' ),
				'add_attachment_label'     => esc_attr__( 'Add Attachment', 'dco-comment-attachment' ),
				'replace_attachment_label' => esc_attr__( 'Replace Attachment', 'dco-comment-attachment' ),
				'comment_attachments'      => $this->get_commment_page_comment_attachments_script_data(),
			]
		);
	}

	private function enqueue_plugin_settings_page_scripts(): void {

		$name = 'dco-comment-attachment-admin-plugin-settings';

		$this->plugin_service->enqueue_style( $name );

		$this->plugin_service->enqueue_script(
			script_name: $name,
			script_data: [
				'show_all_label'  => esc_attr__( 'Show all', 'dco-comment-attachment' ),
				'show_less_label' => esc_attr__( 'Show less', 'dco-comment-attachment' ),
			]
		);
	}

	private function get_commment_page_comment_attachments_script_data(): array {

		$data = [];

		$comment = $this->comment_service->get_current_comment();
		if ( ! $comment ) {
			return [];
		}

		foreach ( $comment->get_attachments() as $attachment ) {

			$data[] = [
				'id'     => $attachment->id,
				'markup' => $attachment->get_markup(),
			];
		}

		return $data;
	}

	private function is_admin_page( string $page ): bool {

		return in_array(
			$page,
			self::ADMIN_PAGES,
			true
		);
	}

	private function is_comment_page( string $page ): bool {

		return 'comment.php' === $page;
	}

	private function is_edit_comments_page( string $page ): bool {

		return 'edit-comments.php' === $page;
	}

	private function is_plugin_settings_page( string $page ): bool {

		return 'settings_page_dco-comment-attachment' === $page;
	}
}
