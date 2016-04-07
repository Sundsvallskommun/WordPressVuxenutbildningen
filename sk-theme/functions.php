<?php
/**
 * Theme functions
 *
 * @since 1.0.0
 *
 * @package sk-theme
 */

/**
 * Helpers.
 */
require_once locate_template( '/lib/helpers/advanced-template.php' );
require_once locate_template( '/lib/helpers/general-template.php' );

/**
 * Theme basics and structure.
 */
require_once locate_template( '/lib/class-sk-init.php' );
$osynlig_init = new SK_Init();

require_once locate_template( '/lib/class-sk-ajax.php' );
$osynlig_ajax = new SK_Ajax();

require_once locate_template( '/lib/class-sk-post-types.php' );
$osynlig_post_types = new SK_Post_Types();

require_once locate_template( '/lib/class-sk-menus.php' );
$osynlig_menus = new SK_Menus();

require_once locate_template( '/lib/class-sk-comments.php' );
$osynlig_comments = new SK_Comments();

require_once locate_template( '/lib/class-sk-walker-sidebar-menu.php' );
require_once locate_template( '/lib/class-sk-walker-top-menu.php' );

function mce_add_buttons( $buttons ){
    array_splice( $buttons, 1, 0, 'styleselect' );
    return $buttons;
}
add_filter( 'mce_buttons_2', 'mce_add_buttons' );