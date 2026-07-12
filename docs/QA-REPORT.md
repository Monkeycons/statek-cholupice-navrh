# QA Report

Zdrojový commit schváleného statického webu: `1a8e2b5 Improve before after comparison control`.

Pracovní větev WordPress balíku: `feat/wordpress-production-theme`.

## Provedené kontroly

- JavaScript syntaxe: `node --check wordpress/wp-content/themes/statek-cholupice/assets/js/main.js` prošla bez chyby.
- Vyhledání starého JS zdroje novinek `newsItems` / `publishedNews`: bez výskytu.
- Vyhledání diagnostických `console.log`: bez výskytu.
- Hero obrázek je napojen přes `<picture>` a jako jediný používá `fetchpriority="high"`.
- Produkční obrázky byly znovu vygenerovány: JPEG fallback, WebP a AVIF.
- ZIP balíčky se testují skriptem `tools/package-wordpress.py`: jedna kořenová složka, pouze `/` v cestách, žádné absolutní cesty, extrakce do prázdné složky.

## Neprovedené kontroly

Na tomto počítači není dostupný PHP runtime (`where php` nic nenašel), proto nebylo možné spustit:

- PHP lint,
- aktivaci šablony a pluginu v čistém WordPressu,
- odeslání kontaktního formuláře proti reálnému `wp_mail()`,
- kontrolu administrace metaboxů přímo ve WordPressu.

Balík proto označuji jako release candidate, ne jako definitivně runtime ověřenou produkční verzi.
