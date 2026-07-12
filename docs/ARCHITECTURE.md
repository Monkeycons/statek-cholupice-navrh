# Architektura

Zdroj pravdy: `github-pages/index.html`, lokální commit `1a8e2b5`.

Šablona je klasická lehká WordPress šablona s `theme.json`. Vzhled je převeden ze statického webu do `assets/css/main.css`, prezentační chování do `assets/js/main.js`.

Companion plugin `statek-cholupice-core` řeší kontaktní formulář a základní inicializaci domovské stránky.

Domovská stránka je v `front-page.php`, hlavička a patička jsou oddělené v `header.php` a `footer.php`. Novinky používají běžné příspěvky WordPressu a na homepage se načítají přes `statek_cholupice_news_items()`.

Schválené rendery jsou uložené v `assets/images`. Vedle nich jsou připravené optimalizované WebP varianty v `assets/images/optimized`, aby je bylo možné při produkčním nasazení napojit podle finální strategie hostingu a cache.
