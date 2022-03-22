<?php

// TODO: Change name to WP_Webfonts_Registry
class WP_Webfont_Registry {
	private $font_families = array();

	public function register_font_face( $font_face ) {
		$font_family_name = $font_face->get_font()['font-family'];
		$font_family_slug = $this->get_font_family_slug( $font_family_name );

		if ( ! isset( $this->font_families[ $font_family_slug ] ) ) {
			$this->font_families[ $font_family_slug ] = new WP_Webfonts_Font_Family( $font_family_name );
		}

		$this->font_families[ $font_family_slug ]->add_font_face( $font_face );
	}

	public function has_font_family_in_registry( $slug ) {
		return isset( $this->font_familes[ $slug ] );
	}

	public function register_font_family( $font_family ) {
		$slug = $this->get_font_family_slug( $font_family->get_font_family_name() );

		$this->font_families[ $slug ] = $font_family;
	}

	public function unregister_family( $slug ) {
		$font_family = $this->font_families[ $slug ];

		unset( $this->font_families[ $slug ] );

		return $font_family;
	}

	public function unregister_face( $font ) {
		// TODO: Unregister fontface by checking for equality
	}

	public function get_font_families() {
		return $this->font_families;
	}

	public function get_font_family_slug( $font_family_name ) {
		return sanitize_title( $font_family_name );
	}
}
