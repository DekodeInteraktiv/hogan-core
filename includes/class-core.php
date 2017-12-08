<?php
/**
 * Core framework class.
 *
 * @package Hogan
 */

declare( strict_types = 1 );
namespace Dekode\Hogan;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Class for rendering modules.
 */
class Core {

	/**
	 * Plugin base directory
	 *
	 * @var string $dir
	 */
	public $dir;

	/**
	 * Plugin base URL
	 *
	 * @var string $url
	 */
	public $url;

	/**
	 * Field groups.
	 *
	 * @var array $_field_groups
	 */
	private $_field_groups = [];

	/**
	 * Modules.
	 *
	 * @var array $_modules
	 */
	private $_modules = [];

	/**
	 * Hold the class instance.
	 *
	 * @var Core $_instance
	 */
	private static $_instance = null;

	/**
	 * Priority for the_content filter.
	 *
	 * @var int $the_content_priority
	 */
	private $_the_content_priority = 0;

	/**
	 * Module constructor.
	 *
	 * @param string $dir Plugin base directory.
	 * @param string $url Plugin base url.
	 * @return void
	 */
	private function __construct( $dir, $url ) {
		$this->dir = $dir;
		$this->url = $url;

		$this->_the_content_priority = absint( apply_filters( 'hogan/the_content_priority', 10 ) );

		// Load text domain on plugins_loaded.
		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );

		// Add admin stylesheets and scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );

		// Register default field group.
		add_action( 'plugins_loaded', [ $this, 'register_default_field_group' ] );

		// Register all modules when acf is ready.
		add_action( 'acf/include_fields', [ $this, 'register_modules' ] );

		// Register all field groups when acf is ready.
		add_action( 'acf/include_fields', [ $this, 'register_field_groups' ] );

		// Append Flexible Content modules to the_content.
		add_filter( 'the_content', [ $this, 'append_modules_content' ], $this->_the_content_priority, 1 );

		// Index modules as post content in SearchWP.
		if ( true === apply_filters( 'hogan/searchwp/index_modules_as_post_content', true ) ) {
			add_filter( 'searchwp_pre_set_post', [ $this, 'populate_post_content_for_indexing' ] );
		}

		add_filter( 'acf/fields/wysiwyg/toolbars', [ $this, 'append_hogan_wysiwyg_toolbar' ] );
		add_filter( 'tiny_mce_before_init', [ $this, 'override_tinymce_settings' ] );

