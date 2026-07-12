# Architektura

Zdroj vizuálního návrhu je aktuální lokální statický web. WordPress balík je rozdělený na:

- šablonu `statek-cholupice`,
- companion plugin `statek-cholupice-core`.

Šablona řeší prezentaci, layout, responzivní obrázky, navigaci, homepage, archiv novinek a metadata.

Plugin řeší:

- kontaktní REST endpoint,
- globální nastavení e-mailu a URL zásad,
- idempotentní inicializaci homepage, stránky Novinky, blog indexu a primárního menu,
- nativní metaboxová pole pro vybrané texty homepage.

Obrázky jsou obsloužené helperem `statek_cholupice_picture()`, který skládá AVIF, WebP a JPEG fallback varianty z `assets/images/optimized`. Hledání variant a čtení rozměrů se cachuje v rámci jednoho requestu, aby se neopakovalo `glob()` a `getimagesize()` pro stejné soubory. Velké originály zůstávají v pracovní složce jako zdroj, ale nejsou balené do produkčního ZIPu šablony.
