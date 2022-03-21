<?php

class WP_Webfont_Registry {
	private $items = array();


	public function register( $font ) {
		if ( ! isset( $this->items[ $font->get_slug() ] ) ) {
			$this->items[ $font->get_slug() ] = array();
		}

		$this->items[ $font->get_slug() ][] = $font;
	}

	public function unregister_family( $slug ) {
		$item = $this->items[ $slug ];

		unset( $this->items[ $slug ] );

		return $item;
	}

	public function unregister_face( $font ) {
		// TODO: Unregister fontface by checking for equality
	}

	public function get_items() {
		return $this->items;
	}
}