		if ( true === apply_filters( 'hogan/flexible_content_layouts_collapsed_by_default', false ) && is_admin() ) {
			add_action( 'acf/input/admin_footer', [ $this, 'append_footer_script_for_collapsed_flexible_content_layouts' ] );
		};
	}

	/**
	 * Get Core instance.
	 *
	 * @param string $dir Plugin base directory.
	 * @param string $url Plugin base url.
	 * @return Core Core instance.
	 */
	public static function get_instance( string $dir = '', string $url = '' ) : Core {

		if ( null === self::$_instance ) {
			self::$_instance = new Core( $dir, $url );
		}

		return self::$_instance;
	}

	/**
	 * Load textdomain for translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'hogan-core', false, $this->dir . '/languages' );
	}

	/**
	 * Load plugin admin assets.
	 *
	 * @return void
	 */
	public function enqueue_admin_assets() {
		$assets_version = defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ? time() : false;
		wp_enqueue_style( 'hogan-admin-style', $this->url . 'assets/style.css', [ 'acf-pro-input' ], $assets_version );
	}

	/**
	 * Register modules from filter into core plugin.
	 *
	 * @return void
	 */
	public function register_modules() {

		do_action( 'hogan/include_modules' );

		foreach ( apply_filters( 'hogan/modules', [] ) as $module ) {

			if ( ! ( $module instanceof Module ) ) {
				$module = new $module();
			}

			$this->_modules[ $module->name ] = $module;
		}

		do_action( 'hogan/modules_registered' );
	}

	/**
	 * Register field groups from filter into core plugin.
	 *
	 * @return void
	 */
	public function register_field_groups() {

		do_action( 'hogan/include_field_groups' );

		foreach ( apply_filters( 'hogan/field_groups', [] ) as $g ) {
			$this->register_field_group( $g['name'], $g['label'], $g['modules'], $g['location'], $g['hide_on_screen'], $g['fields_before_flexible_content'], $g['fields_after_flexible_content'] );
		}

		do_action( 'hogan/field_groups_registered' );
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

		$this->_field_groups[] = $name;

		if ( true !== apply_filters( 'hogan/field_group/' . $name . '/enabled', true ) ) {
			return;
		}

		// Get flexible content layouts from modules.
		$field_group_layouts = array_filter( array_map( function( Module $module ) use ( $modules ) : array {

			if ( is_array( $modules ) && ! empty( $modules ) ) {

				// Limit modules to specified only.
				if ( in_array( $module->name, $modules, true ) ) {
					return $module->get_layout_definition();
				}
			} else {
				// All modules.
				return $module->get_layout_definition();
			}

		}, $this->_modules ) );

		if ( empty( $field_group_layouts ) ) {
			// No modules, no fun.
			return;
		}

		$fields_before_flexible_content = apply_filters( 'hogan/field_group/' . $name . '/fields_before_flexible_content', $fields_before_flexible_content );
		$fields_after_flexible_content = apply_filters( 'hogan/field_group/' . $name . '/fields_after_flexible_content', $fields_after_flexible_content );

		// Include custom fields before and after flexible content field.
		$field_group_fields =
			array_merge( $fields_before_flexible_content, [
				[
					'type'         => 'flexible_content',
					'key'          => 'hogan_' . $name . '_modules_key', // i.e. hogan_default_modules_key.
					'name'         => 'hogan_' . $name . '_modules_name',
					'button_label' => esc_html__( 'Add module', 'hogan-core' ),
					'layouts'      => $field_group_layouts,
				],
			], $fields_after_flexible_content );

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
	 *
	 * @return void
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

		hogan_register_field_group( 'default', __( 'Content Modules', 'hogan-core' ), [], $location, $hide_on_screen );
	}

	/**
	 * Append modules HTML content to the_content for the global post object.
	 *
	 * @param string $content Content HTML string.
	 * @return string
	 */
	public function append_modules_content( string $content ) : string {

		global $more, $post;
		$flexible_content = '';

		// Remove current filter to avoid recursive loop.
		remove_filter( 'the_content', [ $this, 'append_modules_content' ], $this->_the_content_priority );

		if ( $post instanceof \WP_Post && function_exists( 'get_field' ) && ( $more || is_search() ) && ! post_password_required( $post ) ) {
			$flexible_content = $this->get_modules_content( $post );
		}

		// Re-add filter after parsing content.
		add_filter( 'the_content', [ $this, 'append_modules_content' ], $this->_the_content_priority, 1 );

		return $content . $flexible_content;
	}

	/**
	 * Get modules HTML content.
	 *
	 * @param \WP_Post $post The post.
	 * @return string
	 */
	private function get_modules_content( \WP_Post $post ) : string {

		$cache_key = 'hogan_modules_' . $post->ID;
		$cache_group = 'hogan_modules';

		$flexible_content = ''; // wp_cache_get( $cache_key, $cache_group );.

		if ( empty( $flexible_content ) ) {

			foreach ( $this->_field_groups as $field_group ) {

				$layouts = get_field( 'hogan_' . $field_group . '_modules_name', $post );

				if ( is_array( $layouts ) && count( $layouts ) ) {

					$module_counter = 0;

					foreach ( $layouts as $layout ) {

						if ( ! isset( $layout['acf_fc_layout'] ) || empty( $layout['acf_fc_layout'] ) ) {
							continue;
						}

						// Get the right module.
						$module = true === isset( $this->_modules[ $layout['acf_fc_layout'] ] ) ? $this->_modules[ $layout['acf_fc_layout'] ] : null;

						if ( $module instanceof Module ) {
							ob_start();

							$module->render_template( $layout, $module_counter );

							$flexible_content .= ob_get_clean();
							$module_counter++;
						}
					}
				}
			}

			// wp_cache_add( $cache_key, $flexible_content, $cache_group, 500 );.
		}

		return (string) $flexible_content;
	}

	/**
	 * Populate the post_content with modules HTML before SearchWP indexing.
	 *
	 * @param \WP_Post $post The post.
	 * @return \WP_Post
	 */
	public function populate_post_content_for_indexing( \WP_Post $post ) : \WP_Post {

		// Fake fill the post_content with modules before SearchWP indexing.
		$post->post_content = $this->get_modules_content( $post );
		return $post;
	}

	/**
	 * Add custom toolbars
	 *
	 * @param array $toolbars Current Toolbars.
	 * @return array $toolbars Array with new toolbars.
	 */
	public function append_hogan_wysiwyg_toolbar( array $toolbars ) : array {

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

		$toolbars['hogan_caption'] = [
			1 => [
				'bold',
				'italic',
				'link',
				'unlink',
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
	public function override_tinymce_settings( array $settings ) : array {
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
