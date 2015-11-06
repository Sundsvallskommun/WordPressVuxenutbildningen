<?php
/**
 * Class for register post types.
 *
 * 2015-08-10 - DS - Added some restrictions for none admins to manage custom taxonomy "kurskategorier". 
 * 
 */

namespace SKChildTheme;

/**
 * Custom post types.
 *
 * Register theme specific post types and taxonomies.
 * 
 * @since 1.0.0
 *
 * @package sk-theme-vuxenutbildning
 */


class SK_Post_Types {
	
  public function __construct() {
    add_action( 'init', array( $this, 'register_post_types' ) );
    add_action( 'admin_init', array( $this, 'remove_submenu_kurskategorier' ) );
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

    // disable publish post for this cpt for none administrators
    $cpt_cap = array('capabilities' => array( 'create_posts' => false ) );
    if( current_user_can( 'activate_plugins' ) ){
      $cpt_cap = array('capabilities' => array( '' ) );  
    }
    
    $args = array(
      'labels'             => $labels,
      'public'             => true,
      'publicly_queryable' => true,
      'show_ui'            => true,
      'show_in_menu'       => true,
      'query_var'          => true,
      'rewrite'            => array( 'slug' => 'kurs' ),
      'capability_type'    => 'post',
      'capabilities'       => $cpt_cap['capabilities'],
      'map_meta_cap'       => true,
      'has_archive'        => false,
      'hierarchical'       => false,
      'menu_position'      => null,
      'menu_icon'          => 'dashicons-book-alt',
      'supports'           => array( 'title', 'editor', 'thumbnail' )
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

}