<?php
/**
 * [3d_models] form-tag – renders Google's <model-viewer>.
 *
 * Syntax:
 *   [3d_models name option:value flag ... "https://example.com/model.glb" "https://example.com/model.usdz"] Accessible title [/3d_models]
 *
 * The first quoted value is the glTF/GLB source; an optional .usdz value is
 * used for iOS Quick Look (or use ios-src:).
 *
 * @package BMediaFieldsCF7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders the 3D model form-tag.
 */
final class BMFCF7_Model_Form_Tag {

	const TAG = '3d_models';

	/**
	 * Registers hooks.
	 */
	public static function init() {
		add_action( 'wpcf7_init', array( __CLASS__, 'register' ), 10, 0 );
		add_filter( 'upload_mimes', array( __CLASS__, 'upload_mimes' ) );
		add_filter( 'wp_check_filetype_and_ext', array( __CLASS__, 'check_filetype' ), 10, 4 );
	}

	/**
	 * Registers the form-tag with Contact Form 7.
	 */
	public static function register() {
		wpcf7_add_form_tag(
			self::TAG,
			array( __CLASS__, 'handler' ),
			array(
				'name-attr'     => true,
				'display-block' => true,
				'not-for-mail'  => true,
			)
		);
	}

	/**
	 * MIME types handled by the viewer.
	 *
	 * @return array ext => mime
	 */
	public static function mime_types() {
		return array(
			'glb'  => 'model/gltf-binary',
			'gltf' => 'model/gltf+json',
			'usdz' => 'model/vnd.usdz+zip',
			'hdr'  => 'image/vnd.radiance',
		);
	}

	/**
	 * Allows 3D model uploads while the field is enabled.
	 *
	 * @param array $mimes Allowed mimes.
	 * @return array
	 */
	public static function upload_mimes( $mimes ) {
		if ( ! BMFCF7_Settings::is_enabled( self::TAG ) || ! current_user_can( 'upload_files' ) ) {
			return $mimes;
		}

		return array_merge( $mimes, self::mime_types() );
	}

	/**
	 * Lets WordPress accept the model file types it cannot sniff.
	 *
	 * @param array  $data     Values for ext, type, proper_filename.
	 * @param string $file     Full path.
	 * @param string $filename File name.
	 * @param array  $mimes    Allowed mimes.
	 * @return array
	 */
	public static function check_filetype( $data, $file, $filename, $mimes ) {
		if ( ! BMFCF7_Settings::is_enabled( self::TAG ) || ! current_user_can( 'upload_files' ) ) {
			return $data;
		}

		$ext   = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
		$types = self::mime_types();

		if ( isset( $types[ $ext ] ) && empty( $data['type'] ) ) {
			$data['ext']  = $ext;
			$data['type'] = $types[ $ext ];
		}

		return $data;
	}

