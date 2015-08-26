<?php

	/**
	 * SK_Course_Basket
	 * This class contains the logic for the course basket session 
	 * and the output of the course basket.
	 *
	 * @since  1.0.0
	 *
	 */

	namespace SKChildTheme;

	class SK_Course_Basket {

		public function __construct() {

			add_action('init', array( $this, 'start_session' ) );
			add_shortcode( 'course_basket', array( $this, 'shortcode' ) );

			// Hook for adding courses to basket
			add_action( 'wp_ajax_add_to_basket', array( $this, 'add_course' ) );
			add_action( 'wp_ajax_nopriv_add_to_basket', array( $this, 'add_course' ) );

			// Hook for removing courses from basket
			add_action( 'wp_ajax_remove_from_basket', array( $this, 'remove_course' ) );
			add_action( 'wp_ajax_nopriv_remove_from_basket', array( $this, 'remove_course' ) );

			// Hook for removing courses from basket
			add_action( 'wp_ajax_empty_basket', array( $this, 'empty_basket' ) );
			add_action( 'wp_ajax_nopriv_empty_basket', array( $this, 'empty_basket' ) );

		}

		/**
		 * Shortcode for the basket output
		 * 
		 * @param  array $atts 	not used
		 * @return string 	the basket output html
		 */
		public function shortcode( $atts ){
			
			$basket = '';

			$basket .= '<div class="course-basket-wrapper">';

			if( $this->has_courses() > 0 ) {

				$basket .= '<table class="of-table of-table-even-odd course-basket-table">';
				$basket .= '<thead>';
				$basket .= '<tr>';
				$basket .= '<th>' . __( 'Kursnamn', 'sk' ) . '</th>';
				$basket .= '<th>' . __( 'Anmälningskod', 'sk' ) . '</th>';
				$basket .= '<th></th>';
				$basket .= '</tr>';
				$basket .= '</thead>';
				$basket .= '<tbody>';
				foreach( $this->get_courses() as $course_id => $course ) {

					$basket .= '<tr>';
						$basket .= '<td>' . $course['name'] . '</td><td>' . $course['code'] . '</td><td><a class="remove-course" href="https://sundsvall.alvis.gotit.se/student/kurskorgen.aspx?update=1&bort=' . $course_id . '&paket=False">' . __( 'Ta bort', 'sk' ) . '</a></td>';
					$basket .= '</tr>';

				}

				$basket .= '</tbody>';
				$basket .= '</table>';

			}

			if( $this->has_courses() > 0 ) {

				$basket .= '<div class="form-group apply-at-alvis-wrapper">';
				$basket .= '<a href="https://sundsvall.alvis.gotit.se/student/kurskatalog.aspx" target="_blank" class="of-btn of-btn-inline of-btn-gra of-btn-spaced apply-at-alvis"><span class="apply-at-alvis">' . __( 'Vidare till ansökan', 'sk' ) . '</span></a>';
				$basket .= '</div>';

			} else {

				$basket .= '<div class="basket-empty">' . __( 'Du har inga kurser i korgen', 'sk' ) . '</div>';

			}
			
			$basket .= '</div>';

			$basket .= '<iframe id="alvis-container" src="https://sundsvall.alvis.gotit.se/student/kurskatalog.aspx" style="width: 100%; display: none;"></iframe>';
			$basket .= '<script>';
			$basket .= "var ajaxurl = '" . admin_url('admin-ajax.php') . "';";
			$basket .= "var course_basket_link = '" . site_url() . "/kurskorg" . "';";
			$basket .= '</script>';
			$basket .= '<script src="' . get_stylesheet_directory_uri() . '/assets/js/source/alvis_basket.js" />';

			return $basket;

		}


		/**
		 * Start the session if there is none
		 *
		 * @since  1.0.0
		 */
		public function start_session() {

			if(!session_id()) {
			  session_start();
			}

			if( ! isset( $_SESSION['course_basket'] ) ) $_SESSION['course_basket'] = array();
			if( ! isset( $_SESSION['course_basket']['courses'] ) ) $_SESSION['course_basket']['courses'] = array();
			
		}


		/**
		 * Add a course to the basket session
		 *
		 * @since  1.0.0
		 */
		public function add_course() {
			
			$course = array();
			//unset($_SESSION['course_basket']['courses']);
			if( isset( $_POST['id'] ) && isset( $_POST['course'] ) ) {

				$course_id = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : null;
				if( ! isset( $course_id ) ) {

					wp_send_json( array(
						'result' => false,
						'message' => 'Missing parameter id'
					));

				}

				if( isset( $_SESSION['course_basket']['courses'][$course_id] ) ) {
					
					wp_send_json( array(
						'result' => false,
						'message' => 'Course already added'
					));

				}


				foreach( $_POST['course'] as $data ) {
				
					if( $data['name'] == 'namn' ) {
						$course['name'] = $data['value'];
					} else if( $data['name'] == 'anmkod' ) {
						$course['code'] = $data['value'];
					} else if( $data['name'] == 'coursestart_id' ) {
						$course['id'] = $data['value'];
					}

				}

				$_SESSION['course_basket']['courses'][$course_id] = $course;
				echo '<pre>' . print_r( $_SESSION['course_basket'], true ) . '</pre>';
				wp_send_json( array(
					'result' => true,
					'message' => 'OK'
				));

			}

			wp_send_json( array(
				'result' => false,
				'message' => 'Failed to add to cart'
			));

		}


		/**
		 * Remove a course from the basket session
		 * 
		 * @since  1.0.0
		 */
		public function remove_course() {

			$course_id = isset( $_POST['id'] ) ? $_POST['id'] : null;

			if( isset( $course_id ) && isset( $_SESSION['course_basket']['courses'][$course_id] ) ) {
				unset( $_SESSION['course_basket']['courses'][$course_id] );

				wp_send_json( array(
					'result' => true,
					'message' => 'OK'
				));
			}

			wp_send_json( array(
				'result' => false,
				'message' => 'No such course'
			));

		}


		/**
		 * Empty the basket completely
		 * 
		 * @since 1.0.0
		 */
		public function empty_basket() {

			if( isset( $_SESSION['course_basket'] ) ) {
				unset( $_SESSION['course_basket'] );

				wp_send_json(array(
					'result' => true,
					'message' => 'OK',
				));
			}

			wp_send_json(array(
				'result' => false,
				'message' => 'An error occured while trying to empty the basket',
			));

		}


		/**
		 * Get a single course from the session
		 * 
		 * @since 1.0.0
		 */
		public function get_courses() {

			return isset( $_SESSION['course_basket']['courses'] ) ? $_SESSION['course_basket']['courses'] : array();

		}


		/**
		 * Get the number of courses in basket session
		 * 
		 * @since 1.0.0
		 */
		public function has_courses() {

			return isset( $_SESSION['course_basket']['courses'] ) ? count( $_SESSION['course_basket']['courses'] ) : 0;

		}

	}