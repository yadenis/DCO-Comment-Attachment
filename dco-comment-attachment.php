<?php
/**
 * DCO Comment Attachment
 *
 * @package DCO_Comment_Attachment
 * @author Denis Yanchevskiy
 * @copyright 2019
 * @license GPLv2+
 *
 * @since 1.0.0
 *
 * Plugin Name: DCO Comment Attachment
 * Plugin URI: https://denisco.pro/dco-comment-attachment/
 * Description: Allows your visitors to attach files with their comments
 * Version: 3.0.0
 * Author: Denis Yanchevskiy
 * Author URI: https://denisco.pro
 * License: GPLv2 or later
 * Text Domain: dco-comment-attachment
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || die;

define( 'DCO_CA_URL', plugin_dir_url( __FILE__ ) );
define( 'DCO_CA_PATH', plugin_dir_path( __FILE__ ) );
define( 'DCO_CA_BASENAME', plugin_basename( __FILE__ ) );
define( 'DCO_CA_VERSION', '3.0.0' );

require_once DCO_CA_PATH . 'vendor/autoload.php';

function dco_ca() {

	require_once DCO_CA_PATH . 'backward-compatibility/functions.php';

	$injector = dco_ca_get_injector();

	$injector->share( DCO_CA\AdminActions\DeleteCommentAttachmentAdminAction::class );
	$injector->share( DCO_CA\AdminActions\DeleteCommentAttachmentBulkAdminAction::class );
	$injector->share( DCO_CA\AdminActions\EditCommentAttachmentAdminAction::class );

	$injector->share( DCO_CA\FormElements\AttachmentAreaFormElement::class );
	$injector->share( DCO_CA\FormElements\AutoembedLinksFormElement::class );
	$injector->share( DCO_CA\FormElements\DropAreaFormElement::class );
	$injector->share( DCO_CA\FormElements\FileTypesFormElement::class );
	$injector->share( DCO_CA\FormElements\InputFormElement::class );
	$injector->share( DCO_CA\FormElements\LabelFormElement::class );
	$injector->share( DCO_CA\FormElements\UploadSizeFormElement::class );

	$injector->share( DCO_CA\FormHandlers\AttachmentUploadHandler::class );
	$injector->share( DCO_CA\FormHandlers\AttachmentUploadValidator::class );

	$injector->share( DCO_CA\Helpers\OptionsHelper::class );
	$injector->share( DCO_CA\Helpers\RequestHelper::class );

	$injector->share( DCO_CA\Services\AttachmentService::class );
	$injector->share( DCO_CA\Services\CommentService::class );
	$injector->share( DCO_CA\Services\PluginService::class );
	$injector->share( DCO_CA\Services\PostService::class );
	$injector->share( DCO_CA\Services\SettingsService::class );
	$injector->share( DCO_CA\Services\UserService::class );

	$injector->share( DCO_CA\Settings\AllowedFileTypesSetting::class );
	$injector->share( DCO_CA\Settings\AutoembedLinksSetting::class );
	$injector->share( DCO_CA\Settings\CombineImagesSetting::class );
	$injector->share( DCO_CA\Settings\DeleteAttachmentActionSetting::class );
	$injector->share( DCO_CA\Settings\DeleteWithCommentSetting::class );
	$injector->share( DCO_CA\Settings\EmbedAttachmentSetting::class );
	$injector->share( DCO_CA\Settings\EnableMultipleUploadSetting::class );
	$injector->share( DCO_CA\Settings\GallerySizeSetting::class );
	$injector->share( DCO_CA\Settings\LinkThumbnailSetting::class );
	$injector->share( DCO_CA\Settings\ManuallyModerationSetting::class );
	$injector->share( DCO_CA\Settings\MaxUploadSizeSetting::class );
	$injector->share( DCO_CA\Settings\RequiredAttachmentSetting::class );
	$injector->share( DCO_CA\Settings\ThumbnailSizeSetting::class );
	$injector->share( DCO_CA\Settings\WhoCanUploadSetting::class );

	$injector->share( DCO_CA\AdminArea::class );
	$injector->share( DCO_CA\CommentsList::class );
	$injector->share( DCO_CA\Form::class );
	$injector->share( DCO_CA\FormHandler::class );
	$injector->share( DCO_CA\RESTAPI::class );
	$injector->share( DCO_CA\SettingsPage::class );
	$injector->share( DCO_CA\Shortcode::class );

	$injector->make( DCO_CA\AdminArea::class );
	$injector->make( DCO_CA\CommentsList::class );
	$injector->make( DCO_CA\Form::class );
	$injector->make( DCO_CA\FormHandler::class );
	$injector->make( DCO_CA\RESTAPI::class );
	$injector->make( DCO_CA\SettingsPage::class );
	$injector->make( DCO_CA\Shortcode::class );

	require_once DCO_CA_PATH . 'backward-compatibility/filters.php';
}

add_action( 'plugins_loaded', dco_ca( ... ) );

function dco_ca_get_injector(): Auryn\Injector {

	static $injector = null;

	if ( null === $injector ) {
		$injector = new Auryn\Injector();
	}

	return $injector;
}
