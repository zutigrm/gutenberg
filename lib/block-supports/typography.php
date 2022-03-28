<?php
/**
 * Typography block support flag.
 *
 * @package gutenberg
 */

/**
 * Registers the style and typography block attributes for block types that support it.
 *
 * @param WP_Block_Type $block_type Block Type.
 */
function gutenberg_register_typography_support( $block_type ) {
	if ( ! property_exists( $block_type, 'supports' ) ) {
		return;
	}

	$typography_supports = _wp_array_get( $block_type->supports, array( 'typography' ), false );
	if ( ! $typography_supports ) {
		return;
	}

	$has_font_family_support     = _wp_array_get( $typography_supports, array( '__experimentalFontFamily' ), false );
	$has_font_size_support       = _wp_array_get( $typography_supports, array( 'fontSize' ), false );
	$has_font_style_support      = _wp_array_get( $typography_supports, array( '__experimentalFontStyle' ), false );
	$has_font_weight_support     = _wp_array_get( $typography_supports, array( '__experimentalFontWeight' ), false );
	$has_letter_spacing_support  = _wp_array_get( $typography_supports, array( '__experimentalLetterSpacing' ), false );
	$has_line_height_support     = _wp_array_get( $typography_supports, array( 'lineHeight' ), false );
	$has_text_decoration_support = _wp_array_get( $typography_supports, array( '__experimentalTextDecoration' ), false );
	$has_text_transform_support  = _wp_array_get( $typography_supports, array( '__experimentalTextTransform' ), false );

	$has_typography_support = $has_font_family_support
		|| $has_font_size_support
		|| $has_font_style_support
		|| $has_font_weight_support
		|| $has_letter_spacing_support
		|| $has_line_height_support
		|| $has_text_decoration_support
		|| $has_text_transform_support;

	if ( ! $block_type->attributes ) {
		$block_type->attributes = array();
	}

	if ( $has_typography_support && ! array_key_exists( 'style', $block_type->attributes ) ) {
		$block_type->attributes['style'] = array(
			'type' => 'object',
		);
	}

	if ( $has_font_size_support && ! array_key_exists( 'fontSize', $block_type->attributes ) ) {
		$block_type->attributes['fontSize'] = array(
			'type' => 'string',
		);
	}
}

/**
 * Add CSS classes and inline styles for typography features such as font sizes
 * to the incoming attributes array. This will be applied to the block markup in
 * the front-end.
 *
 * @param  WP_Block_Type $block_type       Block type.
 * @param  array         $block_attributes Block attributes.
 *
 * @return array Typography CSS classes and inline styles.
 */
