<?php
/**
 * General theme settings.
 *
 * Controls settings like image sizes, what files that can be uploaded, etc.
 *
 * @since 1.0.0
 *
 * @package sk-theme
 */
define( 'PRODUCTION_MODE', true );
define( 'COOKIE_WARNING', false );

class SK_Init {
	public function __construct() {

		/* 
		 * Add the image sizes that we need.
		 */
		add_image_size( 'image-250', 250, 250, true );
		add_image_size( 'image-280', 280, 280, true );
		add_image_size( 'image-400', 400, 400, true );
		add_image_size( 'image-540', 540, 405, true );
		add_image_size( 'image-1000', 1000, 1000, false );
		add_image_size( 'image-1080', 1080, 810, true );
		add_image_size( 'image-1136', 1136, 600, true );

		// Actions and filters
		add_action( 'init', array( &$this, 'init' ) );
		add_action( 'after_setup_theme', array( &$this, 'after_setup_theme' ) );
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'login_head', array( &$this, 'login_head' ) );
		add_action( 'generate_rewrite_rules', array( &$this, 'generate_rewrite_rules' ) );
		add_action( 'after_setup_theme', array( &$this, 'add_editor_styles' ) );
		add_action( 'wp_loaded', array( &$this, 'wp_loaded' ) );
		add_filter( 'tiny_mce_before_init', array( &$this, 'tinymce_custom_format' ) );
		//add_filter( 'acf/fields/wysiwyg/toolbars' , array( &$this, 'tinymce_acf_custom_format' ) );
		add_filter( 'intermediate_image_sizes_advanced', array( &$this, 'filter_image_sizes' ) );
		add_filter( 'image_size_names_choose', array( &$this, 'image_size_name' ) );
		add_action( 'init', array( &$this, 'options_page' ) );
		add_filter( 'the_content', array( &$this, 'add_of_element_class' ) );
		add_filter( 'acf_the_content', array( &$this, 'add_of_element_class' ) );
		add_filter( 'user_has_cap', array( &$this, 'sk_give_edit_theme_options' ) );
		add_filter( 'wp_nav_menu', array( &$this, 'wp_nav_menu' ), 10, 2 );
		add_filter( 'wp_nav_menu_items', array( &$this, 'wp_nav_menu_items' ), 10, 2 );
		add_filter( 'wp_nav_menu_objects', array( &$this, 'wp_nav_menu_objects' ), 10, 2 );
		//add_filter( 'acf/settings/show_admin', '__return_false' ); // Hide ACF fields from menu
		add_filter( 'gform_init_scripts_footer', array( &$this, 'gform_init_scripts_footer' ) );
		add_filter( 'gform_cdata_open', array( &$this, 'gform_cdata_open' ) );
		add_filter( 'gform_cdata_close', array( &$this, 'gform_cdata_close' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
	}

	/**
	 * Filter initialization on Wordpress init.
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	public function init() {
		add_filter( 'sanitize_file_name', array( &$this, 'sluggify' ), 10 );
		add_filter( 'upload_mimes', array( &$this, 'custom_upload_mimes' ) );
		
		$this->cleanup_wp_head();
	}

	/**
	 * Expands access to menus for editors
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	public function sk_give_edit_theme_options( $caps ) {
		/* check if the user has the edit_pages capability */
		if ( ! empty( $caps[ 'edit_pages' ] ) ) {
			
			/* give the user the edit theme options capability */
			$caps[ 'edit_theme_options' ] = true;
		}
		
		/* return the modified capabilities */
		return $caps;
	}

