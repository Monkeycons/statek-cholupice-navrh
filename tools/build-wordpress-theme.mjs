import fs from "node:fs";
import path from "node:path";

const root = process.cwd();
const themeRoot = path.join(root, "wordpress", "wp-content", "themes", "statek-cholupice");
const pluginRoot = path.join(root, "wordpress", "wp-content", "plugins", "statek-cholupice-core");
const docsRoot = path.join(root, "docs");
const distRoot = path.join(root, "dist");

const read = (file) => fs.readFileSync(path.join(root, file), "utf8");
const write = (file, content) => {
  fs.mkdirSync(path.dirname(file), { recursive: true });
  fs.writeFileSync(file, content, "utf8");
};

const html = read("index.html");
const cssMatch = html.match(/<style>([\s\S]*?)<\/style>/);
const mainMatch = html.match(/<main>([\s\S]*?)<\/main>/);
const scriptMatch = html.match(/<footer>[\s\S]*?<\/footer>\s*<script>([\s\S]*?)<\/script>/);

if (!cssMatch || !mainMatch || !scriptMatch) {
  throw new Error("Nepodarilo se nacist style/main/script ze statickeho index.html.");
}

const sourceImages = [
  "karousel3_premium_dss_v4_4k_preview.jpg",
  "hero_vizualizace/cholupice-hero-super-render-web-spravne.png",
  "statek_web_premium/1_16_photo_premium_residential.png",
  "statek_web_premium/1_18_photo_premium_commerce_people_atm.png",
  "statek_web_premium/administrativni_centrum_premium.png",
  "statek_web_premium/bezpecnost-vstup-kontrola.png",
  "statek_web_premium/doprava-areal-delivery-truck-focus.png",
  "statek_web_premium/hala_a_premium.png",
  "statek_web_premium/hero-vjezd-preview-v4-lide-obchod.png",
  "statek_web_premium/parkovaci_dum_premium.png",
  "statek_web_premium/po_premium_dss_van_logo_left.png",
  "statek_web_premium/pred_premium_documentary.png",
  "statek_web_premium/vyvojove_centrum_premium_engineers.png",
];

for (const image of sourceImages) {
  const src = path.join(root, image);
  const dest = path.join(themeRoot, "assets", "images", image);
  fs.mkdirSync(path.dirname(dest), { recursive: true });
  fs.copyFileSync(src, dest);
}

