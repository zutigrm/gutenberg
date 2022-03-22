<?php

class WP_Webfonts_Font_Family {
	private $font_faces = [];
	private $font_family_name = '';

	public function __construct ( $font_family_name ) {
		$this->font_family_name = $font_family_name;
	}

	public function get_font_faces() {
		return $this->font_faces;
	}

	public function get_font_family_name() {
		return $this->font_family_name;
	}

	public function add_font_face( $given_font_face ) {
		foreach ( $this->font_faces as $index => $font_face ) {
			if ( $given_font_face->is_equal( $font_face )  ) {
				$this->font_faces[$index] = $given_font_face;
				return;
			}
		}

		$this->font_faces[] = $given_font_face;
	}
}
