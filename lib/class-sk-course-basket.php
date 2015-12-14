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

		//private $alvis_url_add = 'https://testsundsvall.alvis.gotit.se/student/laggtillkorg.aspx?add=';
		private $alvis_url_add = 'https://sundsvall.alvis.gotit.se/student/laggtillkorg.aspx?add=';

		public function __construct() {

			add_action( 'init', array( $this, 'start_session' ) );

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
			$courses = array();
			?>

			<div class="course-basket-wrapper">

			<?php if( $this->has_courses() > 0 ) : ?>

				<table class="of-table of-table-even-odd course-basket-table">
					<thead>
						<tr>
							<th><?php _e( 'Kursnamn', 'sk' ); ?></th>
							<th><?php _e( 'Anmälningskod', 'sk' ); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach( $this->get_courses() as $course_id => $course ) : ?>
						<tr id="course-<?php echo $course_id; ?>">
							<td><?php echo $course['name']; ?></td>
							<td><?php echo $course['code']; ?></td>
							<td><a class="remove-course" href="#"><?php _e( 'Ta bort', 'sk' ); ?></a></td>					
						</tr>
					<?php 
					$courses[] = $course_id; 
					endforeach; ?>
					</tbody>
				</table>

			<?php endif; ?>
			<?php if( $this->has_courses() > 0 ) : ?>

				<div class="alert alert-info">
					<p><i class="glyphicon glyphicon-info-sign"></i> <?php _e('Klicka på "Vidare till ansökan" för att ta med dina valda kurser till Alvis.', 'sk' ); ?></p>
					<p><?php _e('Du kommer att vidarebefordras till Alvis där du kan sluföra din ansökan.', 'sk' ); ?></p>
				</div>

				<div class="form-group apply-at-alvis-wrapper">
					<a href="<?php echo $this->alvis_url_add ?><?php echo implode(',', $courses ); ?>" target="_blank" class="of-btn of-btn-inline of-btn-vattjom of-btn-spaced apply-at-alvis">
						<span class=""><?php _e( 'Vidare till ansökan', 'sk' ); ?></span>
					</a>
				</div>

				<?php else : ?>
					<div class="basket-empty alert alert-blank"><?php _e( 'Du har inga kurser i korgen', 'sk' ); ?></div>
				<?php endif; ?>
			
			</div>
			<script>
				var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
				var course_basket_link = '<?php echo site_url(); ?>/kurskorg';
			</script>
			<script src="<?php echo get_stylesheet_directory_uri(); ?>/assets/js/source/alvis_basket.js"></script>

		<?php 
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


			if(!empty( $_SESSION['course_basket'])){
				$ids = array();
				foreach ($_SESSION['course_basket']['courses'] as $key => $value ) {
					$ids[] = $key;
					
				}
				echo $this->alvis_url_add . implode(',', $ids );
			}

			}
			
			die();

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