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

	add_filter(
		'hogan/modules', function( array $modules ) use ( $module ) : array {
			$modules[] = $module;
			return $modules;
		}
	);
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
		'name'                           => sanitize_key( $name ),
		'label'                          => $label,
		'modules'                        => $modules,
		'location'                       => $location,
		'hide_on_screen'                 => $hide_on_screen,
		'fields_before_flexible_content' => $fields_before_flexible_content,
		'fields_after_flexible_content'  => $fields_after_flexible_content,
	];

	add_filter(
		'hogan/field_groups', function( array $groups ) use ( $group ) : array {
			$groups[] = $group;
			return $groups;
		}
	);
}

/**
 * Helper function for adding default heading field
 *
 * @param array                $fields ACF fields array.
 * @param \Dekode\Hogan\Module $module Hogan module object.
 * @return void
 */
function hogan_append_heading_field( array &$fields, \Dekode\Hogan\Module $module ) {
	$fields[] = [
		'type'         => 'text',
		'key'          => $module->field_key . '_heading',
		'name'         => 'heading',
		'label'        => __( 'Heading', 'hogan-core' ),
		'instructions' => __( 'Optional heading will show only if filled in.', 'hogan-core' ),
	];

	$module->add_helper_field( 'heading' );
}

/**
 * Helper function for adding default lead field
 *
 * @param array                $fields ACF fields array.
 * @param \Dekode\Hogan\Module $module Hogan module object.
 * @return void
 */
function hogan_append_lead_field( array &$fields, \Dekode\Hogan\Module $module ) {

	$fields[] = [
		'type'         => 'wysiwyg',
		'key'          => $module->field_key . '_lead',
		'name'         => 'lead',
		'label'        => __( 'Lead Paragraph', 'hogan-core' ),
		'instructions' => apply_filters( 'hogan/module/' . $module->name . '/lead/instructions', '' ),
		'tabs'         => apply_filters( 'hogan/module/' . $module->name . '/lead/tabs', 'visual' ),
		'media_upload' => apply_filters( 'hogan/module/' . $module->name . '/lead/allow_media_upload', 0 ),
		'toolbar'      => apply_filters( 'hogan/module/' . $module->name . '/lead/toolbar', 'hogan_caption' ),
		'wrapper'      => [
			'class' => apply_filters( 'hogan/module/' . $module->name . '/lead/wrapper_class', 'medium-height-editor' ),
		],
	];

	$module->add_helper_field( 'lead' );
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
		'em'     => true,
		'a'      => [
			'href'   => true,
			'target' => true,
		],
	];
}

/**
 * Allowed wp_kses HTML for lead field
 *
 * @return array
 */
function hogan_lead_allowed_html() : array {

	return[
		'p'      => true,
		'strong' => true,
		'em'     => true,
		'a'      => [
			'href'   => true,
			'target' => true,
		],
	];
}

/**
 * Conditionally join classnames together
 *
 * @return string Joined classnames as space separated string.
 */
function hogan_classnames() : string {

	$args = func_get_args();

	$classes = array_map(
		function( $arg ) {

			if ( is_array( $arg ) ) {

				return implode(
					' ', array_filter(
						array_map(
							function( $key, $value ) {
								if ( is_array( $value ) ) {
									return hogan_classnames( $value );
								}

								if ( is_numeric( $key ) ) {
									return $value;
								}

								return $value ? $key : false;
							}, array_keys( $arg ), $arg
						)
					)
				);
			}

				return $arg;

		}, $args
	);

	return trim( implode( ' ', array_filter( $classes ) ) );
}

/**
 * Build tag attributes
 *
 * @param array $attr Array of attributes.
 * @return string Attributes.
 */
function hogan_attributes( array $attr = [] ) : string {
	$attributes = '';

	foreach ( $attr as $name => $value ) {
		if ( empty( $value ) || ! $value ) {
			continue;
		}

		if ( ! $name ) {
			$attributes .= ' ' . esc_attr( $value );
			continue;
		}

		$name = esc_attr( $name );

		if ( is_bool( $value ) ) {
			$attributes .= " {$name}";
			continue;
		}

		if ( 'src' === $name || 'href' === $name ) {
			$value = esc_url( $value );
		} else {
			$value = esc_attr( $value );
		}

		$attributes .= " {$name}=\"{$value}\"";
	}

	return $attributes;
}

/**
 * Component
 *
 * @param string $name Name of component.
 * @param array  $args Arguments to pass to the component.
 */
function hogan_component( string $name, array $args = [] ) {
	$templates = [
		'components/' . $name . '/' . $name . '.php',
		'components/' . $name . '.php',
	];

	$component = '';
	foreach ( $templates as $template ) {
		if ( file_exists( HOGAN_CORE_PATH . '/' . $template ) ) {
			$component = HOGAN_CORE_PATH . '/' . $template;
			break;
		}
	}

	if ( $component && 0 === validate_file( $component ) ) {
		include $component;
	}
}
