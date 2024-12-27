<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\Options;
use DCO_CA\Settings\Enums\AllowedFileTypesFormat;
use DCO_CA\Settings\Interfaces\Setting;

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
			'image'       => esc_html__( 'image', 'dco-comment-attachment' ),
			'audio'       => esc_html__( 'audio', 'dco-comment-attachment' ),
			'video'       => esc_html__( 'video', 'dco-comment-attachment' ),
			'document'    => esc_html__( 'document', 'dco-comment-attachment' ),
			'spreadsheet' => esc_html__( 'spreadsheet', 'dco-comment-attachment' ),
			'interactive' => esc_html__( 'interactive', 'dco-comment-attachment' ),
			'text'        => esc_html__( 'text', 'dco-comment-attachment' ),
			'archive'     => esc_html__( 'archive', 'dco-comment-attachment' ),
			'code'        => esc_html__( 'code', 'dco-comment-attachment' ),
			'other'       => esc_html__( 'other', 'dco-comment-attachment' ),
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
