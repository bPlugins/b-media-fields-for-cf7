<?php
/**
 * Registry describing every form-tag option and how it maps to Plyr config.
 *
 * The same registry drives the front-end config builder (form-tag handler)
 * and the admin tag generator panel, so both always stay in sync.
 *
 * Form-tag option values in Contact Form 7 may only contain the characters
 * -+*=0-9a-zA-Z:.!?#$&@_/|% (no spaces or commas). Lists therefore use "|"
 * as a separator and free text uses "_" in place of spaces.
 *
 * @package BMediaFieldsCF7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Static registry of Plyr options.
 */
final class BMFCF7_Options {

	/**
	 * Plyr control names (in Plyr's own default order).
	 *
	 * @return array<string,string>
	 */
	public static function controls() {
		return array(
			'play-large'   => __( 'Large play button (centre)', 'b-media-fields-for-cf7' ),
			'restart'      => __( 'Restart', 'b-media-fields-for-cf7' ),
			'rewind'       => __( 'Rewind', 'b-media-fields-for-cf7' ),
			'play'         => __( 'Play / pause', 'b-media-fields-for-cf7' ),
			'fast-forward' => __( 'Fast forward', 'b-media-fields-for-cf7' ),
			'progress'     => __( 'Progress bar & scrubber', 'b-media-fields-for-cf7' ),
			'current-time' => __( 'Current time', 'b-media-fields-for-cf7' ),
			'duration'     => __( 'Duration', 'b-media-fields-for-cf7' ),
			'mute'         => __( 'Mute toggle', 'b-media-fields-for-cf7' ),
			'volume'       => __( 'Volume slider', 'b-media-fields-for-cf7' ),
			'captions'     => __( 'Captions toggle', 'b-media-fields-for-cf7' ),
			'settings'     => __( 'Settings menu', 'b-media-fields-for-cf7' ),
			'pip'          => __( 'Picture-in-picture', 'b-media-fields-for-cf7' ),
			'airplay'      => __( 'AirPlay (Safari)', 'b-media-fields-for-cf7' ),
			'download'     => __( 'Download', 'b-media-fields-for-cf7' ),
			'fullscreen'   => __( 'Fullscreen', 'b-media-fields-for-cf7' ),
		);
	}

	/**
	 * Plyr's default control set.
	 *
	 * @return string[]
	 */
	public static function default_controls() {
		return array( 'play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen' );
	}

	/**
	 * Plyr settings-menu entries.
	 *
	 * @return array<string,string>
	 */
	public static function settings_menu() {
		return array(
			'captions' => __( 'Captions', 'b-media-fields-for-cf7' ),
			'quality'  => __( 'Quality', 'b-media-fields-for-cf7' ),
			'speed'    => __( 'Speed', 'b-media-fields-for-cf7' ),
			'loop'     => __( 'Loop', 'b-media-fields-for-cf7' ),
		);
	}

	/**
	 * Plyr's default settings-menu set.
	 *
	 * @return string[]
	 */
	public static function default_settings_menu() {
		return array( 'captions', 'quality', 'speed' );
	}

	/**
	 * Supported providers.
	 *
	 * @return array<string,string>
	 */
	public static function providers() {
		return array(
			'html5'   => __( 'Self-hosted (HTML5 video / audio file)', 'b-media-fields-for-cf7' ),
			'youtube' => __( 'YouTube', 'b-media-fields-for-cf7' ),
			'vimeo'   => __( 'Vimeo', 'b-media-fields-for-cf7' ),
		);
	}

