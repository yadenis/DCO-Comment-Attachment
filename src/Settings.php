<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\Enums\SettingsSection;
use DCO_CA\Settings\MaxUploadSize;

defined( 'ABSPATH' ) || die;

final class Settings {

	public const ID = 'dco_ca';

	public function __construct(
		private MaxUploadSize $max_upload_size,
	) {

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
				settings_fields( self::ID );
				do_settings_sections( self::ID );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function add_settings_fields(): void {

		register_setting( self::ID, self::ID );

		foreach ( $this->get_sections() as $key => $title ) {

			add_settings_section(
				id: $key,
				title: $title,
				callback: $this->render_section( ... ),
				page: self::ID
			);
		}

		foreach ( $this->get_fields() as $field ) {

			add_settings_field(
				id: $field->id,
				title: $field->title,
				callback: $field->callback,
				page: self::ID,
				section: $field->section->value,
				args: [
					'name' => $field->id,
					'id'   => self::ID . "[{$field->id}]",
				]
			);
		}
	}

	public function render_section( array $args ): void {}

	private function get_sections(): array {

		return [
			SettingsSection::GENERAL->value         => esc_html__( 'General', 'dco-comment-attachment' ),
			SettingsSection::IMAGES->value          => esc_html__( 'Images', 'dco-comment-attachment' ),
			SettingsSection::MULTIPLE_UPLOAD->value => esc_html__( 'Multiple upload', 'dco-comment-attachment' ),
			SettingsSection::PERMISSIONS->value     => esc_html__( 'Permissions', 'dco-comment-attachment' ),
			SettingsSection::IN_ADMIN->value        => esc_html__( 'Admin Panel', 'dco-comment-attachment' ),
		];
	}

	private function get_fields(): array {

		return [
			$this->max_upload_size->get_setting_field_dto(),

		];
	}
}
