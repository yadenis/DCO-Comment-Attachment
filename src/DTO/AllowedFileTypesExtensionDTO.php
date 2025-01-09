<?php
/**
 * DTO: Allowed File Types Extension
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace DCO_CA\DTO;

defined( 'ABSPATH' ) || die;

/**
 * Represents the details of an allowed file type extension.
 */
final class AllowedFileTypesExtensionDTO {

	/**
	 * Constructor.
	 *
	 * @param string $extension               The file extension (e.g., 'jpg', 'pdf').
	 * @param string $group                   The group the file extension belongs to (e.g., 'image', 'video').
	 * @param bool   $is_allowed_to_upload    Whether the file type is allowed to upload.
	 * @param bool   $is_embedded             Whether the file type is embedded when displayed.
	 * @param bool   $is_for_administrators   Whether the file type is allowed only for administrators and editors.
	 */
	public function __construct(
		public readonly string $extension,
		public readonly string $group,
		public readonly bool $is_allowed_to_upload,
		public readonly bool $is_embedded,
		public readonly bool $is_for_administrators,
	) {
	}
}