let css = cssMatch[1]
  .replace(/url\("hero_vizualizace\//g, 'url("../images/hero_vizualizace/')
  .replace(
    /\n\s*\.principles-section \{[\s\S]*?\.principle-card p \{[\s\S]*?\n\s*\}\n(?=\n\s*\.site-area)/,
    "\n"
  )
  .replace(/\n\s*html\.js \.principles-grid \.principle-card:nth-child\(2\)[^\n]*\n\s*html\.js \.principles-grid \.principle-card:nth-child\(3\)[^\n]*\n/, "\n")
  .replace(
    ".news-card,\n        .news-carousel-button,\n        .principle-card { animation: none; transition: none; }",
    ".news-card,\n        .news-carousel-button { animation: none; transition: none; }"
  )
  .replace(/\n\s*\.principles-section \{ margin-top: 36px; padding-top: 24px; \}\n\s*\.principles-heading h2 \{ font-size: 34px; \}\n\s*\.principles-heading p \{ font-size: 18px; \}\n\s*\.principles-grid \{ grid-template-columns: 1fr; \}\n\s*\.principle-card \{ min-height: 188px; \}/, "")
  .replace(/\n\s*@media \(max-width: 380px\) \{\n\s*\.principle-card \{ min-height: 224px; \}\n\s*\}\n/, "\n")
  .replace(
    "body {\n      margin: 0;",
    `@font-face {
      font-family: "Inter";
      src: url("../fonts/InterVariable.woff2") format("woff2");
      font-weight: 100 900;
      font-style: normal;
      font-display: swap;
    }

    body {
      margin: 0;`
  )
  .replace(
    ".visually-hidden {",
    `.skip-link {
      position: fixed;
      left: 16px;
      top: 16px;
      z-index: 1000;
      padding: 10px 14px;
      border-radius: 6px;
      background: var(--green-dark);
      color: white;
      transform: translateY(-140%);
      transition: transform .2s ease;
    }

    .skip-link:focus {
      transform: translateY(0);
    }

    .form-honeypot {
      position: absolute;
      left: -9999px;
      width: 1px;
      height: 1px;
      opacity: 0;
      pointer-events: none;
    }

    .visually-hidden {`
  );

let js = scriptMatch[1]
  .replace(
    'const publishedNews = [];',
    'const publishedNews = Array.isArray(window.StatekCholupice?.newsItems) ? window.StatekCholupice.newsItems : [];'
  )
  .replace(
    'const NEWS_INDEX_URL = "";',
    'const NEWS_INDEX_URL = window.StatekCholupice?.newsIndexUrl || "";'
  )
  .replace(/const CONTACT_ENDPOINT = "";[^\n]*/, 'const CONTACT_ENDPOINT = window.StatekCholupice?.contactEndpoint || "";')
  .replace(
    'message: messageField.value.trim()',
    'message: messageField.value.trim(),\n              nonce: window.StatekCholupice?.contactNonce || "",\n              company: contactForm.elements.company?.value || ""'
  )
  .replace(".principles-section, .principle-card, ", "")
  .replace(/`\/novinky\/\$\{item\.slug\}\/`/g, '(item.url || `/novinky/${item.slug}/`)');

let main = mainMatch[1]
  .replace(/src="([^"]+\.(?:png|jpg|jpeg|webp|avif))"/gi, (_match, src) => {
    return `src="<?php echo esc_url( statek_cholupice_asset_url( 'images/${src}' ) ); ?>"`;
  })
  .replace(
    /<section class="news-section" id="novinky" hidden>[\s\S]*?<\/section>\s*(?=<section class="faq-section")/,
    "<?php get_template_part( 'template-parts/home/news' ); ?>\n\n    "
  )
  .replace(
    '<form class="faq-contact-form" id="faq-contact-form" novalidate>',
    '<form class="faq-contact-form" id="faq-contact-form" novalidate data-contact-form>'
  )
  .replace(
    '</div>\n            <div class="form-field">\n              <label for="contact-message">',
    '</div>\n            <div class="form-field form-honeypot" aria-hidden="true">\n              <label for="contact-company">Firma</label>\n              <input id="contact-company" name="company" type="text" tabindex="-1" autocomplete="off">\n            </div>\n            <div class="form-field">\n              <label for="contact-message">'
  )
  .replace(
    '          </div>\n            </div>\n          </div>\n        </div>\n      </section>',
    '          </div>\n        </div>\n      </div>\n    </section>'
  );

write(path.join(themeRoot, "assets", "css", "main.css"), css);
write(path.join(themeRoot, "assets", "js", "main.js"), js);

write(path.join(themeRoot, "style.css"), `/*
Theme Name: Statek Cholupice
Theme URI: https://www.statekcholupice.cz/
Author: Monkey Consulting
Description: Lehká zakázková šablona pro projekt Statek Cholupice.
Version: 1.0.0
Text Domain: statek-cholupice
Requires at least: 6.5
Tested up to: 6.6
Requires PHP: 8.0
License: Proprietary
*/
`);

write(path.join(themeRoot, "functions.php"), `<?php
/**
 * Bootstrap šablony Statek Cholupice.
 *
 * @package StatekCholupice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_theme_file_path( 'inc/setup.php' );
require_once get_theme_file_path( 'inc/helpers.php' );
require_once get_theme_file_path( 'inc/enqueue.php' );
require_once get_theme_file_path( 'inc/seo.php' );
`);

