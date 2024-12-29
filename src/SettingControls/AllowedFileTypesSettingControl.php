<?php

declare(strict_types=1);

namespace DCO_CA\SettingControls;

use DCO_CA\Interfaces\SettingControl;

defined( 'ABSPATH' ) || die;

final class AllowedFileTypes implements SettingControl {

	private const SPECIAL_EXTENSIONS = [ 'htm', 'html', 'js' ];

	public function __construct(
		private string $name,
		private string $id,
		private array $options,
	) {
	}

	public function render(): void {

		printf(
			'<select name="%s" id="%s">',
			esc_attr( $this->name ),
			esc_attr( $this->id ),
		);

		foreach ( $this->options as $option ) {

			if ( ! $option instanceof SelectOption ) {
				continue;
			}

			$option->render();
		}

		echo '</select>';
	}




	public function field_allowed_file_types_render( $setting_val, $control_name, $control_id, $args ) {
		
		$embed_exts = array_merge( wp_get_video_extensions(), wp_get_audio_extensions(), $this->get_image_exts() );

		/*
		* Translators: If the type names in your language are wider or narrower than in English - you can change the width of the column here.
		*/
		$column_width = _x( '100', 'Allowed File Types Setting: column width in px', 'dco-comment-attachment' );

		echo '<div id="dco-file-types">';
		$types = $this->get_allowed_file_types();
		$more  = 6;
		foreach ( $types as $type ) {
			echo '<div class="dco-file-type" style="width: ' . (int) $column_width . 'px;">';
			echo '<label class="dco-file-type-name" title="' . esc_attr__( 'Click to check/uncheck all extensions of this type.', 'dco-comment-attachment' ) . '"><input type="checkbox" class="dco-file-type-name-checkbox"> ' . $this->mb_ucfirst( esc_html( $type['name'] ) ) . '</label>';
			echo '<div class="dco-file-type-items">';
			$i = 1;
			foreach ( $type['exts'] as $ext ) {
				if ( $i === $more ) {
					echo '</div><div class="dco-file-type-items-more">';
				}
				$mark = '';
				if ( in_array( $ext, $embed_exts, true ) ) {
					$mark = ' *';
				}
				if ( in_array( $ext, $special_exts, true ) ) {
					$mark = ' **';
				}
				echo '<label class="dco-file-type-item"><input type="checkbox" class="dco-file-type-item-checkbox" name="' . esc_attr( $control_name ) . '[]" value="' . esc_attr( $ext ) . '"' . checked( in_array( $ext, $setting_val, true ), true, false ) . '> ' . esc_html( $ext . $mark ) . '</label>';
				++$i;
			}
			echo '</div>';
			if ( $i > $more ) {
				echo '<a href="#" class="dco-show-all">' . esc_html__( 'Show all', 'dco-comment-attachment' ) . '</a>';
			}
			echo '</div>';
		}
		echo '</div>';
	}
}
