<?php

declare(strict_types=1);

namespace DCO_CA\FormElements;

use DCO_CA\Interfaces\FormElement;
use DCO_CA\Services\PluginService;
use DCO_CA\Services\SettingsService;

defined( 'ABSPATH' ) || die;

final class FileTypesFormElement implements FormElement {

	private array $file_types_groups;

	private string $text;

	public function __construct(
		private PluginService $plugin_service,
		private SettingsService $settings_service,
	) {

		$this->file_types_groups = $this->settings_service->get_grouped_allowed_file_types();

		/* translators: %s: the allowed file types list */
		$this->text = __( 'You can upload: %s.', 'dco-comment-attachment' );
	}

	public function render(): void {

		$this->plugin_service->the_kses_post(
			apply_filters(
				'dco_ca_form_element_file_types',
				$this->generate_markup(),
				$this->file_types_groups
			)
		);
	}

	private function generate_markup(): string {

		if ( ! $this->file_types_groups ) {
			return '';
		}

		return sprintf(
			'<span class="comment-form-attachment__file-types-notice">%s</span>',
			wp_kses(
				sprintf( $this->text, $this->build_html_types() ),
				[ 'abbr' => [ 'title' => true ] ]
			)
		);
	}

	private function build_html_types(): string {

		$html = [];

		foreach ( $this->file_types_groups as $group ) {

			if ( ! $group->extensions ) {
				continue;
			}

			$title = implode( ', ', wp_list_pluck( $group->extensions, 'extension' ) );

			$html[] = sprintf(
				'<abbr title="%s">%s</abbr>',
				esc_attr( $title ),
				esc_html( $group->title ),
			);
		}

		return implode( ', ', $html );
	}
}
