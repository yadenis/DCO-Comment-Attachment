<?php
/**
 * Helpers: Request
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

use DCO_CA\Enums\RequestFieldType;

defined( 'ABSPATH' ) || die;

/**
 * Provides functionality to retrieve fields from the request
 * and ensure they are properly cast to the desired type.
 *
 * @since 3.0.0
 */
final class RequestHelper {

	/**
	 * The request fields.
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	private array $fields;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->fields = $_REQUEST;
	}

	/**
	 * Retrieves an integer field from the request.
	 *
	 * @since 3.0.0
	 *
	 * @param string $field_name The field name.
	 *
	 * @return int|null The field value cast to integer,
	 *                  or null if the field doesn't exist.
	 */
	public function get_int_field( string $field_name ): ?int {

		return $this->get_field( $field_name, RequestFieldType::INT );
	}

	/**
	 * Retrieves an array field from the request.
	 *
	 * @since 3.0.0
	 *
	 * @param string $field_name The field name.
	 *
	 * @return array|null The field value cast to array,
	 *                    or null if the field doesn't exist.
	 */
	public function get_array_field( string $field_name ): ?array {

		return $this->get_field( $field_name, RequestFieldType::ARRAY );
	}

	/**
	 * Retrieves a specific field from the request based on the field name and type.
	 *
	 * @since 3.0.0
	 *
	 * @param string           $field_name The field name.
	 * @param RequestFieldType $type The expected type of the field value.
	 *
	 * @return mixed The field value cast to the specified type,
	 *               or null if the field doesn't exist.
	 */
	private function get_field( string $field_name, RequestFieldType $type ): mixed {

		if ( ! isset( $this->fields[ $field_name ] ) ) {
			return null;
		}

		$value = wp_unslash( $this->fields[ $field_name ] );

		return match ( $type ) {
			RequestFieldType::INT => (int) $value,
			RequestFieldType::ARRAY => is_array( $value ) ? $value : [],
		};
	}
}
