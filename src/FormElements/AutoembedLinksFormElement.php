<?php

declare(strict_types=1);

namespace DCO_CA\FormElements;

use DCO_CA\Interfaces\FormElement;
use DCO_CA\Services\PluginService;
use DCO_CA\Settings\AutoembedLinksSetting;

defined( 'ABSPATH' ) || die;

final class AutoembedLinksFormElement implements FormElement {

	private bool $is_autoembed_links;

	private string $text;

	public function __construct(
		private PluginService $plugin_service,
		private AutoembedLinksSetting $autoembed_links_setting,
	) {

		$this->is_autoembed_links = $this->autoembed_links_setting->get_value();

		$this->text = esc_html__(
			'Links to YouTube, Facebook, Twitter and other services inserted in the comment text will be automatically embedded.',
			'dco-comment-attachment'
		);
	}

	public function render(): void {

		$this->plugin_service->the_kses_post(
			/**
			 * Filters the autoembed links notification form element markup.
			 *
			 * @since 1.3.0
			 *
			 * @param string $markup HTML markup for the autoembed links
			 *                       notification list form element.
			 * @param bool $is_autoembed_links Whether the links is automatically embedded.
			 */
			apply_filters(
				'dco_ca_form_element_autoembed_links',
				$this->get_markup(),
				$this->is_autoembed_links
			)
		);
	}

	private function get_markup(): string {

		if ( ! $this->is_autoembed_links ) {
			return '';
		}

		return sprintf(
			'<span class="comment-form-attachment__autoembed-links-notice">%s</span>',
			$this->text
		);
	}
}
