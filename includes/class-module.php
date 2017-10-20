<?php
/**
 * Base class for flexible content layouts.
 *
 * @package Hogan
 */

namespace Dekode\Hogan;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * The class.
 */
abstract class Module {
	/**
	 * Module name.
	 *
	 * @var string $name
	 */
	public $name;

	/**
	 * Module label.
	 *
	 * @var string $label
	 */
	public $label;

	/**
	 * Module field key prefix.
	 *
	 * @var string $field_key
	 */
	public $field_key;

	/**
	 * Module output template.
	 *
	 * @var string $template
	 */
	public $template;

	/**
	 * Module raw field values.
	 *
	 * @var string $template
	 */
	public $raw_content;

	/**
	 * Wrapper classes.
	 *
	 * @var array $wrapper_classes
	 */
	public $wrapper_classes = [];

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->field_key = 'hogan_module_' . $this->name;

		add_filter( 'hogan_modules', function( $modules ) {
			$modules[] = $this;
			return $modules;
		} );

		$this->wrapper_classes = array_merge(
			apply_filters( 'hogan_module_wrapper_classes', [
				'row',
				'hogan-module',
			] ),
			apply_filters( 'hogan_module_' . $this->name . '_wrapper_classes', [
				'hogan-module-' . $this->name,
			] )
		);

		// TODO: enque scripts? add_action( 'wp_enqueue_scripts', 'pantelotteriet_enqueue_assets' ).
	}

	/**
	 * Base class for field definitions.
	 */
	public function get_layout_definition() { }

	/**
	 * Base class for loading module assets.
	 */
	public function load_assets() {
		// TODO: wp_enqueue_style( 'style', get_stylesheet_uri(), [ 'aos' ], $_v ) ?
	}

	/**
	 * Map raw field values to content array.
	 *
	 * @param array $content Content values.
	 */
	public function load_args_from_layout_content( $content ) {
		$this->raw_content = $content;
	}

	/**
	 * Render module template.
	 */
	public function render_template() {
		// TODO: Filter template and check if the template exists.
		include $this->template;
	}
}
