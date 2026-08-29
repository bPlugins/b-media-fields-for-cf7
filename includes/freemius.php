<?php
/**
 * Freemius integration.
 *
 * Used only for opt-in usage tracking: which sites run the plugin, on what
 * WordPress and PHP versions, and why people deactivate. Nothing is sent
 * unless the site owner agrees on the opt-in screen shown once after
 * activation, and they can change their mind later from the plugin's row on
 * the Plugins screen. There are no paid plans, no licensing and no add-ons.
 *
 * @package BMediaFieldsCF7
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'bmfcf7_fs' ) ) {
	/**
	 * Returns the Freemius SDK instance for this plugin.
	 *
	 * @return Freemius
	 */
	function bmfcf7_fs() {
		global $bmfcf7_fs;

		if ( ! isset( $bmfcf7_fs ) ) {
			// Include Freemius SDK.
			require_once BMFCF7_PATH . 'vendor/freemius/start.php';

			$bmfcf7_fs = fs_dynamic_init(
				array(
					'id'               => '38073',
					'slug'             => 'b-media-fields-for-cf7',
					'type'             => 'plugin',
					'public_key'       => 'pk_76cda66a37b55a041fb4b7f1f683b',
					'is_premium'       => false,
					'has_addons'       => false,
					'has_paid_plans'   => false,
					'is_org_compliant' => true,
					'menu'             => array(
						// The opt-in screen replaces our settings page until the
						// user decides, then 'first-path' brings them back to it.
						'slug'        => 'bmfcf7-settings',
						'first-path'  => 'admin.php?page=bmfcf7-settings',
						'parent'      => array( 'slug' => 'wpcf7' ),
						'account'     => false,
						'support'     => false,
						'contact'     => false,
						'affiliation' => false,
						'pricing'     => false,
					),
				)
			);
		}

		return $bmfcf7_fs;
	}

	// Init Freemius.
	bmfcf7_fs();
	// Signal that SDK was initiated.
	do_action( 'bmfcf7_fs_loaded' );

	/**
	 * Opt-in copy shown once after activation.
	 *
	 * Freemius' default text is generic. This says plainly what is collected
	 * and why, which is also what the wordpress.org guidelines expect.
	 *
	 * @param string $message         Default message (unused).
	 * @param string $user_first_name Current user's first name.
	 * @param string $product_title   Plugin name.
	 * @return string
	 */
	function bmfcf7_fs_connect_message( $message, $user_first_name, $product_title ) {
		return sprintf(
			/* translators: 1: user first name, 2: plugin name. */
			esc_html__( 'Hi %1$s, thank you for installing %2$s. Help us build the right fields next by sharing a little non-sensitive data about your site: the WordPress and PHP versions, active plugins, and which of our fields you use. Nothing is sent unless you allow it, and you can opt out at any time from the Plugins screen.', 'b-media-fields-for-cf7' ),
			'<b>' . esc_html( $user_first_name ) . '</b>',
			'<b>' . esc_html( $product_title ) . '</b>'
		);
	}
	bmfcf7_fs()->add_filter( 'connect_message', 'bmfcf7_fs_connect_message', 10, 3 );
	bmfcf7_fs()->add_filter( 'connect_message_on_update', 'bmfcf7_fs_connect_message', 10, 3 );

	/**
	 * Cleans up on uninstall.
	 *
	 * Replaces uninstall.php: Freemius registers the uninstall hook itself so
	 * it can record the uninstall, then fires this. WordPress ignores the hook
	 * whenever an uninstall.php exists, so the two cannot coexist.
	 */
	function bmfcf7_fs_uninstall_cleanup() {
		$settings = get_option( 'bmfcf7_settings', array() );

		if ( is_array( $settings ) && ! empty( $settings['general']['delete_on_uninstall'] ) ) {
			delete_option( 'bmfcf7_settings' );

			if ( is_multisite() ) {
				delete_site_option( 'bmfcf7_settings' );
			}
		}
	}
	bmfcf7_fs()->add_action( 'after_uninstall', 'bmfcf7_fs_uninstall_cleanup' );
}