	/**
	 * Renders the form-tag.
	 *
	 * @param WPCF7_FormTag $tag Form-tag.
	 * @return string
	 */
	public static function handler( $tag ) {
		if ( ! BMFCF7_Settings::is_enabled( self::TAG ) ) {
			return self::editor_notice(
				__( 'The 3D model field is disabled. Enable it under Contact → Media Fields.', 'b-media-fields-for-cf7' )
			);
		}

		$src     = '';
		$ios_src = '';

		foreach ( (array) $tag->raw_values as $value ) {
			$url = self::sanitize_url( $value );
			if ( ! $url ) {
				continue;
			}
			$ext = strtolower( pathinfo( (string) wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
			if ( 'usdz' === $ext ) {
				$ios_src = $ios_src ? $ios_src : $url;
			} elseif ( ! $src ) {
				$src = $url;
			}
		}

		if ( ! $src ) {
			return self::editor_notice(
				sprintf(
					/* translators: %s: example form-tag */
					__( 'The 3D model form-tag has no source. Add a quoted .glb / .gltf URL, e.g. %s', 'b-media-fields-for-cf7' ),
					'[' . self::TAG . ' ' . $tag->name . ' "https://example.com/model.glb"]'
				)
			);
		}

		$defaults = BMFCF7_Settings::section( self::TAG );
		$opts     = self::collect_options( $tag );

		// ---- Attributes -------------------------------------------------
		$atts = array(
			'class' => 'bmfcf7-model-viewer',
			'src'   => $src,
			'alt'   => '',
		);
		$css  = array();

		$title       = trim( (string) $tag->content );
		$atts['alt'] = '' !== $title ? $title : __( 'Interactive 3D model', 'b-media-fields-for-cf7' );

		if ( $ios_src && empty( $opts['attrs']['ios-src'] ) ) {
			$atts['ios-src'] = $ios_src;
		}

		// Global defaults (only when the tag does not say otherwise).
		if ( empty( $opts['internal']['_no_camera_controls'] ) && ! empty( $defaults['camera_controls'] ) ) {
			$atts['camera-controls'] = true;
		}
		if ( empty( $opts['internal']['_no_auto_rotate'] ) && ! empty( $defaults['auto_rotate'] ) ) {
			$atts['auto-rotate'] = true;
		}
		if ( empty( $opts['internal']['_no_ar'] ) && ! empty( $defaults['ar'] ) ) {
			$atts['ar'] = true;
		}
		if ( ! empty( $defaults['environment'] ) ) {
			$atts['environment-image'] = $defaults['environment'];
		}
		if ( isset( $defaults['exposure'] ) && '' !== $defaults['exposure'] && 1 != $defaults['exposure'] ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual
			$atts['exposure'] = (string) $defaults['exposure'];
		}
		if ( ! empty( $defaults['shadow_intensity'] ) ) {
			$atts['shadow-intensity'] = (string) $defaults['shadow_intensity'];
		}
		if ( ! empty( $defaults['tone_mapping'] ) ) {
			$atts['tone-mapping'] = $defaults['tone_mapping'];
		}
		if ( isset( $defaults['interaction_prompt'] ) && empty( $defaults['interaction_prompt'] ) ) {
			$atts['interaction-prompt'] = 'none';
		}
		if ( ! empty( $defaults['loading'] ) && 'auto' !== $defaults['loading'] ) {
			$atts['loading'] = $defaults['loading'];
		}
		if ( ! empty( $defaults['poster_color'] ) ) {
			$css[] = '--poster-color:' . $defaults['poster_color'];
		}
		if ( ! empty( $defaults['progress_color'] ) ) {
			$css[] = '--progress-bar-color:' . $defaults['progress_color'];
		}

		// Tag attributes override defaults.
		foreach ( $opts['attrs'] as $name => $value ) {
			$atts[ $name ] = $value;
		}
		if ( ! empty( $opts['internal']['_no_camera_controls'] ) ) {
			unset( $atts['camera-controls'] );
		}
		if ( ! empty( $opts['internal']['_no_auto_rotate'] ) ) {
			unset( $atts['auto-rotate'] );
		}
		if ( ! empty( $opts['internal']['_no_ar'] ) ) {
			unset( $atts['ar'] );
		}

		foreach ( $opts['css'] as $prop => $value ) {
			$css[] = $prop . ':' . $value;
		}

		$height = ! empty( $opts['internal']['_height'] ) ? (int) $opts['internal']['_height'] : (int) $defaults['height'];
		$height = $height > 0 ? $height : 400;
		$css[]  = 'height:' . $height . 'px';

		$bg = ! empty( $opts['internal']['_bg'] ) ? $opts['internal']['_bg'] : ( ! empty( $defaults['background'] ) ? $defaults['background'] : '' );
		if ( $bg ) {
			$css[] = 'background-color:' . $bg;
		}

		$atts['style'] = implode( ';', $css );

		/**
		 * Filters the <model-viewer> attributes for a single tag.
		 *
		 * @param array         $atts Attributes.
		 * @param WPCF7_FormTag $tag  Form-tag.
		 */
		$atts = apply_filters( 'bmfcf7_model_viewer_atts', $atts, $tag );

		// ---- Children ---------------------------------------------------
		$children = '';

		if ( ! empty( $opts['internal']['_ar_button'] ) && ! empty( $atts['ar'] ) ) {
			$children .= sprintf(
				"\n\t\t<button type=\"button\" slot=\"ar-button\" class=\"bmfcf7-ar-button\">%s</button>",
				esc_html( $opts['internal']['_ar_button'] )
			);
		}

		if ( ! empty( $opts['internal']['_no_progress'] ) ) {
			$children .= "\n\t\t<div slot=\"progress-bar\"></div>";
		}

		if ( ! empty( $opts['internal']['_hotspots'] ) ) {
			$i = 0;
			foreach ( $opts['internal']['_hotspots'] as $hotspot ) {
				++$i;
				$h_atts    = array(
					'type'                      => 'button',
					'class'                     => 'bmfcf7-hotspot',
					'slot'                      => 'hotspot-' . $i,
					'data-position'             => $hotspot['position'],
					'data-normal'               => $hotspot['normal'] ? $hotspot['normal'] : null,
					'data-visibility-attribute' => 'visible',
					'aria-label'                => $hotspot['label'] ? $hotspot['label'] : sprintf(
						/* translators: %d: hotspot number */
						__( 'Hotspot %d', 'b-media-fields-for-cf7' ),
						$i
					),
				);
				$children .= sprintf(
					"\n\t\t<button %s>%s</button>",
					wpcf7_format_atts( $h_atts ),
					$hotspot['label'] ? '<span class="bmfcf7-hotspot__label">' . esc_html( $hotspot['label'] ) . '</span>' : ''
				);
			}
		}

		// ---- Wrapper ----------------------------------------------------
		$wrapper_class = array( 'bmfcf7-player-wrap', 'bmfcf7-model' );
		$class_option  = $tag->get_class_option();
		if ( $class_option ) {
			$wrapper_class = array_merge( $wrapper_class, explode( ' ', $class_option ) );
		}
		if ( ! empty( $opts['internal']['_align'] ) ) {
			$wrapper_class[] = 'bmfcf7-align-' . $opts['internal']['_align'];
		}

		$wrapper_atts = array(
			'class'     => implode( ' ', array_unique( array_filter( $wrapper_class ) ) ),
			'id'        => $tag->get_id_option(),
			'style'     => ! empty( $opts['internal']['_width'] ) ? 'max-width:' . (int) $opts['internal']['_width'] . 'px' : null,
			'data-name' => $tag->name,
		);

		BMFCF7_Assets::enqueue_model_viewer();

		return sprintf(
			"<div %s>\n\t<model-viewer %s>%s\n\t</model-viewer>\n</div>",
			wpcf7_format_atts( $wrapper_atts ),
			wpcf7_format_atts( $atts ),
			$children
		);
	}

	/**
	 * Reads every registry option from the tag.
	 *
	 * @param WPCF7_FormTag $tag Form-tag.
	 * @return array{attrs:array,css:array,internal:array}
	 */
	private static function collect_options( $tag ) {
		$out = array(
			'attrs'    => array(),
			'css'      => array(),
			'internal' => array(),
		);

		$set = static function ( $field, $value ) use ( &$out ) {
			if ( ! empty( $field['css'] ) ) {
				$out['css'][ $field['css'] ] = $value;
			} elseif ( ! empty( $field['attr'] ) && 0 === strpos( $field['attr'], '_' ) ) {
				$out['internal'][ $field['attr'] ] = $value;
			} elseif ( ! empty( $field['attr'] ) ) {
				$out['attrs'][ $field['attr'] ] = $value;
			}
		};

		foreach ( BMFCF7_Model_Options::fields() as $key => $field ) {
			$type = isset( $field['type'] ) ? $field['type'] : 'text';

			switch ( $type ) {
				case 'flag':
					if ( $tag->has_option( $key ) ) {
						$set( $field, isset( $field['value'] ) ? $field['value'] : true );
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
						$str = ( floor( $num ) === $num ) ? (string) (int) $num : (string) $num;
						$set( $field, $str . ( isset( $field['unit'] ) ? $field['unit'] : '' ) );
					}
					break;

				case 'select':
					$raw = $tag->get_option( $key, '', true );
					if ( false !== $raw && '' !== $raw && isset( $field['choices'][ $raw ] ) ) {
						$set( $field, $raw );
					}
					break;

				case 'color':
					$raw = $tag->get_option( $key, '', true );
					if ( false !== $raw ) {
						$hex = sanitize_hex_color( '#' === substr( $raw, 0, 1 ) ? $raw : '#' . $raw );
						if ( $hex ) {
							$set( $field, $hex );
						}
					}
					break;

				case 'url':
					$raw = $tag->get_option( $key, '', true );
					if ( false !== $raw ) {
						$url = self::sanitize_url( $raw );
						if ( $url ) {
							$set( $field, $url );
						}
					}
					break;

				case 'token':
					$raw = $tag->get_option( $key, '', true );
					if ( false !== $raw && '' !== $raw ) {
						// environment-image accepts keywords or a URL.
						if ( 'environment' === $key && ! in_array( $raw, array( 'neutral', 'legacy' ), true ) ) {
							$url = self::sanitize_url( $raw );
							if ( $url ) {
								$set( $field, $url );
							}
						} else {
							$set( $field, self::sanitize_token( $raw ) );
						}
					}
					break;

				case 'text':
					$raw = $tag->get_option( $key, '', true );
					if ( false !== $raw && '' !== $raw ) {
						$set( $field, sanitize_text_field( str_replace( '_', ' ', $raw ) ) );
					}
					break;

				case 'multi':
					$raw = $tag->get_option( $key );
					if ( false === $raw ) {
						break;
					}
					$items = array();
					foreach ( (array) $raw as $chunk ) {
						foreach ( explode( '|', $chunk ) as $item ) {
							$item = trim( $item );
							if ( isset( $field['choices'][ $item ] ) ) {
								$items[] = $item;
							}
						}
					}
					if ( $items ) {
						$set( $field, implode( isset( $field['join'] ) ? $field['join'] : ' ', array_unique( $items ) ) );
					}
					break;

				case 'list':
					$raw = $tag->get_option( $key );
					if ( false === $raw ) {
						break;
					}
					if ( 'hotspot' === $key ) {
						$set( $field, self::parse_hotspots( (array) $raw ) );
						break;
					}
					// Vector values: "a|b|c" -> "a b c" (first occurrence wins).
					$parts = array();
					foreach ( explode( '|', (string) reset( $raw ) ) as $part ) {
						$part = self::sanitize_token( $part );
						if ( '' !== $part ) {
							$parts[] = $part;
						}
					}
					if ( $parts ) {
						$set( $field, implode( ' ', $parts ) );
					}
					break;
			}
		}

		return $out;
	}

	/**
	 * Parses hotspot definitions "x|y|z|Label|nx|ny|nz".
	 *
	 * @param array $raw Raw option values.
	 * @return array[]
	 */
	private static function parse_hotspots( array $raw ) {
		$hotspots = array();

		foreach ( $raw as $item ) {
			$parts = explode( '|', (string) $item );
			if ( count( $parts ) < 3 ) {
				continue;
			}

			$pos = array();
			for ( $i = 0; $i < 3; $i++ ) {
				$pos[] = self::sanitize_length( $parts[ $i ] );
			}
			if ( in_array( '', $pos, true ) ) {
				continue;
			}

			$label  = isset( $parts[3] ) ? sanitize_text_field( str_replace( '_', ' ', $parts[3] ) ) : '';
			$normal = '';

			if ( isset( $parts[6] ) ) {
				$n = array(
					self::sanitize_length( $parts[4] ),
					self::sanitize_length( $parts[5] ),
					self::sanitize_length( $parts[6] ),
				);
				if ( ! in_array( '', $n, true ) ) {
					$normal = implode( ' ', $n );
				}
			}

			$hotspots[] = array(
				'position' => implode( ' ', $pos ),
				'label'    => $label,
				'normal'   => $normal,
			);
		}

		return $hotspots;
	}

	/**
	 * Keeps a numeric value with an optional unit (m, cm, mm, deg, rad, %).
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	private static function sanitize_length( $value ) {
		$value = trim( (string) $value );
		return preg_match( '/^-?(?:\d+|\d*\.\d+)(?:m|cm|mm|deg|rad|%)?$/', $value ) ? $value : '';
	}

	/**
	 * Restricts a token to characters valid in model-viewer values.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	private static function sanitize_token( $value ) {
		return preg_replace( '/[^-0-9a-zA-Z.%]/', '', (string) $value );
	}

	/**
	 * Validates a URL (http/https only).
	 *
	 * @param string $raw Raw URL.
	 * @return string
	 */
	private static function sanitize_url( $raw ) {
		$url = esc_url_raw( trim( (string) $raw ), array( 'http', 'https' ) );
		return $url ? $url : '';
	}

	/**
	 * Inline notice for editors; nothing for visitors.
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
