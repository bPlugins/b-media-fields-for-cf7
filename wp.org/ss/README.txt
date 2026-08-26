Screenshots for the wordpress.org listing
=========================================

screenshot-1.png … screenshot-10.png — 1600 x 1131, ready to upload.
raw/                                 — the unframed 2x captures they were built from.

Upload the numbered files to the plugin's SVN "assets/" directory (NOT trunk/),
alongside icon-*.png and banner-*.png. They must sit in assets/ directly, not in
a sub-folder:

    svn/assets/screenshot-1.png … screenshot-10.png

Each image is a use-case frame: brand canvas, a headline and sub-line set in
Space Grotesk (the bplugins.com heading font), and the real UI inset with a
shadow — the same treatment the Kirki listing uses.

The readme.txt "== Screenshots ==" list supplies the caption under each image
and is matched by number, so keep both in sync if you reorder them.

  1  Put media inside your forms      form with video, fields and gallery
  2  Video, without leaving the form  video field
  3  Play audio right where you ask   audio field
  4  Let people spin it in 3D         3D model with hotspots
  5  Show your work in a gallery      gallery grid with captions
  6  Open any image full size         lightbox
  7  Or slide through a carousel      gallery carousel
  8  Turn pages like a brochure       PDF flipbook
  9  Build tags without the syntax    tag generator in the CF7 editor
 10  Set your defaults once           settings screen

Rebuilding: the capture and framing scripts are throwaway Playwright scripts —
recapture from the /demo-* pages, then wrap with the same canvas.

All demo content is our own: the artwork, the brochure PDF and the GLB model
were generated for these shots, so there are no third-party asset licences to
attribute. The video and audio clips are Mozilla's CC0 sample media.