write(path.join(themeRoot, "inc", "setup.php"), `<?php
/**
 * Základní nastavení šablony.
 *
 * @package StatekCholupice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function statek_cholupice_setup(): void {
	load_theme_textdomain( 'statek-cholupice', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_editor_style( 'assets/css/main.css' );

	register_nav_menus(
		array(
			'primary' => __( 'Hlavní menu', 'statek-cholupice' ),
		)
	);
}
add_action( 'after_setup_theme', 'statek_cholupice_setup' );
`);

write(path.join(themeRoot, "inc", "helpers.php"), `<?php
/**
 * Pomocné funkce šablony.
 *
 * @package StatekCholupice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function statek_cholupice_asset_url( string $path ): string {
	return get_theme_file_uri( 'assets/' . ltrim( $path, '/' ) );
}

function statek_cholupice_asset_path( string $path ): string {
	return get_theme_file_path( 'assets/' . ltrim( $path, '/' ) );
}

function statek_cholupice_asset_version( string $path ): string {
	$file = statek_cholupice_asset_path( $path );
	return file_exists( $file ) ? (string) filemtime( $file ) : wp_get_theme()->get( 'Version' );
}

function statek_cholupice_anchor_url( string $anchor ): string {
	$anchor = ltrim( $anchor, '#' );
	if ( is_front_page() ) {
		return '#' . $anchor;
	}
	return home_url( '/#' . $anchor );
}

function statek_cholupice_default_menu_items(): array {
	return array(
		'projekt'           => __( 'O projektu', 'statek-cholupice' ),
		'bezpecnost'        => __( 'Bezpečnost', 'statek-cholupice' ),
		'doprava'           => __( 'Doprava', 'statek-cholupice' ),
		'zivotni-prostredi' => __( 'Životní prostředí', 'statek-cholupice' ),
		'prinosy'           => __( 'Přínosy', 'statek-cholupice' ),
		'kontakt'           => __( 'Časté dotazy', 'statek-cholupice' ),
	);
}

function statek_cholupice_primary_navigation( bool $mobile = false ): void {
	$items = statek_cholupice_default_menu_items();
	foreach ( $items as $anchor => $label ) {
		printf(
			'<a href="%s">%s</a>',
			esc_url( statek_cholupice_anchor_url( $anchor ) ),
			esc_html( $label )
		);
	}
	printf(
		'<a class="button" href="%s">%s</a>',
		esc_url( statek_cholupice_anchor_url( 'faq-contact-form' ) ),
		esc_html__( 'Kontakt', 'statek-cholupice' )
	);
}

function statek_cholupice_news_items(): array {
	$query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 6,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);

	$items = array();
	while ( $query->have_posts() ) {
		$query->the_post();
		$image = get_the_post_thumbnail_url( get_the_ID(), 'large' );
		if ( ! $image ) {
			$image = statek_cholupice_asset_url( 'images/statek_web_premium/hero-vjezd-preview-v4-lide-obchod.png' );
		}
		$items[] = array(
			'status'   => 'published',
			'slug'     => get_post_field( 'post_name', get_the_ID() ),
			'title'    => get_the_title(),
			'date'     => get_the_date( 'c' ),
			'image'    => esc_url_raw( $image ),
			'imageAlt' => get_post_meta( get_post_thumbnail_id(), '_wp_attachment_image_alt', true ) ?: get_the_title(),
			'excerpt'  => wp_strip_all_tags( get_the_excerpt() ),
			'url'      => get_permalink(),
		);
	}
	wp_reset_postdata();

	return $items;
}
`);

