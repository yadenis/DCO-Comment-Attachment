<?php

declare(strict_types=1);

namespace DCO_CA\FormElements;

use DCO_CA\FormElements\Interfaces\FormElement;

defined( 'ABSPATH' ) || die;

final class AttachmentArea implements FormElement {

	public function __construct(
		private Label $label,
		private Input $input,
	) {
	}

	public function render(): void {

		ob_start();
		?>
		<p class="comment-form-attachment">
			<?php
			$this->label->render();
			$this->input->render();
			?>
		</p>
		<?php
		echo apply_filters( 'dco_ca_attachment_area', ob_get_clean() );
	}
}
