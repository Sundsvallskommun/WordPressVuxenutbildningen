<?php
/**
 * Displays the menu in the advanced template.
 *
 * @since 1.0.0
 * 
 * @return $menu mixed WP menu. Array or boolean depending on results.
 */
function get_advanced_template_menu() {
  global $post;
  $show_sidebar_menu = get_field( 'hide_sidebar_menu' );

  $nav_args = array(
    'theme_location' => 'main-menu',
    'container' => '',
    'walker' => new SK_Walker_Sidebar_Menu(),
    'echo' => false
  );

  $menu = $show_sidebar_menu !== false ? wp_nav_menu( $nav_args ) : false;

  return $menu;
}

/**
 * Echoes classes for the wrapper in advanced template.
 *
 * @since 1.0.0
 * 
 * @return null
 */
function the_advanced_template_classes() {
  $classes = array();
  $menu = get_advanced_template_menu();

  if ( $menu !== false ) {
    $classes[] = 'has-sidebar-menu';
  }

  if ( has_boxes( 'sidebar_boxes', 'get_field' ) ) {
    $classes[] = 'has-sidebar-boxes';
  }

  echo ! empty( $classes ) ? ' ' . implode( ' ', $classes ) : '';
}

/**
 * Represents the latest posts block in the advanced template.
 *
 * Prepares the data for use and returns it.
 *
 * @since 1.0.0
 * 
 * @return object
 */
function get_latest_posts_block() {
  $data = new stdClass;

  $data->title = get_sub_field( 'latest_posts_title' );
  $data->categories = get_sub_field( 'latest_posts_category' );
  $data->limit = get_sub_field( 'latest_posts_limit' );

  $args = array(
    'numberposts' => empty( $data->limit ) ? 5 : $data->limit
  ); 

  if ( ! empty( $data->categories ) ) {
    $args['cat'] = implode( ',', $data->categories );
  }

  $data->posts = get_posts( $args );

  foreach( $data->posts as $key => $post ) {
    setup_postdata( $post );

    $excerpt_length = has_post_thumbnail( $post->ID ) ? 65 : 100;
    $data->posts[ $key ]->excerpt = get_excerpt_max_charlength( $excerpt_length );
  }

  return $data; 
}

/**
 * Represents the latest posts block in the advanced template.
 *
 * @since 1.0.0
 * 
 * @return null
 */
function the_latest_posts_block() {
  $latest_posts = get_latest_posts_block();
  
  if ( empty( $latest_posts->posts ) ) : ?>
    <?php return; ?>
  <?php endif; ?>
  
  <div class="sk-main sk-latest-posts-block">  
    <?php if ( ! empty( $latest_posts->title ) ) : ?>
      <header>
        <h2><?php echo $latest_posts->title; ?></h2>
      </header>
    <?php endif; ?>

    
    <ul class="sk-grid-list">
      <?php foreach ( $latest_posts->posts as $post ) : ?>
        <li>
          <a class="of-dark-link" href="<?php echo get_permalink( $post->ID ); ?>">
            <?php if ( has_post_thumbnail( $post->ID ) ) : ?>
              <figure>
                <?php echo get_the_post_thumbnail( $post->ID, 'thumbnail' ); ?>
              </figure>
            <?php endif; ?>
            
            <article<?php if ( has_post_thumbnail( $post->ID ) ) : ?> class="sk-narrow"<?php endif; ?>>
              <header>
                <h5><?php echo $post->post_title; ?></h5>
              </header>
              
              <?php if ( ! empty( $post->excerpt ) ) : ?>
                <p><?php echo $post->excerpt; ?>...</p>
              <?php endif; ?>

              <ul class="of-meta-line">
                <li><?php echo get_the_time( 'j F H:i', $post->ID ); ?></li>
              </ul>
            </article>
          </a>
        </li>
      <?php endforeach; ?>      
    </ul>
  </div>
  <?php
}

/**
 * Represents the form block in the advanced template.
 *
 * @since 1.0.0
 * 
 * @return null
 */
