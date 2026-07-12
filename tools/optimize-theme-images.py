from __future__ import annotations

from pathlib import Path

from PIL import Image


ROOT = Path(__file__).resolve().parents[1]
IMAGE_ROOT = ROOT / "wordpress" / "wp-content" / "themes" / "statek-cholupice" / "assets" / "images"
OUTPUT_ROOT = IMAGE_ROOT / "optimized"
REPORT = ROOT / "docs" / "ASSET-REPORT.md"
WIDTHS = (960, 1280, 1920)
EXTENSIONS = {".jpg", ".jpeg", ".png", ".webp"}


def human_size(size: int) -> str:
    value = float(size)
    for unit in ("B", "KB", "MB"):
        if value < 1024 or unit == "MB":
            return f"{value:.1f} {unit}" if unit != "B" else f"{int(value)} B"
        value /= 1024
    return f"{value:.1f} MB"


def should_skip(path: Path) -> bool:
    if OUTPUT_ROOT in path.parents:
        return True
    return path.suffix.lower() not in EXTENSIONS


def resize_image(image: Image.Image, width: int) -> Image.Image:
    if image.width <= width:
        return image.copy()
    height = round(image.height * width / image.width)
    return image.resize((width, height), Image.Resampling.LANCZOS)


rows: list[tuple[str, str, str, int]] = []
OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)

for source in sorted(IMAGE_ROOT.rglob("*")):
    if not source.is_file() or should_skip(source):
        continue

    relative = source.relative_to(IMAGE_ROOT)
    stem_path = relative.with_suffix("")

    with Image.open(source) as image:
        image = image.convert("RGB")
        target_widths = [width for width in WIDTHS if width < image.width]
        if not target_widths:
            target_widths = [image.width]
        elif image.width not in target_widths and image.width <= max(WIDTHS):
            target_widths.append(image.width)

        for width in sorted(set(target_widths)):
            optimized = resize_image(image, width)
            target = OUTPUT_ROOT / stem_path.parent / f"{stem_path.name}-{width}.webp"
            target.parent.mkdir(parents=True, exist_ok=True)
            optimized.save(target, "WEBP", quality=84, method=6)
            rows.append((str(relative).replace("\\", "/"), str(target.relative_to(IMAGE_ROOT)).replace("\\", "/"), human_size(source.stat().st_size), target.stat().st_size))

summary = [
    "# Asset report",
    "",
    "Originální schválené vizualizace zůstávají v balíčku beze změny. Vedle nich jsou připravené lehčí WebP varianty pro produkční nasazení nebo následné napojení přes WordPress media pipeline.",
    "",
    "| Zdroj | WebP varianta | Původní velikost | WebP velikost |",
    "| --- | --- | ---: | ---: |",
]

for source, target, original_size, optimized_size in rows:
    summary.append(f"| `{source}` | `{target}` | {original_size} | {human_size(optimized_size)} |")

REPORT.write_text("\n".join(summary) + "\n", encoding="utf-8")
print(f"Created {len(rows)} optimized image variants.")
