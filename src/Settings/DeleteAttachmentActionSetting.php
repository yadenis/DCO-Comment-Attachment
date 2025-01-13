<?php
/**
 * Settings: Delete Attachment Action
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\DTO\SettingsFieldDTO;
use DCO_CA\Enums\DeleteAttachmentActionType;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Helpers\OptionsHelper;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingsControls\RadioChoiceSettingsControl;
use DCO_CA\SettingsControls\RadioSettingsControl;

defined( 'ABSPATH' ) || die;

/**
 * Provides functionality for the Delete Attachment Action plugin setting.
 *
 * @since 3.0.0
 */
final class DeleteAttachmentActionSetting implements Setting {

	private const OPTION_NAME   = 'delete_attachment_action';
	private const DEFAULT_VALUE = DeleteAttachmentActionType::DELETE;

	private const LEGACY_VALUES_MAP = [
		'1' => DeleteAttachmentActionType::DELETE,
		'0' => DeleteAttachmentActionType::DETACH,
	];

	/**
	 * The settings field title.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private string $title;

	/**
	 * The list of delete attachment action types.
	 *
	 * @since 3.0.0
	 *
	 * @var array<string, string>
	 */
	private array $types;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param OptionsHelper $options_helper Helper functions for options.
	 */
	public function __construct(
		private OptionsHelper $options_helper,
	) {

		$this->title = __( 'Delete Attachment action on Edit Comments page', 'dco-comment-attachment' );

		$this->types = [
			DeleteAttachmentActionType::DELETE->value => __(
				'Delete attachment from Media Library',
				'dco-comment-attachment'
			),
			DeleteAttachmentActionType::DETACH->value => __(
				'Detach attachment from comment',
				'dco-comment-attachment'
			),
		];
	}

	/**
	 * Retrieves the delete attachment action type from the plugin settings.
	 *
	 * @since 3.0.0
	 *
	 * @return string The delete attachment action type.
	 */
	public function get_setting_value(): string {

		$value = $this->options_helper->get_string_option( self::OPTION_NAME );

		return $this->ensure_backward_compatibility( $value ) ?? self::DEFAULT_VALUE->value;
	}

	/**
	 * Whether attachments should be deleted from the media library.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if attachments should bе deleted, false otherwise.
	 */
	public function is_delete_attachment_from_media_library(): bool {

		return DeleteAttachmentActionType::DELETE->value === $this->get_setting_value();
	}

	/**
	 * Returns the settings field DTO for rendering the Delete Attachment Action setting.
	 *
	 * @since 3.0.0
	 *
	 * @return SettingsFieldDTO The settings field DTO.
	 */
	public function get_settings_field_dto(): SettingsFieldDTO {

		return new SettingsFieldDTO(
			id: self::OPTION_NAME,
			title: $this->title,
			callback: $this->render_settings_field( ... ),
			section: SettingsSection::IN_ADMIN
		);
	}

	/**
	 * Renders the Delete Attachment Action settings field in the plugin settings page.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args The arguments list for rendering.
	 */
	public function render_settings_field( array $args ): void {

		(
			new RadioSettingsControl(
				choices: $this->build_choices( $args ),
			)
		)->render_control();
	}

	/**
	 * Builds the choices for the radio button control.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args The arguments list for rendering.
	 *
	 * @return RadioChoiceSettingsControl[] The list of radio choice controls.
	 */
	private function build_choices( array $args ): array {

		if ( empty( $args['name'] ) ) {
			return [];
		}

		return array_map(
			fn( string $value, string $text ): RadioChoiceSettingsControl => new RadioChoiceSettingsControl(
				name: $args['name'],
				value: $value,
				text: $text,
				checked: $value === $this->get_setting_value(),
			),
			array_keys( $this->types ),
			$this->types
		);
	}

	/**
	 * Ensures backward compatibility with legacy values for the plugin setting.
	 *
	 * @since 3.0.0
	 *
	 * @param string|null $value The old setting value.
	 *
	 * @return string|null The converted value, or null if not compatible.
	 */
	private function ensure_backward_compatibility( ?string $value ): ?string {

		return self::LEGACY_VALUES_MAP[ $value ]->value ?? $value;
	}
}