	/**
	 * Custom options pages positon.
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	public function options_page() {
		if ( function_exists( 'acf_add_options_page' ) ) {

			acf_add_options_page( array(
					'page_title' 	=> 'Webbplatsen',
					'menu_title'	=> 'Webbplatsen',
					'menu_slug' 	=> 'general-settings',
					'parent_slug'	=> '',
					//'capability'	=> 'manage_options',
					'redirect'		=> true,
					'position'		=> '59,5'
				) 
			);

			$default_option_sub_pages = array(
				array(
					'page_title' 	=> 'Tema',
					'menu_title'	=> 'Tema',
					//'capability'	=> 'manage_options',
					'parent_slug'	=> 'general-settings',
				),
				array(
					'page_title' 	=> 'Sidhuvud',
					'menu_title'	=> 'Sidhuvud',
					//'capability'	=> 'manage_options',
					'parent_slug'	=> 'general-settings',
				),
				array(
					'page_title' 	=> 'Sidfot',
					'menu_title'	=> 'Sidfot',
					//'capability'	=> 'manage_options',
					'parent_slug'	=> 'general-settings'
				)
			);

			// Put this through filter to be able to change it
			$default_option_sub_pages = apply_filters( 'sk_default_option_sub_pages', $default_option_sub_pages );
				
			if(! empty( $default_option_sub_pages ) ){
				foreach ( $default_option_sub_pages as $option_page ) {
					acf_add_options_sub_page( $option_page );
				}
			}

		}
	}

	/**
	 * Add OF classes to elements
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	public function add_of_element_class( $content ) {
		// Ul - Only replace lists without classes because of Gravity Forms, etc.
	  $content = str_replace( '<ul>', '<ul class="sk-list">', $content );

	  if( !empty( $content ) ) {
	  	
	  	// Convert encoding since DOMDocuments loadHTML expects ISO-8859-1
	  	$content = mb_convert_encoding( $content, 'ISO-8859-1', 'UTF-8' );
	    
	    // Table
	    $doc = new DOMDocument();

	    // You should be able to use LIBXML_HTML_NOIMPLIED to remove html and body elements, but there is a bug that moves around elements.
	    //$doc->loadHTML( $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD ); 
	    $doc->loadHTML( $content, LIBXML_HTML_NODEFDTD ); 

	    foreach( $doc->getElementsByTagName( 'table' ) as $tag ) {
	      $tag->setAttribute( 'class', 'of-table of-table-even-odd' );
	    }

	    // Fixes the LIBXML_HTML_NOIMPLIED error. Quite ugly.
	    $content = str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $doc->saveHTML());

	    $content = mb_convert_encoding( $content, 'UTF-8', 'HTML-ENTITIES' );

	  }

	  return $content;
	}

	/**
	 * Define what the theme should support.
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	public function after_setup_theme() {		
		add_theme_support( 'post-thumbnails' );
	}

	/**
	 * Save disk space by removing the 'large' image size.
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	public function filter_image_sizes( $sizes ) {
		unset( $sizes['large'] );

		return $sizes;
	}

	/**
	 * Save disk space by removing the 'large' image size.
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	public function image_size_name( $sizes ) {
		unset( $sizes['large'] );
		unset( $sizes['medium'] );
		// unset( $sizes['thumbnail'] );

		$sizes['image-250'] = 'Anpassad';
		$sizes['image-280'] = 'Anpassad';
		$sizes['image-400'] = 'Anpassad';
		$sizes['image-540'] = 'Anpassad';
		$sizes['image-1000'] = 'Anpassad';
		$sizes['image-1080'] = 'Anpassad';
		$sizes['image-1136'] = 'Anpassad';

		return $sizes;
	}

	/**
	 * Remove media options page.
	 *
	 * The page isn't used and only confuses the administrators.
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	public function admin_menu() {
		remove_submenu_page( 'options-general.php', 'options-media.php' );
	}
	
	/**
	 * Add a nice login head image.
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	public function login_head() {
		?>
		<style>
			#login h1 { background: url('<?php echo get_template_directory_uri(); ?>/assets/images/login-logo.png') no-repeat top center; }
			#login h1 a { background: none; }
			
			@media only screen and (-webkit-min-device-pixel-ratio: 2), only screen and (min--moz-device-pixel-ratio: 2), only screen and (-o-min-device-pixel-ratio: 2 / 1), only screen and (min-resolution: 192dpi), only screen and (min-resolution: 2dppx) {
				#login h1 { background: url('<?php echo get_template_directory_uri(); ?>/assets/images/login-logo@2x.png') no-repeat top center; background-size: 320px auto; }
			}
		</style>
		<?php
	}

	/**
	 * Fix post slug.
	 *
	 * Only accept a-z0-9 in post slugs.
	 *
	 * @since 1.0.0
	 *
	 * @param string $filename
	 * @param boolean $file
	 *
	 * @return null
	 */
	public function sluggify( $filename, $file = true ) {
		if ( false !== $file ) {
			$info = pathinfo( $filename );  
			$ext  = empty( $info['extension'] ) ? '' : '.' . $info['extension'];
			$name = basename( $filename, $ext );
			$name = str_replace( $ext, '', $name );
		}
		else {
			$name = $filename;
		}

		$name = trim( strtolower( preg_replace( '/([^\w]|-)+/', '-', trim( strtr( str_replace( '\'', '', trim( $name ) ), array(
				'À'=>'A','Á'=>'A','Â'=>'A','Ã'=>'A','Å'=>'A','Ä'=>'A','Æ'=>'AE',
				'à'=>'a','á'=>'a','â'=>'a','ã'=>'a','å'=>'a','ä'=>'a','æ'=>'ae',
				'Þ'=>'B','þ'=>'b','Č'=>'C','Ć'=>'C','Ç'=>'C','č'=>'c','ć'=>'c',
				'ç'=>'c','Ď'=>'D','ð'=>'d','ď'=>'d','Đ'=>'Dj','đ'=>'dj','È'=>'E',
				'É'=>'E','Ê'=>'E','Ë'=>'E','è'=>'e','é'=>'e','ê'=>'e','ë'=>'e',
				'Ì'=>'I','Í'=>'I','Î'=>'I','Ï'=>'I','ì'=>'i','í'=>'i','î'=>'i',
				'ï'=>'i','Ľ'=>'L','ľ'=>'l','Ñ'=>'N','Ň'=>'N','ñ'=>'n','ň'=>'n',
				'Ò'=>'O','Ó'=>'O','Ô'=>'O','Õ'=>'O','Ø'=>'O','Ö'=>'O','Œ'=>'OE',
				'ð'=>'o','ò'=>'o','ó'=>'o','ô'=>'o','õ'=>'o','ö'=>'o','œ'=>'oe',
				'ø'=>'o','Ŕ'=>'R','Ř'=>'R','ŕ'=>'r','ř'=>'r','Š'=>'S','š'=>'s',
				'ß'=>'ss','Ť'=>'T','ť'=>'t','Ù'=>'U','Ú'=>'U','Û'=>'U','Ü'=>'U',
				'Ů'=>'U','ù'=>'u','ú'=>'u','û'=>'u','ü'=>'u','ů'=>'u','Ý'=>'Y',
				'Ÿ'=>'Y','ý'=>'y','ý'=>'y','ÿ'=>'y','Ž'=>'Z','ž'=>'z', '^'=>'',
				'¨'=>'', '´'=>'', '`'=>'', '"' => ''
				) ) ) ) ) );

		return $file !== false ? $name . $ext : $name;
	}
	
