<?php

/*
Plugin Name:       Add Custom Header Images
Plugin URI:        https://github.com/afragen/add-custom-header-images
Description:       Remove default header images and add custom header images. Images must be added to new page titled <strong>The Headers</strong>.  Based upon a post from <a href="http://juliobiason.net/2011/10/25/twentyeleven-with-easy-rotating-header-images/">Julio Biason</a>.
Author:            Andy Fragen
Author URI:        http://thefragens.com/blog/
Version:           0.8.0
License:           GNU General Public License v2
License URI:       http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
GitHub Plugin URI: https://github.com/afragen/add-custom-header-images
GitHub Branch:     develop
*/

/**
 * Class Add_Custom_Header_Images
 */
class Add_Custom_Header_Images {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'headers_page_present' ) );
		add_action( 'after_setup_theme', array( $this, 'remove_header_images', 11 ) );
		add_action( 'after_setup_theme', array( $this, 'new_default_header_images' ) );
	}


	/**
	 * Disable plugin if 'The Headers' page does not exist
	 */
	public function headers_page_present () {
		global $wp_version;

		if ( is_admin() ) {
			if ( ( is_null( get_page_by_title( 'The Headers' ) ) ) || ( ! ( $wp_version >= 3.4 ) ) ) {
				echo '<div class="error"><p>Add Custom Header Images requires a page titled <strong>The Headers</strong> with images and WordPress v3.4 or greater.</p></div>';
				deactivate_plugins( __FILE__ );
			}
		}
	}

	/**
	 * Remove default header images
	 */
	public function remove_header_images() {
		global $_wp_default_headers;
		$header_ids = array();

		foreach ( $_wp_default_headers as $key => $value ) {
			if ( ! is_int( $key ) ) { $header_ids[] = $key; }
		}

		unregister_default_headers( $header_ids );
	}

	/**
	 * Add new default header images
	 *
	 * @source http://juliobiason.net/2011/10/25/twentyeleven-with-easy-rotating-header-images/
	 */
	public function new_default_header_images() {
		$page    = get_page_by_title( 'The Headers' );
		$headers = array();
		$images  = get_children(
						array(
							'post_parent'    => $page->ID,
							'post_status'    => 'inherit',
							'post_type'      => 'attachment',
							'post_mime_type' => 'image',
							'order'          => 'ASC',
							'orderby'        => 'menu_order ID',
						)
					);

		foreach ( $images as $key => $image ) {
			$thumb     = wp_get_attachment_image_src( $image->ID, 'medium' );
			$headers[] = array(
				'url'           => $image->guid,
				'thumbnail_url' => $thumb[0],
				'description'   => $image->post_title,
			);
		}

		register_default_headers( $headers );
	}

}

new Add_Custom_Header_Images();