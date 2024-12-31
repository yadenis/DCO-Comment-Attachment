<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\Services\PluginService;

defined( 'ABSPATH' ) || die;

final class Admin {

	protected const ADMIN_PAGES = [
		'edit-comments.php',
		'comment.php',
		'settings_page_dco-comment-attachment',
	];

	public function __construct(
		private PluginService $plugin_service,
		private Settings $settings,
	) {

		add_action( 'admin_enqueue_scripts', $this->enqueue_scripts( ... ) );
	}

	public function enqueue_scripts( string $hook_suffix ): void {

		if ( $this->is_admin_page( $hook_suffix ) ) {

			$this->plugin_service->enqueue_style( 'dco-comment-attachment-admin' );
		}
	}

	private function is_admin_page( string $page ): bool {

		return in_array(
			$page,
			self::ADMIN_PAGES,
			true
		);
	}
}