	/**
	 * Disable support for certain mime types.
	 *
	 * We never wan't certain image types to be uploaded.
	 *
	 * @since 1.0.0
	 *
	 * @param array $existing_mimes Default mime types.
	 *
	 * @return null
	 */
	public function custom_upload_mimes( $existing_mimes = array() ) {
		unset( $existing_mimes['bmp'] );
		unset( $existing_mimes['tif|tiff'] );

		return $existing_mimes;
	}

	/**
	 * Action for adding HTML5 Boilerplate .htaccess.
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	public function generate_rewrite_rules() {
		$this->add_h5bp_htaccess();
	}

	/**
	 * Add HTML5 Boilerplate .htaccess.
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	private function add_h5bp_htaccess() {
		global $wp_rewrite;

		$home_path = function_exists( 'get_home_path' ) ? get_home_path() : ABSPATH;
		$htaccess_file = $home_path . '.htaccess';
		$mod_rewrite_enabled = function_exists( 'got_mod_rewrite' ) ? got_mod_rewrite() : false;

		if ( ( ! file_exists( $htaccess_file ) && is_writable( $home_path ) && $wp_rewrite->using_mod_rewrite_permalinks() ) || is_writable( $htaccess_file ) ) {
			if ( $mod_rewrite_enabled ) {
				$h5bp_rules = extract_from_markers( $htaccess_file, 'HTML5 Boilerplate' );
				if ( $h5bp_rules === array() ) {
					$filename = dirname( __FILE__ ) .'/assets/h5bp-htaccess';
					return insert_with_markers( $htaccess_file, 'HTML5 Boilerplate', extract_from_markers( $filename, 'HTML5 Boilerplate' ) );
				}
			}
		}
	}

	/**
	 * Remove unwanted head meta.
	 *
	 * For example Wordpress-version. Originally from
	 * http://wpengineer.com/1438/wordpress-header/.
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	public function cleanup_wp_head() {
		remove_action( 'wp_head', 'feed_links', 2 );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );

		add_filter( 'use_default_gallery_style', '__return_null' );
	}

	/**
	 * WYSIWYG editor styles.
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	public function add_editor_styles() {
    add_editor_style( 'editor-style.css' );
	}

	/**
	 * Automatically add default box types (once).
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	public function wp_loaded() {
		$check = get_option( 'sk_theme_activation' );

		if ( $check === true ) {
			return;
		}

		$default_box_types = array(
			//'Facebook-flöde',
			'RSS-flöde',
			'Bild',
			'Text',
			'Länklista',
			'Senaste inläggen',
			'Dokument',
			'Kontaktkort'
		);

		// Filter for adding box types
		$default_box_types = apply_filters('sk_default_box_types', $default_box_types );

		foreach ( $default_box_types as $default_box_type ) {
			$asd = wp_insert_term(
				$default_box_type,
				'box-type'
			);
		}

		// Add marker so it doesn't run in future.
		add_option( 'sk_theme_activation', true );
	}

	/**
	 * Custom formatting for TinyMCE.
	 *
	 * @since 1.0.0
	 *
	 * @param array $init_array
	 * 
	 * @return null
	 */
	public function tinymce_custom_format( $init_array ) {
	  $style_formats = array(  
	    array(  
	      'title' => 'Ingress',  
	      'block' => 'p',  
	      'classes' => 'of-paragraph-ingress',
	      'wrapper' => false
	    )
	  ); 

	  $init_array['style_formats'] = json_encode( $style_formats );  

	  $removeFromSecond = array( 'format', 'underline' );
	  $toolbar2 = explode( ',', $init_array['toolbar2'] );

	  foreach( $removeFromSecond as $r ) {
	  	$key = array_search( $r, $toolbar2);

	  	//unset( $toolbar2[$key] );
	  }

	  //$init_array['block_formats'] = 'Stycke=p; Ingress=p; Rubrik 2=h2; Rubrik 3=h3; Rubrik 4=h4';

	  $init_array['toolbar1'] = str_replace( 'italic', 'italic,underline', $init_array['toolbar1'] );
	  $init_array['toolbar2'] = implode( ',', $toolbar2 );

	  $init_array['style_formats_merge'] = false;

	  return $init_array;
	}

