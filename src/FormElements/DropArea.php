<?php

declare(strict_types=1);

namespace DCO_CA\FormElements;

use DCO_CA\FormElements\Interfaces\FormElement;
use DCO_CA\Settings\EnableMultipleUpload;

defined( 'ABSPATH' ) || die;

final class DropArea implements FormElement {

	private bool $is_enabled_multiple_upload;

	public function __construct(
		private EnableMultipleUpload $enable_multiple_upload,
	) {

		$this->is_enabled_multiple_upload = $this->enable_multiple_upload->get_value();
	}

	public function render(): void {

		$text = $this->get_drop_area_text();

		ob_start();
		?>
		<span class="comment-form-attachment__drop-area">
			<span class="comment-form-attachment__drop-area-inner">
				<?= $text; ?>
			</span>
		</span>
		<?php

		/**
		 * Filters the drop area form element markup.
		 *
		 * @since 2.2.0
		 *
		 * @param string $markup HTML markup for the drop area form element.
		 * @param bool $is_enabled_multiple_upload
		 */
		echo apply_filters( 'dco_ca_form_element_drop_area', ob_get_clean(), $text, $this->is_enabled_multiple_upload );
	}

	private function get_drop_area_text(): string {

		$singular_text = esc_html__( 'Drop file here', 'dco-comment-attachment' );
		$plural_text   = esc_html__( 'Drop files here', 'dco-comment-attachment' );

		$text = $this->is_enabled_multiple_upload ? $plural_text : $singular_text;

		return apply_filters(
			'dco_ca_form_element_drop_area_text',
			$text,
			$this->is_enabled_multiple_upload,
		);
	}
}
