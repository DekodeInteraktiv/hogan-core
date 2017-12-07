<?php
/**
 * Helper functions in global namespace.
 *
 * @package Hogan
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registert module
 *
 * @param \Dekode\Hogan\Module $module Module object.
 *
 * @return void
 */
function hogan_register_module( \Dekode\Hogan\Module $module ) {

	if ( did_action( 'hogan/modules_registered' ) ) {
		_doing_it_wrong( __METHOD__, esc_html__( 'Hogan modules have already been registered. Please run hogan_register_module() on action hogan/include_modules.', 'hogan-core' ), '1.0.0' );
	}

	add_filter( 'hogan/modules', function( array $modules ) use ( $module ) : array {
		$modules[] = $module;
		return $modules;
	} );
}

/**
 * De-register default field group.
 *
 * @return void
 */
function hogan_deregister_default_field_group() {

	if ( did_action( 'hogan/field_groups_registered' ) ) {
		_doing_it_wrong( __METHOD__, esc_html__( 'Hogan field groups have already been registered. Please run hogan_deregister_default_field_group() on action hogan/include_field_groups.', 'hogan-core' ), '1.0.0' );
	}

	add_filter( 'hogan/field_group/default/enabled', '__return_false' );
}

/**
 * Register a specific field group.
 *
 * @param string $name                           Field group name.
 * @param string $label                          Label.
 * @param array  $modules                        Array/String with supported modules.
 * @param array  $location                       Location rules.
 * @param array  $hide_on_screen                 Array of elements to hide on edit screen.
 * @param array  $fields_before_flexible_content Prepend fields.
 * @param array  $fields_after_flexible_content  Append fields.
 * @return void
 */
function hogan_register_field_group( string $name, string $label, array $modules = [], array $location = [], array $hide_on_screen = [], array $fields_before_flexible_content = [], array $fields_after_flexible_content = [] ) {

	if ( did_action( 'hogan/field_groups_registered' ) ) {
		_doing_it_wrong( __METHOD__, esc_html__( 'Hogan field groups have already been registered. Please run hogan_register_field_group() on action hogan/include_field_groups.', 'hogan-core' ), '1.0.0' );
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

	add_filter( 'hogan/field_groups', function( array $groups ) use ( $group ) : array {
		$groups[] = $group;
		return $groups;
	} );
}

/**
 * Helper function for adding default heading field
 *
 * @param array                $fields ACF fields array.
 * @param \Dekode\Hogan\Module $module Hogan module object.
 * @return void
 */
function hogan_append_heading_field( array &$fields, \Dekode\Hogan\Module $module ) {

	if ( true === apply_filters( 'hogan/module/' . $module->name . '/heading/enabled', true ) ) {

		$fields[] = [
			'type'         => 'text',
			'key'          => $module->field_key . '_heading',
			'name'         => 'heading',
			'label'        => __( 'Heading', 'hogan-core' ),
			'instructions' => __( 'Optional heading will show only if filled in.', 'hogan-core' ),
		];

		$module->add_helper_field( 'heading' );
	}
}

/**
 * Helper function for adding caption field
 *
 * @param array                $fields ACF fields array.
 * @param \Dekode\Hogan\Module $module Hogan module object.
 * @return void
 */
function hogan_append_caption_field( array &$fields, \Dekode\Hogan\Module $module ) {

	if ( true === apply_filters( 'hogan/module/' . $module->name . '/caption/enabled', true ) ) {

		$fields[] = [
			'type'         => 'wysiwyg',
			'key'          => $module->field_key . '_caption',
			'name'         => 'caption',
			'label'        => __( 'Caption', 'hogan-core' ),
			'instructions' => apply_filters( 'hogan/module/' . $module->name . '/caption/instructions', '' ),
			'tabs'         => apply_filters( 'hogan/module/' . $module->name . '/caption/tabs', 'visual' ),
			'media_upload' => apply_filters( 'hogan/module/' . $module->name . '/caption/allow_media_upload', 0 ),
			'toolbar'      => apply_filters( 'hogan/module/' . $module->name . '/caption/toolbar', 'hogan_caption' ),
			'wrapper'      => [
				'class' => apply_filters( 'hogan/module/' . $module->name . '/caption/wrapper_class', 'small-height-editor' ),
			],
		];

		$module->add_helper_field( 'caption' );
	}
}

/**
 * Allowed wp_kses HTML for caption field
 *
 * @return array
 */
function hogan_caption_allowed_html() : array {

	return [
		'strong' => true,
		'em' => true,
		'a' => [
			'href' => true,
			'target' => true,
		],
	];
}
