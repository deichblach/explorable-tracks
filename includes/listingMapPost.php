<?php
    $classtext = '';
    $titletext = get_the_title();
    $thumb = '';
    $width = (int) apply_filters('et_map_image_width', 480);
    $height = (int) apply_filters('et_map_image_height', 240);
    $thumbnail = get_thumbnail($width, $height, $classtext, $titletext, $titletext, false, 'MapIndex');
    $thumb = $thumbnail["thumb"];
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
 if ('' != $thumb) { ?>
                    <div class="thumbnail">
                    <?php print_thumbnail($thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext); ?>
                        <div class="et-description">
                            <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                        <?php if (( $et_description = get_post_meta(get_the_ID(), '_et_listing_description', true) ) && '' != $et_description) : ?>
                                <p><?php echo esc_html($et_description); ?></p>
                            <?php endif; ?>
                            <?php if (( $et_rating = et_get_rating() ) && 0 != $et_rating) : ?>
                                <span class="et-rating"><span style="<?php printf('width: %dpx;', esc_attr($et_rating * 21)); ?>"></span></span>
                            <?php endif; ?>
                        </div>
                            <?php
                            printf('<div class="et-date-wrapper"><span class="et-date">%s<span>%s</span></span></div>', get_the_time(_x('F j', 'Location date format first part', 'Explorable')), get_the_time(_x('Y', 'Location date format second part', 'Explorable'))
                            );
                            ?>
                    </div>
                    <?php } ?>

                   
                    <div class="et-map-postmeta">
                          
                        <div class="etc_previous_post_map">
                         <?php 
                         $previousPost = get_previous_post(is_tax(), '');
                         if($previousPost != null){
                         ?> 
                            <a class="ajax" href="<?php echo get_site_url(); ?>/?t=<?php echo $previousPost->ID;?>" rel="prev"><span>&laquo;</span> vorheriger Beitrag</a>                            
                         <?php }?>
                        </div>
                         <div class="etc_next_post_map">
                        <?php
                        $nextPost = get_next_post(is_tax(),  '');
                       
                        if($nextPost != null){
                            ?>
                            
                                 <a class="ajax" href="<?php echo get_site_url(); ?>/?t=<?php echo $nextPost->ID;?>" rel="prev">nächster Beitrag <span>&raquo;</span></a>                            
                            
                        <?php } ?>
                                 </div>
                             <?php if (( $et_location_address = get_post_meta(get_the_ID(), '_et_listing_custom_address', true) ) && '' != $et_location_address) : ?>
                              <div class="etc_location_header"><?php echo esc_html($et_location_address); ?></div>
                              <?php endif; ?>
                    </div>
                

                <div class="et-place-content">
                    <div class="et-place-text-wrapper">
                        <div class="et-place-main-text">
                            <div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>
                            <div class="viewport">
                                <div class="overview">
    <?php
    if (has_excerpt())
        the_excerpt();
    else
        the_content('');
    ?>
                                </div>
                            </div>
                        </div> <!-- .et-place-main-text -->
                    </div> <!-- .et-place-text-wrapper -->
                    <a class="more" href="<?php the_permalink(); ?>"><?php esc_html_e('More Information', 'Explorable'); ?><span>&raquo;</span></a>
                </div> <!-- .et-place-content -->
