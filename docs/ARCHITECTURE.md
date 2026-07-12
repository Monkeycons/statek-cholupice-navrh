# Architektura

Zdroj vizuálního návrhu je aktuální lokální statický web. WordPress balík je rozdělený na:

- šablonu `statek-cholupice`,
- companion plugin `statek-cholupice-core`.

Šablona řeší prezentaci, layout, responzivní obrázky, navigaci, homepage, archiv novinek a metadata.

Plugin řeší:

- kontaktní REST endpoint,
- globální nastavení e-mailu a URL zásad,
- idempotentní inicializaci homepage, stránky Novinky, blog indexu a primárního menu,
- nativní metaboxová pole pro redakční editaci homepage bez ACF,
- media modal pro výměnu obrázků a repeater rozhraní pro FAQ a opakovatelné seznamy.

Výchozí schválený obsah homepage je uložen ve fallback helperu šablony `inc/content.php`. Pokud v administraci není uložená hodnota, front-end použije tento fallback. Uložená redakční data se drží jako post meta stránky nastavené jako `page_on_front`; patička je napojená na stejný zdroj, protože jde o obsah konkrétní prezentační microsite.

Obrázky jsou obsloužené helperem `statek_cholupice_picture()`, který skládá AVIF, WebP a JPEG fallback varianty z `assets/images/optimized`. Hledání variant a čtení rozměrů se cachuje v rámci jednoho requestu, aby se neopakovalo `glob()` a `getimagesize()` pro stejné soubory. Velké originály zůstávají v pracovní složce jako zdroj, ale nejsou balené do produkčního ZIPu šablony.
