<form action="/" method="get">
  <div class="of-right">
    <button type="submit" class="of-btn"><span><?php _e( 'Sök', 'sk' ); ?></span></button>
  </div>
  <div class="of-overflow">
    <input class="of-searchfield of-no-margin" type="text" name="s" value="<?php the_search_query(); ?>" >
  </div>
</form>