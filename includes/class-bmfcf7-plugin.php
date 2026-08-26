<?php
/**
 * Main plugin loader.
 *
 * @package BMediaFieldsCF7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Bootstraps the plugin once Contact Form 7 is confirmed to be available.
 */
final class BMFCF7_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var BMFCF7_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Returns the singleton instance.
	 *
	 * @return BMFCF7_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Wires up hooks.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ), 20 );
		add_filter( 'plugin_action_links_' . BMFCF7_BASENAME, array( $this, 'action_links' ) );
	}

	/**
	 * Whether a compatible Contact Form 7 is active.
	 *
	 * @return bool
	 */
	public static function cf7_is_compatible() {
		return defined( 'WPCF7_VERSION' )
			&& version_compare( WPCF7_VERSION, BMFCF7_MIN_CF7_VERSION, '>=' )
			&& function_exists( 'wpcf7_add_form_tag' );
	}

	/**
	 * Initialises components or shows a dependency notice.
	 */
	public function init() {
		if ( ! self::cf7_is_compatible() ) {
			add_action( 'admin_notices', array( $this, 'dependency_notice' ) );
			return;
		}

		BMFCF7_Settings::init();
		BMFCF7_Assets::init();
		BMFCF7_Form_Tag::init();
		BMFCF7_Model_Form_Tag::init();
		BMFCF7_Gallery_Form_Tag::init();
		BMFCF7_Pdf_Form_Tag::init();

		if ( is_admin() ) {
			BMFCF7_Tag_Generator::init();
			BMFCF7_Model_Tag_Generator::init();
			BMFCF7_Gallery_Tag_Generator::init();
			BMFCF7_Pdf_Tag_Generator::init();
		}
	}

	/**
	 * Prints a notice when Contact Form 7 is missing or outdated.
	 */
	public function dependency_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			sprintf(
				/* translators: 1: plugin name, 2: minimum CF7 version */
				esc_html__( '%1$s requires Contact Form 7 version %2$s or later to be installed and active.', 'b-media-fields-for-cf7' ),
				'<strong>' . esc_html__( 'Media Fields for Contact Form 7', 'b-media-fields-for-cf7' ) . '</strong>',
				esc_html( BMFCF7_MIN_CF7_VERSION )
			)
		);
	}

	/**
	 * Adds a Settings link on the Plugins screen.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public function action_links( $links ) {
		if ( self::cf7_is_compatible() ) {
			$settings = sprintf(
				'<a href="%s">%s</a>',
				esc_url( admin_url( 'admin.php?page=' . BMFCF7_Settings::PAGE_SLUG ) ),
				esc_html__( 'Settings', 'b-media-fields-for-cf7' )
			);
			array_unshift( $links, $settings );
		}

		return $links;
	}
}
