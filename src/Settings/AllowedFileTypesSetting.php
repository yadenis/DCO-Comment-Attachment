<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\DTO\AllowedFileTypesExtensionDTO;
use DCO_CA\DTO\AllowedFileTypesGroupDTO;
use DCO_CA\DTO\SettingFieldDTO;
use DCO_CA\Options;
use DCO_CA\Enums\AllowedFileTypesFormat;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Interfaces\Setting;
use DCO_CA\Services\AttachmentService;
use DCO_CA\SettingControls\AllowedFileTypesSettingControl;
use DCO_CA\SettingControls\DescriptionSettingControl;

defined( 'ABSPATH' ) || die;

final class AllowedFileTypesSetting implements Setting {

	private const OPTION_NAME = 'allowed_file_types';

	private string $title;
	private string $description;

	private array $setting_value;
	private array $system_value;

	public function __construct(
		private Options $options,
		private AttachmentService $attachment_service,
	) {

		$this->title = __( 'Allowed File Types', 'dco-comment-attachment' );

		$mark1 = __( 'available for embedding.', 'dco-comment-attachment' );
		$mark2 = __( 'allowed only for Administrators and Editors.', 'dco-comment-attachment' );

		$this->description = "* — {$mark1}<br>** — {$mark2}";

		$value = $this->options->get_array_option( self::OPTION_NAME );

		$this->setting_value = $value ?? $this->get_system_value();
	}

	public function get_value( AllowedFileTypesFormat $format = AllowedFileTypesFormat::ARRAY ): array {

		$value = array_map( $this->get_extension_dto( ... ), $this->setting_value );

		return $this->format_value( $value, $format );
	}

	public function get_all_extensions( AllowedFileTypesFormat $format = AllowedFileTypesFormat::ARRAY ): array {

		$system_value = $this->get_system_value();

		$value = array_map( $this->get_extension_dto( ... ), $system_value );

		return $this->format_value( $value, $format );
	}

	public function get_setting_field_dto(): SettingFieldDTO {

		return new SettingFieldDTO(
			id: self::OPTION_NAME,
			title: $this->title,
			callback: $this->render_setting_field( ... ),
			section: SettingsSection::PERMISSIONS
		);
	}

	public function render_setting_field( array $args ): void {

		(
			new AllowedFileTypesSettingControl(
				name: $args['name'],
				groups: $this->get_all_extensions( AllowedFileTypesFormat::GROUPED_ARRAY ),
			)
		)->render();

		(
			new DescriptionSettingControl(
				text: $this->description,
			)
		)->render();
	}

	public function apply_file_types_filter_to_function( callable $callback, array $arguments ): mixed {

		add_filter( 'upload_mimes', $this->filter_upload_mimes( ... ), 999 );

		$result = call_user_func_array( $callback, $arguments );

		remove_filter( 'upload_mimes', $this->filter_upload_mimes( ... ), 999 );

		return $result;
	}

	private function format_value( array $value, AllowedFileTypesFormat $format ) {

		return match ( $format ) {

			AllowedFileTypesFormat::ARRAY => $value,
			AllowedFileTypesFormat::GROUPED_ARRAY => $this->format_grouped_value( $value ),
		};
	}

	private function get_system_value(): array {

		if ( isset( $this->system_value ) ) {
			return $this->system_value;
		}

		$this->system_value = [];

		$raw_extensions = array_keys( get_allowed_mime_types() );

		foreach ( $raw_extensions as $extension ) {

			$this->system_value = array_merge(
				$this->system_value,
				explode( '|', $extension )
			);
		}

		return $this->system_value;
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
			fn( &$value, $key ) => $value = $this->get_group_dto( $key, $value )
		);

		return $groups;
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

		$is_allowed = in_array( $extension, $this->setting_value, true );

		return new AllowedFileTypesExtensionDTO(
			extension: $extension,
			group: $this->get_extension_group( $extension ),
			is_allowed: $is_allowed,
			is_embedded: $this->attachment_service->is_embedded_extension( $extension ),
			is_for_administrators: $this->attachment_service->is_administrator_extension( $extension ),
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

		$groups = $this->get_groups();

		if ( ! isset( $groups[ $name ] ) ) {
			return '';
		}

		return $groups[ $name ];
	}

	public function filter_upload_mimes( array $mimes ): array {

		$allowed_extensions = wp_list_pluck( $this->get_value(), 'extension' );

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
