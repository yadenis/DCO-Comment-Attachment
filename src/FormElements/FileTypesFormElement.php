<?php
/**
 * Form Elements: File Types
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
 * Provides functionality to render the file types form element.
 *
 * @since 3.0.0
 */
final class FileTypesFormElement implements FormElement {

	/**
	 * Grouped allowed file types from the plugin settings.
	 *
	 * @since 3.0.0
	 *
	 * @var array<string, \DCO_CA\DTO\AllowedFileTypesGroupDTO>
	 */
	private array $file_types_groups;

	/**
	 * The text template for the file types notice.
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

		$this->file_types_groups = $this->settings_service->get_grouped_allowed_file_types();

		/* translators: %s: the allowed file types list */
		$this->text = __( 'You can upload: %s.', 'dco-comment-attachment' );
	}

	/**
	 * Renders the file types form element.
	 *
	 * @since 3.0.0
	 */
	public function render(): void {

		$this->plugin_service->the_kses_post(
			apply_filters(
				'dco_ca_form_element_file_types',
				$this->generate_markup(),
				$this->file_types_groups
			)
		);
	}

	/**
	 * Generates the HTML markup for the file types notice.
	 *
	 * If no file types are allowed, returns an empty string. Otherwise,
	 * constructs an HTML string with a list of allowed file types.
	 *
	 * @since 3.0.0
	 *
	 * @return string The generated HTML markup,
	 *                or an empty string if the file types list is empty.
	 */
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

	/**
	 * Builds an HTML string of grouped allowed file types.
	 *
	 * @since 3.0.0
	 *
	 * @return string The builded HTML string of grouped allowed file types.
	 */
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
