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

			// Add ajax services
			add_action( 'wp_ajax_search_courses', array( $this, 'search_courses' ) );
			add_action( 'wp_ajax_nopriv_search_courses', array( $this, 'search_courses' ) );

			add_action( 'wp_ajax_delete_session', array( $this, 'delete_session' ) );
			add_action( 'wp_ajax_nopriv_delete_session', array( $this, 'delete_session' ) );			


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
				$transient_history = $_SESSION['search_history'];
			
			if( !empty( $transient_history ) )
				$post_data = $transient_history;

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