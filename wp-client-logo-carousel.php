<?php
/**
 * Plugin Name: WP Client Logo Carousel
 * Plugin URI: https://aftabhusain.wordpress.com/
 * Description: Display client logos responsive carousel with the help of a shortcode.
 * Version: 1.0.1
 * Author: Aftab Husain
 * Author URI: https://aftabhusain.wordpress.com/
 * License: GPLv2
 */
 
//WP Client Logos post type to add images	
add_action('init', 'client_logo_register');
function client_logo_register() {

	$labels = array(
		'name' => _x('WP Client Logo', 'post type general name'),
		'singular_name' => _x('Client Logo', 'post type singular name'),
		'add_new' => _x('Add New Client Logo', 'Client Logo'),
		'add_new_item' => __('Add New Client Logo'),
		'edit_item' => __('Edit Client Logo'),
		'new_item' => __('New Client Logo'),
		'view_item' => __('View Client Logo'),
		'search_items' => __('Search Client Logo'),
		'not_found' =>  __('Nothing found'),
		'not_found_in_trash' => __('Nothing found in Trash'),
		'parent_item_colon' => ''
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => true,
		'menu_icon' => 'dashicons-format-image',
		'rewrite' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => null,
		'supports' => array( 'title', 'thumbnail' )
	  ); 

	register_post_type( 'client-logo' , $args );
} 

// Add the posts and pages columns filter. They can both use the same function.
add_filter('manage_posts_columns', 'wpaft_add_post_thumbnail_column', 2);
	
// Add the column
function wpaft_add_post_thumbnail_column($cols){
  	
	global $post;
	$pst_type=$post->post_type;
		if( $pst_type == 'client-logo'){ 
		$cols['wpaft_logo_thumb'] = __('Logo Image');
		$cols['wpaft_client_url'] = __('Client Website Url');
		}
	return $cols;
}

// Hook into the posts an pages column managing. Sharing function callback again.
add_action('manage_posts_custom_column', 'wpaft_display_post_thumbnail_column', 5, 2);
	
// Grab featured-thumbnail size post thumbnail and display it.
function wpaft_display_post_thumbnail_column($col, $id){
  switch($col){
	case 'wpaft_logo_thumb':
	  if( function_exists('the_post_thumbnail') ){
	
		$post_thumbnail_id = get_post_thumbnail_id($id);
		$post_thumbnail_img = wp_get_attachment_image_src($post_thumbnail_id, 'featured_preview');
		$post_thumbnail_img= $post_thumbnail_img[0];
		if($post_thumbnail_img !='')
		  echo '<img width="120" height="120" src="' . $post_thumbnail_img . '" />';
		else
		  echo 'No logo added.';	
	  }
	  else{
		echo 'No logo added.';
	  }	
	case 'wpaft_client_url':
		if($col == 'wpaft_client_url'){
			echo get_post_meta( $id, 'wpaft_clientlogo_meta_url', true );;
		} 		   
	  break;
 
  }
}

// client logo Meta Box
function wpaft_clientlogo_add_meta_box(){
// add meta Box
 remove_meta_box( 'postimagediv', 'post', 'side' );
 add_meta_box('postimagediv', __('Client Logo'), 'post_thumbnail_meta_box', 'client-logo', 'normal', 'high');
 add_meta_box('wpaft_clientlogo_meta_id', __('Client Website Url'), 'wpaft_meta_callback', 'client-logo', 'normal', 'high');
}
add_action('add_meta_boxes' , 'wpaft_clientlogo_add_meta_box');

