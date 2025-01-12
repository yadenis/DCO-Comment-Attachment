<?php
/**
 * SettingControls: Allowed File Types
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA\SettingControls;

use DCO_CA\DTO\AllowedFileTypesExtensionDTO;
use DCO_CA\DTO\AllowedFileTypesGroupDTO;
use DCO_CA\Interfaces\SettingControl;

defined( 'ABSPATH' ) || die;

/**
 * Rendering the allowed file types setting control in the admin panel.
 *
 * @since 3.0.0
 */
final class AllowedFileTypesSettingControl implements SettingControl {

	/**
	 * The width of the group columns in the settings interface.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	private int $group_column_width;

	/**
	 * Constructor.
	 *
	 * @param string                     $name   The the setting name.
	 * @param AllowedFileTypesGroupDTO[] $groups The allowed file types groups.
	 */
	public function __construct(
		private string $name,
		private array $groups,
	) {

		$this->group_column_width = intval(
			// translators: If the type names in your language are wider
			// or narrower than in English - you can change the width of the column here.
			_x(
				'100',
				'Allowed File Types Setting: column width in px',
				'dco-comment-attachment'
			)
		);
	}

	/**
	 * Renders the allowed file types setting control.
	 *
	 * @since 3.0.0
	 */
	public function render(): void {

		echo '<div class="dco-file-types">';

		foreach ( $this->groups as $group ) {

			$this->render_group( $group );
		}

		echo '</div>';
	}

	/**
	 * Renders the allowed file types group.
	 *
	 * @since 3.0.0
	 *
	 * @param AllowedFileTypesGroupDTO $group The file types group to render.
	 */
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

	/**
	 * Renders the header for the allowed file types group.
	 *
	 * @since 3.0.0
	 *
	 * @param AllowedFileTypesGroupDTO $group The file types group to render.
	 */
	private function render_group_header( AllowedFileTypesGroupDTO $group ): void {

		printf(
			'<label class="dco-file-types-group__name" title="%s">%s %s</label>',
			esc_attr__( 'Click to check/uncheck all extensions of this type.', 'dco-comment-attachment' ),
			'<input type="checkbox" class="dco-file-types-group__checkbox dco-file-types-group-checkbox">',
			esc_html( $group->title ),
		);
	}

	/**
	 * Renders the allowed file types group extensions.
	 *
	 * @since 3.0.0
	 *
	 * @param AllowedFileTypesGroupDTO $group The file types group to render.
	 */
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

	/**
	 * Renders the allowed file types group extension.
	 *
	 * @since 3.0.0
	 *
	 * @param AllowedFileTypesExtensionDTO $extension The file types extension to render.
	 */
	private function render_extension( AllowedFileTypesExtensionDTO $extension ): void {

		$ext  = $extension->extension;
		$mark = $this->get_extension_mark( $extension );

		echo '<label class="dco-file-type-extension">';

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

	/**
	 * Returns a special mark for extension based on certain conditions.
	 *
	 * @param AllowedFileTypesExtensionDTO $extension The extension to check.
	 *
	 * @return string The mark to append to the extension name.
	 */
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
