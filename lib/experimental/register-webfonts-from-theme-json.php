<?php
/**
 * Bootstraps Global Styles.
 *
 * @package gutenberg
 */

/**
 * Register webfonts defined in theme.json.
 */
function gutenberg_register_webfonts_from_theme_json() {
	// Get settings from theme.json.
	$theme_settings = WP_Theme_JSON_Resolver_Gutenberg::get_theme_data()->get_settings();

	// Bail out early if there are no settings for webfonts.
	if ( empty( $theme_settings['typography'] ) || empty( $theme_settings['typography']['fontFamilies'] ) ) {
		return;
	}

	$webfonts = array();

	// Look for fontFamilies.
	foreach ( $theme_settings['typography']['fontFamilies'] as $preset_font_families ) {
		foreach ( $preset_font_families as $font_family ) {
			// The whole font family is already registered programmatically, so we can skip it.
			if ( isset( $font_family['origin'] ) && 'gutenberg_wp_webfonts_api' === $font_family['origin'] ) {
				continue;
			}

			// If a provider is set on the root level, they're trying to register
			// a whole font family to the same provider. Let's do it!
			if ( isset( $font_family['provider'] ) ) {
				if ( empty( $font_family['fontFaces'] ) ) {
					trigger_error(
						sprint_r( 'The %s font family was going to be registered, but no font faces where declared.', $font_family['fontFamily'] )
					);
					continue;
				}

				wp_register_webfonts(
					array_map(
						function( $font_face ) use ( $font_family ) {
							$font_face['provider'] = $font_family['provider'];
						},
						$font_family['fontFaces']
					)
				);

				continue;
			}

			// Skip if fontFaces are not defined.
			if ( empty( $font_family['fontFaces'] ) ) {
				continue;
			}

			$font_family['fontFaces'] = (array) $font_family['fontFaces'];

			foreach ( $font_family['fontFaces'] as $font_face ) {
				// Skip if the webfont was registered through the Webfonts API.
				if ( isset( $font_face['origin'] ) && 'gutenberg_wp_webfonts_api' === $font_face['origin'] ) {
					continue;
				}

				// Skip if there is no provider key in the font face.
				if ( ! isset( $font_face['provider'] ) ) {
					continue;
				}

				// Check if webfonts have a "src" param, and if they do account for the use of "file:./".
				if ( ! empty( $font_face['src'] ) ) {
					$font_face['src'] = (array) $font_face['src'];

					foreach ( $font_face['src'] as $src_key => $url ) {
						// Tweak the URL to be relative to the theme root.
						if ( ! str_starts_with( $url, 'file:./' ) ) {
							continue;
						}
						$font_face['src'][ $src_key ] = get_theme_file_uri( str_replace( 'file:./', '', $url ) );
					}
				}

				// Convert keys to kebab-case.
				foreach ( $font_face as $property => $value ) {
					$kebab_case               = _wp_to_kebab_case( $property );
					$font_face[ $kebab_case ] = $value;
					if ( $kebab_case !== $property ) {
						unset( $font_face[ $property ] );
					}
				}

				$webfonts[] = $font_face;
			}
		}
	}

	foreach ( $webfonts as $webfont ) {
		wp_webfonts()->register_webfont( $webfont );
	}
}

add_action( 'init', 'gutenberg_register_webfonts_from_theme_json' );
