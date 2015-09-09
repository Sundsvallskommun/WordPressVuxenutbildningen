<?php
namespace SKChildTheme;

/**
 * Represents the campaign block in the advanced template.
 *
 * Prepares the data for use and returns it.
 *
 * @since 1.0.0
 * 
 * @return object
 */

function get_campaign_block() {
  $data = new \stdClass;

  $data->title = get_sub_field( 'campaign_title' );
  $data->categories = get_sub_field( 'campaign_category' );
  $data->limit = 3;

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
 * Represents the campaign block in the advanced template.
 *
 * @since 1.0.0
 * 
 * @return null
 */
function the_campaign_block() {
  $campaign_posts = get_campaign_block();

  if ( empty( $campaign_posts->posts ) ) : ?>
    <?php return; ?>
  <?php endif; ?>
  
  <div class="sk-main sk-campaign-block">
    <ul class="sk-campaign-content-list">  
    <?php if ( ! empty( $campaign_posts->title ) ) : ?>
      <header>
        <h2><?php echo $campaign_posts->title; ?></h2>
      </header>
    <?php endif; ?>
  <li class="sk-campaign-slider">
  
  <div class="owl-carousel 
        <?php if(count(get_field('campaign_content')) == 1) : 
          echo 'single'; 
        else : 
          echo 'multiple'; 
      endif; ?>">

    <?php if( have_rows('campaign_content') ): ?>

   
      <?php while( have_rows('campaign_content') ): the_row(); 
        $campaign_image = get_sub_field( 'campaign_image' );
        ?>

        <div class="item">
          
            <img src="<?php echo $campaign_image['url']; ?>" alt="<?php echo $campaign_image['alt'] ?>" />
        
          <?php if ( get_sub_field( 'campaign_image_url' ) ) : ?>
            <a href="<?php the_sub_field( 'campaign_image_url' ); ?>">
          <?php endif; ?>

          <?php if( get_sub_field('campaign_image_text') ) : ?>
            <div class="wrap">
                <div class="text"><?php the_sub_field( 'campaign_image_text' ); ?></div>
            </div>
          <?php endif; ?>
        <?php if ( get_sub_field( 'campaign_image_url' ) ) : ?>
          </a>
       <?php endif; ?>
       
        </div>
      <?php endwhile; ?>
    <?php endif; ?>
  </div>
  </li>

  <li class="sk-campaign-list">
   
      <?php if ( ! empty( $campaign_posts->title ) ) : ?>
      <header>
        <h2><?php echo $campaign_posts->title; ?></h2>
      </header>
     <?php endif; ?>
    <ul class="sk-campaign-grid-list">
      <?php foreach ( $campaign_posts->posts as $post ) : ?>
        <li>
            <?php if ( has_post_thumbnail( $post->ID ) ) : ?>
            <a href="<?php echo get_permalink( $post->ID ); ?>">
              <figure>
                <?php echo get_the_post_thumbnail( $post->ID, 'thumbnail' ); ?>
              </figure>
              </a>
            <?php endif; ?>

            <a class="of-dark-link" href="<?php echo get_permalink( $post->ID ); ?>">
            <article <?php if ( has_post_thumbnail( $post->ID ) ) : ?> class="sk-narrow"<?php endif; ?>>
              <header> 
                <h5>
                  <?php echo $post->post_title; ?>
                </h5>
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
    <a class="archive-link" href="<?php echo get_bloginfo( 'url' ) . '/' . date('Y') . '/'; ?>"><?php _e('Fler nyheter', 'sk-theme') ?></a>
  </li>
  </ul>
  </div>
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
function get_faq_block( $terms = array() ) {
  
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

  if( count( $terms ) > 0 ) {

    $args['tax_query'] = array(
      array(
        'taxonomy' => 'faqkategorier',
        'field' => 'slug',
        'terms' => $terms,
        'operator' => 'IN'
      )
    );

  }

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
  
  $search_terms = isset( $_POST['categories'] ) ? $_POST['categories'] : array();
  
 
  // Add the default terms to search array  
  $default_terms = get_sub_field( 'faq_block_category' );
  if( !empty( $default_terms ) && is_array( $default_terms ) ) {

    foreach( $default_terms as $default_faq_term ) {
      $search_terms []= $default_faq_term->slug;
    }

  }
 
  // Get the matching posts
  $faq_posts = get_faq_block( $search_terms );

  // Title of the FAQ
  $title = get_field( 'faq_title', $post->ID );

  // Get all the terms available for faq
  $faq_terms = get_terms( 'faqkategorier', array( 'order' => 'ASC' ) );

  if ( empty( $faq_posts->posts ) ) : ?>
    <?php return; ?>
  <?php endif; ?>

  
  <div class="sk-main sk-faq-posts-block">
    <?php if ( ! empty( $faq_posts->title ) ) : ?>
      <header>
        <h2><?php echo $faq_posts->title; ?></h2>
      </header>
    <?php endif; ?>


    <div class="of-c-sm-2 of-c-xxl-8">      
      <div class="of-block-label">
        <div class="box-filter-search">
          <label for="searchFilter"><?php _e( 'Filtrera på fråga:', 'sk-theme' ); ?></label>
          <input type="text" name="searchFilter" id="searchFilter" class="searchFilter form-control" value="" />
        </div>
      </div>

      <div class="sk-faq-list">
        <?php foreach ( $faq_posts->posts as $faq_post ) : ?>
        <?php $faq_post_terms=wp_get_post_terms( $faq_post->ID, "faqkategorier");?>
        <div class="faq-item">

        <?php if ( has_post_thumbnail( $faq_post->ID ) ) : ?>
          <figure>
            <?php echo get_the_post_thumbnail( $faq_post->ID, 'thumbnail' ); ?>
          </figure>
        <?php endif; ?>
      
          <article <?php if ( has_post_thumbnail( $faq_post->ID ) ) : ?>class="sk-narrow"<?php endif; ?>>
            <div class="of-table box-qa-answers" cellpadding="0" cellspacing="0">
              <header>
                <h5 class="of-icon"> <i><svg viewBox="0 0 512 512"><use xlink:href="#posts"></use></svg></i><span> <?php echo $faq_post->post_title; ?></span></h5>
              </header>
              <div class="faq-answer">

                <?php if ( ! empty( $faq_post->answer) ) : ?>
                  <strong><?php _e('Svar:', 'sk'); ?></strong> <?php echo $faq_post->answer; ?>
                <?php endif; ?>

                <?php if ( ! empty( $faq_post_terms) ) : ?>
                  <div class="faq-post-terms"> 
                  <?php foreach( $faq_post_terms as $faq_post_term ) : ?>
                    <div class="of-tag"><?php echo $faq_post_term->name; ?></div>
                  <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              
              </div><!-- .faq-answer -->


              <?php if ( ! empty( $faq_post->references) ) : ?>
              <div class="faq-related">
                <?php _e('Relaterat:', 'sk'); ?>
                <?php foreach( $faq_post->references as $reference ) : ?>
                  <a href="<?php the_permalink( $reference->ID ); ?>"><?php echo $reference->post_title; ?> </a>
                <?php endforeach; ?>
              </div><!-- .faq-related -->
              <?php endif; ?>

            </div><!-- .box-qa-answers -->     

          </article>
          </div><!-- .faq-item -->
        <?php endforeach; ?>      
      </div><!-- .faq-filter-search -->

    </div>

    <div class="of-c-sm-2 of-c-xxl-2 of-omega">
    <h4><?php _e('Kategorier', 'sk') ?></h4>
      <div class="faq-filter-checkbox">
        <form method="post" action="">
          <fieldset>
            <ul>
              <?php if( count( $faq_terms ) > 0 ) : ?>
              <?php foreach( $faq_terms as $faq_term ) : ?>
              <li>
                <label class="checkbox-inline">
                <?php $checked=in_array($faq_term->slug, $search_terms) ? "checked" : "";?> 
                <input type="checkbox" name="categories[]" value="<?php echo $faq_term->slug; ?>" <?php echo $checked; ?>>
                <?php echo $faq_term->name; ?>
                </label>
              </li>
              <?php endforeach; ?>
              <?php endif; ?>
            </ul>
            <div class="of-inner-padded-t">
              <button type="submit" class="of-btn of-btn-inline of-btn-gronsta of-btn-spaced"><?php _e('Sök', 'sk'); ?></button>
            </div>
          </fieldset>
        </form>
      </div><!-- .faq-filter-checkbox -->
    </div>
  </div>


  <?php
}


/**
 * 
 * Get data for courses and return WP_Query object
 *
 * @since 1.0.0
 * 
 * @param  array  $postdata Ajax postdata
 * 
 * @return object WP_Query 
 */
function get_course_block( $postdata = array() ) {
  global $post;

  if(isset($_SESSION['search_history']))
    $session_history = $_SESSION['search_history'];

  if( !empty( $session_history ) ){
    $postdata = $session_history;
  }

  // delete session on reload.
  if(! get_query_var('page') ){
     unset( $_SESSION['postdata'] );
   }

  $is_course_search = false;
  $is_points_order = false;
  $show_only_appliable = false;

  $filter = array(
    'meta_key'      => array(),
    'free_search'   => '',
    'sort_meta_key' => '',
    'sort_orderby'  => '',
    'sort_order'    => ''
  );
  
  $meta_query = array();
  $tax_query = array();
  $posts_per_page = 9;


  
  $ajax_call = false;
  if( !empty( $postdata ) )
    $ajax_call = true;


  $paged = (get_query_var('page')) ? get_query_var('page') : 1;

  if( $ajax_call ){
    if(isset($_SESSION['postdata']))
      $_SESSION['postdata'] = $postdata;
  }

  // if scroll is triggered, user transient postdata
  if( $paged > 1 ){
    if(isset($_SESSION['postdata']))
      $postdata = $_SESSION['postdata'];
  }

  // When filtering is in use
  if( !empty( $postdata )){

    // filter out meta queries
    foreach( $postdata as $key => $value ){
      
      if(!empty( $value['name'] ) && strstr($value['name'], '-meta-') ) {
        switch ($value['value']) {
          case 'Gymnasienivå':
            $value['value'] = 'GY';
            break;

          case 'Grundskola':
            $value['value'] = 'GR';
            break;   
                          
        }
        $filter['meta_key'][str_replace( 'filter-meta-', '', $value['name'] )][] = $value['value'];
      }


    if(!empty( $value['name'] ) && strstr($value['name'], '-metapackage-') ) {
        switch ($value['value']) {
          case 'Gymnasienivå':
            $value['value'] = 'GY';
            break;

          case 'Yrkeshögskola':
            $value['value'] = 'YH';
            break;   
                          
        }
        $filter['meta_key'][str_replace( 'filter-metapackage-', '', $value['name'] )][] = $value['value'];
        $filter['tax'] = 'yrkesinriktade-utbildningar';
      }



      if( $value['name'] == 'filter-search' ) {
         $filter['free_search'] = $value['value'];
      }

      if( $value['name'] == 'filter-taxonomy-amnesomrade' ) {
        
        if(!empty( $value['value']) ){
          $term = get_term_by('name', $value['value'], 'kurskategorier');
          if( isset( $term ) && is_object( $term ) ) {
            $filter['tax'] = $term->slug;
          }
        }

      }

      if( $value['name'] == 'filter-type' ) {

        if( $value['value'] == 'courses' ) {
          $is_course_search = true;
        } else {
          $is_course_search = false;
        }

      }

      if( $value['name'] == 'show_only_appliable' && $value['value'] == 'true' ) {

        $show_only_appliable = true;

      }


      if( $value['name'] == 'filter-sortorder' ) {

        switch ( $value['value']) {
          case 'sort-alpha':
            $filter['sort_meta_key'] = '';
            $filter['sort_orderby'] = 'post_title';
            $filter['sort_order'] = 'ASC';
          break;          

          case 'sort-startdate':
            $filter['sort_meta_key'] = 'nearest_start_date';
            $filter['sort_orderby'] = 'meta_value_num';
            $filter['sort_order'] = 'ASC';
          break;          

          case 'sort-points':
            $filter['sort_meta_key'] = 'poang';
            $filter['sort_orderby'] = 'meta_value_num';
            $filter['sort_order'] = 'DESC';
            $is_points_order = true;
          break;
          
          default:
            $filter['sort_meta_key'] = '';
            $filter['sort_orderby'] = 'post_title';
            $filter['sort_order'] = 'ASC';
          break;
        }
      }


    }

  }


  foreach( $filter['meta_key'] as $key => $value ){
    $meta_query[] = array(
      'key'     => $key,
      'value'   => $value,
      'compare' => 'IN',
    );
  }
  
  if( $is_course_search ) {
    if(!empty( $filter['tax'] )){
      $tax_query[] = array(      
        'taxonomy' => 'kurskategorier',
        'field'    => 'slug',
        'terms'    => $filter['tax'],
      );
    }
  } 


  if( $is_course_search ) {

    $meta_query []= array(
      'key'     => 'kurspaket',
      'value'   => 'false',
      'compare' => '=',
    );
  } else {
    
    $tax_query[] = array(
      'taxonomy' => 'kurskategorier',
      'field'     => 'slug',
      'terms'    => array('yrkesinriktade-utbildningar'),
      'operator' => 'IN',
    );

    $meta_query []= array(
      'key'     => 'kurspaket',
      'value'   => 'true',
      'compare' => '=',
    );

  }


  if( $show_only_appliable === true ) {
    $meta_query []= array(
      'key'     => 'is_searchable',
      'value'   => 'true',
      'compare' => '='
    );
  }

  if( !$ajax_call && ! $is_points_order ) { // Is points order fugly hack to resolve problem with sorting. Here be monkeys!
    $filter['sort_meta_key'] = '';
    $filter['sort_orderby'] = 'post_title';
    $filter['sort_order'] = 'ASC';
  }

  $args = array(
    'posts_per_page'  => empty( $posts_per_page ) ? 5 : $posts_per_page,
    'post_type'       => 'kurs',
    'paged'           => $paged,
    'meta_query'      => $meta_query,
    'tax_query'       => $tax_query,
    's'               => $filter['free_search'],
    'meta_key'        => $filter['sort_meta_key'],
    'orderby'         => $filter['sort_orderby'],
    'order'           => $filter['sort_order'],
  );

  $result = new \WP_Query( $args );

  return $result;

}

/**
 * HTML output for the filter block
 *
 * @todo  tab content for course occupation is static and is not possible to filter out, waiting for input from Alvis.
 *
 * @since 1.0.0
 * 
 */
function the_courselist_filter(){
  global $post;
  $collected_terms = get_option( 'vuxenutbildning_categorized_terms');
  $title = get_sub_field( 'courselist_title', $post->ID );

  // exclude terms from select amnesomrade, add new items in array to exclude, lowercase
  $exclude_terms_amnesomrade = array( 'yrkesinriktade utbildningar' );


  // default value for filter, values set in acf content block
  $activate_tab = get_sub_field( 'courselist_activate_tab', $post->ID );
  $filter = array('filter-type' => array( $activate_tab ) );
  
  if(isset($_SESSION['search_history']) ){
    $session_history = $_SESSION['search_history'];
  }


  if( !empty( $session_history ) ){
    $filter = array();
    $postdata = $session_history;

    foreach ($postdata as $items => $value) {
      $filter[$value['name']][] = $value['value'];
    }

  }

   $post_form_id = false;
      if(isset($_SESSION['search_history'])){
        foreach( $_SESSION['search_history'] as $key => $value ){
          if( $value['name'] == 'post_id' ){
            $post_form_id = $value['value'];
          }
        }
      }
        

  // no session set, check for default values for current page      
  if( isset( $post_form_id ) && $post_form_id != $post->ID ){
    
    $defaults = array();
    $in_categories = get_sub_field( 'courselist_categories', $post->ID );
    
    // which tab is set as default for current search container.
    // get values and save as pre default settings
    
    if( $activate_tab == 'educations'){
      $defaults['filter-type'][] = 'educations';
      $defaults['post_id'][] = $post->ID;
      if(!empty( $in_categories )){
        $i = 0;
        foreach( $in_categories as $in_cat ) {
          $term = get_term( $in_cat, 'kurskategorier' );
            foreach( $collected_terms['niva'] as $collected_term ){
              if( $term->name == $collected_term ){
                $defaults['filter-metapackage-skolform'][] = $term->name;
              }  
            }  
          $i++;
        }

      }
    }

    if( $activate_tab == 'courses'){
      $defaults['filter-type'][] = 'courses';
      $defaults['post_id'][] = $post->ID;

      if(!empty( $in_categories )){
        $i = 0;
        foreach( $in_categories as $in_cat ) {
          $term = get_term( $in_cat, 'kurskategorier' );
            foreach( $collected_terms['niva'] as $collected_term ){
              if( $term->name == $collected_term ){
                $defaults['filter-meta-skolform'][] = $term->name;
              }  
            }

            foreach( $collected_terms['amnesomrade'] as $collected_term ){
              if( $term->name == $collected_term ){
                $defaults['filter-taxonomy-amnesomrade'][] = $term->name;
              }  
            }

            foreach( $collected_terms['studieform'] as $collected_term ){
              if( $term->name == $collected_term ){
                $defaults['filter-meta-kurskategori'][] = $term->name;
              }  
            }            
            
            $i++;

        }

      }
      
    }

    // save defaults to filter
    $filter = $defaults;

  }



// sniff for older IE
$ie_fix = true;
if(preg_match('/(?i)msie [6-9]/', $_SERVER['HTTP_USER_AGENT']) ){
  $ie_fix = true;
}

?>

<?php if ( !empty( $title ) ) : ?>
  <header>
    <h2><?php echo $title; ?></h2>
  </header>
<?php endif; ?>

 <div class="sk-courselist-filter">
    <form id="form-single-courses">
    <?php 
      // we dont use placeholder for older version of IE, value gets placeholder value on search
      if( $ie_fix == true ) : 
    ?>
      <label for="course-filter-search"><?php _e( 'Ange sökord', 'sk' ); ?></label>
      <input type="text" id="course-filter-search" name="filter-search" value="">
    <?php else : ?>
      <input type="text" id="course-filter-search" name="filter-search" placeholder="<?php _e('Ange sökord', 'sk') ?>" value="">
    <?php endif; ?>
      
        <div role="tabpanel">

          <ul class="nav nav-tabs search-education-tabs" role="tablist">
            <li role="presentation" class="<?php echo isset( $filter['filter-type'] ) && $filter['filter-type'][0] == 'educations' ? 'active' : '';?>" id="educations-tab"><a href="#course-occupation" aria-controls="course-occupation" role="tab" data-toggle="tab"><?php _e('Yrkesutbildningar', 'sk') ?></a></li>
            <li role="presentation" id="courses-tab" class="<?php echo isset( $filter['filter-type'] ) && $filter['filter-type'][0] == 'courses' ? 'active' : '';?>"><a href="#course-single" aria-controls="course-single" role="tab" data-toggle="tab"><?php _e('Kurser', 'sk') ?></a></li>
          </ul><!-- .mav-tabs -->

          <?php if( isset( $filter['filter-type'] ) ) : ?>
            <?php if( $filter['filter-type'][0] == 'educations' ) : ?>
              <input id="filter-search-type" name="filter-type" type="hidden" value="educations">
            <?php else : ?>
              <input id="filter-search-type" name="filter-type" type="hidden" value="courses">
            <?php endif ; ?>
          <?php else : ?>
            <input id="filter-search-type" name="filter-type" type="hidden" value="educations">
          <?php endif; ?>
            

          <div class="tab-content">
            <div role="tabpanel" class="tab-pane <?php echo isset( $filter['filter-type'] ) && $filter['filter-type'][0] == 'educations' ? 'active' : '';?>" id="course-occupation">
              
              <?php 
                $exclude_niva_from_courses = array('grundskola');
                foreach( $collected_terms['niva'] as $item ) : 
                  if(! in_array( mb_strtolower( $item ), $exclude_niva_from_courses )) :
                  ?>
                <label class="checkbox-inline"><input type="checkbox" <?php if( isset( $filter['filter-metapackage-skolform'] ) ) checked( in_array( $item, $filter['filter-metapackage-skolform'] ) ? $item : '' , $item );?> value="<?php echo $item ?>" name="filter-metapackage-skolform"> <?php echo $item ?></label>
              <?php endif; endforeach; ?>

            </div><!-- #course-occupation -->

            <div role="tabpanel" class="tab-pane <?php echo isset( $filter['filter-type'] ) && $filter['filter-type'][0] == 'courses' ? 'active' : '';?>" id="course-single">
              <?php if( !empty( $collected_terms['niva'] ) ) : ?>
                <div class="form-group">

                <h5><?php _e('Nivå', 'sk'); ?></h5> 
                <?php 
                  foreach( $collected_terms['niva'] as $item ) : 
                    // exclude yrkesutbildning 
                    if( mb_strtolower( $item ) != 'yrkeshögskola') :
                ?>
                  <label class="checkbox-inline"><input type="checkbox" <?php if( isset( $filter['filter-meta-skolform'] ) ) checked( in_array( $item, $filter['filter-meta-skolform'] ) ? $item : '' , $item );?> value="<?php echo $item ?>" name="filter-meta-skolform"> <?php echo $item ?></label>
                <?php endif; ?>
              <?php endforeach; ?>
                </div><!-- .form-group -->
              <?php endif; ?>
              
              <?php if( !empty( $collected_terms['studieform'] ) ) : ?>
                <div class="form-group">
                <h5><?php _e('Studieform', 'sk'); ?></h5>
                <?php foreach( $collected_terms['studieform'] as $item ) : ?>
                  
                  <?php if( ! in_array( $item, array( 'Distanskurs', 'Dagkurs' ) ) ) : // Ugly hack to remove old terms from displaying ?>
                    <label class="checkbox-inline"><input type="checkbox" <?php if( isset( $filter['filter-meta-kurskategori'] ) ) checked( in_array( $item, $filter['filter-meta-kurskategori'] ) ? $item : '' , $item );?> value="<?php echo $item ?>" name="filter-meta-kurskategori"> <?php echo $item ?></label>
                  <?php endif; ?>

                <?php endforeach; ?>
                </div><!-- .form-group -->
              <?php endif; ?>
              
              <?php if( !empty( $collected_terms['amnesomrade'] ) ) :  ?>
                <div class="form-group">
                <h5><label for="filter-taxonomy-amnesomrade"><?php _e('Ämnesområde', 'sk'); ?></label></h5>
                <select name="filter-taxonomy-amnesomrade" id="filter-taxonomy-amnesomrade">
                  <option value="">-- <?php _e('Välj ämnesområde', 'sk') ?> --</option>
                <?php 
                  foreach( $collected_terms['amnesomrade'] as $item ) : 
                    if(! in_array( mb_strtolower( $item ), $exclude_terms_amnesomrade )) :
                    ?>

                  <option <?php if( isset( $filter['filter-taxonomy-amnesomrade'] ) ) selected( in_array( $item, $filter['filter-taxonomy-amnesomrade'] ) ? $item : '' , $item );?> value="<?php echo $item ?>"><?php echo $item; ?></option>
                <?php endif; endforeach; ?>
                </select>
                </div><!-- .form-group -->
              <?php endif; ?>
            </div><!-- #course-single -->

            <div class="form-group">
              <br />
              <label class="checkbox-inline"><input type="checkbox" <?php isset( $filter['show_only_appliable'][0] ) ? checked( $filter['show_only_appliable'][0], 'true' ) : '';?> name="show_only_appliable" value="true"><?php _e( 'Visa endast sökbara', 'sk' ); ?></label>
            </div>

          </div><!-- .tab-content -->
    
          <div class="of-inner-padded-t">
            <a id="btn-courselist-filter" class="of-btn of-btn-inline of-btn-vattjom of-btn-spaced" href="#">
              <span><?php _e('Sök/filtrera', 'sk'); ?></span>
            </a>
            <a id="clear-courselist-filter" class="of-btn of-btn-inline of-btn-gra of-btn-spaced" href="#">
              <span><?php _e('Återställ', 'sk'); ?></span>
            </a>
            <div class="filter-sortorder-wrap">
            <label for="filter-sortorder"><?php _e('Sortera på', 'sk'); ?></label>
              <select name="filter-sortorder" id="filter-sortorder">
                <option value="sort-alpha"><?php _e('Bokstavsordning', 'sk'); ?></option>
                <option value="sort-startdate"><?php _e('Startdatum', 'sk'); ?></option>
                <option value="sort-points"><?php _e('Kurspoäng', 'sk'); ?></option>
              </select>
            </div>

          </div>


    
        </div><!-- tabpanel -->
        <input type="hidden" id="post_id" name="post_id" value="<?php echo $post->ID ?>" >
      </form>  
    </div><!-- #sk-courselist-filter -->
  
<?php
}

/**
 * Represents the block in the advanced template.
 *
 * @since 1.0.0
 * 
 * @return null
 */

function the_courselist_block( $with_search_fields = false, $post_data = false ) {

  global $post, $wpdb, $flexible_index, $wp_query;
  $courses = get_course_block( $post_data );
  $collected_terms = get_option( 'vuxenutbildning_categorized_terms', array() );
  ?>
  <div class="sum-search-result-header"><p><?php printf(__('Sökningen resulterade i totalt %s träffar.'), $courses->found_posts ); ?></p></div>
  <?php

  if ( empty( $courses->posts ) ) : ?>

  <div class="sk-main sk-courselist-posts-block">
    <div class="jscroll">
      <ul class="sk-grid-list">
      <li class="sum-search-result-ended-footer" style="float: left; width: 100%; text-align: center;">
            <div class="of-btn of-btn-inline of-btn-vattjom of-btn-spaced" disabled="disabled">
              <span><?php printf(__('Visar <span id="sum-current">%s</span> träffar av totalt <span id="sum-total">%s</span>. Det finns inga fler träffar att visa.'), $courses->post_count, $courses->found_posts ); ?></span>
            </div>    
          </li>
        <li style=""><a class="next-scroll-block" href="<?php echo get_bloginfo('url'); ?>/?scroll-ended"></a></li>
      </ul>

    </div>
  </div>

    <?php return; ?>
  <?php endif; ?>
  <div class="sk-main sk-courselist-posts-block">
    <div class="jscroll">
    <ul class="sk-grid-list">

      <?php foreach ( $courses->posts as $course ) : ?>
        <?php
          $metadata = get_post_custom( $course->ID );
          $terms = wp_get_post_terms( $course->ID, 'kurskategorier' );
          $terms_array = array();
        ?>
        <li>
            <?php if ( has_post_thumbnail( $course->ID ) ) : ?>
              <figure>
                <?php echo get_the_post_thumbnail( $course->ID, 'thumbnail' ); ?>
              </figure>
            <?php endif; ?>
            
            <article<?php if ( has_post_thumbnail( $course->ID ) ) : ?> class="sk-narrow"<?php endif; ?>>
              <header>
                <h5><a href="<?php echo get_permalink( $course->ID ); ?>"><?php echo $course->post_title; ?></a></h5>
              </header>
              
              <?php if ( ! empty( $course->excerpt ) ) : ?>
                <p><?php echo $course->excerpt; ?>...</p>
              <?php endif; ?>

              <div class="of-meta-line">
              <?php foreach( $terms as $term ) : ?>
                  <?php 
                  $terms_array []= $term->name;
                  ?>
              <?php endforeach; ?>
              <?php $terms_string = implode( ', ', $terms_array ); ?>
              </div>
              <ul class="of-meta-line">
                <?php echo $terms_string; ?>
              </ul>
            </article>
        </li>
        <?php endforeach; ?>
          <li style=""><a class="next-scroll-block" href="<?php echo get_bloginfo('url'); ?>/page/<?php echo $courses->query['paged'] + 1;  ?>"></a></li>
          <li class="sum-search-result-footer" style="width: 100%; text-align: center;">
            <a href="#" class="of-btn of-btn-inline of-btn-vattjom of-btn-spaced btn-courselist-filter-reload" onclick="scroll_trigger();">
              <span><?php printf(__('Visar <span id="sum-current">%s</span> träffar av totalt %s. Scrolla eller klicka här för att ladda fler'), $courses->post_count, $courses->found_posts ); ?></span>
            </a>    
          </li>
        </ul>

    </div><!-- .jscroll -->


  </div><!-- .sk-main sk-courselist-posts-block -->
  
  

  <?php
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
  $width = get_sub_field( 'boxes_width');


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

            <li class="sk-box-type-<?php echo $box->type->slug;?> sk-box-width-<?php echo $width;?>">
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
              <?php elseif ( $box->type->slug == 'bild-och-lanklista' ) : ?>
                <?php the_image_and_textlist_box( $box ); ?>
              <?php endif; ?>
            </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif;
}

/**
 * Represents the image and textlist box in the boxes block.
 *
 * @since 1.0.0
 *
 * @param object $box Box object which contains the box post and box type term.
 * 
 * @return null
 */
function the_image_and_textlist_box( $box ) {
  global $post;
  global $flexible_index;
  
  $title = get_field( 'box_image_and_textlist_title', $box->post->ID );
  $image = get_field( 'box_image_and_textlist_image', $box->post->ID );
 
  ?>
  <ul class="sk-image-link-list">
    <li class="sk-image-link-image">
      <img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt'] ?>" />
    </li>
    <li class="sk-image-link-linklist">
  <?php if ( ! empty( $title ) ) : ?>
    <header>
      <h5><?php echo $title; ?></h5>
    </header>
  <?php endif; ?>
 
   <ul class="sk-links-list">
    <?php while( the_flexible_field( 'box_image_and_textlist_links', $box->post->ID ) ) : ?>
      <?php if ( get_row_layout() == 'box_image_and_textlist_internal_link' ) : ?>
         <li>
          
            <a href="<?php the_sub_field( 'link' ); ?>">
              <?php the_sub_field( 'text' ); ?>
            </a>
        </li>
      <?php elseif ( get_row_layout() == 'box_image_and_textlist_external_link' ) : ?>
        <li>
         
            <a href="<?php the_sub_field( 'link' ); ?>"<?php if ( get_sub_field( 'new_window' ) ) : ?> target="_blank"<?php endif; ?>>
              <?php the_sub_field( 'text' ); ?>
              <i class="of-icon"><?php icon( 'external' ); ?></i>
            </a>
        </li>
      <?php endif; ?>
    <?php endwhile; ?>
  </ul>
</li>
</ul>
  <?php
}

function custom_breadcrumbs() {
 
  $showOnHome = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show
  $delimiter = ''; // delimiter between crumbs &raquo;
  $home = 'Hem'; // text for the 'Home' link
  $showCurrent = 1; // 1 - show current post/page title in breadcrumbs, 0 - don't show
  $before = '<li><span>'; // tag before the current crumb
  $after = '</span></li>'; // tag after the current crumb
 
  global $post;
  $homeLink = get_bloginfo('url');
 
  if (is_home() || is_front_page()) {
 
    if ($showOnHome == 1) echo '<ul class="of-breadcrumbs"><li><a href="' . $homeLink . '">' . $home . '</a></li></ul>';
 
  } else {
 
    echo '<ul class="of-breadcrumbs"><li><a href="' . $homeLink . '">' . $home . '</a></li> ' . $delimiter . ' ';
 
    if ( is_category() ) {
      $thisCat = get_category(get_query_var('cat'), false);
      if ($thisCat->parent != 0) echo get_category_parents($thisCat->parent, TRUE, ' ' . $delimiter . ' ');
      echo $before . 'Archive by category "' . single_cat_title('', false) . '"' . $after;
 
    } elseif ( is_search() ) {
      echo $before . 'Search results for "' . get_search_query() . '"' . $after;
 
    } elseif ( is_day() ) {
      echo ' <li><a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a></li> ' . $delimiter . ' ';
      echo ' <li><a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a></li> ' . $delimiter . ' ';
      echo $before . get_the_time('d') . $after;
 
    } elseif ( is_month() ) {
      echo ' <li><a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
      echo $before . get_the_time('F') . $after;
 
    } elseif ( is_year() ) {
      echo $before . get_the_time('Y') . $after;
 
    } elseif ( is_single() && !is_attachment() ) {
      if ( get_post_type() != 'post' ) {
        $post_type = get_post_type_object(get_post_type());
        $slug = $post_type->rewrite;
        echo ' <li><a href="' . $homeLink . '/' . $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a></li>';
        if ($showCurrent == 1) echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
      } else {
        $cat = get_the_category(); $cat = $cat[0];
        $cats = get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
        if ($showCurrent == 0) $cats = preg_replace("#^(.+)\s$delimiter\s$#", "$1", $cats);
        echo $cats;
        if ($showCurrent == 1) echo $before . get_the_title() . $after;
      }
 
    } elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {
      $post_type = get_post_type_object(get_post_type());
      echo $before . $post_type->labels->singular_name . $after;
 
    } elseif ( is_attachment() ) {
      $parent = get_post($post->post_parent);
      $cat = get_the_category($parent->ID); $cat = $cat[0];
      echo get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
      echo ' <li><a href="' . get_permalink($parent) . '">' . $parent->post_title . '</a></li>';
      if ($showCurrent == 1) echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
 
    } elseif ( is_page() && !$post->post_parent ) {
      if ($showCurrent == 1) echo $before . get_the_title() . $after;
 
    } elseif ( is_page() && $post->post_parent ) {
      $parent_id  = $post->post_parent;
      $breadcrumbs = array();
      while ($parent_id) {
        $page = get_page($parent_id);
        $breadcrumbs[] = ' <li><a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a></li>';
        $parent_id  = $page->post_parent;
      }
      $breadcrumbs = array_reverse($breadcrumbs);
      for ($i = 0; $i < count($breadcrumbs); $i++) {
        echo $breadcrumbs[$i];
        if ($i != count($breadcrumbs)-1) echo ' ' . $delimiter . ' ';
      }
      if ($showCurrent == 1) echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
 
    } elseif ( is_tag() ) {
      echo $before . 'Posts tagged "' . single_tag_title('', false) . '"' . $after;
 
    } elseif ( is_author() ) {
       global $author;
      $userdata = get_userdata($author);
      echo $before . 'Articles posted by ' . $userdata->display_name . $after;
 
    } elseif ( is_404() ) {
      echo $before . 'Error 404' . $after;
    }
 
    if ( get_query_var('paged') ) {
      if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ' (';
      echo __('Page') . ' ' . get_query_var('paged');
      if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ')';
    }
 
    echo '</ul>';
 
  }
} // end custom_breadcrumbs()