function gutenberg_apply_typography_support( $block_type, $block_attributes ) {
	if ( ! property_exists( $block_type, 'supports' ) ) {
		return array();
	}

	$typography_supports = _wp_array_get( $block_type->supports, array( 'typography' ), false );
	if ( ! $typography_supports ) {
		return array();
	}

	$skip_typography_serialization = _wp_array_get( $typography_supports, array( '__experimentalSkipSerialization' ), false );
	if ( $skip_typography_serialization ) {
		return array();
	}

	$attributes = array();
	$classes    = array();
	$styles     = array();

	$has_font_family_support     = _wp_array_get( $typography_supports, array( '__experimentalFontFamily' ), false );
	$has_font_size_support       = _wp_array_get( $typography_supports, array( 'fontSize' ), false );
	$has_font_style_support      = _wp_array_get( $typography_supports, array( '__experimentalFontStyle' ), false );
	$has_font_weight_support     = _wp_array_get( $typography_supports, array( '__experimentalFontWeight' ), false );
	$has_letter_spacing_support  = _wp_array_get( $typography_supports, array( '__experimentalLetterSpacing' ), false );
	$has_line_height_support     = _wp_array_get( $typography_supports, array( 'lineHeight' ), false );
	$has_text_decoration_support = _wp_array_get( $typography_supports, array( '__experimentalTextDecoration' ), false );
	$has_text_transform_support  = _wp_array_get( $typography_supports, array( '__experimentalTextTransform' ), false );

	if ( $has_font_size_support ) {
		$has_named_font_size  = array_key_exists( 'fontSize', $block_attributes );
		$has_custom_font_size = isset( $block_attributes['style']['typography']['fontSize'] );

		if ( $has_named_font_size ) {
			$classes[] = sprintf( 'has-%s-font-size', _wp_to_kebab_case( $block_attributes['fontSize'] ) );
		} elseif ( $has_custom_font_size ) {
			$styles[] = sprintf( 'font-size: %s;', $block_attributes['style']['typography']['fontSize'] );
		}
	}

	if ( $has_font_family_support ) {
		$has_named_font_family  = array_key_exists( 'fontFamily', $block_attributes );
		$has_custom_font_family = isset( $block_attributes['style']['typography']['fontFamily'] );

		if ( $has_named_font_family ) {
			$classes[] = sprintf( 'has-%s-font-family', _wp_to_kebab_case( $block_attributes['fontFamily'] ) );
		} elseif ( $has_custom_font_family ) {
			// Before using classes, the value was serialized as a CSS Custom Property.
			// We don't need this code path when it lands in core.
			$font_family_custom = $block_attributes['style']['typography']['fontFamily'];
			if ( strpos( $font_family_custom, 'var:preset|font-family' ) !== false ) {
				$index_to_splice    = strrpos( $font_family_custom, '|' ) + 1;
				$font_family_slug   = _wp_to_kebab_case( substr( $font_family_custom, $index_to_splice ) );
				$font_family_custom = sprintf( 'var(--wp--preset--font-family--%s)', $font_family_slug );
			}
			$styles[] = sprintf( 'font-family: %s;', $font_family_custom );
		}
	}

	if ( $has_font_style_support ) {
		$font_style = gutenberg_typography_get_css_variable_inline_style( $block_attributes, 'fontStyle', 'font-style' );
		if ( $font_style ) {
			$styles[] = $font_style;
		}
	}

	if ( $has_font_weight_support ) {
		$font_weight = gutenberg_typography_get_css_variable_inline_style( $block_attributes, 'fontWeight', 'font-weight' );
		if ( $font_weight ) {
			$styles[] = $font_weight;
		}
	}

	if ( $has_line_height_support ) {
		$has_line_height = isset( $block_attributes['style']['typography']['lineHeight'] );
		if ( $has_line_height ) {
			$styles[] = sprintf( 'line-height: %s;', $block_attributes['style']['typography']['lineHeight'] );
		}
	}

	if ( $has_text_decoration_support ) {
		$text_decoration_style = gutenberg_typography_get_css_variable_inline_style( $block_attributes, 'textDecoration', 'text-decoration' );
		if ( $text_decoration_style ) {
			$styles[] = $text_decoration_style;
		}
	}

	if ( $has_text_transform_support ) {
		$text_transform_style = gutenberg_typography_get_css_variable_inline_style( $block_attributes, 'textTransform', 'text-transform' );
		if ( $text_transform_style ) {
			$styles[] = $text_transform_style;
		}
	}

	if ( $has_letter_spacing_support ) {
		$letter_spacing_style = gutenberg_typography_get_css_variable_inline_style( $block_attributes, 'letterSpacing', 'letter-spacing' );
		if ( $letter_spacing_style ) {
			$styles[] = $letter_spacing_style;
		}
	}

	if ( ! empty( $classes ) ) {
		$attributes['class'] = implode( ' ', $classes );
	}
	if ( ! empty( $styles ) ) {
		$attributes['style'] = implode( ' ', $styles );
	}

	return $attributes;
}

/**
 * Generates an inline style for a typography feature e.g. text decoration,
 * text transform, and font style.
 *
 * @param array  $attributes   Block's attributes.
 * @param string $feature      Key for the feature within the typography styles.
 * @param string $css_property Slug for the CSS property the inline style sets.
 *
 * @return string              CSS inline style.
 */
