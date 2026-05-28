"""
HarnessLink Stallion Directory — Premium Rate Card 2026
Generates a single A4 PDF: cinematic hero + data instrument layout.
"""

import os
import math
from reportlab.pdfgen import canvas
from reportlab.lib.pagesizes import A4
from reportlab.lib.colors import HexColor, Color
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from PIL import Image, ImageDraw, ImageFilter
import io

# ── Dimensions ──────────────────────────────────────────────────────────────
W, H = A4          # 595.28 x 841.89 pt
MARGIN = 0         # full bleed

# ── Colours ─────────────────────────────────────────────────────────────────
NAVY      = HexColor('#0E2266')
MID_NAVY  = HexColor('#1A3A8C')
WHITE     = HexColor('#FFFFFF')
WHITE_DIM = HexColor('#B0BEDD')   # lighter white for secondary labels
NAVY_TEXT = HexColor('#0E2266')

# ── Font registration ────────────────────────────────────────────────────────
FONT_DIR = '/tmp/fonts'
pdfmetrics.registerFont(TTFont('Cinzel-Bold',    f'{FONT_DIR}/Cinzel-Bold.ttf'))
pdfmetrics.registerFont(TTFont('OpenSans',       f'{FONT_DIR}/OpenSans-Regular.ttf'))
pdfmetrics.registerFont(TTFont('OpenSans-Bold',  f'{FONT_DIR}/OpenSans-Bold.ttf'))

CIN  = 'Cinzel-Bold'
SANS = 'OpenSans'
SANS_B = 'OpenSans-Bold'

# ── Helper: canvas y from top ────────────────────────────────────────────────
def cy(y_from_top):
    return H - y_from_top

def draw_rect(c, x, y_top, w, h, fill=None, stroke=None, stroke_width=1):
    c.saveState()
    if fill:
        c.setFillColor(fill)
    if stroke:
        c.setStrokeColor(stroke)
        c.setLineWidth(stroke_width)
    if fill and stroke:
        c.rect(x, cy(y_top + h), w, h, fill=1, stroke=1)
    elif fill:
        c.rect(x, cy(y_top + h), w, h, fill=1, stroke=0)
    elif stroke:
        c.rect(x, cy(y_top + h), w, h, fill=0, stroke=1)
    c.restoreState()

def text(c, txt, x, y_top, font, size, color=WHITE, align='left', max_width=None):
    c.saveState()
    c.setFont(font, size)
    c.setFillColor(color)
    if align == 'center':
        c.drawCentredString(x, cy(y_top), txt)
    elif align == 'right':
        c.drawRightString(x, cy(y_top), txt)
    else:
        c.drawString(x, cy(y_top), txt)
    c.restoreState()

def hline(c, x1, x2, y_top, color=WHITE, width=1):
    c.saveState()
    c.setStrokeColor(color)
    c.setLineWidth(width)
    c.line(x1, cy(y_top), x2, cy(y_top))
    c.restoreState()

def vline(c, x, y_top, y_bottom, color=WHITE, width=1):
    c.saveState()
    c.setStrokeColor(color)
    c.setLineWidth(width)
    c.line(x, cy(y_top), x, cy(y_bottom))
    c.restoreState()


