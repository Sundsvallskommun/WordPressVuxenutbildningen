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

		private $browser = array();

		public function __construct() {

			add_action( 'init', array( $this, 'start_session' ) );
			add_action( 'init', array( $this, 'browser_detection' ) );

			add_shortcode( 'course_basket', array( $this, 'shortcode' ) );


			// Hook for adding courses to alvis on checkout
			//add_action( 'wp_ajax_add_to_alvis', array( $this, 'add_alvis' ) );
			//add_action( 'wp_ajax_nopriv_add_to_alvis', array( $this, 'add_alvis' ) );			

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

		public function browser_detection(){
			$this->browser['msie'] = strpos($_SERVER["HTTP_USER_AGENT"], 'MSIE') ? true : false;
			$this->browser['firefox'] = strpos($_SERVER["HTTP_USER_AGENT"], 'Firefox') ? true : false;
			
			$this->browser['trident'] = strpos($_SERVER["HTTP_USER_AGENT"], 'Trident') ? true : false;


			$this->browser['chrome'] = false;
			$this->browser['safari'] = false;

			if (strpos( $_SERVER["HTTP_USER_AGENT"], 'Chrome') !== false)
			  $this->browser['chrome'] = strpos($_SERVER["HTTP_USER_AGENT"], 'Chrome') ? true : false;
			elseif (strpos( $_SERVER["HTTP_USER_AGENT"], 'Safari') !== false)
				$this->browser['safari'] = strpos($_SERVER["HTTP_USER_AGENT"], 'Safari') ? true : false;


		}

		/**
		 * Shortcode for the basket output
		 * 
		 * @param  array $atts 	not used
		 * @return string 	the basket output html
		 */
		public function shortcode( $atts ){
			$this->browser_detection();
			$basket = '';
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

				<?php endforeach; ?>

				</tbody>
				</table>

			<?php endif; ?>
			<?php if( $this->has_courses() > 0 ) : ?>

				<div class="form-group apply-at-alvis-wrapper">
				<div class="alert alert-info"><h4><i class="glyphicon glyphicon-info-sign"></i> <?php _e('Skapa ansökan', 'sk');?></h4> 
					<p><?php _e('För att slutföra din ansökan behöver du lägga till kurserna till ditt studentkonto hos Alvis.') ?></p>
					<p><?php _e('Följ nedan steg för att lägga till dina valda kurser och gå vidare med din ansökan.') ?></p>
				</div>
				
				<ul>
				<?php 
					//\util::debug( $_SERVER );
					$step_two_hidden = false;
					if( $this->browser['safari'] == true || $this->browser['msie'] == true || $this->browser['trident'] == true ) : 
						$step_two_hidden = true;

				?>
					<li id="alvis-connection">
						<div class="alert alert-blank"><i><?php _e('<b>Steg 1 av 3.</b> <br>Vi behöver öppna Alvis i en ny tabb för att skapa en anslutning. Återgå sedan till denna tabb för att slutföra din ansökan.', 'sk') ?></i></div>
						<a href="#" class="of-btn of-btn-inline of-btn-spaced"><span class="apply-at-alvis"><?php _e( 'Klicka här för att skapa anslutning mot Alvis.', 'sk' ); ?></span></a>
					</li>
				<?php else : ?>
					<li id="alvis-connection">
						<div class="alert alert-blank"><i class="glyphicon glyphicon-ok"></i> Anslutning mot Alvis är skapad. Steg 1 av 3.</div>
					</li>
				<?php endif; ?>


				<li id="alvis-add-courses" <?php echo $step_two_hidden == true ? 'style="display:none"' : '';?>>
					<a href="#" id="add_to_alvis" class="of-btn of-btn-inline of-btn-spaced"><span class="apply-at-alvis"><?php _e( 'Klicka här för att lägga till dina kurser till ditt studentkonto', 'sk' ); ?></span></a>
				</li>
				<li id="alvis-proceed" style="display:none">
					<a href="https://sundsvall.alvis.gotit.se/student/kurskatalog.aspx" target="_blank" class="of-btn of-btn-inline of-btn-spaced apply-at-alvis"><span class="apply-at-alvis"><?php _e( 'Gå vidare till Alvis för att slutföra din ansökan.', 'sk' ); ?></span></a>
				</li>
				</ul>

				</div>
				<?php else : ?>

				<div class="basket-empty alert alert-blank"><?php _e( 'Du har inga kurser i korgen', 'sk' ); ?></div>

				<?php endif; ?>
			
			</div>
			
			<iframe id="alvis-container" src="https://sundsvall.alvis.gotit.se/student/kurskatalog.aspx" style="width: 100%; display:none;"></iframe>
			
			<script>
			var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
			var course_basket_link = '<?php echo site_url(); ?>/kurskorg';
			</script>
			<script src="<?php echo get_stylesheet_directory_uri(); ?>/assets/js/source/alvis_basket.js" />

		<?php 
		}


		/**
		 * Start the session if there is none
		 *
		 * @since  1.0.0
		 */
		public function start_session() {
			header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');

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
		public function add_alvis() {
			\util::debug( $_SESSION['course_basket']['courses'] );
			die();
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