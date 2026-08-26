Email marketing
===============

Two emails, two different audiences. Not shipped in the release zip
(see .distignore).

Files
-----
  1.1.0-announcement.html / .txt   To people who ALREADY use this plugin.
                                   Announces the 1.1.0 release: the gallery
                                   and PDF flipbook fields.

  launch-announcement.html / .txt  To subscribers of our OTHER plugins.
                                   Introduces the plugin itself, since most
                                   of them have never heard of it.

  subject-lines.txt                Subject options and preheaders for both.
  images/                          The hero image used at the top of both
                                   emails. Needs hosting, see below.

Send the HTML and plain text versions together as multipart, so clients that
block HTML still get a readable message.

Which one to send
-----------------
They are not interchangeable. The first assumes the reader knows the plugin
and skips straight to what changed. The second assumes nothing, explains what
the plugin is, and lets people who do not use Contact Form 7 opt out in the
first two lines rather than reading a pitch that does not apply to them.

If someone is on both lists, send them the existing user email only.

On the version
--------------
1.0.0 and 1.1.0 both went out on launch day, so both emails simply say the
plugin is at 1.1.0 today. Neither draws attention to the gap, because for a
reader it is one launch, and explaining a same day version bump raises a
question nobody asked.

Merge tags
----------
Swap these for whatever your sending tool uses before the send:

  {{first_name|there}}   first name, falling back to "there"
  {{unsubscribe_url}}    unsubscribe link, required by law in most markets

Images
------
ONE IMAGE NEEDS HOSTING. The hero at the top of both emails points at

  images/b-media-fields-for-cf7-explainer-thumb-c.png

using a relative path, so the file previews correctly when you open the HTML
from this folder. A relative path cannot work in a real inbox. Before you
send, upload that PNG (1280 x 720) to your own site or to your sending tool's
image library, and replace the src with the absolute URL it gives you. There
is a comment at that line in both HTML files marking the spot.

Everything else is served from the wordpress.org CDN, so there is nothing
extra to host and those URLs stay valid:

  https://ps.w.org/b-media-fields-for-cf7/assets/screenshot-6.png   gallery
  https://ps.w.org/b-media-fields-for-cf7/assets/screenshot-7.png   3D model
  https://ps.w.org/b-media-fields-for-cf7/assets/screenshot-3.png   PDF flipbook

If you reorder the screenshots on wordpress.org, check these URLs still point
at the shots the copy describes.

Before sending
--------------
  [ ] Upload the hero PNG and swap the relative src for its absolute URL
  [ ] Replace the merge tags for your sending tool
  [ ] Send yourself a test, and open it in Gmail and on a phone
  [ ] Check it still reads with images blocked (alt text and layout)
  [ ] Confirm the unsubscribe link works
  [ ] Send to a small segment first, then the rest
  [ ] For the launch email, expect a higher unsubscribe rate than usual:
      it goes to people who did not ask about this plugin specifically

A note on the copy
------------------
Both are deliberately plain. These lists are people who already trust us
enough to run our code, so the emails read as news and an invitation to
reply, not as a pitch. The closing question matters as much as the release
does, because replies are what tell us which field to build next.
