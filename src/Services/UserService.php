<?php

declare(strict_types=1);

namespace DCO_CA\Services;

use DCO_CA\Enums\WhoCanUploadType;

defined( 'ABSPATH' ) || die;

final class UserService {

	public function __construct(
		private SettingsService $settings_service,
	) {
	}

	public function is_current_user_can_upload_attachment(): bool {

		$who_can_upload = $this->settings_service->get_who_can_upload();

		if ( WhoCanUploadType::ALL_USERS->value === $who_can_upload ) {
			return true;
		}

		if ( WhoCanUploadType::LOGGED_USERS->value && is_user_logged_in() ) {
			return true;
		}

		return false;
	}
}