function the_form_block() {
  global $post;
  global $flexible_index;
  $title = get_sub_field( 'form_title' ); 
  $form = get_sub_field( 'form_id' );

  if ( $form === false ) : ?>
    <?php return; ?>
  <?php endif; ?>

  <div class="sk-main sk-form-block">  
    <?php if ( ! empty( $title ) ) : ?>
      <header>
        <h2><?php echo $title; ?></h2>
      </header>
    <?php endif; ?>
    
    <?php
    //sk_enqueue_form_scripts( $form->ID );
    gravity_form_enqueue_scripts( $form['id'] );
    // gravity_form($id_or_title, $display_title=true, $display_description=true, $display_inactive=false, $field_values=null, $ajax=false, $tabindex);
    gravity_form( $form['id'] );
    ?>
  </div>
  <?php
}

/**
 * Represents the text block in the advanced template.
 *
 * @since 1.0.0
 * 
 * @return null
 */
function the_text_block() {
  global $post;
  global $flexible_index;
  $title = get_sub_field( 'text_title' );

  ?>
  <div class="sk-main">  
    <?php if ( ! empty( $title ) && $title !== get_the_title() ) : ?>
      <header>
        <h2><?php echo $title; ?></h2>
      </header>
    <?php endif; ?>

    <?php if ( get_sub_field( 'text_columns' ) == 2 ) : ?>
      <div class="sk-columns-2 of-clear">
        <div class="sk-column">
          <?php the_sub_field( 'text_content' ); ?>
        </div>

        <div class="sk-column">
          <?php the_sub_field( 'text_content_2' ); ?>
        </div>
      </div>
    <?php else : ?>
      <?php the_sub_field( 'text_content' ); ?>
    <?php endif; ?>
  </div>
  <?php
}

/**
 * Represents the slider block in the advanced template.
 *
 * @since 1.0.0
 * 
 * @return null
 */
function the_slider_block() {
  global $post;
  global $flexible_index;
  $title = get_sub_field( 'slider_title' );

  ?>
  <div class="sk-main">
    <?php if ( ! empty( $title ) ) : ?>
      <header>
        <h2><?php echo $title; ?></h2>
      </header>
    <?php endif; ?>

      <div class="owl-carousel 
        <?php if(count(get_field('slider_content')) == 1) : 
          echo 'single'; 
        else : 
          echo 'multiple'; 
      endif; ?>">

      <?php while( the_flexible_field( 'slider_content' ) ) : ?>

        <?php if ( get_row_layout() == 'slider_image' ) : ?>

          <?php
          $image_id = get_sub_field( 'slider_image_content' );
          $image = wp_get_attachment_image_src( $image_id, 'image-1136' ); 
          ?>

          <div class="item">
            <?php if ( get_sub_field( 'slider_image_url' ) ) : ?>
              <a href="<?php the_sub_field( 'slider_image_url' ); ?>">
            <?php endif; ?>

              <img class="owl-lazy" src="<?php bloginfo('template_directory'); ?>/assets/images/pixel.gif" data-src="<?php echo $image[0]; ?>" alt="<?php echo get_image_alt( $image_id ); ?>">

              <?php if ( get_sub_field( 'slider_image_text' ) ) : ?>
                <div class="wrap">
                  <div class="text"><?php the_sub_field( 'slider_image_text' ); ?></div>
                </div>
              <?php endif; ?>

            <?php if ( get_sub_field( 'slider_image_url' ) ) : ?>
              </a>
            <?php endif; ?>
          </div>

        <?php elseif ( get_row_layout() == 'slider_video' ) : ?>

          <div class="item-video">
            <a class="owl-video owl-lazy" href="<?php the_sub_field( 'slider_video_url' ); ?>">
              <?php if ( get_sub_field( 'slider_video_image' ) ) : ?>
                <?php 
                $image_id = get_sub_field( 'slider_video_image' );
                $image = wp_get_attachment_image_src( $image_id, 'image-1080' ); 
                ?>

                <img class="owl-lazy" src="<?php bloginfo('template_directory'); ?>/assets/images/pixel.gif" data-src="<?php echo $image[0]; ?>" alt="<?php echo get_image_alt( $image_id ); ?>">
              <?php endif; ?>
            </a>

            <?php if ( get_sub_field( 'slider_video_text' ) ) : ?>
              <div class="wrap-video">
                <div class="text"><?php the_sub_field('slider_video_text'); ?></div>
              </div>
            <?php endif; ?>
          </div>

        <?php endif; ?>

      <?php endwhile; ?>
    </div>
  </div>
  <?php
}

/**
 * Represents the links block in the advanced template.
 *
 * @since 1.0.0
 * 
 * @return null
 */
