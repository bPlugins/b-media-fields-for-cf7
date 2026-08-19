<?php
/**
 * Registry of [3d_models] form-tag options mapped to <model-viewer>
 * attributes and CSS custom properties (https://modelviewer.dev/docs/).
 *
 * Field keys:
 *  - type     flag | number | text | token | url | select | multi | list | color
 *  - attr     <model-viewer> attribute name the value is written to
 *  - css      CSS custom property (e.g. --poster-color) instead of attr
 *  - join     for multi/list: glue used when writing the attribute (default " ")
 *  - value    for flag: attribute value (true = boolean attribute; a string
 *             writes attr="value"; false = internal negative flag)
 *
 * Form-tag option values may not contain spaces, so multi-part values such
 * as camera-orbit use "|" as the separator: camera-orbit:0deg|75deg|105%.
 *
 * @package EssentialFieldsCF7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Static registry for the 3D model field.
 */
final class EFCF7_Model_Options {

	/**
	 * AR modes.
	 *
	 * @return array
	 */
	public static function ar_modes() {
		return array(
			'webxr'        => __( 'WebXR (in-browser AR, Android Chrome)', 'essential-fields-for-cf7' ),
			'scene-viewer' => __( 'Scene Viewer (Android app)', 'essential-fields-for-cf7' ),
			'quick-look'   => __( 'Quick Look (iOS – needs a USDZ file)', 'essential-fields-for-cf7' ),
		);
	}

	/**
	 * Tone mapping functions.
	 *
	 * @return array
	 */
	public static function tone_mappings() {
		return array(
			''         => __( 'Default (neutral)', 'essential-fields-for-cf7' ),
			'neutral'  => 'neutral',
			'aces'     => 'aces',
			'agx'      => 'agx',
			'reinhard' => 'reinhard',
			'cineon'   => 'cineon',
			'linear'   => 'linear',
			'none'     => 'none',
		);
	}

	/**
	 * Option groups shown in the tag generator.
	 *
	 * @return array
	 */
	public static function groups() {
		return array(
			'layout'    => array(
				'label' => __( 'Layout & appearance', 'essential-fields-for-cf7' ),
				'desc'  => '',
			),
			'loading'   => array(
				'label' => __( 'Loading', 'essential-fields-for-cf7' ),
				'desc'  => __( 'The title field above is used as the accessible description (alt).', 'essential-fields-for-cf7' ),
			),
			'camera'    => array(
				'label' => __( 'Camera & interaction', 'essential-fields-for-cf7' ),
				'desc'  => __( 'Camera orbit / target / orientation / scale take three values separated by |, e.g. 0deg|75deg|105%.', 'essential-fields-for-cf7' ),
			),
			'ar'        => array(
				'label' => __( 'Augmented reality', 'essential-fields-for-cf7' ),
				'desc'  => __( 'Add a .usdz file as a second source (or use ios-src) for iOS Quick Look.', 'essential-fields-for-cf7' ),
			),
			'lighting'  => array(
				'label' => __( 'Lighting, environment & skybox', 'essential-fields-for-cf7' ),
				'desc'  => '',
			),
			'animation' => array(
				'label' => __( 'Animation & variants', 'essential-fields-for-cf7' ),
				'desc'  => __( 'Use underscores for spaces in names.', 'essential-fields-for-cf7' ),
			),
			'hotspots'  => array(
				'label' => __( 'Hotspots (annotations)', 'essential-fields-for-cf7' ),
				'desc'  => __( 'Each hotspot is x|y|z|Label (positions in metres, label optional, underscores for spaces). Separate several hotspots with spaces. Optionally append |nx|ny|nz for the surface normal.', 'essential-fields-for-cf7' ),
			),
			'advanced'  => array(
				'label' => __( 'Advanced', 'essential-fields-for-cf7' ),
				'desc'  => '',
			),
		);
	}

