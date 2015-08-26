<?php
/*
Template Name: Sökresultat kurser
*/
?>

<?php
//global $wp_query;
$tmp_query = $wp_query;
//$wp_query = null;
$wp_query = SKChildTheme\get_course_search_result( $_POST );

$collected_terms = get_option( 'vuxenutbildning_categorized_terms', array() );

//echo '<pre>' . print_r( $collected_terms, true ) . '</pre>';
//die();
$prev_args = get_transient( 'course_search_arguments' );

if( isset( $prev_args['tax_query'][0]['terms'] ) ) {
  $search_categories = $prev_args['tax_query'][0]['terms'];
} else if( isset( $_POST['search_categories'] ) ) {
  $search_categories = $_POST['search_categories'];
} else {
  $search_categories = array();
}



?>

<?php get_header(); ?>

<div class="of-wrap">
  <div class="of-inner-padded-t sk-main-padded">

    <form method="post" action="" class="of-form">
      <label class="of-block-label">
        <span><?php _e('Fritext', 'sk'); ?></span>
        <input type="text" name="text">
      </label>
      <label class="of-block-label">
        <span><?php _e( 'Välj kategorier att söka', 'sk' ); ?></span>
        <select name="course_categories[]" multiple data-of-select>
          
          <optgroup label="Studieform">
            <?php if( count( $collected_terms['studieform'] ) > 0 ) : ?>
              <?php foreach( $collected_terms['studieform'] as $c_term ) : ?>
                <option value="<?php echo $c_term; ?>" <?php if( isset( $search_categories ) && is_array( $_POST ) && in_array( $c_term, $search_categories ) ) echo 'selected="true"'; ?>><?php echo $c_term; ?></option>
              <?php endforeach; ?>
            <?php endif; ?>
          </optgroup>

          <optgroup label="Nivå">
            <?php if( count( $collected_terms['niva'] ) > 0 ) : ?>
              <?php foreach( $collected_terms['niva'] as $c_term ) : ?>
                <option value="<?php echo $c_term; ?>" <?php if( isset( $search_categories ) && is_array( $_POST ) && in_array( $c_term, $search_categories ) ) echo 'selected="true"'; ?>><?php echo $c_term; ?></option>
              <?php endforeach; ?>
            <?php endif; ?>
          </optgroup>

          <optgroup label="Ämnesområde">
            <?php if( count( $collected_terms['amnesomrade'] ) > 0 ) : ?>
              <?php foreach( $collected_terms['amnesomrade'] as $c_term ) : ?>
                <option value="<?php echo $c_term; ?>" <?php if( isset( $search_categories ) && is_array( $_POST ) && in_array( $c_term, $search_categories ) ) echo 'selected="true"'; ?>><?php echo $c_term; ?></option>
              <?php endforeach; ?>
            <?php endif; ?>
          </optgroup>

        </select>
      </label>
      <input type="submit" value="<?php _e('Sök', 'sk'); ?>" />
    </form>

    <?php if( have_posts()) : ?>
      <?php if( strlen( get_search_query() ) > 0 ) : ?>
        <h3 class=""><?php printf( __( 'Din sökning på "%s" gav %d träffar', 'sk' ), get_search_query(), $wp_query->found_posts ); ?></h3>
      <?php else : ?>
        <h3 class=""><?php printf( __( 'Din sökning gav %d träffar', 'sk' ), $wp_query->found_posts ); ?></h3>
      <?php endif; ?>
      
      <ul class="of-post-list">
          <?php while ( have_posts() ) : the_post(); ?>
            <?php $terms_array = array(); ?>
            <?php $terms = wp_get_post_terms( get_the_ID(), 'kurskategorier' ); ?>
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
                
                <?php the_excerpt(); ?>
                
                <div class="of-meta-line">
                <?php foreach( $terms as $term ) : ?>
                    <?php 
                    $terms_array []= $term->name;
                    ?>
                <?php endforeach; ?>
                <?php $terms_string = implode( ', ', $terms_array ); ?>
                  <ul class="of-meta-line">
                    <?php echo $terms_string; ?>
                  </ul>
                </div>

                

                <ul class="of-meta-line">
                  <li><?php //the_time('j F Y H:i'); ?></li>
                </ul>
              </header>
            </li>
          <?php endwhile; // end of the loop. ?>
      </ul>

      <!--<div class="nav-previous alignright"><?php next_posts_link( __( 'Nästa sida &raquo;', 'sk' ) ); ?></div>
      <div class="nav-next alignleft"><?php previous_posts_link( __( '&laquo; Föregående sida', 'sk' ) ); ?></div>-->
      <?php
        $big = 999999999; // This needs to be an unlikely integer

        // For more options and info view the docs for paginate_links()
        // http://codex.wordpress.org/Function_Reference/paginate_links
        $paginate_links = paginate_links( array(
            'base' => str_replace( $big, '%#%', get_pagenum_link($big) ),
            'current' => max( 1, get_query_var('paged') ),
            'total' => $wp_query->max_num_pages,
            'mid_size' => 5
        ) );

        // Display the pagination if more than one page is found
        if ( $paginate_links ) {
            echo '<div class="pagination">';
            echo $paginate_links;
            echo '</div><!--// end .pagination -->';
            wp_reset_query();
        }

      ?>
    <?php else : ?>
        <?php if( strlen( get_search_query() ) > 0 ) : ?>
        <h3 class=""><?php printf( __( 'Din sökning på "%s" gav %d träffar', 'sk' ), get_search_query(), $wp_query->found_posts ); ?></h3>
      <?php else : ?>
        <h3 class=""><?php printf( __( 'Din sökning gav %d träffar', 'sk' ), $wp_query->found_posts ); ?></h3>
      <?php endif; ?>
    <?php endif; ?>
    <?php
    $wp_query = NULL;
    $wp_query = $tmp_query;
    ?>
  </div>
</div>

<?php get_footer();