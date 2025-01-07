<?php

declare(strict_types=1);

namespace DCO_CA\Services;

use DCO_CA\Entities\AttachmentEntity;

defined( 'ABSPATH' ) || die;

final class AttachmentService {

	public function __construct(
		private SettingsService $settings_service,
	) {
	}

	public function get_attachment_instance( int $attachment_id ): ?AttachmentEntity {

		// Checks attachment exists.
		if ( ! wp_get_attachment_url( $attachment_id ) ) {
			return null;
		}

		return new AttachmentEntity(
			$this->settings_service,
			$attachment_id
		);
	}
}
