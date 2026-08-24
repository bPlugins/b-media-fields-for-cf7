<?php
/**
 * Uninstall handler.
 *
 * Removes the plugin's option when the user opted in on the settings page.
 *
 * @package BMediaFieldsCF7
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$bmfcf7_settings = get_option( 'bmfcf7_settings', array() );

if ( is_array( $bmfcf7_settings ) && ! empty( $bmfcf7_settings['general']['delete_on_uninstall'] ) ) {
	delete_option( 'bmfcf7_settings' );

	if ( is_multisite() ) {
		delete_site_option( 'bmfcf7_settings' );
	}
}
