<?php

namespace SKChildTheme;

function get_page_owner( $post_id ) {
  if( $page_owner = get_field( 'sidansvarig', $post_id ) ) return $page_owner;

  return null;
}


function the_page_owner() {
  global $post;

  if( $page_owner = get_field( 'sidansvarig', $post->ID ) ) : 
    $name = !empty( $page_owner['user_firstname'] ) ? trim( $page_owner['user_firstname'] ) : '';
    $name .= ' ' . trim( !empty( $page_owner['user_lastname'] ) ? trim( $page_owner['user_lastname'] ) : '' );
    $email = !empty( $page_owner['user_email'] ) ? $page_owner['user_email'] : '';
    ?>
    
    <div class="page-owner">
      
      <div class="title">
        <?php _e('Sidansvarig', 'sk'); ?>
      </div>

      <div class="name">
        <?php echo $name ?>
      </div>

      <div class="email">
         <a class="of-icon" href="mailto:<?php echo $email; ?>">
      <i>
        <?php icon( 'mail' ); ?>
      </i>
      <span><?php echo _e('Skicka mail', 'sk'); ?></span>
    </a>
      </div>
        
   
    </div>
  <?php endif;
}