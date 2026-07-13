# Známá omezení

- Lokální prostředí neobsahuje PHP runtime ani běžící čistou instalaci WordPressu. PHP lint, aktivace balíčků a reálné uložení metaboxů proto musí být ověřeny v následujícím WordPress Playground nebo staging testu.
- Chování WordPress media modalu, generování `srcset` pro nově nahranou hero přílohu a návrat k fallbacku jsou implementované, ale čekají na potvrzení v reálné administraci.
- Kontaktní endpoint vrací stav podle `wp_mail()`, které samo o sobě negarantuje doručení. Na cílovém hostingu je nutné ověřit SMTP nebo jinou mailer konfiguraci.
- Řazení používá číselné pole; drag and drop není součástí této verze.
- ZIPy jsou release candidate a před produkčním nasazením vyžadují runtime test na čistém WordPressu.
