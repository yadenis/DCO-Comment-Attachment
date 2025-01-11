<?php
/**
 * Helpers: Options
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA\Helpers;

use DCO_CA\Enums\OptionType;
use DCO_CA\Services\PluginService;

defined( 'ABSPATH' ) || die;

/**
 * Ensures that the retrieved options are properly cast to the desired type.
 *
 * @since 3.0.0
 */
final class OptionsHelper {

	/**
	 * The plugin options from the database.
	 * 
	 * @since 3.0.0
	 *
	 * @var array
	 */
	private array $options;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		$this->init_options();
	}

	/**
	 * Retrieves an integer option.
	 *
	 * @since 3.0.0
	 *
	 * @param string $option_name The option name.
	 *
	 * @return int|null The option value cast to integer,
	 *                  or null if the option doesn't exist.
	 */
	public function get_int_option( string $option_name ): ?int {

		return $this->get_option( $option_name, OptionType::INT );
	}

	/**
	 * Retrieves a boolean option.
	 *
	 * @since 3.0.0
	 *
	 * @param string $option_name The option name.
	 *
	 * @return bool|null The option value cast to boolean,
	 *                   or null if the option doesn't exist.
	 */
	public function get_bool_option( string $option_name ): ?bool {

		return $this->get_option( $option_name, OptionType::BOOL );
	}

	/**
	 * Retrieves a string option.
	 *
	 * @since 3.0.0
	 *
	 * @param string $option_name The option name.
	 *
	 * @return string|null The option value cast to string,
	 *                     or null if the option doesn't exist.
	 */
	public function get_string_option( string $option_name ): ?string {

		return $this->get_option( $option_name, OptionType::STRING );
	}

	/**
	 * Retrieves an array option.
	 *
	 * @since 3.0.0
	 *
	 * @param string $option_name The option name.
	 *
	 * @return array|null The option value cast to array,
	 *                    or null if the option doesn't exist.
	 */
	public function get_array_option( string $option_name ): ?array {

		return $this->get_option( $option_name, OptionType::ARRAY );
	}

	/**
	 * Retrieves a specific option based on the name and type.
	 *
	 * @since 3.0.0
	 *
	 * @param string     $option_name The option name.
	 * @param OptionType $type The expected type of the option value.
	 *
	 * @return mixed The option value cast to the specified type,
	 *               or null if the option is not set.
	 */
	private function get_option( string $option_name, OptionType $type ): mixed {

		if ( ! isset( $this->options[ $option_name ] ) ) {
			return null;
		}

		$value = $this->options[ $option_name ];

		/**
		 * Filters the value of the plugin option.
		 *
		 * The dynamic portion of the hook name, `$option_name`, refers to the option name.
		 *
		 * @since 2.0.0
		 *
		 * @param mixed $value Value of the option.
		 */
		$value = apply_filters( "dco_ca_get_option_{$option_name}", $value );

		return match ( $type ) {
			OptionType::INT => (int) $value,
			OptionType::BOOL => (bool) $value,
			OptionType::STRING => (string) $value,
			OptionType::ARRAY => is_array( $value ) ? $value : [],
		};
	}

	/**
	 * Initializes the plugin options.
	 *
	 * @since 3.0.0
	 */
	private function init_options(): void {

		$options = get_option( PluginService::SETTINGS_ID );

		$this->options = is_array( $options ) ? $options : [];
	}
}
