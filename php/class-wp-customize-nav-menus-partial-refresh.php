<?php
/**
 * WP_Customize_Nav_Menus_Partial_Refresh class.
 *
 * @package WordPress
 */

/**
 * This class is a replacement for the rendering methods in WP_Customize_Nav_Menus.
 *
 * This will be integrated into WP_Customize_Nav_Menus during core merge.
 */
class WP_Customize_Nav_Menus_Partial_Refresh {

	/**
	 * Plugin instance.
	 *
	 * This will not be included in core merge.
	 *
	 * @access public
	 * @var WP_Customize_Partial_Refresh_Plugin
	 */
	public $plugin;

	/**
	 * WP_Customize_Nav_Menu_Selective_Refresh constructor.
	 *
	 * This will not be included in core merge.
	 *
	 * @param WP_Customize_Partial_Refresh_Plugin $plugin  Plugin instance.
	 */
	function __construct( WP_Customize_Partial_Refresh_Plugin $plugin ) {
		$this->plugin = $plugin;

		add_action( 'customize_register', array( $this, 'override_core_nav_menu_refresh' ) );
	}

	/**
	 * Disable nav menu partial refresh as bundled in Core.
	 *
	 * This will not be included in core merge.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 */
	public function override_core_nav_menu_refresh( $wp_customize ) {
		if ( ! empty( $wp_customize->nav_menus ) ) {
			remove_action( 'customize_preview_init', array( $wp_customize->nav_menus, 'customize_preview_init' ) );
			add_action( 'customize_preview_init', array( $this, 'customize_preview_init' ) );
			add_filter( 'customize_dynamic_partial_args', array( $this, 'customize_dynamic_partial_args' ), 10, 2 );
		}
	}

	/**
	 * The following lines can replace the corresponding code in WP_Customize_Nav_Menus.
	 *
	 * @link https://github.com/xwp/wordpress-develop/blob/7bc7bd07d4a07544b567ce25f099b0bd658e8560/src/wp-includes/class-wp-customize-nav-menus.php#L804-L1016
	 */

	/**
	 * The number of wp_nav_menu() calls which have happened in the preview.
	 *
	 * @since 4.3.0
	 * @access public
	 * @var int
	 */
	public $preview_nav_menu_instance_number = 0;

	/**
	 * Nav menu args used for each instance.
	 *
	 * @since 4.3.0
	 * @access public
	 * @var array
	 */
	public $preview_nav_menu_instance_args = array();

	/**
	 * Filter args for nav_menu partials.
	 *
	 * @since 4.5.0
	 *
	 * @param array|false $partial_args Partial args.
	 * @param string      $partial_id  Partial ID.
	 * @return array Partial args
	 */
	public function customize_dynamic_partial_args( $partial_args, $partial_id ) {

		if ( preg_match( '/^nav_menu_placement\[\d+\]$/', $partial_id ) ) {
			if ( false === $partial_args ) {
				$partial_args = array();
			}
			$partial_args = array_merge(
				$partial_args,
				array(
					'type' => 'nav_menu_placement',
					'render_callback' => array( $this, 'render_nav_menu_partial' ),
				)
			);
		}

		return $partial_args;
	}

