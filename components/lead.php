<?php
/**
 * Hogan Component: Lead
 *
 * @package Hogan
 */

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$defaults = [
	'classname' => '',
	'tag'       => 'p',
	'content'   => '',
];

$args = wp_parse_args( $args, $defaults );

// Return early if title isn't defined.
if ( empty( $args['content'] ) ) {
	return;
}

printf(
	'<%1$s class="%2$s">%3$s</%1$s>',
	esc_html( $args['tag'] ),
	esc_attr( hogan_classnames( 'hogan-lead', $args['classname'] ) ),
	esc_textarea( $args['content'] )
);
