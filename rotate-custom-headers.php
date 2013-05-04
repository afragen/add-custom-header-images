<?php

/*
Plugin Name: Rotate Custom Headers
Plugin URI: https://github.com/afragen/rotate-custom-headers
Description: Remove default headers and add custom headers. Images must be added to new page titled 'The Headers'.  Idea and code from <a href="http://juliobiason.net/2011/10/25/twentyeleven-with-easy-rotating-header-images/">Julio Biason</a>.
Version: 0.6.4
Author: Andy Fragen
Author URI: http://thefragens.com/blog/
License: GNU General Public License v2
License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

//Disable plugin if 'The Headers' page doesn't exist
add_action( 'plugins_loaded', 'rch_headers_page_present' );
function rch_headers_page_present () {
	global $wp_version;
	$pageIDs = get_all_page_ids();
	$pageTitles = array();
	foreach ( $pageIDs as $pageID ) {
		$pageTitles[] = get_the_title($pageID);
	}
	if ( is_admin() ) {
		if ( ( !in_array('The Headers', $pageTitles) ) || ( !($wp_version >= 3.4) ) ) {
			echo '<div class="error"><p>Rotate Custom Headers requires a page titled \'The Headers\' with images and WordPress v3.4 or greater.</p>
    </div>';
			deactivate_plugins(__FILE__);
		}
	}
}

//print_r($pageTitles);
add_action( 'after_setup_theme', 'rch_remove_header_images', 11 );
add_action( 'after_setup_theme', 'wptips_new_default_header_images' );


// REMOVE DEFAULT HEADER IMAGES
function rch_remove_header_images() {
	global $_wp_default_headers;
    $header_ids = array();
    foreach ( $_wp_default_headers as $key => $value) {
    	if ( !is_int($key) ) { $header_ids[] = $key; }
    }
    unregister_default_headers( $header_ids );
}

//ADD NEW DEFAULT HEADER IMAGES
//http://juliobiason.net/2011/10/25/twentyeleven-with-easy-rotating-header-images/
function wptips_new_default_header_images() {
	$images = array();

	$page = get_page_by_title('The Headers');
	/*
	echo ">>>";
	print_r($page);
	echo "<<<";

	echo "]]]";*/
	$attachments = get_children(
		array(
			'post_parent' => $page->ID,
			'post_type' => 'attachment',
			'orderby' => 'menu_order ASC, ID',
			'order' => 'DESC'
		) 
	);
	/*
	print_r($attachments);
	echo "[[[";
	 */
	foreach($attachments as $id => $info) {
		$image_id = $info->ID;
		$url = wp_get_attachment_image_src($image_id, 'full');
		$thumb = wp_get_attachment_image_src($image_id, 'medium');
		$images[] = array(
			'url' => $url[0],
			'thumbnail_url' =>  $thumb[0],
			'description' => __($info->post_title, 'customheaders')
		);
	}

    register_default_headers($images);
}

//Load github_plugin_updater
if ( is_admin() )
	add_action( 'plugins_loaded', 'rch_github_plugin_updater' );
	
function rch_github_plugin_updater() {

	if ( ! function_exists( 'github_plugin_updater_register' ) )
		return false;

	github_plugin_updater_register( array(
		'owner'	=> 'afragen',
		'repo'	=> 'rotate-custom-headers',
		'slug'	=> 'rotate-custom-headers/rotate-custom-headers.php', // defaults to the repo value ('repo/repo.php')
	) );
}