// client logo Meta Box Call Back Funtion
function wpaft_meta_callback($post){

    wp_nonce_field( basename( __FILE__ ), 'aft_nonce' );
    $aft_stored_meta = get_post_meta( $post->ID );
    ?>

    <p>
        <label for="wpaft_clientlogo_meta_url" class="wpaft_clientlogo_meta_url"><?php _e( 'Client Website Url', '' )?></label>
        <input class="widefat" type="text" name="wpaft_clientlogo_meta_url" id="wpaft_clientlogo_meta_url" value="<?php if ( isset ( $aft_stored_meta['wpaft_clientlogo_meta_url'] ) ) echo $aft_stored_meta['wpaft_clientlogo_meta_url'][0]; ?>" /> <br>
		<em>(For Example: http://clients-website-url.com)</em>
    </p>

<?php

}

//client logo Save Meta Box 
function wpaft_clientlogo_meta_save( $post_id ) {

    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'wpaft_nonce' ] ) && wp_verify_nonce( $_POST[ 'wpaft_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';

    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }

    // Checks for input and sanitizes/saves if needed
    if( isset( $_POST[ 'wpaft_clientlogo_meta_url' ] ) ) {
        update_post_meta( $post_id, 'wpaft_clientlogo_meta_url', sanitize_text_field( $_POST[ 'wpaft_clientlogo_meta_url' ] ) );
    }

}
add_action( 'save_post', 'wpaft_clientlogo_meta_save' );


//client logo admin style
function wpaft_testimonials_dashboard_icon(){
?>
 <style>
#toplevel_page_logo-client-carousel {
 display:none; 
}
</style>
<?php
}
add_action( 'admin_head', 'wpaft_testimonials_dashboard_icon' );
 
 
 
// Initialize the plugin options on first run
function wpaft_initialize(){
	$options_not_set = get_option('wpaft_post_type_settings');
	if( $options_not_set ) return;
	
	$slider_settings = array( 'items' => 3, 'single_item' => false, 'slide_speed' => 500, 'pagination_speed' => 500, 'rewind_speed' => 500, 'auto_play' => true, 'stop_on_hover' => true, 'navigation' => false, 'pagination' => true, 'responsive' => true );
	update_option('wpaft_slider_settings', $slider_settings);
}
register_activation_hook(__FILE__, 'wpaft_initialize');

// Delete the plugin options on uninstall
function wpaft_remove_options(){
	delete_option('wpaft_slider_settings');
}
register_uninstall_hook(__FILE__, 'wpaft_remove_options');



// Setup the shortcode
function wpaft_logo_slider_callback( $atts ) {
	
	//include css and js start
	wp_enqueue_style( 'wpaft-logo-slider', plugins_url('includes/client-carousel.css', __FILE__), array(), '1.0', 'all' );
	wp_enqueue_script( "wpaft-logo-slider", plugins_url('includes/client-carousel.js', __FILE__ ), array('jquery') );
	$slider_settings = get_option('wpaft_slider_settings');
	wp_localize_script( 'wpaft-logo-slider', 'wpaft', $slider_settings);
	//include css and js end
	ob_start();
    extract( shortcode_atts( array (
        'type' => 'client-logo',
        'order' => 'date',
        'orderby' => 'title',
        'posts' => -1,
    
    ), $atts ) );
    $options = array(
        'post_type' => $type,
        'order' => $order,
        'orderby' => $orderby,
        'posts_per_page' => $posts,
  		
    );
    $query = new WP_Query( $options );?>
    <?php if ( $query->have_posts() ) { ?>
	<div id="wpaft-logo-slider" class="owl-carousel">
	<?php while ( $query->have_posts() ) : $query->the_post(); ?>
	
	<div class="logo-container">
				<a target="_blank" href="<?php echo get_post_meta(get_the_ID(),'wpaft_clientlogo_meta_url',true);?>"><?php the_post_thumbnail('full'); ?></a>
	</div>
	<?php endwhile;
      wp_reset_postdata(); ?>
	  </div>
	<?php 
	
	}else{
		
		echo "No Image is added..";
	}
	
	return ob_get_clean();
}
add_shortcode( 'wpaft_logo_slider', 'wpaft_logo_slider_callback' );

//include setting page
include('includes/carousel-settings.php');
?>