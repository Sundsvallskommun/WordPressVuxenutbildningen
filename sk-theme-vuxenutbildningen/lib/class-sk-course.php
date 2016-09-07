<?php
/**
 * Class for register post types.
 *
 * 2015-08-10 - DS - Added some restrictions for none admins to manage custom taxonomy "kurskategorier". 
 * 
 */

//namespace SKChildTheme;

/**
 * Custom post types.
 *
 * Register theme specific post types and taxonomies.
 * 
 * @since 1.0.0
 *
 * @package sk-theme-vuxenutbildning
 */


class SK_Course {

  private $manual_mode = false;
	
  public function __construct() {
    //add_action( 'wp', array( $this, 'set_manual_mode' ) );
    add_action( 'init', array( $this, 'register_post_types' ) );
    add_action( 'admin_init', array( $this, 'remove_submenu_kurskategorier' ) );

    add_action( 'admin_head', array( $this, 'admin_course_head' ) );
    add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
    add_action( 'save_post_kurs', array( $this, 'save_post_course' ), 99, 2 );
	}

  /**
   * Remove submenu for taxonomy kurskategorier
   *
   * @since 1.0.0
   *  
   * @return none
   */
  public function remove_submenu_kurskategorier() {
      // remove only for none administrators
      if(! current_user_can( 'activate_plugins' ) ){
        remove_submenu_page( 'edit.php?post_type=kurs', 'edit-tags.php?taxonomy=kurskategorier&amp;post_type=kurs' );
      }
  }


  /**
   * Register custom post types and taxonomies.
   *
   * @since 1.0.0
   *
   * @return null
   */
	public function register_post_types() {
		// Register post types and taxonomies here
    $this->post_type_course();
	}


  /**
   * Method for register post type kurs
   *
   * @since 1.0.0
   * 
   * @return none
   */
  private function post_type_course() {

    $labels = array(
      'name'               => _x( 'Kurs', 'Kurs', 'sk' ),
      'singular_name'      => _x( 'Kurs', 'kurs', 'sk' ),
      'menu_name'          => _x( 'Kurser', 'admin menu', 'sk' ),
      'name_admin_bar'     => _x( 'Kurs', 'add new on admin bar', 'sk' ),
      'add_new'            => _x( 'Skapa ny kurs', 'kurs', 'sk' ),
      'add_new_item'       => __( 'Skapa ny kurs', 'sk' ),
      'new_item'           => __( 'Ny kurs', 'sk' ),
      'edit_item'          => __( 'Redigera kurs', 'sk' ),
      'view_item'          => __( 'Visa kurs', 'sk' ),
      'all_items'          => __( 'Alla kurser', 'sk' ),
      'search_items'       => __( 'Sök bland kurser', 'sk' ),
      'parent_item_colon'  => __( 'Förälderkurs:', 'sk' ),
      'not_found'          => __( 'Hittade inga kurser.', 'sk' ),
      'not_found_in_trash' => __( 'Hittade inga kurser i papperskorgen.', 'sk' )
    );
    
    $args = array(
      'labels'             => $labels,
      'public'             => true,
      'publicly_queryable' => true,
      'show_ui'            => true,
      'show_in_menu'       => true,
      'query_var'          => true,
      'rewrite'            => array( 'slug' => 'kurs' ),
      'capability_type'    => 'post',
      'map_meta_cap'       => true,
      'has_archive'        => false,
      'hierarchical'       => false,
      'menu_position'      => null,
      'menu_icon'          => 'dashicons-book-alt',
      'supports'           => array( 'title', 'editor' )
    );

    register_post_type( 'kurs', $args );


    // FAQ category labels
    $category_labels = array(
      'name'              => __( 'Kurskategorier', 'sk' ),
      'singular_name'     => __( 'Kurskategori', 'sk' ),
      'search_items'      => __( 'Sök kurskategori', 'sk' ),
      'all_items'         => __( 'Alla kurskategorier', 'sk' ),
      'parent_item'       => __( 'Förälder, kategori', 'sk' ),
      'parent_item_colon' => __( 'Kategoriförälder:', 'sk' ),
      'edit_item'         => __( 'Ändra kategori', 'sk' ),
      'update_item'       => __( 'Uppdatera kategori', 'sk' ),
      'add_new_item'      => __( 'Ny kategori', 'sk' ),
      'new_item_name'     => __( 'Nytt kategorinamn', 'sk' ),
      'menu_name'         => __( 'Kurskategorier', 'sk' )
    );
    
    // disable edit for this term for none administrators
    $edit_terms = true;
    if(! current_user_can( 'activate_plugins' ) ){
      $edit_terms = false;
    }

    $category_args = array(
      'labels'            => $category_labels,
      'hierarchical'      => true,
      'query_var'         => 'true',
      'rewrite'           => array('slug' => 'kurser'),
      'show_admin_column' => true,
      'show_ui'           => true,
      'capabilities'      => array( 'edit_terms' => $edit_terms ),
    );

    register_taxonomy( 'kurskategorier', 'kurs', $category_args );

  }

