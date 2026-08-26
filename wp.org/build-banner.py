import base64, sys, os

FONTS = 'fonts/'
LOGO = "/Users/bplugins/shared_space/wp.org /banners-svg/rejected/logo-2-dark.svg"
OUT = '/Users/bplugins/Studio/contact-from-7-addons/wp-content/plugins/b-media-fields-for-cf7/wp.org/banner.svg'

b64 = lambda p: base64.b64encode(open(p, 'rb').read()).decode()
bold = b64(FONTS + 'space-grotesk-bold.ttf')
medium = b64(FONTS + 'space-grotesk-medium.ttf')
logo = b64(LOGO)

# Tunables (pass 2 rewrites these from measured text).
HERO_SIZE = float(sys.argv[1]) if len(sys.argv) > 1 else 88
PILL_W = float(sys.argv[2]) if len(sys.argv) > 2 else 492
SUB_SIZE = float(sys.argv[3]) if len(sys.argv) > 3 else 28

svg = f'''<svg xmlns="http://www.w3.org/2000/svg" width="1544" height="500" viewBox="0 0 1544 500">
  <!--
    Media Fields for Contact Form 7 — wordpress.org banner (1544x500).
    bPlugins brand: Space Grotesk, #146EF5 blue, #FF7A00 orange, #070127 navy.
  -->
  <defs>
    <style>
      @font-face {{
        font-family: "Space Grotesk";
        font-weight: 700;
        src: url(data:font/ttf;base64,{bold}) format("truetype");
      }}
      @font-face {{
        font-family: "Space Grotesk";
        font-weight: 500;
        src: url(data:font/ttf;base64,{medium}) format("truetype");
      }}
      .sg {{ font-family: "Space Grotesk", "Inter", system-ui, sans-serif; }}
    </style>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#FFFFFF"/>
      <stop offset="0.55" stop-color="#F2F7FF"/>
      <stop offset="1" stop-color="#E2ECFD"/>
    </linearGradient>
    <radialGradient id="glow" cx="0.5" cy="0.5" r="0.5">
      <stop offset="0" stop-color="#146EF5" stop-opacity="0.14"/>
      <stop offset="1" stop-color="#146EF5" stop-opacity="0"/>
    </radialGradient>
    <linearGradient id="tile" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#146EF5"/>
      <stop offset="1" stop-color="#0B3FA8"/>
    </linearGradient>
    <linearGradient id="strip" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#EAF1FE"/>
      <stop offset="1" stop-color="#D9E6FD"/>
    </linearGradient>
    <filter id="shadow" x="-25%" y="-25%" width="150%" height="150%">
      <feDropShadow dx="0" dy="20" stdDeviation="22" flood-color="#0B3FA8" flood-opacity="0.28"/>
    </filter>
  </defs>

  <rect width="1544" height="500" fill="url(#bg)"/>
  <circle cx="300" cy="250" r="300" fill="url(#glow)"/>

  <!-- decorative shapes -->
  <g fill="#146EF5" fill-opacity="0.06">
    <circle cx="1400" cy="60" r="110"/>
    <circle cx="1470" cy="430" r="150"/>
  </g>
  <g fill="#FF7A00" fill-opacity="0.07">
    <circle cx="1230" cy="450" r="60"/>
  </g>

  <!-- ICON ZONE -->
  <g transform="translate(140,90)" filter="url(#shadow)">
    <rect width="320" height="320" fill="url(#tile)"/>
    <g transform="scale(1.25)">
      <rect x="32" y="44" width="192" height="112" fill="#ffffff"/>
      <path d="M116 64 L152 86 L116 108 Z" fill="#146EF5"/>
      <g fill="#FF7A00">
        <rect x="105" y="128" width="7" height="12"/>
        <rect x="118" y="120" width="7" height="20"/>
        <rect x="131" y="124" width="7" height="16"/>
        <rect x="144" y="116" width="7" height="24"/>
      </g>
      <rect x="32" y="172" width="192" height="16" fill="#ffffff" fill-opacity="0.92"/>
      <rect x="32" y="200" width="128" height="16" fill="#ffffff" fill-opacity="0.55"/>
      <rect x="172" y="200" width="52" height="16" fill="#FF7A00"/>
    </g>
  </g>

  <!-- MESSAGE ZONE -->
  <g class="sg">
    <rect id="pill" x="560" y="100" width="{PILL_W}" height="46" fill="#146EF5" fill-opacity="0.10"/>
    <text id="pill-text" x="{560 + PILL_W / 2}" y="130" text-anchor="middle" font-size="20" font-weight="700" letter-spacing="3" fill="#0B3FA8">MEDIA FIELDS FOR CONTACT FORM 7</text>

    <text id="hero1" x="560" y="240" font-size="{HERO_SIZE}" font-weight="700" fill="#070127" letter-spacing="-2">Put media inside</text>
    <text id="hero2" x="560" y="328" font-size="{HERO_SIZE}" font-weight="700" fill="#070127" letter-spacing="-2">your forms.</text>

    <rect x="562" y="366" width="120" height="6" fill="#FF7A00"/>
    <text id="sub" x="562" y="406" font-size="{SUB_SIZE}" font-weight="500" fill="#485781">Video, audio &amp; 3D model fields for Contact Form 7</text>
  </g>

  <!-- bPlugins logo (dark version for the light background) -->
  <image x="1294" y="418" width="210" height="42" href="data:image/svg+xml;base64,{logo}"/>
</svg>
'''
open(OUT, 'w').write(svg)
print('written', len(svg), 'bytes ->', OUT)
