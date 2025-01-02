<?php

declare(strict_types=1);

namespace DCO_CA\Services;

use DCO_CA\Attachment;

defined( 'ABSPATH' ) || die;

final class AttachmentService {

	public function __construct(
		private SettingsService $settings_service,
	) {
	}

	public function get_attachment_instance( int $attachment_id ): ?Attachment {

		// Checks attachment exists.
		if ( ! wp_get_attachment_url( $attachment_id ) ) {
			return null;
		}

		return new Attachment(
			$this->settings_service,
			$attachment_id
		);
	}
}
