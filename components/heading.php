<?php
/**
 * Hogan Component: Heading
 *
 * @package Hogan
 */

$defaults = [
	'classname' => '',
	'tag'       => 'h2',
	'title'     => '',
];

$args = wp_parse_args( $args, $defaults );

// Return early if title isn't defined.
if ( empty( $args['title'] ) ) {
	return;
}

// Only allow certain tags.
if ( ! in_array( $args['tag'], [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ], true ) ) {
	$args['tag'] = $defaults['tag'];
}

printf( '<%1$s class="%2$s">%3$s</%1$s>',
	esc_html( $args['tag'] ),
	esc_attr( hogan_classnames( 'hogan-heading', $args['classname'] ) ),
	wp_kses( $args['title'], [
		'br' => [],
	] )
);
