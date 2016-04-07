<?php
/**
 * Get Wordpress base directory.
 *
 * @since 1.0.0
 * 
 * @return null
 */
function wp_base_dir() {
  preg_match( '!(https?://[^/|"]+)([^"]+)?!', site_url(), $matches );
  
  if ( count( $matches ) === 3 ) {
    return end( $matches );
  }
  
  return '';
}

/**
 * Outputs icon markup for icon from icons.svg.
 *
 * @since 1.0.0
 *
 * @param string      $icon   Icon name.
 * @param (int|array) $size   Optional. Integer if square, array(w, h) if not
 *
 * @return null
 */
function icon( $icon, $size = 512 ) {
  echo __icon( $icon, $size );
}

/**
 * Generate icon markup for icon from icons.svg.
 *
 * @since 1.0.0
 *
 * @param string      $icon   Icon name.
 * @param (int|array) $size   Optional. Integer if square, array(w, h) if not
 *
 * @return null
 */
function __icon( $icon, $size = 512 ) {
  $size = ( is_array($size) ) ? $size[0] .' '. $size[1] : $size .' '. $size;

  ob_start();
  ?>
  <svg viewBox="0 0 <?php echo $size; ?>">
    <use xlink:href="#<?php echo $icon; ?>"></use>
  </svg>
  <?php
  
  $svg = ob_get_contents();
  ob_end_clean();

  return $svg;
}

/**
 * Get file extension.
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID
 *
 * @return string
 */
function get_file_extension( $string ) {

  return substr( strrchr( $string, '.' ), 1 );
}

/**
 * Get post thumbnail alternative text.
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID
 *
 * @return string
 */
function get_post_thumbnail_alt( $post_id = null ) {
  if ( $post_id == null ) {
    global $post;
    $post_id = $post->ID;
  }

  $thumbnail_id = get_post_thumbnail_id( $post_id );

  if( empty( $thumbnail_id ) ) {
    return false;
  }

  return get_image_alt( $thumbnail_id );
}

/**
 * Get image alternative text.
 *
 * @since 1.0.0
 *
 * @param int $image_od Image post ID
 *
 * @return string
 */
function get_image_alt( $image_id ) {
  return get_post_meta( $image_id, '_wp_attachment_image_alt', true );
}

/**
 * Use get_the_excerpt() to return an excerpt by specifying a maximium number of characters.
 *
 * @since 1.0.0
 *
 * @param int $charlength Number of characters to output.
 *
 * @return string
 */
function get_excerpt_max_charlength( $charlength ) {
  $excerpt = get_the_excerpt();

  $charlength++;

  if ( mb_strlen( $excerpt ) > $charlength ) {
    $subex = mb_substr( $excerpt, 0, $charlength - 5 );
    $exwords = explode( ' ', $subex );
    $excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );

    if ( $excut < 0 ) {
      return mb_substr( $subex, 0, $excut );
    } else {
      return $subex;
    }

    return '...';
  }
  else {
    return $excerpt;
  }
}

/**
 * Store form_id's that needs to output script/css in the footer. Used in the_forms_block.
 *
 * @since 1.0.0
 *
 * @param int $form_id
 *
 * @return boolean
 */
/*function sk_enqueue_form_scripts( $form_id ) {
  global $sk_form_scripts;

  if ( $sk_form_scripts === null ) {
    $sk_form_scripts = array();
  }

  $sk_form_scripts[] = $form_id;
}*/

/**
 * Output scripts for forms that are used in the_forms_block.
 *
 * @since 1.0.0
 *
 * @return null
 */
/*function sk_form_scripts( ) {
  global $sk_form_scripts;

  if ( empty( $sk_form_scripts ) ) {
    return;
  }

  foreach ( $sk_form_scripts as $form_id ) {
    gravity_form_enqueue_scripts( $form_id, true );
  }
}*/

/**
 * Extract excerpt for advanced themed posts
 *
 * @since 1.0.1
 *
 * @param sting $field What field to retrieve
 *
 * @return string
 */
function get_custom_field_excerpt( $field ) {
  global $post;

  $data = get_field( $field );

  if ( ! is_array( $data ) ) {
    return false;
  }

  $text = array_map( function( $array ) {
    return isset( $array['text_content'] ) ? $array['text_content'] : false;
  }, $data );

  $text = implode( PHP_EOL, $text );

  if ( '' != $text ) {
    $text = strip_shortcodes( $text );
    $text = apply_filters( 'the_content', $text );
    $text = str_replace( ']]>', ']]>', $text );
    $excerpt_length = has_post_thumbnail( $post->ID ) ? 65 : 100;
    $excerpt_more = apply_filters( 'excerpt_more', ' ' . '[...]' );
    $text = wp_trim_words( $text, $excerpt_length, $excerpt_more );
  }
  return apply_filters( 'the_excerpt', $text );
}

function filter_attachments( $posts ) {

  foreach( $posts as &$post ) {

    if ( $post->post_type !== 'attachment' ) {
      continue;
    }

    if ( strrpos( $post->post_mime_type, 'image/' ) !== false ) {
      $post = null;
    }

  }

  $posts = array_filter( $posts );

  return array_values( $posts );
}

/**
 * Extract excerpt for advanced themed posts
 *
 * @since 1.0.1
 *
 * @param sting $field What field to retrieve
 *
 */
function the_custom_field_excerpt( $field ) {
  echo get_custom_field_excerpt( $field );
}

/**
 * Get the responsible owner of the page
 * This is a ACF field.
 *
 * @since 1.0.0
 *
 * @param int $post_id
 *
 * @return string | null
 */
function get_page_owner( $post_id ) {
  if( $page_owner = get_field( 'sidansvarig', $post_id ) ) return $page_owner;

  return null;
}

/**
 * Write the html output for the page owner.
 *
 * @since  1.0.0
 * 
 * @return html
 */
function the_page_owner() {
  global $post;

  if( $page_owner = get_field( 'sidansvarig', $post->ID ) ) : 
    $name = !empty( $page_owner['user_firstname'] ) ? trim( $page_owner['user_firstname'] ) : '';
    $name .= ' ' . trim( !empty( $page_owner['user_lastname'] ) ? trim( $page_owner['user_lastname'] ) : '' );
    $email = !empty( $page_owner['user_email'] ) ? $page_owner['user_email'] : '';
    ?>
    
    <div class="page-owner">
      
      <div class="title">
        <?php _e('Sidansvarig', 'sk'); ?>
      </div>

      <div class="name">
        <?php echo $name ?>
      </div>

      <div class="email">
        <a class="of-icon" href="mailto:<?php echo $email; ?>">
          <i>
            <?php icon( 'mail' ); ?>
          </i>
          <span><?php echo _e('Skicka mail', 'sk'); ?></span>
        </a>
      </div>
      
    </div>
  <?php endif;
}