function the_links_block() {
  global $post;
  global $flexible_index;
  $title = get_sub_field( 'links_title' );

  ?>
  <?php if ( have_rows( 'links_repeater' ) ) : ?>
    <div class="sk-main">
      <?php if ( ! empty( $title ) ) : ?>
        <header>
          <h2><?php echo $title; ?></h2>
        </header>
      <?php endif; ?>

      <ul class="of-activity-list">
        <?php while ( have_rows( 'links_repeater' ) ) : the_row(); ?>
          <li>
            <header>
              <?php if ( get_sub_field( 'is_external' ) ) : 
                $url = get_sub_field( 'external' );
              else :
                $url = get_sub_field( 'internal' );
              endif; ?>

              <a href="<?php echo $url; ?>">
                <?php the_sub_field( 'text' ); ?>

                <?php if ( get_sub_field( 'is_external' ) ) : ?>
                  <i class="of-icon"><?php icon( 'external' ); ?></i>
                <?php endif; ?>
              </a>
            </header>

            <ul class="of-meta-line">
              <li><?php echo $url; ?></li>
            </ul>
          </li>
        <?php endwhile; ?>
      </ul>
    </div>
  <?php endif;
}

/**
 * Represents the gallery block in the advanced template.
 *
 * @since 1.0.0
 * 
 * @return null
 */
function the_gallery_block() {
  global $post;
  global $flexible_index;
  $images = get_sub_field( 'gallery_content' );
  $title = get_sub_field( 'gallery_title' );
  ?>
  <?php if( ! empty( $images ) ) : ?>
    <div class="sk-main">
      <?php if ( ! empty( $title ) ) : ?>
        <header>
          <h2><?php echo $title; ?></h2>
        </header>
      <?php endif; ?>

      <div class="sk-gallery-wrap of-clear">
        <?php foreach( $images as $image ) : ?><a href="<?php echo $image['sizes']['image-1080']; ?>" class="image" data-title="<?php echo $image['description']; ?>">
            <img src="<?php echo $image['sizes']['image-250']; ?>" alt="<?php echo $image['alt']; ?>">
          </a><?php endforeach; ?>
      </div>
    </div>
  <?php endif;
}

/**
 * Represents the boxes block in the advanced template.
 *
 * Prepares the data for use and returns it.
 *
 * @since 1.0.0
 * 
 * @return array|boolean
 */
function get_boxes_block( $field_name, $acf_function ) {
  $boxes = call_user_func($acf_function, $field_name );

  if( empty( $boxes ) ) {
    return false;
  }

  $result = array();

  foreach( $boxes as $box_id ) {
    $box = get_post( $box_id );
    $box_type = wp_get_post_terms( $box_id, 'box-type' );

    if( $box === null || empty( $box_type ) ) {
      continue;
    }

    $data = new stdClass;
    $data->post = $box;
    $data->type = $box_type[0];

    $result[] = $data;
  }

  return $result;
}

/**
 * Represents the boxes block in the advanced template.
 *
 * @since 1.0.0
 * 
 * @return null
 */
function the_boxes_block( $field_name = 'boxes_content', $acf_function = 'get_sub_field', $sidebar = false ) {
  global $post;
  global $flexible_index;
  $boxes = get_boxes_block( $field_name, $acf_function );
  $title = get_sub_field( 'boxes_title' );
  $class = $sidebar !== false ? 'sk-boxes-sidebar' : 'sk-boxes';
  ?>
  <?php if ( ! empty( $boxes ) ) : ?>
    <div class="sk-main">
      <?php if ( ! empty( $title ) ) : ?>
        <header>
          <h2><?php echo $title; ?></h2>
        </header>
      <?php endif; ?>
      
      <ul class="<?php echo $class; ?>">
        <?php foreach( $boxes as $box ) : ?>
            <li class="sk-box-type-<?php echo $box->type->slug; ?>">
              <?php if( $box->type->slug == 'lanklista' ) : ?>
                <?php the_links_box( $box ); ?>
              <?php elseif ( $box->type->slug == 'bild' ) : ?>
                <?php the_image_box( $box ); ?>
              <?php elseif ( $box->type->slug == 'text' ) : ?>
                <?php the_text_box( $box ); ?>
              <?php elseif ( $box->type->slug == 'facebook-flode' ) : ?>
                <?php the_facebook_box( $box ); ?>
              <?php elseif ( $box->type->slug == 'senaste-inlaggen' ) : ?>
                <?php the_latest_posts_box( $box ); ?>
              <?php elseif ( $box->type->slug == 'dokument' ) : ?>
                <?php the_documents_box( $box ); ?>
              <?php elseif ( $box->type->slug == 'kontaktkort' ) : ?>
                <?php the_contact_box( $box ); ?>
              <?php endif; ?>
            </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif;
}

