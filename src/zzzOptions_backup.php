<?php

declare(strict_types=1);

namespace DCO_CA;

defined( 'ABSPATH' ) || die;

final class Options2s {

	private const ID = 'dco_ca';

	private array $options;

	public function __construct() {

		$this->options = $this->get_options();
	}

	private function get_options(): array {

		if ( isset( $this->options ) ) {
			return $this->options;
		}

		$default = $this->get_default_options();
		$options = get_option( self::ID );

		return wp_parse_args( $options, $default );
	}

	public static function get_option( $name ): mixed {

		if ( ! isset( static::$options[ $name ] ) ) {
			return false;
		}

		/**
		 * Filters the value of the plugin option.
		 *
		 * The dynamic portion of the hook name, `$name`, refers to the option name.
		 *
		 * @since 2.0.0
		 *
		 * @param mixed $value Value of the option.
		 */
		return apply_filters( "dco_ca_get_option_{$name}", static::$options[ $name ] );
	}

	public static function get_int_option( $name ): int {

		return (int) static::get_option( $name );
	}

	public static function get_bool_option( $name ): bool {

		return (bool) static::get_option( $name );
	}

	public static function get_string_option( $name ): string {

		return (string) static::get_option( $name );
	}

	public static function is_comments_used(): bool {

		return is_singular() && comments_open();
	}

	public static function is_attachment_field_enabled(): bool {

		$disable = false;

		if ( ! static::is_user_can_upload() ) {
			$disable = true;
		}

		/**
		 * Filters whether to disable the attachment upload field.
		 *
		 * Prevents the attachment upload field from being appended to the commenting form.
		 *
		 * @since 1.1.0
		 *
		 * @param bool $disable Whether to disable the attachment upload field.
		 *                      Returning true to the filter will disable the attachment field.
		 *                      Default false.
		 */
		return ! apply_filters( 'dco_ca_disable_attachment_field', $disable );
	}

	public static function is_user_can_upload(): bool {

		$who_can_upload = static::get_int_option( 'who_can_upload' );

		// All users.
		if ( 1 === $who_can_upload ) {
			return true;
		}

		// Only logged users.
		if ( 2 === $who_can_upload && is_user_logged_in() ) {
			return true;
		}

		return false;
	}

	private function get_default_options() {
	}
}
