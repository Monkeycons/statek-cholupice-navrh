from __future__ import annotations

import json
import re
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
ADMIN_PHP = ROOT / "wordpress/wp-content/plugins/statek-cholupice-core/includes/homepage-admin.php"
ADMIN_JS = ROOT / "wordpress/wp-content/plugins/statek-cholupice-core/assets/admin-homepage.js"
FRONT_PAGE = ROOT / "wordpress/wp-content/themes/statek-cholupice/front-page.php"
HELPERS = ROOT / "wordpress/wp-content/themes/statek-cholupice/inc/helpers.php"
HOME_ARCHIVE = ROOT / "wordpress/wp-content/themes/statek-cholupice/home.php"
HOME_NEWS = ROOT / "wordpress/wp-content/themes/statek-cholupice/template-parts/home/news.php"
HEADER = ROOT / "wordpress/wp-content/themes/statek-cholupice/header.php"
MAIN_CSS = ROOT / "wordpress/wp-content/themes/statek-cholupice/assets/css/main.css"
ANALYTICS = ROOT / "wordpress/wp-content/themes/statek-cholupice/inc/analytics.php"


def require(condition: bool, message: str) -> None:
    if not condition:
        raise AssertionError(message)


def check_php_delimiters(path: Path) -> None:
    source = path.read_text(encoding="utf-8")
    stack: list[tuple[str, int]] = []
    pairs = {"(": ")", "[": "]", "{": "}"}
    index = 0
    quote = ""
    line_comment = False
    block_comment = False

    while index < len(source):
        char = source[index]
        next_char = source[index + 1] if index + 1 < len(source) else ""
        if line_comment:
            if char == "\n":
                line_comment = False
        elif block_comment:
            if char == "*" and next_char == "/":
                block_comment = False
                index += 1
        elif quote:
            if char == "\\":
                index += 1
            elif char == quote:
                quote = ""
        elif char in {"'", '"'}:
            quote = char
        elif char == "/" and next_char == "/":
            line_comment = True
            index += 1
        elif char == "/" and next_char == "*":
            block_comment = True
            index += 1
        elif char in pairs:
            stack.append((char, index))
        elif char in pairs.values():
            require(bool(stack), f"Unexpected {char} in {path}")
            opening, _ = stack.pop()
            require(pairs[opening] == char, f"Mismatched {opening}{char} in {path}")
        index += 1

    require(not quote and not block_comment, f"Unclosed PHP string or comment in {path}")
    require(not stack, f"Unclosed PHP delimiter in {path}: {stack[-1] if stack else ''}")


admin_php = ADMIN_PHP.read_text(encoding="utf-8")
admin_js = ADMIN_JS.read_text(encoding="utf-8")
front_page = FRONT_PAGE.read_text(encoding="utf-8")
helpers = HELPERS.read_text(encoding="utf-8")
home_archive = HOME_ARCHIVE.read_text(encoding="utf-8")
home_news = HOME_NEWS.read_text(encoding="utf-8")
header = HEADER.read_text(encoding="utf-8")
main_css = MAIN_CSS.read_text(encoding="utf-8")
analytics = ANALYTICS.read_text(encoding="utf-8")

for php_file in sorted((ROOT / "wordpress").rglob("*.php")):
    check_php_delimiters(php_file)

payload = {
    "czech": "Příliš žluťoučký kůň",
    "quote": 'Výraz "brownfield"',
    "backslash": r"C:\\projekt\\data",
    "url": "https://example.test/cesta?x=1&y=2",
    "multiline": "První řádek\nDruhý řádek",
    "entity": "A &amp; B",
}
require(json.loads(json.dumps(payload, ensure_ascii=False)) == payload, "JSON round-trip failed")

sanitize_match = re.search(
    r"function statek_cholupice_core_sanitize_home_meta\b.*?\n}",
    admin_php,
    re.S,
)
require(sanitize_match is not None, "Missing home meta sanitizer")
require("wp_unslash" not in sanitize_match.group(0), "JSON sanitizer must not unslash again")
require("update_post_meta( $post_id, $key, wp_slash( $json ) )" in admin_php, "JSON save is not slashed")

