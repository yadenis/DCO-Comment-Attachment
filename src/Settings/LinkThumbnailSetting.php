<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\DTO\SettingFieldDTO;
use DCO_CA\Enums\LinkThumbnailType;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Helpers\OptionsHelper;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingControls\RadioSettingControl;
use DCO_CA\SettingControls\RadioChoiceSettingControl;

defined( 'ABSPATH' ) || die;

final class LinkThumbnailSetting implements Setting {

	private const OPTION_NAME   = 'link_thumbnail';
	private const DEFAULT_VALUE = LinkThumbnailType::NO_LINK;

	private string $title;
	private array $types;

	public function __construct(
		private OptionsHelper $options,
	) {

		$this->title = __( 'Link thumbnail?', 'dco-comment-attachment' );

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
			'https://wordpress.org/plugins/dco-comment-attachment/#what%20lightbox%20plugins%20are%20supported%3F'
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

	public function get_value(): string {

		return $this->options->get_string_option( self::OPTION_NAME ) ?? $this->get_default_value();
	}

	public function get_setting_field_dto(): SettingFieldDTO {

		return new SettingFieldDTO(
			id: self::OPTION_NAME,
			title: $this->title,
			callback: $this->render_setting_field( ... ),
			section: SettingsSection::IMAGES
		);
	}

	public function render_setting_field( array $args ): void {

		(
			new RadioSettingControl(
				choices: $this->build_choices( $args ),
			)
		)->render();
	}

	private function get_default_value(): string {

		return self::DEFAULT_VALUE->value;
	}

	private function build_choices( array $args ): array {

		$choices = [];

		foreach ( $this->types as $value => $text ) {

			$choices[] = new RadioChoiceSettingControl(
				name: $args['name'],
				value: $value,
				text: $text,
				checked: $value === $this->get_value(),
			);
		}

		return $choices;
	}
}
