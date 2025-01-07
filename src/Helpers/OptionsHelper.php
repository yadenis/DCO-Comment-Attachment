<?php

declare(strict_types=1);

namespace DCO_CA\Helpers;

use DCO_CA\Enums\OptionType;
use DCO_CA\Services\PluginService;

defined( 'ABSPATH' ) || die;

final class OptionsHelper {

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

		$options = get_option( PluginService::SETTINGS_ID );

		return is_array( $options ) ? $options : [];
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
