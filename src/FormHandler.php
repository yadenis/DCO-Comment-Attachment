<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\Services\PluginService;
use DCO_CA\Settings\ManuallyModerationSetting;

defined( 'ABSPATH' ) || die;

final class FormHandler {

	private bool $is_manually_moderation;

	public function __construct(
		private PluginService $plugin_service,
		private AttachmentUploadValidator $attachment_upload_validator,
		private ManuallyModerationSetting $manually_moderation,
	) {

		$this->is_manually_moderation = $this->manually_moderation->get_value();

		add_filter( 'preprocess_comment', $this->check_attachment( ... ) );
		//add_action( 'comment_post', array( $this, 'save_attachment' ), 5, 3 );
		//add_filter( 'pre_comment_approved', array( $this, 'approve_comment' ) );
	}

	public function check_attachment( array $commentdata ): array {

		$validated = $this->attachment_upload_validator->validate();

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
}
