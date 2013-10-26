<?php
require_once 'KLogger.php';
require_once 'CartoDB.php';
$log = KLogger::instance('/tmp');
add_filter('et_theme_image_sizes', 'etc_set_thumbnail_size');
add_filter('et_map_image_height', 'etc_get_map_image_height');

function etc_set_thumbnail_size($args) {
    foreach ($args as $image_size_dimensions => $image_size_name) {
        if ($image_size_name == 'et-map-slide-thumb') {
            unset($args[$image_size_dimensions]);
            $args['480x160'] = 'et-map-slide-thumb';
        }
    }
}

function etc_get_map_image_height($args) {
    return 160;
}

add_action('add_meta_boxes', 'etc_listing_posttype_meta_box');

function etc_listing_posttype_meta_box() {
    add_meta_box('etc_settings_meta_box', __('Extra settings', 'Explorable-child'), 'etc_settings_meta_box', 'listing', 'normal', 'high');
}

function etc_settings_meta_box() {
    ?>
    <p>
        <label for="etc_listing_full_post" style="min-width: 150px; display: inline-block;"><?php esc_html_e('Use Full Page', 'Explorable-child'); ?>: </label>
        <input type="checkbox" name="etc_listing_full_post" id="etc_listing_full_post" class="regular-text" value="on" <?php checked('on' == get_post_meta(get_the_ID(), '_et_full_post', true) || '' == get_post_meta(get_the_ID(), '_et_full_post', true)); ?> />
    </p>
    <?php
}

add_action('save_post', 'etc_metabox_settings_save_details', 10, 2);
add_action('save_post', 'etc_update_tracks', 10, 2);

function etc_metabox_settings_save_details($post_id, $post) {
    $checkResult = validateCorrectSave($post_id, $post);
    if ($checkResult) {
        return $post_id;
    }
    if ('listing' == $post->post_type) {
        if (isset($_POST['etc_listing_full_post']))
            update_post_meta($post_id, '_et_full_post', sanitize_text_field($_POST['etc_listing_full_post']));
        else
            update_post_meta($post_id, '_et_full_post', 'off');
    }
}

function validateCorrectSave($post_id, $post) {
    global $pagenow;
    if ('post.php' != $pagenow)
        return $post_id;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return $post_id;
    $post_type = get_post_type_object($post->post_type);
    if (!current_user_can($post_type->cap->edit_post, $post_id))
        return $post_id;
    if (!isset($_POST['et_settings_nonce']) || !wp_verify_nonce($_POST['et_settings_nonce'], basename(__FILE__)))
        return $post_id;
    return null;
}

function etc_update_tracks($post_id, $post) {
    $checkResult = validateCorrectSave($post_id, $post);
    if($checkResult){
        return $post_id;
    }
    $carto = new CartoDB('deichblach', '506129f263820b6c8da1ebfa4c2a101e8d7dd83f');
    $carto->updatePost($post);
}

function register_tracks_attachments($attachments) {
//Configuration of attachments
    $defaults = array(
        // title of the meta box (string)
        'label' => __('Routen', 'routen'),
        // all post types to utilize (string|array)
        'post_type' => array('post', 'page', 'listing'),
        // meta box position (string) (normal, side or advanced)
        'position' => 'normal',
        // meta box priority (string) (high, default, low, core)
        'priority' => 'high',
        // maximum number of Attachments (int) (-1 is unlimited)
        'limit' => -1,
        // allowed file type(s) (array) (image|video|text|audio|application)
        'filetype' => null, // no filetype limit
        // include a note within the meta box (string)
        'note' => null, // no note
        // by default new Attachments will be appended to the list
        // but you can have then prepend if you set this to false
        'append' => true,
        // text for 'Attach' button (string)
        'button_text' => __('Attach', 'attachments'),
        // text for modal 'Attach' button (string)
        'modal_text' => __('Attach', 'attachments'),
        // which tab should be the default in the modal (string) (browse|upload)
        'router' => 'upload',
        // fields for this instance (array)
        'fields' => array(
            array(
                'name' => 'endflag', // unique field name
                'type' => 'select', // registered field type
                'label' => __('Soll der Endpunkt als "Start des Posts" genommen werden? (Leer = Nein / Sonst = ja)', 'attachments'), // label to display
                'default' => 'true', // default value upon selection
                'meta' => array(
                                'allow_null' => false,
                                'multiple' => false,
                                'options' => array(
                                    'true' => 'Ja',
                                    'false' => 'Nein'
                                )
                            )
            ),
            array(
                'name' => 'vehicle', // unique field name
                'type' => 'select', // registered field type
                'label' => __('Fahrzeug', 'attachments'), // label to display
                'default' => 'bike', // default value upon selection
                'meta' => array(
                                'allow_null' => false,
                                'multiple' => false,
                                'options' => array(
                                    'bike' => 'Fahrrad',
                                    'plane' => 'Flugzeug',
                                    'car' => 'Auto'
                                )
                            )
            )
        ),
    );
    $attachments->register('tracks', $defaults);
}

