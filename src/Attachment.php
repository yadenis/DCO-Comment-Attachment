<?php

declare(strict_types=1);

namespace DCO_CA;

use DCO_CA\Services\AttachmentService;

defined( 'ABSPATH' ) || die;

final class Attachment {

	private string $filepath;
	private string $embed_type;

	public function __construct( private int $id ) {

		$this->filepath   = get_attached_file( $this->id );
		$this->embed_type = $this->get_embed_type();
	}

	public function get_render(): string {
	}

	public function render(): void {

		echo $this->get_render();
	}

	private function get_embed_type(): string {

		$extension = wp_check_filetype( $this->filepath )['ext'];

		$types = [
			'image' => AttachmentService::IMAGE_EXTENSIONS,
			'video' => wp_get_video_extensions(),
			'audio' => wp_get_audio_extensions(),
		];

		foreach ( $types as $type => $extensions ) {

			if ( in_array( $extension, $extensions, true ) ) {
				return $type;
			}
		}

		return 'misc';
	}
}