write(path.join(themeRoot, "inc", "enqueue.php"), `<?php
/**
 * Načítání front-end assetů.
 *
 * @package StatekCholupice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function statek_cholupice_enqueue_assets(): void {
	wp_enqueue_style(
		'statek-cholupice-main',
		statek_cholupice_asset_url( 'css/main.css' ),
		array(),
		statek_cholupice_asset_version( 'css/main.css' )
	);

	wp_enqueue_script(
		'statek-cholupice-main',
		statek_cholupice_asset_url( 'js/main.js' ),
		array(),
		statek_cholupice_asset_version( 'js/main.js' ),
		true
	);

	wp_localize_script(
		'statek-cholupice-main',
		'StatekCholupice',
		array(
			'contactEndpoint' => esc_url_raw( rest_url( 'statek-cholupice/v1/contact' ) ),
			'contactNonce'    => wp_create_nonce( 'wp_rest' ),
			'newsIndexUrl'    => esc_url_raw( get_post_type_archive_link( 'post' ) ?: home_url( '/novinky/' ) ),
			'newsItems'       => statek_cholupice_news_items(),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'statek_cholupice_enqueue_assets' );

function statek_cholupice_preload_assets(): void {
	$font = statek_cholupice_asset_url( 'fonts/InterVariable.woff2' );
	echo '<link rel="preload" href="' . esc_url( $font ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
}
add_action( 'wp_head', 'statek_cholupice_preload_assets', 1 );
`);

write(path.join(themeRoot, "inc", "seo.php"), `<?php
/**
 * Lehká produkční metadata bez SEO pluginu.
 *
 * @package StatekCholupice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function statek_cholupice_meta_description(): string {
	if ( is_singular( 'post' ) ) {
		return wp_strip_all_tags( get_the_excerpt() );
	}
	return 'Proměna bývalého hospodářského areálu v Cholupicích: nové využití brownfieldu, služby, pracovní místa, zeleň a záchrana historického špejcharu.';
}

function statek_cholupice_head_meta(): void {
	$description = statek_cholupice_meta_description();
	$url         = is_singular() ? get_permalink() : home_url( '/' );
	$title       = wp_get_document_title();
	$image       = statek_cholupice_asset_url( 'images/hero_vizualizace/cholupice-hero-super-render-web-spravne.png' );

	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
	printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
	printf( '<meta property="og:type" content="%s">' . "\n", is_singular( 'post' ) ? 'article' : 'website' );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
	printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}
add_action( 'wp_head', 'statek_cholupice_head_meta', 5 );
`);

write(path.join(themeRoot, "header.php"), `<?php
/**
 * Header šablony.
 *
 * @package StatekCholupice
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script>document.documentElement.classList.add("js");</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main-content"><?php esc_html_e( 'Přejít na obsah', 'statek-cholupice' ); ?></a>
<header>
	<div class="nav">
		<a class="brand brand-link" href="<?php echo esc_url( statek_cholupice_anchor_url( 'uvod' ) ); ?>" aria-label="<?php esc_attr_e( 'Statek Cholupice - zpět na úvod', 'statek-cholupice' ); ?>">Statek Cholupice</a>
		<nav aria-label="<?php esc_attr_e( 'Hlavní menu', 'statek-cholupice' ); ?>">
			<?php statek_cholupice_primary_navigation(); ?>
		</nav>
		<button class="mobile-menu-toggle" id="mobile-menu-toggle" type="button" aria-expanded="false" aria-controls="mobile-menu" aria-label="<?php esc_attr_e( 'Otevřít menu', 'statek-cholupice' ); ?>">
			<span aria-hidden="true"></span>
			<span aria-hidden="true"></span>
			<span aria-hidden="true"></span>
		</button>
	</div>
	<div class="mobile-menu" id="mobile-menu" hidden>
		<nav aria-label="<?php esc_attr_e( 'Mobilní menu', 'statek-cholupice' ); ?>">
			<?php statek_cholupice_primary_navigation( true ); ?>
		</nav>
	</div>
</header>
`);

write(path.join(themeRoot, "front-page.php"), `<?php
/**
 * Úvodní stránka.
 *
 * @package StatekCholupice
 */

get_header();
?>
<main id="main-content">
${main}
</main>
<?php
get_footer();
`);

