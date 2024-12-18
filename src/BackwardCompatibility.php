<?php

declare(strict_types=1);

namespace DCO_CA;

defined( 'ABSPATH' ) || die;

final class BackwardCompatibility {

	public function __construct() {

		add_filter( 'dco_ca_attachment_area', $this->dco_ca_attachment_field_filter( ... ) );
	}

	public function dco_ca_attachment_field_filter( string $attachment_area ): string {

		/**
		 * Filters the attachment field markup.
		 *
		 * @since 1.1.1
		 *
		 * @param string $markup HTML markup for the attachment field.
		 */
		return apply_filters( 'dco_ca_attachment_field', $attachment_area );
	}
}
