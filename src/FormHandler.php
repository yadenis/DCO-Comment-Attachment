<?php
/**
 * Form Handler
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

use DCO_CA\FormHandlers\AttachmentUploadHandler;
use DCO_CA\FormHandlers\AttachmentUploadValidator;
use DCO_CA\Services\CommentService;
use DCO_CA\Services\PluginService;
use DCO_CA\Services\SettingsService;
use WP_Error;

defined( 'ABSPATH' ) || die;

/**
 * Handles the form submissions functionality.
 *
 * @since 3.0.0
 */
final class FormHandler {

	/**
	 * The list of uploaded attachments.
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	private array $uploaded_attachments;

	/**
	 * The list of processed attachment ids.
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	private array $handled_attachment_ids = [];

	/**
	 * Whether manual moderation is enabled.
	 *
	 * @since 3.0.0
	 *
	 * @var bool
	 */
	private bool $is_manually_moderation_enabled;

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 *
	 * @param PluginService             $plugin_service              Service functions for the plugin.
	 * @param SettingsService           $settings_service            Service functions for settings.
	 * @param CommentService            $comment_service             Service functions for comments.
	 * @param AttachmentUploadValidator $attachment_upload_validator Attachment upload validator.
	 * @param AttachmentUploadHandler   $attachment_upload_handler   Attachment upload handler.
	 */
	public function __construct(
		private PluginService $plugin_service,
		private SettingsService $settings_service,
		private CommentService $comment_service,
		private AttachmentUploadValidator $attachment_upload_validator,
		private AttachmentUploadHandler $attachment_upload_handler,
	) {

		$this->init_uploaded_attachments();

		$this->is_manually_moderation_enabled = $this->settings_service->is_manually_moderation_enabled();

		add_filter( 'preprocess_comment', $this->validate_uploaded_attachments( ... ) );
		add_action( 'comment_post', $this->handle_uploaded_attachments( ... ), 5 );
		add_filter( 'pre_comment_approved', $this->unapprove_comment_or_not( ... ) );
	}

	/**
	 * Validates uploaded attachments before the comment is saved.
	 *
	 * Displays an error and stops submission if validation fails.
	 *
	 * @since 3.0.0
	 *
	 * @param array $commentdata Comment data.
	 *
	 * @return array Validated comment data.
	 */
	public function validate_uploaded_attachments( array $commentdata ): array {

		$validated = $this->attachment_upload_validator->validate( $this->uploaded_attachments );

		if ( ! is_wp_error( $validated ) ) {
			return $commentdata;
		}

		wp_die(
			sprintf(
				'<p><strong>%s</strong>: %s</p>',
				esc_html__( 'ERROR', 'dco-comment-attachment' ),
				esc_html( $validated->get_error_message() )
			),
			esc_html__( 'Comment Submission Failure', 'dco-comment-attachment' ),
			[ 'back_link' => true ]
		);

		return $commentdata;
	}

	/**
	 * Handles file uploads and associates attachments with a comment.
	 *
	 * @since 3.0.0
	 *
	 * @param int $comment_id Comment ID.
	 *
	 * @return void
	 */
	public function handle_uploaded_attachments( int $comment_id ): void {

		$comment = $this->comment_service->get_comment_instance( $comment_id );
		if ( ! $comment ) {
			return;
		}

		$this->handled_attachment_ids = $this->attachment_upload_handler->handle(
			$this->uploaded_attachments,
			$comment->post_id
		);

		if ( ! $this->handled_attachment_ids ) {
			return;
		}

		$this->comment_service->attach_attachments_to_comment(
			$comment->id,
			$this->handled_attachment_ids
		);
	}

	/**
	 * Changes the approval status of the comment to "unapproved"
	 * if manual moderation is enabled and attachments were uploaded.
	 *
	 * @since 3.0.0
	 *
	 * @param int|string|WP_Error $approved Approval status.
	 *
	 * @return int|string|WP_Error Processed approval status.
	 */
	public function unapprove_comment_or_not( int|string|WP_Error $approved ): int|string|WP_Error {

		if ( ! $this->plugin_service->is_form_enabled() ) {
			return $approved;
		}

		if ( ! $this->handled_attachment_ids || ! $this->is_manually_moderation_enabled ) {
			return $approved;
		}

		return 0;
	}

	/**
	 * Initializes the uploaded attachments from $_FILES.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function init_uploaded_attachments(): void {

		// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( empty( $_FILES[ PluginService::UPLOAD_FIELD_NAME ] ) ) {
			$this->uploaded_attachments = [];
			return;
		}

		$files = $_FILES[ PluginService::UPLOAD_FIELD_NAME ];

		if ( ! is_array( $files ) ) {
			$this->uploaded_attachments = [];
			return;
		}

		$this->uploaded_attachments = $files;

		// phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}
}
