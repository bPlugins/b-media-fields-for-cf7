<?php
/**
 * [video] and [audio] form-tag handler.
 *
 * Syntax:
 *   [video name option:value flag ... "https://example.com/clip.mp4"] Optional title [/video]
 *   [audio name "https://example.com/track.mp3" ...]
 *
 * Quoted values are media sources (one per quality for self-hosted media,
 * optionally suffixed with "|height", e.g. "clip-720.mp4|720"). For YouTube
 * and Vimeo a single quoted value holds the video URL or ID.
 *
 * @package BMediaFieldsCF7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders the media form-tags.
 */
final class BMFCF7_Form_Tag {

	/**
	 * Registers hooks.
	 */
	public static function init() {
		add_action( 'wpcf7_init', array( __CLASS__, 'register' ), 10, 0 );
	}

	/**
	 * Registers the form-tag types with Contact Form 7.
	 */
	public static function register() {
		wpcf7_add_form_tag(
			array( 'video', 'audio' ),
			array( __CLASS__, 'handler' ),
			array(
				'name-attr'     => true,
				'display-block' => true,
				'not-for-mail'  => true,
			)
		);
	}

	/**
	 * Renders the form-tag.
	 *
	 * @param WPCF7_FormTag $tag Form-tag.
	 * @return string HTML.
	 */
	public static function handler( $tag ) {
		$media_type = ( 'audio' === $tag->basetype ) ? 'audio' : 'video';

		if ( ! BMFCF7_Settings::is_enabled( $media_type ) ) {
			return self::editor_notice(
				sprintf(
					/* translators: 1: field type, 2: settings page link */
					__( 'The %1$s field is disabled. Enable it under Contact → B Media Fields (%2$s).', 'b-media-fields-for-cf7' ),
					$media_type,
					admin_url( 'admin.php?page=' . BMFCF7_Settings::PAGE_SLUG )
				)
			);
		}

		$sources  = self::parse_sources( $tag->raw_values );
		$provider = self::resolve_provider( $tag, $sources, $media_type );

		if ( empty( $sources ) ) {
			return self::editor_notice(
				sprintf(
					/* translators: 1: form-tag name, 2: example form-tag */
					__( 'The media form-tag "%1$s" has no source. Add a quoted URL, e.g. %2$s', 'b-media-fields-for-cf7' ),
					$tag->name,
					'[' . $tag->basetype . ' ' . $tag->name . ' "https://example.com/clip.mp4"]'
				)
			);
		}

		$config = self::build_config( $tag );

		BMFCF7_Assets::enqueue_frontend();

		$wrapper_class = array( 'bmfcf7-player-wrap', 'bmfcf7-' . $media_type, 'bmfcf7-provider-' . $provider );
		$class_option  = $tag->get_class_option();
		if ( $class_option ) {
			$wrapper_class = array_merge( $wrapper_class, explode( ' ', $class_option ) );
		}
		if ( ! empty( $config['_align'] ) ) {
			$wrapper_class[] = 'bmfcf7-align-' . $config['_align'];
		}

		$style = array();
		if ( ! empty( $config['_color'] ) ) {
			$style[] = '--plyr-color-main:' . $config['_color'];
		}
		if ( ! empty( $config['_width'] ) ) {
			$style[] = 'max-width:' . (int) $config['_width'] . 'px';
		}

		$wrapper_atts = array(
			'class'     => implode( ' ', array_unique( array_filter( $wrapper_class ) ) ),
			'id'        => $tag->get_id_option(),
			'style'     => $style ? implode( ';', $style ) : null,
			'data-name' => $tag->name,
		);

		$title = trim( (string) $tag->content );
		if ( '' !== $title ) {
			$config['title'] = $title;
			BMFCF7_Options::set_path( $config, 'mediaMetadata.title', $title );
		}

		$poster      = ! empty( $config['_poster'] ) ? $config['_poster'] : '';
		$captions    = ! empty( $config['_captions'] ) ? $config['_captions'] : array();
		$crossorigin = ! empty( $config['_crossorigin'] );

		// Remove internal keys before handing the config to the browser.
		foreach ( array_keys( $config ) as $key ) {
			if ( 0 === strpos( $key, '_' ) ) {
				unset( $config[ $key ] );
			}
		}

		/**
		 * Filters the Plyr config for a single player.
		 *
		 * @param array         $config   Plyr options.
		 * @param WPCF7_FormTag $tag      The form-tag.
		 * @param string        $provider html5|youtube|vimeo.
		 */
		$config = apply_filters( 'bmfcf7_player_config', $config, $tag, $provider );

		$media_atts = array(
			'class'             => 'bmfcf7-player',
			'data-bmfcf7-config' => wp_json_encode( (object) $config ),
		);

		if ( 'html5' === $provider ) {
			$media_atts['controls']    = 'controls';
			$media_atts['preload']     = 'metadata';
			$media_atts['playsinline'] = ( isset( $config['playsinline'] ) && false === $config['playsinline'] ) ? null : 'playsinline';
			$media_atts['crossorigin'] = $crossorigin ? 'anonymous' : null;

			if ( 'video' === $media_type && $poster ) {
				$media_atts['data-poster'] = $poster;
			}

			$inner = '';
			foreach ( $sources as $source ) {
				$inner .= sprintf(
					"\n\t\t<source %s />",
					wpcf7_format_atts(
						array(
							'src'  => $source['url'],
							'type' => $source['type'],
							'size' => $source['size'] ? (string) $source['size'] : null,
						)
					)
				);
			}

			foreach ( $captions as $track ) {
				$inner .= sprintf(
					"\n\t\t<track %s />",
					wpcf7_format_atts(
						array(
							'kind'    => 'captions',
							'label'   => $track['label'],
							'srclang' => $track['lang'],
							'src'     => $track['url'],
						)
					)
				);
			}

			$media_html = sprintf(
				"<%1\$s %2\$s>%3\$s\n\t\t%4\$s\n\t</%1\$s>",
				$media_type,
				wpcf7_format_atts( $media_atts ),
				$inner,
				esc_html__( 'Your browser does not support embedded media.', 'b-media-fields-for-cf7' )
			);
		} else {
			$embed = $sources[0];

			$media_atts['data-plyr-provider'] = $provider;
			$media_atts['data-plyr-embed-id'] = $embed['id'];

			if ( 'vimeo' === $provider && ! empty( $embed['hash'] ) ) {
				$media_atts['data-plyr-embed-hash'] = $embed['hash'];
			}

			if ( $poster ) {
				$media_atts['data-poster'] = $poster;
			}

			$media_html = sprintf( '<div %s></div>', wpcf7_format_atts( $media_atts ) );
		}

		return sprintf(
			"<div %s>\n\t%s\n</div>",
			wpcf7_format_atts( $wrapper_atts ),
			$media_html
		);
	}

