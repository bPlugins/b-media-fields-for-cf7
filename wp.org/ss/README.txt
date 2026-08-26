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

  1  Set your defaults once           settings screen (opens with the intro video)
  2  Build tags without the syntax    tag generator in the CF7 editor
  3  Turn pages like a brochure       PDF flipbook
  4  Swipe through recent work        gallery carousel inside a form
  5  Open any piece full size         lightbox
  6  Show the work before they ask    gallery grid inside an enquiry form
  7  Let people spin it in 3D         3D model with hotspots
  8  Play audio right where you ask   audio field
  9  Video, without leaving the form  video field
 10  Put media inside your forms      form with video, fields and gallery

Rebuilding: the capture and framing scripts are throwaway Playwright scripts —
recapture from the /demo-* pages, then wrap with the same canvas.

All demo content is our own: the artwork, the brochure PDF and the GLB model
were generated for these shots, so there are no third-party asset licences to
attribute. The video and audio clips are Mozilla's CC0 sample media.
