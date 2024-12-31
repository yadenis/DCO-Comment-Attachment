<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\Services\PluginService;

defined( 'ABSPATH' ) || die;

final class Admin {

	public function __construct(
		private PluginService $plugin_service,
		private Settings $settings,
	) {

		add_action( 'admin_enqueue_scripts', $this->enqueue_scripts( ... ) );
	}

	public function enqueue_scripts( string $hook_suffix ): void {

		if ( in_array( $hook_suffix, [ 'edit-comments.php', 'comment.php', 'settings_page_dco-comment-attachment' ], true ) ) {

			$this->plugin_service->enqueue_style( 'dco-comment-attachment-admin' );
		}
	}
}
