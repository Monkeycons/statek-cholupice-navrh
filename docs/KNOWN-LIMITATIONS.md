# Známá omezení

- Lokální prostředí neobsahuje PHP runtime ani běžící čistou instalaci WordPressu. PHP lint, aktivace balíčků a reálné uložení metaboxů proto musí být ověřeny v následujícím WordPress Playground nebo staging testu.
- Chování WordPress media modalu, generování `srcset` pro nově nahranou hero přílohu a návrat k fallbacku jsou implementované, ale čekají na potvrzení v reálné administraci.
- Kontaktní endpoint vrací stav podle `wp_mail()`, které samo o sobě negarantuje doručení. Na cílovém hostingu je nutné ověřit SMTP nebo jinou mailer konfiguraci.
- Řazení používá číselné pole; drag and drop není součástí této verze.
- ZIPy jsou release candidate a před produkčním nasazením vyžadují runtime test na čistém WordPressu.
## Stav po 1.1.0-rc.2

- Sablona byla zvysena na `1.1.0-rc.2`; companion plugin zustava `1.1.0-rc.1`.
- V teto revizi nebyl menen administracni model, kontaktni formular ani pluginovy kod.
- Ziva vizualni kontrola ve WordPress Playgroundu je doporucena zejmena pro otevrene tematicke sekce a blok Novinek na sirce 1440 px, 1920 px a mobilu 390 px.

## Stav po 1.1.0-rc.3

- Sablona i companion plugin jsou ve verzi `1.1.0-rc.3`.
- Doručení e-mailu z kontaktního formuláře není považováno za ověřené. Playground ověřuje pouze validaci, REST odpověď a chybové hlášení maileru; skutečné doručení se musí potvrdit na staging hostingu.
- Cloudflare Turnstile není aktivní, protože nejsou vložené site/secret klíče. Integraci je vhodné přidat až po staging testu.