/**
 * Checks if there are any boxes.
 *
 * @since 1.0.0
 * 
 * @return null
 */
function has_boxes( $field_name = 'boxes_content', $acf_function = 'get_sub_field' ) {
  $boxes = get_boxes_block( $field_name, $acf_function );
  return ( $boxes === false ) ? false : true;
}

/**
 * Represents the links box in the boxes block.
 *
 * @since 1.0.0
 *
 * @param object $box Box object which contains the box post and box type term.
 * 
 * @return null
 */
function the_links_box( $box ) {
  global $post;
  global $flexible_index;
  
  $title = get_field( 'box_links_title', $box->post->ID );
  ?>
  <?php if ( ! empty( $title ) ) : ?>
    <header>
      <h4><?php echo $title; ?></h4>
    </header>
  <?php endif; ?>
  
  <ul class="of-activity-list">
    <?php while( the_flexible_field( 'box_links', $box->post->ID ) ) : ?>
      <?php if ( get_row_layout() == 'internal_link' ) : ?>
         <li>
          <header>
            <a href="<?php the_sub_field( 'link' ); ?>">
              <?php the_sub_field( 'text' ); ?>
            </a>
          </header>

          <ul class="of-meta-line">
            <li><?php the_sub_field( 'link' ); ?></li>
          </ul>
        </li>
      <?php elseif ( get_row_layout() == 'external_link' ) : ?>
        <li>
          <header>
            <a href="<?php the_sub_field( 'link' ); ?>"<?php if ( get_sub_field( 'new_window' ) ) : ?> target="_blank"<?php endif; ?>>
              <?php the_sub_field( 'text' ); ?>
              <i class="of-icon"><?php icon( 'external' ); ?></i>
            </a>
          </header>

          <ul class="of-meta-line">
            <li><?php the_sub_field( 'link' ); ?></li>
          </ul>
        </li>
      <?php endif; ?>
    <?php endwhile; ?>
  </ul>
  <?php
}

/**
 * Represents the text box in the boxes block.
 *
 * Prepares the data for use and returns it.
 *
 * @since 1.0.0
 * 
 * @return object
 */
function get_text_box( $box ) {
  $data = new stdClass;

  $data->title = get_field( 'box_text_title', $box->post->ID );
  $data->content = get_field( 'box_text_content', $box->post->ID );

  return $data; 
}

/**
 * Represents the text box in the boxes block.
 *
 * @since 1.0.0
 *
 * @param object $box Box object which contains the box post and box type term.
 * 
 * @return null
 */
function the_text_box( $box ) {
  $text = get_text_box( $box ); ?>
  
  <?php if ( ! empty( $text->title ) ) : ?>
    <header>
      <h4><?php echo $text->title; ?></h4>
    </header>
  <?php endif; ?>

  <?php if ( ! empty( $text->content ) ) : ?>
    <?php echo $text->content; ?>
  <?php endif;
}

/**
 * Represents the image box in the boxes block.
 *
 * Prepares the data for use and returns it.
 *
 * @since 1.0.0
 * 
 * @return object
 */
function get_image_box( $box ) {
  $data = new stdClass;

  // Image
  $data->image_id = get_field( 'box_image_image', $box->post->ID );
  $data->image = empty( $data->image_id ) ? false : wp_get_attachment_image_src( $data->image_id, 'image-540' );
  $data->image_alt = empty( $data->image_id ) ? false : get_image_alt( $data->image_id );

  // Excerpt
  $data->content = get_field( 'box_image_content', $box->post->ID );

  // Link
  $is_external = get_field( 'box_image_is_external', $box->post->ID );
  $new_window = get_field( 'box_image_new_window', $box->post->ID );

  $data->link_is_external = ( ! empty( $is_external ) && $is_external[0]  === 'Ja' );
  $data->link_url = $data->link_is_external ? get_field( 'box_image_external_link', $box->post->ID ) : get_field( 'box_image_internal_link', $box->post->ID );
  $data->link_new_window = ( ! empty( $new_window ) && $new_window[0] === 'Ja' ) ? true : false;

  return $data; 
}

