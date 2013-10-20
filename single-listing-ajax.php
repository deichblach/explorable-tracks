<?php while ( have_posts() ) : the_post();    
    get_template_part('includes/listingMapPost', 'front_page');
endwhile; ?>
