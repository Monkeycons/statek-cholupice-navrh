# QA Report

Zdrojový commit: `1a8e2b5 Improve before after comparison control`.
Tag reference: `approved-static-v1`.
Pracovní větev: `feat/wordpress-production-theme`.

Provedeno:

- převod vychází z aktuální lokální verze, ve které jsou opravené kotvy i interaktivní porovnání Před / Po;
- `tools/build-wordpress-theme.mjs` prošel syntaktickou kontrolou přes Node;
- veřejný `assets/js/main.js` prošel syntaktickou kontrolou přes Node;
- odstraněny zbytky po sekci „Tři principy proměny“ z HTML i CSS výstupu;
- doplněn lokální font Inter a licence;
- vytvořeno 39 WebP variant obrazových assetů;
- kontaktní formulář je napojený na REST endpoint companion pluginu a používá nonce, honeypot a jednoduché rate limiting pravidlo.

Neprovedeno:

- PHP syntax check a runtime test WordPressu, protože v prostředí není dostupné PHP ani lokální WordPress instalace;
- vizuální kontrola uvnitř skutečné WordPress instalace.
