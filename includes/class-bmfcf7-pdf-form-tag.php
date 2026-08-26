<?php
/**
 * [pdf_flipbook] form-tag — PDF viewer with page-turn or scroll mode.
 *
 * Syntax:
 *   [pdf_flipbook name option:value flag ... "https://example.com/brochure.pdf"] Title [/pdf_flipbook]
 *
 * @package BMediaFieldsCF7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders the PDF flipbook form-tag.
 */
final class BMFCF7_Pdf_Form_Tag {

	const TAG = 'pdf_flipbook';

	/**
	 * Registers hooks.
	 */
	public static function init() {
		add_action( 'wpcf7_init', array( __CLASS__, 'register' ), 10, 0 );
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
	 * Renders the form-tag.
	 *
	 * @param WPCF7_FormTag $tag Form-tag.
	 * @return string
	 */
	public static function handler( $tag ) {
		if ( ! BMFCF7_Settings::is_enabled( self::TAG ) ) {
			return self::editor_notice(
				__( 'The PDF flipbook field is disabled. Enable it under Contact → Media Fields.', 'b-media-fields-for-cf7' )
			);
		}

		$src = '';
		foreach ( (array) $tag->raw_values as $value ) {
			$url = esc_url_raw( trim( (string) $value ), array( 'http', 'https' ) );
			if ( $url ) {
				$src = $url;
				break;
			}
		}

		if ( ! $src ) {
			return self::editor_notice(
				sprintf(
					/* translators: %s: example form-tag */
					__( 'The PDF flipbook form-tag has no file. Add a quoted .pdf URL, e.g. %s', 'b-media-fields-for-cf7' ),
					'[' . self::TAG . ' ' . $tag->name . ' "https://example.com/brochure.pdf"]'
				)
			);
		}

		$defaults = BMFCF7_Settings::section( self::TAG );
		$opt      = self::collect_options( $tag );

		$height = isset( $opt['height'] ) ? (int) $opt['height'] : (int) $defaults['height'];
		$height = $height > 0 ? $height : 520;

		$mode = ! empty( $opt['mode'] ) ? $opt['mode'] : ( ! empty( $defaults['mode'] ) ? $defaults['mode'] : 'flip' );
		$mode = ( 'scroll' === $mode ) ? 'scroll' : 'flip';

		$show_toolbar = empty( $opt['no-toolbar'] ) && ! empty( $defaults['toolbar'] );
		if ( ! empty( $opt['no-toolbar'] ) ) {
			$show_toolbar = false;
		}

		$config = array(
			'src'        => $src,
			'mode'       => $mode,
			'startPage'  => isset( $opt['start-page'] ) ? max( 1, (int) $opt['start-page'] ) : 1,
			'singlePage' => ! empty( $opt['single-page'] ),
			'flipTime'   => isset( $opt['flip-time'] ) ? (int) $opt['flip-time'] : (int) $defaults['flip_time'],
			'shadow'     => empty( $opt['no-shadow'] ),
			'eager'      => ! empty( $opt['eager'] ),
			'height'     => $height,
		);

		if ( $config['flipTime'] < 100 ) {
			$config['flipTime'] = 800;
		}

		/**
		 * Filters the PDF viewer config for a single tag.
		 *
		 * @param array         $config Viewer config.
		 * @param WPCF7_FormTag $tag    The form-tag.
		 */
		$config = apply_filters( 'bmfcf7_pdf_config', $config, $tag );

		// ---- wrapper ----------------------------------------------------
		$wrapper_class = array( 'bmfcf7-player-wrap', 'bmfcf7-pdf', 'bmfcf7-pdf--' . $mode );
		$class_option  = $tag->get_class_option();
		if ( $class_option ) {
			$wrapper_class = array_merge( $wrapper_class, explode( ' ', $class_option ) );
		}
		if ( ! empty( $opt['align'] ) ) {
			$wrapper_class[] = 'bmfcf7-align-' . $opt['align'];
		}

		$wrapper_atts = array(
			'class'     => implode( ' ', array_unique( array_filter( $wrapper_class ) ) ),
			'id'        => $tag->get_id_option(),
			'style'     => ! empty( $opt['width'] ) ? 'max-width:' . (int) $opt['width'] . 'px' : null,
			'data-name' => $tag->name,
		);

		$viewer_style = array( 'height:' . $height . 'px' );
		$bg           = ! empty( $opt['bg'] ) ? $opt['bg'] : ( ! empty( $defaults['background'] ) ? $defaults['background'] : '' );
		if ( $bg ) {
			$viewer_style[] = 'background-color:' . $bg;
		}

		$viewer_atts = array(
			'class'           => 'bmfcf7-pdf__viewer',
			'style'           => implode( ';', $viewer_style ),
			'data-bmfcf7-pdf' => wp_json_encode( $config ),
		);

		// ---- toolbar ----------------------------------------------------
		$toolbar = '';

		if ( $show_toolbar ) {
			$buttons = '';

			if ( empty( $opt['no-nav'] ) ) {
				$buttons .= self::button( 'prev', __( 'Previous page', 'b-media-fields-for-cf7' ), '&#8249;' );
				$buttons .= '<span class="bmfcf7-pdf__pages" data-role="pages" aria-live="polite"></span>';
				$buttons .= self::button( 'next', __( 'Next page', 'b-media-fields-for-cf7' ), '&#8250;' );
			}

			$buttons .= '<span class="bmfcf7-pdf__spacer"></span>';

			if ( empty( $opt['no-zoom'] ) ) {
				$buttons .= self::button( 'zoom-out', __( 'Zoom out', 'b-media-fields-for-cf7' ), '&minus;' );
				$buttons .= self::button( 'zoom-in', __( 'Zoom in', 'b-media-fields-for-cf7' ), '+' );
			}

			if ( empty( $opt['no-fullscreen'] ) ) {
				$buttons .= self::button( 'fullscreen', __( 'Fullscreen', 'b-media-fields-for-cf7' ), '&#9974;' );
			}

			if ( ! empty( $opt['download'] ) ) {
				$buttons .= sprintf(
					'<a class="bmfcf7-pdf__btn" href="%1$s" download target="_blank" rel="noopener" title="%2$s" aria-label="%2$s">&#8595;</a>',
					esc_url( $src ),
					esc_attr__( 'Download', 'b-media-fields-for-cf7' )
				);
			}

			$toolbar = sprintf( "\n\t\t<div class=\"bmfcf7-pdf__toolbar\">%s</div>", $buttons );
		}

		$title = trim( (string) $tag->content );

		BMFCF7_Assets::enqueue_pdf();

		return sprintf(
			"<div %s>\n\t<div %s>\n\t\t<div class=\"bmfcf7-pdf__stage\" data-role=\"stage\"></div>\n\t\t<div class=\"bmfcf7-pdf__status\" data-role=\"status\">%s</div>%s\n\t</div>\n</div>",
			wpcf7_format_atts( $wrapper_atts ),
			wpcf7_format_atts( $viewer_atts ),
			esc_html( $title ? $title : __( 'Loading document…', 'b-media-fields-for-cf7' ) ),
			$toolbar
		);
	}

	/**
	 * Builds one toolbar button.
	 *
	 * @param string $role  data-role value.
	 * @param string $label Accessible label.
	 * @param string $glyph Button glyph (already escaped entity).
	 * @return string
	 */
	private static function button( $role, $label, $glyph ) {
		return sprintf(
			'<button type="button" class="bmfcf7-pdf__btn" data-role="%1$s" title="%2$s" aria-label="%2$s">%3$s</button>',
			esc_attr( $role ),
			esc_attr( $label ),
			$glyph
		);
	}

	/**
	 * Reads the registry options from the tag.
	 *
	 * @param WPCF7_FormTag $tag Form-tag.
	 * @return array
	 */
	private static function collect_options( $tag ) {
		$out = array();

		foreach ( BMFCF7_Pdf_Options::fields() as $key => $field ) {
			$type = isset( $field['type'] ) ? $field['type'] : 'text';

			switch ( $type ) {
				case 'flag':
					if ( $tag->has_option( $key ) ) {
						$out[ $key ] = true;
					}
					break;

				case 'number':
					$raw = $tag->get_option( $key, 'int', true );
					if ( false !== $raw && '' !== $raw ) {
						$num = (int) $raw;
						if ( isset( $field['min'] ) ) {
							$num = max( (int) $field['min'], $num );
						}
						if ( isset( $field['max'] ) ) {
							$num = min( (int) $field['max'], $num );
						}
						$out[ $key ] = $num;
					}
					break;

				case 'select':
					$raw = $tag->get_option( $key, '', true );
					if ( false !== $raw && '' !== $raw && isset( $field['choices'][ $raw ] ) ) {
						$out[ $key ] = $raw;
					}
					break;

				case 'color':
					$raw = $tag->get_option( $key, '', true );
					if ( false !== $raw ) {
						$hex = sanitize_hex_color( '#' === substr( $raw, 0, 1 ) ? $raw : '#' . $raw );
						if ( $hex ) {
							$out[ $key ] = $hex;
						}
					}
					break;

				default:
					$raw = $tag->get_option( $key, '', true );
					if ( false !== $raw && '' !== $raw ) {
						$out[ $key ] = sanitize_text_field( $raw );
					}
					break;
			}
		}

		if ( isset( $out['align'] ) && ! in_array( $out['align'], array( 'center', 'right' ), true ) ) {
			unset( $out['align'] );
		}

		return $out;
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
