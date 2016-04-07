<?php
/**
 * The template for displaying 404 pages (Not Found).
 */

get_header(); ?>

<div class="of-wrap">
  <div class="sk-main-padded of-inner-padded-t">
      <header class="entry-header">
        <h1 class="entry-title"><?php _e( '404 Sidan kan inte hittas', 'sk' ); ?></h1>
      </header>
    <p><?php _e( 'Tyvärr, vi kunde inte hitta sidan du letade efter.', 'sk' ); ?></p>
  </div>
</div>

<?php get_footer(); ?>