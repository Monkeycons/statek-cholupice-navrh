# Hosting security

Tento dokument popisuje doporučené nastavení hostingu pro nasazení webu Statek Cholupice. Hodnoty ověřte proti konkrétnímu hostingu, CDN a případným pluginům před spuštěním do produkce.

## HTTPS a přesměrování

- Vynutit přesměrování všech HTTP požadavků na HTTPS.
- Zkontrolovat, že WordPress `home` a `siteurl` používají `https://`.
- Po nasazení ověřit, že smíšený obsah není blokován pro obrázky, fonty, CSS ani JavaScript.
- HSTS zapnout až po ověření, že HTTPS funguje správně pro hlavní doménu i případné subdomény.

Doporučený HSTS po ověření:

```http
Strict-Transport-Security: max-age=31536000; includeSubDomains
```

`preload` přidávejte pouze tehdy, pokud je doména dlouhodobě připravená na permanentní HTTPS.

## Doporučené security headers

```http
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()
```

Content Security Policy doporučujeme nasadit až po samostatném testu ve stagingu, protože web používá WordPress administraci, REST API, obrázky, fonty a případně analytiku. Začněte v režimu `Content-Security-Policy-Report-Only`.

## Ochrana administrace

- Používat silná hesla a dvoufaktorové ověření pro administrátory.
- Omezit počet administrátorských účtů na minimum.
- Zakázat nepoužívané účty a pravidelně kontrolovat role uživatelů.
- Na hostingu nebo WAF zapnout rate limiting pro `/wp-login.php` a `/wp-admin/`.
- Zvážit omezení přístupu do administrace na povolené IP adresy, pokud to pracovní režim klienta umožní.
- Vypnout editaci souborů ve WordPress administraci:

```php
define( 'DISALLOW_FILE_EDIT', true );
```

## XML-RPC

Pokud web nepoužívá Jetpack, mobilní aplikaci WordPress nebo jiné napojení vyžadující XML-RPC, doporučujeme XML-RPC omezit nebo vypnout na úrovni hostingu/WAF.

Minimální varianta je blokovat metodu `system.multicall` a rate-limitovat požadavky na `/xmlrpc.php`.

## Kontaktní formulář a antispam

- Formulář má nonce, honeypot, IP rate limit a ochranu proti příliš rychlému odeslání.
- Skutečné doručení e-mailu není ověřené v Playgroundu. Test doručení proveďte až na staging hostingu se skutečnou mail konfigurací.
- Pro vyšší ochranu je připravitelná integrace Cloudflare Turnstile. Neaktivovat bez vlastních site/secret klíčů a bez staging testu.

## Zálohy a aktualizace

- Zapnout automatické denní zálohy databáze a souborů.
- Držet alespoň 14 až 30 dní obnovitelných záloh podle možností hostingu.
- Před aktualizací WordPressu, pluginů nebo PHP verze vytvořit ruční bod obnovy.
- Aktualizace nejdříve ověřit na stagingu, zejména kontaktní formulář, homepage metaboxy a obrázkovou pipeline.
- Pravidelně kontrolovat logy PHP chyb, bezpečnostní logy hostingu a stav cron úloh.
