<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\DTO\SettingsFieldDTO;
use DCO_CA\Enums\SettingsSection;
use DCO_CA\Interfaces\Setting;
use DCO_CA\Services\PluginService;
use DCO_CA\Services\SettingsService;

defined( 'ABSPATH' ) || die;

final class Settings {

	private array $settings;

	public function __construct(
		private SettingsService $settings_service,
	) {

		$this->settings = $this->settings_service->get_all_settings_instances();

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
				settings_fields( PluginService::SETTINGS_ID );
				do_settings_sections( PluginService::SETTINGS_ID );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function add_settings_fields(): void {

		register_setting( PluginService::SETTINGS_ID, PluginService::SETTINGS_ID );

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
			static fn( Setting $setting ): SettingsFieldDTO => $setting->get_settings_field_dto(),
			$this->settings
		);
	}

	private function add_section( string $id, string $title ): void {

		add_settings_section(
			id: $id,
			title: esc_html( $title ),
			callback: $this->render_section( ... ),
			page: PluginService::SETTINGS_ID
		);
	}

	private function add_field( SettingsFieldDTO $field ): void {

		add_settings_field(
			id: $field->id,
			title: esc_html( $field->title ),
			callback: $field->callback,
			page: PluginService::SETTINGS_ID,
			section: esc_html( $field->section->value ),
			args: [
				'name'      => PluginService::SETTINGS_ID . "[{$field->id}]",
				'id'        => $field->id,
				'label_for' => $field->id,
			]
		);
	}
}
