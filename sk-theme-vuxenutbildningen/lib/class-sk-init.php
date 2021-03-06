<?php

	namespace SKChildTheme;

	/**
	 * General child theme settings.
	 *
	 * Controls settings like image sizes, what files that can be uploaded, etc.
	 *
	 * @since 1.0.0
	 *
	 * @package sk-theme-vuxenutbildning
	 */


	class SK_Init {
		
		public function __construct() {
			add_action( 'sk_default_box_types', array( $this, 'default_box_types' ) );
			add_action( 'init', array( $this, 'scroll_ended' ), 99 );
			add_action( 'wp_footer', array( $this, 'footer_script'), 999 );

			// chrome fix for 4.3
			add_action('admin_enqueue_scripts', array( $this, 'chrome_fix' ) );

			// Add ajax services
			add_action( 'wp_ajax_search_courses', array( $this, 'search_courses' ) );
			add_action( 'wp_ajax_nopriv_search_courses', array( $this, 'search_courses' ) );

			add_action( 'wp_ajax_delete_session', array( $this, 'delete_session' ) );
			add_action( 'wp_ajax_nopriv_delete_session', array( $this, 'delete_session' ) );			

		}


		/**
		 * Hotfix for bug that messing up admin menu in Chrome version 45 and WP 4.3
		 *
		 * @since 1.0.0 
		 * 
		 * @return null
		 */
		public function chrome_fix() {
			if ( strpos( $_SERVER['HTTP_USER_AGENT'], 'Chrome' ) !== false )
  			wp_add_inline_style( 'wp-admin', '#adminmenu { transform: translateZ(0); }' );
		}

		/**
		 * Script in footer to trigger a search with pre default values
		 *
		 * @since 1.0.0 
		 * 
		 * @return null
		 */
		public function footer_script(){
			global $post;
			
			$post_form_id = false;
			// check for previous form id
			if(isset($_SESSION['search_history'])){
  		  foreach( $_SESSION['search_history'] as $key => $value ){
	    		if( $value['name'] == 'post_id' ){
	      		$post_form_id = $value['value'];
	    		}
  			}
			}
			
			// on mismatch or empty form id, do a search based on pre settings.
			if( isset( $post_form_id ) && $post_form_id != $post->ID ) :
 		 ?>
		  	<script type="text/javascript">
					(function ($) {
					   $(function() {
					    $( '#btn-courselist-filter' ).click();
					     });
					}(jQuery));
				</script>
		<?php endif; ?>
		  	<script type="text/javascript">
		  		function scroll_trigger(){
		  			$('html, body').animate({scrollTop:$(document).height()}, 'slow');
		  		}
				</script>
			<?php
	}

		/**
		 * Ajax method for delete history session
		 *
		 * @since 1.0.0 
		 * 
		 * @return [type] [description]
		 */
		public function delete_session(){
			unset( $_SESSION['search_history'] );
			die();

		}


		/**
		 * Ajax call, kill jScroll when ended, fix for prevent js error.
		 *
		 * @since 1.0.0 
		 * 
		 * @return [type] [description]
		 */
		public function scroll_ended(){
			if(isset($_GET['scroll-ended'])){
				die();
			}
		}

		/**
		 * TODO
		 * @return [type] [description]
		 */
		public function search_courses( ){
			$post_data = array();
			
			if(isset( $_POST['data'] ) && !empty($_POST['data'])){
				unset( $_SESSION['search_history'] );
				$post_data = $_POST['data'];
			}

			if(isset($_SESSION['search_history']))
				$search_history = $_SESSION['search_history'];
			
			if( !empty( $search_history ) )
				$post_data = $search_history;

			// save as session for history go back button
			$_SESSION['search_history'] = $post_data;

			the_courselist_block( false, $post_data );
		  die();
		}	

		/**
		 * Filter default box types
		 * 
		 * @since 1.0.0
		 * 
		 * @param array $default_box_types
		 * @return array
		 */
		public function default_box_types( $default_box_types ) {

			$default_box_types []= 'Bild och Länklista';

			return $default_box_types;

		}

	}