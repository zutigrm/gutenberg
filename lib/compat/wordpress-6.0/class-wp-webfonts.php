<?php
/**
 * Webfonts API class.
 *
 * @package Gutenberg
 */

/**
 * Class WP_Webfonts
 */
class WP_Webfonts {

	/**
	 * An array of registered webfonts.
	 *
	 * @access private
	 * @var WP_Webfont_Registry
	 */
	private $registered_webfonts;

	/**
	 * An array of enqueued webfonts.
	 *
	 * @access private
	 * @var WP_Webfont_Registry
	 */
	private $enqueued_webfonts;

	/**
	 * An array of registered providers.
	 *
	 * @access private
	 * @var array
	 */
	private $providers = array();

	/**
	 * Stylesheet handle.
	 *
	 * @var string
	 */
	private $stylesheet_handle = '';

	/**
	 * Init.
	 */
	public function init() {
		$this->registered_webfonts = new WP_Webfont_Registry();
		$this->enqueued_webfonts   = new WP_Webfont_Registry();

		// Register default providers.
		$this->register_provider( 'local', 'WP_Webfonts_Provider_Local' );

		// Register callback to generate and enqueue styles.
		if ( did_action( 'wp_enqueue_scripts' ) ) {
			$this->stylesheet_handle = 'webfonts-footer';
			$hook                    = 'wp_print_footer_scripts';
		} else {
			$this->stylesheet_handle = 'webfonts';
			$hook                    = 'wp_enqueue_scripts';
		}
		add_action( $hook, array( $this, 'generate_and_enqueue_styles' ) );

		add_action( 'wp_loaded', array( $this, 'collect_fonts_from_global_styles' ) );
		add_filter( 'pre_render_block', array( $this, 'collect_fonts_from_block' ), 10, 2 );

		// We are already enqueueing all registered fonts by default when loading the block editor.
		// So we need to bail out of block and global styles webfont scanning.
		add_action( 'admin_init', array( $this, 'remove_webfont_scanning_hooks' ) );

		// Enqueue webfonts in the block editor.
		add_action( 'admin_init', array( $this, 'generate_and_enqueue_editor_styles' ) );
	}

	public function collect_fonts_from_global_styles() {
		$global_styles = gutenberg_get_global_styles();

		if ( isset( $global_styles['blocks'] ) ) {
			// Register used fonts from blocks.
			foreach ( $global_styles['blocks'] as $setting ) {
				$font_slug = $this->get_font_slug_from_setting( $setting );

				if ( $font_slug ) {
					$this->enqueue_font_family_by_slug( $font_slug );
				}
			}
		}

		if ( isset( $global_styles['elements'] ) ) {
			// Register used fonts from elements.
			foreach ( $global_styles['elements'] as $setting ) {
				$this->get_font_slug_from_setting( $setting );
			}
		}

		$has_global_typography_setting = isset( $global_styles['typography'] ) && isset( $global_styles['typography']['fontFamily'] );

		if ( ! $has_global_typography_setting ) {
			return;
		}

		$font_family_custom = $global_styles['typography']['fontFamily'];
		$index_to_splice    = strrpos( $font_family_custom, '|' ) + 1;
		$font_family_slug   = substr( $font_family_custom, $index_to_splice );
		$this->enqueue_font_family_by_slug( $font_family_slug );
	}

	private function get_font_slug_from_setting( $setting ) {
		if ( isset( $setting['typography'] ) && isset( $setting['typography']['fontFamily'] ) ) {
			$font_family = $setting['typography']['fontFamily'];

			// Full string: var(--wp--preset--font-family--slug).
			// We do not care about the origin of the font, only its slug.
			preg_match( '/font-family--(?P<slug>.+)\)$/', $font_family, $matches );

			if ( isset( $matches['slug'] ) ) {
				return $matches['slug'];
			}
		}
	}

	public function remove_webfont_scanning_hooks() {
		remove_action( 'wp_loaded', array( $this, 'collect_fonts_from_global_styles' ) );
		remove_filter( 'pre_render_block', array( $this, 'collect_fonts_from_block' ) );
	}

	public function collect_fonts_from_block( $content, $parsed_block ) {
		if ( isset( $parsed_block['attrs']['fontFamily'] ) ) {
			$this->enqueue_font_family_by_slug( $parsed_block['attrs']['fontFamily'] );
		}

		return $content;
	}

	/**
	 * Get the list of registered fonts.
	 *
	 * @return array
	 */
	public function get_registered_webfonts() {
			return $this->registered_webfonts->get_font_families();
	}

	/**
	 * Get the list of enqueued fonts.
	 *
	 * @return array
	 */
	public function get_enqueued_webfonts() {
		return $this->enqueued_webfonts->get_font_families();
	}

	/**
	 * Get the list of all fonts.
	 *
	 * @return array
	 */
	public function get_all_webfonts() {
		return array_merge( $this->get_registered_webfonts(), $this->get_enqueued_webfonts() );
	}

