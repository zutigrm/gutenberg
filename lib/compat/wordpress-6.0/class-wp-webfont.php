<?php

// TODO: Change to WP_Webfonts_Font_Face
class WP_Webfont {
	private $font_face;

	public function __construct( $raw_font_face ) {
		$font_face = $this->validate( $raw_font_face );

		if ( $font_face ) {
			$this->font_face = $font_face;
		}
	}

	public function get_font() {
		return $this->font_face;
	}

	public function update_font( $updates ) {
		$this->font_face = array_merge( $this->font_face, $updates );
		return $this->font;
	}

	public function is_equal( $given_font_face ) {
		return $this->font_face['font-family'] === $given_font_face->get_font()['font-family'] &&
			$this->font_face['font-style'] === $given_font_face->get_font()['font-style'] &&
			$this->font_face['font-display'] === $given_font_face->get_font()['font-display'];
	}

	private function validate( $font ) {
		$font = wp_parse_args(
			$font,
			array(
				'provider'     => 'local',
				'font-family'  => '',
				'font-style'   => 'normal',
				'font-weight'  => '400',
				'font-display' => 'fallback',
			)
		);

		// Check the font-family.
		if ( empty( $font['font-family'] ) || ! is_string( $font['font-family'] ) ) {
			trigger_1( __( 'Webfont font family must be a non-empty string.', 'gutenberg' ) );
			return false;
		}

		// Local fonts need a "src".
		if ( 'local' === $font['provider'] ) {
			// Make sure that local fonts have 'src' defined.
			if ( empty( $font['src'] ) || ( ! is_string( $font['src'] ) && ! is_array( $font['src'] ) ) ) {
				trigger_error( __( 'Webfont src must be a non-empty string or an array of strings.', 'gutenberg' ) );
				return false;
			}
		}

		// Validate the 'src' property.
		if ( ! empty( $font['src'] ) ) {
			foreach ( (array) $font['src'] as $src ) {
				if ( empty( $src ) || ! is_string( $src ) ) {
					trigger_error( __( 'Each webfont src must be a non-empty string.', 'gutenberg' ) );
					return false;
				}
			}
		}

		// Check the font-weight.
		if ( ! is_string( $font['font-weight'] ) && ! is_int( $font['font-weight'] ) ) {
			trigger_error( __( 'Webfont font weight must be a properly formatted string or integer.', 'gutenberg' ) );
			return false;
		}

		// Check the font-display.
		if ( ! in_array( $font['font-display'], array( 'auto', 'block', 'fallback', 'swap' ), true ) ) {
			$font['font-display'] = 'fallback';
		}

		$valid_props = array(
			'ascend-override',
			'descend-override',
			'font-display',
			'font-family',
			'font-stretch',
			'font-style',
			'font-weight',
			'font-variant',
			'font-feature-settings',
			'font-variation-settings',
			'line-gap-override',
			'size-adjust',
			'src',
			'unicode-range',

			// Exceptions.
			'provider',
		);

		foreach ( $font as $prop => $value ) {
			if ( ! in_array( $prop, $valid_props, true ) ) {
				unset( $font[ $prop ] );
			}
		}

		return $font;
	}
}
