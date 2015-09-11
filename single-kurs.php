<?php

/**
 * @todo  Add the "multiväljare" to the course listing to filter courses
 */

get_header();

$todays_date = current_time( 'mysql' );

$nav_args = array(
  'theme_location' => 'main-menu',
  'container' => '',
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

        <?php $post_meta = get_post_custom( get_the_ID() ); ?>

      	<div class="<?php if ( has_boxes( 'sidebar_boxes', 'get_field' ) ) : ?>of-c-sm-4 of-inner-padded-r of-c-md-4 of-c-lg-12 of-c-xl-flexible-10<?php else : ?>of-c-sm-4<?php endif; ?> of-omega">
          <?php edit_post_link( __( 'Redigera den här sidan', 'sk' ), '<p>', '</p>' ); ?>
          <?php SKChildTheme\custom_breadcrumbs(); ?>
    			<h1><?php the_title(); ?></h1>
            
            <a href="#" class="of-btn of-btn-inline of-btn-vattjom of-btn-spaced" onclick="window.history.go(-1); return false;">
              <span><?php _e('Tillbaka till sök', 'sk') ?></span>
            </a>

    			<div class="sk-entry-content">
    				<?php the_content(); ?>
    			</div>
        
          <div class="course-meta-data">
            <div class="course-starts">
              <p><label><?php _e( 'Anmälningskod: ', 'sk' ); ?></label><span> <?php echo $post_meta['anmkod'][0]; ?></span></p>
              <p><label><?php _e( 'Kurskod: ', 'sk' ); ?></label><span> <?php echo $post_meta['kurskod'][0]; ?></span></p>
              <p><label><?php _e( 'Poäng: ', 'sk' ); ?></label><span> <?php echo $post_meta['poang'][0]; ?></span></p>
              <p><label><?php _e( 'Studieform: ', 'sk' ); ?></label><span> <?php echo $post_meta['kurskategori'][0]; ?></span></p>
              <p><label><?php _e( 'Skolform: ', 'sk' ); ?></label><span> <?php echo $post_meta['skolform'][0]; ?></span></p>
              <p><label><?php _e( 'Förkunskap: ', 'sk' ); ?></label><span> <?php echo $post_meta['forkunskap'][0]; ?></span></p>
              <p><label><?php _e( 'Ämnes-/kursplan hos skolverket: ' , 'sk' ); ?></label><span><a href="<?php echo $post_meta['skolverketurl'][0]; ?>" target="_blank"> <?php echo $post_meta['anmkod'][0]; ?>
                 <i class="of-icon"><?php icon( 'external' ); ?></i>
                </a></span></p>
          </div>

          <div class="course-starts">
            <h3><?php _e( 'Kursstarter', 'sk' ); ?></h3>
            <table class="of-table of-table-even-odd" cellpadding="0" cellspacing="0">
              <thead>
                <tr>
                  <th><?php _e( 'Sökbar', 'sk' ); ?></th>
                  <th><?php _e( 'Sökbar till', 'sk' ); ?></th>
                  <th><?php _e( 'Startdatum', 'sk' ); ?></th>
                  <th><?php _e( 'Ort', 'sk' ); ?></th>
                  <th></th>
                </tr>
              </thead>
              <tbody>

                <?php $course_starts = unserialize( $post_meta['kursstarter'][0] ); ?>
                <?php 
                  $flag = false;
                  foreach( $course_starts as $course_start ) : ?>
                  <?php 
                    if( strtotime( $todays_date ) <= strtotime( $course_start['sokbarTill'] ) ) : 
                      $flag = true;
                  ?>
                    <tr>
                      <td data-of-tr="<?php _e( 'Sökbar', 'sk' ); ?>"><?php echo $course_start['sokbar']; ?></td>
                      <td data-of-tr="<?php _e( 'Sökbar till', 'sk' ); ?>"><?php echo $course_start['sokbarTill']; ?></td>
                      <td data-of-tr="<?php _e( 'Startdatum', 'sk' ); ?>"><?php echo $course_start['datum']; ?></td>
                      <td data-of-tr="<?php _e( 'Ort', 'sk' ); ?>"><?php echo $course_start['ort']; ?></td>
                      <td data-of-tr="<?php _e( 'Lägg i kurskorg', 'sk' ); ?>">
                          <?php if( strtotime( $todays_date ) <= strtotime( $course_start['sokbarTill'] ) ) : ?>
                            <a href="https://sundsvall.alvis.gotit.se/student/laggtillkorg.aspx?add=<?php echo $course_start['id']; ?>" target="_blank" class="add-to-basket">
                              <?php _e( 'Lägg i kurskorg', 'sk' ); ?>
                            </a>
                          <?php endif; ?>
                          <img class="add-to-basket-spinner" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/ajax-loader.gif" style="display: none;" />
                      </td>
                    </tr>
                <?php endif; endforeach; ?>
                  <?php if( isset( $flag ) && $flag === false ) : ?>
                    <tr>
                      <td colspan="5"><i><?php _e('Det finns för närvarande inga aktuella startdatum för denna kurs.', 'sk') ?></i></td>
                    </tr>
                  <?php endif; ?>


              </tbody>
            </table>
            <form id="course-form">
              <input type="hidden" name="namn" value="<?php echo get_the_title( get_the_id() ); ?>" />
              <input type="hidden" name="anmkod" value="<?php echo $post_meta['anmkod'][0]; ?>" />
            </form>
          </div>


          <?php edit_post_link( __( 'Redigera den här sidan', 'sk' ), '<p>', '</p>' ); ?>
    		</div>

       

    	<?php endwhile; // end of the loop. ?>
      
      <?php if ( has_boxes( 'sidebar_boxes', 'get_field' ) ) : ?>
        <div class="of-c-sm-4 of-c-md-4 of-c-lg-12 of-c-xl-fixed-2 of-omega sk-sidebar">
          <?php SKChildTheme\the_boxes_block( 'sidebar_boxes', 'get_field', true ); ?>
        </div>
      <?php endif; ?>
      <?php if ( $menu !== false ): ?>

        </div>
      <?php endif; ?>
  </div>

</div>

<iframe id="alvis-container" src="https://sundsvall.alvis.gotit.se/student/kurskatalog.aspx" style="width: 100%; display: none;"></iframe>

<script>
  var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
  var course_basket_link = '<?php echo site_url() . "/kurskorg"; ?>';
</script>
<script src="<?php echo get_stylesheet_directory_uri(); ?>/assets/js/source/alvis_basket.js"></script>

<?php get_footer(); ?>