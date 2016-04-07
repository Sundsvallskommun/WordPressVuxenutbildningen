<?php
get_header();

$nav_args = array(
  'theme_location' => 'main-menu',
  'container' => '',
  //'container' => 'nav',
  //'container_class' => 'of-sidebar-menu-advanced js-mobile-menu',
  'walker' => new SK_Walker_Sidebar_Menu(),
  'echo' => false
);

$menu = get_field( 'hide_sidebar_menu' ) !== false ? wp_nav_menu( $nav_args ) : false;
$classes = array();

if ( $menu !== false ) {
	$classes[] = 'has-sidebar-menu';
}

if ( has_boxes( 'sidebar_boxes', 'get_field' ) ) {
	$classes[] = 'has-sidebar-boxes';
}

?>
<div class="of-wrap<?php echo ! empty( $classes ) ? ' ' . implode( ' ', $classes ) : ''; ?>">
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
          <?php // edit_post_link( __( 'Redigera den här sidan', 'sk' ), '<p>', '</p>' ); ?>

    			<div class="sk-entry-content">
            <?php if ( get_field( 'show_h1' ) === true ) : ?>
              <h1><?php the_title(); ?></h1>
            <?php endif; ?>
            
    				<?php the_content(); ?>
    			</div>

          <!-- Page Owner -->
          <?php the_page_owner(); ?>

          <?php // edit_post_link( __( 'Redigera den här sidan', 'sk' ), '<p>', '</p>' ); ?>
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