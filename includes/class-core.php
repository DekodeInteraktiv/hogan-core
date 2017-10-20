<?php
/**
 * Core framework class.
 *
 * @package Hogan
 */

namespace Dekode\Hogan;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Class for rendering modules.
 */
class Core {

	/**
	 * Directory.
	 *
	 * @var string $dir
	 */
	public $dir;

	/**
	 * Field groups.
	 *
	 * @var array $field_groups
	 */
	private $field_groups = [];

	/**
	 * Modules.
	 *
	 * @var array $modules
	 */
	private $modules = [];

	/**
	 * Module constructor.
	 *
	 * @param string $dir Plugin directory.
	 */
	public function __construct( $dir ) {
		$this->dir = $dir;

		// Load text domain on plugins_loaded.
		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );

		// Register modules and default field group.
		add_action( 'acf/include_fields', [ $this, 'register_default_field_group' ], 15 );

		// The content filter.
		add_filter( 'the_content', [ $this, 'render_modules' ] );

		add_filter( 'acf/fields/wysiwyg/toolbars', [ $this, 'append_hogan_wysiwyg_toolbar' ] );
		add_filter( 'tiny_mce_before_init', [ $this, 'override_tinymce_settings' ] );

		if ( true === apply_filters( 'hogan/flexible_content_layouts_collapsed_by_default', false ) && is_admin() ) {
			add_action( 'acf/input/admin_footer', [ $this, 'append_footer_script_for_collapsed_flexible_content_layouts' ] );
		};
	}

	/**
	 * Load textdomain for translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'hogan', false, $this->dir . '/languages' );
	}

	public function register_module( $module ) {

		if ( $module instanceof Module ) {
			$this->modules[ $module->name ] = $module;
		} else {
			$instance = new $module();
			$this->modules[ $instance->name ] = $instance;
		}
	}

	/**
	 * Register a specific field group.
	 *
	 * @param string $name                           Field group name.
	 * @param mixed  $label                          Label.
	 * @param mixed  $modules                        Array/String with supported modules.
	 * @param array  $location                       Location rules.
	 * @param array  $hide_on_screen                 Array of elements to hide on edit screen.
	 * @param array  $fields_before_flexible_content Prepend fields.
	 * @param array  $fields_after_flexible_content  Append fields.
	 *
	 * @return void
	 */
	public function register_field_group( $name, $label, $modules = 'all', $location = [], $hide_on_screen = [], $fields_before_flexible_content = [], $fields_after_flexible_content = [] ) {
		// Sanitized field group name will be used for all filters, and prefix for field group and field names.
		$name = sanitize_key( $name );

		$this->field_groups[] = $name;

		if ( true !== apply_filters( 'hogan/field_group/' . $name . '/enabled', true ) ) {
			return;
		}

		// Get flexible content layouts from modules.
		$field_group_layouts = array_map( function( $module ) use ( $modules ) {

			if ( is_array( $modules ) && ! empty( $modules ) ) {

				// Limit modules to specified only.
				if ( in_array( $module->name, $modules, true ) ) {
					return $module->get_layout_definition();
				}
			} else {
				// All modules.
				return $module->get_layout_definition();
			}

		}, $this->modules );

		if ( empty( $field_group_layouts ) ) {
			// No modules, no fun.
			return;
		}

		// Include custom fields before and after flexible content field.
		$field_group_fields = [
			array_merge( $fields_before_flexible_content, [
				'type'         => 'flexible_content',
				'key'          => 'hogan_' . $name . '_modules_key', // i.e. hogan_default_modules_key.
				'name'         => 'hogan_' . $name . '_modules_name',
				'button_label' => esc_html__( 'Add module', 'hogan' ),
				'layouts'      => $field_group_layouts,
			], $fields_after_flexible_content ),
		];

		acf_add_local_field_group(
			[
				'key'            => 'hogan_' . $name, // i.e. hogan_default.
				'title'          => $label,
				'fields'         => $field_group_fields,
				'location'       => apply_filters( 'hogan/field_group/' . $name . '/location', $location ),
				'hide_on_screen' => apply_filters( 'hogan/field_group/' . $name . '/hide_on_screen', $hide_on_screen ),
			]
		);
	}

	/**
	 * Register default field group for modules.
	 */
	public function register_default_field_group() {
		$location = [
			[
				[
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'post',
				],
			],
			[
				[
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'page',
				],
			],
		];

		$hide_on_screen = [
			'the_content',
			'custom_fields',
			'discussion',
			'comments',
			'revisions',
			'slug',
			'author',
			'format',
			'tags',
			'send-trackbacks',
		];

		$this->register_field_group( 'default', __( 'Content modules', 'hogan' ), null, $location, $hide_on_screen );
	}

	/**
	 * Render modules.
	 *
	 * @param string $content Content HTML string.
	 */
	public function render_modules( $content ) {

		global $more, $post;
		$flexible_content = '';

		// Remove current filter to avoid recursive loop.
		remove_filter( 'the_content', [ $this, 'render_modules' ] );

		if ( $more && $post instanceof \WP_Post && function_exists( 'get_field' ) && ! post_password_required( $post ) ) {

			foreach ( $this->field_groups as $field_group ) {

				$layouts = get_field( 'hogan_' . $field_group . '_modules_name', $post );

				if ( is_array( $layouts ) && count( $layouts ) ) {
					foreach ( $layouts as $layout ) {

						if ( ! isset( $layout['acf_fc_layout'] ) || empty( $layout['acf_fc_layout'] ) ) {
							continue;
						}

						// Get the right module.
						$module = $this->modules[ $layout['acf_fc_layout'] ];

						if ( $module instanceof Module ) {
							// TODO: Cache HTML.
							ob_start();

							$module->load_args_from_layout_content( $layout );
							$module->render_template();

							$flexible_content .= ob_get_clean();
						}
					}
				}
			}
		}

		// Re add filter after parsing content.
		add_filter( 'the_content', [ $this, 'render_modules' ] );

		return $content . $flexible_content;
	}

	/**
	 * Add custom toolbars
	 *
	 * @param array $toolbars Current Toolbars.
	 * @return array $toolbars Array with new toolbars.
	 */
	public function append_hogan_wysiwyg_toolbar( $toolbars ) {
		// TODO: Include blockquote tinymce plugin. 'blockquote_cite'.
		$toolbars['hogan'] = [
			1 => [
				'formatselect',
				'bold',
				'italic',
				'numlist',
				'bullist',
				'undo',
				'redo',
				'link',
				'unlink',
				'pastetext',
				'removeformat',
				'code',
			],
		];

		return $toolbars;
	}

	/**
	 * Override TinyMCE Settings
	 *
	 * @param array $settings TinyMCE settings.
	 * @return array $settings Optimized TinyMCE settings.
	 */
	public function override_tinymce_settings( $settings ) {
		$settings['block_formats'] = apply_filters( 'hogan/tinymce_block_formats', 'Paragraph=p;Overskrift 2=h2;Overskrift 3=h3;Overskrift4=h4' );
		return $settings;
	}

	/**
	 * Re-style the modules.
	 *
	 * @return void
	 */
	public function append_footer_script_for_collapsed_flexible_content_layouts() {
		?>
		<script>
			(function( $ ) {
				$( document ).ready( function( ) {
					$('.acf-flexible-content .layout').addClass('-collapsed');
					$('.acf-flexible-content .acf-postbox').addClass('closed');
				});
			} )( jQuery );
		</script>
		<?php
	}
}