	/**
	 * Every form-tag option.
	 *
	 * @return array
	 */
	public static function fields() {
		static $fields = null;

		if ( null !== $fields ) {
			return $fields;
		}

		$fields = array(

			/* ---------------- Layout ---------------- */
			'height'                       => array(
				'group' => 'layout',
				'type'  => 'number',
				'label' => __( 'Height (px)', 'essential-fields-for-cf7' ),
				'desc'  => __( 'Leave empty to use the default from the settings page.', 'essential-fields-for-cf7' ),
				'min'   => 100,
				'max'   => 2000,
				'attr'  => '_height',
			),
			'width'                        => array(
				'group' => 'layout',
				'type'  => 'number',
				'label' => __( 'Max width (px)', 'essential-fields-for-cf7' ),
				'min'   => 100,
				'max'   => 4000,
				'attr'  => '_width',
			),
			'align'                        => array(
				'group'   => 'layout',
				'type'    => 'select',
				'label'   => __( 'Alignment', 'essential-fields-for-cf7' ),
				'attr'    => '_align',
				'choices' => array(
					''       => __( 'Default (left)', 'essential-fields-for-cf7' ),
					'center' => __( 'Center', 'essential-fields-for-cf7' ),
					'right'  => __( 'Right', 'essential-fields-for-cf7' ),
				),
			),
			'bg'                           => array(
				'group' => 'layout',
				'type'  => 'color',
				'label' => __( 'Background colour', 'essential-fields-for-cf7' ),
				'desc'  => __( 'Hex colour behind the model (ignored when a skybox image is set).', 'essential-fields-for-cf7' ),
				'attr'  => '_bg',
			),
			'poster-color'                 => array(
				'group' => 'layout',
				'type'  => 'color',
				'label' => __( 'Poster colour', 'essential-fields-for-cf7' ),
				'desc'  => __( 'Colour shown while the model loads (--poster-color).', 'essential-fields-for-cf7' ),
				'css'   => '--poster-color',
			),
			'progress-color'               => array(
				'group' => 'layout',
				'type'  => 'color',
				'label' => __( 'Progress bar colour', 'essential-fields-for-cf7' ),
				'css'   => '--progress-bar-color',
			),
			'progress-height'              => array(
				'group' => 'layout',
				'type'  => 'number',
				'label' => __( 'Progress bar height (px)', 'essential-fields-for-cf7' ),
				'min'   => 0,
				'max'   => 50,
				'css'   => '--progress-bar-height',
				'unit'  => 'px',
			),
			'no-progress-bar'              => array(
				'group' => 'layout',
				'type'  => 'flag',
				'label' => __( 'Hide the loading progress bar', 'essential-fields-for-cf7' ),
				'attr'  => '_no_progress',
				'value' => true,
			),

			/* ---------------- Loading ---------------- */
			'poster'                       => array(
				'group' => 'loading',
				'type'  => 'url',
				'media' => 'image',
				'label' => __( 'Poster image URL', 'essential-fields-for-cf7' ),
				'desc'  => __( 'Shown until the model is loaded and rendered.', 'essential-fields-for-cf7' ),
				'attr'  => 'poster',
			),
			'loading'                      => array(
				'group'   => 'loading',
				'type'    => 'select',
				'label'   => __( 'Loading strategy', 'essential-fields-for-cf7' ),
				'attr'    => 'loading',
				'choices' => array(
					''      => __( 'Auto (load when near the viewport)', 'essential-fields-for-cf7' ),
					'lazy'  => 'lazy',
					'eager' => 'eager',
				),
			),
			'reveal'                       => array(
				'group'   => 'loading',
				'type'    => 'select',
				'label'   => __( 'Reveal', 'essential-fields-for-cf7' ),
				'desc'    => __( '"manual" keeps the poster until the visitor interacts.', 'essential-fields-for-cf7' ),
				'attr'    => 'reveal',
				'choices' => array(
					''       => __( 'Auto', 'essential-fields-for-cf7' ),
					'manual' => 'manual',
				),
			),
			'with-credentials'             => array(
				'group' => 'loading',
				'type'  => 'flag',
				'label' => __( 'Send credentials when fetching the model (with-credentials)', 'essential-fields-for-cf7' ),
				'attr'  => 'with-credentials',
				'value' => true,
			),
			'generate-schema'              => array(
				'group' => 'loading',
				'type'  => 'flag',
				'label' => __( 'Generate JSON-LD 3DModel schema for SEO (generate-schema)', 'essential-fields-for-cf7' ),
				'attr'  => 'generate-schema',
				'value' => true,
			),

			/* ---------------- Camera ---------------- */
			'no-camera-controls'           => array(
				'group' => 'camera',
				'type'  => 'flag',
				'label' => __( 'Disable mouse / touch camera controls', 'essential-fields-for-cf7' ),
				'attr'  => '_no_camera_controls',
				'value' => true,
			),
			'auto-rotate'                  => array(
				'group' => 'camera',
				'type'  => 'flag',
				'label' => __( 'Auto-rotate', 'essential-fields-for-cf7' ),
				'attr'  => 'auto-rotate',
				'value' => true,
			),
			'no-auto-rotate'               => array(
				'group' => 'camera',
				'type'  => 'flag',
				'label' => __( 'Never auto-rotate (overrides the global default)', 'essential-fields-for-cf7' ),
				'attr'  => '_no_auto_rotate',
				'value' => true,
			),
			'auto-rotate-delay'            => array(
				'group' => 'camera',
				'type'  => 'number',
				'label' => __( 'Auto-rotate delay (ms)', 'essential-fields-for-cf7' ),
				'min'   => 0,
				'attr'  => 'auto-rotate-delay',
			),
			'rotation-per-second'          => array(
				'group' => 'camera',
				'type'  => 'token',
				'label' => __( 'Rotation speed (rotation-per-second)', 'essential-fields-for-cf7' ),
				'desc'  => __( 'e.g. 30deg, 0.5rad or -100%', 'essential-fields-for-cf7' ),
				'attr'  => 'rotation-per-second',
			),
			'camera-orbit'                 => array(
				'group' => 'camera',
				'type'  => 'list',
				'label' => __( 'Camera orbit (theta|phi|radius)', 'essential-fields-for-cf7' ),
				'desc'  => __( 'Default 0deg|75deg|105%. Radius accepts m, cm, mm, % or auto.', 'essential-fields-for-cf7' ),
				'attr'  => 'camera-orbit',
			),
			'camera-target'                => array(
				'group' => 'camera',
				'type'  => 'list',
				'label' => __( 'Camera target (x|y|z)', 'essential-fields-for-cf7' ),
				'desc'  => __( 'e.g. 0m|1.5m|-0.5m or auto|auto|auto', 'essential-fields-for-cf7' ),
				'attr'  => 'camera-target',
			),
			'field-of-view'                => array(
				'group' => 'camera',
				'type'  => 'token',
				'label' => __( 'Field of view', 'essential-fields-for-cf7' ),
				'desc'  => __( 'e.g. 30deg or 0.5rad', 'essential-fields-for-cf7' ),
				'attr'  => 'field-of-view',
			),
			'min-camera-orbit'             => array(
				'group' => 'camera',
				'type'  => 'list',
				'label' => __( 'Minimum camera orbit (theta|phi|radius)', 'essential-fields-for-cf7' ),
				'attr'  => 'min-camera-orbit',
			),
			'max-camera-orbit'             => array(
				'group' => 'camera',
				'type'  => 'list',
				'label' => __( 'Maximum camera orbit (theta|phi|radius)', 'essential-fields-for-cf7' ),
				'attr'  => 'max-camera-orbit',
			),
			'min-field-of-view'            => array(
				'group' => 'camera',
				'type'  => 'token',
				'label' => __( 'Minimum field of view (max zoom-in)', 'essential-fields-for-cf7' ),
				'attr'  => 'min-field-of-view',
			),
			'max-field-of-view'            => array(
				'group' => 'camera',
				'type'  => 'token',
				'label' => __( 'Maximum field of view (max zoom-out)', 'essential-fields-for-cf7' ),
				'attr'  => 'max-field-of-view',
			),
			'disable-zoom'                 => array(
				'group' => 'camera',
				'type'  => 'flag',
				'label' => __( 'Disable zoom', 'essential-fields-for-cf7' ),
				'attr'  => 'disable-zoom',
				'value' => true,
			),
			'disable-pan'                  => array(
				'group' => 'camera',
				'type'  => 'flag',
				'label' => __( 'Disable panning', 'essential-fields-for-cf7' ),
				'attr'  => 'disable-pan',
				'value' => true,
			),
			'disable-tap'                  => array(
				'group' => 'camera',
				'type'  => 'flag',
				'label' => __( 'Disable tap-to-recenter', 'essential-fields-for-cf7' ),
				'attr'  => 'disable-tap',
				'value' => true,
			),
			'touch-action'                 => array(
				'group'   => 'camera',
				'type'    => 'select',
				'label'   => __( 'Touch action', 'essential-fields-for-cf7' ),
				'desc'    => __( 'pan-y lets touch users scroll the page vertically over the model.', 'essential-fields-for-cf7' ),
				'attr'    => 'touch-action',
				'choices' => array(
					''      => __( 'Default (pan-y)', 'essential-fields-for-cf7' ),
					'pan-y' => 'pan-y',
					'pan-x' => 'pan-x',
					'none'  => 'none',
				),
			),
			'orbit-sensitivity'            => array(
				'group' => 'camera',
				'type'  => 'number',
				'label' => __( 'Orbit sensitivity', 'essential-fields-for-cf7' ),
				'step'  => 0.1,
				'attr'  => 'orbit-sensitivity',
			),
			'zoom-sensitivity'             => array(
				'group' => 'camera',
				'type'  => 'number',
				'label' => __( 'Zoom sensitivity', 'essential-fields-for-cf7' ),
				'step'  => 0.1,
				'attr'  => 'zoom-sensitivity',
			),
			'pan-sensitivity'              => array(
				'group' => 'camera',
				'type'  => 'number',
				'label' => __( 'Pan sensitivity', 'essential-fields-for-cf7' ),
				'step'  => 0.1,
				'attr'  => 'pan-sensitivity',
			),
			'interaction-prompt'           => array(
				'group'   => 'camera',
				'type'    => 'select',
				'label'   => __( 'Interaction prompt', 'essential-fields-for-cf7' ),
				'attr'    => 'interaction-prompt',
				'choices' => array(
					''     => __( 'Auto (show after the threshold)', 'essential-fields-for-cf7' ),
					'none' => __( 'None', 'essential-fields-for-cf7' ),
				),
			),
			'interaction-prompt-style'     => array(
				'group'   => 'camera',
				'type'    => 'select',
				'label'   => __( 'Interaction prompt style', 'essential-fields-for-cf7' ),
				'attr'    => 'interaction-prompt-style',
				'choices' => array(
					''      => __( 'Default (wiggle)', 'essential-fields-for-cf7' ),
					'basic' => 'basic',
				),
			),
			'interaction-prompt-threshold' => array(
				'group' => 'camera',
				'type'  => 'number',
				'label' => __( 'Interaction prompt threshold (ms)', 'essential-fields-for-cf7' ),
				'min'   => 0,
				'attr'  => 'interaction-prompt-threshold',
			),
			'interpolation-decay'          => array(
				'group' => 'camera',
				'type'  => 'number',
				'label' => __( 'Interpolation decay (ms)', 'essential-fields-for-cf7' ),
				'desc'  => __( 'Camera movement smoothing; default 50.', 'essential-fields-for-cf7' ),
				'min'   => 1,
				'attr'  => 'interpolation-decay',
			),

			/* ---------------- AR ---------------- */
			'ar'                           => array(
				'group' => 'ar',
				'type'  => 'flag',
				'label' => __( 'Enable AR ("View in your space" button)', 'essential-fields-for-cf7' ),
				'attr'  => 'ar',
				'value' => true,
			),
			'no-ar'                        => array(
				'group' => 'ar',
				'type'  => 'flag',
				'label' => __( 'Never show AR (overrides the global default)', 'essential-fields-for-cf7' ),
				'attr'  => '_no_ar',
				'value' => true,
			),
			'ar-modes'                     => array(
				'group'   => 'ar',
				'type'    => 'multi',
				'label'   => __( 'AR modes (priority order)', 'essential-fields-for-cf7' ),
				'attr'    => 'ar-modes',
				'choices' => self::ar_modes(),
			),
			'ar-scale'                     => array(
				'group'   => 'ar',
				'type'    => 'select',
				'label'   => __( 'AR scale', 'essential-fields-for-cf7' ),
				'attr'    => 'ar-scale',
				'choices' => array(
					''      => __( 'Auto (pinch to resize)', 'essential-fields-for-cf7' ),
					'fixed' => __( 'Fixed (always 100%)', 'essential-fields-for-cf7' ),
				),
			),
			'ar-placement'                 => array(
				'group'   => 'ar',
				'type'    => 'select',
				'label'   => __( 'AR placement', 'essential-fields-for-cf7' ),
				'attr'    => 'ar-placement',
				'choices' => array(
					''     => __( 'Floor', 'essential-fields-for-cf7' ),
					'wall' => __( 'Wall', 'essential-fields-for-cf7' ),
				),
			),
			'ios-src'                      => array(
				'group' => 'ar',
				'type'  => 'url',
				'media' => 'model',
				'label' => __( 'iOS USDZ file URL (ios-src)', 'essential-fields-for-cf7' ),
				'attr'  => 'ios-src',
			),
			'usdz-max-texture-size'        => array(
				'group' => 'ar',
				'type'  => 'number',
				'label' => __( 'Max texture size for auto-generated USDZ', 'essential-fields-for-cf7' ),
				'min'   => 16,
				'attr'  => 'ar-usdz-max-texture-size',
			),
			'xr-environment'               => array(
				'group' => 'ar',
				'type'  => 'flag',
				'label' => __( 'Use AR lighting estimation in WebXR (xr-environment)', 'essential-fields-for-cf7' ),
				'attr'  => 'xr-environment',
				'value' => true,
			),
			'ar-button-label'              => array(
				'group' => 'ar',
				'type'  => 'text',
				'label' => __( 'AR button label', 'essential-fields-for-cf7' ),
				'desc'  => __( 'Replaces the default button; underscores become spaces.', 'essential-fields-for-cf7' ),
				'attr'  => '_ar_button',
			),

			/* ---------------- Lighting ---------------- */
			'environment'                  => array(
				'group' => 'lighting',
				'type'  => 'token',
				'media' => 'any',
				'label' => __( 'Environment image (environment-image)', 'essential-fields-for-cf7' ),
				'desc'  => __( '"neutral", "legacy" or the URL of an equirectangular .hdr / .jpg.', 'essential-fields-for-cf7' ),
				'attr'  => 'environment-image',
			),
			'skybox'                       => array(
				'group' => 'lighting',
				'type'  => 'url',
				'media' => 'any',
				'label' => __( 'Skybox image URL (skybox-image)', 'essential-fields-for-cf7' ),
				'attr'  => 'skybox-image',
			),
			'skybox-height'                => array(
				'group' => 'lighting',
				'type'  => 'token',
				'label' => __( 'Skybox height (projects the skybox onto the ground)', 'essential-fields-for-cf7' ),
				'desc'  => __( 'e.g. 1.5m', 'essential-fields-for-cf7' ),
				'attr'  => 'skybox-height',
			),
			'exposure'                     => array(
				'group' => 'lighting',
				'type'  => 'number',
				'label' => __( 'Exposure', 'essential-fields-for-cf7' ),
				'min'   => 0,
				'step'  => 0.05,
				'attr'  => 'exposure',
			),
			'tone-mapping'                 => array(
				'group'   => 'lighting',
				'type'    => 'select',
				'label'   => __( 'Tone mapping', 'essential-fields-for-cf7' ),
				'attr'    => 'tone-mapping',
				'choices' => self::tone_mappings(),
			),
			'shadow-intensity'             => array(
				'group' => 'lighting',
				'type'  => 'number',
				'label' => __( 'Shadow intensity (0–1)', 'essential-fields-for-cf7' ),
				'min'   => 0,
				'max'   => 1,
				'step'  => 0.05,
				'attr'  => 'shadow-intensity',
			),
			'shadow-softness'              => array(
				'group' => 'lighting',
				'type'  => 'number',
				'label' => __( 'Shadow softness (0–1)', 'essential-fields-for-cf7' ),
				'min'   => 0,
				'max'   => 1,
				'step'  => 0.05,
				'attr'  => 'shadow-softness',
			),

			/* ---------------- Animation & scene ---------------- */
			'animation'                    => array(
				'group' => 'animation',
				'type'  => 'text',
				'label' => __( 'Animation name', 'essential-fields-for-cf7' ),
				'attr'  => 'animation-name',
			),
			'autoplay'                     => array(
				'group' => 'animation',
				'type'  => 'flag',
				'label' => __( 'Autoplay the animation', 'essential-fields-for-cf7' ),
				'attr'  => 'autoplay',
				'value' => true,
			),
			'crossfade'                    => array(
				'group' => 'animation',
				'type'  => 'number',
				'label' => __( 'Animation crossfade duration (ms)', 'essential-fields-for-cf7' ),
				'min'   => 0,
				'attr'  => 'animation-crossfade-duration',
			),
			'variant'                      => array(
				'group' => 'animation',
				'type'  => 'text',
				'label' => __( 'Material variant name', 'essential-fields-for-cf7' ),
				'attr'  => 'variant-name',
			),
			'orientation'                  => array(
				'group' => 'animation',
				'type'  => 'list',
				'label' => __( 'Orientation (roll|pitch|yaw)', 'essential-fields-for-cf7' ),
				'desc'  => __( 'e.g. 0deg|0deg|90deg', 'essential-fields-for-cf7' ),
				'attr'  => 'orientation',
			),
			'scale'                        => array(
				'group' => 'animation',
				'type'  => 'list',
				'label' => __( 'Scale (x|y|z)', 'essential-fields-for-cf7' ),
				'desc'  => __( 'e.g. 1|1|1 or 0.5|0.5|0.5', 'essential-fields-for-cf7' ),
				'attr'  => 'scale',
			),
			'bounds'                       => array(
				'group'   => 'animation',
				'type'    => 'select',
				'label'   => __( 'Bounds calculation', 'essential-fields-for-cf7' ),
				'attr'    => 'bounds',
				'choices' => array(
					''       => __( 'Default', 'essential-fields-for-cf7' ),
					'tight'  => 'tight',
					'legacy' => 'legacy',
				),
			),

			/* ---------------- Hotspots ---------------- */
			'hotspot'                      => array(
				'group'    => 'hotspots',
				'type'     => 'list',
				'label'    => __( 'Hotspots', 'essential-fields-for-cf7' ),
				'desc'     => __( 'Example: 0|0.5|0.2|Handle -0.3|0.1|0|Base_plate', 'essential-fields-for-cf7' ),
				'attr'     => '_hotspots',
				'sanitize' => 'captions',
			),
			'min-hotspot-opacity'          => array(
				'group' => 'hotspots',
				'type'  => 'number',
				'label' => __( 'Hidden hotspot opacity (0–1)', 'essential-fields-for-cf7' ),
				'min'   => 0,
				'max'   => 1,
				'step'  => 0.05,
				'css'   => '--min-hotspot-opacity',
			),
			'max-hotspot-opacity'          => array(
				'group' => 'hotspots',
				'type'  => 'number',
				'label' => __( 'Visible hotspot opacity (0–1)', 'essential-fields-for-cf7' ),
				'min'   => 0,
				'max'   => 1,
				'step'  => 0.05,
				'css'   => '--max-hotspot-opacity',
			),

			/* ---------------- Advanced ---------------- */
			'seamless-poster'              => array(
				'group' => 'advanced',
				'type'  => 'flag',
				'label' => __( 'Seamless poster transition', 'essential-fields-for-cf7' ),
				'attr'  => 'seamless-poster',
				'value' => true,
			),
		);

		/**
		 * Filters the registry of [3d_models] options.
		 *
		 * @param array $fields Field definitions keyed by option name.
		 */
		$fields = apply_filters( 'efcf7_model_option_fields', $fields );

		return $fields;
	}

	/**
	 * Fields of one group.
	 *
	 * @param string $group Group key.
	 * @return array
	 */
	public static function fields_in_group( $group ) {
		return array_filter(
			self::fields(),
			static function ( $field ) use ( $group ) {
				return isset( $field['group'] ) && $field['group'] === $group;
			}
		);
	}
}
