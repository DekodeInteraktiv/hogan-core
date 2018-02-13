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
	'content'   => '',
	'tag'       => 'div',
];

$args = wp_parse_args( $args, $defaults );

// Return early if no content.
if ( empty( $args['content'] ) ) {
	return;
}

printf(
	'<%1$s class="%2$s">%3$s</%1$s>',
	esc_html( $args['tag'] ),
	esc_attr( hogan_classnames( 'hogan-lead', $args['classname'] ) ),
	wp_kses( $args['content'], hogan_lead_allowed_html() )
);
