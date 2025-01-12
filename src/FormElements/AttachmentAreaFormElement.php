<?php
/**
 * Form Elements: Attachment Area
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA\FormElements;

use DCO_CA\Interfaces\FormElement;
use DCO_CA\Services\PluginService;

defined( 'ABSPATH' ) || die;

/**
 * Provides functionality to render the attachment area form element.
 *
 * @since 3.0.0
 */
final class AttachmentAreaFormElement implements FormElement {

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 *
	 * @param PluginService             $plugin_service    Service functions for the plugin.
	 * @param LabelFormElement          $label             Label form element.
	 * @param InputFormElement          $input             Input form element.
	 * @param UploadSizeFormElement     $upload_size       Upload Size form element.
	 * @param FileTypesFormElement      $file_types        File Types form element.
	 * @param AutoembedLinksFormElement $autoembed_links   Autoembed Links form element.
	 * @param DropAreaFormElement       $drop_area         Drop Area form element.
	 */
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

	/**
	 * Renders the attachment area form element.
	 */
	public function render_element(): void {

		ob_start();
		?>
		<p class="comment-form-attachment">
			<?php
			$this->label->render_element();
			$this->input->render_element();
			$this->upload_size->render_element();
			$this->file_types->render_element();
			$this->autoembed_links->render_element();
			$this->drop_area->render_element();
			?>
		</p>
		<?php
		$this->plugin_service->the_kses_post(
			apply_filters( 'dco_ca_attachment_area', ob_get_clean() )
		);
	}
}
