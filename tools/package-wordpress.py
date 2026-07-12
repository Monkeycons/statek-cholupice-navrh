from __future__ import annotations

import shutil
import zipfile
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
THEME_ROOT = ROOT / "wordpress" / "wp-content" / "themes" / "statek-cholupice"
PLUGIN_ROOT = ROOT / "wordpress" / "wp-content" / "plugins" / "statek-cholupice-core"
DIST_ROOT = ROOT / "dist"
TEST_ROOT = DIST_ROOT / "zip-test"
MANIFEST = DIST_ROOT / "ZIP-MANIFEST.txt"


def should_skip_theme_file(path: Path) -> bool:
    rel = path.relative_to(THEME_ROOT).as_posix()
    if rel.startswith("assets/images/optimized/"):
        return False
    if rel.startswith("assets/images/statek_web_premium/"):
        return True
    if rel.startswith("assets/images/hero_vizualizace/"):
        return True
    if rel == "assets/images/karousel3_premium_dss_v4_4k_preview.jpg":
        return True
    return False


def write_zip(source_root: Path, package_name: str, target: Path, skip=None) -> list[str]:
    entries: list[str] = []
    if target.exists():
        target.unlink()
    with zipfile.ZipFile(target, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
        for path in sorted(source_root.rglob("*")):
            if not path.is_file():
                continue
            if skip and skip(path):
                continue
            arcname = f"{package_name}/{path.relative_to(source_root).as_posix()}"
            archive.write(path, arcname)
            entries.append(arcname)
    return entries


def test_extract(zip_path: Path, expected_root: str) -> None:
    target = TEST_ROOT / zip_path.stem
    if target.exists():
        shutil.rmtree(target)
    target.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(zip_path) as archive:
        names = archive.namelist()
        if not names:
            raise RuntimeError(f"{zip_path.name} is empty")
        for name in names:
            if "\\" in name:
                raise RuntimeError(f"{zip_path.name} contains a backslash entry: {name}")
            if name.startswith("/") or ":" in name:
                raise RuntimeError(f"{zip_path.name} contains an absolute-looking entry: {name}")
            if not name.startswith(expected_root + "/"):
                raise RuntimeError(f"{zip_path.name} contains an entry outside {expected_root}: {name}")
        archive.extractall(target)
    if not (target / expected_root).exists():
        raise RuntimeError(f"{zip_path.name} did not extract to {expected_root}")


def main() -> None:
    DIST_ROOT.mkdir(parents=True, exist_ok=True)
    if TEST_ROOT.exists():
        shutil.rmtree(TEST_ROOT)

    theme_zip = DIST_ROOT / "statek-cholupice-theme.zip"
    plugin_zip = DIST_ROOT / "statek-cholupice-core.zip"

    theme_entries = write_zip(THEME_ROOT, "statek-cholupice", theme_zip, should_skip_theme_file)
    plugin_entries = write_zip(PLUGIN_ROOT, "statek-cholupice-core", plugin_zip)

    test_extract(theme_zip, "statek-cholupice")
    test_extract(plugin_zip, "statek-cholupice-core")

    lines = [
        "WordPress ZIP manifest",
        "",
        f"{theme_zip.name}: {len(theme_entries)} entries",
        *[f"  {entry}" for entry in theme_entries[:80]],
        "  ..." if len(theme_entries) > 80 else "",
        "",
        f"{plugin_zip.name}: {len(plugin_entries)} entries",
        *[f"  {entry}" for entry in plugin_entries],
        "",
        "Extraction test: OK",
        "ZIP paths: forward slashes only, one root folder, no absolute paths.",
    ]
    MANIFEST.write_text("\n".join(line for line in lines if line != "") + "\n", encoding="utf-8")
    print(f"Created {theme_zip}")
    print(f"Created {plugin_zip}")
    print(f"Wrote {MANIFEST}")


if __name__ == "__main__":
    main()
