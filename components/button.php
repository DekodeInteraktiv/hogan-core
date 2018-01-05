<?php
/**
 * Hogan Component: Button
 *
 * @package Hogan
 */

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$defaults = [
	'attr'      => [],
	'classname' => '',
	'disabled'  => false,
	'target'    => '',
	'title'     => '',
	'type'      => 'button',
	'url'       => '',
];

$args = wp_parse_args( $args, $defaults );

// Return early if title isn't defined.
if ( empty( $args['title'] ) ) {
	return;
}

$props = [
	'class' => hogan_classnames( 'hogan-button', $args['classname'] ),
];

if ( ! empty( $args['url'] ) && ! $args['disabled'] ) {
	$tag             = 'a';
	$props['href']   = $args['url'];
	$props['target'] = $args['target'];

	if ( '_blank' === $args['target'] ) {
		$props['rel'] = 'noopener noreferrer';
	}
} else {
	$tag               = 'button';
	$props['type']     = $args['type'];
	$props['disabled'] = $args['disabled'];
}

$attributes = wp_parse_args( $args['attr'], $props );

printf( '<%1$s%2$s>%3$s</%1$s>',
	esc_html( $tag ),
	hogan_attributes( $attributes ),
	wp_kses( $args['title'], [
		'br'     => [],
		'em'     => [],
		'strong' => [],
	] )
);
