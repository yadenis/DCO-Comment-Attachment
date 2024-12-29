<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\DTO\SettingFieldDTO;
use DCO_CA\Options;
use DCO_CA\Enums\AllowedFileTypesFormat;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingControls\Description;

defined( 'ABSPATH' ) || die;

final class AllowedFileTypes implements Setting {

	private const OPTION_NAME = 'allowed_file_types';

	public function __construct(
		private Options $options,
	) {
	}

	public function get_value( AllowedFileTypesFormat $format = AllowedFileTypesFormat::ARRAY ): array {

		$value = $this->options->get_array_option( self::OPTION_NAME );
		if ( null === $value ) {
			$value = $this->get_system_value();
		}

		return match ( $format ) {

			AllowedFileTypesFormat::ARRAY => $value,
			AllowedFileTypesFormat::GROUPED_ARRAY => $this->format_grouped_value( $value ),
		};
	}

	public function get_setting_field_dto(): SettingFieldDTO {

		return new SettingFieldDTO(
			id: self::OPTION_NAME,
			title: __( 'Allowed File Types', 'dco-comment-attachment' ),
			callback: $this->render_setting_field( ... ),
			section: SettingsSection::PERMISSIONS
		);
	}

	public function render_setting_field( array $args ): void {

		(
			new Checkbox(
				name: $args['name'],
				id: $args['id'],
				checked: true === $this->get_value(),
			)
		)->render();

		(
			new Description(
				text:  '* — ' . __( 'available for embedding.', 'dco-comment-attachment' ) . '<br>** — ' . __( 'allowed only for Administrators and Editors.', 'dco-comment-attachment' ),
			)
		)->render();
	}

	public function apply_file_types_filter_to_function( callable $callback, array $arguments ): mixed {

		add_filter( 'upload_mimes', $this->filter_upload_mimes( ... ), 999 );

		$result = call_user_func_array( $callback, $arguments );

		remove_filter( 'upload_mimes', $this->filter_upload_mimes( ... ), 999 );

		return $result;
	}

	private function get_system_value(): array {

		$raw_extensions = array_keys( get_allowed_mime_types() );
		$extensions     = [];

		foreach ( $raw_extensions as $extension ) {

			$exts = explode( '|', $extension );

			foreach ( $exts as $ext ) {
				$extensions[] = $ext;
			}
		}

		return $extensions;
	}

	private function format_grouped_value( array $value ): array {

		$groups = $this->get_groups();

		foreach ( $value as $extension ) {

			$group = wp_ext2type( $extension );

			if ( ! isset( $groups[ $group ]['extensions'] ) ) {
				$group = 'other';
			}

			$groups[ $group ]['extensions'][] = $extension;
		}

		return $groups;
	}

	private function get_plugin_groups(): array {

		$groups_list = [
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

		$groups = [];

		foreach ( $groups_list as $key => $name ) {

			$groups[ $key ] = [
				'name'       => $name,
				'extensions' => [],
			];
		}

		return $groups;
	}

	private function get_system_groups(): array {

		$system_groups      = wp_get_ext_types();
		$system_groups_list = array_keys( $system_groups );

		$groups = [];

		foreach ( $system_groups_list as $group ) {

			$groups[ $group ] = [
				'name'       => $group,
				'extensions' => [],
			];
		}

		return $groups;
	}

	private function get_groups(): array {

		return array_merge(
			$this->get_system_groups(),
			$this->get_plugin_groups()
		);
	}

	public function filter_upload_mimes( array $mimes ): array {

		$allowed_extensions = $this->get_value();

		$filtered_mimes = [];

		foreach ( $mimes as $mime => $mime_type ) {

			$extensions = explode( '|', $mime );

			$filtered_extensions = [];

			foreach ( $extensions as $extension ) {

				if ( ! in_array( $extension, $allowed_extensions, true ) ) {
					continue;
				}

				$filtered_extensions[] = $extension;
			}

			if ( ! $filtered_extensions ) {
				continue;
			}

			$extensions_row                    = implode( '|', $filtered_extensions );
			$filtered_mimes[ $extensions_row ] = $mime_type;
		}

		return $filtered_mimes;
	}
}
