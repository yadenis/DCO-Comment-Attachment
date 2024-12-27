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
// define( 'DCO_CA_BASENAME', plugin_basename( __FILE__ ) );
define( 'DCO_CA_VERSION', '3.0.0' );

require_once DCO_CA_PATH . '/vendor/autoload.php';

function dco_ca() {

	$injector = dco_ca_get_injector();

	$injector->make( DCO_CA\Form::class );

	$injector->make( DCO_CA\Settings::class );

	$injector->make( DCO_CA\BackwardCompatibility::class );
}

add_action( 'plugins_loaded', dco_ca( ... ) );

function dco_ca_get_injector(): Auryn\Injector {

	static $injector = null;

	if ( null === $injector ) {
		$injector = new Auryn\Injector();
	}

	return $injector;
}
