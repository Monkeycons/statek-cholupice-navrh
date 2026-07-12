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


rows: list[tuple[str, str, str, int, str]] = []
OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
supports_avif = "AVIF" in Image.registered_extensions().values()

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
            target_dir = OUTPUT_ROOT / stem_path.parent
            target_dir.mkdir(parents=True, exist_ok=True)

            jpg_target = target_dir / f"{stem_path.name}-{width}.jpg"
            optimized.save(jpg_target, "JPEG", quality=86, optimize=True, progressive=True)
            rows.append((str(relative).replace("\\", "/"), str(jpg_target.relative_to(IMAGE_ROOT)).replace("\\", "/"), human_size(source.stat().st_size), jpg_target.stat().st_size, "JPEG fallback"))

            webp_target = target_dir / f"{stem_path.name}-{width}.webp"
            optimized.save(webp_target, "WEBP", quality=84, method=6)
            rows.append((str(relative).replace("\\", "/"), str(webp_target.relative_to(IMAGE_ROOT)).replace("\\", "/"), human_size(source.stat().st_size), webp_target.stat().st_size, "WebP"))

            if supports_avif:
                avif_target = target_dir / f"{stem_path.name}-{width}.avif"
                optimized.save(avif_target, "AVIF", quality=52)
                rows.append((str(relative).replace("\\", "/"), str(avif_target.relative_to(IMAGE_ROOT)).replace("\\", "/"), human_size(source.stat().st_size), avif_target.stat().st_size, "AVIF"))

summary = [
    "# Asset report",
    "",
    "Produkční šablona je napojená na responzivní `<picture>` výstup. Pro každou použitou vizualizaci vzniká JPEG fallback, WebP varianta a podle podpory knihovny také AVIF.",
    "",
    f"AVIF podpora v tomto běhu: {'ano' if supports_avif else 'ne'}.",
    "",
    "| Zdroj | Varianta | Formát | Původní velikost | Velikost varianty |",
    "| --- | --- | --- | ---: | ---: |",
]

for source, target, original_size, optimized_size, label in rows:
    summary.append(f"| `{source}` | `{target}` | {label} | {original_size} | {human_size(optimized_size)} |")

REPORT.write_text("\n".join(summary) + "\n", encoding="utf-8")
print(f"Created {len(rows)} optimized image variants.")
