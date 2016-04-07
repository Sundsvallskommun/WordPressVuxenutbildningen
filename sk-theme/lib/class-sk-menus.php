<?php
/**
 * Theme menu registration.
 *
 * @since 1.0.0
 *
 * @package sk-theme
 */
class SK_Menus {
  public function __construct() {
    add_action( 'init', array( &$this, 'register_nav_menus' ) );
    add_filter( 'nav_menu_css_class', array( &$this, 'nav_menu_css_class' ), 10, 2 );
  }

  /**
   * Theme menus.
   *
   * @since 1.0.0
   * 
   * @return null
   */
  public function register_nav_menus() {
    register_nav_menus( array(
      'main-menu' => 'Huvudmeny',
    ) );
  }

  /**
   * All menus list items should have theese classes.
   *
   * @since 1.0.0
   *
   * @param $classes Nav menu item classes.
   * @param $item Nav menu item data object.
   * 
   * @return array $classes
   */
  public function nav_menu_css_class($classes, $item) {
    if( empty( $item->classes ) ) {
      return $item->classes;
    }

    if ( in_array( 'current-menu-ancestor', $item->classes ) ) {
      $classes[] = 'of-active-ascendant';
    }

    if ( in_array( 'current-menu-item', $item->classes ) ) {
      $classes[] = 'of-active sk-open of-expanded';
    }

    if ( in_array( 'menu-item-has-children', $item->classes ) ) {
      $classes[] = 'of-has-children';
    }

    return $classes;
  }
}