/**
 * Represents the image box in the boxes block.
 *
 * @since 1.0.0
 *
 * @param object $box Box object which contains the box post and box type term.
 * 
 * @return null
 */
function the_image_box( $box ) {
  $image = get_image_box( $box );

  if ( ! empty( $image->link_url ) ) : ?>
    <a href="<?php echo $image->link_url; ?>"<?php if( $image->link_new_window === true ) : ?> target="_blank"<?php endif; ?>>
  <?php endif; ?>

  <?php if ( $image->image !== false ) : ?>
    <img src="<?php echo $image->image[0]; ?>" alt="<?php echo $image->image_alt; ?>">
  <?php endif; ?>
  
  <?php if ( ! empty( $image->content ) ) : ?>
    <p><?php echo $image->content; ?></p>
  <?php endif; ?>

  <?php if ( ! empty( $image->link_url ) ) : ?>
    </a>
  <?php endif;
}

/**
 * Represents the contact box in the boxes block.
 *
 * Prepares the data for use and returns it.
 *
 * @since 1.0.0
 * 
 * @return object
 */
function get_contact_box( $box ) {
  $data = new stdClass;

  // Image
  $data->image_id = get_field( 'box_contact_image', $box->post->ID );
  $data->image = empty( $data->image_id ) ? false : wp_get_attachment_image_src( $data->image_id, 'image-400' );
  $data->image_alt = empty( $data->image_id ) ? false : get_image_alt( $data->image_id );

  // Name
  $data->name = get_field( 'box_contact_name', $box->post->ID );

  // Roll
  $data->role = get_field( 'box_contact_role', $box->post->ID );

  // Email
  $data->email = get_field( 'box_contact_email', $box->post->ID );

  // Phone
  $data->phone = get_field( 'box_contact_phone', $box->post->ID );

  // Mobile
  $data->mobile = get_field( 'box_contact_mobile', $box->post->ID );

  return $data; 
}

/**
 * Represents the contact box in the boxes block.
 *
 * @since 1.0.0
 *
 * @param object $box Box object which contains the box post and box type term.
 * 
 * @return null
 */
function the_contact_box( $box ) {
  $text = get_contact_box( $box );

  if ( ! empty( $text->link_url ) ) : ?>
    <a href="<?php echo $text->link_url; ?>"<?php if( $text->link_new_window === true ) : ?> target="_blank"<?php endif; ?>>
  <?php endif;

  // Show the image
  if ( $text->image !== false ) : ?>
    <img src="<?php echo $text->image[0]; ?>" alt="<?php echo $text->image_alt; ?>">
  <?php endif;

  // Show the name
  if ( ! empty( $text->name ) ) : ?>
    <h4><?php echo $text->name; ?></h4>
  <?php endif;

  // Show the role
  if ( ! empty( $text->role ) ) : ?>
    <p><?php echo $text->role; ?></p>
  <?php endif;

  // Show email
  if ( ! empty( $text->email ) ) : ?>
    <a class="of-icon" href="mailto:<?php echo $text->email; ?>">
      <i>
        <?php icon( 'mail' ); ?>
      </i>
      <span><?php echo $text->email; ?></span>
    </a>
  <?php endif;

  // Show phone and mobile
  if ( ! empty( $text->phone ) || ! empty( $text->mobile ) ) : ?>
    <p>
      <?php 
      if ( ! empty( $text->phone ) ) {
        echo $text->phone;
      }
      if ( ! empty( $text->mobile ) ) {
        echo '<br>' . $text->mobile;
      } 
      ?>
    </p>
  <?php endif;

  if ( ! empty( $text->link_url ) ) : ?>
    </a>
  <?php endif;
}

/**
 * Represents the Facebook box in the boxes block. NOT IN USE AS OF 2014-12-03.
 *
 * @since 1.0.0
 *
 * @param object $box Box object which contains the box post and box type term.
 * 
 * @return null
 */
