<?php
/**
 * Tag generator dialog for [pdf_flipbook].
 *
 * @package BMediaFieldsCF7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds the "PDF" button to the form editor and renders its dialog.
 */
final class BMFCF7_Pdf_Tag_Generator {

	const GENERATOR_ID = 'pdfflipbook';

	/**
	 * Registers hooks.
	 */
	public static function init() {
		add_action( 'wpcf7_admin_init', array( __CLASS__, 'register' ), 63, 0 );
	}

	/**
	 * Registers the generator with CF7.
	 */
	public static function register() {
		if ( ! class_exists( 'WPCF7_TagGenerator' ) || ! BMFCF7_Settings::is_enabled( BMFCF7_Pdf_Form_Tag::TAG ) ) {
			return;
		}

		WPCF7_TagGenerator::get_instance()->add(
			self::GENERATOR_ID,
			__( 'PDF', 'b-media-fields-for-cf7' ),
			array( __CLASS__, 'render' ),
			array( 'version' => '2' )
		);
	}

	/**
	 * Renders the dialog.
	 *
	 * @param WPCF7_ContactForm $contact_form Contact form.
	 * @param array             $options      Generator options.
	 */
	public static function render( $contact_form, $options ) {
		$tgg = new WPCF7_TagGeneratorGenerator( $options['content'] );
		?>
<header class="description-box">
	<h3><?php esc_html_e( 'PDF flipbook form-tag generator', 'b-media-fields-for-cf7' ); ?></h3>
	<p><?php esc_html_e( 'Generates a form-tag that shows a PDF inside the form — as a page-turning flipbook or a scrolling document, with zoom and fullscreen. Only the options you change are written into the tag; everything else uses the defaults from Contact → Media Fields.', 'b-media-fields-for-cf7' ); ?></p>
</header>

<div class="control-box bmfcf7-control-box" data-bmfcf7-basetype="<?php echo esc_attr( BMFCF7_Pdf_Form_Tag::TAG ); ?>">
	<input type="hidden" data-tag-part="basetype" value="<?php echo esc_attr( BMFCF7_Pdf_Form_Tag::TAG ); ?>" />
		<?php $tgg->print( 'field_name' ); ?>

	<fieldset class="bmfcf7-source">
		<legend id="<?php echo esc_attr( $tgg->ref( 'sources-legend' ) ); ?>"><?php esc_html_e( 'PDF file', 'b-media-fields-for-cf7' ); ?></legend>
		<label for="<?php echo esc_attr( $tgg->ref( 'sources' ) ); ?>" id="<?php echo esc_attr( $tgg->ref( 'sources-label' ) ); ?>"><?php esc_html_e( 'PDF URL', 'b-media-fields-for-cf7' ); ?></label><br />
		<textarea id="<?php echo esc_attr( $tgg->ref( 'sources' ) ); ?>" rows="2" required data-tag-part="value" data-bmfcf7-sanitize="url-lines" aria-labelledby="<?php echo esc_attr( $tgg->ref( 'sources-label' ) ); ?>" aria-describedby="<?php echo esc_attr( $tgg->ref( 'sources-desc' ) ); ?>" placeholder="https://example.com/brochure.pdf"></textarea>
		<br />
		<button type="button" class="button" data-bmfcf7-media="application/pdf" data-bmfcf7-target="#<?php echo esc_attr( $tgg->ref( 'sources' ) ); ?>" data-bmfcf7-mode="replace"><?php esc_html_e( 'Select from Media Library', 'b-media-fields-for-cf7' ); ?></button>
		<p class="description" id="<?php echo esc_attr( $tgg->ref( 'sources-desc' ) ); ?>"><?php esc_html_e( 'The PDF must be on this site, or on a server that allows cross-origin requests, otherwise the browser cannot read it.', 'b-media-fields-for-cf7' ); ?></p>
	</fieldset>

	<fieldset>
		<legend id="<?php echo esc_attr( $tgg->ref( 'title-legend' ) ); ?>"><?php esc_html_e( 'Loading text', 'b-media-fields-for-cf7' ); ?></legend>
		<input type="text" data-tag-part="content" data-bmfcf7-sanitize="content" aria-labelledby="<?php echo esc_attr( $tgg->ref( 'title-legend' ) ); ?>" placeholder="<?php esc_attr_e( 'Optional - shown while the document loads', 'b-media-fields-for-cf7' ); ?>" />
	</fieldset>

		<?php foreach ( BMFCF7_Pdf_Options::groups() as $group_key => $group ) : ?>
			<?php
			$fields = BMFCF7_Pdf_Options::fields_in_group( $group_key );
			if ( empty( $fields ) ) {
				continue;
			}
			?>
	<details class="bmfcf7-group" data-bmfcf7-group="<?php echo esc_attr( $group_key ); ?>">
		<summary><?php echo esc_html( $group['label'] ); ?></summary>
			<?php if ( ! empty( $group['desc'] ) ) : ?>
		<p class="description"><?php echo esc_html( $group['desc'] ); ?></p>
			<?php endif; ?>
			<?php foreach ( $fields as $key => $field ) : ?>
				<?php BMFCF7_Tag_Generator::render_field( $tgg, $key, $field ); ?>
			<?php endforeach; ?>
	</details>
		<?php endforeach; ?>

		<?php
		$tgg->print( 'id_attr' );
		$tgg->print( 'class_attr' );
		?>
</div>

<footer class="insert-box">
		<?php $tgg->print( 'insert_box_content' ); ?>
	<p class="description"><?php esc_html_e( 'This tag has no user input, so there is no mail-tag for it.', 'b-media-fields-for-cf7' ); ?></p>
</footer>
		<?php
	}
}
