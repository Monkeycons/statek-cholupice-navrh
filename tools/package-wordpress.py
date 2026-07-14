from __future__ import annotations

import re
import shutil
import zipfile
from pathlib import Path, PurePosixPath


ROOT = Path(__file__).resolve().parents[1]
THEME_ROOT = ROOT / "wordpress" / "wp-content" / "themes" / "statek-cholupice"
PLUGIN_ROOT = ROOT / "wordpress" / "wp-content" / "plugins" / "statek-cholupice-core"
DIST_ROOT = ROOT / "dist"
TEST_ROOT = DIST_ROOT / "zip-test"
MANIFEST = DIST_ROOT / "ZIP-MANIFEST.txt"
EXPECTED_THEME_VERSION = "1.1.0-rc.4"
EXPECTED_PLUGIN_VERSION = "1.1.0-rc.3"


def package_file_policy(path: Path, source_root: Path) -> str:
    rel = path.relative_to(source_root)
    parts = tuple(part.lower() for part in rel.parts)
    name = path.name.lower()

    if "node_modules" in parts or "__pycache__" in parts:
        return "skip"
    if name in {".ds_store", "thumbs.db"} or path.suffix.lower() in {
        ".bak",
        ".log",
        ".orig",
        ".swp",
        ".tmp",
    }:
        return "skip"
    if (
        name == ".env"
        or name.startswith(".env.")
        or name in {"id_rsa", "id_ed25519"}
        or path.suffix.lower() in {".key", ".p12", ".pem", ".pfx"}
    ):
        raise RuntimeError(f"Refusing to package possible secret: {rel.as_posix()}")
    return "include"


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
            if package_file_policy(path, source_root) == "skip":
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
            parts = PurePosixPath(name).parts
            if "\\" in name:
                raise RuntimeError(f"{zip_path.name} contains a backslash entry: {name}")
            if name.startswith("/") or ":" in name:
                raise RuntimeError(f"{zip_path.name} contains an absolute-looking entry: {name}")
            if ".." in parts:
                raise RuntimeError(f"{zip_path.name} contains a parent traversal entry: {name}")
            if not name.startswith(expected_root + "/"):
                raise RuntimeError(f"{zip_path.name} contains an entry outside {expected_root}: {name}")
            lowered = tuple(part.lower() for part in parts)
            if "node_modules" in lowered or any(part.endswith(".log") for part in lowered):
                raise RuntimeError(f"{zip_path.name} contains a development file: {name}")
        archive.extractall(target)
    if not (target / expected_root).exists():
        raise RuntimeError(f"{zip_path.name} did not extract to {expected_root}")


def test_version(zip_path: Path, member: str, marker: str, expected_version: str) -> None:
    with zipfile.ZipFile(zip_path) as archive:
        content = archive.read(member).decode("utf-8")
    expected = f"{marker}{expected_version}"
    if expected not in content:
        raise RuntimeError(f"{zip_path.name} does not contain {expected!r} in {member}")


def test_no_secret_signatures(zip_path: Path) -> None:
    patterns = (
        re.compile(rb"-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----"),
        re.compile(rb"\bAKIA[0-9A-Z]{16}\b"),
        re.compile(rb"\bghp_[A-Za-z0-9]{30,}\b"),
    )
    text_extensions = {".css", ".html", ".js", ".json", ".md", ".php", ".txt", ".xml", ".yaml", ".yml"}
    with zipfile.ZipFile(zip_path) as archive:
        for name in archive.namelist():
            if PurePosixPath(name).suffix.lower() not in text_extensions:
                continue
            content = archive.read(name)
            if any(pattern.search(content) for pattern in patterns):
                raise RuntimeError(f"{zip_path.name} contains a possible secret in {name}")


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
    test_version(theme_zip, "statek-cholupice/style.css", "Version: ", EXPECTED_THEME_VERSION)
    test_version(
        plugin_zip,
        "statek-cholupice-core/statek-cholupice-core.php",
        " * Version: ",
        EXPECTED_PLUGIN_VERSION,
    )
    test_no_secret_signatures(theme_zip)
    test_no_secret_signatures(plugin_zip)
    if TEST_ROOT.exists():
        shutil.rmtree(TEST_ROOT)

    lines = [
        "WordPress ZIP manifest",
        "",
        f"Theme release candidate version: {EXPECTED_THEME_VERSION}",
        f"Companion plugin version: {EXPECTED_PLUGIN_VERSION}",
        "",
        f"{theme_zip.name}: {len(theme_entries)} entries",
        *[f"  {entry}" for entry in theme_entries[:80]],
        "  ..." if len(theme_entries) > 80 else "",
        "",
        f"{plugin_zip.name}: {len(plugin_entries)} entries",
        *[f"  {entry}" for entry in plugin_entries],
        "",
        "Extraction test: OK",
        "Version test: OK for theme and companion plugin.",
        "Package policy: no node_modules, temporary files, development logs, key files, or common secret signatures.",
        "ZIP paths: forward slashes only, one root folder, no absolute or parent-traversal paths.",
    ]
    MANIFEST.write_text("\n".join(line for line in lines if line != "") + "\n", encoding="utf-8")
    print(f"Created {theme_zip}")
    print(f"Created {plugin_zip}")
    print(f"Wrote {MANIFEST}")


if __name__ == "__main__":
    main()