write(path.join(themeRoot, "footer.php"), `<?php
/**
 * Footer šablony.
 *
 * @package StatekCholupice
 */
?>
<footer>
	<div class="wrap footer-main">
		<div class="footer-column">
			<h3>Investor projektu</h3>
			<p>DSS a.s.<br>Kloboučnická 1735/26, Nusle, 140 00 Praha 4<br>IČ: 26161541, DIČ: CZ26161541<br>Zapsaná v obchodním rejstříku vedeném Městským soudem v Praze, oddíl B, vložka 6434.</p>
		</div>
		<div class="footer-column">
			<h3>Kontakt</h3>
			<p><a href="mailto:info@statekcholupice.cz">info@statekcholupice.cz</a></p>
		</div>
		<div class="footer-column">
			<h3>Dokumenty</h3>
			<p><a href="https://www.statekcholupice.cz/ochrana-osobnich-udaju/">Zásady zpracování osobních údajů</a></p>
		</div>
	</div>
	<div class="wrap footer-notice">
		<div>
			<h3>Aktuálnost informací</h3>
			<p>Informace uvedené na tomto webu odpovídají stavu projektu v době jejich zveřejnění. Průběžně je aktualizujeme, mezi změnou projektu a jejím zveřejněním na webu však může vzniknout časová prodleva.</p>
		</div>
		<div>
			<h3>Vizualizace projektu</h3>
			<p>Vizualizace mají ilustrativní charakter a zachycují předpokládanou podobu projektu v době svého vzniku. V průběhu další přípravy, povolování a realizace může dojít k dílčím změnám architektonického, technického nebo materiálového řešení.</p>
		</div>
	</div>
	<div class="wrap footer-bottom">
		<span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> Statek Cholupice</span>
		<div class="footer-bottom-links">
			<a href="https://www.statekcholupice.cz/ochrana-osobnich-udaju/">Zásady zpracování osobních údajů</a>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
`);

write(path.join(themeRoot, "template-parts", "home", "news.php"), `<?php
/**
 * Sekce novinek.
 *
 * @package StatekCholupice
 */

$news_items = statek_cholupice_news_items();
?>
<section class="news-section" id="novinky" <?php echo empty( $news_items ) ? 'hidden' : ''; ?>>
	<div class="wrap">
		<div class="news-heading">
			<p class="news-kicker">Novinky</p>
			<h2>Co je nového na Statku Cholupice</h2>
			<p class="news-motto">Aktuální informace o projektu na jednom místě.</p>
			<p class="news-intro">Sledujte průběh přípravy projektu, důležité milníky a nové odpovědi na otázky, které se kolem proměny statku objevují.</p>
		</div>
		<div class="news-carousel" aria-label="Novinky">
			<div class="news-carousel-toolbar">
				<button class="news-carousel-button" type="button" data-news-prev aria-label="Předchozí novinky">‹</button>
				<button class="news-carousel-button" type="button" data-news-next aria-label="Další novinky">›</button>
				<a class="read-more news-all-link" data-news-all href="<?php echo esc_url( get_post_type_archive_link( 'post' ) ?: home_url( '/novinky/' ) ); ?>">Všechny novinky</a>
			</div>
			<div class="news-carousel-viewport" data-news-viewport tabindex="0">
				<div class="news-track" data-news-track></div>
			</div>
			<p class="news-carousel-status visually-hidden" data-news-status aria-live="polite"></p>
		</div>
	</div>
</section>
`);

const simpleTemplate = (title, body) => `<?php
/**
 * ${title}
 *
 * @package StatekCholupice
 */

get_header();
?>
<main id="main-content">
	<section>
		<div class="wrap">
${body}
		</div>
	</section>
</main>
<?php
get_footer();
`;

