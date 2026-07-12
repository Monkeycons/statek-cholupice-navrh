# Známá omezení

- Lokální prostředí neobsahuje PHP ani čistou WordPress instalaci, proto nebyl proveden runtime test aktivace šablony, aktivace pluginu ani reálné odeslání přes `wp_mail()`.
- Kontaktní endpoint vrací stav podle výsledku `wp_mail()`, ale samotné `wp_mail()` negarantuje doručení e-mailu. Na cílovém hostingu je nutné ověřit SMTP / mailer konfiguraci.
- Homepage má nativní editaci hlavních textů, FAQ a kontaktního bloku. Zbývající schválené prezentační sekce jsou záměrně pevné, aby zůstala zachovaná grafika a rozložení.
- ZIPy jsou připravené jako release candidate. Před produkčním nasazením je potřeba provést test na čistém WordPressu.
