<?php
/**
 * Services: Settings
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

use DCO_CA\Enums\AllowedFileTypesFormat;
use DCO_CA\Enums\MaxUploadSizeFormat;
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

/**
 * Service for handling settings-related operations.
 *
 * @since 3.0.0
 */
final class SettingsService {

	/**
	 * Constructor
	 *
	 * Initializes all settings instances.
	 *
	 * @since 3.0.0
	 *
	 * @param MaxUploadSizeSetting          $max_upload_size_setting          The max upload size setting.
	 * @param RequiredAttachmentSetting     $required_attachment_setting      The required attachment setting.
	 * @param EmbedAttachmentSetting        $embed_attachment_setting         The embed attachment setting.
	 * @param AutoembedLinksSetting         $autoembed_links_setting          The autoembed links setting.
	 * @param ThumbnailSizeSetting          $thumbnail_size_setting           The thumbnail size setting.
	 * @param LinkThumbnailSetting          $link_thumbnail_setting           The link thumbnail setting.
	 * @param EnableMultipleUploadSetting   $enable_multiple_upload_setting   The enable multiple upload setting.
	 * @param CombineImagesSetting          $combine_images_setting           The combine images setting.
	 * @param GallerySizeSetting            $gallery_size_setting             The gallery size setting.
	 * @param AllowedFileTypesSetting       $allowed_file_types_setting       The allowed file types setting.
	 * @param WhoCanUploadSetting           $who_can_upload_setting           The who can upload setting.
	 * @param ManuallyModerationSetting     $manually_moderation_setting      The manually moderation setting.
	 * @param DeleteWithCommentSetting      $delete_with_comment_setting      The delete with comment setting.
	 * @param DeleteAttachmentActionSetting $delete_attachment_action_setting The delete attachment action setting.
	 */
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
	}

	/**
	 * Retrieves the formatted maximum upload size.
	 *
	 * @since 3.0.0
	 *
	 * @return string The formatted maximum upload size (e.g., '10 MB').
	 */
	public function get_formatted_max_upload_size(): string {

		return $this->max_upload_size_setting->get_setting_value( MaxUploadSizeFormat::FORMATTED );
	}

	/**
	 * Retrieves the maximum upload size in bytes.
	 *
	 * @since 3.0.0
	 *
	 * @return int The maximum upload size in bytes.
	 */
	public function get_max_upload_size_in_bytes(): int {

		return $this->max_upload_size_setting->get_setting_value( MaxUploadSizeFormat::IN_BYTES );
	}

	/**
	 * Whether an attachment is required for comment submission.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if an attachment is required, false otherwise.
	 */
	public function is_required_attachment(): bool {

		return $this->required_attachment_setting->get_setting_value();
	}

	/**
	 * Whether the attachments should be embedded.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if the attachments should be embedded, false otherwise.
	 */
	public function is_embeded_attachment(): bool {

		return $this->embed_attachment_setting->get_setting_value();
	}

	/**
	 * Whether auto-embedding of links is enabled.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if auto-embedding of links is enabled, false otherwise.
	 */
	public function is_autoembed_links(): bool {

		return is_admin() ? false : $this->autoembed_links_setting->get_setting_value();
	}

	/**
	 * Retrieves the thumbnail size for images.
	 *
	 * @since 3.0.0
	 *
	 * @return string The thumbnail size (e.g., 'medium', 'large') from the plugin settings,
	 *                except for the admin panel, where the size is forced to 'medium'.
	 */
	public function get_thumbnail_image_size(): string {

		if ( is_admin() ) {
			/**
			 * Filters the attachment image size for the admin panel.
			 *
			 * @since 2.0.0
			 *
			 * @param string $size The thumbnail size of the attachment image.
			 */
			return apply_filters( 'dco_ca_admin_thumbnail_size', 'medium' );
		} else {
			return $this->thumbnail_size_setting->get_setting_value();
		}
	}

	/**
	 * Retrieves the type of the link for the thumbnail.
	 *
	 * Returns one of the predefined link types for thumbnails based on the setting value.
	 *
	 * @see \DCO_CA\Settings\LinkThumbnailSetting
	 *
	 * @since 3.0.0
	 *
	 * @return string The type of the link for the thumbnail.
	 */
	public function get_link_thumbnail_type(): string {

		return $this->link_thumbnail_setting->get_setting_value();
	}

	/**
	 * Whether multiple upload is enabled.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if multiple upload is enabled, false otherwise.
	 */
	public function is_enabled_multiple_upload(): bool {

		return $this->enable_multiple_upload_setting->get_setting_value();
	}

	/**
	 * Whether images should be combined into a gallery.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if images should be combined, false otherwise.
	 */
	public function is_combined_images(): bool {

		return is_admin() ? true : $this->combine_images_setting->get_setting_value();
	}

	/**
	 * Retrieves the gallery size for images.
	 *
	 * @since 3.0.0
	 *
	 * @return string The gallery image size (e.g., 'medium', 'large').
	 */
	public function get_gallery_image_size(): string {

		return $this->gallery_size_setting->get_setting_value();
	}

	/**
	 * Retrieves the allowed file types for upload.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] The list of allowed file types.
	 */
	public function get_allowed_file_types(): array {

		return $this->allowed_file_types_setting->get_setting_value();
	}

	/**
	 * Retrieves the allowed file types, organized into groups.
	 *
	 * @since 3.0.0
	 *
	 * @return array<string, \DCO_CA\DTO\AllowedFileTypesGroupDTO> The grouped list of allowed file types.
	 */
	public function get_grouped_allowed_file_types(): array {

		return $this->allowed_file_types_setting->get_setting_value( AllowedFileTypesFormat::GROUPED_BY_TYPE );
	}

	/**
	 * Retrieves the image extensions.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] The image extensions (e.g., 'jpg', 'png').
	 */
	public function get_image_extensions(): array {

		return AllowedFileTypesSetting::IMAGE_EXTENSIONS;
	}

	/**
	 * Whether all users can upload attachments.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if all users can upload attachments, false otherwise.
	 */
	public function is_can_upload_all_users(): bool {

		return $this->who_can_upload_setting->is_can_upload_all_users();
	}

	/**
	 * Whether only logged-in users can upload attachments.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if only logged-in users can upload attachments, false otherwise.
	 */
	public function is_can_upload_only_logged_users(): bool {

		return $this->who_can_upload_setting->is_can_upload_only_logged_users();
	}

	/**
	 * Whether manual moderation is enabled.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if manual moderation is enabled, false otherwise.
	 */
	public function is_manually_moderation_enabled(): bool {

		return $this->manually_moderation_setting->get_setting_value();
	}

	/**
	 * Whether attachments should be deleted along with the associated comment.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if attachments should bе deleted with the comment, false otherwise.
	 */
	public function is_delete_attachments_with_comment(): bool {

		return $this->delete_with_comment_setting->get_setting_value();
	}

	/**
	 * Whether attachments should be deleted from the media library.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if attachments should bе deleted, false otherwise.
	 */
	public function is_delete_attachment_from_media_library(): bool {

		return $this->delete_attachment_action_setting->is_delete_attachment_from_media_library();
	}

	/**
	 * Retrieves all settings instances.
	 *
	 * @since 3.0.0
	 *
	 * @return Setting[] The list of all settings instances.
	 */
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

	/**
	 * Applies the file types filter to a given function.
	 *
	 * Adds the file types filter and calls the provided callback with the given arguments.
	 * The filter is removed after the callback execution.
	 *
	 * @since 3.0.0
	 *
	 * @param callable $callback The function to be called after applying the filter.
	 * @param array    $arguments The arguments to pass to the callback function.
	 *
	 * @return mixed The result of the callback function.
	 */
	public function apply_file_types_filter_to_function( callable $callback, array $arguments ): mixed {

		$filter = $this->allowed_file_types_setting->filter_upload_mimes( ... );

		add_filter( 'upload_mimes', $filter, 999 );

		$result = call_user_func_array( $callback, $arguments );

		remove_filter( 'upload_mimes', $filter, 999 );

		return $result;
	}
}
