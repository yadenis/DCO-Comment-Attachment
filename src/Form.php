<?php
/**
 * Form
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\FormElements\AttachmentAreaFormElement;
use DCO_CA\Services\PluginService;

defined( 'ABSPATH' ) || die;

/**
 * Handles the form functionality.
 *
 * @since 3.0.0
 */
final class Form {

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 *
	 * @param PluginService             $plugin_service  Service functions for the plugin.
	 * @param AttachmentAreaFormElement $attachment_area The attachment area form element.
	 */
	public function __construct(
		private PluginService $plugin_service,
		private AttachmentAreaFormElement $attachment_area,
	) {

		add_filter( 'comment_form_submit_field', $this->add_attachment_area( ... ) );
		add_action( 'wp_enqueue_scripts', $this->enqueue_scripts( ... ) );
	}

	/**
	 * Adds the attachment area markup before the submit button in the comment form.
	 *
	 * @since 3.0.0
	 *
	 * @param string $submit_field The original markup of the submit field.
	 *
	 * @return string The modified submit field markup with the attachment area prepended.
	 */
	public function add_attachment_area( string $submit_field ): string {

		if ( ! $this->plugin_service->is_form_enabled() ) {
			return $submit_field;
		}

		ob_start();

		$this->attachment_area->render_element();

		return ob_get_clean() . $submit_field;
	}

	/**
	 * Enqueues scripts and styles for the form.
	 *
	 * @since 3.0.0
	 */
	public function enqueue_scripts(): void {

		$this->plugin_service->enqueue_style( 'dco-comment-attachment' );

		if ( ! $this->plugin_service->is_form_enabled() ) {
			return;
		}

		$this->plugin_service->enqueue_script(
			script_name: 'dco-comment-attachment',
			script_data: [
				'commenting_form_not_found' => esc_attr__( 'The commenting form not found.', 'dco-comment-attachment' ),
			]
		);
	}
}
