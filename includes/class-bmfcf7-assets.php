<?php
/**
 * Script and style registration.
 *
 * Plyr (MIT, https://github.com/sampotts/plyr) is bundled locally; nothing is
 * loaded from a CDN.
 *
 * @package BMediaFieldsCF7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles front-end and admin assets.
 */
final class BMFCF7_Assets {

	/**
	 * Registers hooks.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_frontend' ), 5 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_model_viewer' ), 5 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_gallery' ), 5 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_pdf' ), 5 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_everywhere' ), 20 );
	}

	/**
	 * Registers (but does not enqueue) front-end assets.
	 */
	public static function register_frontend() {
		if ( wp_script_is( 'bmfcf7-frontend', 'registered' ) ) {
			return;
		}

		$general = BMFCF7_Settings::section( 'general' );
		$min     = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$build   = ( 'polyfilled' === $general['script_build'] ) ? 'plyr.polyfilled' : 'plyr';

		wp_register_style(
			'bmfcf7-plyr',
			BMFCF7_URL . 'assets/vendor/plyr/plyr.css',
			array(),
			BMFCF7_PLYR_VERSION
		);

		wp_register_style(
			'bmfcf7-frontend',
			BMFCF7_URL . 'assets/css/frontend.css',
			array( 'bmfcf7-plyr' ),
			BMFCF7_VERSION
		);

		wp_register_script(
			'bmfcf7-plyr',
			BMFCF7_URL . 'assets/vendor/plyr/' . $build . $min . '.js',
			array(),
			BMFCF7_PLYR_VERSION,
			true
		);

		wp_register_script(
			'bmfcf7-frontend',
			BMFCF7_URL . 'assets/js/frontend.js',
			array( 'bmfcf7-plyr' ),
			BMFCF7_VERSION,
			true
		);

		wp_localize_script( 'bmfcf7-frontend', 'bmfcf7Frontend', self::frontend_data() );
	}

	/**
	 * Data passed to the front-end script.
	 *
	 * @return array
	 */
	private static function frontend_data() {
		$defaults = array();

		foreach ( array( 'video', 'audio' ) as $media ) {
			$s = BMFCF7_Settings::section( $media );

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
			'iconUrl'    => BMFCF7_URL . 'assets/vendor/plyr/plyr.svg',
			'blankVideo' => BMFCF7_URL . 'assets/vendor/plyr/blank.mp4',
			'defaults'   => $defaults,
			'i18n'       => self::i18n(),
		);

		/**
		 * Filters the data handed to the front-end initialiser.
		 *
		 * @param array $data Front-end data.
		 */
		return apply_filters( 'bmfcf7_frontend_data', $data );
	}

	/**
	 * Translatable Plyr UI strings.
	 *
	 * @return array
	 */
	private static function i18n() {
		return array(
			'restart'         => __( 'Restart', 'b-media-fields-for-cf7' ),
			'rewind'          => __( 'Rewind {seektime}s', 'b-media-fields-for-cf7' ),
			'play'            => __( 'Play', 'b-media-fields-for-cf7' ),
			'pause'           => __( 'Pause', 'b-media-fields-for-cf7' ),
			'fastForward'     => __( 'Forward {seektime}s', 'b-media-fields-for-cf7' ),
			'seek'            => __( 'Seek', 'b-media-fields-for-cf7' ),
			'seekLabel'       => __( '{currentTime} of {duration}', 'b-media-fields-for-cf7' ),
			'played'          => __( 'Played', 'b-media-fields-for-cf7' ),
			'buffered'        => __( 'Buffered', 'b-media-fields-for-cf7' ),
			'currentTime'     => __( 'Current time', 'b-media-fields-for-cf7' ),
			'duration'        => __( 'Duration', 'b-media-fields-for-cf7' ),
			'volume'          => __( 'Volume', 'b-media-fields-for-cf7' ),
			'mute'            => __( 'Mute', 'b-media-fields-for-cf7' ),
			'unmute'          => __( 'Unmute', 'b-media-fields-for-cf7' ),
			'enableCaptions'  => __( 'Enable captions', 'b-media-fields-for-cf7' ),
			'disableCaptions' => __( 'Disable captions', 'b-media-fields-for-cf7' ),
			'download'        => __( 'Download', 'b-media-fields-for-cf7' ),
			'enterFullscreen' => __( 'Enter fullscreen', 'b-media-fields-for-cf7' ),
			'exitFullscreen'  => __( 'Exit fullscreen', 'b-media-fields-for-cf7' ),
			'frameTitle'      => __( 'Player for {title}', 'b-media-fields-for-cf7' ),
			'captions'        => __( 'Captions', 'b-media-fields-for-cf7' ),
			'settings'        => __( 'Settings', 'b-media-fields-for-cf7' ),
			'pip'             => __( 'PIP', 'b-media-fields-for-cf7' ),
			'menuBack'        => __( 'Go back to previous menu', 'b-media-fields-for-cf7' ),
			'speed'           => __( 'Speed', 'b-media-fields-for-cf7' ),
			'normal'          => __( 'Normal', 'b-media-fields-for-cf7' ),
			'quality'         => __( 'Quality', 'b-media-fields-for-cf7' ),
			'loop'            => __( 'Loop', 'b-media-fields-for-cf7' ),
			'start'           => __( 'Start', 'b-media-fields-for-cf7' ),
			'end'             => __( 'End', 'b-media-fields-for-cf7' ),
			'all'             => __( 'All', 'b-media-fields-for-cf7' ),
			'reset'           => __( 'Reset', 'b-media-fields-for-cf7' ),
			'disabled'        => __( 'Disabled', 'b-media-fields-for-cf7' ),
			'enabled'         => __( 'Enabled', 'b-media-fields-for-cf7' ),
			'advertisement'   => __( 'Ad', 'b-media-fields-for-cf7' ),
		);
	}

