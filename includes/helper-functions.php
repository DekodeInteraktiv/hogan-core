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

	return [
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
 * Enqueue module assets
 *
 * @param string|array $modules Modules.
 */
function hogan_enqueue_module_assets( $modules ) {
	if ( ! is_array( $modules ) ) {
		$modules = [ $modules ];
	}

	$hogan = \Dekode\Hogan\Core::get_instance();

	foreach ( $modules as $module ) {
		$hogan->enqueue_module_assets( $module );
	}
}

/**
 * Module
 *
 * @param string $name Name of module.
 * @param array  $args Arguments to pass to module.
 */
function hogan_module( string $name, array $args ) {
	$hogan = \Dekode\Hogan\Core::get_instance();
	$hogan->render_module_template( $name, $args );
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

/**
 * Cached version of url_to_postid, which can be expensive.
 *
 * Examine a url and try to determine the post ID it represents.
 *
 * @param string $url Permalink to check.
 * @return int Post ID, or 0 on failure.
 */
function hogan_url_to_postid( string $url ) : int {
	// Sanity check; no URLs not from this site.
	if ( wp_parse_url( $url, PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST ) ) {
		return 0;
	}

	$cache_key = md5( $url );
	$post_id   = wp_cache_get( $cache_key, 'url_to_postid' );

	if ( false === $post_id ) {
		$post_id = url_to_postid( $url ); // phpcs:ignore
		wp_cache_set( $cache_key, $post_id, 'url_to_postid', 3 * HOUR_IN_SECONDS );
	}

	return (int) $post_id;
}

/**
 * Get link title from link field
 *
 * @param array $link Link field.
 * @return string Link title.
 */
function hogan_get_link_title( array $link ) : string {
	// Return early if link title already exists.
	if ( ! empty( $link['title'] ) ) {
		return $link['title'];
	}

	// Try find post id based on url and return post title if found.
	$post_id = hogan_url_to_postid( $link['url'] );
	if ( 0 !== $post_id ) {
		$title = get_the_title( $post_id );

		// Check if the post does have a title.
		if ( ! empty( $title ) ) {
			return $title;
		}
	}

	// Return url as a last resort.
	return $link['url'];
}
