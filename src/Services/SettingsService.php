<?php

declare(strict_types=1);

namespace DCO_CA\Services;

use DCO_CA\Enums\AllowedFileTypesFormat;
use DCO_CA\Enums\MaxUploadSizeFormat;
use DCO_CA\Settings;
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

final class SettingsService {

	public function __construct(
		private MaxUploadSizeSetting $max_upload_size_setting,
		private RequiredAttachmentSetting $required_attachment_setting,
		private EmbedAttachmentSetting $embed_attachment_setting,
		private AutoembedLinksSetting $autoembed_links_setting,
		private ThumbnailSizeSetting $thumbnail_size_setting,
		private LinkThumbnailSetting $link_thumbnail_setting,
		private EnableMultipleUploadSetting $enable_multiple_upload_setting,
		private CombineImagesSetting $combine_images_setting,
		private GallerySizeSetting $gallery_size_setting,
		private AllowedFileTypesSetting $allowed_file_types_setting,
		private WhoCanUploadSetting $who_can_upload_setting,
		private ManuallyModerationSetting $manually_moderation_setting,
		private DeleteWithCommentSetting $delete_with_comment_setting,
		private DeleteAttachmentActionSetting $delete_attachment_action_setting,
	) {

		new Settings( $this->get_all_settings_instances() );
	}

	public function get_formatted_max_upload_size(): string {

		return $this->max_upload_size_setting->get_value( MaxUploadSizeFormat::FORMATTED );
	}

	public function get_max_upload_size_in_bytes(): int {

		return $this->max_upload_size_setting->get_value( MaxUploadSizeFormat::IN_BYTES );
	}

	public function is_required_attachment(): bool {

		return $this->required_attachment_setting->get_value();
	}

	public function is_embeded_attachment(): bool {

		return $this->embed_attachment_setting->get_value();
	}

	public function is_autoembed_links(): bool {

		if ( is_admin() ) {
			return false;
		}

		return $this->autoembed_links_setting->get_value();
	}

	public function get_thumbnail_image_size(): string {

		$thumbnail_size = $this->thumbnail_size_setting->get_value();

		if ( is_admin() ) {
			/**
			 * Filters the attachment image size for the admin panel.
			 *
			 * @since 2.0.0
			 *
			 * @param string $size The thumbnail size of the attachment image.
			 */
			$thumbnail_size = apply_filters( 'dco_ca_admin_thumbnail_size', 'medium' );
		}

		return $thumbnail_size;
	}

	public function get_link_thumbnail_type(): string {

		return $this->link_thumbnail_setting->get_value();
	}

	public function is_enabled_multiple_upload(): bool {

		return $this->enable_multiple_upload_setting->get_value();
	}

	public function is_combined_images(): bool {

		if ( is_admin() ) {
			return true;
		}

		return $this->combine_images_setting->get_value();
	}

	public function get_gallery_image_size(): string {

		return $this->gallery_size_setting->get_value();
	}

	public function get_allowed_file_types(): array {

		return $this->allowed_file_types_setting->get_value();
	}

	public function get_grouped_allowed_file_types(): array {

		return $this->allowed_file_types_setting->get_value( AllowedFileTypesFormat::GROUPED_ARRAY );
	}

	public function get_image_extensions(): array {

		return AllowedFileTypesSetting::IMAGE_EXTENSIONS;
	}

	public function is_can_upload_all_users(): bool {

		return $this->who_can_upload_setting->is_can_upload_all_users();
	}

	public function is_can_upload_only_logged_users(): bool {

		return $this->who_can_upload_setting->is_can_upload_only_logged_users();
	}

	public function is_manually_moderation_enabled(): bool {

		return $this->manually_moderation_setting->get_value();
	}

	public function is_delete_attachments_with_comment(): bool {

		return $this->delete_with_comment_setting->get_value();
	}

	public function is_delete_attachment_from_media_library(): bool {

		return $this->delete_attachment_action_setting->is_delete_attachment_from_media_library();
	}

	public function get_all_settings_instances(): array {

		return [
			$this->max_upload_size_setting,
			$this->required_attachment_setting,
			$this->embed_attachment_setting,
			$this->autoembed_links_setting,
			$this->thumbnail_size_setting,
			$this->link_thumbnail_setting,
			$this->enable_multiple_upload_setting,
			$this->combine_images_setting,
			$this->gallery_size_setting,
			$this->allowed_file_types_setting,
			$this->who_can_upload_setting,
			$this->manually_moderation_setting,
			$this->delete_with_comment_setting,
			$this->delete_attachment_action_setting,
		];
	}

	public function apply_file_types_filter_to_function( callable $callback, array $arguments ): mixed {

		add_filter( 'upload_mimes', $this->allowed_file_types_setting->filter_upload_mimes( ... ), 999 );

		$result = call_user_func_array( $callback, $arguments );

		remove_filter( 'upload_mimes', $this->allowed_file_types_setting->filter_upload_mimes( ... ), 999 );

		return $result;
	}
}