add_action('attachments_register', 'register_tracks_attachments');


//Add needed js / css libraries
add_action('wp_enqueue_scripts', 'etc_enqueue_carto_scripts');

function etc_enqueue_carto_scripts() {
    wp_enqueue_script('leaflet', 'http://cdn.leafletjs.com/leaflet-0.6.4/leaflet.js', null, '0.6.4');
    wp_enqueue_style('leaflet', 'http://cdn.leafletjs.com/leaflet-0.6.4/leaflet.css', null, '0.6.4');
    if (et_is_listing_page()) {
        wp_enqueue_script('cartodb', 'http://libs.cartocdn.com/cartodb.js/v3/cartodb.js', array('gmap3','google-maps-api'), '3.0', false);
        wp_enqueue_style('cartodb', 'http://libs.cartocdn.com/cartodb.js/v3/themes/css/cartodb.css', null, '3.0');
        wp_enqueue_script('gvector', get_stylesheet_directory_uri().'/js/gvector.js', array('cartodb'), '1.3.0');
        wp_enqueue_script('mercator', get_stylesheet_directory_uri().'/js/mercator.js', array('google-maps-api'));

        wp_enqueue_script('explorable', get_stylesheet_directory_uri().'/js/explorable.js', array('gvector'));
        wp_enqueue_script('explorableChild', get_stylesheet_directory_uri().'/js/explorableChild.js', array('explorable','mercator'));
        wp_enqueue_script('jquery-history', get_stylesheet_directory_uri().'/js/jquery.history.js', array('jquery'));
        wp_enqueue_style('mCustomScrollbar',get_stylesheet_directory_uri().'/js/jquery.mCustomScrollbar.css',null,'2.8.2');
        wp_enqueue_script('mCustomScrollbar',  get_stylesheet_directory_uri().'/js/jquery.mCustomScrollbar.concat.min.js', array('jquery'),'2.8.2',true);
        
        
    }
}

//Add action for the footer into php page
add_action('wp_footer', 'addCartoDBLayer');

function addCartoDBLayer() {
    if (et_is_listing_page()) {
        rewind_posts();       
        ?>
        <script type="text/javascript">
             <?php
                $ids = array();
                $idFound = false;
                while ( have_posts() ) : the_post();
                    if(!$idFound){
                        echo 'var lastId = '.get_the_ID().';';
                        $idFound = true;
                    }
                    $ids[] =  get_the_ID();
                endwhile;
                $idString = 'var idCause = \'('.implode(',',$ids).')\';';                
                echo $idString;
                rewind_posts()
                ?>                        
        </script>
        <?php
    }
}
function ect_world_endpoint() {
    add_rewrite_endpoint( 'world', EP_ALL );
    add_rewrite_endpoint( 'ajax', EP_ALL );
}
add_action( 'init', 'ect_world_endpoint' );

add_filter('request', 'etc_set_worldvar');
function etc_set_worldvar($vars) {
  $vars = setDefaultValue($vars, 'world');
  $vars = setDefaultValue($vars, 'ajax');
  return $vars;
}
function setDefaultValue($vars, $varName){
    if (isset($vars[$varName]) && $vars[$varName] === '') {
    $vars[$varName] = true;
  }
  return $vars;
}
function etc_custom_templates($template){
        if( ! is_singular( array( 'listing' ) ) )
            return $template;
        global $wp_query;
        $post_type = get_post_type();
        $listingOnMap = isset($wp_query->query_vars['world']);
        $template = $listingOnMap ? get_query_template('single-listing-on-map') : (get_query_var('ajax') ? get_query_template('single-listing-ajax') : get_query_template( 'single-'.$post_type ));
        return $template;
}
function isWorldMapShown(){
     return (et_is_listing_page() && !is_single()) || (get_query_var('world') && is_singular('listing'));
}
add_action( 'template_include', 'etc_custom_templates' );

function etc_theme_setup() {
	load_child_theme_textdomain( 'Explorable', get_stylesheet_directory() . '/lang' );
}
add_action( 'after_setup_theme', 'etc_theme_setup' );
