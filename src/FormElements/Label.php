<?php

declare(strict_types=1);

namespace DCO_CA\FormElements;

use DCO_CA\FormElements\Interfaces\FormElement;
use DCO_CA\Settings\EnableMultipleUpload;
use DCO_CA\Settings\RequiredAttachment;

defined( 'ABSPATH' ) || die;

final class Label implements FormElement {

	private bool $is_required_attachment;
	private bool $is_enabled_multiple_upload;

	public function __construct(
		private RequiredAttachment $required_attachment,
		private EnableMultipleUpload $enable_multiple_upload,
	) {

		$this->is_required_attachment     = $this->required_attachment->get_value();
		$this->is_enabled_multiple_upload = $this->enable_multiple_upload->get_value();
	}

	public function render(): void {

		ob_start();
		?>
		<label class="comment-form-attachment__label" for="attachment">
			<?php $this->the_label_text(); ?>
		</label>
		<?php
		/**
		 * Filters the label form element markup.
		 *
		 * @since 1.1.1
		 *
		 * @param string $markup HTML markup for the label form element.
		 * @param bool $required_attachment Whether to attachment is required.
		 */
		echo apply_filters( 'dco_ca_form_element_label', ob_get_clean(), $this->is_required_attachment );
	}

	private function the_label_text(): void {

		$label = $this->is_enabled_multiple_upload ? __( 'Attachments', 'dco-comment-attachment' ) : __( 'Attachment', 'dco-comment-attachment' );

		$label .= $this->is_required_attachment ? ' <span class="required">*</span>' : '';

		$label = apply_filters(
			'dco_ca_form_element_label_text',
			$label,
			$this->is_enabled_multiple_upload,
			$this->is_required_attachment
		);

		echo $label;
	}
}
