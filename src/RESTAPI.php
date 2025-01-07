<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\FormHandlers\AttachmentUploadHandler;
use DCO_CA\FormHandlers\AttachmentUploadValidator;
use DCO_CA\Services\CommentService;
use DCO_CA\Services\PluginService;
use DCO_CA\Services\SettingsService;
use WP_Comment;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

defined( 'ABSPATH' ) || die;

final class RESTAPI {

	private array $handled_attachment_ids = [];

	private bool $is_manually_moderation_enabled;

	public function __construct(
		private PluginService $plugin_service,
		private CommentService $comment_service,
		private SettingsService $settings_service,
		private AttachmentUploadValidator $attachment_upload_validator,
		private AttachmentUploadHandler $attachment_upload_handler,
	) {

		$this->is_manually_moderation_enabled = $this->settings_service->is_manually_moderation_enabled();

		add_filter( 'rest_preprocess_comment', $this->validate_uploaded_attachments( ... ), 10, 2 );
		add_action( 'rest_after_insert_comment', $this->handle_uploaded_attachments( ... ), 10, 3 );
		add_filter( 'pre_comment_approved', $this->unapprove_comment_or_not( ... ) );

		add_action( 'rest_after_insert_comment', $this->update_comment_attachments( ... ), 10, 3 );

		add_filter( 'rest_prepare_comment', $this->add_attachment_links_to_comment( ... ), 10, 2 );
	}

	public function validate_uploaded_attachments( array $commentdata, WP_REST_Request $request ): array|WP_Error {

		if ( ! $this->plugin_service->is_form_enabled() ) {
			return $commentdata;
		}

		$validated = $this->attachment_upload_validator->validate( $this->get_uploaded_attachments( $request ) );

		if ( ! is_wp_error( $validated ) ) {
			return $commentdata;
		}

		return $validated;
	}

	public function handle_uploaded_attachments( WP_Comment $wp_comment, WP_REST_Request $request, bool $creating ): void {

		if ( ! $this->plugin_service->is_form_enabled() ) {
			return;
		}

		if ( ! $creating ) {
			return;
		}

		$comment = $this->comment_service->get_comment_instance( $wp_comment );
		if ( ! $comment ) {
			return;
		}

		$this->handled_attachment_ids = $this->attachment_upload_handler->handle(
			$this->get_uploaded_attachments( $request ),
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

	public function update_comment_attachments( WP_Comment $wp_comment, WP_REST_Request $request, bool $creating ): void {

		if ( $creating ) {
			return;
		}

		$attachment_ids = $request->get_param( 'dco_attachment_id' );

		if ( ! is_array( $attachment_ids ) ) {
			return;
		}

		$comment = $this->comment_service->get_comment_instance( $wp_comment );
		if ( ! $comment ) {
			return;
		}

		$attachment_ids = array_map(
			'intval',
			$attachment_ids
		);

		// We need to delete the last empty element, because it's used
		// as a placeholder in the attachments edit form.
		array_pop( $attachment_ids );

		$this->comment_service->attach_attachments_to_comment( $comment->id, $attachment_ids );
	}

	public function add_attachment_links_to_comment( WP_REST_Response $response, WP_Comment $wp_comment ): WP_REST_Response {

		$comment = $this->comment_service->get_comment_instance( $wp_comment );
		if ( ! $comment ) {
			return $response;
		}

		if ( ! $comment->has_attachments() ) {
			return $response;
		}

		foreach ( $comment->get_attachments() as $attachment ) {

			$response->add_link(
				'related',
				rest_url( 'wp/v2/media/' . $attachment->id ),
				[ 'embeddable' => true ]
			);
		}

		return $response;
	}

	private function get_uploaded_attachments( WP_REST_Request $request ): array {

		if ( empty( $request->get_file_params()[ PluginService::UPLOAD_FIELD_NAME ] ) ) {
			return [];
		}

		$files = $request->get_file_params()[ PluginService::UPLOAD_FIELD_NAME ];

		if ( ! is_array( $files ) ) {
			return [];
		}

		return $files;
	}
}