  /**
   * Add meta box for course
   *
   * @since    1.0.0
   * 
   * @return none
   */
  public function add_meta_boxes(){
    global $post_id;

    //remove_meta_box( 'kurskategorierdiv', 'kurs', 'side');

    $screen = get_current_screen();

    if( $screen->post_type != 'kurs')
      return false;
    
    $type = get_post_meta( $post_id, 'skolform', true );
    
    if( empty( $type ) || $type === 'YH' )
      $this->manual_mode = true;
    
    if( !$this->manual_mode == true )
      return false;


    add_meta_box( 'sk-course-meta', __( 'Kursdata', 'sk' ), array( $this, 'course_meta_box_callback' ), 'kurs', 'normal', 'high' );
    //add_meta_box( 'cc-survey-result', __( 'Resultat', 'sk' ), array( $this, 'survey_meta_box_result_callback' ), 'survey', 'normal', 'low' );   
  }

  /**
   * Adding styles in admin for course meta box
   * 
   * @author Daniel Söderström <daniel.soderstrom@cybercom.com>
   * 
   * @return echo 
   */
  public function admin_course_head(){
    ?>
    <style type="text/css">
      #sk-course-meta .block{
        float: left;
        width: 100%;
      }

      #sk-course-meta span.desc{
        font-style: italic;
      }
    </style>

    <?php if( ! $this->manual_mode == true ) : ?>
      <script type="text/javascript">
        jQuery(document).ready( function( $ ) {
          $('#acf-group_56bde87e26166').hide();
        });
      </script>
    <?php endif; ?>


    <?php

  }


  public function course_meta_box_callback(){
    global $post;
    $post_id = $post->ID;
    
    ?>
    <p><b><?php _e( 'Följande fält används enbart till att skapa manuella kurser gällande Yrkeshögskoleutbildningar.', 'sk' ); ?></b></p>
    <hr />
      <p>
        <?php _e( 'Skolform:', 'sk' ); ?> YH
      </p>
      <p>
        <label class="block" for="sk_poang"><?php _e( 'Poäng:', 'sk' ); ?></label>
        <input id="sk_poang" type="number" class="regular-text" name="poang" value="<?php echo get_post_meta( $post_id, 'poang', true ); ?>">
        <span class="desc"><?php _e( 'Endast siffror', 'sk' ); ?></span>
      </p>

      <p>
        <label for="sk_studieform"><?php _e( 'Studieform:', 'sk' ); ?></label>
        <textarea cols="60" rows="2" name="kurskategori" id="sk_studieform" class="widefat"><?php echo get_post_meta( $post_id, 'kurskategori', true ); ?></textarea>
        <span class="desc"><?php _e( 'Tex: Dagtid, Distans', 'sk' ); ?></span>
      </p>

      <p>
        <label for="sk_forkunskap"><?php _e( 'Förkunskap:', 'sk' ); ?></label>
        <textarea cols="60" rows="4" name="forkunskap" id="sk_forkunskap" class="widefat"><?php echo get_post_meta( $post_id, 'forkunskap', true ); ?></textarea>
      </p>

      <p>
        <label for="included_courses_for_yh"><?php _e( 'Inkluderande kurser:', 'sk' ); ?> </label>
        <textarea cols="60" rows="4" name="included_courses_for_yh" id="included_courses_for_yh" class="widefat"><?php echo get_post_meta( $post_id, 'included_courses_for_yh', true ); ?></textarea>
      </p>
    
    <?php
      wp_nonce_field( plugin_basename( __FILE__ ), 'course_meta_box_nonce' ); 

  }

  public function save_post_course( $post_id ){
    // Bail if we're doing an auto save  
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return; 

    // if our nonce isn't there, or we can't verify it, bail 
    if( !isset( $_POST['course_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['course_meta_box_nonce'], plugin_basename( __FILE__ ) ) ) return; 

    // if our current user can't edit this post, bail  
    if( !current_user_can( 'edit_post' ) ) return;  


    $type = get_post_meta( $post_id, 'skolform', true );
    if( ! (empty( $type ) || $type === 'YH' ) )
      return false;
    
    $fields = array();
    $fields['skolform']                 = 'YH';
    $fields['kurspaket']                = 'true';
    $fields['poang']                    = $_POST['poang'];
    $fields['kurskategori']             = $_POST['kurskategori'];
    $fields['forkunskap']               = $_POST['forkunskap'];
    $fields['included_courses_for_yh']  = $_POST['included_courses_for_yh'];

    foreach( $fields as $field => $value ) {
    
      $new_meta = $value;
      $old_meta = get_post_meta( $post_id, $field, true );
        
      if( $new_meta && $new_meta != $old_meta ){
        update_post_meta( $post_id, $field, $new_meta );
      } elseif( $old_meta && $new_meta == '' ) {
        delete_post_meta( $post_id, $field, $old_meta );
      }          

    }

    if(isset( $_POST['tax_input']['kurskategorier'] )){
      $posted_terms = $_POST['tax_input']['kurskategorier'];  
    }
      
    $term = get_term_by('slug', 'yrkesinriktade-utbildningar', 'kurskategorier');
    $posted_terms[] = $term->term_id;

    wp_set_post_terms( $post_id, $posted_terms, 'kurskategorier' );



    // update is searchable
    $courses = get_field( 'sk_course_starts', $post_id );

    $today = date('Y-m-d');
    $searchable_flag = false;   
    if(!empty( $courses )){
      foreach ($courses as $data ) {
        if( ( strtotime( $today ) >= strtotime( $data['searchable_from'] ) ) &&  ( strtotime( $today ) <= strtotime( $data['searchable_to'] ) ) ){
          $searchable_flag = true;
        }
      }
    }
  
    if( $searchable_flag === true ){
      update_post_meta( $post_id, 'is_searchable', 'true' );
    }else{
      update_post_meta( $post_id, 'is_searchable', 'false' );
    }


  }


  static public function get_yh_course_starts(){
    global $post;
    $courses = get_field( 'sk_course_starts', $post->ID );

    $_courses = array();

    if( !empty( $courses )){
      foreach( $courses as $key => $value ) {
        $_courses[$key]['sokbar']     = $value['searchable_from'];
        $_courses[$key]['sokbarTill'] = $value['searchable_to'];
        $_courses[$key]['datum']      = $value['start'];
        $_courses[$key]['ort']        = $value['city'];
        $_courses[$key]['url']        = $value['url_to_register'];
      }
    }


    return $_courses;

  }

  public static function sort_by_column( &$array, $column, $direction = SORT_ASC ) {
      $reference_array = array();

      foreach($array as $key => $row) {
        $reference_array[$key] = $row[$column];
      }

      array_multisort($reference_array, $direction, $array);
  }

}