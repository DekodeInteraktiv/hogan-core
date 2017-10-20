<?php
/**
 * Base class for Hogan Modules.
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
	 * Module field key prefix.
	 *
	 * @var string $field_key
	 */
	public $field_key;

	/**
	 * Module label.
	 *
	 * @var string $label
	 */
	public $label;

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

		$this->name = strtolower( substr( strrchr( get_class( $this ), '\\' ), 1 ) );
		$this->field_key = 'hogan_module_' . $this->name;

		$this->wrapper_classes = array_merge(
			apply_filters( 'hogan/module/wrapper_classes', [
				'row',
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
		include apply_filters( 'hogan/module/' . $this->name . '/template', $this->template );
	}
}