write(path.join(themeRoot, "index.php"), simpleTemplate("Index", "\t\t\t<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>\n\t\t\t\t<article <?php post_class(); ?>>\n\t\t\t\t\t<h1><?php the_title(); ?></h1>\n\t\t\t\t\t<?php the_content(); ?>\n\t\t\t\t</article>\n\t\t\t<?php endwhile; endif; ?>"));
write(path.join(themeRoot, "page.php"), simpleTemplate("Stránka", "\t\t\t<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>\n\t\t\t\t<article <?php post_class(); ?>>\n\t\t\t\t\t<h1><?php the_title(); ?></h1>\n\t\t\t\t\t<?php the_content(); ?>\n\t\t\t\t</article>\n\t\t\t<?php endwhile; endif; ?>"));
write(path.join(themeRoot, "single.php"), simpleTemplate("Detail článku", "\t\t\t<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>\n\t\t\t\t<article <?php post_class(); ?>>\n\t\t\t\t\t<p class=\"news-card-date\"><?php echo esc_html( get_the_date() ); ?></p>\n\t\t\t\t\t<h1><?php the_title(); ?></h1>\n\t\t\t\t\t<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large', array( 'class' => 'news-card-image' ) ); } ?>\n\t\t\t\t\t<?php the_content(); ?>\n\t\t\t\t</article>\n\t\t\t<?php endwhile; endif; ?>"));
write(path.join(themeRoot, "archive.php"), simpleTemplate("Archiv", "\t\t\t<h1><?php the_archive_title(); ?></h1>\n\t\t\t<?php if ( have_posts() ) : ?>\n\t\t\t\t<div class=\"benefits\">\n\t\t\t\t<?php while ( have_posts() ) : the_post(); ?>\n\t\t\t\t\t<article class=\"benefit\">\n\t\t\t\t\t\t<h3><a href=\"<?php the_permalink(); ?>\"><?php the_title(); ?></a></h3>\n\t\t\t\t\t\t<p><?php echo esc_html( get_the_excerpt() ); ?></p>\n\t\t\t\t\t</article>\n\t\t\t\t<?php endwhile; ?>\n\t\t\t\t</div>\n\t\t\t<?php else : ?>\n\t\t\t\t<p>Zatím zde nejsou žádné novinky.</p>\n\t\t\t<?php endif; ?>"));
write(path.join(themeRoot, "home.php"), fs.readFileSync(path.join(themeRoot, "archive.php"), "utf8"));
write(path.join(themeRoot, "404.php"), simpleTemplate("404", "\t\t\t<h1>Stránka nebyla nalezena</h1>\n\t\t\t<p>Omlouváme se, požadovaná stránka neexistuje.</p>\n\t\t\t<p><a class=\"button\" href=\"<?php echo esc_url( home_url( '/' ) ); ?>\">Zpět na úvod</a></p>"));
write(path.join(themeRoot, "theme.json"), JSON.stringify({
  $schema: "https://schemas.wp.org/trunk/theme.json",
  version: 3,
  settings: {
    layout: { contentSize: "1180px", wideSize: "1440px" },
    color: {
      palette: [
        { slug: "green", color: "#4f7d57", name: "Zelená" },
        { slug: "green-dark", color: "#2f5a38", name: "Tmavě zelená" },
        { slug: "cream", color: "#fbfaf6", name: "Krémová" },
        { slug: "graphite", color: "#303631", name: "Grafitová" }
      ]
    },
    typography: {
      fontFamilies: [
        { slug: "inter", name: "Inter", fontFamily: "Inter, Segoe UI, Arial, sans-serif" },
        { slug: "serif", name: "Georgia", fontFamily: "Georgia, Times New Roman, serif" }
      ]
    }
  }
}, null, 2));

write(path.join(pluginRoot, "statek-cholupice-core.php"), `<?php
/**
 * Plugin Name: Statek Cholupice Core
 * Description: Projektové funkce pro web Statek Cholupice.
 * Version: 1.0.0
 * Author: Monkey Consulting
 * Text Domain: statek-cholupice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'STATEK_CHOLUPICE_CORE_VERSION', '1.0.0' );

require_once __DIR__ . '/includes/contact.php';
require_once __DIR__ . '/includes/setup.php';
`);

