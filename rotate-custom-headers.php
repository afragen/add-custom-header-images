<?php

/*
Plugin Name: Rotate Custom Headers
Plugin URI: https://github.com/afragen/rotate-custom-headers
Description: Remove default headers and add custom headers. Images must be added to new page titled 'The Headers'.  Idea and code from <a href="http://juliobiason.net/2011/10/25/twentyeleven-with-easy-rotating-header-images/">Julio Biason</a>.
Version: 0.5.1
Author: Andy Fragen
License: GNU General Public License v2
License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

//Disable plugin if 'The Headers' page doesn't exist

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
global $wp_version;
$pageIDs = get_all_page_ids();
$pageTitles = array();
$plugin = plugin_basename( __FILE__ );
$plugin_data = get_plugin_data( __FILE__, false );
foreach ( $pageIDs as $pageID ) {
	$pageTitles[] = get_the_title($pageID);
}
if ( is_admin() ) {
	if ( ( !in_array('The Headers', $pageTitles) ) || ( !($wp_version >= 3.4) ) ) {
		deactivate_plugins( $plugin );
		wp_die( "'".$plugin_data['Name']."' requires a page titled 'The Headers' with images. Deactivating Plugin.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
	}
}
//print_r($pageTitles);
add_action( 'after_setup_theme', 'ajf_remove_header_images', 11 );
add_action( 'after_setup_theme', 'wptips_new_default_header_images' );


// REMOVE DEFAULT HEADER IMAGES
function ajf_remove_header_images() {
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
		'readme' => 'readme.txt'

	);
	new WPGitHubUpdater($config);
}

?>