<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\FormElements\AttachmentArea;

defined( 'ABSPATH' ) || die;

final class Form {

	public function __construct(
		private AttachmentArea $attachment_area,
	) {

		if ( ! $this->is_form_enabled() ) {
			return;
		}

		add_action( 'comment_form_submit_field', $this->add_attachment_area( ... ) );
	}

	public function add_attachment_area( string $submit_field ): string {

		ob_start();

		$this->attachment_area->render();

		return ob_get_clean() . $submit_field;
	}

	private function is_form_enabled(): bool {

		$disable = false;

		if ( ! $this->is_user_can_upload() ) {
			$disable = true;
		}

		/**
		 * Filters whether to disable the attachment upload field.
		 *
		 * Prevents the attachment upload field from being appended to the commenting form.
		 *
		 * @since 1.1.0
		 *
		 * @param bool $disable Whether to disable the attachment upload field.
		 *                      Returning true to the filter will disable the attachment field.
		 *                      Default false.
		 */
		return ! apply_filters( 'dco_ca_disable_attachment_field', $disable );
	}

	private function is_user_can_upload() {

		return true;

		$who_can_upload = (int) $this->get_option( 'who_can_upload' );

		// All users.
		if ( 1 === $who_can_upload ) {
			return true;
		}

		// Only logged users.
		if ( 2 === $who_can_upload && is_user_logged_in() ) {
			return true;
		}

		return false;
	}
}
