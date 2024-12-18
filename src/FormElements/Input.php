<?php

declare(strict_types=1);

namespace DCO_CA\FormElements;

use DCO_CA\FormElements\Interfaces\FormElement;
use DCO_CA\Settings\AllowedFileTypes;
use DCO_CA\Settings\EnableMultipleUpload;

defined( 'ABSPATH' ) || die;

final class Input implements FormElement {

	private const FIELD_NAME = 'attachment';

	private bool $is_enabled_multiple_upload;
	private array $allowed_file_types_list;

	public function __construct(
		private EnableMultipleUpload $enabled_multiple_upload,
		private AllowedFileTypes $allowed_file_types,
	) {

		$this->is_enabled_multiple_upload = $this->enabled_multiple_upload->get_value();
		$this->allowed_file_types_list    = $this->allowed_file_types->get_value();
	}

	public function render(): void {

		ob_start();
		$field_name = $this->get_field_name();
		$multiple   = $this->get_multiple_attribute();
		$accept     = $this->get_accept_attribute();
		?>
		<input 
			class="comment-form-attachment__input" 
			id="<?= esc_attr( self::FIELD_NAME ); ?>" 
			name="<?= esc_attr( $field_name ); ?>" 
			type="file" 
			accept="<?= esc_attr( $accept ); ?>"
			<?= esc_attr( $multiple ); ?> 
			/>
		<?php
		/**
		 * Filters the input form element markup.
		 *
		 * @since 1.1.1
		 *
		 * @param string $markup HTML markup for the input form element.
		 * @param string $field_name Name of the attachment input.
		 * @param array $allowed_file_types Allowed upload file types.
		 */
		echo apply_filters( 'dco_ca_form_element_input', ob_get_clean(), $field_name, $this->allowed_file_types );
	}

	private function get_field_name(): string {

		$name = self::FIELD_NAME;

		$name .= $this->is_enabled_multiple_upload ? '[]' : '';

		return $name;
	}

	private function get_multiple_attribute(): string {

		return $this->is_enabled_multiple_upload ? 'multiple' : '';
	}

	private function get_accept_attribute(): string {

		if ( ! $this->allowed_file_types_list ) {
			return '';
		}

		return '.' . implode( ',.', $this->allowed_file_types_list );
	}
}
