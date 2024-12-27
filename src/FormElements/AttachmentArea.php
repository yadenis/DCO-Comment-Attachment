<?php

declare(strict_types=1);

namespace DCO_CA\FormElements;

use DCO_CA\Interfaces\FormElement;

defined( 'ABSPATH' ) || die;

final class AttachmentArea implements FormElement {

	public function __construct(
		private Label $label,
		private Input $input,
		private UploadSize $upload_size,
		private FileTypes $file_types,
		private AutoembedLinks $autoembed_links,
		private DropArea $drop_area,
	) {
	}

	public function render(): void {

		ob_start();
		?>
		<p class="comment-form-attachment">
			<?php
			$this->label->render();
			$this->input->render();
			$this->upload_size->render();
			$this->file_types->render();
			$this->autoembed_links->render();
			$this->drop_area->render();
			?>
		</p>
		<?php
		echo apply_filters( 'dco_ca_attachment_area', ob_get_clean() );
	}
}
