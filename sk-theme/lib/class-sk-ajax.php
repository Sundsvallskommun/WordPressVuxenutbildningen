<?php
/**
 * Theme ajax handling.
 *
 * @since 1.0.0
 *
 * @package sk-theme
 */
class SK_Ajax {
	public function __construct() {
		add_action( 'init', array( &$this, 'init_ajax' ) );
	}

	/**
	 * Set up AJAX callbacks.
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	public function init_ajax() {
		// Setup AJAX calls
		//add_action('wp_ajax_example', array(&$this, 'example')); // Logged in
		//add_action('wp_ajax_nopriv_example', array(&$this, 'example')); // Everyone else
	}

	/**
	 * Example AJAX callback.
	 *
	 * Lorem ipsum dolor sit amet.
	 *
	 * @since 1.0.0
	 *
	 * @param string $nonce AJAX nonce.
	 * @return type Description.
	 */
	public function example() {
		$nonce = $_POST['nonce'];

		if ( ! wp_verify_nonce( $nonce, 'example-nonce' ) ) {
			die( '-1' );
		}

		$data = json_encode( array( 'Success!' ) );

		die( $data );

		die( '1' ); // Always echo something and kill the script
	}
}