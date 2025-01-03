<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\AdminActions\DeleteAttachmentAdminAction;
use DCO_CA\Services\PluginService;

defined( 'ABSPATH' ) || die;

final class Admin {

	protected const ADMIN_PAGES = [
		'edit-comments.php',
		'comment.php',
		'settings_page_dco-comment-attachment',
	];

	protected const DELETE_ATTACHMENT_ACTION_NAME = 'deletecommentattachment';

	public function __construct(
		private PluginService $plugin_service,
		private Settings $settings,
		private DeleteAttachmentAdminAction $delete_attachment_admin_action,
	) {

		add_action( 'admin_enqueue_scripts', $this->enqueue_scripts( ... ) );
		add_filter( 'plugin_action_links_' . PluginService::BASENAME, $this->add_plugin_action_links( ... ) );
	}

	public function enqueue_scripts( string $hook_suffix ): void {

		if ( 'comment.php' === $hook_suffix ) {
			wp_enqueue_media();
		}

		if ( $this->is_admin_page( $hook_suffix ) ) {

			$this->plugin_service->enqueue_script( 'dco-comment-attachment-admin' );

			$this->plugin_service->enqueue_style( 'dco-comment-attachment-admin' );
		}
	}

	public function add_plugin_action_links( array $actions ): array {

		array_unshift(
			$actions,
			sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( admin_url( 'options-general.php?page=dco-comment-attachment' ) ),
				esc_html__( 'Settings', 'dco-comment-attachment' )
			)
		);

		return $actions;
	}

	private function is_admin_page( string $page ): bool {

		return in_array(
			$page,
			self::ADMIN_PAGES,
			true
		);
	}
}
