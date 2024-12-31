<?php

declare(strict_types=1);

namespace DCO_CA\SettingControls;

use DCO_CA\DTO\AllowedFileTypesExtensionDTO;
use DCO_CA\DTO\AllowedFileTypesGroupDTO;
use DCO_CA\Interfaces\SettingControl;

defined( 'ABSPATH' ) || die;

final class AllowedFileTypesSettingControl implements SettingControl {

	private int $group_column_width;

	public function __construct(
		private string $name,
		private array $groups,
	) {

		$this->group_column_width = intval(
			/* translators: If the type names in your language are wider or narrower than in English - you can change the width of the column here. */
			_x(
				'100',
				'Allowed File Types Setting: column width in px',
				'dco-comment-attachment'
			)
		);
	}

	public function render(): void {

		echo '<div id="dco-file-types">';

		foreach ( $this->groups as $group ) {

			$this->render_group( $group );
		}

		echo '</div>';
	}

	private function render_group( AllowedFileTypesGroupDTO $group ): void {

		printf(
			'<div class="dco-file-types-group" style="width: %dpx;">',
			intval( $this->group_column_width )
		);

		$this->render_group_header( $group );

		$this->render_group_extensions( $group );

		echo '</div>';
	}

	private function render_group_header( AllowedFileTypesGroupDTO $group ): void {

		printf(
			'<label class="dco-file-types-group__name" title="%s">%s %s</label>',
			esc_attr__( 'Click to check/uncheck all extensions of this type.', 'dco-comment-attachment' ),
			'<input type="checkbox" class="dco-file-types-group__checkbox">',
			esc_html( $group->title ),
		);
	}

	private function render_group_extensions( AllowedFileTypesGroupDTO $group ): void {

		echo '<div class="dco-file-types-group__extensions">';

		foreach ( $group->extensions as $extension ) {

			$this->render_extension( $extension );
		}

		echo '</div>';
	}

	private function render_extension( AllowedFileTypesExtensionDTO $extension ): void {

		echo '<label class="dco-file-type-extension">';

		$ext  = $extension->extension;
		$mark = $this->get_extension_mark( $extension );

		printf(
			'<input type="checkbox" class="dco-file-type-extension__checkbox" name="%s[]" value="%s"%s> %s',
			esc_attr( $this->name ),
			esc_attr( $ext ),
			checked(
				checked: $extension->is_allowed,
				current: true,
				display: false
			),
			esc_html( $ext . $mark )
		);

		echo '</label>';
	}

	private function get_extension_mark( AllowedFileTypesExtensionDTO $extension ): string {

		if ( $extension->is_embedded ) {
			return ' *';
		}

		if ( $extension->is_for_administrators ) {
			return ' **';
		}

		return '';
	}
}
