<?php

declare(strict_types=1);

namespace DCO_CA\FormElements;

use DCO_CA\Interfaces\FormElement;
use DCO_CA\Settings\AllowedFileTypes;
use DCO_CA\Enums\AllowedFileTypesFormat;

defined( 'ABSPATH' ) || die;

final class FileTypes implements FormElement {

	private array $types;

	public function __construct(
		private AllowedFileTypes $allowed_file_types,
	) {

		$this->types = $this->allowed_file_types->get_value( AllowedFileTypesFormat::GROUPED_ARRAY );
	}

	public function render(): void {

		echo apply_filters( 'dco_ca_form_element_file_types', $this->get_markup(), $this->types );
	}

	private function get_markup(): string {

		if ( ! $this->types ) {
			return '';
		}

		return sprintf(
			'<span class="comment-form-attachment__file-types-notice">%s</span>',
			sprintf(
			/* translators: %s: the allowed file types list */
				esc_html__( 'You can upload: %s.', 'dco-comment-attachment' ),
				wp_kses_data( $this->build_html_types() )
			)
		);
	}

	private function build_html_types(): string {

		$html = [];

		foreach ( $this->types as $type ) {

			if ( ! $type['extensions'] ) {
				continue;
			}

			$title = implode( ', ', $type['extensions'] );

			$html[] = sprintf(
				'<abbr title="%s">%s</abbr>',
				esc_attr( $title ),
				esc_html( $type['name'] ),
			);
		}

		return implode( ', ', $html );
	}
}
