<?php

/**
 * Plugin Name:       Add Custom Header Images
 * Plugin URI:        https://github.com/afragen/add-custom-header-images
 * Description:       Remove default header images and add custom header images. Images must be added to new page titled <strong>The Headers</strong>.  Based upon a post from <a href="http://juliobiason.net/2011/10/25/twentyeleven-with-easy-rotating-header-images/">Julio Biason</a>.
 * Version:           1.5.1
 * Author:            Andy Fragen
 * Author URI:        http://thefragens.com
 * License:           GNU General Public License v2
 * License URI:       http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain:       add-custom-header-images
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/afragen/add-custom-header-images
 * GitHub Branch:     develop
 * Requires WP:       3.4.0
 */


/**
 * Class Add_Custom_Header_Images
 */
class Add_Custom_Header_Images {

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wp_version;

		load_plugin_textdomain( 'add-custom-header-images', false, basename( dirname( __FILE__ ) ) );

		if ( is_admin() &&
		     is_null( get_page_by_title( esc_attr__( 'The Headers', 'add-custom-header-images' ) ) ) ||
		     ! $wp_version >= 3.4
		) {
			add_action( 'admin_notices', array( $this, 'headers_page_present' ) );

			return false;
		}

		add_action( 'after_setup_theme', array( $this, 'new_default_header_images' ), 99 );
	}


	/**
	 * Disable plugin if 'The Headers' page does not exist.
	 */
	public function headers_page_present() {
		?>
		<div class="error notice is-dismissible">
			<p>
				<?php printf(
					esc_html__( 'Add Custom Header Images requires a page titled %sThe Headers%s with images and WordPress v3.4 or greater.', 'add-custom-header-images' ),
					'<strong>',
					'</strong>'
				); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Remove default header images.
	 */
	public function remove_default_header_images() {
		global $_wp_default_headers;
		$header_ids = array();

		if ( empty( $_wp_default_headers ) ) {
			return false;
		}

		foreach ( $_wp_default_headers as $key => $value ) {
			if ( ! is_int( $key ) ) {
				$header_ids[] = $key;
			}
		}

		unregister_default_headers( $header_ids );
	}

	/**
	 * Add new default header images.
	 *
	 * @link http://juliobiason.net/2011/10/25/twentyeleven-with-easy-rotating-header-images/
	 */
	public function new_default_header_images() {
		$page = get_page_by_title( __( 'The Headers', 'add-custom-header-images' ) );
		if ( ! is_object( $page ) ) {
			return false;
		}

		$this->remove_default_header_images();
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

		if ( empty( $images ) ) {
			return false;
		}

		foreach ( $images as $key => $image ) {
			$thumb = wp_get_attachment_image_src( $image->ID, 'medium' );

			$headers[] = array(
				'url'           => $image->guid,
				'thumbnail_url' => $thumb[0],
				'description'   => $image->post_title,
				'attachment_id' => $image->ID,
			);
		}

		register_default_headers( $headers );
	}

}

new Add_Custom_Header_Images();