	/**
	 * Add hooks for the Customizer preview.
	 *
	 * @since 4.3.0
	 * @access public
	 */
	public function customize_preview_init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'customize_preview_enqueue_deps' ) );
		add_filter( 'wp_nav_menu_args', array( $this, 'filter_wp_nav_menu_args' ), 1000 );
		add_filter( 'wp_nav_menu', array( $this, 'filter_wp_nav_menu' ), 10, 2 );
	}

	/**
	 * Whether the wp_nav_menu preview filters should be suspended.
	 *
	 * @see WP_Customize_Nav_Menu_Selective_Refresh::filter_wp_nav_menu_args()
	 * @see WP_Customize_Nav_Menu_Selective_Refresh::filter_wp_nav_menu
	 *
	 * @since 4.5.0
	 * @access private
	 * @var bool
	 */
	protected $suspended_wp_nav_menu_filters = false;

	/**
	 * Keep track of the arguments that are being passed to wp_nav_menu().
	 *
	 * @since 4.3.0
	 * @access public
	 * @see wp_nav_menu()
	 *
	 * @param array $args An array containing wp_nav_menu() arguments.
	 * @return array Arguments.
	 */
	public function filter_wp_nav_menu_args( $args ) {
		if ( $this->suspended_wp_nav_menu_filters ) {
			return $args;
		}

		$can_partial_refresh = (
			! empty( $args['echo'] )
			&&
			( empty( $args['fallback_cb'] ) || is_string( $args['fallback_cb'] ) )
			&&
			( empty( $args['walker'] ) || is_string( $args['walker'] ) )
			&&
			(
				! empty( $args['theme_location'] )
				||
				( ! empty( $args['menu'] ) && ( is_numeric( $args['menu'] ) || is_object( $args['menu'] ) ) )
			)
			&&
			(
				! empty( $args['container'] )
				||
				'<' === substr( $args['items_wrap'], 0, 1 )
			)
		);
		if ( ! $can_partial_refresh ) {
			return $args;
		}

		$exported_args = $args;

		$this->preview_nav_menu_instance_number += 1;
		$args['instance_number'] = $this->preview_nav_menu_instance_number;

		// Replace object menu arg with a term_id menu arg, as this exports better to JS and is easier to compare hashes.
		if ( ! empty( $exported_args['menu'] ) && is_object( $exported_args['menu'] ) ) {
			$exported_args['menu'] = $exported_args['menu']->term_id;
		}

		ksort( $exported_args );
		$exported_args['args_hmac'] = $this->hash_nav_menu_args( $exported_args );

		$this->preview_nav_menu_instance_args[ $this->preview_nav_menu_instance_number ] = $exported_args;

		return $args;
	}

	/**
	 * Prepare wp_nav_menu() calls for partial refresh. Injects attributes into container element.
	 *
	 * @since 4.3.0
	 * @access public
	 *
	 * @see wp_nav_menu()
	 *
	 * @param string $nav_menu_content The HTML content for the navigation menu.
	 * @param object $args             An object containing wp_nav_menu() arguments.
	 * @return null
	 */
	public function filter_wp_nav_menu( $nav_menu_content, $args ) {
		if ( $this->suspended_wp_nav_menu_filters ) {
			return $nav_menu_content;
		}

		if ( ! empty( $args->instance_number ) ) {
			$nav_menu_content = preg_replace(
				'#^(<\w+)#',
				sprintf(
					'$1 data-customize-nav-menu-args="%s"',
					esc_attr( wp_json_encode( $this->preview_nav_menu_instance_args[ $args->instance_number ] ) )
				),
				$nav_menu_content,
				1 // Only replace first.
			);
		}
		return $nav_menu_content;
	}

	/**
	 * Hash (hmac) the arguments with the nonce and secret auth key to ensure they
	 * are not tampered with when submitted in the Ajax request.
	 *
	 * @since 4.3.0
	 * @access public
	 *
	 * @param array $args The arguments to hash.
	 * @return string
	 */
	public function hash_nav_menu_args( $args ) {
		return wp_hash( serialize( $args ) );
	}

	/**
	 * Enqueue scripts for the Customizer preview.
	 *
	 * @since 4.3.0
	 * @access public
	 */
	public function customize_preview_enqueue_deps() {
		wp_enqueue_script( 'customize-preview-nav-menus' ); // Note that we have overridden this.
		wp_enqueue_style( 'customize-preview' );
	}

	/**
	 * Export data from PHP to JS.
	 *
	 * @deprecated
	 * @since 4.3.0
	 * @since 4.5.0 Obsolete.
	 * @access public
	 */
	public function export_preview_data() {
		_deprecated_function( __METHOD__, '4.5.0' );
	}

	/**
	 * Render a specific menu via wp_nav_menu() using the supplied arguments.
	 *
	 * @since 4.3.0
	 * @access public
	 *
	 * @see wp_nav_menu()
	 *
	 * @param WP_Customize_Partial $partial       Partial.
	 * @param array                $nav_menu_args Nav menu args supplied as container context.
	 * @return string|false
	 */
	public function render_nav_menu_partial( $partial, $nav_menu_args ) {
		unset( $partial );

		if ( ! isset( $nav_menu_args['args_hmac'] ) ) {
			return false; // Error: missing_args_hmac.
		}
		$nav_menu_args_hmac = $nav_menu_args['args_hmac'];
		unset( $nav_menu_args['args_hmac'] );

		if ( ! hash_equals( $this->hash_nav_menu_args( $nav_menu_args ), $nav_menu_args_hmac ) ) {
			return false; // Error: args_hmac_mismatch.
		}

		$nav_menu_args['echo'] = false;

		$this->suspended_wp_nav_menu_filters = true;
		$container = wp_nav_menu( $nav_menu_args );
		$this->suspended_wp_nav_menu_filters = false;

		/*
		 * Unwrap the container to return the contents. This could be done
		 * automatically on the JS side, by checking if the returned value
		 * contains a root element that has the same element name, ID and/or classes.
		 */
		$content = preg_replace( '#^\s*<(\w+)[^>]*?>(.+)</\1>\s*$#s', '$2', $container );

		return $content;
	}
}