<?php
/**
 * Settings: Allowed File Types
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\DTO\AllowedFileTypesExtensionDTO;
use DCO_CA\DTO\AllowedFileTypesGroupDTO;
use DCO_CA\DTO\SettingsFieldDTO;
use DCO_CA\Helpers\OptionsHelper;
use DCO_CA\Enums\AllowedFileTypesFormat;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingsControls\AllowedFileTypesSettingsControl;
use DCO_CA\SettingsControls\DescriptionSettingsControl;

defined( 'ABSPATH' ) || die;

final class AllowedFileTypesSetting implements Setting {

	private const OPTION_NAME = 'allowed_file_types';

	public const IMAGE_EXTENSIONS = [ 'jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp' ];

	private const ADMINISTRATOR_EXTENSIONS = [ 'htm', 'html', 'js' ];

	private string $title;
	private string $description;

	private array $wp_value;
	private array $settings_value;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param OptionsHelper $options_helper Helper functions for options.
	 */
	public function __construct(
		private OptionsHelper $options_helper,
	) {

		$this->title = __( 'Allowed File Types', 'dco-comment-attachment' );

		$this->init_wp_value();
		$this->init_settings_value();
		$this->init_description();
	}

	/**
	 * Retrieves the file extensions allowed for upload from the plugin settings in the specified format.
	 *
	 * @since 3.0.0
	 *
	 * @param AllowedFileTypesFormat $format The format for the output value (optional).
	 *                                       Default ARRAY.
	 *
	 * @return array The formatted array of allowed for upload file extensions.
	 */
	public function get_settings_value( AllowedFileTypesFormat $format = AllowedFileTypesFormat::ARRAY ): array {

		$value = array_map( $this->get_extension_dto( ... ), $this->settings_value );

		return $this->format_value( $value, $format );
	}

	/**
	 * Returns the settings field DTO for rendering setting.
	 *
	 * @since 3.0.0
	 *
	 * @return SettingsFieldDTO The settings field DTO.
	 */
	public function get_settings_field_dto(): SettingsFieldDTO {

		return new SettingsFieldDTO(
			id: self::OPTION_NAME,
			title: $this->title,
			callback: $this->render_settings_field( ... ),
			section: SettingsSection::PERMISSIONS
		);
	}

	/**
	 * Renders the settings field in the plugin settings page.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args The arguments list for rendering.
	 */
	public function render_settings_field( array $args ): void {

		(
			new AllowedFileTypesSettingsControl(
				name: $args['name'],
				groups: $this->get_allowed_wp_extension_groups(),
			)
		)->render();

		(
			new DescriptionSettingsControl(
				text: $this->description,
			)
		)->render();
	}

	private function format_value( array $value, AllowedFileTypesFormat $format ) {

		return match ( $format ) {

			AllowedFileTypesFormat::ARRAY => $value,
			AllowedFileTypesFormat::GROUPED_BY_TYPE => $this->format_grouped_value( $value ),
		};
	}

	private function format_grouped_value( array $value ): array {

		$groups = $this->get_groups();

		foreach ( $value as $extension ) {

			$group = $extension->group;

			if ( ! is_array( $groups[ $group ] ) ) {
				$groups[ $group ] = [];
			}

			$groups[ $group ][] = $extension;
		}

		array_walk(
			$groups,
			// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
			fn( array &$extensions, string $group ): AllowedFileTypesGroupDTO => $extensions = $this->get_group_dto( $group, $extensions )
		);

		return $groups;
	}

	/**
	 * Retrieves the allowed file types from WordPress in the specified format.
	 *
	 * @since 3.0.0
	 *
	 * @return array The formatted list of allowed extensions.
	 */
	private function get_allowed_wp_extension_groups(): array {

		$value = array_map( $this->get_extension_dto( ... ), $this->wp_value );

		return $this->format_value( $value, AllowedFileTypesFormat::GROUPED_BY_TYPE );
	}

	private function get_plugin_groups(): array {

		return [
			'image'       => __( 'image', 'dco-comment-attachment' ),
			'audio'       => __( 'audio', 'dco-comment-attachment' ),
			'video'       => __( 'video', 'dco-comment-attachment' ),
			'document'    => __( 'document', 'dco-comment-attachment' ),
			'spreadsheet' => __( 'spreadsheet', 'dco-comment-attachment' ),
			'interactive' => __( 'interactive', 'dco-comment-attachment' ),
			'text'        => __( 'text', 'dco-comment-attachment' ),
			'archive'     => __( 'archive', 'dco-comment-attachment' ),
			'code'        => __( 'code', 'dco-comment-attachment' ),
			'other'       => __( 'other', 'dco-comment-attachment' ),
		];
	}

	private function get_system_groups(): array {

		$system_groups      = wp_get_ext_types();
		$system_groups_list = array_keys( $system_groups );

		return array_combine(
			$system_groups_list,
			$system_groups_list
		);
	}

	private function get_groups(): array {

		return array_merge(
			$this->get_system_groups(),
			$this->get_plugin_groups()
		);
	}

	private function get_extension_dto( string $extension ): AllowedFileTypesExtensionDTO {

		$is_allowed_to_upload = in_array( $extension, $this->settings_value, true );

		return new AllowedFileTypesExtensionDTO(
			extension: $extension,
			group: $this->get_extension_group( $extension ),
			is_allowed_to_upload: $is_allowed_to_upload,
			is_embedded: $this->is_embedded_extension( $extension ),
			is_for_administrators: $this->is_administrator_extension( $extension ),
		);
	}

	private function get_group_dto( string $group, array $extensions ): AllowedFileTypesGroupDTO {

		return new AllowedFileTypesGroupDTO(
			name: $group,
			title: $this->get_group_title_by_name( $group ),
			extensions: $extensions,
		);
	}

	private function get_extension_group( string $extension ): string {

		$groups = $this->get_groups();

		$group = wp_ext2type( $extension );

		if ( ! isset( $groups[ $group ] ) ) {
			$group = 'other';
		}

		return $group;
	}

	private function get_group_title_by_name( string $name ): string {

		return $this->get_groups()[ $name ] ?? '';
	}

	private function is_administrator_extension( string $extension ): bool {

		return in_array( $extension, self::ADMINISTRATOR_EXTENSIONS, true );
	}

	private function is_embedded_extension( string $extension ): bool {

		return in_array( $extension, $this->get_embedded_extensions(), true );
	}

	private function get_embedded_extensions(): array {

		return array_merge(
			wp_get_video_extensions(),
			wp_get_audio_extensions(),
			self::IMAGE_EXTENSIONS,
		);
	}

	private function init_wp_value(): void {

		$this->wp_value = [];

		$raw_extensions = array_keys( get_allowed_mime_types() );

		foreach ( $raw_extensions as $extension ) {

			$this->wp_value = array_merge(
				$this->wp_value,
				explode( '|', $extension )
			);
		}
	}

	private function init_settings_value(): void {

		$this->settings_value = $this->options_helper->get_array_option( self::OPTION_NAME ) ?? $this->wp_value;
	}

	private function init_description(): void {

		$mark1 = __( 'available for embedding.', 'dco-comment-attachment' );
		$mark2 = __( 'allowed only for Administrators and Editors.', 'dco-comment-attachment' );

		$note1 = __(
			'To view all file extensions in a group, click the "Show All" link below that group.',
			'dco-comment-attachment'
		);
		$note2 = __(
			'Clicking on the name of a file type group (e.g., image) will select or deselect all extensions in that group.',
			'dco-comment-attachment'
		);

		$this->description = sprintf(
			'* — %s<br>** — %s<br><br>- %s<br>- %s',
			$mark1,
			$mark2,
			$note1,
			$note2
		);
	}

	public function filter_upload_mimes( array $mimes ): array {

		$allowed_extensions = wp_list_pluck( $this->get_settings_value(), 'extension' );

		$filtered_mimes = [];

		foreach ( $mimes as $mime => $mime_type ) {

			$extensions = explode( '|', $mime );

			$filtered_extensions = array_intersect( $extensions, $allowed_extensions );

			if ( ! $filtered_extensions ) {
				continue;
			}

			$extensions_row                    = implode( '|', $filtered_extensions );
			$filtered_mimes[ $extensions_row ] = $mime_type;
		}

		return $filtered_mimes;
	}
}
