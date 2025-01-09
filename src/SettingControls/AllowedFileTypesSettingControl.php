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

		echo '<div class="dco-file-types">';

		foreach ( $this->groups as $group ) {

			$this->render_group( $group );
		}

		echo '</div>';
	}

	private function render_group( AllowedFileTypesGroupDTO $group ): void {

		$show_less_class = count( $group->extensions ) > 5 ? 'show-less' : '';

		printf(
			'<div class="dco-file-types-group %s" style="width: %dpx;">',
			esc_attr( $show_less_class ),
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
			'<input type="checkbox" class="dco-file-types-group__checkbox dco-file-types-group-checkbox">',
			esc_html( $group->title ),
		);
	}

	private function render_group_extensions( AllowedFileTypesGroupDTO $group ): void {

		echo '<div class="dco-file-types-group__extensions">';

		foreach ( $group->extensions as $extension ) {

			$this->render_extension( $extension );
		}

		if ( count( $group->extensions ) > 5 ) {

			printf(
				'<a href="#" class="%s">%s</a>',
				'dco-file-types-group__show-all-extensions dco-file-types-group-show-all-extensions',
				esc_html__( 'Show all', 'dco-comment-attachment' )
			);
		}

		echo '</div>';
	}

	private function render_extension( AllowedFileTypesExtensionDTO $extension ): void {

		echo '<label class="dco-file-type-extension">';

		$ext  = $extension->extension;
		$mark = $this->get_extension_mark( $extension );

		printf(
			'<input type="checkbox" class="%s" name="%s[]" value="%s"%s> %s',
			'dco-file-type-extension__checkbox dco-file-type-extension-checkbox',
			esc_attr( $this->name ),
			esc_attr( $ext ),
			checked(
				checked: $extension->is_allowed_to_upload,
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
