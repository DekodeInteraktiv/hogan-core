<?php
/**
 * Hogan Component: Caption
 *
 * @package Hogan
 */

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$defaults = [
	'classname' => '',
	'content'   => '',
	'tag'       => 'figcaption',
];

$args = wp_parse_args( $args, $defaults );

// Return early if no content.
if ( empty( $args['content'] ) ) {
	return;
}

printf( '<%1$s class="%2$s">%3$s</%1$s>',
	esc_html( $args['tag'] ),
	esc_attr( hogan_classnames( 'hogan-caption', $args['classname'] ) ),
	wp_kses( $args['content'], hogan_caption_allowed_html() )
);
