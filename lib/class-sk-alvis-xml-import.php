<?php
	/**
	 * SK_Alvis_XML_Import
	 * This class imports courses from the Alvis system 
	 * run by GOTIT. It sets up a schedule to import the courses on.
	 * If a course does not exist previously it creates a new one of
	 * the custom post type kurs. If it already exists it updates
	 * the course.
	 *
	 * @since  1.0.0
	 *
	 * @todo  remove local xml file support in method get_xml before production
	 */


	namespace SKChildTheme;

	set_time_limit( 600 ); // set timelimit to 10 minutes

	class SK_Alvis_XML_Import {

		private $import_date;
		private $import_time;
		private $num_courses = 0;
		private $courses;
		private $is_manual_import = false;

		/**
		 * Constructor
		 *
		 * @since 1.0.0 
		 * 
		 */
		public function __construct() {
			// Add a dayly interval
      //wp_clear_scheduled_hook( 'sk_vuxenutbildning_import_courses' );
			if( !wp_next_scheduled( 'sk_vuxenutbildning_import_courses' ) ) {
				$start = strtotime( date('Y-m-d') . '04:30:00' . '+ 1 days');
				wp_schedule_event( $start, 'daily', 'sk_vuxenutbildning_import_courses' );
			} 

			add_action( 'sk_vuxenutbildning_import_courses', array( $this, 'import' ) );

			// Add a link to menu for admins to set import options.
    	if( is_admin() && current_user_can( 'activate_plugins' ) ) {
      	add_action( 'admin_menu', array( $this, 'add_course_import_options' ) );
    	}

    	add_action( 'wp_ajax_manual_course_import', array( $this, 'manual_import' ) );

			// Use this one only on local tests. It forces a
			// run on every page update.
			// add_action('init', array( $this, 'import' ));

		}

		/**
     * Add the weekly cron interval to cron schedule list.
     * 
     * @since 1.0.0
     * 
     * @param array $schedules
     * @return array
     */
    public function add_minutly_cron_schedule( $schedules ) {

      $schedules['minutly'] = array(
        'interval' => 60*5, // 1 week in seconds
        'display'  => __( 'Once a minute', 'sk' ),
      );

      return $schedules;
    }

    /**
     * Method for manual triggered import
     *
     * @since 1.0.0
     * 
     */
    public function manual_import() {


    	// NEED TO ADD NOUNCE AND USER_PERMISSIONS FOR ADMIN
    	$this->is_manual_import = true;
    	$result = $this->import();

    	if( is_array( $result ) ) {

    		if( isset( $result['result'] ) && $result['result'] == true ) {
    			
    			update_option( 'course_import_status', 'OK - ' . date_i18n('Y-m-d H:i:s') );

    		} else {
    			
    			update_option( 'course_import_status', 'ERROR - ' . date_i18n('Y-m-d H:i:s') );

    		}

    		wp_send_json( $result );

    	}

    }


    /**
     * Delete all terms in taxonomy kurskategorier
     *
     * @since 1.0.0 
     * 
     * @return none
     * 
     */
    private function delete_terms(){
    	global $wpdb;
    	
    	$sql = "DELETE FROM $wpdb->terms WHERE term_id IN
				( SELECT * FROM 
					( SELECT $wpdb->terms.term_id FROM $wpdb->terms
				    	JOIN $wpdb->term_taxonomy ON $wpdb->term_taxonomy.term_id = $wpdb->terms.term_id
				    	WHERE taxonomy = 'kurskategorier'
					) as T
				);
				";

			$wpdb->query( $sql );

    }


    /**
     * Delete all posts and postmeta in post type kurs
     *
     * CURRENTLY NOT IN USE
     *
     * @since 1.0.0 
     * 
     * @return none
     * 
     */
    private function delete_posts(){
    	/*
    	global $wpdb;
    	// Delete post meta
    	$sql = "DELETE FROM $wpdb->postmeta WHERE post_id IN (
				 	SELECT * FROM ( 
				 		SELECT $wpdb->posts.ID FROM $wpdb->posts
				    	WHERE post_type = 'kurs' AND post_status = 'publish'
					) as P
				);
				";

			$wpdb->query( $sql );

			// Delete posts
    	$sql = "DELETE FROM $wpdb->posts WHERE post_type = 'kurs';";
			$wpdb->query( $sql );
			*/
    }    

    /**
     * Main import function
     *
     * @since  1.0.0
     * 
     * @return boolean
     */
		public function import() {

			$xml_content = $this->get_xml();

			// No xml, no continue
			if( ! $xml_content || is_array( $xml_content ) ) {
				
				// Update failed
				update_option( 'course_import_status', 'ERROR - ' . date_i18n('Y-m-d H:i:s') );

				return $xml_content;

			}

			// xml exists, delete option and remove all terms and insert new to prevent empty unused terms
			delete_option( 'vuxenutbildning_categorized_terms' );
			self::delete_terms();


			// Run import
			$importer = simplexml_load_string( $xml_content );

			if( isset( $importer->kurs ) ) {

				$this->num_courses = count( $importer->kurs );

				if( $this->num_courses > 0 ) {
					
					foreach( $importer->kurs as $course ) {

						$this->create_or_update_course( $course );

					}

				}

			}

			// Import ran smoothly
			update_option( 'course_import_status', 'OK - ' . date_i18n( 'Y-m-d H:i:s' ) );

			if( $this->is_manual_import ) {

				return array(
					'result' => true,
					'message' => 'OK',
					'num_courses' => $this->num_courses,
				);

			}
			

		}


		/**
		 * Get the xml file from the ftp server
		 * 
		 * @return bool|string $xml_content
		 */
		public function get_xml() {

			// Remove this in production
			
			if( file_exists( get_stylesheet_directory() . '/alvis.xml' ) ) {
				return file_get_contents( get_stylesheet_directory() . '/alvis.xml' );
			}
			
			// Load options
	  	$options = get_option( 'sk_course_import_options' );
	  	$ftp_address = isset( $options['ftp_address'] ) ? $options['ftp_address'] : '';
	  	$ftp_port = isset( $options['ftp_port'] ) ? $options['ftp_port'] : 22;
	  	$ftp_path = isset( $options['ftp_path'] ) ? $options['ftp_path'] : '';
	  	$ftp_username = isset( $options['ftp_username'] ) ? $options['ftp_username'] : '';
	  	$ftp_password = isset( $options['ftp_password'] ) ? $options['ftp_password'] : '';

	  	// Do not proceed if we do not have the settings
	  	if( empty( $ftp_address ) || empty( $ftp_port ) || empty( $ftp_username ) || empty( $ftp_password ) ) 
	  		return array( 'result' => 'false', 'message' => 'Missing connection parameters.' );

			try {
				
				//file_put_contents( '/tmp/path.txt', "ssh2.sftp://$ftp_username:$ftp_password@$ftp_address:$ftp_port$ftp_path" );
				$xml_content = @file_get_contents( "ssh2.sftp://$ftp_username:$ftp_password@$ftp_address:$ftp_port$ftp_path" );

				// If failed to load the file. The xml_content == false
				if( $xml_content === false ) {
					throw new \Exception( 'Failed to read xml file' );
				}

			} catch( \Exception $e ) {

				return array( 'result' => false, 'message' => 'Kunde inte komma åt xmlfilen. Anslutningsfel eller filen finns inte.' );

			}

			return $xml_content;

		}


		/**
		 * Perfor the logic to either create a new
		 * course or update an existing one.
		 *
		 * @since  1.0.0
		 * 
		 * @param  Simple_XML_Object $course_xml_object
		 * @return boolean
		 */
		private function create_or_update_course( $course_xml_object ) {
			
			$tmp1 = $course_xml_object->attributes();
			$course_id =  (int) $tmp1['id'];
			$tmp2 = $course_xml_object->attributes();
			$course_package = (string) $tmp2['kurspaket'];

			$course_content = '';
			if(!empty( $course_xml_object->kursbeskrivning ))
				$course_content = strip_tags( $course_xml_object->kursbeskrivning, '<a>' );

			// Check if course already exists
			if( !$this->course_exists( $course_id ) ) {

				// Set the post ID so that we know the post was created successfully
				$post_id = wp_insert_post(
					array(
						'comment_status'  => 'closed',
						'ping_status'   => 'closed',
						'post_author'   => 1,
						'post_name'   => sanitize_title( $course_xml_object->kursnamn ),
						'post_title'    => $course_xml_object->kursnamn,
						'post_content' => $course_content,
						'post_status'   => 'publish',
						'post_type'   => 'kurs'
					)
				);

				if( !is_wp_error( $post_id ) ) {

					$this->update_course_metadata( $post_id, $course_xml_object, $course_id, $course_package );
					$this->update_course_terms( $post_id, $course_xml_object );

					return true;

				}

			} else { // Update the course

				$course = $this->get_course( $course_id );

				$args = array(
					'ID' => $course->ID,
					'post_name'   => sanitize_title( $course_xml_object ),
					'post_title'    => $course_xml_object->kursnamn,
					'post_content' => $course_content,
				);

				$result = wp_update_post( $args, true );

				if( !is_wp_error( $result ) ) {

					$this->update_course_metadata( $course->ID, $course_xml_object, $course_id, $course_package );
					$this->update_course_terms( $course->ID, $course_xml_object );

					return true;

				}

			}

			return false;

		}


		/**
		 * Load the course with corresponding id from db
		 *
		 * @since  1.0.0
		 * 
		 * @param  integer $course_id
		 * @return WP_Object
		 */
		private function get_course( $course_id ) {

			$args = array(
				'post_type' => 'kurs',
				'posts_per_page' => 1,
				'meta_query' => array(
					array(
						'key' => 'kursid',
						'value' => $course_id
					)
				)
			);

			$courses = get_posts( $args );
			return $courses[0];

		}


		/**
		 * Set the metadata for the course
		 *
		 * @since  1.0.0 
		 * 
		 * @param  object $course 
		 *
		 */
		private function update_course_metadata( $post_id, $course, $course_id, $course_package ) {

			//\util::debug( $course );

			update_post_meta( $post_id, 'kursid', (int) $course_id );
			update_post_meta( $post_id, 'kurspaket', (string) $course_package );
			update_post_meta( $post_id, 'kurskod', (string) $course->kurskod );
			update_post_meta( $post_id, 'amnesomrade', (string) $course->ämnesområde );
			update_post_meta( $post_id, 'skolform', (string) $course->skolform );
			update_post_meta( $post_id, 'poang', (string) $course->poäng );
			update_post_meta( $post_id, 'anmkod', (string) $course->anmkod );
			update_post_meta( $post_id, 'kurskategori', (string) $course->kurskategori );
			update_post_meta( $post_id, 'forkunskap', (string) $course->förkunskap );
			update_post_meta( $post_id, 'skolverketurl', (string) $course->skolverketurl );
			update_post_meta( $post_id, 'startdatum', strtotime( $course->datum ) );

			$course_starts = array();

			if( count( $course->kursstarter ) > 0 ) {

				foreach( $course->kursstarter->kursstart as $course_start ) {

					//echo '<pre>' . print_r( $course_start, true ) . '</pre>';
					$tmp1 = $course_start->attributes();
					$coursestart_id =  (int) $tmp1['id'];
					$tmp2 = $course_start->attributes;
					$coursestart_type = (string) $tmp2['typ'];

					$start = array();
					$start['id'] = $coursestart_id;
					$start['typ'] = $coursestart_type;
					$start['period'] = (string) $course_start->period;
					$start['datum'] = (string) $course_start->datum;
					$start['sokbar'] = (string) $course_start->sokbar;
					$start['sokbarTill'] = (string) $course_start->sokbarTill;
					$start['kursbeskedsDatum'] = (string) $course_start->kursbeskedsDatum;
					$start['skola'] = (string) $course_start->skola;
					$start['ort'] = (string) $course_start->ort;
					$start['veckor'] = (string) $course_start->veckor;
					$start['terminer'] = (string) $course_start->terminer;
					$start['maxantal'] = (string) $course_start->maxantal;

					$course_starts []= $start;

				}

			}

			if( isset( $course_starts[0]['datum'] ) ) {
				update_post_meta( $post_id, 'nearest_start_date', strtotime( $course_starts[0]['datum'] ) );
			}
			update_post_meta( $post_id, 'kursstarter', $course_starts );

		}


		/**
		 * Set the course terms
		 *
		 * @since  1.0.0
		 * 
		 * @param  object $course
		 */
		private function update_course_terms( $post_id, $course ) {

			$level_array = array(
				'GY' => 'Gymnasie',
				'GR' => 'Grundskola',
				'LV' => 'Lärvux',
				'SI' => 'SFI',
				'YH' => 'Yrkesutbildning',
			);

			$collected_terms = get_option( 'vuxenutbildning_categorized_terms', array() );

			if( ! isset( $collected_terms['studieform'] ) ) {
				$collected_terms['studieform'] = array();
			}

			$course_type = (string) $course->kurskategori;
			if( ! in_array( $course_type, $collected_terms['studieform'] ) ) array_push( $collected_terms['studieform'], $course_type );

			if( ! isset( $collected_terms['niva'] ) ) {
				$collected_terms['niva'] = array();
			}

			// Add education level to collected terms array
			$level = $level_array[(string) $course->skolform];
			if( ! in_array( $level, $collected_terms['niva'] ) ) array_push( $collected_terms['niva'], $level );

			// Add couse category to collected terms array
			$terms_string = (string) $course->ämnesområde;
			$terms = explode( ',', $terms_string );
			if( ! isset( $collected_terms['amnesomrade'] ) ) {
				$collected_terms['amnesomrade'] = array();
			}

			if( count( $terms ) > 0 ) {
				foreach( $terms as $tmp_term ) {
					if( ! in_array( $tmp_term, $collected_terms['amnesomrade'] ) ) array_push( $collected_terms['amnesomrade'], $tmp_term );
				}
			}


			// Add course subjects as terms
			array_push( $terms, $course_type );
			// Add course points as term
			array_push( $terms, (string) $course->poäng . ' poäng' );
			// Add course level as term
			array_push( $terms, $level );

			if( ! isset( $collected_terms['poang'] ) ) {
				$collected_terms['poang'] = array();
			}
			if( ! in_array( (string) $course->poäng, $collected_terms['poang'] ) ) array_push( $collected_terms['poang'], (string) $course->poäng );

			// Add city/place as term
			$city_terms = array();
			if( count( $course->kursstarter ) > 0 ) {

				foreach( $course->kursstarter->kursstart as $course_start ) {

					if( !in_array( (string) $course_start->ort, $city_terms ) ) {

						array_push( $terms, (string) $course_start->ort );

					}

				}

			}


			// Create the terms
			if( count( $terms ) > 0 ) {

				$terms_array = array();

				foreach( $terms as $term_name ) {

					if( !term_exists( $term_name, 'kurskategorier' ) ) {

						$term_array = wp_insert_term( $term_name, 'kurskategorier' );

						if( is_wp_error( $term_array ) ) return false;

						if( is_array( $term_array ) ) {
							$term = get_term( $term_array['term_id'], 'kurskategorier' );
						}

					} else {

						$term = get_term_by( 'name', $term_name, 'kurskategorier' );

					}

					if( isset( $term ) && is_object( $term ) ) {
						$terms_array []= $term->term_id;
					}

				}

			}

			wp_set_post_terms( $post_id, $terms_array, 'kurskategorier' );
			
			update_option( 'vuxenutbildning_categorized_terms', $collected_terms );

		}


		/**
		 * Test against db if a course already exists
		 *
		 * @since  1.0.0
		 * 
		 * @param  integer $course_id
		 * @return boolean
		 */
		private function course_exists( $course_id ) {

			$args = array(
				'post_type' => 'kurs',
				'numberposts' => -1,
				'meta_query' => array(
					array(
						'key' => 'kursid',
						'value' => $course_id,
						'compare' => '='
					)
				)
			);

			$courses = get_posts( $args );

			// Test if any courses where found with corresponding id
			if( count( $courses ) > 0 ) {
				return true; // Yaaaay
			}
		
			return false;

		}


		/**
	   * Add a menu item to the settings section
	   *
	   * @since  1.0.0
	   */
	  public function add_course_import_options() {

	    add_options_page( __( 'Inställningar för Alvis kursimport', 'sk' ), __( 'Kursimport', 'sk' ), 'manage_options', 'sk_course_import_options', array( $this, 'course_import_options') );

	  }


	  /**
	   * The output callback for the options page
	   * @since 1.0.0
	   * 
	   */
	  public function course_import_options() {

	  	// Load options
	  	$options = get_option( 'sk_course_import_options' );
	  	$ftp_address = isset( $options['ftp_address'] ) ? $options['ftp_address'] : '';
	  	$ftp_port = isset( $options['ftp_port'] ) ? $options['ftp_port'] : 22;
	  	$ftp_path = isset( $options['ftp_path'] ) ? $options['ftp_path'] : '';
	  	$ftp_username = isset( $options['ftp_username'] ) ? $options['ftp_username'] : '';
	  	$ftp_password = isset( $options['ftp_password'] ) ? $options['ftp_password'] : '';
	  	
	  	$timestamp = wp_next_scheduled( 'sk_vuxenutbildning_import_courses' );
      $next_run = date_i18n( 'Y-m-d H:i:s', $timestamp ); 

	    ?>


	      <div class="wrap">         
	          <h2><?php _e( 'Inställningar för Alvis kursimport', 'sk' ); ?></h2>
			      <table class="widefat">
		          <tbody>
		            <tr>
		              <td class="desc" colspan="3">
		              	<p><?php printf( __('Senaste importen: %s', 'sk' ), get_option( 'course_import_status' ) ); ?></p>
		              	<p><?php printf( __('Nästa automatiska import sker: %s', 'sk' ), $next_run ); ?></p>
		              </td>
		            </tr>
		          </tbody>
		        </table>	          
	          <form method="post" action="options.php">
	              <?php wp_nonce_field('update-options') ?>

	              <h3><?php _e( 'FTP', 'sk' ); ?></h3>
	              <table class="form-table">
	                <tbody>
	                <tr class="option-course-import">
	                  <th scope="row"><?php _e( 'FTP-adress', 'sk' ); ?></th>
	                  <td>
	                    <input type="text" id="sk_course_import_ftp_address" name="sk_course_import_options[ftp_address]" value="<?php echo $ftp_address; ?>" />
	                    <p class="description"><?php _e( 'Ange adressen till ftp:n', 'sk' ); ?></p>
	                  </td>
	                </tr>
	                <tr class="option-course-import">
	                  <th scope="row"><?php _e( 'FTP-port', 'sk' ); ?></th>
	                  <td>
	                    <input type="text" id="sk_course_import_ftp_port" name="sk_course_import_options[ftp_port]" value="<?php echo $ftp_port; ?>" />
	                    <p class="description"><?php _e( 'Ange port att använda vid anslutning till FTP', 'sk' ); ?></p>
	                  </td>
	                </tr>
	                <tr class="option-course-import">
	                  <th scope="row"><?php _e( 'Genväg och namn till filen', 'sk' ); ?></th>
	                  <td>
	                    <input type="text" id="sk_course_import_ftp_path" name="sk_course_import_options[ftp_path]" value="<?php echo $ftp_path; ?>" />
	                    <p class="description"><?php _e( 'Ange komplett genväg och namn till filen på FTP:n', 'sk' ); ?></p>
	                  </td>
	                </tr>
	              </tbody>
	            </table>

	            <h3><?php _e( 'Användare', 'sk' ); ?></h3>

	            <table class="form-table">
	              <tbody>
	                <tr class="option-course-import">
	                  <th scope="row"><?php _e( 'Användarnamn', 'sk' ); ?></th>
	                  <td>
	                    <input type="text" id="sk_course_import_ftp_username" name="sk_course_import_options[ftp_username]" value="<?php echo $ftp_username; ?>" />
	                    <p class="description"><?php _e( 'Ange användarnamn att använda vid anslutning till FTP', 'sk' ); ?></p>
	                  </td>
	                </tr>
	                <tr class="option-course-import">
	                  <th scope="row"><?php _e( 'Lösenord', 'sk' ); ?></th>
	                  <td>
	                    <input type="password" id="sk_course_import_ftp_password" name="sk_course_import_options[ftp_password]" value="<?php echo $ftp_password; ?>" />
	                    <p class="description"><?php _e( 'Ange lösenord att använda vid anslutning till FTP', 'sk' ); ?></p>
	                  </td>
	                </tr>
	                </tbody>
	              </table>

	              <p><input type="submit" class="button button-primary" name="Submit" value="<?php _e('Spara'); ?>" /></p>
	              <input type="hidden" name="action" value="update" />
	              <input type="hidden" name="page_options" value="sk_course_import_options" />
	          </form>

	          <br />
	          <h3>Manuell Kursimport</h3>
	          <div class="form-group">
	          	<input type="button" class="button button-secondary" value="Importera!" id="manual-course-import" />
	          	<div id="manual-import-result" style="display:none;">
	          		<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/ajax-loader.gif" />
	          	</div>
	          </div>
	      </div>
	    <?php

	  }

	}
