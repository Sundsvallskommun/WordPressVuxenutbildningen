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

$basket_link = sprintf('<a class="link-to-basket" href="%s/kurskorg/"><span class="glyphicon glyphicon-ok"></span> %s</a>', site_url(), __( 'Gå till kurskorg', 'sk' ) );

$menu = get_field( 'hide_sidebar_menu' ) !== false ? wp_nav_menu( $nav_args ) : false;
$classes = array();

if ( $menu !== false ) {
	$classes[] = 'has-sidebar-menu';
}

if ( has_boxes( 'sidebar_boxes', 'get_field' ) ) {
	$classes[] = 'has-sidebar-boxes';
}

$sub_courses = get_post_meta( $post->ID, 'included_courses', true ); 
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
    			<div class="sk-entry-content">
    				<?php the_content(); ?>                        
            <div class="single-course-back-btn"> 
              <a href="#" class="of-btn of-btn-inline of-btn-vattjom of-btn-spaced" onclick="window.history.go(-1); return false;">
                <span><?php _e('Tillbaka till sök', 'sk') ?></span>
              </a>
            </div><!-- .single-course-back-btn -->
    			</div><!-- .sk-entry-content -->

          <div class="course-meta-data">
            <div class="course-starts">
              <p><span class="course-meta-title"><?php _e( 'Anmälningskod: ', 'sk' ); ?></span> <?php echo $post_meta['anmkod'][0]; ?></p>
              <p><span class="course-meta-title"><?php _e( 'Kurskod: ', 'sk' ); ?></span> <?php echo $post_meta['kurskod'][0]; ?></p>
              <p><span class="course-meta-title"><?php _e( 'Poäng: ', 'sk' ); ?></span> <?php echo $post_meta['poang'][0]; ?></p>
              <p><span class="course-meta-title"><?php _e( 'Studieform: ', 'sk' ); ?></span> <?php echo $post_meta['kurskategori'][0]; ?></p>
              <p><span class="course-meta-title"><?php _e( 'Skolform: ', 'sk' ); ?></span> <?php echo $post_meta['skolform'][0]; ?></p>
              <p><span class="course-meta-title"><?php _e( 'Förkunskap: ', 'sk' ); ?></span> <?php echo $post_meta['forkunskap'][0]; ?></p>
              
              <?php if( $post_meta['amnesomrade'][0] != 'Yrkesinriktade utbildningar' ) : ?>
              <p>
                <span class="course-meta-title"><?php _e( 'Ämnes-/kursplan hos skolverket: ' , 'sk' ); ?></span>
                <?php if( !empty( $post_meta['skolverketurl'][0] ) ) : ?>
                  <a href="<?php echo $post_meta['skolverketurl'][0]; ?>" target="_blank"> 
                    <?php echo $post_meta['anmkod'][0]; ?> <i class="of-icon"><?php icon( 'external' ); ?></i>
                  </a>
                <?php endif; ?>
              </p>
              <?php endif; ?>

              
              <?php if(! empty( $sub_courses ) ) : ?>
                <p><span class="course-meta-title"><?php _e( 'Inkluderade kurser: ', 'sk' ); ?></span></p>
                <ul class="sub-courses-list">
              <?php foreach ($sub_courses as $course ) : ?>
                  <li><?php echo $course['name']; ?> (<?php echo $course['code']; ?>)</li>
                <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div><!-- .course-starts -->
    		  </div><!-- .course-meta-data -->

          <?php edit_post_link( __( 'Redigera den här sidan', 'sk' ), '<p>', '</p>' ); ?>

          <div class="course-starts" style="clear:both;">
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
                <?php 
                  $course_starts = unserialize( $post_meta['kursstarter'][0] );
                
                  $flag = false;
                  $course_added = false;
                  foreach( $course_starts as $course_start ) : 

                    // check if course already added
                    if(isset( $_SESSION['course_basket']['courses'] )){
                      foreach ($_SESSION['course_basket']['courses'] as $key => $value) {
                        if( $key == $course_start['id'] )
                          $course_added = true;
                      }
                    }

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
                            
                            <?php if( $course_added == true ) : ?>
                              <?php echo $basket_link; ?>
                            <?php else : ?>  
                              <input type="hidden" class="course_id" value="<?php echo $course_start['id']; ?>">
                              <a href="#" class="add-to-basket">
                                <?php _e( 'Lägg i kurskorg', 'sk' ); ?>
                              </a>
                            <?php endif; ?>
                          
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
<script>
  var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
  var course_basket_link = '<?php echo site_url() . "/kurskorg"; ?>';
</script>
<script src="<?php echo get_stylesheet_directory_uri(); ?>/assets/js/source/alvis_basket.js"></script>

<?php get_footer(); ?>