function gutenberg_typography_get_css_variable_inline_style( $attributes, $feature, $css_property ) {
	// Retrieve current attribute value or skip if not found.
	$style_value = _wp_array_get( $attributes, array( 'style', 'typography', $feature ), false );
	if ( ! $style_value ) {
		return;
	}

	// If we don't have a preset CSS variable, we'll assume it's a regular CSS value.
	if ( strpos( $style_value, "var:preset|{$css_property}|" ) === false ) {
		return sprintf( '%s:%s;', $css_property, $style_value );
	}

	// We have a preset CSS variable as the style.
	// Get the style value from the string and return CSS style.
	$index_to_splice = strrpos( $style_value, '|' ) + 1;
	$slug            = substr( $style_value, $index_to_splice );

	// Return the actual CSS inline style e.g. `text-decoration:var(--wp--preset--text-decoration--underline);`.
	return sprintf( '%s:var(--wp--preset--%s--%s);', $css_property, $css_property, $slug );
}

/**
 * Checks a string for a unit and value and returns an array consisting of `'value'` and `'unit'`, e.g., [ '42', 'rem' ].
 *
 * @param string $raw_value            Raw size value from theme.json.
 * @param string $coerce_to            Coerce the value to rem or px. Default `'rem'`.
 * @param number $root_font_size_value Value of root font size for rem|em <-> px conversion.
 * @param array  $acceptable_units     An array of font size units.
 * @return array                       An array consisting of `'value'` and `'unit'`, e.g., [ '42', 'rem' ]
 */
function gutenberg_get_typography_value_and_unit( $raw_value, $coerce_to = '', $root_font_size_value = 16, $acceptable_units = array( 'rem', 'px', 'em' ) ) {
	$acceptable_units_group = implode( '|', $acceptable_units );
	$pattern                = '/^(\d*\.?\d+)(' . $acceptable_units_group . '){1,1}$/';

	preg_match( $pattern, $raw_value, $matches );

	// We need a number value and a px or rem unit.
	if ( ! isset( $matches[1] ) && isset( $matches[2] ) ) {
		return null;
	}

	$value = $matches[1];
	$unit  = $matches[2];

	// Default browser font size. Later we could inject some JS to compute this `getComputedStyle( document.querySelector( "html" ) ).fontSize`.
	if ( 'px' === $coerce_to && ( 'em' === $unit || 'rem' === $unit ) ) {
		$value = $value * $root_font_size_value;
		$unit  = $coerce_to;
	}

	if ( 'px' === $unit && ( 'em' === $coerce_to || 'rem' === $coerce_to ) ) {
		$value = $value / $root_font_size_value;
		$unit  = $coerce_to;
	}

	return array(
		'value' => $value,
		'unit'  => $unit,
	);
}

/**
 * Internal implementation of clamp() based on available min/max viewport width, and min/max font sizes..
 *
 * @param array  $fluid_settings        Possible values: array( 'minViewportWidth' => string, 'maxViewportWidth' => string ).
 * @param string $minimum_font_size_raw Minimumn font size for any clamp() calculation.
 * @param string $maximum_font_size_raw Maximumn font size for any clamp() calculation.
 * @return string                        A font-size value using clamp().
 */
function gutenberg_get_computed_typography_clamp_value( $fluid_settings, $minimum_font_size_raw, $maximum_font_size_raw ) {
	$minimum_font_size = gutenberg_get_typography_value_and_unit( $minimum_font_size_raw );
	// We get a 'preferred' unit to keep units across the calc as consistent as possible.
	$font_size_unit = $minimum_font_size['unit'];

	// Grab the maximum font size and normalize it in order to use the value for calculations.
	$maximum_font_size = gutenberg_get_typography_value_and_unit( $maximum_font_size_raw, $font_size_unit );
	// Use rem for accessible fluid target font scaling.
	$minimum_font_size_rem = gutenberg_get_typography_value_and_unit( $minimum_font_size_raw, 'rem' );

	// Viewport widths defined for fluid typography. Normalize units.
	$maximum_viewport_width = gutenberg_get_typography_value_and_unit( $fluid_settings['maxViewportWidth'], $font_size_unit );
	$minimum_viewport_width = gutenberg_get_typography_value_and_unit( $fluid_settings['minViewportWidth'], $font_size_unit );

	// Build CSS rule.
	// Borrowed from https://websemantics.uk/tools/responsive-font-calculator/.
	$view_port_width_offset = round( $minimum_viewport_width['value'] / 100, 3 ) . $font_size_unit;
	$linear_factor          = 100 * ( ( $maximum_font_size['value'] - $minimum_font_size['value'] ) / ( $maximum_viewport_width['value'] - $minimum_viewport_width['value'] ) );
	$linear_factor          = round( $linear_factor, 3 );
	$fluid_target_font_size = 'calc(' . implode( '', $minimum_font_size_rem ) . " + ((1vw - $view_port_width_offset) * $linear_factor))";

	return "clamp({$minimum_font_size_raw}, $fluid_target_font_size, {$maximum_font_size_raw})";
}

