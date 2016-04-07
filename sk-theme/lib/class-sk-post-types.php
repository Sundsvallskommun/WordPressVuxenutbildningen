<?php
/**
 * Custom post types.
 *
 * Register theme specific post types and taxonomies.
 *
 * @since 1.0.0
 *
 * @package sk-theme
 */
class SK_Post_Types {
	public function __construct() {
		add_action('init', array(&$this, 'register_post_types'));
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

    // $this->post_type_name();
    // $this->taxonomy_name();
    // ...

    $this->post_type_boxes();
    $this->post_type_faq(); // Register the post type FAQ and corresponding taxonomy
    $this->taxonomy_box_type();
	}

  /**
   * Custom post type boxes
   *
   * @since 1.0.0
   * @access private
   *
   * @return null
   */
  private function post_type_boxes() {
    $labels = array(
      'name'               => _x( 'Puffar', 'boxes', 'sk' ),
      'singular_name'      => _x( 'Puff', 'box', 'sk' ),
      'menu_name'          => _x( 'Puffar', 'admin menu', 'sk' ),
      'name_admin_bar'     => _x( 'Puff', 'add new on admin bar', 'sk' ),
      'add_new'            => _x( 'Skapa ny', 'box', 'sk' ),
      'add_new_item'       => __( 'Skapa ny puff', 'sk' ),
      'new_item'           => __( 'Ny puff', 'sk' ),
      'edit_item'          => __( 'Redigera puff', 'sk' ),
      'view_item'          => __( 'Visa puff', 'sk' ),
      'all_items'          => __( 'Alla puffar', 'sk' ),
      'search_items'       => __( 'Sök bland puffar', 'sk' ),
      'parent_item_colon'  => __( 'Förälderpuff:', 'sk' ),
      'not_found'          => __( 'Hittade inga puffar.', 'sk' ),
      'not_found_in_trash' => __( 'Hittade inga puffar i papperskorgen.', 'sk' )
    );

    $args = array(
      'labels'             => $labels,
      'public'             => false,
      'publicly_queryable' => false,
      'show_ui'            => true,
      'show_in_menu'       => true,
      'query_var'          => true,
      'rewrite'            => array( 'slug' => 'puff' ),
      'capability_type'    => 'post',
      'has_archive'        => false,
      'hierarchical'       => false,
      'menu_position'      => null,
      'menu_icon'          => 'dashicons-forms',
      'supports'           => array( 'title', 'author', 'revisions' ),
      'exclude_from_search' => true
    );

    register_post_type( 'boxes', $args );
  }


  /**
   * Custom post type faq
   *
   * @since 1.0.0
   * @access private
   *
   * @return null
   */
  private function post_type_faq() {

    $labels = array(
      'name'               => _x( 'Faq', 'Faq', 'sk' ),
      'singular_name'      => _x( 'Faq', 'faq', 'sk' ),
      'menu_name'          => _x( 'Faq', 'admin menu', 'sk' ),
      'name_admin_bar'     => _x( 'Faq', 'add new on admin bar', 'sk' ),
      'add_new'            => _x( 'Skapa ny fråga', 'faq', 'sk' ),
      'add_new_item'       => __( 'Skapa ny fråga', 'sk' ),
      'new_item'           => __( 'Ny fråga', 'sk' ),
      'edit_item'          => __( 'Redigera fråga', 'sk' ),
      'view_item'          => __( 'Visa fråga', 'sk' ),
      'all_items'          => __( 'Alla frågor', 'sk' ),
      'search_items'       => __( 'Sök bland frågor', 'sk' ),
      'parent_item_colon'  => __( 'Förälderfaq:', 'sk' ),
      'not_found'          => __( 'Hittade inga frågor.', 'sk' ),
      'not_found_in_trash' => __( 'Hittade inga frågot i papperskorgen.', 'sk' )
    );

    $args = array(
      'labels'             => $labels,
      'public'             => true,
      'publicly_queryable' => true,
      'show_ui'            => true,
      'show_in_menu'       => true,
      'query_var'          => true,
      'rewrite'            => array( 'slug' => 'faq' ),
      'capability_type'    => 'post',
      'has_archive'        => false,
      'hierarchical'       => false,
      'menu_position'      => null,
      'menu_icon'          => 'dashicons-search',
      'supports'           => array( 'title', 'author' ),
      'exclude_from_search' => true
    );

    register_post_type( 'faq', $args );


    // FAQ category labels
    $category_labels = array(
      'name'              => __( 'Faq kategorier', 'sk' ),
      'singular_name'     => __( 'Faq kategori', 'sk' ),
      'search_items'      => __( 'Sök faq kategori', 'sk' ),
      'all_items'         => __( 'Alla faq kategorier', 'sk' ),
      'parent_item'       => __( 'Förälder, kategori', 'sk' ),
      'parent_item_colon' => __( 'Kategoriförälder:', 'sk' ),
      'edit_item'         => __( 'Ändra kategori', 'sk' ),
      'update_item'       => __( 'Uppdatera kategori', 'sk' ),
      'add_new_item'      => __( 'Ny kategori', 'sk' ),
      'new_item_name'     => __( 'Nytt kategorinamn', 'sk' ),
      'menu_name'         => __( 'Faqkategorier', 'sk' )
    );

    $category_args = array(
      'labels' => $category_labels,
      'hierarchical' => true,
      'query_var' => 'true',
      'rewrite' => array('slug' => 'vanliga-fragor-faq'),
      'show_admin_column' => 'true'
    );

    register_taxonomy( 'faqkategorier', 'faq', $category_args );

  }


   /**
   * Custom taxonomy Box type
   *
   * @since 1.0.0
   * @access private
   *
   * @return null
   */
  private function taxonomy_box_type() {
    $labels = array(
      'name'              => _x( 'Typ av puff', 'box-types', 'sk' ),
      'singular_name'     => _x( 'Typ av puff', 'box-type', 'sk' ),
      'search_items'      => __( 'Sök bland pufftyper', 'sk' ),
      'all_items'         => __( 'Alla pufftyper', 'sk' ),
      'parent_item'       => __( 'Föräldertyp', 'sk' ),
      'parent_item_colon' => __( 'Föräldertyp:', 'sk' ),
      'edit_item'         => __( 'Redigera pufftyp', 'sk' ),
      'update_item'       => __( 'Uppdatera pufftyp', 'sk' ),
      'add_new_item'      => __( 'Lägg till ny', 'sk' ),
      'new_item_name'     => __( 'Nytt namn på pufftyp', 'sk' ),
      'menu_name'         => __( 'Typ av puff', 'sk' ),
    );

    $args = array(
      'hierarchical'      => true,
      'capabilities'      => array(
        'manage_terms'  => 'manage_options',
        'edit_terms'    => 'manage_options',
        'delete_terms'  => 'manage_options',
        'assign_terms'  => 'edit_posts'
      ),
      'labels'            => $labels,
      'show_ui'           => true,
      'show_admin_column' => true,
      'query_var'         => true,
      'rewrite'           => array( 'slug' => 'box-type' ),
    );

    register_taxonomy( 'box-type', array( 'boxes' ), $args );
  }
}