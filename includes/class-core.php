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
	 * Field groups.
	 *
	 * @var array $_field_groups
	 */
	private $_field_groups = [];

	/**
	 * Current post layouts.
	 *
	 * @var array $_layouts
	 */
	private $_current_layouts = [];

	/**
	 * Modules.
	 *
	 * @var array $_modules
	 */
	private $_modules = [];

	/**
	 * Module counter
	 *
	 * @var int $_module_counter
	 */
	private $_module_counter = 0;

	/**
	 * Flexible content stored for reuse if the_content is applied multiple times.
	 *
	 * @var array
	 */
	private $_flexible_content = [];

	/**
	 * Enqueued module assets
	 *
	 * @var array $_enqueued_modules
	 */
	private $_enqueued_modules = [];

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
	 * Assets version
	 *
	 * @var string $_assets_version
	 */
	private $_assets_version;

	/**
	 * Init hooks and filter on plugins_loaded.
	 *
	 * @return void
	 */
	public function setup_on_plugins_loaded() {

		// Register core text domain.
		\load_plugin_textdomain( 'hogan-core', false, HOGAN_CORE_DIR . '/languages' );

		// Register all modules when acf is ready.
		add_action( 'acf/include_fields', [ $this, 'include_modules' ] );

		// Register default field group.
		add_action( 'hogan/include_field_groups', [ $this, 'register_default_field_group' ] );

		// Register all field groups when acf is ready.
		add_action( 'acf/include_fields', [ $this, 'include_field_groups' ] );

		// Add admin stylesheets and scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );

		// Enqueue Scripts.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_core_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_current_post_modules_assets' ] );

	}

	/**
	 * Init hooks and filter on after_setup_theme.
	 *
	 * @return void
	 */
	public function setup_on_after_setup_theme() {

		$this->_the_content_priority = absint( apply_filters( 'hogan/the_content_priority', 10 ) );

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

		add_filter( 'acf/fields/flexible_content/layout_title', [ $this, 'extend_layout_titles' ], 10, 3 );

	}

	/**
	 * Module constructor. Core is instantiated on plugin load.
	 *
	 * @return void
	 */
	private function __construct() {
		add_action( 'plugins_loaded', [ $this, 'setup_on_plugins_loaded' ] );
		add_action( 'after_setup_theme', [ $this, 'setup_on_after_setup_theme' ] );

		$this->assets_version = defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ? time() : HOGAN_CORE_VERSION;
	}

	/**
	 * Get Core instance.
	 *
	 * @return Core Core instance.
	 */
	public static function get_instance() : Core {

		if ( null === self::$_instance ) {
			self::$_instance = new Core();
		}

		return self::$_instance;
	}

	/**
	 * Load plugin admin assets.
	 *
	 * @return void
	 */
	public function enqueue_admin_assets() {
		wp_enqueue_style( 'hogan-admin-style', HOGAN_CORE_URL . 'assets/css/hogan-core-admin.css', [ 'acf-pro-input' ], $this->assets_version );
	}

	/**
	 * Load plugin core assets.
	 *
	 * @return void
	 */
	public function enqueue_core_assets() {
		wp_enqueue_style( 'hogan-core', HOGAN_CORE_URL . 'assets/css/hogan-core.css', [], $this->assets_version );

		/**
		 * Filters the content width of the modules
		 *
		 * @param int $content_width Width.
		 */
		$content_width = (int) apply_filters( 'hogan/frontend/content_width', 1360 );

		wp_add_inline_style( 'hogan-core', sprintf( '.hogan-module-inner { max-width: %spx; }', $content_width ) );
	}

	/**
	 * Register module
	 *
	 * @param \Dekode\Hogan\Module $module Module object.
	 * @return void
	 */
	public function register_module( \Dekode\Hogan\Module $module ) {

		if ( did_action( 'hogan/modules_included' ) ) {
			_doing_it_wrong( __METHOD__, esc_html__( 'Hogan modules have already been registered. Please do module registration on action hogan/include_modules.', 'hogan-core' ), '1.0.0' );
		}

		$this->_modules[ $module->name ] = $module;
	}

	/**
	 * Include all modules.
	 *
	 * @return void
	 */
	public function include_modules() {
		do_action( 'hogan/include_modules', $this );
		do_action( 'hogan/modules_included' );
	}

	/**
	 * Get module
	 *
	 * @param string $name Module name.
	 * @return Module|null
	 */
	public function get_module( string $name ) {
		return true === isset( $this->_modules[ $name ] ) ? $this->_modules[ $name ] : null;
	}

	/**
	 * Register field groups from filter into core plugin.
	 *
	 * @return void
	 */
	public function include_field_groups() {
		do_action( 'hogan/include_field_groups', $this );
		do_action( 'hogan/field_groups_included' );
	}

	/**
	 * Register a field group.
	 *
	 * @param array $args Field group arguments.
	 * @return void
	 */
	public function register_field_group( array $args ) {

		if ( ! did_action( 'hogan/modules_included' ) ) {
			_doing_it_wrong( __METHOD__, esc_html__( 'Hogan modules haven\'t been registered yet. Please do field group registration on action hogan/include_field_groups.', 'hogan-core' ), '1.0.0' );
		}

		if ( did_action( 'hogan/field_groups_included' ) ) {
			_doing_it_wrong( __METHOD__, esc_html__( 'Hogan field groups have already been registered. Please register/deregister field groups on action hogan/include_field_groups.', 'hogan-core' ), '1.0.0' );
		}

		$args = wp_parse_args( $args, [
			'name'                           => '',
			'title'                          => __( 'Content Modules', 'hogan-core' ),
			'modules'                        => [], // All modules.
			'location'                       => [],
			'hide_on_screen'                 => [],
			'fields_before_flexible_content' => [],
			'fields_after_flexible_content'  => [],
		] );

		// Sanitized field group name will be used for all filters, and prefix for field group and field names.
		$args['name'] = sanitize_key( $args['name'] );

		if ( empty( $args['name'] ) ) {
			// Everyone should have a name.
			return;
		}

		// Filter for disabling entire field group.
		if ( true !== apply_filters( 'hogan/field_group/' . $args['name'] . '/enabled', true ) ) {
			return; // Don't you want to play with me, Billy?
		}

		// Deprecated filters as of 1.3.0. To be removed as some point in the future.
		$args['fields_before_flexible_content'] = apply_filters_deprecated( 'hogan/field_group/' . $args['name'] . '/fields_before_flexible_content', [ $args['fields_before_flexible_content'] ], '1.3.0', 'hogan/field_group/' . $args['name'] . '/args' );
		$args['fields_after_flexible_content']  = apply_filters_deprecated( 'hogan/field_group/' . $args['name'] . '/fields_after_flexible_content', [ $args['fields_after_flexible_content'] ], '1.3.0', 'hogan/field_group/' . $args['name'] . '/args' );
		$args['location']                       = apply_filters_deprecated( 'hogan/field_group/' . $args['name'] . '/location', [ $args['location'] ], '1.3.0', 'hogan/field_group/' . $args['name'] . '/args' );
		$args['hide_on_screen']                 = apply_filters_deprecated( 'hogan/field_group/' . $args['name'] . '/hide_on_screen', [ $args['hide_on_screen'] ], '1.3.0', 'hogan/field_group/' . $args['name'] . '/args' );

		// Allow all args to be filtered before creating acf field group.
		$args = apply_filters( 'hogan/field_group/' . $args['name'] . '/args', $args );

		// Create a list of flexible content layouts based on selected modules.
		$field_group_layouts = [];

		foreach ( $this->_modules as $module ) {

			if ( is_array( $args['modules'] ) && ! empty( $args['modules'] ) ) {
				// Include a subset of modules.
				if ( in_array( $module->name, $args['modules'], true ) ) {
					$field_group_layouts[] = $module->get_layout_definition();
				}
			} else {
				// Include all modules modules.
				$field_group_layouts[] = $module->get_layout_definition();
			}
		}

		if ( empty( $field_group_layouts ) ) {
			return; // Why keep on living, what's the use?
		}

		// Set field group key based on name, i.e. hogan_default.
		$args['key'] = 'hogan_' . $args['name'];

		// Set field group fields from modules.
		$args['fields'] = array_merge( $args['fields_before_flexible_content'], [
			[
				'type'         => 'flexible_content',
				'key'          => 'hogan_' . $args['name'] . '_modules_key',
				'name'         => 'hogan_' . $args['name'] . '_modules_name',
				'button_label' => __( 'Add module', 'hogan-core' ),
				'layouts'      => $field_group_layouts,
			],
		], $args['fields_after_flexible_content'] );

		// Add to core field groups.
		$this->_field_groups[] = $args['name'];

		// Create ACF field group.
		acf_add_local_field_group( $args );
	}

	/**
	 * Register default field group for all modules.
	 *
	 * @return void
	 */
	public function register_default_field_group() {

		$location   = [];
		$post_types = apply_filters( 'hogan/field_group/default/supported_post_types', [ 'page' ] );

		// Include the deprecated global filter, to be removed at some point.
		$post_types = apply_filters_deprecated( 'hogan/supported_post_types', [ $post_types ], '1.3.0', 'hogan/field_group/default/supported_post_types' );

		if ( is_array( $post_types ) && ! empty( $post_types ) ) {
			// Add default location parameter for each supported post type.
			foreach ( $post_types as $post_type ) {
				$location[] = [
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => $post_type,
					],
				];
			}
		}

		// Register a field group with mostly default args.
		$this->register_field_group( [
			'name'           => 'default',
			'location'       => $location,
			'hide_on_screen' => [
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
			],
		] );
	}

	/**
	 * Check if current post is flexible.
	 *
	 * @param \WP_Post $post Current post.
	 * @param bool     $more More.
	 * @return bool
	 */
	private function is_current_post_flexible( $post, $more ) {
		return $post instanceof \WP_Post && function_exists( 'get_field' ) && ( $more || is_search() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) && ! post_password_required( $post );
	}

	/**
	 * Build an array of active layouts for current post.
	 *
	 * @param \WP_Post $post The post.
	 * @return array
	 */
	private function get_current_post_layouts( \WP_Post $post ) : array {

		$key = 'post-' . $post->ID;

		if ( ! array_key_exists( $key, $this->_current_layouts ) || empty( $this->_current_layouts[ $key ] ) ) {

			// Initial creation if key is does not exists.
			$this->_current_layouts[ $key ] = [];

			foreach ( $this->_field_groups as $field_group ) {

				// Get post Hogan layouts/content for field group.
				$layouts = get_field( 'hogan_' . $field_group . '_modules_name', $post );

				if ( is_array( $layouts ) && ! empty( $layouts ) ) {
					// Merge layouts/content from field group.
					$this->_current_layouts[ $key ] = array_merge( $this->_current_layouts[ $key ], $layouts );
				}
			}
		}

		return $this->_current_layouts[ $key ];
	}

	/**
	 * Enqueue module assets on current page.
	 */
	public function enqueue_current_post_modules_assets() {

		global $more, $post;

		if ( $this->is_current_post_flexible( $post, $more ) ) {
			$layouts = $this->get_current_post_layouts( $post );

			foreach ( $layouts as $layout ) {

				if ( ! isset( $layout['acf_fc_layout'] ) || empty( $layout['acf_fc_layout'] ) ) {
					continue;
				}

				// Enqueue assets.
				$this->enqueue_module_assets( $layout['acf_fc_layout'] );
			}
		}
	}

	/**
	 * Enqueue module assets.
	 *
	 * @param string $name Module name.
	 */
	public function enqueue_module_assets( $name ) {
		if ( ! in_array( $name, $this->_enqueued_modules, true ) ) {
			$module = $this->get_module( $name );

			if ( $module instanceof Module && method_exists( $module, 'enqueue_assets' ) ) {
				$this->_enqueued_modules[] = $name;
				$module->enqueue_assets();
			}
		}
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

		if ( $this->is_current_post_flexible( $post, $more ) ) {
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
		$flexible_content = $this->_flexible_content[ $post->ID ] ?? null;

		if ( empty( $flexible_content ) ) {
			$layouts = $this->get_current_post_layouts( $post );

			foreach ( $layouts as $layout ) {

				if ( ! isset( $layout['acf_fc_layout'] ) || empty( $layout['acf_fc_layout'] ) ) {
					continue;
				}

				ob_start();
				$this->render_module_template( $layout['acf_fc_layout'], $layout );
				$flexible_content .= ob_get_clean();
			}

			/*
			 * Store the flexible content to reuse it if `the_content` is
			 * runned more than once.
			 */
			$this->_flexible_content[ $post->ID ] = $flexible_content;
		}

		return (string) $flexible_content;
	}

	/**
	 * Render template.
	 *
	 * @param string $name Module name.
	 * @param array  $args Module arguments.
	 */
	public function render_module_template( string $name, array $args ) {
		if ( ! apply_filters( 'hogan/module/' . $name . '/show_in_rest', true ) && \hogan_is_rest() ) {
			return;
		}

		// Get the right module.
		$module = $this->get_module( $name );

		if ( $module instanceof Module ) {
			$module->render_template( $args, $this->_module_counter );
			$this->_module_counter++;
		}
	}

	/**
	 * Populate the post_content with modules HTML before SearchWP indexing.
	 *
	 * @param \WP_Post $post The post.
	 * @return \WP_Post
	 */
	public function populate_post_content_for_indexing( \WP_Post $post ) : \WP_Post {

		$module_content = $this->get_modules_content( $post );

		if ( ! empty( $module_content ) ) {
			// Fake fill the post_content with modules before SearchWP indexing.
			$post->post_content = $module_content;
		}

		return $post;
	}

	/**
	 * Add custom toolbars
	 *
	 * @param array $toolbars Current Toolbars.
	 * @return array $toolbars Array with new toolbars.
	 */
	public function append_hogan_wysiwyg_toolbar( array $toolbars ) : array {

		$toolbars['hogan'] = [
			1 => apply_filters( 'hogan/tinymce/toolbar/hogan', [
				'formatselect',
				'bold',
				'italic',
				'blockquote_cite',
				'numlist',
				'bullist',
				'undo',
				'redo',
				'link',
				'unlink',
				'pastetext',
				'removeformat',
				'code',
			] ),
		];

		$toolbars['hogan_caption'] = [
			1 => apply_filters( 'hogan/tinymce/toolbar/hogan_caption', [
				'bold',
				'italic',
				'link',
				'unlink',
			] ),
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
					$('.acf-flexible-content .layout:not([data-layout="text"]):not(.acf-clone)').addClass('-collapsed');
				});
			} )( jQuery );
		</script>
		<?php
	}

	/**
	 * Extend layout titles.
	 *
	 * @param string $title Layout title.
	 * @param array  $field Current field.
	 * @param array  $layout Current layout title.
	 */
	public function extend_layout_titles( $title, $field, $layout ) : string {

		if ( ! empty( get_sub_field( 'heading' ) ) ) {
			$title .= ': ' . get_sub_field( 'heading' );
		}

		return apply_filters( 'hogan/extend_layout_title', $title );
	}
}