	/**
	 * Custom formatting for the ACF WYSIYG.
	 *
	 * @since 1.0.0
	 *
	 * @param array $init_array
	 * 
	 * @return null
	 */
	/*public function tinymce_acf_custom_format( $init_array ) {

		// http://www.advancedcustomfields.com/resources/customize-the-wysiwyg-toolbars/

		$toolbars['Very Simple' ] = array();
		$toolbars['Very Simple' ][1] = array('bold' , 'italic' , 'underline' );

		if( ! is_null( $toolbars['Full' ][2] ) && ($key = array_search( 'code' , $toolbars['Full' ][2])) !== false ) {
			unset( $toolbars[ 'Full' ][2][$key] );
		}

		unset( $toolbars['Basic' ] );

		return $toolbars;
		return $init_array;
	}*/

	/**
	 * of-sidebar-menu-advanced requires an inner container for smooth scrolling.
	 *
	 * @since 1.0.0
	 *
	 * @param string $nav_menu The menu.
	 * @param array $args wp_nav_menu() args.
	 * 
	 * @return string $nav_menu
	 */
	public function wp_nav_menu( $nav_menu, $args ) {
		$nav_menu = '<nav class="of-sidebar-menu-advanced js-mobile-menu"><div class="of-sidebar-inner">'. $nav_menu;
		$nav_menu .= '</div></nav>';

		return $nav_menu;
	}

