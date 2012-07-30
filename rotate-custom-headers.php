<?php

/*
Plugin Name: Rotate Custom Headers
Plugin URI: https://github.com/afragen/rotate-custom-headers
Description: Remove default headers and add custom headers. Images must be added to new page titled 'The Headers'.  Idea and code from <a href="http://juliobiason.net/2011/10/25/twentyeleven-with-easy-rotating-header-images/">Julio Biason</a>.
Version: 0.1
Author: Andy Fragen
License: GNU General Public License v2
License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


// REMOVE DEFAULT HEADER IMAGES
function ajf_remove_header_images() {
	global $_wp_default_headers;
    $header_ids = array();
    foreach ( $_wp_default_headers as $key => $value) {
    	if ( !is_int($key) ) { $header_ids[] = $key; }
    }
    unregister_default_headers( $header_ids );
}
add_action( 'after_setup_theme', 'ajf_remove_header_images', 11 );

//ADD NEW DEFAULT HEADER IMAGES
//http://juliobiason.net/2011/10/25/twentyeleven-with-easy-rotating-header-images/
function wptips_new_default_header_images() {
    $child2011_dir = get_bloginfo('stylesheet_directory');
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
			'description' => __($info->post_title, 'twentyelevenheaders')
		);
	}

    register_default_headers($images);
}
add_action( 'after_setup_theme', 'wptips_new_default_header_images' );

// GithubUpdater
if ( is_admin() ) {
	global $wp_version;
	include_once( 'updater.php' );
	$config = array(		
		'slug' => plugin_basename(__FILE__),
		'proper_folder_name' => dirname( plugin_basename(__FILE__) ),
		'api_url' => 'https://api.github.com/repos/afragen/rotate-custom-headers',
		'raw_url' => 'https://raw.github.com/afragen/rotate-custom-headers/master',
		'github_url' => 'https://github.com/afragen/rotate-custom-headers',
		'zip_url' => 'https://github.com/afragen/rotate-custom-headers/zipball/master',
		'sslverify' => true,
		'requires' => $wp_version,
		'tested' => $wp_version,
		'readme' => 'readme.md'

	);
	new WPGitHubUpdater($config);
}

?>