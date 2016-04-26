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

		private $taxonomy = 'kurskategorier';
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

			// Add a link to menu for editor and admins to set import options.
			add_action( 'admin_menu', array( $this, 'add_course_import_options' ) );

    	add_action( 'wp_ajax_manual_course_import', array( $this, 'manual_import' ) );

			// Use this one only on local tests. It forces a run on every page update.
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
     * Update posts that are not deletet for courses
     * 
     * @author Daniel Söderström <daniel.soderstrom@cybercom.com>
     * 
     * @return none
     */
    private function update_posts(){
			global $wpdb;

			// Update posts where not in skolform YH
			// update the is_searchable meta
    	$sql = "SELECT ID FROM $wpdb->posts WHERE post_type = 'kurs' AND post_status ='publish'"; 
			$results = $wpdb->get_results( $sql );
			$today = date_i18n('Y-m-d');
			
			if(!empty( $results ) ){
				foreach ( $results as $post ) {
					$courses = get_field( 'sk_course_starts', $post->ID );

					$searchable_flag = false;		
					if(!empty( $courses )){
						foreach ($courses as $data ) {
							if( ( strtotime( $today ) >= strtotime( $data['searchable_from'] ) ) &&  ( strtotime( $today ) <= strtotime( $data['searchable_to'] ) ) ){
								$searchable_flag = true;
							}
						}
					}
				
					if( $searchable_flag === true ){
						update_post_meta( $post->ID, 'is_searchable', 'true' );
					}else{
						update_post_meta( $post->ID, 'is_searchable', 'false' );
					}

				}
			}
    }

    /**
     * Delete all posts and postmeta in post type kurs
     *
     * @since 1.0.0 
     * 
     * @return none
     * 
     */
    private function delete_posts(){
    	global $wpdb;

    	// Delete post meta where not in skolform YH.
    	$sql = "DELETE FROM $wpdb->postmeta WHERE post_id IN (
				 	SELECT * FROM ( 
				 		SELECT $wpdb->posts.ID FROM $wpdb->posts
				    	WHERE post_type = 'kurs' AND post_status = 'publish'
					) as P
				) AND ( meta_key = 'skolform' AND meta_value <> 'YH' );
			";

			// Delete posts where not in skolform YH
    	$sql = "DELETE post FROM $wpdb->posts AS post 
    	LEFT JOIN $wpdb->postmeta AS meta ON post.ID = meta.post_id 
    	WHERE meta.meta_key = 'skolform' AND meta.meta_value <> 'YH'
    	AND post.post_type = 'kurs';";
			$wpdb->query( $sql );

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

			// xml exists, delete option and update with new values from xml
			delete_option( 'vuxenutbildning_categorized_terms' );

			// delete posts and insert all as new from xml.
			self::delete_posts();

			// update posts that are left in database
			self::update_posts();

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

			$this->remove_unused_terms();

			// Import ran smoothly
			update_option( 'course_import_status', 'OK - ' . date_i18n( 'Y-m-d H:i:s' ) );


			if( $this->is_manual_import ) {

				return array(
					'result' => true,
					'message' => 'OK',
					'num_courses' => $this->num_courses,
					'import_status' => get_option( 'course_import_status' )
				);

			}

		}
		/**
		 * Set YH courses searchable flag
		 *  
		 * @author Daniel Söderström <daniel.soderstrom@cybercom.com>
		 * 
		 * @return [type]
		 */
		public function after_import(){

		}

		/**
		 * Remove terms that arent connected to any course, remove empty terms.
		 * 
		 * @return [type] [description]
		 */
		public function remove_unused_terms(){

			$terms = get_terms( $this->taxonomy, array( 'hide_empty' => false ) );

			if( !empty( $terms ) ){
				foreach( $terms as $term ){
						// remove term if empty
						if( $term->count == 0 ){
							if( wp_delete_term( $term->term_id, $this->taxonomy ) ){
								// place for insert a log	when needed
							}
						}
				}
			}

		}


		/**
		 * Get the xml file from the ftp server
		 * 
		 * @return bool|string $xml_content
		 */
		public function get_xml() {

			// Remove this in production
			/*
			if( file_exists( get_stylesheet_directory() . '/alvis/alvis_2016_02.xml' ) ) {
				return file_get_contents( get_stylesheet_directory() . '/alvis/alvis_2016_02.xml' );
			}
			*/
			
			
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



			// we dont want do import skolform = YH. 
			// https://trello.com/c/hihBrZDn/4-2-importera-ej-yh-utbildningar-fran-alvis
			if( $course_xml_object->skolform == 'YH' )
				return false;


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
		 * Get included courses for kurspaket.
		 *
		 * @since 1.0.0 
		 * 
		 * @return 
		 */
		private function set_included_courses( $post_id = false, $included_courses ){	
			if( empty( $post_id ) || empty( $included_courses ) )
				return false;

			$include = array();
			$i = 0;
			foreach ( $included_courses as $course ) {
				$include[$i]['name'] 		= (string) $course->namn;
				$include[$i]['code'] 		= (string) $course->kurskod;
				$include[$i]['points'] 	= (string) $course->poäng;
				$include[$i]['url'] 		= (string) $course->skolverketurl;
				$i++;
			}

			if(! empty( $include ) )
				update_post_meta( $post_id, 'included_courses', $include );

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

			$today = date_i18n('Y-m-d H:i:s');

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

			// save included courses if this is a kurspaket
			if( $course->attributes()->kurspaket[0] == 'true' ){
				$this->set_included_courses( $post_id, $course->delkurs );
			}

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

			// need to sort course start dates because there is no default ordering in xml, first array is not always the closest date.
			usort($course_starts, array($this, 'sortFunction' ) );			

			if( isset( $course_starts[0]['datum'] ) ) {
				update_post_meta( $post_id, 'nearest_start_date', strtotime( $course_starts[0]['datum'] ) );
			}

			
			// check if there is a course that is searchable
			$searchable_flag = false;
			foreach( $course_starts as $data ){
				if( ( strtotime( $today ) >= strtotime( $data['sokbar'] ) ) &&  ( strtotime( $today ) <= strtotime( $data['sokbarTill'] ) ) )
					$searchable_flag = true;

			}

			// update post meta
			if( $searchable_flag === true ){
				update_post_meta( $post_id, 'is_searchable', 'true' );
			}else{
				update_post_meta( $post_id, 'is_searchable', 'false' );
			}

			update_post_meta( $post_id, 'kursstarter', $course_starts );

		}


function sortFunction( $a, $b ) {
  return strtotime( $b["datum"] ) - strtotime( $a["datum"] );
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
				'GY' => 'Gymnasienivå',
				'GR' => 'Grundskola',
				'LV' => 'Lärvux',
				'SI' => 'SFI',
				'YH' => 'Yrkeshögskola',
			);

			$collected_terms = get_option( 'vuxenutbildning_categorized_terms', array() );

			if( ! isset( $collected_terms['studieform'] ) ) {
				$collected_terms['studieform'] = array();
			}

			$course_type = $this->mb_ucfirst( mb_strtolower( (string) $course->kurskategori ), 'utf-8' );
			if( ! in_array( $course_type, $collected_terms['studieform'] ) ) array_push( $collected_terms['studieform'], $course_type );

			if( ! isset( $collected_terms['niva'] ) ) {
				$collected_terms['niva'] = array();
			}

			// Add education level to collected terms array
			$level = $this->mb_ucfirst( mb_strtolower( $level_array[(string) $course->skolform] ), 'utf-8' );
			if( ! in_array( $level, $collected_terms['niva'] ) ) array_push( $collected_terms['niva'], $level );

	
			// Add couse category to collected terms array
			$terms[] = $this->mb_ucfirst( mb_strtolower( (string) $course->ämnesområde ), 'utf-8' );
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

					if( !in_array( (string) $this->mb_ucfirst( mb_strtolower( $course_start->ort ), 'utf-8' ), $city_terms ) ) {

						array_push( $terms, (string) $this->mb_ucfirst( mb_strtolower( $course_start->ort ), 'utf-8' ) );

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

				
				// update post content with hidden data to be searchable like alvis.
				$post = get_post( $post_id );
			 	$content = $post->post_content;

			 	// print post categories into content in hidden element to be searchable in free search.
			 	$hidden_meta = '<!-- searchable meta --> <div class="no-print" style="display:none">';
			 	foreach( array_unique( $terms_array ) as $term_id ){
			 		$term = get_term( $term_id, $this->taxonomy );
			 		$hidden_meta .= $term->name . ' ';
			 	}
			 	$hidden_meta .= '</div><!-- end searchable meta-->';

			 	//arguments
			 	$post_data = array(
			    'ID'           => $post_id,
			    'post_content' => $content . $hidden_meta
			  );

				//update the post into the database
			 	wp_update_post( $post_data );
			
			update_option( 'vuxenutbildning_categorized_terms', $collected_terms );

		}

		/**
		 * mb_ucfirst to capitilaze first multi byte character
		 *
		 * @since 1.0.0
		 * 
		 * @param  string     $string
		 * @param  string     $encoding (utf-8, latin1)
		 * 
		 * @return string 
		 */
		private function mb_ucfirst( $string, $encoding ){
    	$strlen = mb_strlen($string, $encoding);
    	$firstChar = mb_substr($string, 0, 1, $encoding);
    	$then = mb_substr($string, 1, $strlen - 1, $encoding);

    	return mb_strtoupper($firstChar, $encoding) . $then;
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

	    add_options_page( __( 'Inställningar för Alvis kursimport', 'sk' ), __( 'Kursimport', 'sk' ), 'edit_pages', 'sk_course_import_options', array( $this, 'course_import_options') );

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
		              	<p><?php printf( __('Senaste importen: <span id="latest-import-time">%s</span>', 'sk' ), get_option( 'course_import_status' ) ); ?></p>
		              	<p><?php printf( __('Nästa automatiska import sker: %s', 'sk' ), $next_run ); ?></p>
		              </td>
		            </tr>
		          </tbody>
		        </table>	
		        <?php if( current_user_can( 'activate_plugins' ) ) : ?>          
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
						<?php endif; ?>
	          
	          <h3>Manuell Kursimport</h3>
	          <p><?php _e('Observera att import av kurser kan ta upp till flera minuter att genomföra.', 'sk'); ?></p>
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
