<?php
/**
 * Form-tag generator dialog (Contact Form 7 tag generator API v2).
 *
 * @package EssentialFieldsCF7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds the "video" button to the form editor toolbar and renders its dialog.
 */
final class EFCF7_Tag_Generator {

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

		if ( EFCF7_Settings::is_enabled( 'video' ) ) {
			$generator->add(
				'video',
				__( 'video', 'essential-fields-for-cf7' ),
				array( __CLASS__, 'render' ),
				array( 'version' => '2' )
			);
		}

		if ( EFCF7_Settings::is_enabled( 'audio' ) ) {
			$generator->add(
				'audio',
				__( 'audio', 'essential-fields-for-cf7' ),
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
			'efcf7-admin',
			EFCF7_URL . 'assets/css/admin.css',
			array(),
			EFCF7_VERSION
		);

		wp_enqueue_script(
			'efcf7-admin',
			EFCF7_URL . 'assets/js/admin.js',
			array(),
			EFCF7_VERSION,
			true
		);

		wp_localize_script(
			'efcf7-admin',
			'efcf7Admin',
			array(
				'i18n' => array(
					'chooseMedia'  => __( 'Select media', 'essential-fields-for-cf7' ),
					'useThis'      => __( 'Use this file', 'essential-fields-for-cf7' ),
					'chooseImage'  => __( 'Select image', 'essential-fields-for-cf7' ),
					'chooseText'   => __( 'Select file', 'essential-fields-for-cf7' ),
					'invalidChars' => __( 'Only letters, numbers and - + * = : . ! ? # $ & @ _ / | % are allowed here (no spaces or commas).', 'essential-fields-for-cf7' ),
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
			$heading     = __( 'Audio form-tag generator', 'essential-fields-for-cf7' );
			$description = __( 'Generates a form-tag that embeds a self-hosted audio file (MP3, M4A, OGG, WAV, FLAC) inside the form, played with the accessible Plyr player. Only the options you change are written into the tag; everything else uses Plyr’s defaults (or the defaults from Contact → Video Addon).', 'essential-fields-for-cf7' );
		} else {
			$heading     = __( 'Video form-tag generator', 'essential-fields-for-cf7' );
			$description = __( 'Generates a form-tag that embeds a self-hosted video file, a YouTube video or a Vimeo video inside the form, played with the accessible Plyr player. Only the options you change are written into the tag; everything else uses Plyr’s defaults (or the defaults from Contact → Video Addon).', 'essential-fields-for-cf7' );
		}
		?>
<header class="description-box">
	<h3><?php echo esc_html( $heading ); ?></h3>
	<p><?php echo esc_html( $description ); ?></p>
</header>

<div class="control-box efcf7-control-box" data-efcf7-basetype="<?php echo esc_attr( $basetype ); ?>">
		<?php
		$tgg->print(
			'field_type',
			array(
				'select_options' => array(
					$basetype => $is_audio
						? __( 'Audio', 'essential-fields-for-cf7' )
						: __( 'Video', 'essential-fields-for-cf7' ),
				),
			)
		);

		$tgg->print( 'field_name' );
		?>

	<fieldset class="efcf7-source">
		<legend id="<?php echo esc_attr( $tgg->ref( 'provider-legend' ) ); ?>"><?php esc_html_e( 'Media source', 'essential-fields-for-cf7' ); ?></legend>
		<?php if ( ! $is_audio ) : ?>
		<label for="<?php echo esc_attr( $tgg->ref( 'provider' ) ); ?>"><?php esc_html_e( 'Provider', 'essential-fields-for-cf7' ); ?></label><br />
		<select id="<?php echo esc_attr( $tgg->ref( 'provider' ) ); ?>" data-tag-part="option" data-tag-option="provider:" data-efcf7-provider="1">
			<?php foreach ( EFCF7_Options::providers() as $value => $label ) : ?>
			<option value="<?php echo esc_attr( 'html5' === $value ? '' : $value ); ?>"><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<br />
		<?php endif; ?>
		<label for="<?php echo esc_attr( $tgg->ref( 'sources' ) ); ?>" id="<?php echo esc_attr( $tgg->ref( 'sources-label' ) ); ?>"><?php $is_audio ? esc_html_e( 'Audio file URL(s)', 'essential-fields-for-cf7' ) : esc_html_e( 'Media URL(s) – or a YouTube / Vimeo URL or ID', 'essential-fields-for-cf7' ); ?></label><br />
		<textarea id="<?php echo esc_attr( $tgg->ref( 'sources' ) ); ?>" rows="3" required data-tag-part="value" data-efcf7-sanitize="url-lines" aria-labelledby="<?php echo esc_attr( $tgg->ref( 'sources-label' ) ); ?>" aria-describedby="<?php echo esc_attr( $tgg->ref( 'sources-desc' ) ); ?>" placeholder="<?php echo $is_audio ? 'https://example.com/track.mp3' : 'https://example.com/video.mp4'; ?>"></textarea>
		<br />
		<button type="button" class="button" data-efcf7-media="<?php echo esc_attr( $basetype ); ?>" data-efcf7-target="#<?php echo esc_attr( $tgg->ref( 'sources' ) ); ?>" data-efcf7-mode="append"><?php esc_html_e( 'Add from Media Library', 'essential-fields-for-cf7' ); ?></button>
		<p class="description" id="<?php echo esc_attr( $tgg->ref( 'sources-desc' ) ); ?>">
		<?php
		if ( $is_audio ) {
			esc_html_e( 'One URL per line. You can add several formats of the same track (e.g. MP3 + OGG); the browser plays the first one it supports.', 'essential-fields-for-cf7' );
		} else {
			esc_html_e( 'One URL per line. For self-hosted media you can add several files (e.g. MP4 + WebM) and optionally add a quality hint after a pipe, e.g. https://example.com/video-720.mp4|720 to enable the quality menu.', 'essential-fields-for-cf7' );
		}
		?>
		</p>
	</fieldset>

	<fieldset>
		<legend id="<?php echo esc_attr( $tgg->ref( 'title-legend' ) ); ?>"><?php esc_html_e( 'Title', 'essential-fields-for-cf7' ); ?></legend>
		<input type="text" data-tag-part="content" data-efcf7-sanitize="content" aria-labelledby="<?php echo esc_attr( $tgg->ref( 'title-legend' ) ); ?>" placeholder="<?php esc_attr_e( 'Optional – used for accessibility and the media session', 'essential-fields-for-cf7' ); ?>" />
	</fieldset>

		<?php
		foreach ( EFCF7_Options::groups() as $group_key => $group ) {
			if ( $is_audio && in_array( $group_key, self::video_only_groups(), true ) ) {
				continue;
			}

			$fields = EFCF7_Options::fields_in_group( $group_key );

			if ( $is_audio ) {
				$fields = array_diff_key( $fields, array_flip( self::video_only_fields() ) );
			}

			if ( empty( $fields ) ) {
				continue;
			}

			if ( $is_audio ) {
				$audio_labels = array(
					'speed'   => __( 'Speed', 'essential-fields-for-cf7' ),
					'markers' => __( 'Timeline markers', 'essential-fields-for-cf7' ),
				);
				if ( isset( $audio_labels[ $group_key ] ) ) {
					$group['label'] = $audio_labels[ $group_key ];
					$group['desc']  = '';
				}
			}

			$show_for = in_array( $group_key, array( 'youtube', 'vimeo' ), true ) ? $group_key : '';
			?>
	<details class="efcf7-group" data-efcf7-group="<?php echo esc_attr( $group_key ); ?>"<?php echo $show_for ? ' data-efcf7-show-for="' . esc_attr( $show_for ) . '"' : ''; ?>>
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
	<p class="description"><?php esc_html_e( 'This tag has no user input, so there is no mail-tag for it.', 'essential-fields-for-cf7' ); ?></p>
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

		echo '<div class="efcf7-field efcf7-field-' . esc_attr( $type ) . '">';

		switch ( $type ) {
			case 'flag':
				printf(
					'<label><input type="checkbox" data-tag-part="option" data-tag-option="%1$s" /> %2$s <code>%1$s</code></label>',
					esc_attr( $key ),
					esc_html( $label )
				);
				break;

			case 'multi':
				printf( '<span class="efcf7-field-label">%s <code>%s:</code></span>', esc_html( $label ), esc_attr( $key ) );
				echo '<div class="efcf7-checkbox-grid">';
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
					'<input type="text" id="%s" data-tag-part="option" data-tag-option="%s:" data-efcf7-sanitize="color" pattern="^#?([0-9a-fA-F]{3}){1,2}$" placeholder="#00b2ff" />',
					esc_attr( $id ),
					esc_attr( $key )
				);
				break;

			case 'url':
				printf( '<label for="%s">%s <code>%s:</code></label><br />', esc_attr( $id ), esc_html( $label ), esc_attr( $key ) );
				printf(
					'<input type="url" id="%s" class="efcf7-url" data-tag-part="option" data-tag-option="%s:" data-efcf7-sanitize="url" placeholder="https://" />',
					esc_attr( $id ),
					esc_attr( $key )
				);
				if ( ! empty( $field['media'] ) ) {
					printf(
						' <button type="button" class="button button-small" data-efcf7-media="%s" data-efcf7-target="#%s" data-efcf7-mode="replace">%s</button>',
						esc_attr( $field['media'] ),
						esc_attr( $id ),
						esc_html__( 'Media Library', 'essential-fields-for-cf7' )
					);
				}
				break;

			case 'list':
				printf( '<label for="%s">%s <code>%s:</code></label><br />', esc_attr( $id ), esc_html( $label ), esc_attr( $key ) );
				printf(
					'<input type="text" id="%s" class="efcf7-wide" data-tag-part="option" data-tag-option="%s:" data-efcf7-sanitize="%s" />',
					esc_attr( $id ),
					esc_attr( $key ),
					esc_attr( isset( $field['sanitize'] ) ? $field['sanitize'] : ( 'captions' === $key ? 'captions' : 'token' ) )
				);
				if ( ! empty( $field['media'] ) ) {
					printf(
						' <button type="button" class="button button-small" data-efcf7-media="%s" data-efcf7-target="#%s" data-efcf7-mode="captions">%s</button>',
						esc_attr( $field['media'] ),
						esc_attr( $id ),
						esc_html__( 'Add .vtt from Media Library', 'essential-fields-for-cf7' )
					);
				}
				break;

			case 'token':
			case 'text':
			default:
				printf( '<label for="%s">%s <code>%s:</code></label><br />', esc_attr( $id ), esc_html( $label ), esc_attr( $key ) );
				printf(
					'<input type="text" id="%s" data-tag-part="option" data-tag-option="%s:" data-efcf7-sanitize="%s" />',
					esc_attr( $id ),
					esc_attr( $key ),
					'text' === $type ? 'text' : 'token'
				);
				if ( ! empty( $field['media'] ) ) {
					printf(
						' <button type="button" class="button button-small" data-efcf7-media="%s" data-efcf7-target="#%s" data-efcf7-mode="replace">%s</button>',
						esc_attr( $field['media'] ),
						esc_attr( $id ),
						esc_html__( 'Media Library', 'essential-fields-for-cf7' )
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
