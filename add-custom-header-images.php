<?php
/**
 * Add Custom Header Images
 *
 * @package wordpress-plugin
 * @link http://juliobiason.net/2011/10/25/twentyeleven-with-easy-rotating-header-images/
 *
 * Plugin Name:       Add Custom Header Images
 * Plugin URI:        https://github.com/afragen/add-custom-header-images
 * Description:       Remove default header images and add custom header images. Images must be added to new page titled <strong>The Headers</strong>.  Based upon a post from <a href="http://juliobiason.net/2011/10/25/twentyeleven-with-easy-rotating-header-images/">Julio Biason</a>.
 * Version:           2.3.3
 * Author:            Andy Fragen
 * Author URI:        https://thefragens.com
 * License:           GNU General Public License v2
 * License URI:       https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain:       add-custom-header-images
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/afragen/add-custom-header-images
 * Requires at least: 5.2
 * Requires PHP:      5.6
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
	 * Variable to hold header image URL.
	 *
	 * @var string
	 */
	public $header_image;

	/**
	 * Variable to hold header images.
	 *
	 * @var array
	 */
	public $header_images;

	/**
	 * Variable to hold image attributes.
	 *
	 * @var array
	 */
	private $image_attr;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$the_headers_title = __( 'The Headers', 'add-custom-header-images' );
		$query             = new WP_Query(
			[
				'post_type'   => 'page',
				'title'       => $the_headers_title,
				'post_status' => 'all',
			]
		);

		$this->the_headers_page = $query->post;
		$this->run();
	}

	/**
	 * Let's get started.
	 *
	 * @return bool
	 */
	public function run() {
		add_action(
			'init',
			function () {
				load_plugin_textdomain( 'add-custom-header-images', false, basename( __DIR__ ) );
			}
		);

		if ( ( is_admin() && null === $this->the_headers_page )
		) {
			add_action( 'admin_notices', [ $this, 'headers_page_not_present' ] );

			return false;
		}
		add_action( 'after_setup_theme', [ $this, 'new_default_header_images' ], 99 );
		add_action( 'after_setup_theme', [ $this, 'setup_default_header_image' ], 100 );
		add_filter( 'wp_get_attachment_image_attributes', [ $this, 'get_image_attributes' ], 100, 3 );
	}

	/**
	 * Disable plugin if 'The Headers' page does not exist.
	 */
	public function headers_page_not_present() {
		echo '<div class="error notice is-dismissible"><p>';
		echo wp_kses_post( __( 'Add Custom Header Images requires a page titled <strong>The Headers</strong>.', 'add-custom-header-images' ) );
		echo '</p></div>';
	}

	/**
	 * Remove default header images.
	 */
	public function remove_default_header_images() {
		global $_wp_default_headers;
		if ( empty( $_wp_default_headers ) ) {
			return false;
		}

		$header_ids = [];
		foreach ( (array) array_keys( $_wp_default_headers ) as $key ) {
			if ( ! is_int( $key ) ) {
				$header_ids[] = $key;
			}
		}

		unregister_default_headers( $header_ids );
	}

	/**
	 * Add new default header images.
	 */
	public function new_default_header_images() {
		if ( ! $this->the_headers_page instanceof \WP_Post ) {
			return false;
		}

		$this->remove_default_header_images();
		$header_images = $this->get_images_from_post( $this->the_headers_page );

		register_default_headers( $header_images );
	}

	/**
	 * Get images from attachments and blocks.
	 *
	 * @param  \WP_Post $post Post object.
	 * @return array    $header_images
	 */
	private function get_images_from_post( \WP_Post $post ) {
		$images_query = new \WP_Query(
			[
				'post_parent'    => $post->ID,
				'post_status'    => 'inherit',
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'order'          => 'ASC',
				'orderby'        => 'menu_order ID',
			]
		);

		// Get images from blocks.
		$parsed_blocks = \parse_blocks( $post->post_content );
		$parsed_images = array_filter(
			$parsed_blocks,
			function ( $block ) {
				return 'core/image' === $block['blockName'];
			}
		);

		$blocks = [];
		foreach ( $parsed_images as $block ) {
			$id       = $block['attrs']['id'];
			$blocks[] = (object) [
				'ID'         => $id,
				'post_title' => get_the_title( $id ),
			];
		}

		$images = array_merge( $images_query->posts, $blocks );

		// Make default header image arrays.
		$header_images = [];
		$image_ids     = [];
		foreach ( $images as $image ) {
			wp_get_attachment_image( $image->ID, 'medium' );
			$header_images[] = [
				'url'           => wp_get_attachment_url( $image->ID ),
				'thumbnail_url' => $this->image_attr['src'],
				'description'   => $image->post_title,
				'attachment_id' => $image->ID,
				'alt_text'      => $this->image_attr['alt'],
			];
			$image_ids[]     = $image->ID;
		}

		$header_images       = $this->filter_headers( $header_images, $image_ids );
		$this->header_images = $header_images;

		return $header_images;
	}

	/**
	 * Remove duplicate $headers.
	 *
	 * @param  array $images    Array of image data.
	 * @param  array $image_ids Array of image IDs.
	 * @return array $images
	 */
	private function filter_headers( $images, $image_ids ) {
		$image_ids = array_flip( array_unique( $image_ids ) );
		$images    = array_filter(
			$images,
			function ( $id ) use ( &$image_ids ) {
				if ( array_key_exists( $id['attachment_id'], $image_ids ) ) {
					unset( $image_ids[ $id['attachment_id'] ] );

					return $id;
				}
			}
		);

		return (array) $images;
	}

	/**
	 * Add default header image if theme doesn't support it.
	 *
	 * @return void
	 */
	public function setup_default_header_image() {
		if ( ! current_theme_supports( 'custom-header' ) ) {
			add_theme_support( 'custom-header' );
			$this->header_image = get_header_image();

			if ( ! function_exists( 'wp_body_open' ) ) {
				/**
				 * Shim for wp_body_open, ensuring backward compatibility with versions of WordPress older than 5.2.
				 */
				function wp_body_open() {
					do_action( 'wp_body_open' );
				}
			}

			$alt_text = array_map(
				function( $img_arr ) {
					if ( $this->header_image === $img_arr['url'] ) {
						return $img_arr['alt_text'];
					}
				},
				(array) $this->header_images
			);

			$alt_text = array_filter( $alt_text );
			$alt_text = array_pop( $alt_text );

			add_action(
				'wp_body_open',
				function () use ( $alt_text ) {
					printf(
						/* translators: 1: image URL, 2: alt text */
						'<header><img src="%s" alt="%s"></header>',
						esc_attr( $this->header_image ),
						esc_attr( $alt_text )
					);
				}
			);
			add_action( 'wp_head', [ $this, 'header_image_style' ] );
		}
	}

	/**
	 * Get image attributes.
	 *
	 * @param string[]     $attr       Array of attribute values for the image markup, keyed by attribute name.
	 * @param WP_Post      $attachment Image attachment post.
	 * @param string|int[] $size       Requested image size. Can be any registered image size name, or
	 *                                 an array of width and height values in pixels (in that order).
	 *
	 * @return array
	 */
	public function get_image_attributes( $attr, $attachment, $size ) {
		$this->image_attr = $attr;

		return $attr;
	}

	/**
	 * Header image CSS styling.
	 *
	 * @return void
	 */
	public function header_image_style() {
		?>
			<style>
			header > img {
				display: block;
				margin-left: auto;
				margin-right: auto;
				width: 90%;
			}
			</style>
		<?php
	}
}

add_action(
	'plugins_loaded',
	function() {
		new Add_Custom_Header_Images();
	}
);