function the_facebook_box( $box ) {
  ?>  
  <div class="facebook-likebox-wrapper">
    <?php /*<iframe style="height: 300px;" src="//www.facebook.com/plugins/likebox.php?href=<?php echo urlencode( $facebook_url ); ?>&amp;width=292&amp;height=395&amp;colorscheme=light&amp;show_faces=false&amp;header=false&amp;stream=true&amp;show_border=true" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100%; height:395px;" allowTransparency="true"></iframe>*/ ?>
    <div class="fb-like-box" data-href="<?php the_field( 'box_facebook_url', $box->post->ID ); ?> " data-width="292" data-colorscheme="light" data-show-faces="false" data-header="false" data-stream="true" data-show-border="false"></div>
  </div>
  <?php
}

/**
 * Represents the document list box in the boxes block.
 *
 * @since 1.0.0
 *
 * @param object $box Box object which contains the box post and box type term.
 * 
 * @return null
 */
function the_documents_box( $box ) {
  $title = get_field( 'box_documents_title', $box->post->ID );
  ?>
  <?php if( ! empty( $title ) ) : ?>
    <header>
      <h4>
        <span><?php echo $title; ?></span>
      </h4>
    </header>
  <?php endif; ?>

  <ul class="of-activity-list">
    <?php while( the_flexible_field( 'box_documents', $box->post->ID ) ) : ?>
      <?php $post = get_post( get_sub_field( 'box_documents_file' ) ); ?>
      <li>
        <header>
          <a href="<?php echo $post->guid; ?>" title="<?php echo $post->guid; ?>">
            <?php echo $post->post_title; ?>
          </a>
        </header>
        <ul class="of-meta-line">
          <li><?php echo get_the_time( 'j/m Y', $post->ID ); ?></li>
          <li>
            <i class="of-icon"><?php icon( 'file' ); ?></i>
            <?php echo get_file_extension( $post->guid ); ?>
          </li>
        </ul>
      </li>
    <?php endwhile; ?>
  </ul>
  <?php
}

/**
 * Represents the latest posts in the boxes block.
 *
 * Prepares the data for use and returns it.
 *
 * @since 1.0.0
 * 
 * @return object
 */
function get_latest_posts_box( $box) {
  global $post;
  $post_tmp = $post;

  $data = new stdClass;

  $data->title = get_field( 'box_latest_posts_title', $box->post->ID );
  $data->categories = get_field( 'box_latest_posts_category', $box->post->ID );
  $data->limit = get_field( 'box_latest_posts_limit', $box->post->ID );

  $args = array(
    'numberposts' => empty( $data->limit ) ? 5 : $data->limit
  ); 

  if ( ! empty( $data->categories ) ) {
    $args['cat'] = implode( ',', $data->categories );
  }

  $data->posts = get_posts( $args );


  foreach( $data->posts as $key => $post ) {
    setup_postdata( $post );

    $excerpt_length = has_post_thumbnail() ? 40 : 80;
    $data->posts[ $key ]->excerpt = get_excerpt_max_charlength( $excerpt_length );
  }

  $post = $post_tmp;

  return $data; 
}

/**
 * Represents the latest posts box in the boxes block.
 *
 * @since 1.0.0
 *
 * @param object $box Box object which contains the box post and box type term.
 * 
 * @return null
 */
function the_latest_posts_box( $box ) {
  $latest_posts = get_latest_posts_box( $box );
  
  if ( empty( $latest_posts->posts ) ) : ?>
    <?php return; ?>
  <?php endif; ?>
  <section>
    <header>
      <h4>
        <span><?php echo $latest_posts->title; ?></span>
      </h4>
    </header>

    <div class="of-inner-padded-t-half">
      <ul class="of-grid-list of-widget">
        <?php foreach ( $latest_posts->posts as $post ) : ?>
          <li>
            <a class="of-dark-link" href="<?php echo get_permalink( $post->ID ); ?>">
              <?php if ( has_post_thumbnail( $post->ID ) ) : ?>
                <figure class="of-badge-vattjom of-room of-figure-lg">
                  <?php echo get_the_post_thumbnail( $post->ID, 'thumbnail' ); ?>
                </figure>
              <?php endif; ?>
              
              <article>
                <header>
                  <h5><?php echo $post->post_title; ?></h5>
                </header>
                
                <?php if ( ! empty( $post->excerpt ) ) : ?>
                  <p><?php echo $post->excerpt; ?></p>
                <?php endif; ?>

                <ul class="of-meta-line">
                  <li><?php echo get_the_time( 'j F H:i', $post->ID ); ?></li>
                </ul>
              </article>
            </a>
          </li>
        <?php endforeach; ?>      
        </ul>
    </div>
  </section>
  <?php
}

