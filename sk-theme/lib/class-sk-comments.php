<?php
/**
 * Disable comments support.
 *
 * Completeley disable Wordpress built-in comment support.
 *
 * @since 1.0.0
 *
 * @package sk-theme
 */
class SK_Comments {
	public function __construct() {
		add_action( 'admin_init', array( &$this, 'df_disable_comments_post_types_support' ) );
		add_filter( 'comments_open', array( &$this, 'df_disable_comments_status' ), 20, 2 );
		add_filter( 'pings_open', array( &$this, 'df_disable_comments_status' ), 20, 2 );
		add_filter( 'comments_array', array( &$this, 'df_disable_comments_hide_existing_comments' ), 10, 2 );
		add_action( 'admin_menu', array( &$this, 'df_disable_comments_admin_menu' ) );
		add_action( 'admin_init', array( &$this, 'df_disable_comments_admin_menu_redirect' ) );
		add_action( 'admin_init', array( &$this, 'df_disable_comments_dashboard' ) );
		add_action( 'init', array( &$this, 'df_disable_comments_admin_bar' ) );
	}

	/**
	 * Disable support for comments and trackbacks in post types.
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	public function df_disable_comments_post_types_support() {
		$post_types = get_post_types();

		foreach( $post_types as $post_type ) {
			if ( post_type_supports( $post_type, 'comments') ) {
				remove_post_type_support( $post_type, 'comments' );
				remove_post_type_support( $post_type, 'trackbacks' );
			}
		}
	}

	/**
	 * Close comments on the front-end.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean Disables comments.
	 */
	public function df_disable_comments_status() {
		return false;
	}

	/**
	 * Hide existing comments.
	 *
	 * @since 1.0.0
	 *
	 * @return array Empty array for when hiding comments.
	 */
	public function df_disable_comments_hide_existing_comments( $comments ) {
		$comments = array();
		return $comments;
	}

	/**
	 * Remove comments page in menu.
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	public function df_disable_comments_admin_menu() {
		remove_menu_page( 'edit-comments.php' );
	}

	/**
	 * Redirect any user trying to access comments page.
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	public function df_disable_comments_admin_menu_redirect() {
		global $pagenow;

		if ( $pagenow === 'edit-comments.php' ) {
			wp_redirect(admin_url());
			exit;
		}
	}

	/**
	 * Remove comments metabox from dashboard.
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	public function df_disable_comments_dashboard() {
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	}

	/**
	 * Remove comments links from admin bar.
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	public function df_disable_comments_admin_bar() {
		if ( is_admin_bar_showing() ) {
			remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
		}
	}
}