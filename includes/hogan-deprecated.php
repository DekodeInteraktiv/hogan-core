<?php
/**
 * Deprecated functions.
 *
 * @package Hogan
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register module helper function
 *
 * @param \Dekode\Hogan\Module $module Module object.
 * @deprecated 1.1.7 Use register_module()
 * @see register_module()
 *
 * @return void
 */
function hogan_register_module( \Dekode\Hogan\Module $module ) {

	_deprecated_function( __FUNCTION__, '1.1.7', 'register_module()' );

	$core = \Dekode\Hogan\Core::get_instance();
	$core->register_module( $module );
}
