<?php
/**
 * Settings: Link Thumbnail
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
use DCO_CA\Enums\LinkThumbnailType;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Helpers\OptionsHelper;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingsControls\RadioSettingsControl;
use DCO_CA\SettingsControls\RadioChoiceSettingsControl;

defined( 'ABSPATH' ) || die;

/**
 * Provides functionality for the Link Thumbnail plugin setting.
 *
 * @since 3.0.0
 */
final class LinkThumbnailSetting implements Setting {

	private const OPTION_NAME   = 'link_thumbnail';
	private const DEFAULT_VALUE = LinkThumbnailType::NO_LINK;
	private const FAQ_LINK      = 'https://wordpress.org/plugins/dco-comment-attachment/#what%20lightbox%20plugins%20are%20supported%3F';

	private const LEGACY_VALUES_MAP = [
		'0' => LinkThumbnailType::NO_LINK,
		'1' => LinkThumbnailType::IMAGE_LIGHTBOX,
		'2' => LinkThumbnailType::IMAGE_NEW_TAB,
		'3' => LinkThumbnailType::ATTACHMENT_PAGE,
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
	 * The list of link thumbnail types.
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

		$this->title = __( 'Link thumbnail?', 'dco-comment-attachment' );

		$this->init_types();
	}

	/**
	 * Retrieves the type of the link for the thumbnail.
	 *
	 * @since 3.0.0
	 *
	 * @return string The type of the link for the thumbnail.
	 */
	public function get_setting_value(): string {

		$value = $this->options_helper->get_string_option( self::OPTION_NAME );

		return $this->ensure_backward_compatibility( $value ) ?? self::DEFAULT_VALUE->value;
	}

	/**
	 * Returns the settings field DTO for rendering the Link Thumbnail setting.
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
			section: SettingsSection::IMAGES
		);
	}

	/**
	 * Renders the Link Thumbnail settings field in the plugin settings page.
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

	/**
	 * Initializes the link thumbnail types.
	 *
	 * @since 3.0.0
	 */
	private function init_types(): void {

		$this->types[ LinkThumbnailType::NO_LINK->value ] = __(
			'Not link',
			'dco-comment-attachment'
		);

		$this->types[ LinkThumbnailType::IMAGE_LIGHTBOX->value ] = sprintf(
			/* translators: %s: the link to the plugin FAQ section on WordPress.org */
			__(
				'Link to a full-size image with lightbox plugins support (see <a href="%s">FAQ</a> for details)',
				'dco-comment-attachment'
			),
			self::FAQ_LINK
		);

		$this->types[ LinkThumbnailType::IMAGE_NEW_TAB->value ] = __(
			'Link to a full-size image in a new tab',
			'dco-comment-attachment'
		);

		$this->types[ LinkThumbnailType::ATTACHMENT_PAGE->value ] = __(
			'Link to the attachment page',
			'dco-comment-attachment'
		);
	}
}
