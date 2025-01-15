<?php
/**
 * Backward Compatibility: Functions
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || die;

if ( ! function_exists( 'mb_ucfirst' ) ) {

	// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound, Universal.NamingConventions.NoReservedKeywordParameterNames.stringFound

	/**
	 * Polyfill for the mb_ucfirst function (introduced in PHP 8.4).
	 *
	 * This function ensures compatibility with PHP versions prior to 8.4,
	 * as the plugin supports PHP 8.1 and later.
	 *
	 * @since 3.0.0
	 *
	 * @param string      $string   The input string.
	 * @param string|null $encoding The string encoding.
	 *
	 * @return string The string with the first character converted to uppercase.
	 */
	function mb_ucfirst( string $string, ?string $encoding = null ): string {

		return mb_strtoupper( mb_substr( $string, 0, 1, $encoding ), $encoding ) . mb_substr( $string, 1, null, $encoding );
	}

	// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound, Universal.NamingConventions.NoReservedKeywordParameterNames.stringFound
}