	/**
	 * Builds the Plyr config array from the form-tag options.
	 *
	 * Internal keys start with an underscore and are stripped before output.
	 *
	 * @param WPCF7_FormTag $tag Form-tag.
	 * @return array
	 */
	private static function build_config( $tag ) {
		$config = array();

		foreach ( BMFCF7_Options::fields() as $key => $field ) {
			$type = isset( $field['type'] ) ? $field['type'] : 'text';
			$path = isset( $field['path'] ) ? $field['path'] : null;

			if ( ! $path ) {
				continue;
			}

			switch ( $type ) {
				case 'flag':
					if ( $tag->has_option( $key ) ) {
						BMFCF7_Options::set_path( $config, $path, $field['value'] );
					}
					break;

				case 'number':
					$raw = $tag->get_option( $key, 'signed_num', true );
					if ( false !== $raw && '' !== $raw ) {
						$num = (float) $raw;
						if ( isset( $field['min'] ) && $num < $field['min'] ) {
							$num = (float) $field['min'];
						}
						if ( isset( $field['max'] ) && $num > $field['max'] ) {
							$num = (float) $field['max'];
						}
						BMFCF7_Options::set_path( $config, $path, ( floor( $num ) === $num ) ? (int) $num : $num );
					}
					break;

				case 'select':
					$raw = $tag->get_option( $key, '', true );
					if ( false !== $raw && isset( $field['choices'][ $raw ] ) && '' !== $raw ) {
						BMFCF7_Options::set_path( $config, $path, $raw );
					}
					break;

				case 'color':
					$raw = $tag->get_option( $key, '', true );
					if ( false !== $raw ) {
						$hex = sanitize_hex_color( '#' === substr( $raw, 0, 1 ) ? $raw : '#' . $raw );
						if ( $hex ) {
							BMFCF7_Options::set_path( $config, $path, $hex );
						}
					}
					break;

				case 'url':
					$raw = $tag->get_option( $key, '', true );
					if ( false !== $raw ) {
						$url = self::sanitize_url( $raw );
						if ( $url ) {
							BMFCF7_Options::set_path( $config, $path, $url );
						}
					}
					break;

				case 'token':
					$raw = $tag->get_option( $key, '', true );
					if ( false !== $raw && '' !== $raw ) {
						BMFCF7_Options::set_path( $config, $path, sanitize_text_field( $raw ) );
					}
					break;

				case 'text':
					$raw = $tag->get_option( $key, '', true );
					if ( false !== $raw && '' !== $raw ) {
						BMFCF7_Options::set_path( $config, $path, sanitize_text_field( BMFCF7_Options::humanize( $raw ) ) );
					}
					break;

				case 'multi':
				case 'list':
					$raw = $tag->get_option( $key );
					if ( false === $raw ) {
						break;
					}
					$items = array();
					foreach ( (array) $raw as $chunk ) {
						foreach ( explode( '|', $chunk ) as $item ) {
							$item = trim( $item );
							if ( '' !== $item ) {
								$items[] = $item;
							}
						}
					}
					if ( 'multi' === $type ) {
						$items = array_values( array_intersect( array_keys( $field['choices'] ), $items ) );
					}
					if ( empty( $items ) ) {
						break;
					}
					if ( isset( $field['cast'] ) ) {
						$items = array_map( 'float' === $field['cast'] ? 'floatval' : 'intval', $items );
					} else {
						$items = array_map( 'sanitize_text_field', $items );
					}
					BMFCF7_Options::set_path( $config, $path, $items );
					break;
			}
		}

		// Special / composite options.
		if ( ! empty( $config['_captions'] ) ) {
			$config['_captions'] = self::parse_captions( $tag->get_option( 'captions' ) );
		}

		if ( ! empty( $config['_markers'] ) ) {
			$points = array();
			foreach ( $config['_markers'] as $pair ) {
				$parts    = explode( '=', $pair, 2 );
				$time     = (float) $parts[0];
				$label    = isset( $parts[1] ) ? BMFCF7_Options::humanize( $parts[1] ) : '';
				$points[] = array(
					'time'  => $time,
					'label' => sanitize_text_field( $label ),
				);
			}
			if ( $points ) {
				$config['markers'] = array(
					'enabled' => true,
					'points'  => $points,
				);
			}
		}

		if ( ! empty( $config['_thumbnails'] ) ) {
			BMFCF7_Options::set_path( $config, 'previewThumbnails.enabled', true );
			BMFCF7_Options::set_path( $config, 'previewThumbnails.src', $config['_thumbnails'] );
		}

		if ( ! empty( $config['_artwork'] ) ) {
			BMFCF7_Options::set_path( $config, 'mediaMetadata.artwork', array( array( 'src' => $config['_artwork'] ) ) );
		}

		if ( ! empty( $config['_ads_tag'] ) || ! empty( $config['_ads_publisher'] ) ) {
			$config['ads'] = array(
				'enabled'     => true,
				'publisherId' => ! empty( $config['_ads_publisher'] ) ? $config['_ads_publisher'] : '',
				'tagUrl'      => ! empty( $config['_ads_tag'] ) ? $config['_ads_tag'] : '',
			);
		}

		if ( ! empty( $config['urls']['download'] ) ) {
			$config['ensureDownloadControl'] = true;
		}

		return $config;
	}

