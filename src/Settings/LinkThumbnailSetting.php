<?php

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

	private string $title;
	private array $types;

	public function __construct(
		private OptionsHelper $options_helper,
	) {

		$this->title = __( 'Link thumbnail?', 'dco-comment-attachment' );

		$this->init_types();
	}

	public function get_setting_value(): string {

		$value = $this->options_helper->get_string_option( self::OPTION_NAME );

		return $this->ensure_backward_compatibility( $value ) ?? self::DEFAULT_VALUE->value;
	}

	public function get_settings_field_dto(): SettingsFieldDTO {

		return new SettingsFieldDTO(
			id: self::OPTION_NAME,
			title: $this->title,
			callback: $this->render_settings_field( ... ),
			section: SettingsSection::IMAGES
		);
	}

	public function render_settings_field( array $args ): void {

		(
			new RadioSettingsControl(
				choices: $this->build_choices( $args ),
			)
		)->render_control();
	}

	private function build_choices( array $args ): array {

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

	private function ensure_backward_compatibility( ?string $value ): ?string {

		return self::LEGACY_VALUES_MAP[ $value ]->value ?? $value;
	}

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
