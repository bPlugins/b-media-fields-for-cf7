<?php
/**
 * Plugin Name:       Media Fields for Contact Form 7
 * Plugin URI:        https://wordpress.org/plugins/b-media-fields-for-cf7/
 * GitHub Plugin URI: https://github.com/bPlugins/b-media-fields-for-cf7
 * Description:       Additional fields for Contact Form 7 – embed audio, video (self-hosted, YouTube, Vimeo), interactive 3D models, image galleries and PDF flipbooks inside your forms, with every player option available.
 * Version:           1.1.3
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Requires Plugins:  contact-form-7
 * Author:            bPlugins
 * Author URI:        https://bplugins.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       b-media-fields-for-cf7
 * Domain Path:       /languages
 *
 * @package BMediaFieldsCF7
 */

defined( 'ABSPATH' ) || exit;

define( 'BMFCF7_VERSION', '1.1.3' );
define( 'BMFCF7_PLYR_VERSION', '3.8.4' );
define( 'BMFCF7_MODEL_VIEWER_VERSION', '4.3.1' );
define( 'BMFCF7_PDFJS_VERSION', '3.11.174' );
define( 'BMFCF7_PAGEFLIP_VERSION', '2.0.7' );
define( 'BMFCF7_INTRO_VIDEO', 'sbw-mq7Yugs' );
define( 'BMFCF7_TUTORIAL_VIDEO', 'Xq6rbmm0YbQ' );
define( 'BMFCF7_MIN_CF7_VERSION', '6.0' );
define( 'BMFCF7_FILE', __FILE__ );
define( 'BMFCF7_PATH', plugin_dir_path( __FILE__ ) );
define( 'BMFCF7_URL', plugin_dir_url( __FILE__ ) );
define( 'BMFCF7_BASENAME', plugin_basename( __FILE__ ) );

// Freemius first: opt-in usage tracking and the uninstall hook. See the file header.
require_once BMFCF7_PATH . 'includes/freemius.php';

require_once BMFCF7_PATH . 'includes/class-bmfcf7-options.php';
require_once BMFCF7_PATH . 'includes/class-bmfcf7-settings.php';
require_once BMFCF7_PATH . 'includes/class-bmfcf7-assets.php';
require_once BMFCF7_PATH . 'includes/class-bmfcf7-form-tag.php';
require_once BMFCF7_PATH . 'includes/class-bmfcf7-tag-generator.php';
require_once BMFCF7_PATH . 'includes/class-bmfcf7-model-options.php';
require_once BMFCF7_PATH . 'includes/class-bmfcf7-model-form-tag.php';
require_once BMFCF7_PATH . 'includes/class-bmfcf7-model-tag-generator.php';
require_once BMFCF7_PATH . 'includes/class-bmfcf7-gallery-options.php';
require_once BMFCF7_PATH . 'includes/class-bmfcf7-gallery-form-tag.php';
require_once BMFCF7_PATH . 'includes/class-bmfcf7-gallery-tag-generator.php';
require_once BMFCF7_PATH . 'includes/class-bmfcf7-pdf-options.php';
require_once BMFCF7_PATH . 'includes/class-bmfcf7-pdf-form-tag.php';
require_once BMFCF7_PATH . 'includes/class-bmfcf7-pdf-tag-generator.php';
require_once BMFCF7_PATH . 'includes/class-bmfcf7-plugin.php';

/**
 * Returns the main plugin instance.
 *
 * @return BMFCF7_Plugin
 */
function bmfcf7() {
	return BMFCF7_Plugin::instance();
}

register_activation_hook( __FILE__, array( 'BMFCF7_Plugin', 'on_activate' ) );

bmfcf7();
