<?php
/**
 * Script and style registration.
 *
 * Plyr (MIT, https://github.com/sampotts/plyr) is bundled locally; nothing is
 * loaded from a CDN.
 *
 * @package EssentialFieldsCF7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles front-end and admin assets.
 */
final class EFCF7_Assets {

	/**
	 * Registers hooks.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_frontend' ), 5 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_model_viewer' ), 5 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_everywhere' ), 20 );
	}

	/**
	 * Registers (but does not enqueue) front-end assets.
	 */
	public static function register_frontend() {
		if ( wp_script_is( 'efcf7-frontend', 'registered' ) ) {
			return;
		}

		$general = EFCF7_Settings::section( 'general' );
		$min     = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$build   = ( 'polyfilled' === $general['script_build'] ) ? 'plyr.polyfilled' : 'plyr';

		wp_register_style(
			'efcf7-plyr',
			EFCF7_URL . 'assets/vendor/plyr/plyr.css',
			array(),
			EFCF7_PLYR_VERSION
		);

		wp_register_style(
			'efcf7-frontend',
			EFCF7_URL . 'assets/css/frontend.css',
			array( 'efcf7-plyr' ),
			EFCF7_VERSION
		);

		wp_register_script(
			'efcf7-plyr',
			EFCF7_URL . 'assets/vendor/plyr/' . $build . $min . '.js',
			array(),
			EFCF7_PLYR_VERSION,
			true
		);

		wp_register_script(
			'efcf7-frontend',
			EFCF7_URL . 'assets/js/frontend.js',
			array( 'efcf7-plyr' ),
			EFCF7_VERSION,
			true
		);

		wp_localize_script( 'efcf7-frontend', 'efcf7Frontend', self::frontend_data() );
	}

	/**
	 * Data passed to the front-end script.
	 *
	 * @return array
	 */
	private static function frontend_data() {
		$defaults = array();

		foreach ( array( 'video', 'audio' ) as $media ) {
			$s = EFCF7_Settings::section( $media );

			$defaults[ $media ] = array(
				'color'        => $s['color'],
				'controls'     => $s['controls'],
				'settings'     => $s['settings_menu'],
				'hideControls' => ! empty( $s['hide_controls'] ),
				'storage'      => array(
					'enabled' => ! empty( $s['remember_prefs'] ),
				),
			);
		}

		$data = array(
			'iconUrl'    => EFCF7_URL . 'assets/vendor/plyr/plyr.svg',
			'blankVideo' => EFCF7_URL . 'assets/vendor/plyr/blank.mp4',
			'defaults'   => $defaults,
			'i18n'       => self::i18n(),
		);

		/**
		 * Filters the data handed to the front-end initialiser.
		 *
		 * @param array $data Front-end data.
		 */
		return apply_filters( 'efcf7_frontend_data', $data );
	}

	/**
	 * Translatable Plyr UI strings.
	 *
	 * @return array
	 */
	private static function i18n() {
		return array(
			'restart'         => __( 'Restart', 'essential-fields-for-cf7' ),
			'rewind'          => __( 'Rewind {seektime}s', 'essential-fields-for-cf7' ),
			'play'            => __( 'Play', 'essential-fields-for-cf7' ),
			'pause'           => __( 'Pause', 'essential-fields-for-cf7' ),
			'fastForward'     => __( 'Forward {seektime}s', 'essential-fields-for-cf7' ),
			'seek'            => __( 'Seek', 'essential-fields-for-cf7' ),
			'seekLabel'       => __( '{currentTime} of {duration}', 'essential-fields-for-cf7' ),
			'played'          => __( 'Played', 'essential-fields-for-cf7' ),
			'buffered'        => __( 'Buffered', 'essential-fields-for-cf7' ),
			'currentTime'     => __( 'Current time', 'essential-fields-for-cf7' ),
			'duration'        => __( 'Duration', 'essential-fields-for-cf7' ),
			'volume'          => __( 'Volume', 'essential-fields-for-cf7' ),
			'mute'            => __( 'Mute', 'essential-fields-for-cf7' ),
			'unmute'          => __( 'Unmute', 'essential-fields-for-cf7' ),
			'enableCaptions'  => __( 'Enable captions', 'essential-fields-for-cf7' ),
			'disableCaptions' => __( 'Disable captions', 'essential-fields-for-cf7' ),
			'download'        => __( 'Download', 'essential-fields-for-cf7' ),
			'enterFullscreen' => __( 'Enter fullscreen', 'essential-fields-for-cf7' ),
			'exitFullscreen'  => __( 'Exit fullscreen', 'essential-fields-for-cf7' ),
			'frameTitle'      => __( 'Player for {title}', 'essential-fields-for-cf7' ),
			'captions'        => __( 'Captions', 'essential-fields-for-cf7' ),
			'settings'        => __( 'Settings', 'essential-fields-for-cf7' ),
			'pip'             => __( 'PIP', 'essential-fields-for-cf7' ),
			'menuBack'        => __( 'Go back to previous menu', 'essential-fields-for-cf7' ),
			'speed'           => __( 'Speed', 'essential-fields-for-cf7' ),
			'normal'          => __( 'Normal', 'essential-fields-for-cf7' ),
			'quality'         => __( 'Quality', 'essential-fields-for-cf7' ),
			'loop'            => __( 'Loop', 'essential-fields-for-cf7' ),
			'start'           => __( 'Start', 'essential-fields-for-cf7' ),
			'end'             => __( 'End', 'essential-fields-for-cf7' ),
			'all'             => __( 'All', 'essential-fields-for-cf7' ),
			'reset'           => __( 'Reset', 'essential-fields-for-cf7' ),
			'disabled'        => __( 'Disabled', 'essential-fields-for-cf7' ),
			'enabled'         => __( 'Enabled', 'essential-fields-for-cf7' ),
			'advertisement'   => __( 'Ad', 'essential-fields-for-cf7' ),
		);
	}

	/**
	 * Enqueues the player assets (called from the form-tag handler).
	 */
	public static function enqueue_frontend() {
		self::register_frontend();

		wp_enqueue_style( 'efcf7-frontend' );
		wp_enqueue_script( 'efcf7-frontend' );
	}

	/**
	 * Registers the <model-viewer> assets.
	 */
	public static function register_model_viewer() {
		if ( wp_script_is( 'efcf7-model-viewer', 'registered' ) ) {
			return;
		}

		wp_register_script(
			'efcf7-model-viewer',
			EFCF7_URL . 'assets/vendor/model-viewer/model-viewer-umd.min.js',
			array(),
			EFCF7_MODEL_VIEWER_VERSION,
			true
		);

		wp_register_style(
			'efcf7-model',
			EFCF7_URL . 'assets/css/model.css',
			array(),
			EFCF7_VERSION
		);
	}

	/**
	 * Enqueues the <model-viewer> assets (called from the 3D form-tag handler).
	 */
	public static function enqueue_model_viewer() {
		self::register_model_viewer();

		wp_enqueue_script( 'efcf7-model-viewer' );
		wp_enqueue_style( 'efcf7-model' );
	}

	/**
	 * Enqueues assets globally when the setting is enabled.
	 */
	public static function maybe_enqueue_everywhere() {
		if ( EFCF7_Settings::get( 'general', 'load_everywhere' ) ) {
			if ( EFCF7_Settings::is_enabled( 'video' ) || EFCF7_Settings::is_enabled( 'audio' ) ) {
				self::enqueue_frontend();
			}
			if ( EFCF7_Settings::is_enabled( '3d_models' ) ) {
				self::enqueue_model_viewer();
			}
		}
	}
}
