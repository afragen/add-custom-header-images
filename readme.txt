=== Plugin Name ===
Contributors: afragen
Tags: headers, rotate headers, images
Requires at least: 3.6
Tested up to: 4.0
Stable tag: 1.0.3
Plugin URI: https://github.com/afragen/add-custom-header-images
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Remove default header images and load custom header images from 'The Headers' page. Allows for easy selection of random header images in your theme.

== Description ==

A plugin that should be able to remove default headers for a theme and add custom headers based upon the article written by <a href="http://juliobiason.net/2011/10/25/twentyeleven-with-easy-rotating-header-images/">Julio Biason</a> who was inspired by <a href="http://wpti.ps/?p=107">wpti.ps</a>.

Create a Page named **The Headers**. Then upload header images (media files) to the the page. The page may have a visibility of private.

Once the custom header images are loaded, just go to `Appearance > Header` or `Customize > Header Image` and select `Randomize suggested headers`.

The plugin will not activate unless there is a page titled, **The Headers**.

== Installation ==

1. Create a new page. It can be private. It must be titled `The Headers`. Add any images that you want to use as custom header images to this page. Header images should be cropped appropriately for the base theme.
1. Upload `add-custom-header-images` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to `Appearance > Header` or `Customize > Header Image` and select `Randomize` from the Default Images section.

== Changelog ==

= 1.0.3 =
* more graceful exit and return

= 1.0.2 =
* exit after deactivating plugin when not able to be activated

= 1.0.1 =
* Add .pot files
* Fix short description by removing Markdown

= 1.0.0 =
* Initial commit to WordPress repository
