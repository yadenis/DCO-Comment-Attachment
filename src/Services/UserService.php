<?php

declare(strict_types=1);

namespace DCO_CA\Services;

defined( 'ABSPATH' ) || die;

final class UserService {

	public function __construct(
		private SettingsService $settings_service,
	) {
	}

	public function is_current_user_can_upload_attachment(): bool {

		if ( $this->settings_service->is_can_upload_all_users() ) {
			return true;
		}

		if ( $this->settings_service->is_can_upload_only_logged_users() && is_user_logged_in() ) {
			return true;
		}

		return false;
	}
}
