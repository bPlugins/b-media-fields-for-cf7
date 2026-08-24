<?php
/**
 * Form-tag generator dialog (Contact Form 7 tag generator API v2).
 *
 * @package BMediaFieldsCF7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds the "video" button to the form editor toolbar and renders its dialog.
 */
final class BMFCF7_Tag_Generator {

	/**
	 * Registers hooks.
	 */
	public static function init() {
		add_action( 'wpcf7_admin_init', array( __CLASS__, 'register' ), 60, 0 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Registers the generator with CF7.
	 */
	public static function register() {
		if ( ! class_exists( 'WPCF7_TagGenerator' ) ) {
			return;
		}

		$generator = WPCF7_TagGenerator::get_instance();

		if ( BMFCF7_Settings::is_enabled( 'video' ) ) {
			$generator->add(
				'video',
				__( 'video', 'b-media-fields-for-cf7' ),
				array( __CLASS__, 'render' ),
				array( 'version' => '2' )
			);
		}

		if ( BMFCF7_Settings::is_enabled( 'audio' ) ) {
			$generator->add(
				'audio',
				__( 'audio', 'b-media-fields-for-cf7' ),
				array( __CLASS__, 'render' ),
				array( 'version' => '2' )
			);
		}
	}

	/**
	 * Option groups that only make sense for video.
	 *
	 * @return string[]
	 */
	private static function video_only_groups() {
		return array( 'youtube', 'vimeo' );
	}

	/**
	 * Individual options that only make sense for video.
	 *
	 * @return string[]
	 */
	private static function video_only_fields() {
		return array( 'ratio', 'poster', 'no-playsinline', 'no-click-to-play', 'no-hide-controls', 'fullscreen-ios-native', 'thumbnails', 'thumbnails-credentials', 'quality', 'quality-options', 'quality-forced', 'captions', 'captions-active', 'captions-lang', 'captions-update', 'crossorigin' );
	}

	/**
	 * Enqueues admin assets on the form editor screen only.
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public static function enqueue( $hook_suffix ) {
		if ( false === strpos( $hook_suffix, 'wpcf7' ) ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_style(
			'bmfcf7-admin',
			BMFCF7_URL . 'assets/css/admin.css',
			array(),
			BMFCF7_VERSION
		);

		wp_enqueue_script(
			'bmfcf7-admin',
			BMFCF7_URL . 'assets/js/admin.js',
			array(),
			BMFCF7_VERSION,
			true
		);

		wp_localize_script(
			'bmfcf7-admin',
			'bmfcf7Admin',
			array(
				'i18n' => array(
					'chooseMedia'  => __( 'Select media', 'b-media-fields-for-cf7' ),
					'useThis'      => __( 'Use this file', 'b-media-fields-for-cf7' ),
					'chooseImage'  => __( 'Select image', 'b-media-fields-for-cf7' ),
					'chooseText'   => __( 'Select file', 'b-media-fields-for-cf7' ),
					'invalidChars' => __( 'Only letters, numbers and - + * = : . ! ? # $ & @ _ / | % are allowed here (no spaces or commas).', 'b-media-fields-for-cf7' ),
				),
			)
		);
	}

	/**
	 * Renders the dialog content.
	 *
	 * @param WPCF7_ContactForm $contact_form Contact form.
	 * @param array             $options      Generator options (id, title, content, version).
	 */
	public static function render( $contact_form, $options ) {
		$basetype = ( 'audio' === $options['id'] ) ? 'audio' : 'video';
		$is_audio = ( 'audio' === $basetype );
		$tgg      = new WPCF7_TagGeneratorGenerator( $options['content'] );

		if ( $is_audio ) {
			$heading     = __( 'Audio form-tag generator', 'b-media-fields-for-cf7' );
			$description = __( 'Generates a form-tag that embeds a self-hosted audio file (MP3, M4A, OGG, WAV, FLAC) inside the form, played with the accessible Plyr player. Only the options you change are written into the tag; everything else uses Plyr’s defaults (or the defaults from Contact → Video Addon).', 'b-media-fields-for-cf7' );
		} else {
			$heading     = __( 'Video form-tag generator', 'b-media-fields-for-cf7' );
			$description = __( 'Generates a form-tag that embeds a self-hosted video file, a YouTube video or a Vimeo video inside the form, played with the accessible Plyr player. Only the options you change are written into the tag; everything else uses Plyr’s defaults (or the defaults from Contact → Video Addon).', 'b-media-fields-for-cf7' );
		}
		?>
<header class="description-box">
	<h3><?php echo esc_html( $heading ); ?></h3>
	<p><?php echo esc_html( $description ); ?></p>
</header>

<div class="control-box bmfcf7-control-box" data-bmfcf7-basetype="<?php echo esc_attr( $basetype ); ?>">
		<?php
		// The field type is fixed per dialog; CF7's generator script reads it from this hidden part.
		?>
	<input type="hidden" data-tag-part="basetype" value="<?php echo esc_attr( $basetype ); ?>" />
		<?php
		$tgg->print( 'field_name' );
		?>

	<fieldset class="bmfcf7-source">
		<legend id="<?php echo esc_attr( $tgg->ref( 'provider-legend' ) ); ?>"><?php esc_html_e( 'Media source', 'b-media-fields-for-cf7' ); ?></legend>
		<?php if ( ! $is_audio ) : ?>
		<label for="<?php echo esc_attr( $tgg->ref( 'provider' ) ); ?>"><?php esc_html_e( 'Provider', 'b-media-fields-for-cf7' ); ?></label><br />
		<select id="<?php echo esc_attr( $tgg->ref( 'provider' ) ); ?>" data-tag-part="option" data-tag-option="provider:" data-bmfcf7-provider="1">
			<?php foreach ( BMFCF7_Options::providers() as $value => $label ) : ?>
			<option value="<?php echo esc_attr( 'html5' === $value ? '' : $value ); ?>"><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<br />
		<?php endif; ?>
		<label for="<?php echo esc_attr( $tgg->ref( 'sources' ) ); ?>" id="<?php echo esc_attr( $tgg->ref( 'sources-label' ) ); ?>"><?php $is_audio ? esc_html_e( 'Audio file URL(s)', 'b-media-fields-for-cf7' ) : esc_html_e( 'Media URL(s) – or a YouTube / Vimeo URL or ID', 'b-media-fields-for-cf7' ); ?></label><br />
		<textarea id="<?php echo esc_attr( $tgg->ref( 'sources' ) ); ?>" rows="3" required data-tag-part="value" data-bmfcf7-sanitize="url-lines" aria-labelledby="<?php echo esc_attr( $tgg->ref( 'sources-label' ) ); ?>" aria-describedby="<?php echo esc_attr( $tgg->ref( 'sources-desc' ) ); ?>" placeholder="<?php echo $is_audio ? 'https://example.com/track.mp3' : 'https://example.com/video.mp4'; ?>"></textarea>
		<br />
		<button type="button" class="button" data-bmfcf7-media="<?php echo esc_attr( $basetype ); ?>" data-bmfcf7-target="#<?php echo esc_attr( $tgg->ref( 'sources' ) ); ?>" data-bmfcf7-mode="append"><?php esc_html_e( 'Add from Media Library', 'b-media-fields-for-cf7' ); ?></button>
		<p class="description" id="<?php echo esc_attr( $tgg->ref( 'sources-desc' ) ); ?>">
		<?php
		if ( $is_audio ) {
			esc_html_e( 'One URL per line. You can add several formats of the same track (e.g. MP3 + OGG); the browser plays the first one it supports.', 'b-media-fields-for-cf7' );
		} else {
			esc_html_e( 'One URL per line. For self-hosted media you can add several files (e.g. MP4 + WebM) and optionally add a quality hint after a pipe, e.g. https://example.com/video-720.mp4|720 to enable the quality menu.', 'b-media-fields-for-cf7' );
		}
		?>
		</p>
	</fieldset>

	<fieldset>
		<legend id="<?php echo esc_attr( $tgg->ref( 'title-legend' ) ); ?>"><?php esc_html_e( 'Title', 'b-media-fields-for-cf7' ); ?></legend>
		<input type="text" data-tag-part="content" data-bmfcf7-sanitize="content" aria-labelledby="<?php echo esc_attr( $tgg->ref( 'title-legend' ) ); ?>" placeholder="<?php esc_attr_e( 'Optional – used for accessibility and the media session', 'b-media-fields-for-cf7' ); ?>" />
	</fieldset>

		<?php
		foreach ( BMFCF7_Options::groups() as $group_key => $group ) {
			if ( $is_audio && in_array( $group_key, self::video_only_groups(), true ) ) {
				continue;
			}

			$fields = BMFCF7_Options::fields_in_group( $group_key );

			if ( $is_audio ) {
				$fields = array_diff_key( $fields, array_flip( self::video_only_fields() ) );
			}

			if ( empty( $fields ) ) {
				continue;
			}

			if ( $is_audio ) {
				$audio_labels = array(
					'speed'   => __( 'Speed', 'b-media-fields-for-cf7' ),
					'markers' => __( 'Timeline markers', 'b-media-fields-for-cf7' ),
				);
				if ( isset( $audio_labels[ $group_key ] ) ) {
					$group['label'] = $audio_labels[ $group_key ];
					$group['desc']  = '';
				}
			}

			$show_for = in_array( $group_key, array( 'youtube', 'vimeo' ), true ) ? $group_key : '';
			?>
	<details class="bmfcf7-group" data-bmfcf7-group="<?php echo esc_attr( $group_key ); ?>"<?php echo $show_for ? ' data-bmfcf7-show-for="' . esc_attr( $show_for ) . '"' : ''; ?>>
		<summary><?php echo esc_html( $group['label'] ); ?></summary>
			<?php if ( ! empty( $group['desc'] ) ) : ?>
		<p class="description"><?php echo esc_html( $group['desc'] ); ?></p>
			<?php endif; ?>
			<?php foreach ( $fields as $key => $field ) : ?>
				<?php self::render_field( $tgg, $key, $field ); ?>
			<?php endforeach; ?>
	</details>
			<?php
		}

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

	/**
	 * Renders one option control.
	 *
	 * @param WPCF7_TagGeneratorGenerator $tgg   Reference helper.
	 * @param string                      $key   Option name.
	 * @param array                       $field Field definition.
	 */
	public static function render_field( $tgg, $key, $field ) {
		$type  = isset( $field['type'] ) ? $field['type'] : 'text';
		$id    = $tgg->ref( 'opt-' . $key );
		$label = isset( $field['label'] ) ? $field['label'] : $key;
		$desc  = isset( $field['desc'] ) ? $field['desc'] : '';

		echo '<div class="bmfcf7-field bmfcf7-field-' . esc_attr( $type ) . '">';

		switch ( $type ) {
			case 'flag':
				printf(
					'<label><input type="checkbox" data-tag-part="option" data-tag-option="%1$s" /> %2$s <code>%1$s</code></label>',
					esc_attr( $key ),
					esc_html( $label )
				);
				break;

			case 'multi':
				printf( '<span class="bmfcf7-field-label">%s <code>%s:</code></span>', esc_html( $label ), esc_attr( $key ) );
				echo '<div class="bmfcf7-checkbox-grid">';
				foreach ( (array) $field['choices'] as $value => $choice_label ) {
					printf(
						'<label><input type="checkbox" data-tag-part="option" data-tag-option="%1$s:" value="%2$s" /> %3$s <code>%2$s</code></label>',
						esc_attr( $key ),
						esc_attr( $value ),
						esc_html( $choice_label )
					);
				}
				echo '</div>';
				break;

			case 'select':
				printf( '<label for="%s">%s <code>%s:</code></label><br />', esc_attr( $id ), esc_html( $label ), esc_attr( $key ) );
				printf( '<select id="%s" data-tag-part="option" data-tag-option="%s:">', esc_attr( $id ), esc_attr( $key ) );
				foreach ( (array) $field['choices'] as $value => $choice_label ) {
					printf( '<option value="%s">%s</option>', esc_attr( $value ), esc_html( $choice_label ) );
				}
				echo '</select>';
				break;

			case 'number':
				printf( '<label for="%s">%s <code>%s:</code></label><br />', esc_attr( $id ), esc_html( $label ), esc_attr( $key ) );
				printf(
					'<input type="number" id="%s" data-tag-part="option" data-tag-option="%s:" %s %s %s />',
					esc_attr( $id ),
					esc_attr( $key ),
					isset( $field['min'] ) ? 'min="' . esc_attr( $field['min'] ) . '"' : '',
					isset( $field['max'] ) ? 'max="' . esc_attr( $field['max'] ) . '"' : '',
					'step="' . esc_attr( isset( $field['step'] ) ? $field['step'] : 'any' ) . '"'
				);
				break;

			case 'color':
				printf( '<label for="%s">%s <code>%s:</code></label><br />', esc_attr( $id ), esc_html( $label ), esc_attr( $key ) );
				printf(
					'<input type="text" id="%s" data-tag-part="option" data-tag-option="%s:" data-bmfcf7-sanitize="color" pattern="^#?([0-9a-fA-F]{3}){1,2}$" placeholder="#00b2ff" />',
					esc_attr( $id ),
					esc_attr( $key )
				);
				break;

			case 'url':
				printf( '<label for="%s">%s <code>%s:</code></label><br />', esc_attr( $id ), esc_html( $label ), esc_attr( $key ) );
				printf(
					'<input type="url" id="%s" class="bmfcf7-url" data-tag-part="option" data-tag-option="%s:" data-bmfcf7-sanitize="url" placeholder="https://" />',
					esc_attr( $id ),
					esc_attr( $key )
				);
				if ( ! empty( $field['media'] ) ) {
					printf(
						' <button type="button" class="button button-small" data-bmfcf7-media="%s" data-bmfcf7-target="#%s" data-bmfcf7-mode="replace">%s</button>',
						esc_attr( $field['media'] ),
						esc_attr( $id ),
						esc_html__( 'Media Library', 'b-media-fields-for-cf7' )
					);
				}
				break;

			case 'list':
				printf( '<label for="%s">%s <code>%s:</code></label><br />', esc_attr( $id ), esc_html( $label ), esc_attr( $key ) );
				printf(
					'<input type="text" id="%s" class="bmfcf7-wide" data-tag-part="option" data-tag-option="%s:" data-bmfcf7-sanitize="%s" />',
					esc_attr( $id ),
					esc_attr( $key ),
					esc_attr( isset( $field['sanitize'] ) ? $field['sanitize'] : ( 'captions' === $key ? 'captions' : 'token' ) )
				);
				if ( ! empty( $field['media'] ) ) {
					printf(
						' <button type="button" class="button button-small" data-bmfcf7-media="%s" data-bmfcf7-target="#%s" data-bmfcf7-mode="captions">%s</button>',
						esc_attr( $field['media'] ),
						esc_attr( $id ),
						esc_html__( 'Add .vtt from Media Library', 'b-media-fields-for-cf7' )
					);
				}
				break;

			case 'token':
			case 'text':
			default:
				printf( '<label for="%s">%s <code>%s:</code></label><br />', esc_attr( $id ), esc_html( $label ), esc_attr( $key ) );
				printf(
					'<input type="text" id="%s" data-tag-part="option" data-tag-option="%s:" data-bmfcf7-sanitize="%s" />',
					esc_attr( $id ),
					esc_attr( $key ),
					'text' === $type ? 'text' : 'token'
				);
				if ( ! empty( $field['media'] ) ) {
					printf(
						' <button type="button" class="button button-small" data-bmfcf7-media="%s" data-bmfcf7-target="#%s" data-bmfcf7-mode="replace">%s</button>',
						esc_attr( $field['media'] ),
						esc_attr( $id ),
						esc_html__( 'Media Library', 'b-media-fields-for-cf7' )
					);
				}
				break;
		}

		if ( $desc ) {
			echo '<p class="description">' . esc_html( $desc ) . '</p>';
		}

		echo '</div>';
	}
}
