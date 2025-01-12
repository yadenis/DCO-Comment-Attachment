<?php
/**
 * Form Elements: Upload Size
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
 * Provides functionality to render the upload size form element.
 *
 * @since 3.0.0
 */
final class UploadSizeFormElement implements FormElement {

	/**
	 * The formatted maximum upload file size.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private string $max_upload_size;

	/**
	 * The text displayed for the upload size notice.
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

		$this->max_upload_size = $this->settings_service->get_formatted_max_upload_size();

		/* translators: %s: the maximum allowed upload file size */
		$this->text = __( 'The maximum upload file size: %s.', 'dco-comment-attachment' );
	}

	/**
	 * Renders the upload size form element.
	 *
	 * @since 3.0.0
	 */
	public function render_element(): void {

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
