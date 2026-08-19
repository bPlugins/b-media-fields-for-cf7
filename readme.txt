=== Essential Fields for CF7 ===
Contributors: bplugins, abuhayat
Tags: contact form 7, video, audio, youtube, vimeo
Requires at least: 6.2
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
Requires Plugins: contact-form-7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Additional fields for Contact Form 7: audio, video (self-hosted, YouTube, Vimeo), 3D models and more inside your forms.

== Description ==

**Essential Fields for CF7** adds additional field types to Contact Form 7 that the core plugin does not ship: `[video]`, `[audio]` and `[3d_models]`, with more on the way. Place a media player or an interactive 3D model anywhere inside a form: an explainer above the fields, a product demo next to a quote request, a podcast episode in a feedback form, a welcome message in a registration form, and so on.

Media is played with [Plyr](https://github.com/sampotts/plyr), a lightweight, accessible and fully customisable player, and 3D models are rendered with Google's [&lt;model-viewer&gt;](https://modelviewer.dev/). Every option of both libraries is exposed as a form-tag option, and visual **tag generators** (video, audio, 3D model) in the form editor let you build the tag without remembering any syntax. A modern settings screen (Contact → Essential Fields) holds per-field-type defaults and lets you enable or disable each field type.

**Coming next:** a PDF Flipbook field (`[pdf_flipbook]`) for brochures, catalogues and manuals.

= Supported media =

* Self-hosted HTML5 **video** (MP4, WebM, Ogg) with multiple sources and quality switching
* Self-hosted HTML5 **audio** (MP3, M4A, OGG, WAV, FLAC)
* **YouTube** (including privacy-enhanced youtube-nocookie.com)
* **Vimeo** (including unlisted videos with a hash)
* **3D models** – glTF / GLB with USDZ for iOS Quick Look, rendered by &lt;model-viewer&gt;

= Every Plyr option =

Layout & appearance – aspect ratio, max width, alignment, accent colour, poster image.
Playback – autoplay, muted, loop, reset on end, inline playback, autopause, volume, seek time, custom duration.
Controls – pick exactly which controls appear (play, progress, time, mute, volume, captions, settings, PiP, AirPlay, download, fullscreen, restart, rewind, fast-forward) and which items are in the settings menu.
Interface – click-to-play, auto-hide controls, context menu, duration display, inverted time, tooltips, keyboard shortcuts (focused/global), fullscreen options incl. iOS native, browser storage.
Captions – any number of WebVTT tracks, default language, captions on by default.
Speed & quality – default speed, speed options, default quality, quality options.
Media metadata – title, artist, album, artwork (Media Session / lock screen).
Markers & preview thumbnails – chapter markers on the timeline, WebVTT preview thumbnails.
YouTube – nocookie, related videos, annotations, native controls, start/end time, interface language.
Vimeo – byline, portrait, title, speed, transparent, native controls, premium, referrer policy.
Advanced – ads (VAST), debug, CORS, disable Plyr.

= Every &lt;model-viewer&gt; option (3D models) =

Layout – height, max width, alignment, background, poster colour, progress bar colour/height/hide.
Loading – poster image, loading strategy, reveal, with-credentials, generate-schema (JSON-LD).
Camera & interaction – camera controls, auto-rotate (delay, speed), camera orbit/target, field of view, min/max orbit & FoV, disable zoom/pan/tap, touch-action, orbit/zoom/pan sensitivity, interaction prompt (style, threshold), interpolation decay.
Augmented reality – ar, ar-modes (WebXR, Scene Viewer, Quick Look), ar-scale, ar-placement, ios-src (USDZ), USDZ max texture size, xr-environment, custom AR button label.
Lighting & environment – environment-image (neutral / legacy / HDR URL), skybox-image, skybox-height, exposure, tone-mapping, shadow-intensity, shadow-softness.
Animation & scene – animation-name, autoplay, crossfade duration, variant-name, orientation, scale, bounds.
Hotspots – any number of annotations (position, label, optional normal) plus hotspot opacity.
GLB / glTF / USDZ / HDR uploads are allowed in the Media Library while the 3D field is enabled.

= Quick examples =

`[video intro "https://example.com/intro.mp4"]`

`[video intro quality:720 poster:https://example.com/poster.jpg "https://example.com/intro-1080.mp4|1080" "https://example.com/intro-720.mp4|720"]`

`[video promo provider:youtube yt-nocookie ratio:16:9 "https://www.youtube.com/watch?v=bTqVqk7FSmY"]`

`[video promo provider:vimeo autoplay muted loop "76979871"]`

`[audio podcast artist:Jane_Doe "https://example.com/ep1.mp3"] Episode 1 [/audio]`

`[3d_models chair auto-rotate ar camera-orbit:45deg|60deg|2m hotspot:0|0.5|0.2|Handle "https://example.com/chair.glb" "https://example.com/chair.usdz"] Red armchair [/3d_models]`

Global defaults (accent colour, default controls, Plyr build, asset loading) live under **Contact → Video Addon**.

= Developer hooks =

* `efcf7_option_fields` – filter the registry of form-tag options.
* `efcf7_player_config` – filter the Plyr config of a single player.
* `efcf7_frontend_data` – filter global data handed to the front-end script.
* `efcf7_model_option_fields` / `efcf7_model_viewer_atts` – filter the 3D model options and rendered attributes.
* `efcf7_settings_schema` – add settings sections for new field types.
* JavaScript event `efcf7:ready` (detail.player is the Plyr instance) and `window.efcf7.players`.

= Privacy =

Self-hosted media and 3D models never contact third parties. When you embed YouTube or Vimeo media, the visitor's browser loads the player from YouTube/Vimeo and is subject to their privacy policies. Plyr and &lt;model-viewer&gt; are bundled with the plugin; no assets are loaded from a CDN. The AR "Scene Viewer" / "Quick Look" modes hand the model file to the device's native AR app.

== Installation ==

1. Install and activate [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) (version 6.0 or later).
2. Upload the plugin to `/wp-content/plugins/` or install it from the Plugins screen, then activate it.
3. Edit a contact form and click the **video**, **audio** or **3D model** button above the form editor to generate a tag, or type one by hand.
4. Optionally set global defaults under **Contact → Video Addon**.

== Frequently Asked Questions ==

= Is the video sent with the form submission? =

No. The tag only displays media; it is not an input field and has no mail-tag.

= Autoplay does not work =

Browsers only allow autoplay when the media is muted. Add the `muted` option together with `autoplay`.

= What is the tag syntax? =

`[video name option:value flag "source URL"] Optional title [/video]` – the name comes first, then options, and the quoted media URL(s) always come last (this is Contact Form 7's standard form-tag order). The tag generator takes care of this for you.

= My option value contains a space or comma =

Contact Form 7 does not allow spaces or commas inside form-tag options. Use `_` for spaces (e.g. `artist:Jane_Doe`) and `|` to separate list items (e.g. `speed-options:0.5|1|2`). Media URLs go inside quotes and may contain anything except quotes.

= Captions do not show =

WebVTT caption files must be served from the same domain as the page, or from a server that sends CORS headers – in that case add the `crossorigin` option.

= Can I use it with page builders or popups that load forms later? =

Yes. Enable "Load player assets on every front-end page" under Contact → Video Addon; the script watches the page and initialises players that are added later.

== Screenshots ==

1. The settings screen with one panel per field type.
2. The video tag generator in the form editor.
3. A video and a 3D model inside a contact form on the front end.

== Third-party libraries ==

This plugin bundles:

* [Plyr](https://github.com/sampotts/plyr) v3.8.4, © Sam Potts, MIT license. Unminified sources are included in `assets/vendor/plyr/`.
* [&lt;model-viewer&gt;](https://github.com/google/model-viewer) v4.3.1, © Google LLC, Apache License 2.0. The minified UMD build is included in `assets/vendor/model-viewer/`; the unminified source is available at https://cdn.jsdelivr.net/npm/@google/model-viewer@4.3.1/dist/model-viewer-umd.js and in the repository linked above.

== Changelog ==

= 1.0.0 =
* Initial release: [video], [audio] and [3d_models] form-tags with tag generators and a settings screen.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