	/**
	 * Enqueues the player assets (called from the form-tag handler).
	 */
	public static function enqueue_frontend() {
		self::register_frontend();

		wp_enqueue_style( 'bmfcf7-frontend' );
		wp_enqueue_script( 'bmfcf7-frontend' );
	}

	/**
	 * Registers the <model-viewer> assets.
	 */
	public static function register_model_viewer() {
		if ( wp_script_is( 'bmfcf7-model-viewer', 'registered' ) ) {
			return;
		}

		wp_register_script(
			'bmfcf7-model-viewer',
			BMFCF7_URL . 'assets/vendor/model-viewer/model-viewer-umd.min.js',
			array(),
			BMFCF7_MODEL_VIEWER_VERSION,
			true
		);

		wp_register_style(
			'bmfcf7-model',
			BMFCF7_URL . 'assets/css/model.css',
			array(),
			BMFCF7_VERSION
		);
	}

	/**
	 * Enqueues the <model-viewer> assets (called from the 3D form-tag handler).
	 */
	public static function enqueue_model_viewer() {
		self::register_model_viewer();

		wp_enqueue_script( 'bmfcf7-model-viewer' );
		wp_enqueue_style( 'bmfcf7-model' );
	}


	/**
	 * Registers the gallery assets.
	 */
	public static function register_gallery() {
		if ( wp_script_is( 'bmfcf7-gallery', 'registered' ) ) {
			return;
		}

		wp_register_style(
			'bmfcf7-gallery',
			BMFCF7_URL . 'assets/css/gallery.css',
			array(),
			BMFCF7_VERSION
		);

		wp_register_script(
			'bmfcf7-gallery',
			BMFCF7_URL . 'assets/js/gallery.js',
			array(),
			BMFCF7_VERSION,
			true
		);

		wp_localize_script(
			'bmfcf7-gallery',
			'bmfcf7Gallery',
			array(
				'i18n' => array(
					'close' => __( 'Close', 'b-media-fields-for-cf7' ),
					'prev'  => __( 'Previous image', 'b-media-fields-for-cf7' ),
					'next'  => __( 'Next image', 'b-media-fields-for-cf7' ),
				),
			)
		);
	}

	/**
	 * Enqueues the gallery assets (called from the [gallery] handler).
	 */
	public static function enqueue_gallery() {
		self::register_gallery();

		wp_enqueue_style( 'bmfcf7-gallery' );
		wp_enqueue_script( 'bmfcf7-gallery' );
	}

	/**
	 * Registers the PDF viewer assets.
	 */
	public static function register_pdf() {
		if ( wp_script_is( 'bmfcf7-pdf', 'registered' ) ) {
			return;
		}

		wp_register_style(
			'bmfcf7-pdf',
			BMFCF7_URL . 'assets/css/pdf-flipbook.css',
			array(),
			BMFCF7_VERSION
		);

		wp_register_script(
			'bmfcf7-pdfjs',
			BMFCF7_URL . 'assets/vendor/pdfjs/pdf.min.js',
			array(),
			BMFCF7_PDFJS_VERSION,
			true
		);

		wp_register_script(
			'bmfcf7-page-flip',
			BMFCF7_URL . 'assets/vendor/page-flip/page-flip.browser.js',
			array(),
			BMFCF7_PAGEFLIP_VERSION,
			true
		);

		wp_register_script(
			'bmfcf7-pdf',
			BMFCF7_URL . 'assets/js/pdf-flipbook.js',
			array( 'bmfcf7-pdfjs', 'bmfcf7-page-flip' ),
			BMFCF7_VERSION,
			true
		);

		wp_localize_script(
			'bmfcf7-pdf',
			'bmfcf7Pdf',
			array(
				'worker' => BMFCF7_URL . 'assets/vendor/pdfjs/pdf.worker.min.js',
				'i18n'   => array(
					'error'   => __( 'The document could not be loaded.', 'b-media-fields-for-cf7' ),
					'loading' => __( 'Loading document…', 'b-media-fields-for-cf7' ),
				),
			)
		);
	}

	/**
	 * Enqueues the PDF viewer assets (called from the [pdf_flipbook] handler).
	 */
	public static function enqueue_pdf() {
		self::register_pdf();

		wp_enqueue_style( 'bmfcf7-pdf' );
		wp_enqueue_script( 'bmfcf7-pdf' );
	}

	/**
	 * Enqueues assets globally when the setting is enabled.
	 */
	public static function maybe_enqueue_everywhere() {
		if ( BMFCF7_Settings::get( 'general', 'load_everywhere' ) ) {
			if ( BMFCF7_Settings::is_enabled( 'video' ) || BMFCF7_Settings::is_enabled( 'audio' ) ) {
				self::enqueue_frontend();
			}
			if ( BMFCF7_Settings::is_enabled( '3d_models' ) ) {
				self::enqueue_model_viewer();
			}
			if ( BMFCF7_Settings::is_enabled( 'gallery' ) ) {
				self::enqueue_gallery();
			}
			if ( BMFCF7_Settings::is_enabled( 'pdf_flipbook' ) ) {
				self::enqueue_pdf();
			}
		}
	}
}
