# QA Report

Zdrojový commit schváleného statického webu: `1a8e2b5 Improve before after comparison control`.

Pracovní větev WordPress balíku: `feat/wordpress-production-theme`.

Tato revize navazuje na release candidate commit: `4ff8caa Address WordPress RC review findings`.

## Externě ověřeno před touto opravou

Externí kontrola potvrdila, že předchozí RC balík byl technicky vhodný pro staging: ZIPy měly bezpečnou strukturu, 18 PHP souborů prošlo syntaktickou kontrolou na PHP 8.4.16, JavaScript prošel kontrolou syntaxe, `theme.json` byl platný, screenshot byl zmenšený, kontaktní formulář posílal `X-WP-Nonce`, stránka Novinky se nastavovala jako `page_for_posts`, menu používalo `wp_nav_menu()` a přetahovačka, mobilní menu i kontaktní formulář fungovaly bez JavaScript chyb.

## Provedené opravy v této revizi

- Homepage dostala datovou vrstvu `inc/content.php` se schválenými fallbacky.
- Front page nově čte obsah sekcí O projektu, Popis areálu, Jak bude areál fungovat, Bezpečnost / Doprava / Životní prostředí a Přínosy z editovatelných dat.
- Patička nově čte investorské údaje a informační upozornění z editovatelných dat.
- Companion plugin má samostatné metaboxy pro jednotlivé obsahové části homepage.
- Obrázky v editovatelných sekcích lze měnit přes WordPress media modal včetně alt textů.
- FAQ už se needituje jako ruční JSON, ale přes repeater s otázkou, odpovědí, pořadím, přidáním a odebráním položky.
- Přínosy mají editovatelné karty, pořadí a výběr z předdefinovaných ikon.
- Dokumentace byla aktualizována podle nové redakční administrace.
- Produkční ZIPy byly znovu vytvořeny včetně nových admin assetů pluginu.

## Lokálně ověřeno po opravě

- JavaScript syntaxe webu: `node --check wordpress/wp-content/themes/statek-cholupice/assets/js/main.js` prošla bez chyby.
- JavaScript syntaxe adminu: `node --check wordpress/wp-content/plugins/statek-cholupice-core/assets/admin-homepage.js` prošla bez chyby.
- Kontrola diagnostických výpisů: ve WordPress PHP/JS souborech nebyl nalezen `console.log`.
- Kontrola starého JSON editoru FAQ: ve WordPress PHP souborech nebyl nalezen původní text „FAQ položky JSON“.
- ZIP balíčky byly znovu vytvořeny skriptem `tools/package-wordpress.py`.
- Test extrakce ZIPů prošel: jedna kořenová složka, dopředná lomítka, žádné absolutní cesty.
- Výsledné velikosti: `statek-cholupice-theme.zip` 19 462 896 B, `statek-cholupice-core.zip` 12 512 B.
- Manifest potvrzuje 142 položek v šabloně a 6 položek v pluginu včetně `assets/admin-homepage.css` a `assets/admin-homepage.js`.

## Neprovedené kontroly v tomto lokálním prostředí

Na tomto počítači není dostupný PHP runtime ani čistá WordPress instalace, proto zde nebylo možné znovu ověřit:

- PHP lint nově upravených PHP souborů,
- aktivaci šablony a pluginu po této konkrétní opravě,
- WP_DEBUG=true v běžícím WordPressu,
- reálné uložení všech nových metaboxů v administraci,
- reálné odeslání kontaktního formuláře přes `wp_mail()`,
- chování media modalu v běžící administraci WordPressu.

Tyto body je potřeba krátce potvrdit na staging WordPressu před produkčním nasazením.