# ── Hero image generation ────────────────────────────────────────────────────
def build_hero_image(width_px, height_px):
    """
    Creates a cinematic atmospheric hero panel: simulates a harness racing scene
    lit against a deep navy-to-black background, fading to navy at the bottom.
    """
    img = Image.new('RGB', (width_px, height_px), (14, 34, 102))
    draw = ImageDraw.Draw(img)

    # Background gradient: near-black at top, fading to navy at bottom
    for y in range(height_px):
        progress = y / height_px
        # Top: very dark (5, 8, 25), bottom: navy (14, 34, 102)
        r = int(5  + (14 - 5)   * progress)
        g = int(8  + (34 - 8)   * progress)
        b = int(25 + (102 - 25) * progress)
        draw.line([(0, y), (width_px, y)], fill=(r, g, b))

    # Atmospheric track lighting — subtle warm glow lower-center
    # suggesting track flood lights reflected on the racing surface
    for i in range(8):
        import random
        random.seed(i * 17)
        lx = int(width_px * (0.2 + 0.6 * random.random()))
        ly = int(height_px * (0.55 + 0.3 * random.random()))
        radius = int(width_px * (0.04 + 0.08 * random.random()))
        intensity = 18 + i * 4
        for dr in range(radius, 0, -1):
            alpha = int(intensity * (1 - dr / radius) ** 2)
            warm_r = min(255, 180 + alpha // 3)
            warm_g = min(255, 140 + alpha // 4)
            warm_b = min(255, 80 + alpha // 6)
            draw.ellipse(
                [lx - dr, ly - dr, lx + dr, ly + dr],
                fill=(warm_r, warm_g, warm_b),
                outline=None
            )

    # Track surface — diagonal perspective lines suggesting the racing surface
    track_y_start = int(height_px * 0.62)
    track_y_end   = height_px
    for y in range(track_y_start, track_y_end):
        t = (y - track_y_start) / (track_y_end - track_y_start)
        base_r = int(18 + 12 * t)
        base_g = int(22 + 15 * t)
        base_b = int(45 + 30 * t)
        draw.line([(0, y), (width_px, y)], fill=(base_r, base_g, base_b))

    # Motion blur streaks — suggest horse at speed
    streak_y = int(height_px * 0.40)
    for i in range(6):
        y_off  = streak_y + i * 4
        x_start = int(width_px * 0.05)
        x_end   = int(width_px * 0.55)
        alpha   = 30 - i * 4
        col     = (min(255, 120 + alpha), min(255, 110 + alpha), min(255, 80 + alpha))
        draw.line([(x_start, y_off), (x_end, y_off)], fill=col, width=2)

    # Silhouette of sulky + driver + horse (abstract shapes)
    # Horse body — dark warm mass, slightly right of center
    cx_h = int(width_px * 0.50)
    cy_h = int(height_px * 0.44)

    # Horse torso (elongated ellipse)
    horse_w, horse_h = int(width_px * 0.26), int(height_px * 0.14)
    for dy in range(-horse_h, horse_h):
        for dx in range(-horse_w, horse_w):
            if (dx / horse_w) ** 2 + (dy / horse_h) ** 2 < 1:
                px, py = cx_h + dx, cy_h + dy
                if 0 <= px < width_px and 0 <= py < height_px:
                    # Bay colour: warm dark brown
                    r = min(255, 65 + abs(dx) // 8)
                    g = min(255, 32 + abs(dx) // 10)
                    b = min(255, 12 + abs(dx) // 12)
                    draw.point((px, py), fill=(r, g, b))

    # Neck and head
    neck_x = cx_h + int(horse_w * 0.85)
    neck_y = cy_h - int(horse_h * 0.4)
    for dy in range(-int(horse_h * 0.9), int(horse_h * 0.1)):
        for dx in range(-int(horse_w * 0.07), int(horse_w * 0.10)):
            px, py = neck_x + dx, neck_y + dy
            if 0 <= px < width_px and 0 <= py < height_px:
                draw.point((px, py), fill=(55, 28, 10))

    # Driver silhouette — behind (left of) horse from our view
    driver_cx = cx_h - int(horse_w * 0.25)
    driver_cy = cy_h + int(horse_h * 0.7)
    d_w, d_h  = int(width_px * 0.06), int(height_px * 0.10)
    for dy in range(-d_h, 0):
        for dx in range(-d_w, d_w):
            if (dx / d_w) ** 2 * 0.5 + (dy / d_h) ** 2 < 1.0:
                px, py = driver_cx + dx, driver_cy + dy
                if 0 <= px < width_px and 0 <= py < height_px:
                    # Royal blue silks
                    r = int(15 + abs(dy) // 4)
                    g = int(35 + abs(dy) // 5)
                    b = int(130 + abs(dy) // 3)
                    draw.point((px, py), fill=(r, g, b))

    # Helmet
    h_cx, h_cy = driver_cx, driver_cy - d_h
    h_r = int(d_w * 0.65)
    for dy in range(-h_r, h_r):
        for dx in range(-h_r, h_r):
            if dx ** 2 + dy ** 2 < h_r ** 2:
                px, py = h_cx + dx, h_cy + dy
                if 0 <= px < width_px and 0 <= py < height_px:
                    draw.point((px, py), fill=(20, 20, 60))

    # Sulky wheels (two circles below)
    for wx_off in [-int(horse_w * 0.35), int(horse_w * 0.05)]:
        wx = driver_cx + wx_off
        wy = cy_h + int(horse_h * 1.1)
        wr = int(horse_h * 0.55)
        draw.ellipse([wx - wr, wy - wr, wx + wr, wy + wr],
                     outline=(80, 80, 120), width=3, fill=None)
        draw.ellipse([wx - wr + 3, wy - wr + 3, wx + wr - 3, wy + wr - 3],
                     outline=(50, 50, 90), width=1, fill=None)

    # Legs — motion streaks beneath horse
    for leg_i in range(4):
        lx = cx_h - int(horse_w * 0.55) + leg_i * int(horse_w * 0.36)
        ly_top = cy_h + int(horse_h * 0.7)
        ly_bot = ly_top + int(horse_h * 1.0)
        for offset in range(3):
            draw.line(
                [(lx + offset * 3, ly_top), (lx + offset * 6, ly_bot)],
                fill=(50 + offset * 10, 25 + offset * 5, 10),
                width=4
            )

    # Apply light motion blur for cinematic feel
    img = img.filter(ImageFilter.GaussianBlur(radius=1.2))

    # Bottom fade to exact navy — 40% of height
    fade_start = int(height_px * 0.58)
    for y in range(fade_start, height_px):
        t = (y - fade_start) / (height_px - fade_start)
        t = t ** 0.7   # ease
        overlay_r = int(14 * t)
        overlay_g = int(34 * t)
        overlay_b = int(102 * t)
        for x in range(width_px):
            orig = img.getpixel((x, y))
            blended = (
                int(orig[0] * (1 - t) + overlay_r),
                int(orig[1] * (1 - t) + overlay_g),
                int(orig[2] * (1 - t) + overlay_b),
            )
            img.putpixel((x, y), blended)

    return img


# ── Main generation ──────────────────────────────────────────────────────────
def generate():
    out_path = '/home/user/harnessbid/HarnessLink_RateCard_2026.pdf'
    c = canvas.Canvas(out_path, pagesize=A4)
    c.setTitle('HarnessLink Stallion Directory — Rate Card 2026')
    c.setAuthor('HarnessLink')

    # ════════════════════════════════════════════════════════════════════════
    # 1. HERO IMAGE  (top 245pt, full bleed)
    # ════════════════════════════════════════════════════════════════════════
    HERO_H = 240
    HERO_PX_W = int(W * 3)   # 3x for sharpness
    HERO_PX_H = int(HERO_H * 3)

    hero_img = build_hero_image(HERO_PX_W, HERO_PX_H)
    hero_path = '/tmp/hero_image.png'
    hero_img.save(hero_path, format='PNG')

    c.drawImage(
        hero_path, 0, cy(HERO_H), W, HERO_H,
        preserveAspectRatio=False, anchor='sw'
    )

    # Navy fill below to ensure seamless fade
    draw_rect(c, 0, HERO_H, W, 10, fill=NAVY)

    # ── Floating wordmark over fade zone ──────────────────────────────────
    WORD_Y   = 188    # vertical position of wordmark baseline
    WORD_X   = 22

    text(c, 'HARNESSLINK', WORD_X, WORD_Y, CIN, 36, WHITE, 'left')

    c.saveState()
    c.setFont(SANS, 10.5)
    c.setFillColor(WHITE_DIM)
    c._charSpace = 0
    c.drawString(WORD_X + 2, cy(WORD_Y + 16), 'Stallion Directory  |  Rate Card 2026')
    c.restoreState()

    # ════════════════════════════════════════════════════════════════════════
    # 2. PLATFORM PERFORMANCE BLOCK
    # ════════════════════════════════════════════════════════════════════════
    TOP_STATS = HERO_H + 10       # starts immediately after hero
    PAD = 16

    # Section label
    c.saveState()
    c.setFont(SANS, 8.5)
    c.setFillColor(WHITE_DIM)
    c._charSpace = 0
    c.drawString(PAD, cy(TOP_STATS + 14), 'PLATFORM PERFORMANCE  —  VERIFIED MAY 2026')
    c.restoreState()

    STATS_ROWS = [
        [
            ('539,000',  'Monthly Impressions',  '+39% vs prior period'),
            ('118,000',  'Active Users',          '+48.8% vs prior period'),
            ('147,000',  'Sessions',              '+38.4% vs prior period'),
        ],
        [
            ('10.1M',    'Total Backlinks',        'Domain Authority Score: 32'),
            ('4,100+',   'Referring Domains',      '+50% year on year'),
            ('19,700',   'Organic Keywords',       'Global long-tail footprint'),
        ],
    ]

    STAT_Y = TOP_STATS + 18   # top of stat boxes
    STAT_ROW_H = 50
    COL_W = W / 3

    for row_i, row in enumerate(STATS_ROWS):
        box_top = STAT_Y + row_i * (STAT_ROW_H + 1)
        draw_rect(c, 0, box_top, W, STAT_ROW_H, fill=MID_NAVY)

        for col_i, (number, label, growth) in enumerate(row):
            cx_box = col_i * COL_W + COL_W / 2
            base_y = box_top + 14

            # Number — Cinzel Bold 38pt
            c.saveState()
            c.setFont(CIN, 36)
            c.setFillColor(WHITE)
            c.drawCentredString(cx_box, cy(base_y + 30), number)
            c.restoreState()

            # Label — OpenSans 9.5pt
            c.saveState()
            c.setFont(SANS, 9.5)
            c.setFillColor(WHITE)
            c.drawCentredString(cx_box, cy(base_y + 42), label)
            c.restoreState()

            # Growth — OpenSans 8.5pt dimmer
            c.saveState()
            c.setFont(SANS, 8.5)
            c.setFillColor(WHITE_DIM)
            c.drawCentredString(cx_box, cy(base_y + 52), growth)
            c.restoreState()

            # Vertical divider (not after last col)
            if col_i < 2:
                vline(c, (col_i + 1) * COL_W, box_top + 6, box_top + STAT_ROW_H - 6, WHITE, 0.5)

    # ════════════════════════════════════════════════════════════════════════
    # 3. VIRAL PROOF STRIP
    # ════════════════════════════════════════════════════════════════════════
    VIRAL_Y = STAT_Y + 2 * (STAT_ROW_H + 1) + 3
    VIRAL_H  = 39
    draw_rect(c, 0, VIRAL_Y, W, VIRAL_H, fill=NAVY)

    # 2px left white border
    vline(c, 2, VIRAL_Y + 6, VIRAL_Y + VIRAL_H - 6, WHITE, 2)

    viral_text = (
        'One editorial story drove 22,040 active users in a single day.  '
        '826% above daily baseline.  Every stud on this platform benefits from this reach.'
    )
    c.saveState()
    c.setFont(SANS, 9.5)
    c.setFillColor(WHITE)
    c.drawString(18, cy(VIRAL_Y + VIRAL_H / 2 + 4), viral_text)
    c.restoreState()

    # ════════════════════════════════════════════════════════════════════════
    # 4. GLOBAL AUDIENCE STRIP
    # ════════════════════════════════════════════════════════════════════════
    AUD_Y = VIRAL_Y + VIRAL_H
    AUD_H = 55
    draw_rect(c, 0, AUD_Y, W, AUD_H, fill=WHITE)

    # Label
    c.saveState()
    c.setFont(SANS, 8)
    c.setFillColor(NAVY_TEXT)
    c._charSpace = 0
    c.drawString(PAD, cy(AUD_Y + 12), 'GLOBAL REACH  —  VERIFIED AUDIENCE')
    c.restoreState()

    regions = [
        ('Asia-Pacific',      'Primary Market',      'AU  NZ  Singapore  Vietnam'),
        ('North America',     'Growing Market',       'USA  Canada'),
        ('Europe',            'Emerging Market',      'UK  Ireland  Scandinavia'),
        ('Digital-First',     '97,000 direct returning users', 'Every month, without paid acquisition'),
    ]

    RCW = W / 4
    for i, (name, market, detail) in enumerate(regions):
        rx = i * RCW + RCW / 2
        # Region name bold
        c.saveState()
        c.setFont(SANS_B, 9.5)
        c.setFillColor(NAVY_TEXT)
        c.drawCentredString(rx, cy(AUD_Y + 27), name)
        c.restoreState()
        # Market label
        c.saveState()
        c.setFont(SANS, 8.5)
        c.setFillColor(NAVY_TEXT)
        c.drawCentredString(rx, cy(AUD_Y + 38), market)
        c.restoreState()
        # Detail
        c.saveState()
        c.setFont(SANS, 8)
        c.setFillColor(HexColor('#4A6098'))
        c.drawCentredString(rx, cy(AUD_Y + 49), detail)
        c.restoreState()
        # Dividers
        if i < 3:
            vline(c, (i + 1) * RCW, AUD_Y + 18, AUD_Y + AUD_H - 6, NAVY_TEXT, 0.5)

    # ════════════════════════════════════════════════════════════════════════
    # 5. PRICING TIERS
    # ════════════════════════════════════════════════════════════════════════
    TIER_Y  = AUD_Y + AUD_H + 3
    TIER_H  = 165
    TIER_W  = W / 3
    draw_rect(c, 0, TIER_Y, W, TIER_H, fill=NAVY)

    # ── Column backgrounds ────────────────────────────────────────────────
    # Left: mid navy
    draw_rect(c, 0,          TIER_Y, TIER_W,     TIER_H, fill=MID_NAVY)
    # Middle: white
    draw_rect(c, TIER_W,     TIER_Y, TIER_W,     TIER_H, fill=WHITE)
    # Right: deep navy
    draw_rect(c, TIER_W * 2, TIER_Y, TIER_W,     TIER_H, fill=NAVY)

    # Vertical 1px white dividers
    vline(c, TIER_W,     TIER_Y, TIER_Y + TIER_H, WHITE, 1)
    vline(c, TIER_W * 2, TIER_Y, TIER_Y + TIER_H, WHITE, 1)

    # ────────────────────────────────────────────────────────────────────
    # LEFT: STANDARD LISTING
    # ────────────────────────────────────────────────────────────────────
    lp = 14   # left pad inside column
    ty = TIER_Y + 16

    c.saveState()
    c.setFont(CIN, 11.5)
    c.setFillColor(WHITE)
    c.drawString(lp, cy(ty), 'STANDARD LISTING')
    c.restoreState()
    ty += 18

    # Price line
    c.saveState()
    c.setFont(CIN, 26)
    c.setFillColor(WHITE)
    c.drawString(lp, cy(ty), 'From $450')
    c.restoreState()
    c.saveState()
    c.setFont(SANS, 9)
    c.setFillColor(WHITE_DIM)
    c.drawString(lp + 120, cy(ty), '/year')
    c.restoreState()
    ty += 10

    hline(c, lp, TIER_W - lp, ty, WHITE, 0.5)
    ty += 10

    vol = [
        '1-3 Stallions  —  $550 per stallion per year',
        '4-7 Stallions  —  $500 per stallion per year',
        '8+  Stallions  —  $450 per stallion per year',
    ]
    for line in vol:
        c.saveState()
        c.setFont(SANS, 8.8)
        c.setFillColor(WHITE)
        c.drawString(lp, cy(ty), line)
        c.restoreState()
        ty += 11
    ty += 2

    hline(c, lp, TIER_W - lp, ty, WHITE, 0.5)
    ty += 10

    feats = [
        'Directory listing',
        'Full contact details visible',
        'Profile page',
        '539,000 monthly impressions',
    ]
    for feat in feats:
        c.saveState()
        c.setFont(SANS, 8.8)
        c.setFillColor(WHITE)
        c.drawString(lp, cy(ty), f'—  {feat}')
        c.restoreState()
        ty += 11

    # CPM line at bottom of card
    cpm_y = TIER_Y + TIER_H - 14
    c.saveState()
    c.setFont(SANS, 7.5)
    c.setFillColor(WHITE_DIM)
    c.drawString(lp, cy(cpm_y), 'CPM equivalent $0.09  ·  Industry standard $10–$25')
    c.restoreState()

    # ────────────────────────────────────────────────────────────────────
    # MIDDLE: PARTNERING STUD
    # ────────────────────────────────────────────────────────────────────
    mx  = TIER_W
    lpm = mx + 14
    mty = TIER_Y + 11

    # MOST POPULAR banner
    c.saveState()
    c.setFont(SANS_B, 7)
    c.setFillColor(NAVY_TEXT)
    c._charSpace = 0
    c.drawCentredString(mx + TIER_W / 2, cy(mty), 'MOST POPULAR')
    c.restoreState()
    mty += 14

    c.saveState()
    c.setFont(CIN, 11.5)
    c.setFillColor(NAVY_TEXT)
    c.drawString(lpm, cy(mty), 'PARTNERING STUD')
    c.restoreState()
    mty += 18

    c.saveState()
    c.setFont(CIN, 26)
    c.setFillColor(NAVY_TEXT)
    c.drawString(lpm, cy(mty), '$3,000')
    c.restoreState()
    c.saveState()
    c.setFont(SANS, 9)
    c.setFillColor(HexColor('#4A6098'))
    c.drawString(lpm + 86, cy(mty), '/year  ·  $250/month')
    c.restoreState()
    mty += 10

    hline(c, lpm, mx + TIER_W - 14, mty, NAVY_TEXT, 0.5)
    mty += 10

    mid_feats = [
        'Branded banner top of directory',
        'Premium alphabetical stallion table',
        'Above all standard listings',
        'Progeny auto-linking across all editorial',
        '5 regional slots  —  AU/NZ  ·  US/CA  ·  Europe',
    ]
    for feat in mid_feats:
        c.saveState()
        c.setFont(SANS, 8.8)
        c.setFillColor(NAVY_TEXT)
        c.drawString(lpm, cy(mty), f'—  {feat}')
        c.restoreState()
        mty += 11

    geo_y = TIER_Y + TIER_H - 14
    c.saveState()
    c.setFont(SANS, 7.5)
    c.setFillColor(HexColor('#4A6098'))
    c.drawString(lpm, cy(geo_y), 'Geo-tagged per region  ·  Once filled, closed')
    c.restoreState()

    # ────────────────────────────────────────────────────────────────────
    # RIGHT: PREMIUM PARTNER
    # ────────────────────────────────────────────────────────────────────
    rx2 = TIER_W * 2
    lpr = rx2 + 14
    rty = TIER_Y + 16

    c.saveState()
    c.setFont(CIN, 11.5)
    c.setFillColor(WHITE)
    c.drawString(lpr, cy(rty), 'PREMIUM PARTNER')
    c.restoreState()
    rty += 18

    prem_prices = [
        '1-3 Stallions  —  $5,000/year',
        '4-7 Stallions  —  $6,000/year',
        '8+  Stallions  —  $7,000/year',
    ]
    for line in prem_prices:
        c.saveState()
        c.setFont(SANS, 8.8)
        c.setFillColor(WHITE)
        c.drawString(lpr, cy(rty), line)
        c.restoreState()
        rty += 11
    rty += 2

    hline(c, lpr, rx2 + TIER_W - 14, rty, WHITE, 0.5)
    rty += 10

    prem_feats = [
        'Everything in Partnering Stud',
        'Header banner above all listings',
        'Top of premium alphabetical table',
        'One dedicated feature article per stallion',
        'SEO optimised  ·  Permanently live',
        'Published across all social channels',
    ]
    for feat in prem_feats:
        c.saveState()
        c.setFont(SANS, 8.8)
        c.setFillColor(WHITE)
        c.drawString(lpr, cy(rty), f'—  {feat}')
        c.restoreState()
        rty += 11

    # ════════════════════════════════════════════════════════════════════════
    # 6. PROGENY AUTO-LINK PANEL
    # ════════════════════════════════════════════════════════════════════════
    PRG_Y = TIER_Y + TIER_H + 3
    PRG_H = 47
    draw_rect(c, 0, PRG_Y, W, PRG_H, fill=WHITE)

    # 2px navy left border
    vline(c, 2, PRG_Y + 6, PRG_Y + PRG_H - 6, NAVY_TEXT, 2)

    c.saveState()
    c.setFont(CIN, 11.5)
    c.setFillColor(NAVY_TEXT)
    c.drawString(18, cy(PRG_Y + 17), 'THE PROGENY AUTO-LINK')
    c.restoreState()

    prg_body = (
        'Every race result, news story and breeding update on Harnesslink referencing your stallion\'s offspring '
        'automatically hyperlinks to your directory page.'
    )
    prg_body2 = (
        'A sire mentioned 30 times a month generates 30 live inbound referrals. '
        'No action required. Built into the platform.'
    )
    c.saveState()
    c.setFont(SANS, 8.8)
    c.setFillColor(NAVY_TEXT)
    c.drawString(18, cy(PRG_Y + 30), prg_body)
    c.drawString(18, cy(PRG_Y + 41), prg_body2)
    c.restoreState()

    # ════════════════════════════════════════════════════════════════════════
    # 7. COMPARISON TABLE
    # ════════════════════════════════════════════════════════════════════════
    TBL_Y  = PRG_Y + PRG_H + 3
    ROW_H  = 11.5
    COL_WIDTHS = [W * 0.42, W * 0.19, W * 0.19, W * 0.20]
    COL_X  = [0, COL_WIDTHS[0], COL_WIDTHS[0] + COL_WIDTHS[1], COL_WIDTHS[0] + COL_WIDTHS[1] + COL_WIDTHS[2]]

    HEADERS = ['FEATURE', 'STANDARD', 'PARTNERING STUD', 'PREMIUM PARTNER']

    # Header row
    draw_rect(c, 0, TBL_Y, W, ROW_H, fill=NAVY)
    for i, hdr in enumerate(HEADERS):
        align = 'left' if i == 0 else 'center'
        x_pos = COL_X[i] + (10 if i == 0 else COL_WIDTHS[i] / 2 + COL_X[i] if i > 0 else 0)
        if align == 'center':
            c.saveState()
            c.setFont(SANS_B, 8.5)
            c.setFillColor(WHITE)
            c.drawCentredString(COL_X[i] + COL_WIDTHS[i] / 2, cy(TBL_Y + ROW_H - 4), hdr)
            c.restoreState()
        else:
            c.saveState()
            c.setFont(SANS_B, 8.5)
            c.setFillColor(WHITE)
            c.drawString(COL_X[i] + 10, cy(TBL_Y + ROW_H - 4), hdr)
            c.restoreState()

    # Table rows
    TABLE_ROWS = [
        ('Directory listing + contact details', True,  True,  True),
        ('Volume pricing',                      True,  False, False),
        ('Branded banner — top of page',        False, True,  True),
        ('Premium alphabetical table',          False, True,  True),
        ('Progeny auto-linking',                False, True,  True),
        ('Header banner',                       False, False, True),
        ('Top of premium table',                False, False, True),
        ('Feature article per stallion',        False, False, True),
        ('Annual cost — 5 stallions',           '$2,500', '$3,000', '$6,000'),
    ]

    for row_i, row_data in enumerate(TABLE_ROWS):
        ry = TBL_Y + ROW_H * (row_i + 1)
        is_last = (row_i == len(TABLE_ROWS) - 1)

        if is_last:
            bg = NAVY
            fg = WHITE
        elif row_i % 2 == 0:
            bg = WHITE
            fg = NAVY_TEXT
        else:
            bg = MID_NAVY
            fg = WHITE

        draw_rect(c, 0, ry, W, ROW_H, fill=bg)

        feature = row_data[0]
        vals    = row_data[1:]

        # Feature label
        c.saveState()
        c.setFont(SANS_B if is_last else SANS, 8.5)
        c.setFillColor(fg)
        c.drawString(COL_X[0] + 10, cy(ry + ROW_H - 4), feature)
        c.restoreState()

        # Values
        for col_i, val in enumerate(vals):
            cx_col = COL_X[col_i + 1] + COL_WIDTHS[col_i + 1] / 2
            if isinstance(val, bool):
                sym  = '✓' if val else '—'
                font = SANS_B if val else SANS
                col  = fg if not is_last else WHITE
                dim  = fg if val else (HexColor('#8899CC') if fg == WHITE else HexColor('#8899CC'))
                c.saveState()
                c.setFont(font, 9)
                c.setFillColor(col if val else dim)
                c.drawCentredString(cx_col, cy(ry + ROW_H - 4), sym)
                c.restoreState()
            else:
                c.saveState()
                c.setFont(SANS_B if is_last else SANS_B, 8.5)
                c.setFillColor(fg)
                c.drawCentredString(cx_col, cy(ry + ROW_H - 4), val)
                c.restoreState()

    TBL_BOTTOM = TBL_Y + ROW_H * (len(TABLE_ROWS) + 1)

    # ════════════════════════════════════════════════════════════════════════
    # 8. FOOTER
    # ════════════════════════════════════════════════════════════════════════
    FTR_Y = TBL_BOTTOM + 3
    FTR_H = H - FTR_Y

    draw_rect(c, 0, FTR_Y, W, FTR_H, fill=NAVY)
    hline(c, 0, W, FTR_Y, WHITE, 1)

    fc_y = FTR_Y + FTR_H / 2 - 8
    c.saveState()
    c.setFont(SANS, 9.5)
    c.setFillColor(WHITE)
    c.drawCentredString(W / 2, cy(fc_y), 'brendan@harnesslink.com  ·  www.harnesslink.com  ·  +61 423 233 288')
    c.restoreState()

    c.saveState()
    c.setFont(SANS, 8)
    c.setFillColor(WHITE_DIM)
    c.drawCentredString(W / 2, cy(fc_y + 14), 'Reaching over one million users annually across the global harness racing community')
    c.restoreState()

    c.save()
    print(f'Generated: {out_path}')
    return out_path


if __name__ == '__main__':
    generate()
