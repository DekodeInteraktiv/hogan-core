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
 *
 * @param object $module Module object.
 *
 * @return void
 */
function hogan_register_module( $module ) {

	if ( did_action( 'hogan/modules_registered' ) ) {
		_doing_it_wrong( __METHOD__, esc_html__( 'Hogan modules have already been registered. Please run hogan_register_module() on action hogan/include_modules.', 'hogan-core' ) , '1.0.0' );
	}

	add_filter( 'hogan/modules', function( $modules ) use ( $module ) {
		$modules[] = $module;
		return $modules;
	} );

}

/**
 * De-register default field group.
 */
function hogan_deregister_default_field_group() {

	if ( did_action( 'hogan/field_groups_registered' ) ) {
		_doing_it_wrong( __METHOD__, esc_html__( 'Hogan field groups have already been registered. Please run hogan_deregister_default_field_group() on action hogan/include_field_groups.', 'hogan-core' ) , '1.0.0' );
	}

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

	if ( did_action( 'hogan/field_groups_registered' ) ) {
		_doing_it_wrong( __METHOD__, esc_html__( 'Hogan field groups have already been registered. Please run hogan_register_field_group() on action hogan/include_field_groups.', 'hogan-core' ) , '1.0.0' );
	}

	$group = [
		'name' => sanitize_key( $name ),
		'label' => $label,
		'modules' => $modules,
		'location' => $location,
		'hide_on_screen' => $hide_on_screen,
		'fields_before_flexible_content' => $fields_before_flexible_content,
		'fields_after_flexible_content' => $fields_after_flexible_content,
	];

	add_filter( 'hogan/field_groups', function( $groups ) use ( $group ) {
		$groups[] = $group;
		return $groups;
	} );
}
