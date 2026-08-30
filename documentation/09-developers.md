# 9. For developers

The plugin is built around a schema for each field type, so most customisation is a filter away.

## Filters

| Filter | Receives | Use it to |
|---|---|---|
| `bmfcf7_option_fields` | array of option definitions | Add or change options for the video and audio tags and their generators. |
| `bmfcf7_model_option_fields` | array | Same for the 3D model tag. |
| `bmfcf7_gallery_option_fields` | array | Same for the gallery tag. |
| `bmfcf7_pdf_option_fields` | array | Same for the PDF flipbook tag. |
| `bmfcf7_settings_schema` | array of settings sections | Add a section or a setting to the settings screen. |
| `bmfcf7_player_config` | Plyr config array, `$tag`, `$provider` (`html5`, `youtube`, `vimeo`) | Change the final Plyr configuration for a video or audio player. |
| `bmfcf7_model_viewer_atts` | attribute array, `$tag` | Change the final `<model-viewer>` attributes. |
| `bmfcf7_pdf_config` | config array, `$tag` | Change the final PDF viewer configuration. |
| `bmfcf7_frontend_data` | array | Change the data passed to the front-end scripts. |

### Example: force a control set on every video

```php
add_filter( 'bmfcf7_player_config', function ( $config, $tag, $provider ) {
    if ( 'video' === $tag->basetype ) {
        $config['controls'] = array( 'play', 'progress', 'fullscreen' );
    }
    return $config;
}, 10, 3 );
```

### Example: add a `tone-mapping` default for all models

```php
add_filter( 'bmfcf7_model_viewer_atts', function ( $atts, $tag ) {
    if ( empty( $atts['tone-mapping'] ) ) {
        $atts['tone-mapping'] = 'aces';
    }
    return $atts;
}, 10, 2 );
```

## Markup

Every field is wrapped in a `div.bmfcf7-player-wrap` carrying a `data-name` attribute with the field name, plus type-specific classes you can style against:

| Field | Wrapper classes |
|---|---|
| Video / audio | `bmfcf7-video` or `bmfcf7-audio`, and `bmfcf7-provider-html5` / `-youtube` / `-vimeo` |
| 3D model | `bmfcf7-model` (the element itself is `model-viewer.bmfcf7-model-viewer`) |
| Gallery | `bmfcf7-gallery` and `bmfcf7-gallery--grid` / `--masonry` / `--justified` / `--carousel` |
| PDF flipbook | `bmfcf7-pdf` and `bmfcf7-pdf--flip` / `--scroll` |

`align:center` / `align:right` add `bmfcf7-align-center` / `bmfcf7-align-right`. Anything given in a `class:` option is added to the same wrapper, and `id:` sets its id.

## Assets

Player scripts and styles are enqueued only on pages where a form contains a media field, unless **Load assets on every page** is on. Bundled libraries and versions:

| Library | Version | Licence |
|---|---|---|
| Plyr | 3.8.4 | MIT |
| model-viewer | 4.3.1 | Apache-2.0 |
| PDF.js | 3.11.174 | Apache-2.0 |
| StPageFlip | 2.0.7 | MIT |

## Source

The plugin is developed in the open: https://github.com/bPlugins/b-media-fields-for-cf7. Issues and pull requests are welcome.
