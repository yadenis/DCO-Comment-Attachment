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
		private SettingsService $settings_service,
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

	public function enqueue_script( string $script_name ): void {

		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
		wp_enqueue_script(
			handle: $script_name,
			src: $this->get_asset_url( "{$script_name}.js" ),
			ver: DCO_CA_VERSION,
			args: [ 'in_footer' => true ]
		);

		if ( 'dco-comment-attachment' === $script_name ) {

			wp_localize_script(
				'dco-comment-attachment',
				'dco_ca',
				[
					'commenting_form_not_found' => esc_attr__( 'The commenting form not found.', 'dco-comment-attachment' ),
				]
			);
		}

		if ( 'dco-comment-attachment-admin' === $script_name ) {

			wp_localize_script(
				'dco-comment-attachment-admin',
				'dcoCA',
				[
					'set_attachment_title'            => esc_attr__( 'Set Comment Attachment', 'dco-comment-attachment' ),
					'add_attachment_label'            => esc_attr__( 'Add Attachment', 'dco-comment-attachment' ),
					'replace_attachment_label'        => esc_attr__( 'Replace Attachment', 'dco-comment-attachment' ),
					'delete_attachment_confirm_text'  => esc_attr__( 'This action will delete the attachment from the Media Library and cannot be undone. Continue?', 'dco-comment-attachment' ),
					'delete_attachments_confirm_text' => esc_attr__( 'This action will delete the attachments from the Media Library and cannot be undone. Continue?', 'dco-comment-attachment' ),
					'show_all_label'                  => esc_attr__( 'Show all', 'dco-comment-attachment' ),
					'show_less_label'                 => esc_attr__( 'Show less', 'dco-comment-attachment' ),
					'is_delete_attachment_from_media_library' => boolval( $this->settings_service->is_delete_attachment_from_media_library() ),
					'detach_attachment_notice'        => wp_kses(
						__( 'Attachment detached. <a href="#">Undo</a>', 'dco-comment-attachment' ),
						[ 'a' => [ 'href' => true ] ]
					),
					'detach_attachments_notice'       => wp_kses(
						__( 'Attachments detached. <a href="#">Undo</a>', 'dco-comment-attachment' ),
						[ 'a' => [ 'href' => true ] ]
					),
				]
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
