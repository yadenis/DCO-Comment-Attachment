<?php
/**
 * Admin Area
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\AdminActions\DeleteCommentAttachmentAdminAction;
use DCO_CA\AdminActions\DeleteCommentAttachmentBulkAdminAction;
use DCO_CA\AdminActions\EditCommentAttachmentAdminAction;
use DCO_CA\Services\CommentService;
use DCO_CA\Services\PluginService;
use DCO_CA\Services\SettingsService;

defined( 'ABSPATH' ) || die;

/**
 * Handles the admin area functionality.
 *
 * @since 3.0.0
 */
final class AdminArea {

	protected const ADMIN_PAGES = [
		'edit-comments.php',
		'comment.php',
		'settings_page_dco-comment-attachment',
	];

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 *
	 * @param PluginService                          $plugin_service                              Service functions for the plugin.
	 * @param SettingsService                        $settings_service                            Service functions for settings.
	 * @param CommentService                         $comment_service                             Service functions for comments.
	 * @param DeleteCommentAttachmentAdminAction     $delete_attachment_admin_action              Delete Comment Attachment admin action.
	 * @param DeleteCommentAttachmentBulkAdminAction $delete_comment_attachment_bulk_admin_action Delete Comment Attachment Bulk admin action.
	 * @param EditCommentAttachmentAdminAction       $edit_comment_attachment_admin_action        Edit Comment Attachment admin action.
	 */
	public function __construct(
		private PluginService $plugin_service,
		private SettingsService $settings_service,
		private CommentService $comment_service,
		private DeleteCommentAttachmentAdminAction $delete_attachment_admin_action,
		private DeleteCommentAttachmentBulkAdminAction $delete_comment_attachment_bulk_admin_action,
		private EditCommentAttachmentAdminAction $edit_comment_attachment_admin_action,
	) {

		add_action( 'admin_enqueue_scripts', $this->enqueue_scripts( ... ) );

		add_filter( 'plugin_action_links_' . PluginService::BASENAME, $this->add_settings_plugin_action_link( ... ) );

		add_filter( 'comment_notification_text', $this->add_attachment_links_to_new_comment_email( ... ), 10, 2 );
		add_filter( 'comment_moderation_text', $this->add_attachment_links_to_new_comment_email( ... ), 10, 2 );
	}

	/**
	 * Enqueues scripts and styles for the admin pages.
	 *
	 * @since 3.0.0
	 *
	 * @param string $hook_suffix The current admin page id.
	 */
	public function enqueue_scripts( string $hook_suffix ): void {

		if ( $this->is_admin_page( $hook_suffix ) ) {
			$this->plugin_service->enqueue_style( 'dco-comment-attachment-admin' );
		}

		if ( $this->is_edit_comments_page( $hook_suffix ) ) {
			$this->enqueue_edit_comments_page_scripts();
		}

		if ( $this->is_edit_comment_page( $hook_suffix ) ) {
			$this->enqueue_edit_comment_page_scripts();
		}

		if ( $this->is_plugin_settings_page( $hook_suffix ) ) {
			$this->enqueue_plugin_settings_page_scripts();
		}
	}

	/**
	 * Adds a "Settings" link to the plugin action links on the Plugins page.
	 *
	 * @since 3.0.0
	 *
	 * @param array $actions The list of existing action links.
	 *
	 * @return array The updated list of action links.
	 */
	public function add_settings_plugin_action_link( array $actions ): array {

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

	/**
	 * Appends the attachment links to the email notification text for new comments.
	 *
	 * @since 3.0.0
	 *
	 * @param string $notification_text The original notification text.
	 * @param int    $comment_id        The ID of the comment.
	 *
	 * @return string The updated notification text with attachment links.
	 */
	public function add_attachment_links_to_new_comment_email( string $notification_text, int $comment_id ): string {

		$comment = $this->comment_service->get_comment_instance( $comment_id );

		if ( ! $comment || ! $comment->has_attachments() ) {
			return $notification_text;
		}

		$attachment_links = [
			"\r\n" . __( 'Attached attachments:', 'dco-comment-attachment' ),
			...wp_list_pluck( $comment->get_attachments(), 'file_url' ),
		];

		return $notification_text . implode( "\r\n- ", $attachment_links );
	}

	/**
	 * Enqueues scripts for the edit comments page.
	 *
	 * @since 3.0.0
	 */
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

	/**
	 * Enqueues scripts and styles for the edit comment page.
	 *
	 * @since 3.0.0
	 */
	private function enqueue_edit_comment_page_scripts(): void {

		wp_enqueue_media();

		$name = 'dco-comment-attachment-admin-comment';

		$this->plugin_service->enqueue_style( $name );

		$this->plugin_service->enqueue_script(
			script_name: $name,
			script_data: [
				'set_attachment_title'     => esc_attr__( 'Set Comment Attachment', 'dco-comment-attachment' ),
				'add_attachment_label'     => esc_attr__( 'Add Attachment', 'dco-comment-attachment' ),
				'replace_attachment_label' => esc_attr__( 'Replace Attachment', 'dco-comment-attachment' ),
				'comment_attachments'      => $this->generate_commment_page_comment_attachments_script_data(),
			]
		);
	}

	/**
	 * Enqueues scripts and styles for the plugin settings page.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
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

	/**
	 * Generates the script data for comment attachments on the edit comment page.
	 *
	 * @since 3.0.0
	 *
	 * @return array An array of attachment data, where each element contains
	 *               the attachment id and its corresponding HTML markup.
	 */
	private function generate_commment_page_comment_attachments_script_data(): array {

		$data = [];

		$comment = $this->comment_service->get_current_comment_instance();
		if ( ! $comment ) {
			return [];
		}

		foreach ( $comment->get_attachments() as $attachment ) {

			$data[] = [
				'id'     => $attachment->id,
				'markup' => $attachment->generate_markup(),
			];
		}

		return $data;
	}

	/**
	 * Checks if the provided page is an admin page related to the plugin.
	 *
	 * @since 3.0.0
	 *
	 * @param string $page The current admin page id.
	 *
	 * @return bool True if the page is a plugin-related admin page, false otherwise.
	 */
	private function is_admin_page( string $page ): bool {

		return in_array(
			$page,
			self::ADMIN_PAGES,
			true
		);
	}

	/**
	 * Checks if the provided page is the edit comment page.
	 *
	 * @since 3.0.0
	 *
	 * @param string $page The current admin page id.
	 *
	 * @return bool True if the page is a edit comment page, false otherwise.
	 */
	private function is_edit_comment_page( string $page ): bool {

		return 'comment.php' === $page;
	}

	/**
	 * Checks if the provided page is the edit comments page.
	 *
	 * @since 3.0.0
	 *
	 * @param string $page The current admin page id.
	 *
	 * @return bool True if the page is the edit comments page, false otherwise.
	 */
	private function is_edit_comments_page( string $page ): bool {

		return 'edit-comments.php' === $page;
	}

	/**
	 * Checks if the provided page is the plugin settings page.
	 *
	 * @since 3.0.0
	 *
	 * @param string $page The current admin page id.
	 *
	 * @return bool True if the page is the plugin settings page, false otherwise.
	 */
	private function is_plugin_settings_page( string $page ): bool {

		return 'settings_page_dco-comment-attachment' === $page;
	}
}
