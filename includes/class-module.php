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
	private $wrapper_classes = [];

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->name = strtolower( substr( strrchr( get_class( $this ), '\\' ), 1 ) );
		$this->field_key = 'hogan_module_' . $this->name;
	}

	/**
	 * Base class for field definitions.
	 */
	final public function get_layout_definition() {

		$sub_fields = array_merge(
			apply_filters( 'hogan/module/' . $this->name . '/fields/pre', [] ),
			$this->get_fields(),
			apply_filters( 'hogan/module/' . $this->name . '/fields/post', [] )
		);

		return [
			'key'        => $this->field_key,
			'name'       => $this->name,
			'label'      => $this->label,
			'display'    => apply_filters( 'hogan/module/' . $this->name . '/layout/display', 'block' ),
			'sub_fields' => apply_filters( 'hogan/module/' . $this->name . '/layout/sub_fields', $sub_fields ),
			'min'        => apply_filters( 'hogan/module/' . $this->name . '/layout/min', '' ),
			'max'        => apply_filters( 'hogan/module/' . $this->name . '/layout/max', '' ),
		];
	}

	/**
	 * Field definitions per module.
	 */
	public function get_fields() {
		return [];
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

		$this->wrapper_classes = array_merge(
			apply_filters( 'hogan/module/wrapper_classes', [
				'hogan-module',
			] ),
			apply_filters( 'hogan/module/' . $this->name . '/wrapper_classes', [
				'hogan-module-' . $this->name,
			], $this )
		);

		include apply_filters( 'hogan/module/' . $this->name . '/template', $this->template );
	}

	/**
	 * Get module wrapper classes
	 *
	 * @param boolean $as_string Return classes as seperated string.
	 * @param string  $seperator String seperator.
	 * @return mixed  Classes as array or string with seperator.
	 */
	public function get_wrapper_classes( $as_string = false, $seperator = ' ' ) {

		if ( true === $as_string ) {
			return trim( implode( $seperator, array_filter( $this->wrapper_classes ) ) );
		}

		return array_filter( $this->wrapper_classes );
	}
}
