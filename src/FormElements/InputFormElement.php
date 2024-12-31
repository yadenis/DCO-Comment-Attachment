<?php

declare(strict_types=1);

namespace DCO_CA\FormElements;

use DCO_CA\Interfaces\FormElement;
use DCO_CA\Services\PluginService;
use DCO_CA\Settings\AllowedFileTypesSetting;
use DCO_CA\Settings\EnableMultipleUploadSetting;

defined( 'ABSPATH' ) || die;

final class InputFormElement implements FormElement {

	private bool $is_enabled_multiple_upload;
	private array $allowed_file_types;

	public function __construct(
		private EnableMultipleUploadSetting $enabled_multiple_upload_setting,
		private AllowedFileTypesSetting $allowed_file_types_setting,
	) {

		$this->is_enabled_multiple_upload = $this->enabled_multiple_upload_setting->get_value();
		$this->allowed_file_types         = $this->allowed_file_types_setting->get_value();
	}

	public function render(): void {

		$field_name = $this->get_field_name();
		$multiple   = $this->get_multiple_attribute();
		$accept     = $this->get_accept_attribute();

		$markup = sprintf(
			'<input class="comment-form-attachment__input" id="%s" name="%s" type="file" accept="%s" %s />',
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

	private function get_field_name(): string {

		return PluginService::UPLOAD_FIELD_NAME . ( $this->is_enabled_multiple_upload ? '[]' : '' );
	}

	private function get_multiple_attribute(): string {

		return $this->is_enabled_multiple_upload ? 'multiple' : '';
	}

	private function get_accept_attribute(): string {

		if ( ! $this->allowed_file_types ) {
			return '';
		}

		return '.' . implode( ',.', wp_list_pluck( $this->allowed_file_types, 'extension' ) );
	}
}
