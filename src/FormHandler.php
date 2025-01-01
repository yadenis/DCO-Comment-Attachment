<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\Services\CommentService;
use DCO_CA\Services\PluginService;
use DCO_CA\Settings\AllowedFileTypesSetting;

defined( 'ABSPATH' ) || die;

final class FormHandler {

	private array $attachments;

	public function __construct(
		private PluginService $plugin_service,
		private CommentService $comment_service,
		private AttachmentUploadValidator $attachment_upload_validator,
		private AttachmentUploader $attachment_uploader,
		private AllowedFileTypesSetting $allowed_file_types_setting,
	) {

		$this->attachments = $this->get_attachments();

		add_filter( 'preprocess_comment', $this->check_attachment( ... ) );
		add_action( 'comment_post', $this->save_attachment( ... ), 5, 3 );
	}

	public function check_attachment( array $commentdata ): array {

		$validated = $this->attachment_upload_validator->validate( $this->attachments );

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

	public function save_attachment( $comment_id, $comment_approved, $commentdata ): void {

		$attachments_ids = $this->attachment_uploader->upload(
			$this->attachments,
			intval( $commentdata['comment_post_ID'] )
		);

		$this->comment_service->attach_attachments_to_comment(
			$comment_id,
			$attachments_ids
		);
	}

	private function get_attachments(): array {

		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return $_FILES[ PluginService::UPLOAD_FIELD_NAME ] ?? [];
	}
}
