<?php
/**
 * DTO: Setting Field
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

use Closure;
use DCO_CA\Enums\SettingsSection;

defined( 'ABSPATH' ) || die;

/**
 * Represents the details of a setting field.
 */
final class SettingFieldDTO {

	/**
	 * Constructor.
	 * 
	 * @since 3.0.0
	 *
	 * @param string          $id        The unique name of the setting field.
	 * @param string          $title     The display title of the setting field.
	 * @param Closure         $callback  The callback function to render the field.
	 * @param SettingsSection $section   The section the field belongs to, as defined in the `SettingsSection` enum.
	 */
	public function __construct(
		public readonly string $id,
		public readonly string $title,
		public readonly Closure $callback,
		public readonly SettingsSection $section,
	) {
	}
}
