<?php

declare(strict_types=1);

namespace DCO_CA\Helpers;

use DCO_CA\Enums\OptionType;

defined( 'ABSPATH' ) || die;

final class RequestHelper {

	private array $fields;

	public function __construct() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->fields = $_REQUEST;
	}

	public function get_int_field( string $field_name ): ?int {

		return $this->get_field( $field_name, OptionType::INT );
	}

	/*
	public function get_bool_option( string $option_name ): ?bool {

		return $this->get_option( $option_name, OptionType::BOOL );
	}

	public function get_string_option( string $option_name ): ?string {

		return $this->get_option( $option_name, OptionType::STRING );
	}*/

	public function get_array_field( string $field_name ): ?array {

		return $this->get_field( $field_name, OptionType::ARRAY );
	}

	private function get_field( string $field_name, OptionType $type ): mixed {

		if ( ! isset( $this->fields[ $field_name ] ) ) {
			return null;
		}

		$value = wp_unslash( $this->fields[ $field_name ] );

		return match ( $type ) {
			OptionType::INT => (int) $value,
			OptionType::BOOL => (bool) $value,
			OptionType::STRING => (string) $value,
			OptionType::ARRAY => is_array( $value ) ? $value : [],
		};
	}
}
