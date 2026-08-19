<?php
/**
 * Plugin Name:       Essential Fields for CF7
 * Plugin URI:        https://wordpress.org/plugins/essential-fields-for-cf7/
 * Description:       Additional fields for Contact Form 7 – embed audio, video (self-hosted, YouTube, Vimeo) and interactive 3D models (glTF/GLB with AR) inside your forms, with every player option available.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Requires Plugins:  contact-form-7
 * Author:            bPlugins
 * Author URI:        https://bplugins.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       essential-fields-for-cf7
 * Domain Path:       /languages
 *
 * @package EssentialFieldsCF7
 */

defined( 'ABSPATH' ) || exit;

define( 'EFCF7_VERSION', '1.0.0' );
define( 'EFCF7_PLYR_VERSION', '3.8.4' );
define( 'EFCF7_MODEL_VIEWER_VERSION', '4.3.1' );
define( 'EFCF7_MIN_CF7_VERSION', '6.0' );
define( 'EFCF7_FILE', __FILE__ );
define( 'EFCF7_PATH', plugin_dir_path( __FILE__ ) );
define( 'EFCF7_URL', plugin_dir_url( __FILE__ ) );
define( 'EFCF7_BASENAME', plugin_basename( __FILE__ ) );

require_once EFCF7_PATH . 'includes/class-efcf7-options.php';
require_once EFCF7_PATH . 'includes/class-efcf7-settings.php';
require_once EFCF7_PATH . 'includes/class-efcf7-assets.php';
require_once EFCF7_PATH . 'includes/class-efcf7-form-tag.php';
require_once EFCF7_PATH . 'includes/class-efcf7-tag-generator.php';
require_once EFCF7_PATH . 'includes/class-efcf7-model-options.php';
require_once EFCF7_PATH . 'includes/class-efcf7-model-form-tag.php';
require_once EFCF7_PATH . 'includes/class-efcf7-model-tag-generator.php';
require_once EFCF7_PATH . 'includes/class-efcf7-plugin.php';

/**
 * Returns the main plugin instance.
 *
 * @return EFCF7_Plugin
 */
function efcf7() {
	return EFCF7_Plugin::instance();
}

efcf7();
