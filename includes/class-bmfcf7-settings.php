<?php
/**
 * Global settings page (Contact → Media Fields).
 *
 * The page is schema driven: every field type (video, audio, 3d_models, …)
 * contributes a section with its own options. Adding a new field type means
 * adding one entry to {@see BMFCF7_Settings::schema()} – rendering,
 * sanitisation, defaults and navigation are handled generically.
 *
 * Stored as one option: bmfcf7_settings[ section ][ key ].
 *
 * @package BMediaFieldsCF7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings page + typed accessors.
 */
final class BMFCF7_Settings {

	const OPTION    = 'bmfcf7_settings';
	const PAGE_SLUG = 'bmfcf7-settings';
	const GROUP     = 'bmfcf7_settings_group';

	/**
	 * Registers hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ), 20 );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	// Schema.

	/**
	 * Settings schema.
	 *
	 * Section keys: label, nav_label, icon (dashicon), desc, status
	 * (active|planned), tag (example form-tag), toggleable (bool – adds an
	 * "enabled" switch), groups (array of group => array( label, desc,
	 * fields )). Field types: toggle, color, chips, radio, number, text.
	 *
	 * @return array
	 */
	public static function schema() {
		static $schema = null;

		if ( null !== $schema ) {
			return $schema;
		}

		$player_groups = static function ( $media ) {
			$is_audio = ( 'audio' === $media );

			return array(
				'appearance' => array(
					'label'  => __( 'Appearance', 'b-media-fields-for-cf7' ),
					'desc'   => __( 'Defaults for every player. Individual form-tags can override each of these.', 'b-media-fields-for-cf7' ),
					'fields' => array(
						'color'         => array(
							'type'    => 'color',
							'label'   => __( 'Accent colour', 'b-media-fields-for-cf7' ),
							'desc'    => __( 'Play button, progress bar and menu highlights. Leave empty for Plyr’s default blue.', 'b-media-fields-for-cf7' ),
							'default' => '',
						),
						'controls'      => array(
							'type'    => 'chips',
							'label'   => __( 'Default controls', 'b-media-fields-for-cf7' ),
							'desc'    => __( 'Controls shown on the player bar, in Plyr’s order.', 'b-media-fields-for-cf7' ),
							'choices' => $is_audio ? self::audio_controls() : BMFCF7_Options::controls(),
							'default' => $is_audio
								? array( 'play', 'progress', 'current-time', 'mute', 'volume', 'settings' )
								: BMFCF7_Options::default_controls(),
						),
						'settings_menu' => array(
							'type'    => 'chips',
							'label'   => __( 'Settings menu', 'b-media-fields-for-cf7' ),
							'desc'    => __( 'Items available in the gear menu.', 'b-media-fields-for-cf7' ),
							'choices' => $is_audio
								? array_intersect_key( BMFCF7_Options::settings_menu(), array_flip( array( 'speed', 'loop' ) ) )
								: BMFCF7_Options::settings_menu(),
							'default' => $is_audio ? array( 'speed' ) : BMFCF7_Options::default_settings_menu(),
						),
					),
				),
				'behaviour'  => array(
					'label'  => __( 'Behaviour', 'b-media-fields-for-cf7' ),
					'desc'   => '',
					'fields' => array(
						'remember_prefs' => array(
							'type'    => 'toggle',
							'label'   => __( 'Remember visitor preferences', 'b-media-fields-for-cf7' ),
							'desc'    => __( 'Store volume, speed and caption choices in the visitor’s browser (localStorage).', 'b-media-fields-for-cf7' ),
							'default' => 1,
						),
						'hide_controls'  => array(
							'type'    => 'toggle',
							'label'   => __( 'Auto-hide controls', 'b-media-fields-for-cf7' ),
							'desc'    => __( 'Hide the control bar after a couple of seconds of inactivity during playback.', 'b-media-fields-for-cf7' ),
							'default' => 1,
						),
					),
				),
			);
		};

		$schema = array(
			'video'        => array(
				'label'      => __( 'Video', 'b-media-fields-for-cf7' ),
				'icon'       => 'dashicons-video-alt3',
				'desc'       => __( 'Self-hosted MP4/WebM, YouTube and Vimeo players inside forms.', 'b-media-fields-for-cf7' ),
				'status'     => 'active',
				'tag'        => '[video my-video "https://example.com/clip.mp4"]',
				'toggleable' => true,
				'groups'     => $player_groups( 'video' ),
			),
			'audio'        => array(
				'label'      => __( 'Audio', 'b-media-fields-for-cf7' ),
				'icon'       => 'dashicons-format-audio',
				'desc'       => __( 'MP3, M4A, OGG, WAV and FLAC players inside forms.', 'b-media-fields-for-cf7' ),
				'status'     => 'active',
				'tag'        => '[audio my-audio "https://example.com/track.mp3"]',
				'toggleable' => true,
				'groups'     => $player_groups( 'audio' ),
			),
			'3d_models'    => array(
				'label'      => __( '3D Models', 'b-media-fields-for-cf7' ),
				'icon'       => 'dashicons-admin-site-alt3',
				'desc'       => __( 'Interactive glTF / GLB model viewer (Google <model-viewer>) with orbit, zoom, hotspots and AR.', 'b-media-fields-for-cf7' ),
				'status'     => 'active',
				'tag'        => '[3d_models my-model "https://example.com/model.glb"]',
				'toggleable' => true,
				'groups'     => array(
					'appearance'  => array(
						'label'  => __( 'Appearance', 'b-media-fields-for-cf7' ),
						'desc'   => __( 'Defaults for every 3D viewer. Individual form-tags can override each of these.', 'b-media-fields-for-cf7' ),
						'fields' => array(
							'height'         => array(
								'type'    => 'number',
								'label'   => __( 'Viewer height (px)', 'b-media-fields-for-cf7' ),
								'desc'    => __( 'The viewer always spans the full form width.', 'b-media-fields-for-cf7' ),
								'default' => 400,
								'min'     => 100,
								'max'     => 2000,
							),
							'background'     => array(
								'type'    => 'color',
								'label'   => __( 'Background colour', 'b-media-fields-for-cf7' ),
								'desc'    => __( 'Behind the model; ignored when a skybox image is used.', 'b-media-fields-for-cf7' ),
								'default' => '',
							),
							'poster_color'   => array(
								'type'    => 'color',
								'label'   => __( 'Poster colour', 'b-media-fields-for-cf7' ),
								'desc'    => __( 'Shown while the model is loading.', 'b-media-fields-for-cf7' ),
								'default' => '',
							),
							'progress_color' => array(
								'type'    => 'color',
								'label'   => __( 'Progress bar colour', 'b-media-fields-for-cf7' ),
								'default' => '',
							),
						),
					),
					'interaction' => array(
						'label'  => __( 'Interaction', 'b-media-fields-for-cf7' ),
						'desc'   => '',
						'fields' => array(
							'camera_controls'    => array(
								'type'    => 'toggle',
								'label'   => __( 'Camera controls', 'b-media-fields-for-cf7' ),
								'desc'    => __( 'Let visitors orbit, zoom and pan with mouse / touch.', 'b-media-fields-for-cf7' ),
								'default' => 1,
							),
							'auto_rotate'        => array(
								'type'    => 'toggle',
								'label'   => __( 'Auto-rotate', 'b-media-fields-for-cf7' ),
								'desc'    => __( 'Slowly spin the model until the visitor interacts.', 'b-media-fields-for-cf7' ),
								'default' => 0,
							),
							'interaction_prompt' => array(
								'type'    => 'toggle',
								'label'   => __( 'Interaction prompt', 'b-media-fields-for-cf7' ),
								'desc'    => __( 'Show the animated "drag to rotate" hint.', 'b-media-fields-for-cf7' ),
								'default' => 1,
							),
							'ar'                 => array(
								'type'    => 'toggle',
								'label'   => __( 'Augmented reality', 'b-media-fields-for-cf7' ),
								'desc'    => __( 'Show the "View in your space" button on supported phones.', 'b-media-fields-for-cf7' ),
								'default' => 0,
							),
						),
					),
					'rendering'   => array(
						'label'  => __( 'Lighting & rendering', 'b-media-fields-for-cf7' ),
						'desc'   => '',
						'fields' => array(
							'environment'      => array(
								'type'    => 'radio',
								'label'   => __( 'Environment lighting', 'b-media-fields-for-cf7' ),
								'choices' => array(
									''        => __( 'Default (model-viewer built-in)', 'b-media-fields-for-cf7' ),
									'neutral' => __( 'Neutral – even, studio-like lighting', 'b-media-fields-for-cf7' ),
									'legacy'  => __( 'Legacy – the older, warmer default', 'b-media-fields-for-cf7' ),
								),
								'default' => '',
							),
							'tone_mapping'     => array(
								'type'    => 'radio',
								'label'   => __( 'Tone mapping', 'b-media-fields-for-cf7' ),
								'choices' => array(
									''         => __( 'Default (neutral)', 'b-media-fields-for-cf7' ),
									'aces'     => 'ACES',
									'agx'      => 'AgX',
									'reinhard' => 'Reinhard',
									'cineon'   => 'Cineon',
									'linear'   => 'Linear',
									'none'     => __( 'None', 'b-media-fields-for-cf7' ),
								),
								'default' => '',
							),
							'exposure'         => array(
								'type'    => 'number',
								'label'   => __( 'Exposure', 'b-media-fields-for-cf7' ),
								'desc'    => __( '1 = neutral. Higher values brighten the scene.', 'b-media-fields-for-cf7' ),
								'default' => 1,
								'min'     => 0,
								'max'     => 10,
								'step'    => 0.05,
							),
							'shadow_intensity' => array(
								'type'    => 'number',
								'label'   => __( 'Shadow intensity', 'b-media-fields-for-cf7' ),
								'desc'    => __( '0 = no shadow, 1 = strongest.', 'b-media-fields-for-cf7' ),
								'default' => 0,
								'min'     => 0,
								'max'     => 1,
								'step'    => 0.05,
							),
							'loading'          => array(
								'type'    => 'radio',
								'label'   => __( 'Loading strategy', 'b-media-fields-for-cf7' ),
								'choices' => array(
									'auto'  => __( 'Auto – load when near the viewport', 'b-media-fields-for-cf7' ),
									'eager' => __( 'Eager – load immediately', 'b-media-fields-for-cf7' ),
								),
								'default' => 'auto',
							),
						),
					),
				),
			),
			'gallery'      => array(
				'label'      => __( 'Image Gallery', 'b-media-fields-for-cf7' ),
				'icon'       => 'dashicons-format-gallery',
				'desc'       => __( 'Responsive image gallery inside forms — grid, masonry, justified rows or carousel, with a lightbox.', 'b-media-fields-for-cf7' ),
				'status'     => 'active',
				'tag'        => '[gallery my-gallery "https://example.com/photo.jpg"]',
				'toggleable' => true,
				'groups'     => array(
					'layout'    => array(
						'label'  => __( 'Layout', 'b-media-fields-for-cf7' ),
						'desc'   => __( 'Defaults for every gallery. Individual form-tags can override each of these.', 'b-media-fields-for-cf7' ),
						'fields' => array(
							'layout'  => array(
								'type'    => 'radio',
								'label'   => __( 'Default layout', 'b-media-fields-for-cf7' ),
								'choices' => array(
									'grid'      => __( 'Grid — equal tiles', 'b-media-fields-for-cf7' ),
									'masonry'   => __( 'Masonry — natural heights', 'b-media-fields-for-cf7' ),
									'justified' => __( 'Justified rows', 'b-media-fields-for-cf7' ),
									'carousel'  => __( 'Carousel / slider', 'b-media-fields-for-cf7' ),
								),
								'default' => 'grid',
							),
							'columns' => array(
								'type'    => 'number',
								'label'   => __( 'Columns', 'b-media-fields-for-cf7' ),
								'desc'    => __( 'Halves on tablet and drops to one column on phones.', 'b-media-fields-for-cf7' ),
								'default' => 3,
								'min'     => 1,
								'max'     => 8,
							),
							'gap'     => array(
								'type'    => 'number',
								'label'   => __( 'Gap between images (px)', 'b-media-fields-for-cf7' ),
								'default' => 8,
								'min'     => 0,
								'max'     => 80,
							),
							'ratio'   => array(
								'type'    => 'radio',
								'label'   => __( 'Thumbnail ratio', 'b-media-fields-for-cf7' ),
								'choices' => array(
									''     => __( 'Original image ratio', 'b-media-fields-for-cf7' ),
									'1:1'  => '1:1',
									'4:3'  => '4:3',
									'16:9' => '16:9',
								),
								'default' => '4:3',
							),
							'height'  => array(
								'type'    => 'number',
								'label'   => __( 'Row height for justified / carousel (px)', 'b-media-fields-for-cf7' ),
								'default' => 240,
								'min'     => 80,
								'max'     => 900,
							),
						),
					),
					'behaviour' => array(
						'label'  => __( 'Behaviour', 'b-media-fields-for-cf7' ),
						'desc'   => '',
						'fields' => array(
							'lightbox' => array(
								'type'    => 'toggle',
								'label'   => __( 'Lightbox', 'b-media-fields-for-cf7' ),
								'desc'    => __( 'Open images full size when clicked.', 'b-media-fields-for-cf7' ),
								'default' => 1,
							),
							'captions' => array(
								'type'    => 'toggle',
								'label'   => __( 'Show captions', 'b-media-fields-for-cf7' ),
								'desc'    => __( 'Captions are added per image with a pipe: "image.jpg|My caption".', 'b-media-fields-for-cf7' ),
								'default' => 0,
							),
						),
					),
				),
			),
			'pdf_flipbook' => array(
				'label'      => __( 'PDF Flipbook', 'b-media-fields-for-cf7' ),
				'icon'       => 'dashicons-book-alt',
				'desc'       => __( 'Page-flipping PDF viewer (brochures, catalogues, manuals) inside forms.', 'b-media-fields-for-cf7' ),
				'status'     => 'active',
				'tag'        => '[pdf_flipbook my-brochure "https://example.com/brochure.pdf"]',
				'toggleable' => true,
				'groups'     => array(
					'appearance' => array(
						'label'  => __( 'Appearance', 'b-media-fields-for-cf7' ),
						'desc'   => __( 'Defaults for every PDF viewer. Individual form-tags can override each of these.', 'b-media-fields-for-cf7' ),
						'fields' => array(
							'height'     => array(
								'type'    => 'number',
								'label'   => __( 'Viewer height (px)', 'b-media-fields-for-cf7' ),
								'default' => 520,
								'min'     => 200,
								'max'     => 2000,
							),
							'background' => array(
								'type'    => 'color',
								'label'   => __( 'Background colour', 'b-media-fields-for-cf7' ),
								'desc'    => __( 'Behind the pages.', 'b-media-fields-for-cf7' ),
								'default' => '',
							),
						),
					),
					'viewer'     => array(
						'label'  => __( 'Viewer', 'b-media-fields-for-cf7' ),
						'desc'   => '',
						'fields' => array(
							'mode'      => array(
								'type'    => 'radio',
								'label'   => __( 'Default mode', 'b-media-fields-for-cf7' ),
								'choices' => array(
									'flip'   => __( 'Flipbook — turn pages like a book', 'b-media-fields-for-cf7' ),
									'scroll' => __( 'Scroll — stacked pages', 'b-media-fields-for-cf7' ),
								),
								'default' => 'flip',
							),
							'flip_time' => array(
								'type'    => 'number',
								'label'   => __( 'Page turn duration (ms)', 'b-media-fields-for-cf7' ),
								'default' => 800,
								'min'     => 100,
								'max'     => 3000,
							),
							'toolbar'   => array(
								'type'    => 'toggle',
								'label'   => __( 'Toolbar', 'b-media-fields-for-cf7' ),
								'desc'    => __( 'Page navigation, zoom and fullscreen controls under the document.', 'b-media-fields-for-cf7' ),
								'default' => 1,
							),
						),
					),
				),
			),
			'general'      => array(
				'label'      => __( 'General', 'b-media-fields-for-cf7' ),
				'icon'       => 'dashicons-admin-generic',
				'desc'       => __( 'Loading behaviour and housekeeping.', 'b-media-fields-for-cf7' ),
				'status'     => 'active',
				'toggleable' => false,
				'groups'     => array(
					'loading' => array(
						'label'  => __( 'Asset loading', 'b-media-fields-for-cf7' ),
						'desc'   => '',
						'fields' => array(
							'script_build'    => array(
								'type'    => 'radio',
								'label'   => __( 'Player build', 'b-media-fields-for-cf7' ),
								'desc'    => __( 'The polyfilled build adds support for very old browsers at the cost of a larger file.', 'b-media-fields-for-cf7' ),
								'choices' => array(
									'standard'   => __( 'Standard (modern browsers, smaller)', 'b-media-fields-for-cf7' ),
									'polyfilled' => __( 'Polyfilled (older browsers)', 'b-media-fields-for-cf7' ),
								),
								'default' => 'standard',
							),
							'load_everywhere' => array(
								'type'    => 'toggle',
								'label'   => __( 'Load assets on every page', 'b-media-fields-for-cf7' ),
								'desc'    => __( 'By default scripts load only where a form with a media tag renders. Turn on if forms are injected later by page builders, popups or AJAX.', 'b-media-fields-for-cf7' ),
								'default' => 0,
							),
						),
					),
					'cleanup' => array(
						'label'  => __( 'Uninstall', 'b-media-fields-for-cf7' ),
						'desc'   => '',
						'fields' => array(
							'delete_on_uninstall' => array(
								'type'    => 'toggle',
								'label'   => __( 'Delete settings on uninstall', 'b-media-fields-for-cf7' ),
								'desc'    => __( 'Remove all plugin settings from the database when the plugin is deleted.', 'b-media-fields-for-cf7' ),
								'default' => 0,
							),
						),
					),
				),
			),
		);

		/**
		 * Filters the settings schema (add sections for new field types here).
		 *
		 * @param array $schema Schema.
		 */
		$schema = apply_filters( 'bmfcf7_settings_schema', $schema );

		return $schema;
	}

