<?php
/**
 * Form Elements: Drop Area
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
 * Provides functionality to render the drop area form element.
 *
 * @since 3.0.0
 */
final class DropAreaFormElement implements FormElement {

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

		$this->is_enabled_multiple_upload = $this->settings_service->is_enabled_multiple_upload();

		$this->singular_text = esc_html__( 'Drop file here', 'dco-comment-attachment' );
		$this->plural_text   = esc_html__( 'Drop files here', 'dco-comment-attachment' );
	}

	/**
	 * Renders the drop area form element.
	 *
	 * @since 3.0.0
	 */
	public function render_element(): void {

		$text = $this->get_drop_area_text();

		ob_start();
		?>
		<span class="comment-form-attachment__drop-area">
			<span class="comment-form-attachment__drop-area-inner">
				<?php $this->plugin_service->the_kses_post( $text ); ?>
			</span>
		</span>
		<?php

		$this->plugin_service->the_kses_post(
			/**
			 * Filters the drop area form element markup.
			 *
			 * @since 2.2.0
			 *
			 * @param string $markup HTML markup for the drop area form element.
			 * @param bool $is_enabled_multiple_upload
			 */
			apply_filters(
				'dco_ca_form_element_drop_area',
				ob_get_clean(),
				$this->is_enabled_multiple_upload
			)
		);
	}

	/**
	 * Retrieves the appropriate text for the drop area.
	 *
	 * Determines whether to use the singular or plural text based on whether
	 * multiple the file upload is enabled.
	 *
	 * @since 3.0.0
	 *
	 * @return string The text for the drop area.
	 */
	private function get_drop_area_text(): string {

		$text = $this->is_enabled_multiple_upload ? $this->plural_text : $this->singular_text;

		return apply_filters(
			'dco_ca_form_element_drop_area_text',
			$text,
			$this->is_enabled_multiple_upload,
		);
	}
}