write(path.join(pluginRoot, "includes", "contact.php"), `<?php
/**
 * Bezpečný kontaktní formulář.
 *
 * @package StatekCholupiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function statek_cholupice_core_contact_email(): string {
	$email = get_option( 'statek_cholupice_contact_email', 'info@statekcholupice.cz' );
	return is_email( $email ) ? $email : 'info@statekcholupice.cz';
}

function statek_cholupice_core_register_settings(): void {
	register_setting(
		'statek_cholupice_settings',
		'statek_cholupice_contact_email',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_email',
			'default'           => 'info@statekcholupice.cz',
		)
	);
}
add_action( 'admin_init', 'statek_cholupice_core_register_settings' );

function statek_cholupice_core_settings_page(): void {
	add_options_page(
		'Statek Cholupice',
		'Statek Cholupice',
		'manage_options',
		'statek-cholupice',
		'statek_cholupice_core_render_settings_page'
	);
}
add_action( 'admin_menu', 'statek_cholupice_core_settings_page' );

function statek_cholupice_core_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1>Statek Cholupice</h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'statek_cholupice_settings' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="statek_cholupice_contact_email">E-mail pro dotazy</label></th>
					<td><input class="regular-text" id="statek_cholupice_contact_email" name="statek_cholupice_contact_email" type="email" value="<?php echo esc_attr( statek_cholupice_core_contact_email() ); ?>"></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function statek_cholupice_core_register_contact_route(): void {
	register_rest_route(
		'statek-cholupice/v1',
		'/contact',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'statek_cholupice_core_handle_contact',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'statek_cholupice_core_register_contact_route' );

function statek_cholupice_core_handle_contact( WP_REST_Request $request ): WP_REST_Response {
	$nonce = (string) $request->get_param( 'nonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_REST_Response( array( 'message' => 'Neplatné ověření formuláře. Obnovte stránku a zkuste to znovu.' ), 403 );
	}

	$honeypot = trim( (string) $request->get_param( 'company' ) );
	if ( '' !== $honeypot ) {
		return new WP_REST_Response( array( 'message' => 'Dotaz byl přijat.' ), 200 );
	}

	$ip       = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? 'unknown' ) );
	$limited = get_transient( 'statek_contact_' . md5( $ip ) );
	if ( $limited ) {
		return new WP_REST_Response( array( 'message' => 'Zkuste to prosím znovu za chvíli.' ), 429 );
	}

	$name    = sanitize_text_field( (string) $request->get_param( 'name' ) );
	$email   = sanitize_email( (string) $request->get_param( 'email' ) );
	$message = sanitize_textarea_field( (string) $request->get_param( 'message' ) );

	if ( ! is_email( $email ) || '' === $message ) {
		return new WP_REST_Response( array( 'message' => 'Vyplňte prosím platný e-mail a dotaz.' ), 400 );
	}

	set_transient( 'statek_contact_' . md5( $ip ), 1, MINUTE_IN_SECONDS );

	$subject = 'Dotaz z webu Statek Cholupice';
	$body    = "Jméno: {$name}\nE-mail: {$email}\n\nDotaz:\n{$message}";
	$headers = array( 'Reply-To: ' . $email );

	$sent = wp_mail( statek_cholupice_core_contact_email(), $subject, $body, $headers );
	if ( ! $sent ) {
		return new WP_REST_Response( array( 'message' => 'Dotaz se nepodařilo odeslat. Napište prosím přímo na info@statekcholupice.cz.' ), 500 );
	}

	return new WP_REST_Response( array( 'message' => 'Děkujeme. Váš dotaz jsme přijali.' ), 200 );
}
`);

