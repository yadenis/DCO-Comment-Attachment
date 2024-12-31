<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\DTO\SettingFieldDTO;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Interfaces\Setting;
use DCO_CA\Services\PluginService;
use DCO_CA\Settings\AllowedFileTypesSetting;
use DCO_CA\Settings\AutoembedLinksSetting;
use DCO_CA\Settings\CombineImagesSetting;
use DCO_CA\Settings\DeleteAttachmentActionSetting;
use DCO_CA\Settings\DeleteWithCommentSetting;
use DCO_CA\Settings\EmbedAttachmentSetting;
use DCO_CA\Settings\EnableMultipleUploadSetting;
use DCO_CA\Settings\GallerySizeSetting;
use DCO_CA\Settings\LinkThumbnailSetting;
use DCO_CA\Settings\ManuallyModerationSetting;
use DCO_CA\Settings\MaxUploadSizeSetting;
use DCO_CA\Settings\RequiredAttachmentSetting;
use DCO_CA\Settings\ThumbnailSizeSetting;
use DCO_CA\Settings\WhoCanUploadSetting;

defined( 'ABSPATH' ) || die;

final class Settings {

	private string $settings_id;

	public function __construct(
		private MaxUploadSizeSetting $max_upload_size,
		private RequiredAttachmentSetting $required_attachment,
		private EmbedAttachmentSetting $embed_attachment,
		private AutoembedLinksSetting $autoembed_links,
		private ThumbnailSizeSetting $thumbnail_size,
		private LinkThumbnailSetting $link_thumbnail,
		private EnableMultipleUploadSetting $enable_multiple_upload,
		private CombineImagesSetting $combine_images,
		private GallerySizeSetting $gallery_size,
		private AllowedFileTypesSetting $allowed_file_types,
		private WhoCanUploadSetting $who_can_upload,
		private ManuallyModerationSetting $manually_moderation,
		private DeleteWithCommentSetting $delete_with_comment,
		private DeleteAttachmentActionSetting $delete_attachment_action,
	) {

		$this->settings_id = PluginService::SETTINGS_ID;

		add_action( 'admin_menu', $this->add_settings_page( ... ) );
		add_action( 'admin_init', $this->add_settings_fields( ... ) );
	}

	public function add_settings_page(): void {

		add_options_page(
			esc_html__( 'DCO Comment Attachment Settings', 'dco-comment-attachment' ),
			esc_html__( 'DCO Comment Attachment', 'dco-comment-attachment' ),
			'manage_options',
			'dco-comment-attachment',
			$this->render_settings_page( ... )
		);
	}

	public function render_settings_page(): void {

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'DCO Comment Attachment Settings', 'dco-comment-attachment' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( $this->settings_id );
				do_settings_sections( $this->settings_id );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function add_settings_fields(): void {

		register_setting( $this->settings_id, $this->settings_id );

		foreach ( $this->get_sections() as $id => $title ) {

			$this->add_section( $id, $title );
		}

		foreach ( $this->get_fields() as $field ) {

			$this->add_field( $field );
		}
	}

	public function render_section( array $args ): void {}

	private function get_sections(): array {

		return [
			SettingsSection::GENERAL->value         => __( 'General', 'dco-comment-attachment' ),
			SettingsSection::IMAGES->value          => __( 'Images', 'dco-comment-attachment' ),
			SettingsSection::MULTIPLE_UPLOAD->value => __( 'Multiple upload', 'dco-comment-attachment' ),
			SettingsSection::PERMISSIONS->value     => __( 'Permissions', 'dco-comment-attachment' ),
			SettingsSection::IN_ADMIN->value        => __( 'Admin Panel', 'dco-comment-attachment' ),
		];
	}

	private function get_fields(): array {

		return array_map(
			fn( Setting $setting ): SettingFieldDTO => $setting->get_setting_field_dto(),
			[
				$this->max_upload_size,
				$this->required_attachment,
				$this->embed_attachment,
				$this->autoembed_links,
				$this->thumbnail_size,
				$this->link_thumbnail,
				$this->enable_multiple_upload,
				$this->combine_images,
				$this->gallery_size,
				$this->allowed_file_types,
				$this->who_can_upload,
				$this->manually_moderation,
				$this->delete_with_comment,
				$this->delete_attachment_action,
			]
		);
	}

	private function add_section( string $id, string $title ): void {

		add_settings_section(
			id: $id,
			title: esc_html( $title ),
			callback: $this->render_section( ... ),
			page: $this->settings_id
		);
	}

	private function add_field( SettingFieldDTO $field ): void {

		add_settings_field(
			id: $field->id,
			title: esc_html( $field->title ),
			callback: $field->callback,
			page: $this->settings_id,
			section: esc_html( $field->section->value ),
			args: [
				'name'      => $this->settings_id . "[{$field->id}]",
				'id'        => $field->id,
				'label_for' => $field->id,
			]
		);
	}
}