	/**
	 * Controls that make sense for audio players.
	 *
	 * @return array
	 */
	private static function audio_controls() {
		$skip = array( 'play-large', 'captions', 'pip', 'airplay', 'fullscreen' );
		return array_diff_key( BMFCF7_Options::controls(), array_flip( $skip ) );
	}

	// Accessors.

	/**
	 * Default values for the whole option.
	 *
	 * @return array
	 */
	public static function defaults() {
		$defaults = array();

		foreach ( self::schema() as $section_key => $section ) {
			$defaults[ $section_key ] = array();

			if ( ! empty( $section['toggleable'] ) ) {
				$defaults[ $section_key ]['enabled'] = 1;
			}

			foreach ( $section['groups'] as $group ) {
				foreach ( $group['fields'] as $key => $field ) {
					$defaults[ $section_key ][ $key ] = $field['default'];
				}
			}
		}

		return $defaults;
	}

	/**
	 * Returns the full, merged settings array.
	 *
	 * @return array
	 */
	public static function all() {
		$saved    = get_option( self::OPTION, array() );
		$saved    = is_array( $saved ) ? $saved : array();
		$defaults = self::defaults();

		foreach ( $defaults as $section => $fields ) {
			$saved[ $section ] = wp_parse_args(
				isset( $saved[ $section ] ) && is_array( $saved[ $section ] ) ? $saved[ $section ] : array(),
				$fields
			);
		}

		return $saved;
	}