/**
 * Represents the faq block in the advanced template.
 *
 * Prepares the data for use and returns it.
 *
 * @since 1.0.0
 * 
 * @return object
 */
function get_faq_block() {
  
  global $post;
  global $flexible_index;
  $post_tmp = $post;

  $data = new \stdClass;


  $tmp = get_field('content_block');

  //$tmp = get_sub_field('faq_block');

  $data->title = get_sub_field( 'faq_block_title' );
  $data->categories = get_sub_field( 'faq_block_category' );
  $data->limit = -1;

  $args = array(
    'numberposts' => empty( $data->limit ) ? 5 : $data->limit,
    'post_type' => 'faq',
    //'suppress_filters' => true,
  );

  if ( ! empty( $data->categories ) ) {
    //$args['category'] = join( ',', $data->categories );
  }

  $data->posts = get_posts( $args );

  foreach( $data->posts as $key => $post ) {
    setup_postdata( $post );

    $excerpt_length = has_post_thumbnail() ? 65 : 100;
    $data->posts[ $key ]->excerpt = get_excerpt_max_charlength( $excerpt_length );
    $data->posts[ $key ]->answer = get_field('svar');
    $data->posts[ $key ]->references = get_field('referenser');

  }

  $post = $post_tmp;

  return $data; 
}

/**
 * Represents the faq block in the advanced template.
 *
 * @since 1.0.0
 * 
 * @return null
 */
function the_faq_block() {

  global $post;
  global $flexible_index;
  
  $faq_posts = get_faq_block();

  $title = get_field( 'faq_title', $post->ID );
  
  if ( empty( $faq_posts->posts ) ) : ?>
    <?php return; ?>
  <?php endif; ?>

  
  <div class="sk-main sk-faq-posts-block">
    <?php if ( ! empty( $faq_posts->title ) ) : ?>
      <header>
        <h2><?php echo $faq_posts->title; ?></h2>
      </header>
    <?php endif; ?>

    <div class="box-filter-search">
      <label for="searchFilter"><?php _e( 'Filtrera på fråga:', 'sk-theme' ); ?></label><input type="text" name="searchFilter" id="searchFilter" class="searchFilter form-control" value="" />
    </div>
    
    <ul class="sk-faq-list">
      <?php foreach ( $faq_posts->posts as $faq_post ) : ?>
        <li>
          <!--<a class="of-dark-link" href="<?php echo get_permalink( $post->ID ); ?>"> -->
            <?php if ( has_post_thumbnail( $faq_post->ID ) ) : ?>
              <figure>
                <?php echo get_the_post_thumbnail( $faq_post->ID, 'thumbnail' ); ?>
              </figure>
            <?php endif; ?>
    
            <article<?php if ( has_post_thumbnail( $faq_post->ID ) ) : ?> class="sk-narrow"<?php endif; ?>>
              <table class="of-table box-qa-answers" cellpadding="0" cellspacing="0">
              <thead>
              <tr>
                <th>
                  <header>
                    <h5 class="of-icon"> <i>
                        <svg viewBox="0 0 512 512">
                          <use xlink:href="#posts"></use>
                        </svg>
                      </i>
                      <span><?php echo $faq_post->post_title; ?></span></h5>
                  </header>
                </th>
              </tr>
              </thead>
              <tbody>
                <tr>
                  <td data-of-tr="Header #1">
                    <?php if ( ! empty( $faq_post->answer) ) : ?>
                <p><?php echo $faq_post->answer; ?></p>
              <?php endif; ?>

              
              
                </tr>
                 <tr>
                <td data-of-tr="Header #1">
                  <?php if ( ! empty( $faq_post->references) ) : ?>
                
                <?php _e('Relaterat:', 'sk'); ?>
                <?php foreach( $faq_post->references as $reference ) : ?>
                  
                  <a href="<?php the_permalink( $reference->ID ); ?>"><?php echo $reference->post_title; ?> </a>
                  
                  
                <?php endforeach; ?>
                
              <?php endif; ?></td>
                </td>
                
              </tr>
              </tbody>
            </table>
              
            </article>
          <!--</a> -->
        </li>
      <?php endforeach; ?>      
    </ul>
  </div>

  <?php
}