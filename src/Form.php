<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\FormElements\AttachmentAreaFormElement;
use DCO_CA\Services\PluginService;

defined( 'ABSPATH' ) || die;

final class Form {

	public function __construct(
		private PluginService $plugin_service,
		private FormHandler $form_handler,
		private AttachmentAreaFormElement $attachment_area,
	) {

		add_filter( 'comment_form_submit_field', $this->add_attachment_area( ... ) );
		add_action( 'wp_enqueue_scripts', $this->enqueue_scripts( ... ) );
	}

	public function add_attachment_area( string $submit_field ): string {

		if ( ! $this->plugin_service->is_form_enabled() ) {
			return $submit_field;
		}

		ob_start();

		$this->attachment_area->render();

		return ob_get_clean() . $submit_field;
	}

	public function enqueue_scripts(): void {

		if ( ! $this->plugin_service->is_form_enabled() ) {
			return;
		}

		$this->plugin_service->enqueue_style( 'dco-comment-attachment' );

		$this->plugin_service->enqueue_script( 'dco-comment-attachment' );
	}
}
