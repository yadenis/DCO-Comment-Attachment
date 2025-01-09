<?php
/**
 * DTO: Allowed File Types Group
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
 * Represents the details of an allowed file type group.
 *
 * @since 3.0.0
 */
final class AllowedFileTypesGroupDTO {

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name       The unique name of the file types group (e.g., 'image', 'video').
	 * @param string $title      The display title of the file types group (e.g., 'Images', 'Videos').
	 * @param array  $extensions The list of file extensions in this group (e.g., ['jpg', 'png', 'gif']).
	 */
	public function __construct(
		public readonly string $name,
		public readonly string $title,
		public readonly array $extensions,
	) {
	}
}
