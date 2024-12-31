<?php

declare(strict_types=1);

namespace DCO_CA\FormElements;

use DCO_CA\Interfaces\FormElement;
use DCO_CA\Services\PluginService;
use DCO_CA\Settings\EnableMultipleUploadSetting;

defined( 'ABSPATH' ) || die;

final class DropAreaFormElement implements FormElement {

	private bool $is_enabled_multiple_upload;

	private string $singular_text;
	private string $plural_text;

	public function __construct(
		private PluginService $plugin_service,
		private EnableMultipleUploadSetting $enable_multiple_upload_setting,
	) {

		$this->is_enabled_multiple_upload = $this->enable_multiple_upload_setting->get_value();

		$this->singular_text = esc_html__( 'Drop file here', 'dco-comment-attachment' );
		$this->plural_text   = esc_html__( 'Drop files here', 'dco-comment-attachment' );
	}

	public function render(): void {

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

	private function get_drop_area_text(): string {

		$text = $this->is_enabled_multiple_upload ? $this->plural_text : $this->singular_text;

		return apply_filters(
			'dco_ca_form_element_drop_area_text',
			$text,
			$this->is_enabled_multiple_upload,
		);
	}
}
