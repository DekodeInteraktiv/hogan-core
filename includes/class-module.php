<?php
/**
 * Base class for Hogan Modules.
 *
 * @package Hogan
 */

declare( strict_types = 1 );
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
	 * Module extra helper fields. Require use of helper function, e.g. hogan_append_heading_field() in module get_fields() implementation.
	 *
	 * @var array $helper_fields
	 */
	public $helper_fields = [];

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
	 * Module location in page layout.
	 *
	 * @var int $counter Incremental counter.
	 */
	public $counter;

	/**
	 * Path to plugin
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Template outer wrapper HTML tag.
	 *
	 * @var string
	 */
	protected $outer_wrapper_tag = 'div';

	/**
	 * Template outer wrapper classnames.
	 *
	 * @var array
	 */
	protected $outer_wrapper_classnames = [];

	/**
	 * Template inner wrapper HTML tag.
	 *
	 * @var string
	 */
	protected $inner_wrapper_tag = 'div';

	/**
	 * Template inner wrapper classnames.
	 *
	 * @var array
	 */
	protected $inner_wrapper_classnames = [];

	/**
	 * Constructor.
	 *
	 * @param string $path Path to plugin.
	 */
	public function __construct( $path = '' ) {

		$this->name      = strtolower( substr( strrchr( get_class( $this ), '\\' ), 1 ) );
		$this->field_key = 'hogan_module_' . $this->name;
		$this->path      = trailingslashit( $path );
	}

	/**
	 * Populate helper fields array with helper field. Used in function load_args_from_layout_content().
	 *
	 * @param string $field_name ACF field name.
	 *
	 * @return void
	 */
	public function add_helper_field( string $field_name ) {
		$this->helper_fields[] = $field_name;
	}

	/**
	 * Base class for field definitions.
	 *
	 * @return array
	 */
	final public function get_layout_definition() : array {

		$standard_fields = [];
		// Add heading field to module.
		if ( true === apply_filters( 'hogan/module/' . $this->name . '/heading/enabled', false ) ) {
			hogan_append_heading_field( $standard_fields, $this );
		}

		// Add lead field to module.
		if ( true === apply_filters( 'hogan/module/' . $this->name . '/lead/enabled', false ) ) {
			hogan_append_lead_field( $standard_fields, $this );
		}

		$sub_fields = array_merge(
			apply_filters( 'hogan/module/' . $this->name . '/fields_before', [] ),
			$standard_fields,
			$this->get_fields(),
			apply_filters( 'hogan/module/' . $this->name . '/fields_after', [] )
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
	 * Field definitions for module.
	 *
	 * @return array $fields Fields for this module
	 */
	abstract protected function get_fields() : array;

	/**
	 * Map raw fields from acf to object variable.
	 *
	 * @param array $raw_content Content values.
	 * @param int   $counter Module location in page layout.
	 * @return void
	 */
	public function load_args_from_layout_content( array $raw_content, int $counter = 0 ) {

		// Add helper fields to module instance.
		foreach ( $this->helper_fields as $helper_field ) {
			$this->{$helper_field} = $raw_content[ $helper_field ] ?? '';
		}

		$this->raw_content = $raw_content;
		$this->counter     = $counter;
	}

	/**
	 * Include file
	 *
	 * @param string $file   File to include.
	 * @param bool   $return Return or include file.
	 */
	final public function include_file( string $file, bool $return = false ) {
		$file = $this->path . $file;
		if ( ! file_exists( $file ) || 0 !== validate_file( $file ) ) {
			return '';
		}

		if ( $return ) {
			ob_start();
			require $file;
			return ob_get_clean();
		}

		require $file;
	}

	/**
	 * Validate module content before template is loaded.
	 *
	 * @return bool Whether validation of the module is successful / filled with content.
	 */
	abstract protected function validate_args() : bool;

	/**
	 * Render module wrappers opening tags before template include.
	 *
	 * @param integer $counter Module number.
	 * @return void
	 */
	protected function render_opening_template_wrappers( $counter = 0 ) {

		// Outer wrapper tag with filters for overriding both globally, per module and per module instance.
		$this->outer_wrapper_tag = apply_filters( 'hogan/module/outer_wrapper_tag', $this->outer_wrapper_tag, $this );

		// Inner wrapper tag with filters for overriding both globally, per module and per module instance.
		$this->inner_wrapper_tag = apply_filters( 'hogan/module/inner_wrapper_tag', $this->inner_wrapper_tag, $this );

		// Echo opening outer wrapper.
		if ( ! empty( $this->outer_wrapper_tag ) ) {
			// Outer wrapper classes with filters for overriding both globally, per module and per module instance.
			$outer_wrapper_default_classnames = [
				'hogan-module',
				'hogan-module-' . $this->name,
				'hogan-module-' . $counter,
			];

			$outer_wrapper_classnames = hogan_classnames(
				apply_filters( 'hogan/module/outer_wrapper_classes', array_merge( $outer_wrapper_default_classnames, $this->outer_wrapper_classnames ), $this )
			);

			$outer_wrapper_attributes = apply_filters( 'hogan/module/outer_wrapper_attributes', [], $this );

			printf(
				'<%s id="%s" class="%s"%s>',
				esc_attr( $this->outer_wrapper_tag ),
				esc_attr( 'module-' . $counter ),
				esc_attr( $outer_wrapper_classnames ),
				hogan_attributes( $outer_wrapper_attributes )
			);
		}

		// Echo inner wrapper.
		if ( ! empty( $this->inner_wrapper_tag ) ) {
			// Inner wrapper classes with filters for overriding both globally, per module and per module instance.
			$inner_wrapper_classnames = hogan_classnames(
				apply_filters( 'hogan/module/inner_wrapper_classes', array_merge( [ 'hogan-module-inner' ], $this->inner_wrapper_classnames ), $this )
			);

			$inner_wrapper_attributes = apply_filters( 'hogan/module/inner_wrapper_attributes', [], $this );

			printf(
				'<%s class="%s"%s>',
				esc_attr( $this->inner_wrapper_tag ),
				esc_attr( $inner_wrapper_classnames ),
				hogan_attributes( $inner_wrapper_attributes )
			);
		}
	}

	/**
	 * Render module wrapper closing tags after template include.
	 *
	 * @return void
	 */
	protected function render_closing_template_wrappers() {

		// Echo closing inner wrapper.
		if ( ! empty( $this->inner_wrapper_tag ) ) {
			printf( '</%s>', esc_attr( $this->inner_wrapper_tag ) );
		}

		// Echo closing outer wrapper.
		if ( ! empty( $this->outer_wrapper_tag ) ) {
			printf( '</%s>', esc_attr( $this->outer_wrapper_tag ) );
		}
	}

	/**
	 * Render module template.
	 *
	 * @param string  $raw_content Raw ACF layout content.
	 * @param integer $counter Module number.
	 * @param boolean $echo Echo content.
	 * @return string
	 */
	final public function render_template( $raw_content, $counter = 0, $echo = true ) : string {

		// Load module data from raw ACF layout content.
		$this->load_args_from_layout_content( $raw_content, $counter );

		if ( true !== $this->validate_args() ) {
			return '';
		}

		$template = apply_filters( 'hogan/module/' . $this->name . '/template', $this->template, $this );

		if ( ! file_exists( $template ) || 0 !== validate_file( $template ) ) {
			return '';
		}

		if ( false === $echo ) {
			ob_start();
		}

		// Echo opening wrappers.
		$this->render_opening_template_wrappers( $counter );

		$output_heading_component = true === apply_filters( 'hogan/module/' . $this->name . '/heading/enabled', false ) && ! empty( $this->heading );
		$output_lead_component    = true === apply_filters( 'hogan/module/' . $this->name . '/lead/enabled', false ) && ! empty( $this->lead );
		$wrap_heading_and_lead    = true === apply_filters( 'hogan/module/wrap_heading_and_lead', false, $this ) && ( $output_heading_component || $output_lead_component );

		// Wrap standard fields.
		if ( true === $wrap_heading_and_lead ) {
			printf( '<div class="%s">', esc_attr( hogan_classnames( 'hogan-module-header', 'hogan-module-' . $this->name . '-header' ) ) );
		}

		// Include standard fields.
		if ( true === $output_heading_component ) {
			hogan_component(
				'heading', [
					'title' => $this->heading,
				]
			);
		}

		if ( true === $output_lead_component ) {
			hogan_component(
				'lead', [
					'content' => $this->lead,
				]
			);
		}

		if ( true === $wrap_heading_and_lead ) {
			echo '</div>';
		}

		// Render custom fields before template.
		do_action( 'hogan/module/render_fields_before', $raw_content, $this );

		// Include module template.
		include $template;

		// Render custom fields after template.
		do_action( 'hogan/module/render_fields_after', $raw_content, $this );

		// Echo closing wrappers.
		$this->render_closing_template_wrappers();

		if ( false === $echo ) {
			return ob_get_clean();
		}

		return '';
	}
}
