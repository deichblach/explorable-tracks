<div id="et_main_map"></div> 
<?php
$trackPostId = $_REQUEST['t'];
$trackPostToShow = null;
$firstPost = null;
while (have_posts()) : the_post();
    if(!isset($firstPost)){
        $firstPost = $post;
    }
    if(!isset($trackPostId)){
        break;
    }else if($trackPostId == get_the_ID()){
        $trackPostToShow = $post;
    }
endwhile;
rewind_posts();
if(!$trackPostToShow){
    $trackPostToShow = $firstPost;
}

?>
<script type="text/javascript">
    var selectedIcon = '<?php echo get_template_directory_uri().'/images/blue-marker.png';?>';
    var notSelectedIcon = '<?php echo get_template_directory_uri().'/images/red-marker.png';?>';
    function initialize( ){
        loadMapCoordinates(<?php echo $trackPostToShow->ID; ?>,function(center, zoom){
   initMap(zoom,center, google.maps.MapTypeId.<?php echo esc_js(strtoupper(et_get_option('explorable_map_type', 'Roadmap'))); ?>);     
   markers = [
   <?php
$i = 0;
while (have_posts()) : the_post();
    $et_location_lat = get_post_meta(get_the_ID(), '_et_listing_lat', true);
    $et_location_lng = get_post_meta(get_the_ID(), '_et_listing_lng', true);   
    if ('' != $et_location_lat && '' != $et_location_lng) {
        ?>
            {latLng:[<?php echo esc_html($et_location_lat);?>, <?php echo esc_html($et_location_lng); ?>],data: {postId:<?php echo get_the_ID();?>, title: '<?php echo get_the_title();?>', tags: '<?php echo wp_strip_all_tags(addslashes(get_the_term_list(get_the_ID(), 'listing_type', '', ', '))); ?>'}},
         
        <?php
    }

    $i++;
endwhile;

rewind_posts();
?>     
                ];
    et_add_markers(markers);
    changeActiveMarker(<?php echo $trackPostToShow->ID; ?>);
});
        

}
google.maps.event.addDomListener(window, 'load', initialize);   



</script>

<div id="et-slider-wrapper" class="et-map-post">
    <div id="et-map-slides">

<?php
while (have_posts()) : the_post();
    if(get_the_ID() != $trackPostToShow->ID){
        continue;
    }    
   
    ?>
            <div class="et-map-slide et-active-map-slide">
                <?php include get_stylesheet_directory().'/includes/listingMapPost.php';?>
            </div> <!-- .et-map-slide -->
    <?php
endwhile;

?>
    </div> <!-- #et-map-slides -->
</div> <!-- .et-map-post -->

<div class="cartodb-legend-stack" style="display: block;"><div class="cartodb-legend custom" style="display: block; right: 0px;bottom: 0px;position: relative; margin: 0;"><div class="custom-legend"><ul><li><div class="bullet" style="background:#d43838"></div>Fahrrad</li><li><div class="bullet" style="background:#5fd45f"></div>Auto / Zug</li><li><div class="bullet" style="background:#54aadd"></div>Flugzeug</li></ul></div></div></div>
<div id="et-slider-shadow-top">
    <div id="shadow-top"></div>
</div>
<div id="et-slider-shadow-bottom">
    <div id="shadow-bottom"></div>
</div>
