<?php

function gutenberg_get_family_indexes_from_theme_json( $font_families ) {
	$font_families_by_index = array();

	foreach ( $font_families as $index => $font_family ) {
		$font_families_by_index[ WP_Webfonts::get_font_slug( $font_family ) ] = $index;
	}

	return $font_families_by_index;
}

function gutenberg_transform_font_face_to_camel_case( $font_face ) {
	$camel_cased = array();

	foreach ( $font_face as $key => $value ) {
		$camel_cased[ lcfirst( str_replace( '-', '', ucwords( $key, '-' ) ) ) ] = $value;
	}

	return $camel_cased;
}

function gutenberg_transform_font_face_to_kebab_case( $font_face ) {
	$new_face = array();

	foreach ( $font_face as $key => $value ) {
		$new_face[ _wp_to_kebab_case( $key ) ] = $value;
	}

	return $new_face;
}

function gutenberg_transform_font_faces_to_theme_json_format( $font_faces ) {
	$transformed_font_faces = array();

	foreach ( $font_faces as $font_face ) {
		$transformed_font_faces[] = array_merge(
			array(
				'origin'                           => 'gutenberg_wp_webfonts_api',
				'dynamicallyIncludedIntoThemeJSON' => true,
			),
			gutenberg_transform_font_face_to_camel_case( $font_face )
		);
	}

	return $transformed_font_faces;
}

function gutenberg_transform_font_family_to_theme_json_format( $slug, $font_faces ) {
	$family_name = $font_faces[0]['font-family'];

	$providers_for_family = array();

	foreach ( $font_faces as $font_face ) {
		$providers_for_family[] = $font_face['provider'];
	}

	$providers_for_family = array_unique( $providers_for_family );

	if ( 1 === count( $providers_for_family ) ) {
		foreach ( $font_faces as $index => $font_face ) {
			unset( $font_faces[ $index ]['provider'] );
		}
	}

	$font_family_in_theme_json_format = array(
		'origin'                           => 'gutenberg_wp_webfonts_api',
		'dynamicallyIncludedIntoThemeJSON' => true,
		'fontFamily'                       => str_contains( $family_name, ' ' ) ? "'{$family_name}'" : $family_name,
		'name'                             => $family_name,
		'slug'                             => $slug,
		'fontFaces'                        => gutenberg_transform_font_faces_to_theme_json_format( $font_faces ),
	);

	if ( 1 === count( $providers_for_family ) ) {
		$font_family_in_theme_json_format['provider'] = $providers_for_family[0];
	}

	return $font_family_in_theme_json_format;
}

function gutenberg_is_webfont_equal( $a, $b ) {
	$equality_attrs = array(
		'font-family',
		'font-style',
		'font-weight',
	);

	foreach ( $equality_attrs as $attr ) {
		if ( $a[ $attr ] !== $b[ $attr ] ) {
			return false;
		}
	}

	return true;
}

function gutenberg_find_webfont( $webfonts, $webfont_to_find ) {
	foreach ( $webfonts as $index => $webfont ) {
		if ( gutenberg_is_webfont_equal( $webfont, $webfont_to_find ) ) {
			return $index;
		}
	}

	return false;
}

/**
 * Add missing fonts data to the global styles.
 *
 * @param array $data The global styles.
 * @return array The global styles with missing fonts data.
 */