	/**
	 * Always add a menu item for closing the menu.
	 *
	 * @since 1.0.0
	 *
	 * @param array $items
	 * @param array $args
	 * 
	 * @return array $items
	 */
	public function wp_nav_menu_items( $items, $args ) {
		if ( empty( $items ) ) {
			return false;
		}

		$top_bar = '<li class="sk-top-bar"></li>';
		$close = '<li class="of-item-icon of-menu-toggle"><a href="#">'. __( 'Stäng meny', 'sk' ) .' <span class="of-icon of-icon-only of-absolute-right"><i>'. __icon( 'close' ) .'</i></span></a></li>';

		$items = $close . $items; // prepend
		//$items .= $close; // append
		
		return $items;
	}

	/**
	 * Remove menu items that isn't part of the current page tree (submenu)
	 *
	 * @since 1.0.0
	 *
	 * @param array $items
	 * @param array $args
	 * 
	 * @return array $items
	 */
	public function wp_nav_menu_objects( $items, $args ) {
		global $post;

		$parents = array();
		$unset = true;
		$should_return_false_count = 0;
		$to_unset = array();

		if ( ( is_object($args) && ! ( $args->walker instanceof SK_Walker_Sidebar_Menu ) ) || ( is_array($args) && ! ( $args['walker'] instanceof SK_Walker_Sidebar_Menu ) ) ) {
			return $items;
		}

		foreach ( $items as $key => $item ) {
			if ( intval( $item->menu_item_parent ) === 0 && $post->ID === intval( $item->object_id ) && ! in_array( 'menu-item-has-children', $item->classes ) ) {
				$should_return_false_count++;
				continue;
			}

			if ( ( intval( $item->menu_item_parent ) === 0 && $item->current_item_ancestor !== true && intval( $item->object_id ) !== $post->ID ) ) {
				$to_unset[$key] = $item->ID;
			}

			if( in_array( $item->menu_item_parent, $to_unset ) ) {
				$to_unset[$key] = $item->ID;
			}

			if ( intval( $item->menu_item_parent ) === 0 && $post->ID === intval( $item->object_id )  && ! in_array( 'menu-item-has-children', $item->classes ) ) {
				// $unset = false;
			}
			else {
				if ( $post->ID === intval( $item->object_id ) && in_array( 'menu-item-has-children', $item->classes ) || $item->current_item_parent !== false ) {
					$item->classes[] = 'sk-open';
					$item->classes[] = 'of-expanded';
					$should_return_false_count++;
				}
			}
		}

		if ( $unset !== false ) {
			foreach( $to_unset as $key => $val ) {
				unset( $items[ $key ] );
			}
		}

		if ( $should_return_false_count === 0 ) {
			return false;
		}
		
		return $items;    
	}

	/**
	 * Tell Gravity Forms to initialize form scripts in the footer.
	 *
	 * @since 1.0.0
	 * 
	 * @return boolean
	 */
	public function gform_init_scripts_footer() {
		return true;
	}

	/**
	 * Wrap Gravity Forms jQuery inline request in a DOMContentLoaded. Opening tag.
	 *
	 * @since 1.0.0
	 * 
	 * @return boolean
	 */
	public function gform_cdata_open( $content = '' ) {
		$content = 'document.addEventListener( "DOMContentLoaded", function() { ';
		return $content;
	}

	/**
	 * Wrap Gravity Forms jQuery inline request in a DOMContentLoaded. Closing tag.
	 *
	 * @since 1.0.0
	 * 
	 * @return boolean
	 */
	public function gform_cdata_close( $content = '' ) {
		$content = ' }, false );';
		return $content;
	}

	/**
	 * Gravity Forms for editors.
	 *
	 * @since 1.0.0
	 * 
	 * @return null
	 */
	public function admin_init() {
		$role = get_role( 'editor' );
		$role->add_cap( 'gform_full_access' );
	}
}