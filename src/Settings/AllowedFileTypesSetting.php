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

/**
 * Provides functionality to Allowed File Types plugin setting.
 *
 * @since 3.0.0
 */
final class AllowedFileTypesSetting implements Setting {

	private const OPTION_NAME = 'allowed_file_types';

	public const IMAGE_EXTENSIONS = [ 'jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp' ];

	private const ADMINISTRATOR_EXTENSIONS = [ 'htm', 'html', 'js' ];

	/**
	 * The settings field title.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private string $title;

	/**
	 * The settings field description.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private string $description;

	/**
	 * The list of extension groups.
	 *
	 * @since 3.0.0
	 *
	 * @var array<string, string>
	 */
	private array $extension_groups;

	/**
	 * The list of embedded extensions.
	 *
	 * @since 3.0.0
	 *
	 * @var string[]
	 */
	private array $embedded_extensions;

	/**
	 * The list of file extensions allowed for upload in WordPress.
	 *
	 * @since 3.0.0
	 *
	 * @var string[]
	 */
	private array $wp_extensions;

	/**
	 * The list of file extensions allowed for upload in the plugin settings.
	 *
	 * @since 3.0.0
	 *
	 * @var string[]
	 */
	private array $setting_extensions;

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

		$this->init_description();
		$this->init_extension_groups();
		$this->init_embedded_extensions();
		$this->init_wp_extensions();
		$this->init_setting_extensions();
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
	public function get_setting_value( AllowedFileTypesFormat $format = AllowedFileTypesFormat::ARRAY ): array {

		$extensions = array_map( $this->get_extension_dto( ... ), $this->setting_extensions );

		return $this->format_extensions_list( $extensions, $format );
	}

	/**
	 * Returns the settings field DTO for rendering Allowed File Types setting.
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
	 * Renders the Allowed File Types settings field in the plugin settings page.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args The arguments list for rendering.
	 */
	public function render_settings_field( array $args ): void {

		if ( empty( $args['name'] ) ) {
			return;
		}

		(
			new AllowedFileTypesSettingsControl(
				name: $args['name'],
				groups: $this->get_allowed_wp_extension_groups(),
			)
		)->render_control();

		(
			new DescriptionSettingsControl(
				text: $this->description,
			)
		)->render_control();
	}

	/**
	 * Formats the extensions into the specified format.
	 *
	 * @since 3.0.0
	 *
	 * @param array                  $extensions The extensions to format.
	 * @param AllowedFileTypesFormat $format The format type.
	 *
	 * @return array The formatted extensions.
	 */
	private function format_extensions_list( array $extensions, AllowedFileTypesFormat $format ) {

		return match ( $format ) {
			AllowedFileTypesFormat::ARRAY => $extensions,
			AllowedFileTypesFormat::GROUPED_BY_TYPE => $this->group_extensions_by_type( $extensions ),
		};
	}

	/**
	 * Groups the file extensions by their type.
	 *
	 * @since 3.0.0
	 *
	 * @param array $extensions The array of file extensions to be grouped.
	 *
	 * @return AllowedFileTypesGroupDTO[] The grouped file extensions.
	 */
	private function group_extensions_by_type( array $extensions ): array {

		$groups = $this->extension_groups;

		foreach ( $extensions as $extension ) {

			$group = $extension->group;

			if ( ! is_array( $groups[ $group ] ) ) {
				$groups[ $group ] = [];
			}

			$groups[ $group ][] = $extension;
		}

		return array_map(
			fn( array $extensions, string $group ): AllowedFileTypesGroupDTO => $this->get_extensions_group_dto( $group, $extensions ),
			$groups,
			array_keys( $groups )
		);
	}

	/**
	 * Retrieves the allowed file extension groups for upload from WordPress.
	 *
	 * @since 3.0.0
	 *
	 * @return AllowedFileTypesGroupDTO[] The list of file extension groups allowed for upload.
	 */
	private function get_allowed_wp_extension_groups(): array {

		$value = array_map( $this->get_extension_dto( ... ), $this->wp_extensions );

		return $this->format_extensions_list( $value, AllowedFileTypesFormat::GROUPED_BY_TYPE );
	}

