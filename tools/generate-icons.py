"""Generate CariKaryawan's raster icons. Run: python tools/generate-icons.py (Pillow)."""

from pathlib import Path

from PIL import Image, ImageDraw


PUBLIC = Path(__file__).resolve().parents[1] / "public"
SIZES = {
    "favicon-16x16.png": 16,
    "favicon-32x32.png": 32,
    "favicon-48x48.png": 48,
    "apple-touch-icon.png": 180,
    "android-chrome-192x192.png": 192,
    "android-chrome-512x512.png": 512,
}

# Geometry mirrors public/favicon.svg; supersampling keeps small icons crisp.
SCALE = 4
image = Image.new("RGBA", (512 * SCALE, 512 * SCALE))
draw = ImageDraw.Draw(image)


def box(coords):
    return tuple(round(value * SCALE) for value in coords)


draw.rounded_rectangle(box((0, 0, 512, 512)), radius=112 * SCALE, fill="#18181b")
# Pillow arcs sit inside their bounding box, so expand by half the SVG stroke.
draw.arc(box((94, 78, 386, 370)), 45, 315, fill="#fafaf9", width=36 * SCALE)
for x, y in ((330.51, 314.51), (330.51, 133.49)):
    draw.ellipse(box((x - 18, y - 18, x + 18, y + 18)), fill="#fafaf9")
draw.line(box((336, 320, 392, 376)), fill="#fbbf24", width=40 * SCALE)
for x, y in ((336, 320), (392, 376)):
    draw.ellipse(box((x - 20, y - 20, x + 20, y + 20)), fill="#fbbf24")
draw.ellipse(box((212, 177, 268, 233)), fill="#fbbf24")
draw.rounded_rectangle(box((190, 250, 290, 314)), radius=32 * SCALE, fill="#fbbf24")

# Inspect existing images before replacing them; reject corrupt/non-image targets.
for name in (*SIZES, "favicon.ico"):
    target = PUBLIC / name
    if target.exists():
        with Image.open(target) as existing:
            existing.verify()

for name, size in SIZES.items():
    image.resize((size, size), Image.Resampling.LANCZOS).save(PUBLIC / name)
image.resize((48, 48), Image.Resampling.LANCZOS).save(
    PUBLIC / "favicon.ico", sizes=[(16, 16), (32, 32), (48, 48)]
)

for name, size in SIZES.items():
    with Image.open(PUBLIC / name) as icon:
        assert icon.size == (size, size), name
        assert icon.getpixel((size // 2, size // 2))[3] == 255, name
with Image.open(PUBLIC / "favicon.ico") as icon:
    assert icon.ico.sizes() == {(16, 16), (32, 32), (48, 48)}
print("Generated and checked SVG-compatible PNG icons and multi-size ICO.")
