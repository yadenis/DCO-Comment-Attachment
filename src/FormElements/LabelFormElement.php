<?php
/**
 * Form Elements: Label
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA\FormElements;

use DCO_CA\Interfaces\FormElement;
use DCO_CA\Services\PluginService;
use DCO_CA\Services\SettingsService;

defined( 'ABSPATH' ) || die;

/**
 * Provides functionality to render the label form element.
 *
 * @since 3.0.0
 */
final class LabelFormElement implements FormElement {

	/**
	 * Whether attachment is required.
	 *
	 * @since 3.0.0
	 *
	 * @var bool
	 */
	private bool $is_required_attachment;

	/**
	 * Whether multiple upload is enabled.
	 *
	 * @since 3.0.0
	 *
	 * @var bool
	 */
	private bool $is_enabled_multiple_upload;

	/**
	 * The text displayed when a single file can be uploaded.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private string $singular_text;

	/**
	 * The text displayed when multiple files can be uploaded.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private string $plural_text;

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 *
	 * @param PluginService   $plugin_service   Service functions for the plugin.
	 * @param SettingsService $settings_service Service functions for settings.
	 */
	public function __construct(
		private PluginService $plugin_service,
		private SettingsService $settings_service,
	) {

		$this->is_required_attachment     = $this->settings_service->is_required_attachment();
		$this->is_enabled_multiple_upload = $this->settings_service->is_enabled_multiple_upload();

		$this->singular_text = __( 'Attachment', 'dco-comment-attachment' );
		$this->plural_text   = __( 'Attachments', 'dco-comment-attachment' );
	}

	/**
	 * Renders the label form element.
	 *
	 * @since 3.0.0
	 */
	public function render_element(): void {

		$markup = sprintf(
			'<label class="comment-form-attachment__label" for="attachment">%s</label>',
			$this->get_label_text()
		);

		$this->plugin_service->the_kses_post(
			/**
			 * Filters the label form element markup.
			 *
			 * @since 1.1.1
			 *
			 * @param string $markup HTML markup for the label form element.
			 * @param bool $required_attachment Whether to attachment is required.
			 */
			apply_filters(
				'dco_ca_form_element_label',
				$markup,
				$this->is_required_attachment,
				$this->is_enabled_multiple_upload
			)
		);
	}

	/**
	 * Retrieves the text for the label.
	 *
	 * Determines whether to use the singular or plural text based on whether
	 * multiple the file upload is enabled. If required, a `*` is added to the label.
	 *
	 * @since 3.0.0
	 *
	 * @return string The text to display in the label.
	 */
	private function get_label_text(): string {

		$label = $this->is_enabled_multiple_upload ? $this->plural_text : $this->singular_text;

		$label .= $this->is_required_attachment ? ' <span class="required">*</span>' : '';

		return apply_filters(
			'dco_ca_form_element_label_text',
			$label,
			$this->is_required_attachment,
			$this->is_enabled_multiple_upload
		);
	}
}