	/**
	 * Returns the allowed file extension DTO.
	 *
	 * @since 3.0.0
	 *
	 * @param string $extension The file extension.
	 *
	 * @return AllowedFileTypesExtensionDTO The allowed file extension DTO.
	 */
	private function get_extension_dto( string $extension ): AllowedFileTypesExtensionDTO {

		$is_allowed_to_upload = in_array( $extension, $this->setting_extensions, true );

		return new AllowedFileTypesExtensionDTO(
			extension: $extension,
			group: $this->get_extension_group_name( $extension ),
			is_allowed_to_upload: $is_allowed_to_upload,
			is_embedded: $this->is_embedded_extension( $extension ),
			is_for_administrators: $this->is_administrator_extension( $extension ),
		);
	}

	/**
	 * Returns the allowed file extensions group DTO.
	 *
	 * @since 3.0.0
	 *
	 * @param string                         $group The group name.
	 * @param AllowedFileTypesExtensionDTO[] $extensions The list of extensions.
	 *
	 * @return AllowedFileTypesGroupDTO The allowed file extensions group DTO.
	 */
	private function get_extensions_group_dto( string $group, array $extensions ): AllowedFileTypesGroupDTO {

		return new AllowedFileTypesGroupDTO(
			name: $group,
			title: $this->get_group_title_by_name( $group ),
			extensions: $extensions,
		);
	}

	/**
	 * Retrieves the group name for a file extension.
	 *
	 * @since 3.0.0
	 *
	 * @param string $extension The file extension.
	 *
	 * @return string The group name.
	 */
	private function get_extension_group_name( string $extension ): string {

		$group = wp_ext2type( $extension );

		return $this->extension_groups[ $group ] ?? 'other';
	}

	/**
	 * Retrieves the group title by its name.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name The group name.
	 *
	 * @return string The group title.
	 */
	private function get_group_title_by_name( string $name ): string {

		return $this->extension_groups[ $name ] ?? '';
	}

	/**
	 * Checks if the extension is for administrators use only.
	 *
	 * @since 3.0.0
	 *
	 * @param string $extension The file extension.
	 *
	 * @return bool True if the extension is for administrators, false otherwise.
	 */
	private function is_administrator_extension( string $extension ): bool {

		return in_array( $extension, self::ADMINISTRATOR_EXTENSIONS, true );
	}

	/**
	 * Checks if the extension is an embedded extension.
	 *
	 * @since 3.0.0
	 *
	 * @param string $extension The file extension.
	 *
	 * @return bool True if the extension is embedded, false otherwise.
	 */
	private function is_embedded_extension( string $extension ): bool {

		return in_array( $extension, $this->embedded_extensions, true );
	}

	/**
	 * Initializes the setting description.
	 *
	 * @since 3.0.0
	 */
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

	/**
	 * Initializes the extension groups.
	 *
	 * @since 3.0.0
	 */
	private function init_extension_groups(): void {

		$wp_groups = array_keys( wp_get_ext_types() );

		$this->extension_groups = array_merge(
			array_combine(
				$wp_groups,
				$wp_groups
			),
			[
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
			]
		);
	}

	/**
	 * Initializes the embedded extensions.
	 *
	 * @since 3.0.0
	 */
	private function init_embedded_extensions(): void {

		$this->embedded_extensions = array_merge(
			wp_get_video_extensions(),
			wp_get_audio_extensions(),
			self::IMAGE_EXTENSIONS,
		);
	}

	/**
	 * Initializes the WordPress extensions.
	 *
	 * @since 3.0.0
	 */
	private function init_wp_extensions(): void {

		$this->wp_extensions = [];

		$raw_extensions = array_keys( get_allowed_mime_types() );

		foreach ( $raw_extensions as $extension ) {

			$this->wp_extensions = array_merge(
				$this->wp_extensions,
				explode( '|', $extension )
			);
		}

		$this->wp_extensions = array_unique( $this->wp_extensions );
	}

	/**
	 * Initializes the setting extensions.
	 *
	 * @since 3.0.0
	 */
	private function init_setting_extensions(): void {

		$this->setting_extensions = $this->options_helper->get_array_option( self::OPTION_NAME ) ?? $this->wp_extensions;
	}

	/**
	 * Filters the allowed MIME types and file extensions for upload based on the plugin settings.
	 *
	 * @since 3.0.0
	 *
	 * @param array<string, string> $mimes The MIME types to filter.
	 *
	 * @return array<string, string> The filtered MIME types.
	 */
	public function filter_upload_mimes( array $mimes ): array {

		$allowed_extensions = wp_list_pluck( $this->get_setting_value(), 'extension' );

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
