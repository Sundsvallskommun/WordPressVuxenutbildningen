<?php get_header(); ?>

<div class="of-wrap">
  <div class="sk-main-padded of-inner-padded-t of-clear">

    <div class="of-c-sm-4 of-c-md-3">
      <?php while ( have_posts() ) : the_post(); ?>
        <?php // edit_post_link( __( 'Redigera den här sidan', 'sk' ), '<p>', '</p>' ); ?>
        <h1><?php the_title(); ?></h1>
        <div class="of-inner-padded-b-half">
          <ul class="of-meta-line">
            <li><?php the_time('j F Y H:i'); ?></li>
            <li>Publicerat av: <?php the_author(); ?></li>
          </ul>
        </div>
        <?php if ( has_post_thumbnail() ) : ?>
          <?php 
          $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id(), 'image-1000' );
          $alt = get_post_meta( get_post_thumbnail_id( $post->ID ), '_wp_attachment_image_alt', true ); 
          ?>
          <figure class="sk-featured-image-single">
            <img src="<?php echo $thumbnail[0]; ?>" alt="<?php echo $alt; ?>">
          </figure>
        <?php endif; ?>

        <div class="sk-entry-content">
          <?php the_content(); ?>
          <?php // edit_post_link( __( 'Redigera den här sidan', 'sk' ), '<p>', '</p>' ); ?>
        </div>
      <?php endwhile; // end of the loop. ?>
    </div>
    <?php get_sidebar( 'archive' ); ?>
  </div>
</div>
<?php get_footer(); ?>