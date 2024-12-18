<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\Enums\OptionType;

defined( 'ABSPATH' ) || die;

final class Options {

	private const OPTIONS_KEY = 'dco_ca';

	private array $options;

	public function __construct() {

		$this->options = $this->get_options();
	}

	public function get_int_option( string $name ): ?int {

		return $this->get_option( $name, OptionType::INT );
	}

	public function get_bool_option( string $name ): ?bool {

		return $this->get_option( $name, OptionType::BOOL );
	}

	public function get_string_option( string $name ): ?string {

		return $this->get_option( $name, OptionType::STRING );
	}

	public function get_array_option( string $name ): ?array {

		return $this->get_option( $name, OptionType::ARRAY );
	}

	private function get_options(): array {

		$options = get_option( self::OPTIONS_KEY );
		if ( ! is_array( $options ) ) {
			return [];
		}

		return $options;
	}

	private function get_option( string $name, OptionType $type ): mixed {

		if ( ! isset( $this->options[ $name ] ) ) {
			return null;
		}

		$value = $this->options[ $name ];

		/**
		 * Filters the value of the plugin option.
		 *
		 * The dynamic portion of the hook name, `$name`, refers to the option name.
		 *
		 * @since 2.0.0
		 *
		 * @param mixed $value Value of the option.
		 */
		$value = apply_filters( "dco_ca_get_option_{$name}", $value );

		return match ( $type ) {
			OptionType::INT => (int) $value,
			OptionType::BOOL => (bool) $value,
			OptionType::STRING => (string) $value,
			OptionType::ARRAY => is_array( $value ) ? $value : [],
		};
	}
}

/*
private function get_default_options(): array {

	return array(
		'max_upload_size'          => $this->max_upload_size->get_settings_value_in_megabytes(),
		'required_attachment'      => false,
		'embed_attachment'         => true,
		'autoembed_links'          => true,
		'enable_multiple_upload'   => false,
		'combine_images'           => true,
		'gallery_size'             => 'thumbnail',
		'thumbnail_size'           => 'medium',
		'link_thumbnail'           => false,
		'allowed_file_types'       => $this->get_allowed_file_types( 'array' ),
		'who_can_upload'           => 1,
		'manually_moderation'      => 0,
		'delete_with_comment'      => 1,
		'delete_attachment_action' => 1,
	);
}*/