/**
 * Returns a font-size value based on a given font-size preset. If typography.fluid is enabled it will try to return a fluid string.
 *
 * @param array $preset  fontSizes preset value as seen in theme.json.
 * @return string        Font-size value.
 */
function gutenberg_get_typography_font_size_value( $preset ) {
	$typography_settings = gutenberg_get_global_settings( array( 'typography' ) );

	if ( ! isset( $typography_settings['fluid'] ) ) {
		return $preset['size'];
	}

	$fluid_settings = $typography_settings['fluid'];

	// Font sizes.
	$fluid_font_size_settings = isset( $preset['fluidSize'] ) ? $preset['fluidSize'] : null;

	if ( ! $fluid_font_size_settings ) {
		return $preset['size'];
	}

	$minimum_font_size_raw = isset( $fluid_font_size_settings['minSize'] ) ? $fluid_font_size_settings['minSize'] : null;
	$maximum_font_size_raw = isset( $fluid_font_size_settings['maxSize'] ) ? $fluid_font_size_settings['maxSize'] : null;
	$fluid_formula         = isset( $fluid_font_size_settings['fluidFormula'] ) ? $fluid_font_size_settings['fluidFormula'] : null;
	$viewport_widths_set   = isset( $fluid_settings['minViewportWidth'] ) && isset( $fluid_settings['maxViewportWidth'] );

	// Gutenberg's internal implementation.

	/*
		"fluid": {
			"maxViewportWidth": "1600px",
			"minViewportWidth": "768px"
		},
		"fontSizes": [
			{
				"size": "5.25rem",
				"fluidSize": {
					"minSize": "5.25rem",
					"maxSize": "9rem"
				},
				"slug": "colossal",
				"name": "Colossal"
			}
	*/
	// Expect all required variables except formula to trigger internal clamp() implementation based on min/max viewport width.
	if ( ! $fluid_formula && $minimum_font_size_raw && $maximum_font_size_raw && $viewport_widths_set ) {
		return gutenberg_get_computed_typography_clamp_value( $fluid_settings, $minimum_font_size_raw, $maximum_font_size_raw );
	}

	// If there are min or max viewport widths, use custom implementation if values available.
	// min, max sizes and fluid formula? Use clamp().

	/*
		"fluid": true,
		"fontSizes": [
			{
				"size": "2rem",
				"fluidSize": {
					"minSize": "2rem",
					"fluidFormula": "calc(2.5 / 100 * 100vw)",
					"maxSize": "2.5rem"
				},
				"slug": "large",
				"name": "Large"
			},
	*/
	if ( $fluid_formula && $minimum_font_size_raw && $maximum_font_size_raw ) {
		return "clamp($minimum_font_size_raw, $fluid_formula, $maximum_font_size_raw)";
	}

	// Use a custom formula. Could be clamp(), could be anything.

	/*
		"fluid": true,
		"fontSizes": [
			{
				"size": "3.7rem",
				"fluidSize": {
					"fluidFormula": "calc(3.7rem * 1px + 2 * 1vw)"
				},
				"slug": "huge",
				"name": "Huge"
			},
	*/
	if ( $fluid_formula && ! $minimum_font_size_raw && ! $maximum_font_size_raw ) {
		return $fluid_formula;
	}

	return $preset['size'];
}

// Register the block support.
WP_Block_Supports::get_instance()->register(
	'typography',
	array(
		'register_attribute' => 'gutenberg_register_typography_support',
		'apply'              => 'gutenberg_apply_typography_support',
	)
);
