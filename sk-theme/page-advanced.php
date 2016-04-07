<?php
/**
 * Template Name: Avancerad mall
 * Description: Advanced page template
 */

get_header();

$menu = get_advanced_template_menu();
?>
<div class="of-wrap<?php the_advanced_template_classes(); ?>">
  <div class="sk-main-padded of-inner-padded-t">
    <?php if ( $menu !== false ): ?>
      <div class="of-c-lg-fixed-2 of-c-xl-fixed-2">
        <div class="of-c-sm-fixed-4 of-c-md-fixed-1 of-c-lg-fixed-2 of-hide-to-lg">
          <?php echo $menu; ?>
        </div>
      </div>
      <div class="of-c-lg-flexible-10 of-c-xl-flexible-10 of-omega">
    <?php endif; ?>
    
    <?php while ( have_posts() ) : the_post(); ?>
      <div class="<?php if ( has_boxes( 'sidebar_boxes', 'get_field' ) ) : ?>of-c-sm-4 of-inner-padded-r of-c-md-4 of-c-lg-12 of-c-xl-flexible-10<?php else : ?>of-c-sm-4<?php endif; ?> of-omega">
        <div class="sk-entry-content">
          <?php if ( get_field( 'show_h1' ) === true ) : ?>
            <h1><?php the_title(); ?></h1>
          <?php endif; ?>
          
          <?php // edit_post_link( __( 'Redigera den här sidan', 'sk' ), '<p>', '</p>' ); ?>

          <?php while ( the_flexible_field( 'content_block' ) ) : ?>

            <?php if ( get_row_layout() == 'text_block' ) : ?>

              <?php the_text_block(); ?>

            <?php elseif ( get_row_layout() == 'links_block' ) : ?>

              <?php the_links_block(); ?>

            <?php elseif ( get_row_layout() == 'slider_block' ) : ?>

              <?php the_slider_block(); ?>

            <?php elseif ( get_row_layout() == 'gallery_block' ) : ?>

              <?php the_gallery_block(); ?>

            <?php elseif ( get_row_layout() == 'boxes_block' ) : ?>

              <?php the_boxes_block(); ?>

            <?php elseif ( get_row_layout() == 'latest_posts_block' ) : ?>

              <?php the_latest_posts_block(); ?>
            
            <?php elseif ( get_row_layout() == 'form_block' ) : ?>

              <?php the_form_block(); ?>

            <?php elseif ( get_row_layout() == 'contact_block' ) : ?>

              <?php the_contact_block(); ?>

            <?php elseif ( get_row_layout() == 'faq_block' ) : ?>
              
              <?php the_faq_block(); ?>

            <?php endif; ?>

          <?php endwhile; ?>

          <?php // edit_post_link( __( 'Redigera den här sidan', 'sk' ), '<p>', '</p>' ); ?>
        </div>
      </div>
    <?php endwhile; // end of the loop. ?>
    
    <?php if ( has_boxes( 'sidebar_boxes', 'get_field' ) ) : ?>
      <div class="of-c-sm-4 of-c-md-4 of-c-lg-12 of-c-xl-fixed-2 of-omega sk-sidebar">
        <?php the_boxes_block( 'sidebar_boxes', 'get_field' ); ?>
      </div>
    <?php endif; ?>

    <?php if ( $menu !== false ): ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php get_footer(); ?>