function gutenberg_add_programmatically_registered_webfonts_to_theme_json( $data ) {
	$programmatically_registered_font_families = wp_webfonts()->get_registered_webfonts();

	// Make sure the path to settings.typography.fontFamilies.theme exists
	// before adding missing fonts.
	if ( empty( $data['settings'] ) ) {
		$data['settings'] = array();
	}
	if ( empty( $data['settings']['typography'] ) ) {
		$data['settings']['typography'] = array();
	}
	if ( empty( $data['settings']['typography']['fontFamilies'] ) ) {
		$data['settings']['typography']['fontFamilies'] = array();
	}

	$font_family_indexes_in_theme_json = gutenberg_get_family_indexes_from_theme_json( $data['settings']['typography']['fontFamilies'] );

	foreach ( $programmatically_registered_font_families as $slug => $programmatically_registered_font_faces ) {
		// This programmatically registered font family does not exist in theme.json, so let's add it, specifying its origin.
		if ( ! isset( $font_family_indexes_in_theme_json[ $slug ] ) ) {
			$data['settings']['typography']['fontFamilies'][] = gutenberg_transform_font_family_to_theme_json_format(
				$slug,
				$programmatically_registered_font_faces
			);

			continue;
		}

		// We know that the programmatically registered font family exists in theme.json at this point.

		$font_family_index_in_theme_json = $font_family_indexes_in_theme_json[ $slug ];
		$font_family_in_theme_json       = $data['settings']['typography']['fontFamilies'][ $font_family_index_in_theme_json ];

		/**
		 * The theme.json entry is specifying a provider at the top level, so that means it'll try to register the whole family later.
		 * Let's make theme.json take precedence over the API as the source of truth,
		 * and unregister the programmatically registered font family.
		 */
		if ( isset( $font_family_in_theme_json['provider'] ) ) {
			wp_webfonts()->unregister_font_family_by_slug( $slug );
			continue;
		}

		/**
		 * The entry in `theme.json` is not registering any font faces, so let's add them so them shows up in the editor.
		 */
		if ( ! isset( $font_family_in_theme_json['fontFaces'] ) ) {
			$data['settings']['typography']['fontFamilies'][ $font_family_index_in_theme_json ]['fontFaces'] = gutenberg_transform_font_faces_to_theme_json_format( $programmatically_registered_font_faces );
			continue;
		}

		/**
		 * There are font faces being registered, so let's add only the ones that are missing
		 */

		$font_faces_to_add_to_theme_json = $programmatically_registered_font_faces;

		/**
		 * Let's un-register from the API the font faces that theme.json is going to register.
		 * Remember: theme.json is the source of truth here.
		 */
		foreach ( $font_family_in_theme_json['fontFaces'] as $index => $font_face_in_theme_json ) {
			$font_face_to_register_index = gutenberg_find_webfont( $font_faces_to_add_to_theme_json, gutenberg_transform_font_face_to_kebab_case( $font_face_in_theme_json ) );

			if ( isset( $font_face_in_theme_json['provider'] ) ) {
				// It'll register the font face in theme.json, and we don't want to re-register it,
				// so lets remove it from the API registry.
				if ( false !== $font_face_to_register_index ) {
					wp_webfonts()->unregister_font_face_by_index( $slug, $font_face_to_register_index );
					unset( $font_faces_to_add_to_theme_json[ $font_face_to_register_index ] );
				}

				continue;
			}

			// If listed in theme.json, but found in programmatically registered font faces, let's signal it.
			if ( false !== $font_face_to_register_index ) {
				$data['settings']['typography']['fontFamilies'][ $font_family_index_in_theme_json ]['fontFaces'][ $index ]           = $font_faces_to_add_to_theme_json[ $font_face_to_register_index ];
				$data['settings']['typography']['fontFamilies'][ $font_family_index_in_theme_json ]['fontFaces'][ $index ]['origin'] = 'gutenberg_wp_webfonts_api';
				// And remove from the list of font faces to add to the family in theme.json.
				unset( $font_faces_to_add_to_theme_json[ $font_face_to_register_index ] );
				continue;
			}

			trigger_error(
				sprintf(
					'The %s:%s:%s font face specified in theme.json is not registered programmatically, nor through theme.json.',
					$font_face_in_theme_json['fontFamily'],
					$font_face_in_theme_json['fontStyle'],
					$font_face_in_theme_json['fontWeight'],
				)
			);
		}

		/**
		 * Finally, let's add the remaining programmatically registered font faces to the respective font family entry.
		 */

		foreach ( $font_faces_to_add_to_theme_json as $font_face_to_add_to_theme_json ) {
			$data['settings']['typography']['fontFamilies'][ $font_family_index_in_theme_json ]['fontFaces'][] = array_merge(
				array(
					'origin'                           => 'gutenberg_wp_webfonts_api',
					'dynamicallyIncludedIntoThemeJSON' => true,
				),
				gutenberg_transform_font_face_to_camel_case( $font_face_to_add_to_theme_json )
			);
		}
	}

	return $data;
}