	/**
	 * Option groups shown in the tag generator.
	 *
	 * @return array<string,array{label:string,desc:string}>
	 */
	public static function groups() {
		return array(
			'layout'   => array(
				'label' => __( 'Layout & appearance', 'b-media-fields-for-cf7' ),
				'desc'  => '',
			),
			'playback' => array(
				'label' => __( 'Playback', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'Most browsers only allow autoplay when the media is muted.', 'b-media-fields-for-cf7' ),
			),
			'controls' => array(
				'label' => __( 'Controls & settings menu', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'Leave every box unticked to use the default set (or the defaults from the plugin settings page).', 'b-media-fields-for-cf7' ),
			),
			'ui'       => array(
				'label' => __( 'Interface behaviour', 'b-media-fields-for-cf7' ),
				'desc'  => '',
			),
			'captions' => array(
				'label' => __( 'Captions', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'Captions apply to self-hosted media (WebVTT files). For YouTube they control the embedded player’s caption preference.', 'b-media-fields-for-cf7' ),
			),
			'speed'    => array(
				'label' => __( 'Speed & quality', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'Quality switching for self-hosted media needs one source per quality with a size hint, e.g. "https://example.com/video-720.mp4|720".', 'b-media-fields-for-cf7' ),
			),
			'metadata' => array(
				'label' => __( 'Media metadata (lock screen / Media Session)', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'Use underscores instead of spaces.', 'b-media-fields-for-cf7' ),
			),
			'markers'  => array(
				'label' => __( 'Timeline markers & preview thumbnails', 'b-media-fields-for-cf7' ),
				'desc'  => '',
			),
			'youtube'  => array(
				'label' => __( 'YouTube options', 'b-media-fields-for-cf7' ),
				'desc'  => '',
			),
			'vimeo'    => array(
				'label' => __( 'Vimeo options', 'b-media-fields-for-cf7' ),
				'desc'  => '',
			),
			'advanced' => array(
				'label' => __( 'Advanced', 'b-media-fields-for-cf7' ),
				'desc'  => '',
			),
		);
	}

