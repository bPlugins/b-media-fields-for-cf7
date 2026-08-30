# 2. Video field

Plays a video inside the form. Works with files on your own site (MP4, WebM, Ogg), **YouTube** and **Vimeo**. The player is [Plyr](https://github.com/sampotts/plyr), a lightweight, accessible player, and every Plyr option is available as a tag option.

![A video field on the front end](images/20-front-video.png)

## 2.1 Quick start with the generator

In the form editor click **video**.

![The video tag generator](images/08-generator-video.png)

* **Field name** — required, for example `tour`.
* **Provider** — leave on *Self-hosted* for a file, or pick YouTube / Vimeo. The plugin also detects YouTube and Vimeo links automatically, so you can usually leave this alone.
* **Media URL(s)** — one URL per line. For self-hosted video you can add several files (for example MP4 and WebM) and the browser picks the one it supports.
* **Title** — optional.

Everything else is optional. Click **Insert Tag**.

## 2.2 Examples

A file on your site:

```
[video intro "https://example.com/intro.mp4"]
```

A YouTube video, privacy-enhanced (no cookies until play):

```
[video promo yt-nocookie "https://www.youtube.com/watch?v=bTqVqk7FSmY"]
```

A Vimeo video that starts muted and loops, using just the video ID:

```
[video promo provider:vimeo autoplay muted loop "76979871"]
```

A 16:9 player with a poster image, a brand colour and a title:

```
[video tour poster:https://example.com/poster.jpg ratio:16:9 color:#146EF5 "https://example.com/tour.mp4"] Inside the studio [/video]
```

Quality switching — one file per quality with a size hint after a pipe:

```
[video intro quality:720 "https://example.com/intro-1080.mp4|1080" "https://example.com/intro-720.mp4|720"]
```

## 2.3 Rules worth knowing

* **URLs go last** and in double quotes.
* Option values cannot contain spaces. Use `_` for a space in text (`artist:Jane_Doe`) and `|` to separate list items.
* **Autoplay** only works when the video is also **muted**. That is a browser rule, not a plugin one.
* A bare word is a flag: `autoplay`, `muted`, `loop`. A `no-` flag turns something off even if the settings page turns it on: `no-fullscreen`.

## 2.4 All options

### Source

| Option | Values | What it does |
|---|---|---|
| `provider:` | `html5`, `youtube`, `vimeo` | Forces the player type. Usually not needed — the URL is detected. |

### Layout and appearance

| Option | Values | What it does |
|---|---|---|
| `ratio:` | `16:9`, `4:3`, `1:1`, `21:9`, `9:16` | Shape of the player. Empty = taken from the video. |
| `width:` | 50–4000 | Maximum width in pixels. Empty = full width of the form. |
| `align:` | `center`, `right` | Alignment. Empty = left. |
| `color:` | hex, e.g. `#00b3ff` | Accent colour for the controls. |
| `poster:` | image URL | Picture shown before play. |

### Playback

| Option | Values | What it does |
|---|---|---|
| `autoplay` | flag | Start automatically. Needs `muted`. |
| `muted` | flag | Start muted. |
| `loop` | flag | Repeat when finished. |
| `reset-on-end` | flag | Rewind to the start when finished. |
| `no-playsinline` | flag | On iPhones, open the native full-screen player instead of playing inline. |
| `no-autopause` | flag | Let several Vimeo players play at once. |
| `volume:` | 0–1 | Starting volume, e.g. `0.5`. |
| `seek-time:` | 1–600 | Seconds jumped by rewind / fast-forward. |
| `duration:` | number | Override the displayed length. Rarely needed. |

### Controls and settings menu

| Option | Values | What it does |
|---|---|---|
| `controls:` | list separated by `\|` | Which buttons to show. Available: `play-large`, `restart`, `rewind`, `play`, `fast-forward`, `progress`, `current-time`, `duration`, `mute`, `volume`, `captions`, `settings`, `pip`, `airplay`, `download`, `fullscreen`. |
| `settings:` | list of `captions`, `quality`, `speed`, `loop` | What the gear menu offers. |
| `download:` | URL | File served by the Download button. Empty = the video itself. |

Example — a minimal player with only play, progress and fullscreen:

```
[video clip controls:play|progress|fullscreen "https://example.com/clip.mp4"]
```

### Interface behaviour

| Option | What it does |
|---|---|
| `no-click-to-play` | Clicking the picture no longer plays/pauses. |
| `no-hide-controls` | Keep the controls visible all the time. |
| `context-menu` | Allow the right-click menu. |
| `no-display-duration` | Hide the length before play. |
| `no-invert-time` | Show time elapsed instead of time remaining. |
| `no-toggle-invert` | Clicking the time no longer switches the format. |
| `tooltips-controls` | Show tooltips on the buttons. |
| `no-tooltips-seek` | Hide the tooltip on the progress bar. |
| `no-keyboard` | Disable keyboard shortcuts. |
| `keyboard-global` | Shortcuts work even when the player is not focused. |
| `no-fullscreen` | Disable fullscreen. |
| `no-fullscreen-fallback` | No "full window" fallback on browsers without fullscreen. |
| `fullscreen-ios-native` | Use the native iOS fullscreen player. |
| `fullscreen-container:` | CSS selector of the element to make fullscreen instead of the player. |
| `no-storage` | Do not remember the visitor's volume, captions and speed. |
| `storage-key:` | Name used to remember them (default `plyr`). |

### Captions

Captions work with **WebVTT** (`.vtt`) files for self-hosted video. For YouTube they set the caption preference of the embedded player.

| Option | Values | What it does |
|---|---|---|
| `captions:` | `language\|URL\|Label` items separated by spaces | Caption tracks. Label is optional. |
| `captions-active` | flag | Show captions by default. |
| `captions-lang:` | `en`, `fr`, … | Default caption language. Empty = the visitor's browser language. |
| `captions-update` | flag | Watch for tracks added later. |

```
[video talk captions:en|https://example.com/en.vtt|English fr|https://example.com/fr.vtt|Français captions-active "https://example.com/talk.mp4"]
```

If the caption file lives on another domain add the `crossorigin` flag.

### Speed and quality

| Option | Values | What it does |
|---|---|---|
| `speed:` | 0.1–16 | Default speed, e.g. `1.25`. |
| `speed-options:` | list, e.g. `0.5\|1\|1.5\|2` | Speeds offered in the menu. |
| `quality:` | height in px, e.g. `720` | Quality selected on load. |
| `quality-options:` | list, e.g. `1080\|720\|480` | Heights offered in the menu. |
| `quality-forced` | flag | Show the quality menu even if switching is not possible. |

Quality switching for your own files needs one source per quality with a size hint: `"video-720.mp4|720"`.

### Lock-screen information (phones)

| Option | What it does |
|---|---|
| `artist:` | Artist name. Use `_` for spaces. |
| `album:` | Album name. |
| `artwork:` | Image URL. |

### Chapters and preview thumbnails

| Option | Values | What it does |
|---|---|---|
| `markers:` | `seconds=Label` items separated by `\|` | Chapter markers on the progress bar, e.g. `markers:0=Intro\|45=Pricing\|120=Questions`. |
| `thumbnails:` | URL of a WebVTT sprite file | Preview pictures while scrubbing. |
| `thumbnails-credentials` | flag | Send cookies when loading the thumbnail file. |

### YouTube

| Option | What it does |
|---|---|
| `yt-nocookie` | Use youtube-nocookie.com (privacy-enhanced). |
| `yt-rel` | Show related videos at the end. |
| `yt-annotations` | Show annotations. |
| `yt-native-controls` | Use YouTube's own controls instead of Plyr's. |
| `yt-start:` | Start at this second. |
| `yt-end:` | Stop at this second. |
| `yt-hl:` | Interface language, e.g. `de`. |

### Vimeo

| Option | What it does |
|---|---|
| `vimeo-byline` | Show the byline. |
| `vimeo-portrait` | Show the author picture. |
| `vimeo-title` | Show the title. |
| `no-vimeo-speed` | Disable speed controls. |
| `vimeo-transparent` | Transparent background. |
| `vimeo-native-controls` | Use Vimeo's own controls. |
| `vimeo-premium` | You have a Vimeo Pro/Business account (lets native controls be hidden). |
| `vimeo-referrer-policy:` | One of the standard referrer policies, e.g. `strict-origin-when-cross-origin`. |

### Advanced

| Option | What it does |
|---|---|
| `crossorigin` | Load the media with CORS. Needed for caption files on another domain. |
| `debug` | Log player activity to the browser console. |
| `disabled` | Turn Plyr off and show the browser's plain player. |
| `ads-publisher-id:` | vi.ai advertising publisher ID. |
| `ads-tag-url:` | VAST ad tag URL (Google IMA). |
| `id:` / `class:` | Standard Contact Form 7 options, added to the wrapper. |

## 2.5 Site-wide defaults

The accent colour, the default control set, the settings menu, "remember visitor preferences" and "auto-hide controls" can be set once for every video in **Contact → Media Fields → Video**. A tag option always wins over the default. See [Settings](07-settings.md).
