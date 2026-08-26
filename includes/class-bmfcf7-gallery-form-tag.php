<?php
/**
 * [gallery] form-tag — responsive image gallery with an optional lightbox.
 *
 * Syntax:
 *   [gallery name option:value flag ... "https://example.com/a.jpg|Caption" "https://example.com/b.jpg"]
 *
 * Each quoted value is one image; anything after a pipe is its caption
 * (captions may contain spaces because the whole value is quoted).
 *
 * @package BMediaFieldsCF7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders the gallery form-tag.
 */
final class BMFCF7_Gallery_Form_Tag {

	const TAG = 'gallery';

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
				__( 'The gallery field is disabled. Enable it under Contact → Media Fields.', 'b-media-fields-for-cf7' )
			);
		}

		$images = self::parse_images( $tag->raw_values );

		if ( empty( $images ) ) {
			return self::editor_notice(
				sprintf(
					/* translators: %s: example form-tag */
					__( 'The gallery form-tag has no images. Add one or more quoted image URLs, e.g. %s', 'b-media-fields-for-cf7' ),
					'[' . self::TAG . ' ' . $tag->name . ' "https://example.com/photo.jpg"]'
				)
			);
		}

		$defaults = BMFCF7_Settings::section( self::TAG );
		$opt      = self::collect_options( $tag );

		$layout = ! empty( $opt['layout'] ) ? $opt['layout'] : ( ! empty( $defaults['layout'] ) ? $defaults['layout'] : 'grid' );
		$layout = in_array( $layout, array( 'grid', 'masonry', 'carousel', 'justified' ), true ) ? $layout : 'grid';

		$columns = isset( $opt['columns'] ) ? (int) $opt['columns'] : (int) $defaults['columns'];
		$columns = max( 1, min( 8, $columns ? $columns : 3 ) );
		$gap     = isset( $opt['gap'] ) ? (int) $opt['gap'] : (int) $defaults['gap'];
		$gap     = max( 0, min( 80, $gap ) );
		$ratio   = ! empty( $opt['ratio'] ) ? $opt['ratio'] : ( ! empty( $defaults['ratio'] ) ? $defaults['ratio'] : '' );
		$height  = isset( $opt['height'] ) ? (int) $opt['height'] : (int) $defaults['height'];
		$height  = $height > 0 ? $height : 240;

		$lightbox = empty( $opt['no-lightbox'] ) && empty( $opt['link-full'] ) && ! empty( $defaults['lightbox'] );
		if ( ! empty( $opt['no-lightbox'] ) || ! empty( $opt['link-full'] ) ) {
			$lightbox = false;
		}

		$captions = ! empty( $opt['captions'] ) || ( ! isset( $opt['captions'] ) && ! empty( $defaults['captions'] ) );
		$contain  = ! empty( $opt['contain'] );
		$loading  = ! empty( $opt['eager'] ) ? 'eager' : 'lazy';

		// ---- wrapper ----------------------------------------------------
		$wrapper_class = array( 'bmfcf7-player-wrap', 'bmfcf7-gallery', 'bmfcf7-gallery--' . $layout );
		$class_option  = $tag->get_class_option();
		if ( $class_option ) {
			$wrapper_class = array_merge( $wrapper_class, explode( ' ', $class_option ) );
		}
		if ( ! empty( $opt['align'] ) ) {
			$wrapper_class[] = 'bmfcf7-align-' . $opt['align'];
		}
		if ( $contain ) {
			$wrapper_class[] = 'is-contain';
		}
		if ( $ratio ) {
			$wrapper_class[] = 'has-ratio';
		}

		$style = array(
			'--bmfcf7-cols:' . $columns,
			'--bmfcf7-gap:' . $gap . 'px',
		);
		if ( $ratio ) {
			$style[] = '--bmfcf7-ratio:' . str_replace( ':', '/', $ratio );
		}
		if ( in_array( $layout, array( 'justified', 'carousel' ), true ) ) {
			$style[] = '--bmfcf7-row-height:' . $height . 'px';
		}
		if ( ! empty( $opt['width'] ) ) {
			$style[] = 'max-width:' . (int) $opt['width'] . 'px';
		}

		$wrapper_atts = array(
			'class'     => implode( ' ', array_unique( array_filter( $wrapper_class ) ) ),
			'id'        => $tag->get_id_option(),
			'style'     => implode( ';', $style ),
			'data-name' => $tag->name,
		);

		if ( 'carousel' === $layout ) {
			$wrapper_atts['data-autoplay'] = ! empty( $opt['autoplay'] ) ? 'true' : null;
			$wrapper_atts['data-interval'] = ! empty( $opt['interval'] ) ? (string) (int) $opt['interval'] : null;
		}

		if ( $lightbox ) {
			$wrapper_atts['data-lightbox'] = 'true';
			$wrapper_atts['data-counter']  = empty( $opt['no-counter'] ) ? 'true' : null;
		}

		// ---- items ------------------------------------------------------
		$items = '';

		foreach ( $images as $index => $image ) {
			$alt = $image['caption'] ? $image['caption'] : sprintf(
				/* translators: %d: image number */
				__( 'Gallery image %d', 'b-media-fields-for-cf7' ),
				$index + 1
			);

			$img = sprintf(
				'<img %s />',
				wpcf7_format_atts(
					array(
						'src'      => $image['url'],
						'alt'      => $alt,
						'loading'  => $loading,
						'decoding' => 'async',
						'class'    => 'bmfcf7-gallery__img',
					)
				)
			);

			if ( $lightbox || ! empty( $opt['link-full'] ) ) {
				$link_atts = array(
					'href'  => $image['url'],
					'class' => 'bmfcf7-gallery__link',
				);

				if ( $lightbox ) {
					$link_atts['data-bmfcf7-lightbox'] = $tag->name;
					$link_atts['data-caption']         = $image['caption'] ? $image['caption'] : null;
				} else {
					$link_atts['target'] = '_blank';
					$link_atts['rel']    = 'noopener';
				}

				$img = sprintf( '<a %s>%s</a>', wpcf7_format_atts( $link_atts ), $img );
			}

			$figcaption = ( $captions && $image['caption'] )
				? sprintf( '<figcaption class="bmfcf7-gallery__caption">%s</figcaption>', esc_html( $image['caption'] ) )
				: '';

			$items .= sprintf(
				"\n\t\t<figure class=\"bmfcf7-gallery__item\">%s%s</figure>",
				$img,
				$figcaption
			);
		}

		// ---- carousel chrome --------------------------------------------
		$chrome = '';

		if ( 'carousel' === $layout ) {
			if ( empty( $opt['no-arrows'] ) ) {
				$chrome .= sprintf(
					"\n\t<button type=\"button\" class=\"bmfcf7-gallery__nav is-prev\" data-bmfcf7-slide=\"prev\" aria-label=\"%s\"></button>",
					esc_attr__( 'Previous image', 'b-media-fields-for-cf7' )
				);
				$chrome .= sprintf(
					"\n\t<button type=\"button\" class=\"bmfcf7-gallery__nav is-next\" data-bmfcf7-slide=\"next\" aria-label=\"%s\"></button>",
					esc_attr__( 'Next image', 'b-media-fields-for-cf7' )
				);
			}

			if ( empty( $opt['no-dots'] ) ) {
				$dots = '';
				foreach ( array_keys( $images ) as $index ) {
					$dots .= sprintf(
						'<button type="button" class="bmfcf7-gallery__dot" data-bmfcf7-dot="%1$d" aria-label="%2$s"></button>',
						(int) $index,
						esc_attr(
							sprintf(
								/* translators: %d: image number */
								__( 'Go to image %d', 'b-media-fields-for-cf7' ),
								$index + 1
							)
						)
					);
				}
				$chrome .= sprintf( "\n\t<div class=\"bmfcf7-gallery__dots\">%s</div>", $dots );
			}
		}

		BMFCF7_Assets::enqueue_gallery();

		return sprintf(
			"<div %s>\n\t<div class=\"bmfcf7-gallery__track\">%s\n\t</div>%s\n</div>",
			wpcf7_format_atts( $wrapper_atts ),
			$items,
			$chrome
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

		foreach ( BMFCF7_Gallery_Options::fields() as $key => $field ) {
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

				default:
					$raw = $tag->get_option( $key, '', true );
					if ( false !== $raw && '' !== $raw ) {
						$out[ $key ] = sanitize_text_field( $raw );
					}
					break;
			}
		}

		// "align" is a select but its value is also used as a CSS class.
		if ( isset( $out['align'] ) && ! in_array( $out['align'], array( 'center', 'right' ), true ) ) {
			unset( $out['align'] );
		}

		return $out;
	}

	/**
	 * Parses quoted values into image URL + caption pairs.
	 *
	 * @param array $values Raw form-tag values.
	 * @return array[]
	 */
	private static function parse_images( $values ) {
		$images = array();

		foreach ( (array) $values as $value ) {
			$value = trim( (string) $value );
			if ( '' === $value ) {
				continue;
			}

			$caption = '';
			if ( false !== strpos( $value, '|' ) ) {
				list( $value, $caption ) = array_pad( explode( '|', $value, 2 ), 2, '' );
			}

			$url = esc_url_raw( trim( $value ), array( 'http', 'https' ) );
			if ( ! $url ) {
				continue;
			}

			$images[] = array(
				'url'     => $url,
				'caption' => sanitize_text_field( trim( $caption ) ),
			);
		}

		return $images;
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
