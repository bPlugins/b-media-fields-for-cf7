<?php
/**
 * Uninstall handler.
 *
 * Removes the plugin's option when the user opted in on the settings page.
 *
 * @package EssentialFieldsCF7
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$efcf7_settings = get_option( 'efcf7_settings', array() );

if ( is_array( $efcf7_settings ) && ! empty( $efcf7_settings['general']['delete_on_uninstall'] ) ) {
	delete_option( 'efcf7_settings' );

	if ( is_multisite() ) {
		delete_site_option( 'efcf7_settings' );
	}
}
