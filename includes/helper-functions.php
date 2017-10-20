<?php
/**
 * Helper functions in global namespace.
 *
 * @package Hogan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registert module
 */
function hogan_register_module( $module ) {
	global $hogan;
	$hogan->register_module( $module );
}

/**
 * De-register default field group.
 */
function hogan_deregister_default_field_group() {
	add_filter( 'hogan/field_group/default/enabled', '__return_false' );
}

/**
 * Register a specific field group.
 *
 * @param string $name                           Field group name.
 * @param mixed  $label                          Label.
 * @param mixed  $modules                        Array/String with supported modules.
 * @param array  $location                       Location rules.
 * @param array  $hide_on_screen                 Array of elements to hide on edit screen.
 * @param array  $fields_before_flexible_content Prepend fields.
 * @param array  $fields_after_flexible_content  Append fields.
 *
 * @return void
 */
function hogan_register_field_group( $name, $label, $modules = [], $location = [], $hide_on_screen = [], $fields_before_flexible_content = [], $fields_after_flexible_content = [] ) {
	// TODO: No need to go any further if did_action === 1?
	add_action( 'acf/include_fields', function() use ( $name, $label, $modules, $location, $hide_on_screen, $fields_before_flexible_content, $fields_after_flexible_content ) {
		global $hogan;

		if ( $hogan instanceof \Dekode\Hogan\Plugin ) {
			$hogan->register_field_group( $name, $label, $modules, $location, $hide_on_screen, $fields_after_flexible_content, $fields_after_flexible_content );
		}

	}, 15 );
}
