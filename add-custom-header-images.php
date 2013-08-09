<?php

/*
Plugin Name: Add Custom Header Images
Plugin URI: https://github.com/afragen/add-custom-header-images
GitHub Plugin URI: https://github.com/afragen/add-custom-header-images
Description: Remove default header images and add custom header images. Images must be added to new page titled <strong>The Headers</strong>.  Based upon a post from <a href="http://juliobiason.net/2011/10/25/twentyeleven-with-easy-rotating-header-images/">Julio Biason</a>.
Version: 0.2
Author: Andy Fragen
Author URI: http://thefragens.com/blog/
License: GNU General Public License v2
License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

//Disable plugin if 'The Headers' page doesn't exist
function achi_headers_page_present () {
	global $wp_version;

	if( is_admin() ) {
		if( ( is_null( get_page_by_title( 'The Headers' ) ) ) || ( !( $wp_version >= 3.4 ) ) ) {
			echo '<div class="error"><p>Add Custom Header Images requires a page titled <strong>The Headers</strong> with images and WordPress v3.4 or greater.</p></div>';
			deactivate_plugins( __FILE__ );
		}
	}
}

//REMOVE DEFAULT HEADER IMAGES
function achi_remove_header_images() {
	global $_wp_default_headers;
	$header_ids = array();

	foreach( $_wp_default_headers as $key => $value )
		if( !is_int( $key ) ) $header_ids[] = $key;

    unregister_default_headers( $header_ids );
}

//ADD NEW DEFAULT HEADER IMAGES
//http://juliobiason.net/2011/10/25/twentyeleven-with-easy-rotating-header-images/
function achi_new_default_header_images() {
	$images = array();
	$page = get_page_by_title( 'The Headers' );

	$images = get_children( array(
		'post_parent'    => $page->ID,
		'post_status'    => 'inherit',
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'order'          => 'ASC',
		'orderby'        => 'menu_order ID',
		));

	foreach( $images as $key => $image ) {
		$thumb = wp_get_attachment_image_src( $image->ID, 'medium' );
		$headers[] = array(
			'url'           => $image->guid,
			'thumbnail_url' => $thumb[0],
			'description'   => $image->post_title,
		);
	}

	register_default_headers( $headers );
}

if( !is_admin() ) return;
add_action( 'plugins_loaded', 'achi_headers_page_present' );
add_action( 'after_setup_theme', 'achi_remove_header_images', 11 );
add_action( 'after_setup_theme', 'achi_new_default_header_images' );

