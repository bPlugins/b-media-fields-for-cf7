Landing page
============

Sales landing page for Media Fields for Contact Form 7. Not shipped in the
release zip (see .distignore).

  index.html    the whole page, self contained apart from Google Fonts
  assets/       hero thumbnail, five field shots, two admin screens, icon

Open index.html in a browser to preview it. Nothing to build.


READ THIS FIRST: which design did I follow
------------------------------------------
You asked for the bPlugins v2 design system. I do not have it. Whatever we
agreed in that other session is not in my memory here, so I did not guess at
it.

Instead I rebuilt the design language from the reference you gave me, the
live page at https://bplugins.com/products/3d-viewer/, by reading its
computed styles:

  Headings      Space Grotesk 700, #070127, tight negative tracking
  Body          Inter, 15 to 17px, #485781
  Primary CTA   #146EF5, white text, ~7.5px radius
  Accent CTA    #FF7A00, same shape
  Section bands white, rgba(65,111,244,.05) tint, and #070127 for the dark one
  Hero          light gradient with a faint grid, copy left, media right,
                two CTAs, then a facts strip

The section order also mirrors that page: hero, facts, why it matters,
feature rows, how it works, feature grid, dark social proof band, FAQ,
closing CTA.

If v2 differs from the live 3D Viewer page, tell me what changed and I will
redo it. The page is one HTML file with all tokens in :root at the top, so
recolouring or retyping it is a small job.


What is deliberately not on the page
------------------------------------
No testimonials and no usage numbers. The plugin is days old, so there are no
real reviews and no install count worth quoting, and inventing either would
be dishonest to visitors and embarrassing later. The dark band is left as
three clearly marked placeholder cards with an HTML comment explaining what
to do. Replace them with real WordPress.org reviews when they arrive, or
delete the whole section until you have at least two.

The facts strip uses things that are true today: five field types, free,
no code needed, no third party calls.

There is also no pricing section, because the plugin is free. If a pro
version appears later, that is where it goes, between the FAQ and the
closing CTA.


Before publishing
-----------------
  [ ] Decide the testimonial section: real quotes, or remove it
  [ ] Point the nav and footer links at your real URLs if they change
  [ ] If your site self hosts Space Grotesk and Inter, drop the Google Fonts
      link and use your own font stack, so there is no extra third party call
  [ ] Compress assets/hero-video-thumb.png if you care about the last few
      hundred KB, it is the heaviest file on the page
  [ ] Add your real header and footer when porting this into the site
