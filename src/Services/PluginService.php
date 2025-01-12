<?php
/**
 * Services: Plugin
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA\Services;

defined( 'ABSPATH' ) || die;

/**
 * Service for handling plugin-related operations.
 *
 * @since 3.0.0
 */
final class PluginService {

	public const VERSION                = DCO_CA_VERSION;
	public const BASENAME               = DCO_CA_BASENAME;
	public const UPLOAD_FIELD_NAME      = 'attachment';
	public const SETTINGS_ID            = 'dco_ca';
	public const ATTACHMENT_ID_META_KEY = 'attachment_id';

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 *
	 * @param UserService $user_service Service functions for users.
	 * @param PostService $post_service Service functions for posts.
	 */
	public function __construct(
		private UserService $user_service,
		private PostService $post_service,
	) {
	}

	/**
	 * Enqueues a CSS style.
	 *
	 * @since 3.0.0
	 *
	 * @param string $style_name The style name to enqueue.
	 */
	public function enqueue_style( string $style_name ): void {

		wp_enqueue_style(
			handle: $style_name,
			src: $this->get_asset_url( "{$style_name}.css" ),
			ver: self::VERSION
		);
	}

	/**
	 * Enqueues a JavaScript script.
	 *
	 * @since 3.0.0
	 *
	 * @param string $script_name The script name to enqueue.
	 * @param array  $script_data Data to be localized with the script (optional).
	 */
	public function enqueue_script( string $script_name, array $script_data = [] ): void {

		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
		wp_enqueue_script(
			handle: $script_name,
			src: $this->get_asset_url( "{$script_name}.js" ),
			ver: DCO_CA_VERSION,
			args: [ 'in_footer' => true ]
		);

		if ( $script_data ) {
			wp_localize_script( $script_name, 'dco_ca', $script_data );
		}
	}

	/**
	 * Checks if the attachment upload form is enabled.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if the form is enabled, false otherwise.
	 */
	public function is_form_enabled(): bool {

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
		return ! apply_filters(
			'dco_ca_disable_attachment_field',
			! $this->post_service->is_current_post_used_comments() ||
			! $this->user_service->is_current_user_can_upload_attachment()
		);
	}

	/**
	 * Outputs sanitized HTML content with additional KSES filters applied.
	 *
	 * @since 3.0.0
	 *
	 * @param string $html The HTML content to output.
	 */
	public function the_kses_post( string $html ): void {

		add_filter( 'wp_kses_allowed_html', $this->extend_wp_kses_post( ... ), 10, 2 );

		echo wp_kses_post( $html );

		remove_filter( 'wp_kses_allowed_html', $this->extend_wp_kses_post( ... ), 10, 2 );
	}

	/**
	 * Retrieves the URL for a given asset file.
	 *
	 * @since 3.0.0
	 *
	 * @param string $filename The asset filename.
	 *
	 * @return string The full URL to the asset.
	 */
	private function get_asset_url( string $filename ): string {

		return DCO_CA_URL . "assets/{$filename}";
	}

	/**
	 * Extends the allowed HTML tags for the KSES post context.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $allowedposttags The default allowed tags.
	 * @param string $context         The context in which the filter is applied.
	 *
	 * @return array The modified allowed tags.
	 */
	public function extend_wp_kses_post( array $allowedposttags, string $context ): array {

		if ( 'post' !== $context ) {
			return $allowedposttags;
		}

		return [
			...$allowedposttags,
			'input' => [
				'class'    => true,
				'id'       => true,
				'name'     => true,
				'type'     => true,
				'accept'   => true,
				'multiple' => true,
			],
		];
	}
}
