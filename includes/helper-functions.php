<?php
/**
 * Helper functions in global namespace.
 *
 * @package Hogan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function hogan_deregister_default_field_group() {
	add_filter( 'hogan/field_group/default/enabled', '__return_false' );
}

function hogan_register_field_group( $name, $label, $modules = [], $location = [], $hide_on_screen = [], $fields_before_flexible_content = [], $fields_after_flexible_content = [] ) {

	add_action( 'acf/include_fields', function() use ( $name, $label, $modules, $location, $hide_on_screen, $fields_before_flexible_content, $fields_after_flexible_content ) {
		global $hogan;

		if ( $hogan instanceof \Dekode\Hogan\Plugin ) {
			$hogan->register_field_group( $name, $label, $modules, $location, $hide_on_screen, $fields_after_flexible_content, $fields_after_flexible_content );
		}

	}, 15 );

}
