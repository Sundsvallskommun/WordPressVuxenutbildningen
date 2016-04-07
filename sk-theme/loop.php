<?php if(have_posts()) : ?>

  <ul class="of-post-list">
    <?php while ( have_posts() ) : the_post(); ?>
      
      <li>
        <header>
          <?php if ( has_post_thumbnail() ) : ?>
            <?php 
            $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id(), 'thumbnail' );
            $alt = get_post_meta(get_post_thumbnail_id($post->ID), '_wp_attachment_image_alt', true); 
            ?>
            <figure class="of-profile">
              <a href="<?php the_permalink(); ?>">
                <img src="<?php echo $thumbnail[0]; ?>" alt="<?php echo $alt; ?>">
              </a>
            </figure>
          <?php endif; ?>
          <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <?php the_excerpt(); ?>
          <ul class="of-meta-line">
            <li><?php the_time('j F Y H:i'); ?></li>
          </ul>
        </header>
      </li>

    <?php endwhile; // end of the loop. ?>
  </ul>  

  <div class="nav-previous alignleft"><?php next_posts_link( 'Äldre inlägg' ); ?></div>
  <div class="nav-next alignright"><?php previous_posts_link( 'Nyare inlägg' ); ?></div>

<?php endif; ?>