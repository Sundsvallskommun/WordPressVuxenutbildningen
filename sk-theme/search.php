<?php
/*
Template Name: Sökresultat
*/
?>
<?php get_header(); ?>

<div class="of-wrap">
  <div class="of-inner-padded-t sk-main-padded">
    <?php if(have_posts()) :

    // $posts = filter_attachments( $posts );
    // $wp_query->post_count = count( $posts );
    ?>
      <h3 class=""><?php printf( __( '"%s" gav %d träffar', 'sk' ), get_search_query(), $wp_query->found_posts); ?></h3>
      
      <ul class="of-post-list">
          <?php while ( have_posts() ) : the_post(); ?>
            <li>
              <header>
                <?php if ( has_post_thumbnail() ) : ?>
                  <?php 
                  $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id(), 'thumbnail' );
                  $alt = get_post_thumbnail_alt(); 
                  ?>
                  <figure class="of-profile">
                    <a href="<?php the_permalink(); ?>">
                      <img src="<?php echo $thumbnail[0]; ?>" alt="<?php echo $alt; ?>">
                    </a>
                  </figure>
                <?php endif; ?>

                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                
                <?php if ( '' == $post->post_excerpt ): ?>
                  <?php the_custom_field_excerpt( 'content_block' ); ?>
                <?php else: ?>
                  <?php the_excerpt(); ?>
                <?php endif; ?>
                
                <ul class="of-meta-line">
                  <li><?php the_time('j F Y H:i'); ?></li>
                </ul>
              </header>
            </li>
          <?php endwhile; // end of the loop. ?>
      </ul>

      <div class="nav-previous alignright"><?php next_posts_link( __( 'Nästa sida &raquo;', 'sk' ) ); ?></div>
      <div class="nav-next alignleft"><?php previous_posts_link( __( '&laquo; Föregående sida', 'sk' ) ); ?></div>
    <?php else : ?>
        <h3 class=""><?php printf( __( '"%s" gav tyvärr %d träffar', 'sk' ), get_search_query(), $wp_query->found_posts); ?></h3>
        <?php get_search_form(); ?>
    <?php endif; ?>
  </div>
</div>

<?php get_footer();