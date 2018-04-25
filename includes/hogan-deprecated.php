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
 * @return void
 */
function hogan_register_module( \Dekode\Hogan\Module $module ) {

	_deprecated_function( __FUNCTION__, '1.1.7', 'register_module()' );

	$core = \Dekode\Hogan\Core::get_instance();
	$core->register_module( $module );
}

/**
 * De-register default field group.
 *
 * @deprecated 1.3.0 Use filter.
 * @return void
 */
function hogan_deregister_default_field_group() {

	_deprecated_function( __FUNCTION__, '1.3.0', '\'hogan/field_group/&lt;name&gt;/enabled\' filter' );

	if ( did_action( 'hogan/field_groups_included' ) ) {
		_doing_it_wrong( __METHOD__, esc_html__( 'Hogan field groups have already been registered. Please register/deregister field groups on action hogan/include_field_groups.', 'hogan-core' ), '1.0.0' );
	}

	add_filter( 'hogan/field_group/default/enabled', '__return_false' );
}

/**
 * Register a specific field group.
 *
 * @deprecated 1.3.0 Use core function register_field_group()
 * @param string $name                           Field group name.
 * @param string $title                          Title.
 * @param array  $modules                        Array/String with supported modules.
 * @param array  $location                       Location rules.
 * @param array  $hide_on_screen                 Array of elements to hide on edit screen.
 * @param array  $fields_before_flexible_content Prepend fields.
 * @param array  $fields_after_flexible_content  Append fields.
 * @return void
 */
function hogan_register_field_group( string $name, string $title, array $modules = [], array $location = [], array $hide_on_screen = [], array $fields_before_flexible_content = [], array $fields_after_flexible_content = [] ) {

	_deprecated_function( __FUNCTION__, '1.3.0', 'Use core function register_field_group()' );

	$core = \Dekode\Hogan\Core::get_instance();
	$core->register_field_group( [
		'name'                           => $name,
		'title'                          => $title,
		'modules'                        => $modules,
		'location'                       => $location,
		'hide_on_screen'                 => $hide_on_screen,
		'fields_before_flexible_content' => $fields_before_flexible_content,
		'fields_after_flexible_content'  => $fields_after_flexible_content,
	] );
}