	/**
	 * Parses quoted values into normalised sources.
	 *
	 * @param array $values Raw form-tag values.
	 * @return array[] Each: url, type, size, id, hash.
	 */
	private static function parse_sources( $values ) {
		$sources = array();

		foreach ( (array) $values as $value ) {
			$value = trim( (string) $value );
			if ( '' === $value ) {
				continue;
			}

			$size = 0;
			if ( preg_match( '/^(.+)\|(\d{2,4})$/', $value, $m ) ) {
				$value = $m[1];
				$size  = (int) $m[2];
			}

			$sources[] = array(
				'raw'  => $value,
				'url'  => '',
				'type' => '',
				'size' => $size,
				'id'   => '',
				'hash' => '',
			);
		}

		return $sources;
	}

	/**
	 * Works out the provider and finalises each source accordingly.
	 *
	 * @param WPCF7_FormTag $tag        Form-tag.
	 * @param array         $sources    Sources (by reference).
	 * @param string        $media_type video|audio.
	 * @return string html5|youtube|vimeo
	 */
	private static function resolve_provider( $tag, array &$sources, $media_type ) {
		$provider = $tag->get_option( 'provider', '', true );
		$provider = in_array( $provider, array( 'html5', 'youtube', 'vimeo' ), true ) ? $provider : '';

		if ( ! $provider && ! empty( $sources ) ) {
			$provider = ( 'audio' === $media_type ) ? 'html5' : self::detect_provider( $sources[0]['raw'] );
		}

		if ( ! $provider ) {
			$provider = 'html5';
		}

		if ( 'html5' === $provider ) {
			foreach ( $sources as $i => $source ) {
				$url = self::sanitize_url( $source['raw'] );
				if ( ! $url ) {
					unset( $sources[ $i ] );
					continue;
				}
				$sources[ $i ]['url']  = $url;
				$sources[ $i ]['type'] = self::mime_type( $url, $media_type );
			}
			$sources = array_values( $sources );
			return 'html5';
		}

		// Embeds use a single source.
		$first   = isset( $sources[0] ) ? $sources[0] : null;
		$sources = array();

		if ( $first ) {
			$parsed = ( 'youtube' === $provider )
				? self::parse_youtube( $first['raw'] )
				: self::parse_vimeo( $first['raw'] );

			if ( $parsed ) {
				$first['id']   = $parsed['id'];
				$first['hash'] = $parsed['hash'];
				$sources[]     = $first;
			}
		}

		return $provider;
	}

