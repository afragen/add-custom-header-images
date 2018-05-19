<?php

/**
 * Plugin Name:       Add Custom Header Images
 * Plugin URI:        https://github.com/afragen/add-custom-header-images
 * Description:       Remove default header images and add custom header images. Images must be added to new page titled <strong>The Headers</strong>.  Based upon a post from <a href="http://juliobiason.net/2011/10/25/twentyeleven-with-easy-rotating-header-images/">Julio Biason</a>.
 * Version:           1.7.0
 * Author:            Andy Fragen
 * Author URI:        http://thefragens.com
 * License:           GNU General Public License v2
 * License URI:       http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain:       add-custom-header-images
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/afragen/add-custom-header-images
 * Requires WP:       3.4.0
 * Requires PHP:      5.3
 */

/**
 * Class Add_Custom_Header_Images
 */
class Add_Custom_Header_Images {
	/**
	 * Variable to hold the data for `get_page_by_title()`.
	 *
	 * @var array|null|\WP_Post
	 */
	private $the_headers_page;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$the_headers_title      = __('The Headers', 'add-custom-header-images');
		$this->the_headers_page = get_page_by_title(esc_attr($the_headers_title));
		$this->run();
	}

	/**
	 * Let's get started.
	 *
	 * @return bool
	 */
	public function run() {
		global $wp_version;
		load_plugin_textdomain('add-custom-header-images', false, basename(dirname(__FILE__)));

		if ($wp_version < 3.4 || (is_admin() && null === $this->the_headers_page)
		) {
			add_action('admin_notices', array( $this, 'headers_page_present' ));

			return false;
		}
		if (! is_admin()) {
			add_action('after_setup_theme', array( $this, 'new_default_header_images' ), 99);
		}
	}

	/**
	 * Disable plugin if 'The Headers' page does not exist.
	 */
	public function headers_page_present() {
		?>
		<div class="error notice is-dismissible">
			<p>
				<?php printf(
					esc_html__('Add Custom Header Images requires a page titled %sThe Headers%s with images and WordPress v3.4 or greater.', 'add-custom-header-images'),
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
		if (empty($_wp_default_headers)) {
			return false;
		}

		$header_ids = array();
		foreach ((array) array_keys($_wp_default_headers) as $key) {
			if (! is_int($key)) {
				$header_ids[] = $key;
			}
		}

		unregister_default_headers($header_ids);
	}

	/**
	 * Add new default header images.
	 *
	 * @link http://juliobiason.net/2011/10/25/twentyeleven-with-easy-rotating-header-images/
	 */
	public function new_default_header_images() {
		if (! $this->the_headers_page instanceof \WP_Post) {
			return false;
		}

		$this->remove_default_header_images();
		$headers      = array();
		$images_query = new \WP_Query(
			array(
				'post_parent'    => $this->the_headers_page->ID,
				'post_status'    => 'inherit',
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'order'          => 'ASC',
				'orderby'        => 'menu_order ID',
			)
		);
		$images       = $images_query->posts;

		if (empty($images)) {
			return false;
		}

		foreach ($images as $image) {
			$thumb = wp_get_attachment_image_src($image->ID, 'medium');

			$headers[] = array(
				'url'           => $image->guid,
				'thumbnail_url' => $thumb[0],
				'description'   => $image->post_title,
				'attachment_id' => $image->ID,
			);
		}

		register_default_headers($headers);
	}
}

new Add_Custom_Header_Images();
