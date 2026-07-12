# QA Report

Zdrojový commit schváleného statického webu: `1a8e2b5 Improve before after comparison control`.

Pracovní větev WordPress balíku: `feat/wordpress-production-theme`.

Navazuje na release candidate commit: `63b98f5 Harden WordPress release candidate package`.

## Externě ověřeno před touto opravou

Externí kontrola potvrdila, že předchozí RC balík se aktivoval ve WordPress Playgroundu, homepage se vykreslila s HTTP 200 bez PHP fatal erroru, warningu nebo notice, PHP soubory prošly syntaktickou kontrolou, JavaScript prošel `node --check`, `theme.json` byl platný a responzivní obrázky se skutečně načítaly jako AVIF/WebP/JPEG varianty.

## Provedené opravy v této revizi

- Kontaktní formulář posílá REST nonce i v hlavičce `X-WP-Nonce`, aby fungoval také přihlášenému administrátorovi.
- Plugin při aktivaci idempotentně vytvoří nebo najde stránku `Novinky` a nastaví ji jako `page_for_posts`, pokud už správce nemá vlastní nastavení.
- Navigace normalizuje stejné homepage odkazy s hashem a filtruje chybné WordPress `current-*` třídy; aktivní stav nastavuje JavaScript podle viditelné sekce.
- Přetahovačka používá správný `aria-valuetext`: současný stav = hodnota range, navrhovaná podoba = 100 minus hodnota range.
- Sticky hlavička má kompenzaci pro WordPress admin bar 32 px / 46 px a vyšší offset kotev pro přihlášeného správce.
- Metabox homepage se zobrazuje pouze na stránce nastavené jako `page_on_front`.
- Obrázkové varianty a rozměry obrázků se cachují v rámci requestu.
- `screenshot.png` byl zmenšen z přibližně 2,9 MB na 461 KB.
- Patička používá `wp_date('Y')`.
- Dokumentace fontu Inter byla opravena podle skutečně přiloženého fontu.

## Lokálně ověřeno po opravě

- JavaScript syntaxe: `node --check wordpress/wp-content/themes/statek-cholupice/assets/js/main.js` prošla bez chyby.
- PHP runtime: `where php` nevrátil dostupnou instalaci, proto nebylo možné lokálně spustit PHP lint.
- ZIP balíčky byly znovu vytvořeny skriptem `tools/package-wordpress.py`.
- Test extrakce ZIPů prošel: jedna kořenová složka, dopředná lomítka, žádné absolutní cesty.
- Výsledné velikosti: `statek-cholupice-theme.zip` 19 459 950 B, `statek-cholupice-core.zip` 5 376 B.
- Kontrola starých diagnostických výpisů: ve WordPress souborech není `console.log`.
- Kontrola starého roku: ve WordPress souborech není `gmdate`.

## Neprovedené kontroly v tomto lokálním prostředí

Na tomto počítači není dostupný PHP runtime ani čistá WordPress instalace, proto zde nebylo možné znovu ověřit:

- aktivaci šablony a pluginu po této konkrétní opravě,
- WP_DEBUG=true v běžícím WordPressu,
- reálné odeslání kontaktního formuláře přes `wp_mail()`,
- přihlášeného administrátora s admin barem v běžícím WordPressu,
- vytvoření stránky Novinky přímo v databázi WordPressu,
- chování metaboxu v administraci WordPressu.

Tyto body je potřeba krátce potvrdit na cílovém hostingu nebo v dalším WordPress Playground testu před produkčním nasazením.
