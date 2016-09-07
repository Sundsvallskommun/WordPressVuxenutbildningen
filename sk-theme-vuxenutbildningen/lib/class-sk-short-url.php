<?php

/**
 * Allow short url shortcut to pages. E.g. /bad/ -> /uppleva-och-gora/bada-simma/badhus-simhallar/
 */
class SK_ShortURL {
	function __construct() {
		add_filter( 'get_shortlink', function ( $shortlink ) {
			return $shortlink;
		} );
		add_filter( 'redirect_canonical', array( &$this, 'canonical_filter' ) );
		add_action( 'wp', array( &$this, 'shortlink_on_404' ), 100 );
	}

	function shortlink_on_404() {
		if ( is_404() ) {
			$this->redirect_if_shortlink();
		}
	}

	function canonical_filter( $redirect_url ) {
		global $wp;
		if ( is_404() ) {
			$this->redirect_if_shortlink();
		}

		return $redirect_url;
	}

	private function redirect_if_shortlink() {
		$path = preg_replace( '/[^A-Za-z0-9\-]/', '', $_SERVER["REQUEST_URI"] );
		// Check for partial match of meta value in case it is comma separated.
		$query = array(
			'meta_key'     => 'sk_shortlink',
			'meta_value'   => trim( $path ),
			'meta_compare' => 'LIKE',
			'post_type'    => 'page'
		);
		$pages = get_posts( $query );
		// Check to see if path is in comma separated meta value.
		foreach ( $pages as $page ) {
			$short_urls = get_post_meta( $page->ID, 'sk_shortlink', true );
			$short_urls = array_map( 'trim', explode( ',', $short_urls ) );
			if ( in_array( $path, $short_urls ) ) {
				wp_redirect( get_permalink( $page ), 301 );
				exit;
			}
		}

		return false;
	}
}