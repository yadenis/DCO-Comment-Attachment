<?php
/**
 * Services: User
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA\Services;

defined( 'ABSPATH' ) || die;

/**
 * Service for handling user-related operations.
 *
 * @since 3.0.0
 */
final class UserService {

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 *
	 * @param SettingsService $settings_service Service functions for settings.
	 */
	public function __construct(
		private SettingsService $settings_service,
	) {
	}

	/**
	 * Checks if the current user has the capability to upload attachments.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if the current user can upload attachments, false otherwise.
	 */
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
