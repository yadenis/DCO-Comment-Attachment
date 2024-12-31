<?php

declare(strict_types=1);

namespace DCO_CA\FormElements;

use DCO_CA\Interfaces\FormElement;
use DCO_CA\Services\PluginService;
use DCO_CA\Settings\EnableMultipleUploadSetting;
use DCO_CA\Settings\RequiredAttachmentSetting;

defined( 'ABSPATH' ) || die;

final class LabelFormElement implements FormElement {

	private bool $is_required_attachment;
	private bool $is_enabled_multiple_upload;

	private string $singular_text;
	private string $plural_text;

	public function __construct(
		private PluginService $plugin_service,
		private RequiredAttachmentSetting $required_attachment_setting,
		private EnableMultipleUploadSetting $enable_multiple_upload_setting,
	) {

		$this->is_required_attachment     = $this->required_attachment_setting->get_value();
		$this->is_enabled_multiple_upload = $this->enable_multiple_upload_setting->get_value();

		$this->singular_text = __( 'Attachment', 'dco-comment-attachment' );
		$this->plural_text   = __( 'Attachments', 'dco-comment-attachment' );
	}

	public function render(): void {

		$markup = sprintf(
			'<label class="comment-form-attachment__label" for="attachment">%s</label>',
			$this->get_label_text()
		);

		$this->plugin_service->the_kses_post(
			/**
			 * Filters the label form element markup.
			 *
			 * @since 1.1.1
			 *
			 * @param string $markup HTML markup for the label form element.
			 * @param bool $required_attachment Whether to attachment is required.
			 */
			apply_filters(
				'dco_ca_form_element_label',
				$markup,
				$this->is_required_attachment,
				$this->is_enabled_multiple_upload
			)
		);
	}

	private function get_label_text(): string {

		$label = $this->is_enabled_multiple_upload ? $this->plural_text : $this->singular_text;

		$label .= $this->is_required_attachment ? ' <span class="required">*</span>' : '';

		return apply_filters(
			'dco_ca_form_element_label_text',
			$label,
			$this->is_required_attachment,
			$this->is_enabled_multiple_upload
		);
	}
}
