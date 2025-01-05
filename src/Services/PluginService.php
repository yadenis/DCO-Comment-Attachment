<?php

declare(strict_types=1);

namespace DCO_CA\Services;

defined( 'ABSPATH' ) || die;

final class PluginService {

	public const VERSION           = DCO_CA_VERSION;
	public const BASENAME          = DCO_CA_BASENAME;
	public const UPLOAD_FIELD_NAME = 'attachment';
	public const SETTINGS_ID       = 'dco_ca';

	public function __construct(
		private UserService $user_service,
		private PostService $post_service,
	) {
	}

	public function enqueue_style( string $style_name ): void {

		wp_enqueue_style(
			handle: $style_name,
			src: $this->get_asset_url( "{$style_name}.css" ),
			ver: self::VERSION
		);
	}

	public function enqueue_script( string $script_name, array $script_data = [] ): void {

		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
		wp_enqueue_script(
			handle: $script_name,
			src: $this->get_asset_url( "{$script_name}.js" ),
			ver: DCO_CA_VERSION,
			args: [ 'in_footer' => true ]
		);

		if ( $script_data ) {

			wp_localize_script(
				$script_name,
				'dco_ca',
				$script_data
			);
		}
	}

	public function is_form_enabled(): bool {

		$disable = false;

		if ( ! $this->post_service->is_current_post_used_comments() ) {
			$disable = true;
		}

		if ( ! $this->user_service->is_current_user_can_upload_attachment() ) {
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

	public function the_kses_post( string $html ): void {

		add_filter( 'wp_kses_allowed_html', $this->extend_wp_kses_post( ... ), 10, 2 );

		echo wp_kses_post( $html );

		remove_filter( 'wp_kses_allowed_html', $this->extend_wp_kses_post( ... ), 10, 2 );
	}

	private function get_asset_url( string $filename ): string {

		return DCO_CA_URL . "assets/{$filename}";
	}

	public function extend_wp_kses_post( array $allowedposttags, string $context ): array {

		if ( 'post' !== $context ) {
			return $allowedposttags;
		}

		return array_merge(
			$allowedposttags,
			[
				'input' => [
					'class'    => true,
					'id'       => true,
					'name'     => true,
					'type'     => true,
					'accept'   => true,
					'multiple' => true,
				],
			]
		);
	}
}
