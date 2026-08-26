<?php
/**
 * Registry of [gallery] form-tag options.
 *
 * Field keys mirror the other field registries:
 *  - type    flag | number | text | token | url | select | color | list
 *  - key     option name used inside the form-tag
 *
 * @package BMediaFieldsCF7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Static registry for the image gallery field.
 */
final class BMFCF7_Gallery_Options {

	/**
	 * Available layouts.
	 *
	 * @return array
	 */
	public static function layouts() {
		return array(
			''          => __( 'Grid (default)', 'b-media-fields-for-cf7' ),
			'masonry'   => __( 'Masonry', 'b-media-fields-for-cf7' ),
			'carousel'  => __( 'Carousel / slider', 'b-media-fields-for-cf7' ),
			'justified' => __( 'Justified rows', 'b-media-fields-for-cf7' ),
		);
	}

	/**
	 * Aspect ratios.
	 *
	 * @return array
	 */
	public static function ratios() {
		return array(
			''     => __( 'Original image ratio', 'b-media-fields-for-cf7' ),
			'1:1'  => '1:1',
			'4:3'  => '4:3',
			'3:2'  => '3:2',
			'16:9' => '16:9',
			'3:4'  => '3:4 (' . __( 'portrait', 'b-media-fields-for-cf7' ) . ')',
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
				'label' => __( 'Layout', 'b-media-fields-for-cf7' ),
				'desc'  => '',
			),
			'behaviour' => array(
				'label' => __( 'Captions & lightbox', 'b-media-fields-for-cf7' ),
				'desc'  => '',
			),
			'carousel'  => array(
				'label' => __( 'Carousel options', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'Only applied when the layout is set to Carousel.', 'b-media-fields-for-cf7' ),
			),
			'advanced'  => array(
				'label' => __( 'Advanced', 'b-media-fields-for-cf7' ),
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
			'layout'      => array(
				'group'   => 'layout',
				'type'    => 'select',
				'label'   => __( 'Layout', 'b-media-fields-for-cf7' ),
				'choices' => self::layouts(),
			),
			'columns'     => array(
				'group' => 'layout',
				'type'  => 'number',
				'label' => __( 'Columns', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'Columns on desktop; the grid halves on tablet and drops to one column on phones.', 'b-media-fields-for-cf7' ),
				'min'   => 1,
				'max'   => 8,
			),
			'gap'         => array(
				'group' => 'layout',
				'type'  => 'number',
				'label' => __( 'Gap between images (px)', 'b-media-fields-for-cf7' ),
				'min'   => 0,
				'max'   => 80,
			),
			'ratio'       => array(
				'group'   => 'layout',
				'type'    => 'select',
				'label'   => __( 'Thumbnail aspect ratio', 'b-media-fields-for-cf7' ),
				'choices' => self::ratios(),
			),
			'height'      => array(
				'group' => 'layout',
				'type'  => 'number',
				'label' => __( 'Row height for justified / carousel (px)', 'b-media-fields-for-cf7' ),
				'min'   => 80,
				'max'   => 900,
			),
			'width'       => array(
				'group' => 'layout',
				'type'  => 'number',
				'label' => __( 'Max width (px)', 'b-media-fields-for-cf7' ),
				'min'   => 100,
				'max'   => 4000,
			),
			'align'       => array(
				'group'   => 'layout',
				'type'    => 'select',
				'label'   => __( 'Alignment', 'b-media-fields-for-cf7' ),
				'choices' => array(
					''       => __( 'Default (left)', 'b-media-fields-for-cf7' ),
					'center' => __( 'Center', 'b-media-fields-for-cf7' ),
					'right'  => __( 'Right', 'b-media-fields-for-cf7' ),
				),
			),
			'contain'     => array(
				'group' => 'layout',
				'type'  => 'flag',
				'label' => __( 'Fit whole image inside the thumbnail instead of cropping', 'b-media-fields-for-cf7' ),
			),

			/* ---------------- Captions / lightbox ---------------- */
			'captions'    => array(
				'group' => 'behaviour',
				'type'  => 'flag',
				'label' => __( 'Show captions under the images', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'Add a caption to an image with a pipe: "https://example.com/a.jpg|My caption".', 'b-media-fields-for-cf7' ),
			),
			'no-lightbox' => array(
				'group' => 'behaviour',
				'type'  => 'flag',
				'label' => __( 'Disable the lightbox (images are not clickable)', 'b-media-fields-for-cf7' ),
			),
			'no-counter'  => array(
				'group' => 'behaviour',
				'type'  => 'flag',
				'label' => __( 'Hide the "3 / 12" counter in the lightbox', 'b-media-fields-for-cf7' ),
			),
			'eager'       => array(
				'group' => 'behaviour',
				'type'  => 'flag',
				'label' => __( 'Load every image immediately (disables lazy loading)', 'b-media-fields-for-cf7' ),
			),

			/* ---------------- Carousel ---------------- */
			'autoplay'    => array(
				'group' => 'carousel',
				'type'  => 'flag',
				'label' => __( 'Autoplay', 'b-media-fields-for-cf7' ),
			),
			'interval'    => array(
				'group' => 'carousel',
				'type'  => 'number',
				'label' => __( 'Autoplay interval (seconds)', 'b-media-fields-for-cf7' ),
				'min'   => 1,
				'max'   => 60,
			),
			'no-arrows'   => array(
				'group' => 'carousel',
				'type'  => 'flag',
				'label' => __( 'Hide the previous / next arrows', 'b-media-fields-for-cf7' ),
			),
			'no-dots'     => array(
				'group' => 'carousel',
				'type'  => 'flag',
				'label' => __( 'Hide the pagination dots', 'b-media-fields-for-cf7' ),
			),

			/* ---------------- Advanced ---------------- */
			'link-full'   => array(
				'group' => 'advanced',
				'type'  => 'flag',
				'label' => __( 'Open the image URL in a new tab instead of the lightbox', 'b-media-fields-for-cf7' ),
			),
		);

		/**
		 * Filters the registry of [gallery] options.
		 *
		 * @param array $fields Field definitions keyed by option name.
		 */
		$fields = apply_filters( 'bmfcf7_gallery_option_fields', $fields );

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