	/**
	 * Returns one section (e.g. "video") merged with defaults.
	 *
	 * @param string $section Section key.
	 * @return array
	 */
	public static function section( $section ) {
		$all = self::all();
		return isset( $all[ $section ] ) ? $all[ $section ] : array();
	}

	/**
	 * Returns one value.
	 *
	 * @param string $section Section key.
	 * @param string $key     Field key.
	 * @param mixed  $fallback Fallback when missing.
	 * @return mixed
	 */
	public static function get( $section, $key, $fallback = null ) {
		$data = self::section( $section );
		return array_key_exists( $key, $data ) ? $data[ $key ] : $fallback;
	}

	/**
	 * Whether a field type is enabled.
	 *
	 * @param string $section Field type key.
	 * @return bool
	 */
	public static function is_enabled( $section ) {
		$schema = self::schema();

		if ( empty( $schema[ $section ] ) || 'active' !== $schema[ $section ]['status'] ) {
			return false;
		}

		if ( empty( $schema[ $section ]['toggleable'] ) ) {
			return true;
		}

		return ! empty( self::get( $section, 'enabled', 1 ) );
	}

	// Registration / sanitisation.

	/**
	 * Adds the submenu under Contact Form 7's menu.
	 */
	public static function add_menu() {
		if ( ! current_user_can( 'wpcf7_edit_contact_forms' ) ) {
			return;
		}

		add_submenu_page(
			'wpcf7',
			__( 'Media Fields for Contact Form 7', 'b-media-fields-for-cf7' ),
			__( 'Media Fields', 'b-media-fields-for-cf7' ),
			'wpcf7_edit_contact_forms',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Registers the option.
	 */
	public static function register() {
		register_setting(
			self::GROUP,
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	/**
	 * Sanitises the submitted option against the schema.
	 *
	 * @param mixed $input Raw input.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$input  = is_array( $input ) ? $input : array();
		$output = self::defaults();

		foreach ( self::schema() as $section_key => $section ) {
			$in = isset( $input[ $section_key ] ) && is_array( $input[ $section_key ] ) ? $input[ $section_key ] : array();

			if ( ! empty( $section['toggleable'] ) ) {
				$output[ $section_key ]['enabled'] = empty( $in['enabled'] ) ? 0 : 1;
			}

			foreach ( $section['groups'] as $group ) {
				foreach ( $group['fields'] as $key => $field ) {
					$raw = isset( $in[ $key ] ) ? $in[ $key ] : null;

					switch ( $field['type'] ) {
						case 'toggle':
							$output[ $section_key ][ $key ] = empty( $raw ) ? 0 : 1;
							break;

						case 'color':
							$hex                            = is_string( $raw ) ? sanitize_hex_color( $raw ) : '';
							$output[ $section_key ][ $key ] = $hex ? $hex : '';
							break;

						case 'chips':
							$raw                            = is_array( $raw ) ? array_map( 'sanitize_key', $raw ) : array();
							$allowed                        = array_keys( $field['choices'] );
							$output[ $section_key ][ $key ] = array_values( array_intersect( $allowed, $raw ) );
							break;

						case 'radio':
							$output[ $section_key ][ $key ] = ( is_string( $raw ) && isset( $field['choices'][ $raw ] ) ) ? $raw : $field['default'];
							break;

						case 'number':
							$num = is_numeric( $raw ) ? $raw + 0 : $field['default'];
							if ( isset( $field['min'] ) && $num < $field['min'] ) {
								$num = $field['min'];
							}
							if ( isset( $field['max'] ) && $num > $field['max'] ) {
								$num = $field['max'];
							}
							$output[ $section_key ][ $key ] = $num;
							break;

						case 'text':
						default:
							$output[ $section_key ][ $key ] = is_string( $raw ) ? sanitize_text_field( $raw ) : $field['default'];
							break;
					}
				}
			}
		}

		return $output;
	}

	// Rendering.

	/**
	 * Enqueues settings page assets.
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public static function enqueue( $hook_suffix ) {
		if ( false === strpos( $hook_suffix, self::PAGE_SLUG ) ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_enqueue_style(
			'bmfcf7-settings',
			BMFCF7_URL . 'assets/css/settings.css',
			array( 'wp-color-picker' ),
			BMFCF7_VERSION
		);

		wp_enqueue_script(
			'bmfcf7-settings',
			BMFCF7_URL . 'assets/js/settings.js',
			array( 'jquery', 'wp-color-picker' ),
			BMFCF7_VERSION,
			true
		);
	}

	/**
	 * Renders the page.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'wpcf7_edit_contact_forms' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'b-media-fields-for-cf7' ) );
		}

		$schema   = self::schema();
		$values   = self::all();
		$fields_n = count(
			array_filter(
				array_keys( $schema ),
				static function ( $k ) use ( $schema ) {
					return 'general' !== $k && 'active' === $schema[ $k ]['status'];
				}
			)
		);
		?>
		<div class="wrap bmfcf7-admin">
			<h1 class="screen-reader-text"><?php esc_html_e( 'Media Fields for Contact Form 7', 'b-media-fields-for-cf7' ); ?></h1>

			<header class="bmfcf7-header">
				<div class="bmfcf7-header__brand">
					<img class="bmfcf7-logo" src="<?php echo esc_url( BMFCF7_URL . 'assets/img/icon.svg' ); ?>" width="44" height="44" alt="" aria-hidden="true" />
					<div>
						<div class="bmfcf7-header__title"><?php esc_html_e( 'Media Fields for Contact Form 7', 'b-media-fields-for-cf7' ); ?> <span class="bmfcf7-badge bmfcf7-badge--muted">v<?php echo esc_html( BMFCF7_VERSION ); ?></span></div>
						<div class="bmfcf7-header__sub">
							<?php
							printf(
								/* translators: %d: number of active field types */
								esc_html( _n( '%d additional field type for Contact Form 7', '%d additional field types for Contact Form 7', $fields_n, 'b-media-fields-for-cf7' ) ),
								(int) $fields_n
							);
							?>
						</div>
					</div>
				</div>
				<div class="bmfcf7-header__actions">
					<a class="bmfcf7-btn bmfcf7-btn--ghost" href="<?php echo esc_url( admin_url( 'admin.php?page=wpcf7' ) ); ?>"><span class="dashicons dashicons-feedback"></span> <?php esc_html_e( 'Contact forms', 'b-media-fields-for-cf7' ); ?></a>
					<a class="bmfcf7-btn bmfcf7-btn--ghost" href="https://wordpress.org/support/plugin/b-media-fields-for-cf7/" target="_blank" rel="noopener"><span class="dashicons dashicons-sos"></span> <?php esc_html_e( 'Support', 'b-media-fields-for-cf7' ); ?></a>
				</div>
			</header>

			<?php settings_errors(); ?>

			<form method="post" action="options.php" class="bmfcf7-form" id="bmfcf7-settings-form">
				<?php settings_fields( self::GROUP ); ?>

				<div class="bmfcf7-layout">
					<nav class="bmfcf7-nav" aria-label="<?php esc_attr_e( 'Settings sections', 'b-media-fields-for-cf7' ); ?>">
						<div class="bmfcf7-nav__brand">
							<img src="<?php echo esc_url( BMFCF7_URL . 'assets/img/icon.svg' ); ?>" width="28" height="28" alt="" aria-hidden="true" />
							<span><?php esc_html_e( 'Media Fields', 'b-media-fields-for-cf7' ); ?></span>
						</div>
						<a class="bmfcf7-nav__item" href="#overview" data-bmfcf7-tab="overview">
							<span class="dashicons dashicons-screenoptions"></span>
							<span class="bmfcf7-nav__label"><?php esc_html_e( 'Overview', 'b-media-fields-for-cf7' ); ?></span>
						</a>
						<div class="bmfcf7-nav__group"><?php esc_html_e( 'Field types', 'b-media-fields-for-cf7' ); ?></div>
						<?php foreach ( $schema as $key => $section ) : ?>
							<?php
							if ( 'general' === $key ) {
								continue; }
							?>
							<a class="bmfcf7-nav__item<?php echo 'planned' === $section['status'] ? ' is-planned' : ''; ?>" href="#<?php echo esc_attr( $key ); ?>" data-bmfcf7-tab="<?php echo esc_attr( $key ); ?>">
								<span class="dashicons <?php echo esc_attr( $section['icon'] ); ?>"></span>
								<span class="bmfcf7-nav__label"><?php echo esc_html( $section['label'] ); ?></span>
								<?php if ( 'planned' === $section['status'] ) : ?>
									<span class="bmfcf7-badge bmfcf7-badge--soon"><?php esc_html_e( 'Soon', 'b-media-fields-for-cf7' ); ?></span>
								<?php elseif ( ! empty( $section['toggleable'] ) ) : ?>
									<span class="bmfcf7-dot<?php echo self::is_enabled( $key ) ? ' is-on' : ''; ?>" data-bmfcf7-dot="<?php echo esc_attr( $key ); ?>" aria-hidden="true"></span>
								<?php endif; ?>
							</a>
						<?php endforeach; ?>
						<div class="bmfcf7-nav__group"><?php esc_html_e( 'Plugin', 'b-media-fields-for-cf7' ); ?></div>
						<a class="bmfcf7-nav__item" href="#general" data-bmfcf7-tab="general">
							<span class="dashicons dashicons-admin-generic"></span>
							<span class="bmfcf7-nav__label"><?php esc_html_e( 'General', 'b-media-fields-for-cf7' ); ?></span>
						</a>
						<a class="bmfcf7-nav__item" href="#reference" data-bmfcf7-tab="reference">
							<span class="dashicons dashicons-editor-code"></span>
							<span class="bmfcf7-nav__label"><?php esc_html_e( 'Tag reference', 'b-media-fields-for-cf7' ); ?></span>
						</a>
					</nav>

					<main class="bmfcf7-content">
						<?php self::render_overview( $schema ); ?>

						<?php foreach ( $schema as $key => $section ) : ?>
							<?php self::render_section( $key, $section, $values[ $key ] ); ?>
						<?php endforeach; ?>

						<?php self::render_reference(); ?>

						<div class="bmfcf7-savebar">
							<span class="bmfcf7-savebar__hint" data-bmfcf7-dirty-hint hidden><?php esc_html_e( 'You have unsaved changes.', 'b-media-fields-for-cf7' ); ?></span>
							<?php submit_button( __( 'Save changes', 'b-media-fields-for-cf7' ), 'primary bmfcf7-btn bmfcf7-btn--primary', 'submit', false ); ?>
						</div>
					</main>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Overview panel: field type cards.
	 *
	 * @param array $schema Schema.
	 */
	private static function render_overview( $schema ) {
		?>
		<section class="bmfcf7-panel" data-bmfcf7-panel="overview">
			<div class="bmfcf7-video">
				<div class="bmfcf7-video__player">
					<button type="button" class="bmfcf7-video__facade" data-bmfcf7-video="<?php echo esc_attr( BMFCF7_TUTORIAL_VIDEO ); ?>">
						<img src="<?php echo esc_url( BMFCF7_URL . 'assets/img/getting-started-poster.jpg' ); ?>" alt="" loading="lazy" />
						<span class="bmfcf7-video__play" aria-hidden="true"></span>
						<span class="screen-reader-text"><?php esc_html_e( 'Play the getting started tutorial (opens YouTube in this page)', 'b-media-fields-for-cf7' ); ?></span>
					</button>
				</div>
				<div class="bmfcf7-video__copy">
					<span class="bmfcf7-badge bmfcf7-badge--soon"><?php esc_html_e( 'Start here', 'b-media-fields-for-cf7' ); ?></span>
					<h2><?php esc_html_e( 'Getting started, step by step', 'b-media-fields-for-cf7' ); ?></h2>
					<p><?php esc_html_e( 'A short tutorial that walks through everything once: open a form, build your first video field with the tag generator, put the form on a page, then add audio and a PDF, and set your defaults on this screen.', 'b-media-fields-for-cf7' ); ?></p>
					<p class="bmfcf7-muted"><?php esc_html_e( 'Nothing is requested from YouTube until you press play.', 'b-media-fields-for-cf7' ); ?></p>
					<a class="bmfcf7-btn bmfcf7-btn--ghost" href="<?php echo esc_url( 'https://www.youtube.com/watch?v=' . BMFCF7_TUTORIAL_VIDEO ); ?>" target="_blank" rel="noopener">
						<span class="dashicons dashicons-external"></span>
						<?php esc_html_e( 'Watch on YouTube', 'b-media-fields-for-cf7' ); ?>
					</a>
				</div>
			</div>

			<div class="bmfcf7-video bmfcf7-video--secondary">
				<div class="bmfcf7-video__player">
					<button type="button" class="bmfcf7-video__facade" data-bmfcf7-video="<?php echo esc_attr( BMFCF7_INTRO_VIDEO ); ?>">
						<img src="<?php echo esc_url( BMFCF7_URL . 'assets/img/intro-poster.jpg' ); ?>" alt="" loading="lazy" />
						<span class="bmfcf7-video__play" aria-hidden="true"></span>
						<span class="screen-reader-text"><?php esc_html_e( 'Play the introduction video (opens YouTube in this page)', 'b-media-fields-for-cf7' ); ?></span>
					</button>
				</div>
				<div class="bmfcf7-video__copy">
					<h3><?php esc_html_e( 'In a hurry? Watch the 2-minute introduction', 'b-media-fields-for-cf7' ); ?></h3>
					<p><?php esc_html_e( 'A quick tour of what each field type can do inside a Contact Form 7 form.', 'b-media-fields-for-cf7' ); ?></p>
					<a class="bmfcf7-link" href="<?php echo esc_url( 'https://www.youtube.com/watch?v=' . BMFCF7_INTRO_VIDEO ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Watch on YouTube', 'b-media-fields-for-cf7' ); ?> &rarr;</a>
				</div>
			</div>

			<div class="bmfcf7-panel__head">
				<div class="bmfcf7-panel__title">
					<span class="bmfcf7-panel__icon"><span class="dashicons dashicons-screenoptions"></span></span>
					<div>
						<h2><?php esc_html_e( 'Overview', 'b-media-fields-for-cf7' ); ?></h2>
						<p><?php esc_html_e( 'Each field type adds a form-tag and a matching button in the Contact Form 7 editor. Enable the ones you use and configure their defaults.', 'b-media-fields-for-cf7' ); ?></p>
					</div>
				</div>
			</div>
			<div class="bmfcf7-cards">
				<?php foreach ( $schema as $key => $section ) : ?>
					<?php
					if ( 'general' === $key ) {
						continue; }
					?>
					<?php $planned = ( 'planned' === $section['status'] ); ?>
					<article class="bmfcf7-card<?php echo $planned ? ' is-planned' : ''; ?>">
						<div class="bmfcf7-card__icon"><span class="dashicons <?php echo esc_attr( $section['icon'] ); ?>"></span></div>
						<div class="bmfcf7-card__body">
							<h3>
								<?php echo esc_html( $section['label'] ); ?>
								<?php if ( $planned ) : ?>
									<span class="bmfcf7-badge bmfcf7-badge--soon"><?php esc_html_e( 'Coming soon', 'b-media-fields-for-cf7' ); ?></span>
								<?php endif; ?>
							</h3>
							<p><?php echo esc_html( $section['desc'] ); ?></p>
							<?php if ( ! empty( $section['tag'] ) ) : ?>
								<code class="bmfcf7-code"><?php echo esc_html( $section['tag'] ); ?></code>
							<?php endif; ?>
						</div>
						<div class="bmfcf7-card__actions">
							<?php if ( $planned ) : ?>
								<span class="bmfcf7-muted"><?php esc_html_e( 'Planned', 'b-media-fields-for-cf7' ); ?></span>
							<?php else : ?>
								<?php if ( ! empty( $section['toggleable'] ) ) : ?>
									<label class="bmfcf7-switch" title="<?php esc_attr_e( 'Enable this field type', 'b-media-fields-for-cf7' ); ?>">
										<input type="checkbox" name="<?php echo esc_attr( self::OPTION . '[' . $key . '][enabled]' ); ?>" value="1" <?php checked( self::is_enabled( $key ) ); ?> data-bmfcf7-enable="<?php echo esc_attr( $key ); ?>" />
										<span class="bmfcf7-switch__track"></span>
										<span class="screen-reader-text"><?php esc_html_e( 'Enabled', 'b-media-fields-for-cf7' ); ?></span>
									</label>
								<?php endif; ?>
								<a class="bmfcf7-btn bmfcf7-btn--ghost bmfcf7-btn--sm" href="#<?php echo esc_attr( $key ); ?>" data-bmfcf7-goto="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Configure', 'b-media-fields-for-cf7' ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span></a>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}

	/**
	 * One settings section panel.
	 *
	 * @param string $key     Section key.
	 * @param array  $section Schema section.
	 * @param array  $values  Current values.
	 */
	private static function render_section( $key, $section, $values ) {
		$planned = ( 'planned' === $section['status'] );
		?>
		<section class="bmfcf7-panel" data-bmfcf7-panel="<?php echo esc_attr( $key ); ?>" hidden>
			<div class="bmfcf7-panel__head">
				<div class="bmfcf7-panel__title">
					<span class="bmfcf7-panel__icon"><span class="dashicons <?php echo esc_attr( $section['icon'] ); ?>"></span></span>
					<div>
						<h2><?php echo esc_html( $section['label'] ); ?></h2>
						<p><?php echo esc_html( $section['desc'] ); ?></p>
					</div>
				</div>
				<?php if ( ! $planned && ! empty( $section['toggleable'] ) ) : ?>
					<label class="bmfcf7-switch bmfcf7-switch--labelled">
						<span class="bmfcf7-switch__text" data-bmfcf7-enable-text data-on="<?php esc_attr_e( 'Enabled', 'b-media-fields-for-cf7' ); ?>" data-off="<?php esc_attr_e( 'Disabled', 'b-media-fields-for-cf7' ); ?>"><?php echo self::is_enabled( $key ) ? esc_html__( 'Enabled', 'b-media-fields-for-cf7' ) : esc_html__( 'Disabled', 'b-media-fields-for-cf7' ); ?></span>
						<input type="checkbox" value="1" <?php checked( self::is_enabled( $key ) ); ?> data-bmfcf7-enable="<?php echo esc_attr( $key ); ?>" data-bmfcf7-enable-mirror="1" />
						<span class="bmfcf7-switch__track"></span>
					</label>
				<?php endif; ?>
			</div>

			<?php if ( $planned ) : ?>
				<div class="bmfcf7-empty">
					<span class="dashicons <?php echo esc_attr( $section['icon'] ); ?>"></span>
					<h3><?php esc_html_e( 'This field type is on the roadmap', 'b-media-fields-for-cf7' ); ?></h3>
					<p><?php echo esc_html( $section['desc'] ); ?></p>
					<?php if ( ! empty( $section['tag'] ) ) : ?>
						<code class="bmfcf7-code"><?php echo esc_html( $section['tag'] ); ?></code>
					<?php endif; ?>
				</div>
				<?php
				echo '</section>';
				return;
			endif;
			?>

			<?php if ( ! empty( $section['tag'] ) ) : ?>
				<div class="bmfcf7-callout">
					<span class="dashicons dashicons-editor-code"></span>
					<div>
						<strong><?php esc_html_e( 'Form-tag', 'b-media-fields-for-cf7' ); ?></strong>
						<code class="bmfcf7-code"><?php echo esc_html( $section['tag'] ); ?></code>
						<span class="bmfcf7-muted"><?php esc_html_e( 'Use the matching button in the form editor to generate a tag with every option.', 'b-media-fields-for-cf7' ); ?></span>
					</div>
				</div>
			<?php endif; ?>

			<?php foreach ( $section['groups'] as $group_key => $group ) : ?>
				<div class="bmfcf7-group">
					<div class="bmfcf7-group__head">
						<h3><?php echo esc_html( $group['label'] ); ?></h3>
						<?php if ( ! empty( $group['desc'] ) ) : ?>
							<p><?php echo esc_html( $group['desc'] ); ?></p>
						<?php endif; ?>
					</div>
					<?php foreach ( $group['fields'] as $field_key => $field ) : ?>
						<?php self::render_field( $key, $field_key, $field, isset( $values[ $field_key ] ) ? $values[ $field_key ] : $field['default'] ); ?>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</section>
		<?php
	}

	/**
	 * One setting row.
	 *
	 * @param string $section Section key.
	 * @param string $key     Field key.
	 * @param array  $field   Field schema.
	 * @param mixed  $value   Current value.
	 */
	private static function render_field( $section, $key, $field, $value ) {
		$name = self::OPTION . '[' . $section . '][' . $key . ']';
		$id   = 'bmfcf7-' . $section . '-' . $key;
		?>
		<div class="bmfcf7-row bmfcf7-row--<?php echo esc_attr( $field['type'] ); ?>">
			<div class="bmfcf7-row__label">
				<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
				<?php if ( ! empty( $field['desc'] ) ) : ?>
					<p class="bmfcf7-row__desc"><?php echo esc_html( $field['desc'] ); ?></p>
				<?php endif; ?>
			</div>
			<div class="bmfcf7-row__control">
				<?php
				switch ( $field['type'] ) {
					case 'toggle':
						?>
						<label class="bmfcf7-switch">
							<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( ! empty( $value ) ); ?> />
							<span class="bmfcf7-switch__track"></span>
						</label>
						<?php
						break;

					case 'color':
						?>
						<input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" class="bmfcf7-color" data-default-color="#00b2ff" />
						<?php
						break;

					case 'chips':
						?>
						<div class="bmfcf7-chips" role="group" aria-labelledby="<?php echo esc_attr( $id ); ?>">
							<?php foreach ( $field['choices'] as $choice => $label ) : ?>
								<label class="bmfcf7-chip">
									<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[]" value="<?php echo esc_attr( $choice ); ?>" <?php checked( in_array( $choice, (array) $value, true ) ); ?> />
									<span class="bmfcf7-chip__box"><span class="dashicons dashicons-yes"></span> <?php echo esc_html( $label ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
						<?php
						break;

					case 'radio':
						?>
						<div class="bmfcf7-radios">
							<?php foreach ( $field['choices'] as $choice => $label ) : ?>
								<label class="bmfcf7-radio">
									<input type="radio" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $choice ); ?>" <?php checked( $value, $choice ); ?> />
									<span><?php echo esc_html( $label ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
						<?php
						break;

					case 'number':
						?>
						<input type="number" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" class="bmfcf7-input bmfcf7-input--sm" <?php echo isset( $field['min'] ) ? 'min="' . esc_attr( $field['min'] ) . '"' : ''; ?> <?php echo isset( $field['max'] ) ? 'max="' . esc_attr( $field['max'] ) . '"' : ''; ?> step="<?php echo esc_attr( isset( $field['step'] ) ? $field['step'] : 'any' ); ?>" />
						<?php
						break;

					default:
						?>
						<input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" class="bmfcf7-input" />
						<?php
						break;
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Tag reference panel.
	 */
	private static function render_reference() {
		$rows = array(
			array( '[video intro "https://example.com/intro.mp4"]', __( 'Self-hosted MP4', 'b-media-fields-for-cf7' ) ),
			array( '[video intro quality:720 "https://example.com/intro-1080.mp4|1080" "https://example.com/intro-720.mp4|720"]', __( 'Two qualities with a quality menu', 'b-media-fields-for-cf7' ) ),
			array( '[video promo provider:youtube yt-nocookie "https://www.youtube.com/watch?v=bTqVqk7FSmY"]', __( 'YouTube (privacy mode)', 'b-media-fields-for-cf7' ) ),
			array( '[video promo provider:vimeo autoplay muted loop "76979871"]', __( 'Vimeo, muted autoplay loop', 'b-media-fields-for-cf7' ) ),
			array( '[audio podcast artist:Jane_Doe "https://example.com/ep1.mp3"] Episode 1 [/audio]', __( 'Audio with a title and artist', 'b-media-fields-for-cf7' ) ),
			array( '[3d_models chair auto-rotate ar "https://example.com/chair.glb" "https://example.com/chair.usdz"] Red armchair [/3d_models]', __( '3D model with auto-rotate and AR (USDZ for iOS)', 'b-media-fields-for-cf7' ) ),
			array( '[3d_models chair camera-orbit:45deg|60deg|2m hotspot:0|0.5|0.2|Handle "https://example.com/chair.glb"]', __( '3D model with a start camera and a hotspot', 'b-media-fields-for-cf7' ) ),
		);
		?>
		<section class="bmfcf7-panel" data-bmfcf7-panel="reference" hidden>
			<div class="bmfcf7-panel__head">
				<div class="bmfcf7-panel__title">
					<span class="bmfcf7-panel__icon"><span class="dashicons dashicons-editor-code"></span></span>
					<div>
						<h2><?php esc_html_e( 'Tag reference', 'b-media-fields-for-cf7' ); ?></h2>
						<p><?php esc_html_e( 'Syntax: the tag name comes first, then options, and the quoted media URL(s) always come last. Use underscores for spaces and | to separate list items.', 'b-media-fields-for-cf7' ); ?></p>
					</div>
				</div>
			</div>
			<div class="bmfcf7-group">
				<table class="bmfcf7-table">
					<thead><tr><th><?php esc_html_e( 'Example', 'b-media-fields-for-cf7' ); ?></th><th><?php esc_html_e( 'Result', 'b-media-fields-for-cf7' ); ?></th></tr></thead>
					<tbody>
						<?php foreach ( $rows as $row ) : ?>
							<tr><td><code class="bmfcf7-code"><?php echo esc_html( $row[0] ); ?></code></td><td><?php echo esc_html( $row[1] ); ?></td></tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</section>
		<?php
	}
}
