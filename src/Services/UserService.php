<?php

declare(strict_types=1);

namespace DCO_CA\Services;

use DCO_CA\Enums\WhoCanUploadType;
use DCO_CA\Settings\WhoCanUpload;

defined( 'ABSPATH' ) || die;

final class UserService {

	public function __construct(
		private WhoCanUpload $who_can_upload
	) {
	}

	public function is_current_user_can_upload_attachment(): bool {

		$who_can_upload = $this->who_can_upload->get_value();

		if ( WhoCanUploadType::ALL_USERS === $who_can_upload ) {
			return true;
		}

		if ( WhoCanUploadType::LOGGED_USERS && is_user_logged_in() ) {
			return true;
		}

		return false;
	}
}