limits = {
    "hero_kicker": 50,
    "hero_title": 90,
    "hero_text": 320,
    "cta_label": 40,
    "section_heading": 140,
    "card_heading": 90,
    "motto": 240,
    "short_intro": 500,
    "benefit_text": 500,
    "faq_question": 180,
    "faq_answer": 3000,
    "contact_heading": 140,
}
for key, limit in limits.items():
    require(
        re.search(rf"'{re.escape(key)}'\s*=>\s*{limit}\b", admin_php) is not None,
        f"Missing central limit {key}={limit}",
    )
require("maxlength=\"" in admin_php, "Admin fields do not render maxlength")
require("statek_cholupice_core_unicode_substr" in admin_php, "Missing Unicode-safe truncation")

require("Date.now" not in admin_js, "Repeater still uses Date.now")
require("/__INDEX__/g" in admin_js, "Repeater token replacement is not global")
require('__index__' not in admin_js, "Lowercase repeater token remains in production JS")
require('__index__' not in admin_php, "Lowercase repeater token remains in PHP templates")
require('library: { type: "image" }' in admin_js, "Media modal is not restricted to images")

require("statek_cholupice_hero_picture()" in front_page, "Front page does not use hero helper")
require("wp_get_attachment_image" in helpers, "Hero attachment does not use WordPress API")
require("statek_home_hero_primary_url" in front_page, "Primary CTA URL is not editable")
require("statek_home_hero_secondary_url" in front_page, "Secondary CTA URL is not editable")
require("esc_url(" in front_page, "CTA URLs are not escaped")

require("statek_cholupice_core_sanitize_cta_url" in admin_php, "Missing CTA sanitizer")
require("'https' !== $scheme" in admin_php, "CTA sanitizer does not enforce HTTPS")
require("statek_cholupice_core_cta_url_fallback" in admin_php, "Missing CTA fallbacks")

require(
    '.news-card > a {\n      display: block;\n      width: 100%;\n      aspect-ratio: 16 / 9;\n      height: auto;' in main_css,
    "News image link must own the 16:9 ratio without filling the card height",
)
require(
    '.news-card > a picture {\n      display: block;\n      width: 100%;\n      height: 100%;' in main_css,
    "News picture must fill only the image link",
)
require(
    '.news-card-image {\n      display: block;\n      width: 100%;\n      height: 100%;\n      object-fit: cover;' in main_css,
    "News image does not fill and crop inside its wrapper",
)
linked_title = '<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>'
require(linked_title in home_archive, "News archive title is not linked")
require(linked_title in home_news, "Homepage news title is not linked")
require(
    "is_front_page() ? '' : ' site-header--solid'" in header,
    "Interior pages do not receive the server-rendered solid header class",
)
require("header.site-header--solid .nav" in main_css, "Solid interior header style is missing")

require("STATEK_CHOLUPICE_GA4_DEFAULT_ID" not in analytics, "GA4 must not have an automatic default ID")
require(
    "const STATEK_CHOLUPICE_GA4_RECOMMENDED_ID  = 'G-6WYM4Z2VWZ';" in analytics,
    "Recommended GA4 ID must remain informational only",
)
require(
    analytics.count("get_option( STATEK_CHOLUPICE_GA4_OPTION, '' )") == 2,
    "Missing GA4 option and first admin render must both default to empty",
)
require("'default'           => ''," in analytics, "Registered GA4 setting must default to empty")
require(
    "Analytika je vypnutá, dokud zde není uloženo platné měřicí ID." in analytics,
    "GA4 admin field must explain explicit activation",
)

print("WordPress preflight source checks: OK")
print("PHP delimiter checks (20 files): OK")
print("JSON round-trip fixture: OK")
print("News card and interior header regression checks: OK")
print("GA4 explicit opt-in source checks: OK")
