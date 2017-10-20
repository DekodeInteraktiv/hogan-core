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

	public $wrapper_classes = [];

	/**
	 * Constructor.
	 */
	public function __construct() {
		// TODO: enque scripts? add_action( 'wp_enqueue_scripts', 'pantelotteriet_enqueue_assets' ).

		$this->field_key = 'hogan_module_' . $this->name; // hogan_module_text

		add_filter( 'hogan/modules', function( $modules ) {
			$modules[] = $this;
			return $modules;
		} );

		$this->wrapper_classes = array_merge(
			apply_filters( 'hogan/module/wrapper_classes', [
				'row',
				//'row-margined',
				'hogan-module',
			] ),
			apply_filters( 'hogan/module/' . $this->name . '/wrapper_classes', [
				'hogan-module-' . $this->name,
			] )
		);
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
		include $this->template;
	}

}