	/**
	 * Every form-tag option.
	 *
	 * Field types:
	 *  - flag    checkbox; when present sets `path` to `value`
	 *  - number  numeric value
	 *  - text    free text; underscores become spaces
	 *  - token   free text kept as is (ids, keys, CSS selectors without spaces)
	 *  - url     URL
	 *  - select  one of `choices`
	 *  - multi   checkboxes; each checked box adds `key:choice`; merged to a list
	 *  - list    pipe separated list typed into a text box
	 *
	 * @return array<string,array>
	 */
	public static function fields() {
		static $fields = null;

		if ( null !== $fields ) {
			return $fields;
		}

		$fields = array(

			/* ---------------- Layout ---------------- */
			'ratio'                  => array(
				'group'   => 'layout',
				'type'    => 'select',
				'label'   => __( 'Aspect ratio', 'b-media-fields-for-cf7' ),
				'path'    => 'ratio',
				'choices' => array(
					''     => __( 'Auto (from the media)', 'b-media-fields-for-cf7' ),
					'16:9' => '16:9',
					'4:3'  => '4:3',
					'1:1'  => '1:1',
					'21:9' => '21:9',
					'9:16' => '9:16 (' . __( 'vertical', 'b-media-fields-for-cf7' ) . ')',
				),
			),
			'width'                  => array(
				'group' => 'layout',
				'type'  => 'number',
				'label' => __( 'Max width (px)', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'Leave empty for full width of the form.', 'b-media-fields-for-cf7' ),
				'path'  => '_width',
				'min'   => 50,
				'max'   => 4000,
			),
			'align'                  => array(
				'group'   => 'layout',
				'type'    => 'select',
				'label'   => __( 'Alignment', 'b-media-fields-for-cf7' ),
				'path'    => '_align',
				'choices' => array(
					''       => __( 'Default (left)', 'b-media-fields-for-cf7' ),
					'center' => __( 'Center', 'b-media-fields-for-cf7' ),
					'right'  => __( 'Right', 'b-media-fields-for-cf7' ),
				),
			),
			'color'                  => array(
				'group' => 'layout',
				'type'  => 'color',
				'label' => __( 'Accent colour', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'Hex colour, e.g. #00b3ff. Overrides the colour from the plugin settings.', 'b-media-fields-for-cf7' ),
				'path'  => '_color',
			),
			'poster'                 => array(
				'group' => 'layout',
				'type'  => 'url',
				'media' => 'image',
				'label' => __( 'Poster image URL', 'b-media-fields-for-cf7' ),
				'path'  => '_poster',
			),

			/* ---------------- Playback ---------------- */
			'autoplay'               => array(
				'group' => 'playback',
				'type'  => 'flag',
				'label' => __( 'Autoplay', 'b-media-fields-for-cf7' ),
				'path'  => 'autoplay',
				'value' => true,
			),
			'muted'                  => array(
				'group' => 'playback',
				'type'  => 'flag',
				'label' => __( 'Start muted', 'b-media-fields-for-cf7' ),
				'path'  => 'muted',
				'value' => true,
			),
			'loop'                   => array(
				'group' => 'playback',
				'type'  => 'flag',
				'label' => __( 'Loop', 'b-media-fields-for-cf7' ),
				'path'  => 'loop.active',
				'value' => true,
			),
			'reset-on-end'           => array(
				'group' => 'playback',
				'type'  => 'flag',
				'label' => __( 'Reset to the start when playback ends', 'b-media-fields-for-cf7' ),
				'path'  => 'resetOnEnd',
				'value' => true,
			),
			'no-playsinline'         => array(
				'group' => 'playback',
				'type'  => 'flag',
				'label' => __( 'Disable inline playback on iOS (opens native player)', 'b-media-fields-for-cf7' ),
				'path'  => 'playsinline',
				'value' => false,
			),
			'no-autopause'           => array(
				'group' => 'playback',
				'type'  => 'flag',
				'label' => __( 'Allow several Vimeo players to play at once (disable autopause)', 'b-media-fields-for-cf7' ),
				'path'  => 'autopause',
				'value' => false,
			),
			'volume'                 => array(
				'group' => 'playback',
				'type'  => 'number',
				'label' => __( 'Initial volume (0–1)', 'b-media-fields-for-cf7' ),
				'path'  => 'volume',
				'min'   => 0,
				'max'   => 1,
				'step'  => 0.05,
			),
			'seek-time'              => array(
				'group' => 'playback',
				'type'  => 'number',
				'label' => __( 'Seek time for rewind / fast-forward (seconds)', 'b-media-fields-for-cf7' ),
				'path'  => 'seekTime',
				'min'   => 1,
				'max'   => 600,
			),
			'duration'               => array(
				'group' => 'playback',
				'type'  => 'number',
				'label' => __( 'Custom duration (seconds)', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'Overrides the displayed duration. Usually left empty.', 'b-media-fields-for-cf7' ),
				'path'  => 'duration',
				'min'   => 1,
			),

			/* ---------------- Controls ---------------- */
			'controls'               => array(
				'group'   => 'controls',
				'type'    => 'multi',
				'label'   => __( 'Controls', 'b-media-fields-for-cf7' ),
				'path'    => 'controls',
				'choices' => self::controls(),
			),
			'settings'               => array(
				'group'   => 'controls',
				'type'    => 'multi',
				'label'   => __( 'Settings menu items', 'b-media-fields-for-cf7' ),
				'path'    => 'settings',
				'choices' => self::settings_menu(),
			),
			'download'               => array(
				'group' => 'controls',
				'type'  => 'url',
				'media' => 'any',
				'label' => __( 'Download URL', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'Used by the Download control. Leave empty to download the current source.', 'b-media-fields-for-cf7' ),
				'path'  => 'urls.download',
			),

			/* ---------------- UI behaviour ---------------- */
			'no-click-to-play'       => array(
				'group' => 'ui',
				'type'  => 'flag',
				'label' => __( 'Disable click-to-play on the video area', 'b-media-fields-for-cf7' ),
				'path'  => 'clickToPlay',
				'value' => false,
			),
			'no-hide-controls'       => array(
				'group' => 'ui',
				'type'  => 'flag',
				'label' => __( 'Always show controls (do not auto-hide)', 'b-media-fields-for-cf7' ),
				'path'  => 'hideControls',
				'value' => false,
			),
			'context-menu'           => array(
				'group' => 'ui',
				'type'  => 'flag',
				'label' => __( 'Allow the right-click context menu', 'b-media-fields-for-cf7' ),
				'path'  => 'disableContextMenu',
				'value' => false,
			),
			'no-display-duration'    => array(
				'group' => 'ui',
				'type'  => 'flag',
				'label' => __( 'Hide the duration before playback starts', 'b-media-fields-for-cf7' ),
				'path'  => 'displayDuration',
				'value' => false,
			),
			'no-invert-time'         => array(
				'group' => 'ui',
				'type'  => 'flag',
				'label' => __( 'Show elapsed time instead of remaining time', 'b-media-fields-for-cf7' ),
				'path'  => 'invertTime',
				'value' => false,
			),
			'no-toggle-invert'       => array(
				'group' => 'ui',
				'type'  => 'flag',
				'label' => __( 'Do not allow clicking the time to toggle the format', 'b-media-fields-for-cf7' ),
				'path'  => 'toggleInvert',
				'value' => false,
			),
			'tooltips-controls'      => array(
				'group' => 'ui',
				'type'  => 'flag',
				'label' => __( 'Show tooltips on controls', 'b-media-fields-for-cf7' ),
				'path'  => 'tooltips.controls',
				'value' => true,
			),
			'no-tooltips-seek'       => array(
				'group' => 'ui',
				'type'  => 'flag',
				'label' => __( 'Hide the seek-time tooltip', 'b-media-fields-for-cf7' ),
				'path'  => 'tooltips.seek',
				'value' => false,
			),
			'no-keyboard'            => array(
				'group' => 'ui',
				'type'  => 'flag',
				'label' => __( 'Disable keyboard shortcuts', 'b-media-fields-for-cf7' ),
				'path'  => 'keyboard.focused',
				'value' => false,
			),
			'keyboard-global'        => array(
				'group' => 'ui',
				'type'  => 'flag',
				'label' => __( 'Global keyboard shortcuts (work even when the player is not focused)', 'b-media-fields-for-cf7' ),
				'path'  => 'keyboard.global',
				'value' => true,
			),
			'no-fullscreen'          => array(
				'group' => 'ui',
				'type'  => 'flag',
				'label' => __( 'Disable fullscreen', 'b-media-fields-for-cf7' ),
				'path'  => 'fullscreen.enabled',
				'value' => false,
			),
			'no-fullscreen-fallback' => array(
				'group' => 'ui',
				'type'  => 'flag',
				'label' => __( 'Disable the "full window" fallback where fullscreen is unsupported', 'b-media-fields-for-cf7' ),
				'path'  => 'fullscreen.fallback',
				'value' => false,
			),
			'fullscreen-ios-native'  => array(
				'group' => 'ui',
				'type'  => 'flag',
				'label' => __( 'Use the native iOS fullscreen player', 'b-media-fields-for-cf7' ),
				'path'  => 'fullscreen.iosNative',
				'value' => true,
			),
			'fullscreen-container'   => array(
				'group' => 'ui',
				'type'  => 'token',
				'label' => __( 'Fullscreen container (CSS selector)', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'An ancestor element to use as the fullscreen container instead of the player.', 'b-media-fields-for-cf7' ),
				'path'  => 'fullscreen.container',
			),
			'no-storage'             => array(
				'group' => 'ui',
				'type'  => 'flag',
				'label' => __( 'Do not remember volume / captions / speed in the browser', 'b-media-fields-for-cf7' ),
				'path'  => 'storage.enabled',
				'value' => false,
			),
			'storage-key'            => array(
				'group' => 'ui',
				'type'  => 'token',
				'label' => __( 'Storage key', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'localStorage key used to remember settings (default: plyr).', 'b-media-fields-for-cf7' ),
				'path'  => 'storage.key',
			),

			/* ---------------- Captions ---------------- */
			'captions'               => array(
				'group' => 'captions',
				'type'  => 'list',
				'media' => 'text',
				'label' => __( 'Caption tracks', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'One track per item in the form language|URL|Label, separated by spaces. Example: en|https://example.com/en.vtt|English fr|https://example.com/fr.vtt|Français. The label is optional; use underscores for spaces.', 'b-media-fields-for-cf7' ),
				'path'  => '_captions',
			),
			'captions-active'        => array(
				'group' => 'captions',
				'type'  => 'flag',
				'label' => __( 'Show captions by default', 'b-media-fields-for-cf7' ),
				'path'  => 'captions.active',
				'value' => true,
			),
			'captions-lang'          => array(
				'group' => 'captions',
				'type'  => 'token',
				'label' => __( 'Default caption language', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'Language code such as en or fr. Default: auto (browser language).', 'b-media-fields-for-cf7' ),
				'path'  => 'captions.language',
			),
			'captions-update'        => array(
				'group' => 'captions',
				'type'  => 'flag',
				'label' => __( 'Listen for tracks added later (captions.update)', 'b-media-fields-for-cf7' ),
				'path'  => 'captions.update',
				'value' => true,
			),

			/* ---------------- Speed & quality ---------------- */
			'speed'                  => array(
				'group' => 'speed',
				'type'  => 'number',
				'label' => __( 'Default speed', 'b-media-fields-for-cf7' ),
				'path'  => 'speed.selected',
				'min'   => 0.1,
				'max'   => 16,
				'step'  => 0.05,
			),
			'speed-options'          => array(
				'group' => 'speed',
				'type'  => 'list',
				'label' => __( 'Speed options', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'Pipe separated, e.g. 0.5|0.75|1|1.25|1.5|2', 'b-media-fields-for-cf7' ),
				'path'  => 'speed.options',
				'cast'  => 'float',
			),
			'quality'                => array(
				'group' => 'speed',
				'type'  => 'number',
				'label' => __( 'Default quality (height in px)', 'b-media-fields-for-cf7' ),
				'path'  => 'quality.default',
				'min'   => 1,
			),
			'quality-options'        => array(
				'group' => 'speed',
				'type'  => 'list',
				'label' => __( 'Quality options', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'Pipe separated heights, e.g. 1080|720|480|360', 'b-media-fields-for-cf7' ),
				'path'  => 'quality.options',
				'cast'  => 'int',
			),
			'quality-forced'         => array(
				'group' => 'speed',
				'type'  => 'flag',
				'label' => __( 'Force the quality list even if the player cannot switch (quality.forced)', 'b-media-fields-for-cf7' ),
				'path'  => 'quality.forced',
				'value' => true,
			),

			/* ---------------- Metadata ---------------- */
			'artist'                 => array(
				'group' => 'metadata',
				'type'  => 'text',
				'label' => __( 'Artist', 'b-media-fields-for-cf7' ),
				'path'  => 'mediaMetadata.artist',
			),
			'album'                  => array(
				'group' => 'metadata',
				'type'  => 'text',
				'label' => __( 'Album', 'b-media-fields-for-cf7' ),
				'path'  => 'mediaMetadata.album',
			),
			'artwork'                => array(
				'group' => 'metadata',
				'type'  => 'url',
				'media' => 'image',
				'label' => __( 'Artwork image URL', 'b-media-fields-for-cf7' ),
				'path'  => '_artwork',
			),

			/* ---------------- Markers & thumbnails ---------------- */
			'markers'                => array(
				'group' => 'markers',
				'type'  => 'list',
				'label' => __( 'Markers', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'Pipe separated seconds=Label pairs, e.g. 0=Intro|45=Pricing|120=Questions. Use underscores for spaces.', 'b-media-fields-for-cf7' ),
				'path'  => '_markers',
			),
			'thumbnails'             => array(
				'group' => 'markers',
				'type'  => 'url',
				'media' => 'text',
				'label' => __( 'Preview thumbnails (WebVTT sprite file URL)', 'b-media-fields-for-cf7' ),
				'path'  => '_thumbnails',
			),
			'thumbnails-credentials' => array(
				'group' => 'markers',
				'type'  => 'flag',
				'label' => __( 'Send credentials when loading thumbnails', 'b-media-fields-for-cf7' ),
				'path'  => 'previewThumbnails.withCredentials',
				'value' => true,
			),

			/* ---------------- YouTube ---------------- */
			'yt-nocookie'            => array(
				'group' => 'youtube',
				'type'  => 'flag',
				'label' => __( 'Use youtube-nocookie.com (privacy-enhanced mode)', 'b-media-fields-for-cf7' ),
				'path'  => 'youtube.noCookie',
				'value' => true,
			),
			'yt-rel'                 => array(
				'group' => 'youtube',
				'type'  => 'flag',
				'label' => __( 'Show related videos at the end', 'b-media-fields-for-cf7' ),
				'path'  => 'youtube.rel',
				'value' => 1,
			),
			'yt-annotations'         => array(
				'group' => 'youtube',
				'type'  => 'flag',
				'label' => __( 'Show annotations', 'b-media-fields-for-cf7' ),
				'path'  => 'youtube.iv_load_policy',
				'value' => 1,
			),
			'yt-native-controls'     => array(
				'group' => 'youtube',
				'type'  => 'flag',
				'label' => __( 'Use YouTube’s own controls instead of Plyr’s', 'b-media-fields-for-cf7' ),
				'path'  => 'youtube.customControls',
				'value' => false,
			),
			'yt-start'               => array(
				'group' => 'youtube',
				'type'  => 'number',
				'label' => __( 'Start at (seconds)', 'b-media-fields-for-cf7' ),
				'path'  => 'youtube.start',
				'min'   => 0,
			),
			'yt-end'                 => array(
				'group' => 'youtube',
				'type'  => 'number',
				'label' => __( 'End at (seconds)', 'b-media-fields-for-cf7' ),
				'path'  => 'youtube.end',
				'min'   => 1,
			),
			'yt-hl'                  => array(
				'group' => 'youtube',
				'type'  => 'token',
				'label' => __( 'Interface language (hl), e.g. en or de', 'b-media-fields-for-cf7' ),
				'path'  => 'youtube.hl',
			),

			/* ---------------- Vimeo ---------------- */
			'vimeo-byline'           => array(
				'group' => 'vimeo',
				'type'  => 'flag',
				'label' => __( 'Show byline', 'b-media-fields-for-cf7' ),
				'path'  => 'vimeo.byline',
				'value' => true,
			),
			'vimeo-portrait'         => array(
				'group' => 'vimeo',
				'type'  => 'flag',
				'label' => __( 'Show author portrait', 'b-media-fields-for-cf7' ),
				'path'  => 'vimeo.portrait',
				'value' => true,
			),
			'vimeo-title'            => array(
				'group' => 'vimeo',
				'type'  => 'flag',
				'label' => __( 'Show title', 'b-media-fields-for-cf7' ),
				'path'  => 'vimeo.title',
				'value' => true,
			),
			'no-vimeo-speed'         => array(
				'group' => 'vimeo',
				'type'  => 'flag',
				'label' => __( 'Disable speed controls', 'b-media-fields-for-cf7' ),
				'path'  => 'vimeo.speed',
				'value' => false,
			),
			'vimeo-transparent'      => array(
				'group' => 'vimeo',
				'type'  => 'flag',
				'label' => __( 'Transparent background', 'b-media-fields-for-cf7' ),
				'path'  => 'vimeo.transparent',
				'value' => true,
			),
			'vimeo-native-controls'  => array(
				'group' => 'vimeo',
				'type'  => 'flag',
				'label' => __( 'Use Vimeo’s own controls instead of Plyr’s', 'b-media-fields-for-cf7' ),
				'path'  => 'vimeo.customControls',
				'value' => false,
			),
			'vimeo-premium'          => array(
				'group' => 'vimeo',
				'type'  => 'flag',
				'label' => __( 'Video owner has a Vimeo Pro/Business account (allows hiding native controls)', 'b-media-fields-for-cf7' ),
				'path'  => 'vimeo.premium',
				'value' => true,
			),
			'vimeo-referrer-policy'  => array(
				'group'   => 'vimeo',
				'type'    => 'select',
				'label'   => __( 'Referrer policy', 'b-media-fields-for-cf7' ),
				'path'    => 'vimeo.referrerPolicy',
				'choices' => array(
					''                                => __( 'Default', 'b-media-fields-for-cf7' ),
					'no-referrer'                     => 'no-referrer',
					'no-referrer-when-downgrade'      => 'no-referrer-when-downgrade',
					'origin'                          => 'origin',
					'origin-when-cross-origin'        => 'origin-when-cross-origin',
					'same-origin'                     => 'same-origin',
					'strict-origin'                   => 'strict-origin',
					'strict-origin-when-cross-origin' => 'strict-origin-when-cross-origin',
					'unsafe-url'                      => 'unsafe-url',
				),
			),

			/* ---------------- Advanced ---------------- */
			'ads-publisher-id'       => array(
				'group' => 'advanced',
				'type'  => 'token',
				'label' => __( 'Ads publisher ID (vi.ai)', 'b-media-fields-for-cf7' ),
				'path'  => '_ads_publisher',
			),
			'ads-tag-url'            => array(
				'group' => 'advanced',
				'type'  => 'url',
				'label' => __( 'Ads VAST tag URL', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'Enables the Google IMA ads plugin when set.', 'b-media-fields-for-cf7' ),
				'path'  => '_ads_tag',
			),
			'crossorigin'            => array(
				'group' => 'advanced',
				'type'  => 'flag',
				'label' => __( 'Request media with CORS (crossorigin="anonymous") – needed for caption files hosted on another domain', 'b-media-fields-for-cf7' ),
				'path'  => '_crossorigin',
				'value' => true,
			),
			'debug'                  => array(
				'group' => 'advanced',
				'type'  => 'flag',
				'label' => __( 'Debug mode (log to browser console)', 'b-media-fields-for-cf7' ),
				'path'  => 'debug',
				'value' => true,
			),
			'disabled'               => array(
				'group' => 'advanced',
				'type'  => 'flag',
				'label' => __( 'Disable Plyr (render the plain native player)', 'b-media-fields-for-cf7' ),
				'path'  => 'enabled',
				'value' => false,
			),
		);

		/**
		 * Filters the registry of form-tag options.
		 *
		 * @param array $fields Field definitions keyed by form-tag option name.
		 */
		$fields = apply_filters( 'bmfcf7_option_fields', $fields );

		return $fields;
	}

	/**
	 * Returns fields belonging to a group.
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

	/**
	 * Sets a value on a nested array using dot notation.
	 *
	 * @param array  $config Config array (by reference).
	 * @param string $path   Dot path, e.g. "fullscreen.enabled".
	 * @param mixed  $value  Value.
	 */
	public static function set_path( array &$config, $path, $value ) {
		$keys = explode( '.', $path );
		$ref  = &$config;

		foreach ( $keys as $key ) {
			if ( ! isset( $ref[ $key ] ) || ! is_array( $ref[ $key ] ) ) {
				$ref[ $key ] = array();
			}
			$ref = &$ref[ $key ];
		}

		$ref = $value;
	}

	/**
	 * Converts "Some_text" to "Some text".
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	public static function humanize( $value ) {
		return trim( str_replace( '_', ' ', (string) $value ) );
	}
}