	/**
	 * Get the list of providers.
	 *
	 * @return array
	 */
	public function get_providers() {
		return $this->providers;
	}

	/**
	 * Register a webfont.
	 *
	 * @param array $raw_font The font arguments.
	 */
	public function register_font( $raw_font ) {
		$font = new WP_Webfont( $raw_font );

		if ( ! $font ) {
			return false;
		}

		$this->registered_webfonts->register_font_face( $font );
	}

	private function enqueue_font_family_by_slug( $slug ) {
		if ( $this->enqueued_webfonts->has_font_family_in_registry( $slug ) ) {
			return new WP_Error( 'webfont_already_enqueued', sprintf( __( 'The "%s" font family is already enqueued.' ), $slug ) );
		}

		if ( ! $this->registered_webfonts->has_font_family_in_registry( $slug ) ) {
			return new WP_Error( 'webfont_not_registered', sprintf( __( 'The "%s" font family is not registered.' ), $slug ) );
		}

		$this->enqueue_font( $slug );
	}

	public function enqueue_font( $font_family_slug ) {
		$font_family = $this->registered_webfonts->unregister_family( $font_family_slug );

		error_log( print_r( $font_family, true) );
		$this->enqueued_webfonts->register_font_family( $font_family );
	}

	/**
	 * Register a provider.
	 *
	 * @param string $provider The provider name.
	 * @param string $class    The provider class name.
	 *
	 * @return bool Whether the provider was registered successfully.
	 */
	public function register_provider( $provider, $class ) {
		if ( empty( $provider ) || empty( $class ) ) {
			return false;
		}
		$this->providers[ $provider ] = $class;
		return true;
	}

	/**
	 * Generate and enqueue webfonts styles.
	 */
	public function generate_and_enqueue_styles() {
		// Generate the styles.
		$styles = $this->generate_styles( $this->get_enqueued_webfonts() );

		// Bail out if there are no styles to enqueue.
		if ( '' === $styles ) {
			return;
		}

		// Enqueue the stylesheet.
		wp_register_style( $this->stylesheet_handle, '' );
		wp_enqueue_style( $this->stylesheet_handle );

		// Add the styles to the stylesheet.
		wp_add_inline_style( $this->stylesheet_handle, $styles );
	}

	/**
	 * Generate and enqueue editor styles.
	 */
	public function generate_and_enqueue_editor_styles() {
		// Generate the styles.
		$styles = $this->generate_styles( $this->get_all_webfonts() );

		// Bail out if there are no styles to enqueue.
		if ( '' === $styles ) {
			return;
		}

		wp_add_inline_style( 'wp-block-library', $styles );
	}

	/**
	 * Generate styles for webfonts.
	 *
	 * @since 6.0.0
	 *
	 * @return string $styles Generated styles.
	 */
	public function generate_styles( $font_families ) {
		$styles    = '';
		$providers = $this->get_providers();

		$webfonts = array();

		// Grab only the font face declarations from $font_families.
		foreach ( $font_families as $font_family ) {
			foreach ( $font_family as $font_face ) {
				$webfonts[] = $font_face;
			}
		}

		// Group webfonts by provider.
		$webfonts_by_provider = array();
		foreach ( $webfonts as $slug => $webfont ) {
			$provider = $webfont->get_font()['provider'];
			if ( ! isset( $providers[ $provider ] ) ) {
				/* translators: %s is the provider name. */
				error_log( sprintf( __( 'Webfont provider "%s" is not registered.', 'gutenberg' ), $provider ) );
				continue;
			}
			$webfonts_by_provider[ $provider ]          = isset( $webfonts_by_provider[ $provider ] ) ? $webfonts_by_provider[ $provider ] : array();
			$webfonts_by_provider[ $provider ][ $slug ] = $webfont;
		}

		/*
		 * Loop through each of the providers to get the CSS for their respective webfonts
		 * to incrementally generate the collective styles for all of them.
		 */
		foreach ( $providers as $provider_id => $provider_class ) {

			// Bail out if the provider class does not exist.
			if ( ! class_exists( $provider_class ) ) {
				/* translators: %s is the provider name. */
				error_log( sprintf( __( 'Webfont provider "%s" is not registered.', 'gutenberg' ), $provider_id ) );
				continue;
			}

			$provider_webfonts = isset( $webfonts_by_provider[ $provider_id ] )
				? $webfonts_by_provider[ $provider_id ]
				: array();

			// If there are no registered webfonts for this provider, skip it.
			if ( empty( $provider_webfonts ) ) {
				continue;
			}

			/*
			 * Process the webfonts by first passing them to the provider via `set_webfonts()`
			 * and then getting the CSS from the provider.
			 */
			$provider = new $provider_class();
			$provider->set_webfonts( $provider_webfonts );
			$styles .= $provider->get_css();
		}

		return $styles;
	}
}
