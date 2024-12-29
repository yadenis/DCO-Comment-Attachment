<?php

declare(strict_types=1);

namespace DCO_CA\FormElements;

use DCO_CA\Interfaces\FormElement;
use DCO_CA\Settings\AutoembedLinks as AutoembedLinksSetting;

defined( 'ABSPATH' ) || die;

final class AutoembedLinksFormElement implements FormElement {

	private bool $is_autoembed_links;

	public function __construct(
		private AutoembedLinksSetting $autoembed_links,
	) {

		$this->is_autoembed_links = $this->autoembed_links->get_value();
	}

	public function render(): void {

		/**
		 * Filters the autoembed links notification form element markup.
		 *
		 * @since 1.3.0
		 *
		 * @param string $markup HTML markup for the autoembed links
		 *                       notification list form element.
		 * @param bool $is_autoembed_links Whether the links is automatically embedded.
		 */
		echo apply_filters( 'dco_ca_form_element_autoembed_links', $this->get_markup(), $this->is_autoembed_links );
	}

	private function get_markup(): string {

		if ( ! $this->is_autoembed_links ) {
			return '';
		}

		return sprintf(
			'<span class="comment-form-attachment__autoembed-links-notice">%s</span>',
			sprintf(
				esc_html__( 'Links to YouTube, Facebook, Twitter and other services inserted in the comment text will be automatically embedded.', 'dco-comment-attachment' )
			)
		);
	}
}
