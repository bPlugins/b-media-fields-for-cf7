<?php
/**
 * Registry of [pdf_flipbook] form-tag options.
 *
 * @package BMediaFieldsCF7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Static registry for the PDF flipbook field.
 */
final class BMFCF7_Pdf_Options {

	/**
	 * Option groups shown in the tag generator.
	 *
	 * @return array
	 */
	public static function groups() {
		return array(
			'layout'  => array(
				'label' => __( 'Layout', 'b-media-fields-for-cf7' ),
				'desc'  => '',
			),
			'viewer'  => array(
				'label' => __( 'Viewer', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'Flip mode turns pages like a book; scroll mode stacks the pages vertically.', 'b-media-fields-for-cf7' ),
			),
			'toolbar' => array(
				'label' => __( 'Toolbar', 'b-media-fields-for-cf7' ),
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
			'height'        => array(
				'group' => 'layout',
				'type'  => 'number',
				'label' => __( 'Height (px)', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'Leave empty to use the default from the settings page.', 'b-media-fields-for-cf7' ),
				'min'   => 200,
				'max'   => 2000,
			),
			'width'         => array(
				'group' => 'layout',
				'type'  => 'number',
				'label' => __( 'Max width (px)', 'b-media-fields-for-cf7' ),
				'min'   => 200,
				'max'   => 4000,
			),
			'align'         => array(
				'group'   => 'layout',
				'type'    => 'select',
				'label'   => __( 'Alignment', 'b-media-fields-for-cf7' ),
				'choices' => array(
					''       => __( 'Default (left)', 'b-media-fields-for-cf7' ),
					'center' => __( 'Center', 'b-media-fields-for-cf7' ),
					'right'  => __( 'Right', 'b-media-fields-for-cf7' ),
				),
			),
			'bg'            => array(
				'group' => 'layout',
				'type'  => 'color',
				'label' => __( 'Background colour', 'b-media-fields-for-cf7' ),
			),

			/* ---------------- Viewer ---------------- */
			'mode'          => array(
				'group'   => 'viewer',
				'type'    => 'select',
				'label'   => __( 'Mode', 'b-media-fields-for-cf7' ),
				'choices' => array(
					''       => __( 'Flipbook (page turn)', 'b-media-fields-for-cf7' ),
					'scroll' => __( 'Scroll (stacked pages)', 'b-media-fields-for-cf7' ),
				),
			),
			'start-page'    => array(
				'group' => 'viewer',
				'type'  => 'number',
				'label' => __( 'Open on page', 'b-media-fields-for-cf7' ),
				'min'   => 1,
				'max'   => 9999,
			),
			'single-page'   => array(
				'group' => 'viewer',
				'type'  => 'flag',
				'label' => __( 'Always show one page at a time (no two-page spread)', 'b-media-fields-for-cf7' ),
			),
			'flip-time'     => array(
				'group' => 'viewer',
				'type'  => 'number',
				'label' => __( 'Page turn duration (ms)', 'b-media-fields-for-cf7' ),
				'min'   => 100,
				'max'   => 3000,
			),
			'no-shadow'     => array(
				'group' => 'viewer',
				'type'  => 'flag',
				'label' => __( 'Disable the page turn shadow', 'b-media-fields-for-cf7' ),
			),
			'eager'         => array(
				'group' => 'viewer',
				'type'  => 'flag',
				'label' => __( 'Load the PDF immediately instead of when it scrolls into view', 'b-media-fields-for-cf7' ),
			),

			/* ---------------- Toolbar ---------------- */
			'no-toolbar'    => array(
				'group' => 'toolbar',
				'type'  => 'flag',
				'label' => __( 'Hide the whole toolbar', 'b-media-fields-for-cf7' ),
			),
			'no-nav'        => array(
				'group' => 'toolbar',
				'type'  => 'flag',
				'label' => __( 'Hide the previous / next buttons and page counter', 'b-media-fields-for-cf7' ),
			),
			'no-zoom'       => array(
				'group' => 'toolbar',
				'type'  => 'flag',
				'label' => __( 'Hide the zoom buttons', 'b-media-fields-for-cf7' ),
			),
			'no-fullscreen' => array(
				'group' => 'toolbar',
				'type'  => 'flag',
				'label' => __( 'Hide the fullscreen button', 'b-media-fields-for-cf7' ),
			),
			'download'      => array(
				'group' => 'toolbar',
				'type'  => 'flag',
				'label' => __( 'Show a download button', 'b-media-fields-for-cf7' ),
				'desc'  => __( 'Hidden by default so the document stays inside the page.', 'b-media-fields-for-cf7' ),
			),
		);

		/**
		 * Filters the registry of [pdf_flipbook] options.
		 *
		 * @param array $fields Field definitions keyed by option name.
		 */
		$fields = apply_filters( 'bmfcf7_pdf_option_fields', $fields );

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
