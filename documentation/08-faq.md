# 8. FAQ and troubleshooting

## General

**Do media fields send anything with the email?**
No. They display content; they collect nothing. That is why they are not offered in the Mail tab and the generator says there is no mail-tag.

**Why does the field need a name if nothing is submitted?**
Contact Form 7 requires every form-tag to have a name. Any name will do.

**Can I put more than one media field in a form?**
Yes, as many as you like, of any type. See the "Commission enquiry" form on the demo site, which combines a video and a gallery.

**Does the plugin load anything from other servers?**
No. The players and viewers (Plyr, model-viewer, PDF.js, StPageFlip) are bundled with the plugin and served from your site. YouTube and Vimeo videos naturally come from YouTube and Vimeo, and only once a visitor presses play (or immediately, if you set `autoplay`).

**Does it work with page builders?**
Yes, wherever the Contact Form 7 shortcode works. If a player appears unstyled inside a builder or a popup, turn on **Load assets on every page** in Settings → General.

## The form editor

**The tag I typed shows as plain text on the page.**
Check the order: type, name, options, then the quoted URL(s) **last**. A URL before an option breaks the tag. Use the generator to avoid this.

**The tag renders nothing at all.**
The field type may be switched off in **Contact → Media Fields → Overview**. Editors see a notice in the editor; visitors see nothing.

**"Add from Media Library" closes the tag generator.**
That is expected. Contact Form 7's dialog sits above everything else, so the plugin closes it while the Media Library is open and reopens it, with your values intact, when you are done.

**I cannot upload a .glb / .usdz file.**
Make sure the 3D field is enabled in Settings. Uploads of `.glb`, `.gltf`, `.usdz` and `.hdr` are allowed only while it is.

## Video and audio

**Autoplay does not work.**
Browsers only allow autoplay when the media is muted. Use `autoplay muted` together.

**The quality menu is empty.**
Quality switching for your own files needs one file per quality with a size hint: `"video-720.mp4|720" "video-1080.mp4|1080"`. YouTube and Vimeo manage quality themselves.

**Captions do not show.**
Captions need WebVTT (`.vtt`) files. If the file is on another domain, add the `crossorigin` flag and make sure that server sends CORS headers.

**A YouTube video says "Error 153" or "Video player configuration error".**
Your site sends a strict `Referrer-Policy` header (often `same-origin`), so YouTube cannot see which site is embedding the video. The plugin sets the correct policy on its own players, but if you embed YouTube some other way on the same page that embed will still fail. The fix at the source is to set the site's policy to `strict-origin-when-cross-origin`.

**The video is huge / tiny.**
Set `width:` for a maximum width, or `ratio:` to control the shape. The player is always as wide as the form unless you limit it.

## 3D models

**The model is cropped or off-centre.**
Use `camera-orbit:` with `auto` for the radius, for example `camera-orbit:0deg|75deg|auto`, or set `camera-target:auto|auto|auto`. You can also try `bounds:tight`.

**The model is dark.**
Add `environment:neutral` and raise `exposure:` (try `1.2`).

**The AR button does not appear on iPhone.**
iOS needs a USDZ file. Add it as a second URL or with `ios-src:`.

**Hotspots are in the wrong place.**
Positions are in metres in the model's own coordinate system. Open the model at https://modelviewer.dev/editor/, click the surface and copy the coordinates shown.

## Gallery

**I turned on captions in Settings and now every gallery has them.**
The setting applies to every gallery, and there is no per-gallery option to remove them. Turn the setting off and add the `captions` flag only to the galleries that need it.

**Images are cropped.**
Add `contain` to fit the whole image, or set `ratio:` to match your images, or leave `ratio:` empty for the original shape.

**Tiles are uneven in a grid.**
Set a `ratio:` — the grid uses it to keep every tile the same shape.

## PDF flipbook

**The PDF does not load.**
Check that the URL opens in a browser tab on its own. The file must be served over http or https from a server that allows it to be read (same site is always fine).

**I want visitors to be able to download it.**
Add the `download` flag. It is hidden by default.

**The pages look blurry.**
Zoom in with the toolbar; pages re-render at the new size. Very small viewers can be enlarged with `height:`.

## Privacy and the opt-in

**What does the opt-in share?**
WordPress and PHP versions, the active plugins and which media fields you use, through Freemius. Nothing is shared unless you allow it, and you can opt out from the Plugins screen at any time. See https://freemius.com/privacy/.

## Still stuck?

Ask on the support forum: https://wordpress.org/support/plugin/b-media-fields-for-cf7/. Include the tag you used and, if possible, a link to the page.