	/**
	 * Detects the provider from a URL.
	 *
	 * @param string $raw URL or ID.
	 * @return string
	 */
	private static function detect_provider( $raw ) {
		$host = wp_parse_url( $raw, PHP_URL_HOST );
		$host = $host ? strtolower( preg_replace( '/^(www|m)\./', '', $host ) ) : '';

		if ( in_array( $host, array( 'youtube.com', 'youtu.be', 'youtube-nocookie.com' ), true ) ) {
			return 'youtube';
		}

		if ( in_array( $host, array( 'vimeo.com', 'player.vimeo.com' ), true ) ) {
			return 'vimeo';
		}

		return 'html5';
	}

	/**
	 * Extracts a YouTube video ID.
	 *
	 * @param string $raw URL or ID.
	 * @return array|null
	 */
	private static function parse_youtube( $raw ) {
		if ( preg_match( '/^[A-Za-z0-9_-]{11}$/', $raw ) ) {
			return array(
				'id'   => $raw,
				'hash' => '',
			);
		}

		if ( preg_match( '#(?:v=|/embed/|youtu\.be/|/shorts/|/live/|/v/)([A-Za-z0-9_-]{11})#', $raw, $m ) ) {
			return array(
				'id'   => $m[1],
				'hash' => '',
			);
		}

		return null;
	}