write(path.join(pluginRoot, "includes", "setup.php"), `<?php
/**
 * Jednoduchá idempotentní inicializace obsahu.
 *
 * @package StatekCholupiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function statek_cholupice_core_activate(): void {
	$page = get_page_by_path( 'domovska-stranka' );
	if ( ! $page ) {
		$page_id = wp_insert_post(
			array(
				'post_title'   => 'Domovská stránka',
				'post_name'    => 'domovska-stranka',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => 'Obsah domovské stránky spravuje šablona Statek Cholupice.',
			)
		);
	} else {
		$page_id = (int) $page->ID;
	}

	if ( $page_id && 'page' !== get_option( 'show_on_front' ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $page_id );
	}
}
register_activation_hook( dirname( __DIR__ ) . '/statek-cholupice-core.php', 'statek_cholupice_core_activate' );
`);

write(path.join(themeRoot, "languages", ".gitkeep"), "");
write(path.join(themeRoot, "assets", "fonts", "README.md"), `# Inter font

Šablona je připravena pro lokální soubor \`InterVariable.woff2\`.

Font se má doplnit z oficiálního projektu Inter: https://github.com/rsms/inter
Licence: SIL Open Font License 1.1.

V tomto prostředí nebyl font stažen automaticky, protože není dostupný síťový přístup.
`);

write(path.join(docsRoot, "ARCHITECTURE.md"), `# Architektura

Zdroj pravdy: \`github-pages/index.html\`, lokální commit \`1a8e2b5\`.

Šablona je klasická WordPress šablona s \`theme.json\`. Vzhled je převeden ze statického webu do \`assets/css/main.css\`, prezentační chování do \`assets/js/main.js\`.

Companion plugin \`statek-cholupice-core\` řeší kontaktní formulář a základní inicializaci domovské stránky.
`);
write(path.join(docsRoot, "INSTALLATION.md"), `# Instalace

1. Nainstalujte ZIP šablony \`dist/statek-cholupice-theme.zip\`.
2. Aktivujte šablonu Statek Cholupice.
3. Nainstalujte a aktivujte \`dist/statek-cholupice-core.zip\`.
4. V Nastavení > Statek Cholupice nastavte e-mail pro dotazy.
5. Zkontrolujte trvalé odkazy a nastavte statickou homepage.
6. Otestujte kontaktní formulář na cílovém hostingu.
`);
write(path.join(docsRoot, "EDITOR-GUIDE.md"), `# Editor Guide

Novinky se spravují jako běžné příspěvky WordPressu. Pro kontaktní e-mail použijte Nastavení > Statek Cholupice.

Homepage je v této verzi převedena jako pevná šablona kvůli zachování schváleného vzhledu. Další fáze může doplnit detailní editaci jednotlivých sekcí nativními poli bez ACF.
`);
write(path.join(docsRoot, "KNOWN-LIMITATIONS.md"), `# Známá omezení

- Font Inter není fyzicky přiložen, protože nebylo možné bezpečně stáhnout externí soubor v tomto prostředí.
- Obrázky jsou zatím přeneseny v původní kvalitě; optimalizace je připravena jako samostatný krok.
- Plná redakční editace všech sekcí homepage není dokončena v této první lokální konverzi.
`);
write(path.join(docsRoot, "QA-REPORT.md"), `# QA Report

Zdrojový commit: \`1a8e2b5 Improve before after comparison control\`.
Tag reference: \`approved-static-v1\`.
Pracovní větev: \`feat/wordpress-production-theme\`.

Runtime test WordPressu nebyl proveden, protože v prostředí není dostupné PHP ani lokální WordPress instalace.
`);
write(path.join(docsRoot, "ASSET-REPORT.md"), `# Asset Report

Původní obrázky byly zkopírovány do šablony jako referenční produkční assety. Optimalizované AVIF/WebP varianty nebyly vytvořeny v tomto běhu.
`);

fs.mkdirSync(distRoot, { recursive: true });

console.log("WordPress theme scaffold generated.");
