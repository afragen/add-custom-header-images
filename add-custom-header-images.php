<?php

/*
Plugin Name:       Add Custom Header Images
Plugin URI:        https://github.com/afragen/add-custom-header-images
Description:       Remove default header images and add custom header images. Images must be added to new page titled <strong>The Headers</strong>.  Based upon a post from <a href="http://juliobiason.net/2011/10/25/twentyeleven-with-easy-rotating-header-images/">Julio Biason</a>.
Version:           1.0.0
Author:            Andy Fragen
Author URI:        http://thefragens.com
Text Domain:       add-custom-header-images
Domain Path:       /languages
GitHub Plugin URI: https://github.com/afragen/add-custom-header-images
GitHub Branch:     develop
*/

/*
Copyright 2014 Andy Fragen

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
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
		add_action( 'after_setup_theme', array( $this, 'remove_header_images' ), 11, 2 );
		add_action( 'after_setup_theme', array( $this, 'new_default_header_images' ) );
		load_plugin_textdomain( 'add-custom-header-images', false, basename( dirname( __FILE__ ) ) );
	}


	/**
	 * Disable plugin if 'The Headers' page does not exist
	 */
	public function headers_page_present () {
		global $wp_version;

		if ( is_admin() ) {
			if ( ( is_null( get_page_by_title( 'The Headers' ) ) ) || ( ! ( $wp_version >= 3.4 ) ) ) {
				$warning = '<div class="error"><p>' . __( 'Add Custom Header Images requires a page titled <strong>The Headers</strong> with images and WordPress v3.4 or greater.', 'add-custom-header-images' ) . '</p></div>';
				echo $warning;
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
	 * @link http://juliobiason.net/2011/10/25/twentyeleven-with-easy-rotating-header-images/
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