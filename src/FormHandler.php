<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\FormHandlers\AttachmentUploadHandler;
use DCO_CA\FormHandlers\AttachmentUploadValidator;
use DCO_CA\Services\CommentService;
use DCO_CA\Services\PluginService;
use DCO_CA\Services\SettingsService;
use WP_Error;

defined( 'ABSPATH' ) || die;

final class FormHandler {

	private array $uploaded_attachments;
	private array $handled_attachment_ids = [];

	private bool $is_manually_moderation_enabled;

	public function __construct(
		private PluginService $plugin_service,
		private CommentService $comment_service,
		private SettingsService $settings_service,
		private AttachmentUploadValidator $attachment_upload_validator,
		private AttachmentUploadHandler $attachment_upload_handler,
	) {

		$this->init_uploaded_attachments();

		$this->is_manually_moderation_enabled = $this->settings_service->is_manually_moderation_enabled();

		add_filter( 'preprocess_comment', $this->validate_uploaded_attachments( ... ) );
		add_action( 'comment_post', $this->handle_uploaded_attachments( ... ), 5, 3 );
		add_filter( 'pre_comment_approved', $this->unapprove_comment_or_not( ... ) );
	}

	public function validate_uploaded_attachments( array $commentdata ): array {

		if ( ! $this->plugin_service->is_form_enabled() ) {
			return $commentdata;
		}

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
	}

	public function handle_uploaded_attachments(
		int $comment_id,
		int|string $comment_approved,
		array $commentdata
	): void {

		if ( ! $this->plugin_service->is_form_enabled() ) {
			return;
		}

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

	public function unapprove_comment_or_not( int|string|WP_Error $approved ): int|string|WP_Error {

		if ( ! $this->plugin_service->is_form_enabled() ) {
			return $approved;
		}

		if ( ! $this->handled_attachment_ids || ! $this->is_manually_moderation_enabled ) {
			return $approved;
		}

		return 0;
	}

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
