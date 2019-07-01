<?php
// fix due to 4.4.1 that breaks page request like /page/[number]
remove_filter('template_redirect','redirect_canonical');

/* ------------------------------------------
|  LOAD UTILITY CLASS, REMOVE FOR PRODUCTION |
 -------------------------------------------- */

require_once( locate_template( '/lib/util.php' ) );


// Load parent themes template helpers
require_once( get_template_directory() . '/lib/helpers/advanced-template.php' );
require_once( get_template_directory() . '/lib/helpers/general-template.php' );

// Load child themes template helpers
require_once locate_template( '/lib/helpers/advanced-template.php' );
require_once locate_template( '/lib/helpers/general-template.php' );

// Load parent init class
require_once( get_template_directory() . '/lib/class-sk-init.php' );
// Load child init class
require_once( get_stylesheet_directory() . '/lib/class-sk-init.php' );
$init = new SKChildTheme\SK_Init();

// Load parent posttypes class
require_once( get_template_directory() . '/lib/class-sk-post-types.php' );
//require_once( get_stylesheet_directory() . '/lib/class-sk-post-types.php' );
//$osynlig_post_types = new SKChildTheme\SK_Post_Types();

require_once( get_stylesheet_directory() . '/lib/class-sk-short-url.php' );
$sk_short_url = new SK_ShortURL();

add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
    // Style
    //wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/assets/css/style.min.css' );
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/assets/css/style.css' );

		// JS echo PRODUCTION_MODE ? '.min' : '';
		if( PRODUCTION_MODE === true )
			wp_enqueue_script( 'child-js', get_stylesheet_directory_uri() . '/assets/js/app.min.js', array( 'jquery', 'jquery-ui-datepicker' ), null, true );
		else
			wp_enqueue_script( 'child-js', get_stylesheet_directory_uri() . '/assets/js/app.js', array( 'jquery', 'jquery-ui-datepicker' ), null, true  );
  

  	// include jquery jscroll
	wp_enqueue_script( 'jscroll', get_stylesheet_directory_uri() . '/assets/js/lib/jscroll/jquery.jscroll.min.js', array( 'jquery' ), null, true );

/**
 * TODO: move
 */
wp_localize_script( 'child-js', 'ajax_object', array(
  'ajaxurl'     => admin_url( 'admin-ajax.php' ),
  'ajax_nonce'  => wp_create_nonce('ajax_nonce'),
  'course_basket_link' => site_url() . '/kurskorg'
  ) 
); // setting ajaxurl and nonce

}

add_action( 'admin_enqueue_scripts', 'sk_child_enque_admin_script' );
function sk_child_enque_admin_script() {

  wp_enqueue_script( 'course-admin', get_stylesheet_directory_uri() . '/assets/js/admin.js', array( 'jquery' ), null, true );

}