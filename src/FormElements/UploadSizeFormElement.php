<?php

declare(strict_types=1);

namespace DCO_CA\FormElements;

use DCO_CA\Interfaces\FormElement;
use DCO_CA\Services\PluginService;
use DCO_CA\Services\SettingsService;

defined( 'ABSPATH' ) || die;

final class UploadSizeFormElement implements FormElement {

	private string $max_upload_size;

	private string $text;

	public function __construct(
		private PluginService $plugin_service,
		private SettingsService $settings_service,
	) {

		$this->max_upload_size = $this->settings_service->get_formatted_max_upload_size();

		/* translators: %s: the maximum allowed upload file size */
		$this->text = __( 'The maximum upload file size: %s.', 'dco-comment-attachment' );
	}

	public function render(): void {

		$markup = sprintf(
			'<span class="comment-form-attachment__file-size-notice">%s</span>',
			sprintf( $this->text, $this->max_upload_size )
		);

		$this->plugin_service->the_kses_post(
			/**
			 * Filters the maximum upload file size form element markup.
			 *
			 * @since 1.1.1
			 *
			 * @param string $markup HTML markup for the maximum upload file size form element.
			 * @param string $max_upload_size The max upload file size with format.
			 */
			apply_filters(
				'dco_ca_form_element_upload_size',
				$markup,
				$this->max_upload_size
			)
		);
	}
}
