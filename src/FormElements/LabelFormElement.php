<?php

declare(strict_types=1);

namespace DCO_CA\FormElements;

use DCO_CA\Interfaces\FormElement;
use DCO_CA\Settings\EnableMultipleUpload;
use DCO_CA\Settings\RequiredAttachment;

defined( 'ABSPATH' ) || die;

final class LabelFormElement implements FormElement {

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

		$markup = sprintf(
			'<label class="comment-form-attachment__label" for="attachment">%s</label>',
			$this->get_label_text()
		);

		/**
		 * Filters the label form element markup.
		 *
		 * @since 1.1.1
		 *
		 * @param string $markup HTML markup for the label form element.
		 * @param bool $required_attachment Whether to attachment is required.
		 */
		echo apply_filters( 'dco_ca_form_element_label', $markup, $this->is_required_attachment );
	}

	private function get_label_text(): string {

		$singular_label = __( 'Attachment', 'dco-comment-attachment' );
		$plural_label   = __( 'Attachments', 'dco-comment-attachment' );

		$label = $this->is_enabled_multiple_upload ? $plural_label : $singular_label;

		$label .= $this->is_required_attachment ? ' <span class="required">*</span>' : '';

		return apply_filters(
			'dco_ca_form_element_label_text',
			esc_html( $label ),
			$this->is_enabled_multiple_upload,
			$this->is_required_attachment
		);
	}
}
