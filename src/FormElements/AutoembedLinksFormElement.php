<?php
/**
 * Form Elements: Autoembed Links
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
 * Provides functionality to render the autoembed links form element.
 *
 * @since 3.0.0
 */
final class AutoembedLinksFormElement implements FormElement {

	/**
	 * Whether autoembed links is enabled.
	 *
	 * @since 3.0.0
	 *
	 * @var bool
	 */
	private bool $is_autoembed_links;

	/**
	 * The text displayed for the autoembed links notice.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private string $text;

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

		$this->is_autoembed_links = $this->settings_service->is_autoembed_links();

		$this->text = esc_html__(
			'Links to YouTube, Facebook, Twitter and other services inserted in the comment text will be automatically embedded.',
			'dco-comment-attachment'
		);
	}

	/**
	 * Renders the autoembed links form element.
	 *
	 * @since 3.0.0
	 */
	public function render(): void {

		$this->plugin_service->the_kses_post(
			/**
			 * Filters the autoembed links notice form element markup.
			 *
			 * @since 1.3.0
			 *
			 * @param string $markup HTML markup for the autoembed links
			 *                       notice list form element.
			 * @param bool $is_autoembed_links Whether the links is automatically embedded.
			 */
			apply_filters(
				'dco_ca_form_element_autoembed_links',
				$this->generate_markup(),
				$this->is_autoembed_links
			)
		);
	}

	/**
	 * Generates the HTML markup for the autoembed links notice.
	 *
	 * If the autoembed links is disabled, returns an empty string.
	 *
	 * @since 3.0.0
	 *
	 * @return string The generated HTML markup,
	 *                or an empty string if autoembed links is disabled.
	 */
	private function generate_markup(): string {

		if ( ! $this->is_autoembed_links ) {
			return '';
		}

		return sprintf(
			'<span class="comment-form-attachment__autoembed-links-notice">%s</span>',
			$this->text
		);
	}
}
