<?php

declare(strict_types=1);

namespace DCO_CA\FormElements;

use DCO_CA\Interfaces\FormElement;
use DCO_CA\Services\PluginService;

defined( 'ABSPATH' ) || die;

final class AttachmentAreaFormElement implements FormElement {

	public function __construct(
		private PluginService $plugin_service,
		private LabelFormElement $label,
		private InputFormElement $input,
		private UploadSizeFormElement $upload_size,
		private FileTypesFormElement $file_types,
		private AutoembedLinksFormElement $autoembed_links,
		private DropAreaFormElement $drop_area,
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
		$this->plugin_service->the_kses_post(
			apply_filters( 'dco_ca_attachment_area', ob_get_clean() )
		);
	}
}
