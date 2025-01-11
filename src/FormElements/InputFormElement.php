<?php
/**
 * Form Elements: Input
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
 * Provides functionality to render the input form element.
 *
 * @since 3.0.0
 */
final class InputFormElement implements FormElement {

	/**
	 * Whether multiple upload is enabled.
	 *
	 * @since 3.0.0
	 *
	 * @var bool
	 */
	private bool $is_enabled_multiple_upload;

	/**
	 * List of allowed file types for upload.
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	private array $allowed_file_types;

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

		$this->is_enabled_multiple_upload = $this->settings_service->is_enabled_multiple_upload();
		$this->allowed_file_types         = $this->settings_service->get_allowed_file_types();
	}

	/**
	 * Renders the input form element.
	 *
	 * @since 3.0.0
	 */
	public function render(): void {

		$field_name = $this->generates_field_name();
		$multiple   = $this->generates_multiple_attribute();
		$accept     = $this->generates_accept_attribute();

		$markup = sprintf(
			'<input type="file" class="comment-form-attachment__input" id="%s" name="%s" accept="%s" %s />',
			esc_attr( $field_name ),
			esc_attr( $field_name ),
			esc_attr( $accept ),
			esc_attr( $multiple )
		);

		$this->plugin_service->the_kses_post(
			/**
			 * Filters the input form element markup.
			 *
			 * @since 1.1.1
			 *
			 * @param string $markup HTML markup for the input form element.
			 * @param string $field_name Name of the attachment input.
			 * @param array $allowed_file_types Allowed upload file types.
			 */
			apply_filters(
				'dco_ca_form_element_input',
				$markup,
				$field_name,
				$this->allowed_file_types
			)
		);
	}

	/**
	 * Generates the name attribute for the input field.
	 *
	 * @since 3.0.0
	 *
	 * @return string The name attribute value.
	 */
	private function generates_field_name(): string {

		return PluginService::UPLOAD_FIELD_NAME . ( $this->is_enabled_multiple_upload ? '[]' : '' );
	}

	/**
	 * Generates the multiple attribute for the input field.
	 *
	 * The attribute is included only if the multiple upload is enabled.
	 *
	 * @since 3.0.0
	 *
	 * @return string The multiple attribute,
	 *                or an empty string if the multiple upload is disabled.
	 */
	private function generates_multiple_attribute(): string {

		return $this->is_enabled_multiple_upload ? 'multiple' : '';
	}

	/**
	 * Generates the accept attribute for the input field based on plugin settings.
	 *
	 * @since 3.0.0
	 *
	 * @return string The accept attribute value,
	 *                or an empty string if the allowed file types list is empty.
	 */
	private function generates_accept_attribute(): string {

		if ( ! $this->allowed_file_types ) {
			return '';
		}

		return '.' . implode( ',.', wp_list_pluck( $this->allowed_file_types, 'extension' ) );
	}
}
