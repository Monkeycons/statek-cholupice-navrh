# Známá omezení

- Lokální prostředí neobsahuje PHP ani čistou WordPress instalaci, proto nebyl proveden runtime test aktivace šablony, aktivace pluginu ani reálné odeslání přes `wp_mail()`.
- Kontaktní endpoint vrací stav podle výsledku `wp_mail()`, ale samotné `wp_mail()` negarantuje doručení e-mailu. Na cílovém hostingu je nutné ověřit SMTP / mailer konfiguraci.
- Homepage má nativní editaci hlavních obsahových sekcí přes post meta na stránce nastavené jako `page_on_front`. Metaboxy jsou záměrně dostupné pouze na této stránce, aby se obsah nespravoval duplicitně na běžných stránkách.
- Editace je navržena bez ACF a bez dalších pluginů. Pokročilé typy polí, drag-and-drop řazení nebo redakční workflow proto nejsou součástí této verze.
- ZIPy jsou připravené jako release candidate. Před produkčním nasazením je potřeba provést test na čistém WordPressu.
