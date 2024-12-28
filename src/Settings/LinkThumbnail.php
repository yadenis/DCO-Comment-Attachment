<?php

declare(strict_types=1);

namespace DCO_CA\Settings;

use DCO_CA\DTO\SettingFieldDTO;
use DCO_CA\Enums\LinkThumbnailType;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Options;
use DCO_CA\Interfaces\Setting;
use DCO_CA\SettingControls\Radio;
use DCO_CA\SettingControls\RadioChoice;

defined( 'ABSPATH' ) || die;

final class LinkThumbnail implements Setting {

	private const OPTION_NAME   = 'link_thumbnail';
	private const DEFAULT_VALUE = LinkThumbnailType::NO_LINK;

	public function __construct(
		private Options $options,
	) {
	}

	public function get_value(): string {

		$value = $this->options->get_string_option( self::OPTION_NAME );
		if ( null === $value ) {
			return $this->get_default_value();
		}

		return $value;
	}

	public function get_setting_field_dto(): SettingFieldDTO {

		return new SettingFieldDTO(
			id: self::OPTION_NAME,
			title: esc_html__( 'Link thumbnail?', 'dco-comment-attachment' ),
			callback: $this->render_setting_field( ... ),
			section: SettingsSection::IMAGES
		);
	}

	public function render_setting_field( array $args ): void {

		(
			new Radio(
				choices: $this->build_choices( $args ),
			)
		)->render();
	}

	private function get_default_value(): string {

		return self::DEFAULT_VALUE->value;
	}

	private function build_choices( array $args ): array {

		$types = [
			LinkThumbnailType::NO_LINK->value         => __( 'Not link', 'dco-comment-attachment' ),
			/* translators: %s: the link to the plugin FAQ section on WordPress.org */
			LinkThumbnailType::IMAGE_LIGHTBOX->value  => sprintf( __( 'Link to a full-size image with lightbox plugins support (see <a href="%s">FAQ</a> for details)', 'dco-comment-attachment' ), 'https://wordpress.org/plugins/dco-comment-attachment/#what%20lightbox%20plugins%20are%20supported%3F' ),
			LinkThumbnailType::IMAGE_NEW_TAB->value   => __( 'Link to a full-size image in a new tab', 'dco-comment-attachment' ),
			LinkThumbnailType::ATTACHMENT_PAGE->value => __( 'Link to the attachment page', 'dco-comment-attachment' ),
		];

		$choices = [];

		foreach ( $types as $value => $text ) {

			$choices[] = new RadioChoice(
				name: $args['name'],
				value: $value,
				text: $text,
				checked: $value === $this->get_value(),
			);
		}

		return $choices;
	}
}
