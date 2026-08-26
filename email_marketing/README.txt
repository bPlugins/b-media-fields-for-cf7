Email marketing
===============

Announcement email for existing users of the plugin. Not shipped in the
release zip (see .distignore).

Files
-----
  1.1.0-announcement.html   HTML version, table based, inline styled
  1.1.0-announcement.txt    Plain text version, send both as multipart
  subject-lines.txt         Subject line options and the preheader

Merge tags
----------
Written with two placeholders. Swap them for whatever your sending tool uses
before the send:

  {{first_name|there}}   first name, falling back to "there"
  {{unsubscribe_url}}    unsubscribe link, required by law in most markets

Images
------
Every image is served from the wordpress.org CDN, so there is nothing extra
to host and the URLs stay valid:

  https://ps.w.org/b-media-fields-for-cf7/assets/screenshot-6.png   gallery
  https://ps.w.org/b-media-fields-for-cf7/assets/screenshot-3.png   PDF flipbook
  https://i.ytimg.com/vi/sbw-mq7Yugs/maxresdefault.jpg              video still

If you reorder the screenshots on wordpress.org, check these two URLs still
point at the gallery and PDF shots.

Before sending
--------------
  [ ] Replace the merge tags for your sending tool
  [ ] Send yourself a test, and open it in Gmail and on a phone
  [ ] Check the images load with images blocked as well (alt text and layout)
  [ ] Confirm the unsubscribe link works
  [ ] Send to a small segment first, then the rest

A note on the copy
------------------
The tone is deliberately plain: this goes to people who already trust the
plugin enough to install it, so it reads as product news and an invitation to
reply, not as a pitch. The closing question is the point of the email as much
as the release is, since replies tell us which field to build next.