	/**
	 * Extracts a Vimeo video ID (and unlisted hash if present).
	 *
	 * @param string $raw URL or ID.
	 * @return array|null
	 */
	private static function parse_vimeo( $raw ) {
		if ( preg_match( '/^\d+$/', $raw ) ) {
			return array(
				'id'   => $raw,
				'hash' => '',
			);
		}

		if ( preg_match( '#vimeo\.com/(?:video/|channels/[^/]+/|groups/[^/]+/videos/)?(\d+)(?:/([a-zA-Z0-9]+))?#', $raw, $m ) ) {
			$hash = isset( $m[2] ) ? $m[2] : '';
			if ( ! $hash && preg_match( '/[?&]h=([a-zA-Z0-9]+)/', $raw, $h ) ) {
				$hash = $h[1];
			}
			return array(
				'id'   => $m[1],
				'hash' => $hash,
			);
		}

		return null;
	}

	/**
	 * Parses captions option values ("lang|url|Label").
	 *
	 * @param array|false $raw Raw option values.
	 * @return array[]
	 */
	private static function parse_captions( $raw ) {
		$tracks = array();

		foreach ( (array) $raw as $item ) {
			$parts = explode( '|', (string) $item );
			if ( count( $parts ) < 2 ) {
				continue;
			}

			$lang = sanitize_text_field( $parts[0] );
			$url  = self::sanitize_url( $parts[1] );
			if ( ! $lang || ! $url ) {
				continue;
			}

			$label = isset( $parts[2] ) && '' !== $parts[2]
				? BMFCF7_Options::humanize( $parts[2] )
				: self::language_label( $lang );

			$tracks[] = array(
				'lang'  => $lang,
				'url'   => $url,
				'label' => sanitize_text_field( $label ),
			);
		}

		return $tracks;
	}

	/**
	 * Human readable label for common language codes.
	 *
	 * @param string $code Language code.
	 * @return string
	 */
	private static function language_label( $code ) {
		$map = array(
			'en' => 'English',
			'es' => 'Español',
			'fr' => 'Français',
			'de' => 'Deutsch',
			'it' => 'Italiano',
			'pt' => 'Português',
			'nl' => 'Nederlands',
			'ru' => 'Русский',
			'ja' => '日本語',
			'zh' => '中文',
			'ar' => 'العربية',
			'hi' => 'हिन्दी',
			'bn' => 'বাংলা',
			'tr' => 'Türkçe',
			'ko' => '한국어',
			'pl' => 'Polski',
			'sv' => 'Svenska',
		);

		$base = strtolower( substr( $code, 0, 2 ) );

		return isset( $map[ $base ] ) ? $map[ $base ] : strtoupper( $code );
	}

	/**
	 * Guesses the MIME type of a media URL from its extension.
	 *
	 * @param string $url        URL.
	 * @param string $media_type video|audio.
	 * @return string
	 */
	private static function mime_type( $url, $media_type ) {
		$path = wp_parse_url( $url, PHP_URL_PATH );
		$ext  = $path ? strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) : '';

		$types = array(
			'mp4'  => 'video/mp4',
			'm4v'  => 'video/mp4',
			'mov'  => 'video/mp4',
			'webm' => 'video/webm',
			'ogv'  => 'video/ogg',
			'mp3'  => 'audio/mpeg',
			'm4a'  => 'audio/mp4',
			'aac'  => 'audio/aac',
			'oga'  => 'audio/ogg',
			'wav'  => 'audio/wav',
			'flac' => 'audio/flac',
			'ogg'  => ( 'audio' === $media_type ) ? 'audio/ogg' : 'video/ogg',
		);

		if ( isset( $types[ $ext ] ) ) {
			return $types[ $ext ];
		}

		return '';
	}

	/**
	 * Validates a URL (http/https only).
	 *
	 * @param string $raw Raw URL.
	 * @return string Sanitised URL or empty string.
	 */
	private static function sanitize_url( $raw ) {
		$url = esc_url_raw( trim( (string) $raw ), array( 'http', 'https' ) );
		return $url ? $url : '';
	}

	/**
	 * Shows an inline notice to users who can edit forms; nothing to visitors.
	 *
	 * @param string $message Message.
	 * @return string
	 */
	private static function editor_notice( $message ) {
		if ( ! current_user_can( 'wpcf7_edit_contact_forms' ) ) {
			return '';
		}

		return sprintf( '<p class="bmfcf7-editor-notice">%s</p>', esc_html( $message ) );
	}
}
