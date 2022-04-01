<?php

function gutenberg_enqueue_webfonts_from_theme_json() {
	$theme_settings = WP_Theme_JSON_Resolver_Gutenberg::get_theme_data()->get_settings();

	// Bail out early if there are no settings for webfonts.
	if ( empty( $theme_settings['typography'] ) || empty( $theme_settings['typography']['fontFamilies'] ) ) {
		return;
	}

	// Look for fontFamilies.
	foreach ( $theme_settings['typography']['fontFamilies'] as $font_families ) {
		foreach ( $font_families as $font_family ) {
			// Skip dynamically included font families. We only want to enqueue explicitly added fonts.
			if ( isset( $font_family['dynamicallyIncludedIntoThemeJSON'] ) && true === $font_family['dynamicallyIncludedIntoThemeJSON'] ) {
				continue;
			}

			// If no font faces defined.
			if ( ! isset( $font_family['fontFaces'] ) ) {
				// And the font family is registered.
				if ( ! wp_webfonts()->is_font_family_registered( $font_family['fontFamily'] ) ) {
					continue;
				}

				// And it was explicitly declared by the developer in theme.json.
				if ( isset( $font_family['dynamicallyIncludedIntoThemeJSON'] ) ) {
					continue;
				}

				// Enqueue the entire family.
				wp_webfonts()->enqueue_webfont( $font_family );
				continue;
			}

			// Loop through all the font faces, enqueueing each one of them.
			foreach ( $font_family['fontFaces'] as $font_face ) {
				wp_webfonts()->enqueue_webfont( $font_family, $font_face );
			}
		}
	}
}

add_filter( 'wp_loaded', 'gutenberg_enqueue_webfonts_from_theme_json' );

// No need to run this -- opening the admin interface enqueues all the webfonts.
add_action(
	'admin_init',
	function() {
		remove_filter( 'wp_loaded', 'gutenberg_enqueue_webfonts_from_theme_json' );
	}
);
