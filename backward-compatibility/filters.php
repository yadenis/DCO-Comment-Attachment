<?php
/**
 * Backward Compatibility: Filters
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

add_filter(
	'dco_ca_attachment_area',
	dco_ca_apply_deprecated_attachment_field_filter( ... )
);

add_filter(
	'dco_ca_disable_display_attachments',
	dco_ca_apply_deprecated_disable_display_attachment_filter( ... )
);

function dco_ca_apply_deprecated_attachment_field_filter( string $attachment_area ): string {

	/**
	 * Filters the attachment field markup.
	 *
	 * @since 1.1.1
	 *
	 * @param string $markup HTML markup for the attachment field.
	 */
	return apply_filters( 'dco_ca_attachment_field', $attachment_area );
}

function dco_ca_apply_deprecated_disable_display_attachment_filter( bool $disable ): bool {

	/**
	 * Filters whether to disable the attachment display.
	 *
	 * Prevents the attachment from being displayed in the comments list.
	 *
	 * @since 1.2.0
	 *
	 * @param bool $bool Whether to disable the attachment display.
	 *                   Returning true to the filter will disable the attachment display.
	 *                   Default false.
	 */
	return (bool) apply_filters( 'dco_ca_disable_display_attachment', $disable );
}
