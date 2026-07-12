<?php
/**
 * Úvodní stránka.
 *
 * @package StatekCholupice
 */

get_header();
?>
<main id="main-content">

    <section class="hero" id="uvod">
      <?php
      echo statek_cholupice_picture(
        'images/hero_vizualizace/cholupice-hero-super-render-web-spravne.png',
        '',
        array(
          'picture_class' => 'hero-media',
          'class'         => 'hero-media-image',
          'sizes'         => '100vw',
          'loading'       => 'eager',
          'fetchpriority' => 'high',
          'aria_hidden'   => true,
        )
      );
      ?>
      <div class="hero-inner">
        <div class="kicker"><?php echo esc_html( statek_cholupice_home_meta( 'statek_home_hero_kicker', 'Revitalizace brownfieldu' ) ); ?></div>
        <h1><?php echo nl2br( esc_html( statek_cholupice_home_meta( 'statek_home_hero_title', 'Nový život pro Statek Cholupice' ) ) ); ?></h1>
        <p><?php echo esc_html( statek_cholupice_home_meta( 'statek_home_hero_text', 'Citlivá přestavba historického areálu propojí bydlení, služby pro obyvatele, moderní výrobu a respekt k místu.' ) ); ?></p>
        <div class="hero-actions">
          <a class="button" href="#projekt"><?php echo esc_html( statek_cholupice_home_meta( 'statek_home_hero_primary', 'Poznat projekt' ) ); ?></a>
          <a class="button secondary" href="#prinosy"><?php echo esc_html( statek_cholupice_home_meta( 'statek_home_hero_secondary', 'Dobrý soused' ) ); ?></a>
        </div>
      </div>
    </section>

    <section class="project-story" id="projekt">
      <div class="wrap">
        <h2 class="visually-hidden">O projektu</h2>
        <div class="story-block">
          <div class="story-copy">
            <h3>Nové využití pro místo, které dlouho čekalo na změnu</h3>
            <p>Naším cílem je vrátit život místu, které bylo dlouhé roky nevyužité a chátralo. Přestavbou nefunkčního statku postupně vznikne moderní a funkční prostor, který propojí kvalitní českou strojírenskou výrobu, nové pracovní příležitosti, bydlení i občanskou vybavenost.</p>
            <p>Bytový dům nabídne klidné bydlení citlivě navazující na okolní zástavbu. V jeho přízemí vzniknou komerční prostory pro obchod, kavárnu nebo služby každodenní potřeby, které mohou přirozeně oživit cholupickou náves a vytvořit zázemí pro místní obyvatele.</p>
            <p>Výroba bude probíhat uvnitř moderních budov bez výrazného hluku, zápachu nebo intenzivní dopravy.</p>
          </div>
          <figure class="story-media">
            <?php echo statek_cholupice_picture( 'images/statek_web_premium/hero-vjezd-preview-v4-lide-obchod.png', 'Vizualizace vstupu do areálu s obchody a novým vjezdem', array( 'sizes' => '(max-width: 900px) 100vw, 50vw' ) ); ?>
          </figure>
        </div>
        <div class="story-block reverse">
          <div class="before-after" data-before-after>
            <?php echo statek_cholupice_picture( 'images/statek_web_premium/pred_premium_documentary.png', 'Současný stav areálu před revitalizací', array( 'sizes' => '(max-width: 900px) 100vw, 50vw' ) ); ?>
            <?php echo statek_cholupice_picture( 'images/statek_web_premium/po_premium_dss_van_logo_left.png', 'Prémiová vizualizace navrženého stavu areálu s dodávkou DSS', array( 'class' => 'after', 'sizes' => '(max-width: 900px) 100vw, 50vw' ) ); ?>
            <span class="ba-label before">Současný stav</span>
            <span class="ba-label after-label">Navrhovaná podoba</span>
            <span class="ba-handle" aria-hidden="true"></span>
            <input type="range" min="0" max="100" value="50" aria-label="Porovnání současného stavu a navrhované podoby">
          </div>
          <div class="story-copy">
            <h3>Revitalizace brownfieldu s respektem k historii</h3>
            <p>Součástí projektu je rozsáhlá přestavba celého areálu, odstranění starých ekologických zátěží i záchrana historického špejcharu ze 16. století.</p>
            <p>Celková investice dosáhne přibližně jedné miliardy korun. Věříme, že projekt může být příkladem moderní a odpovědné přestavby brownfieldu, která propojí průmysl, služby, bydlení i respekt k historii a okolnímu prostředí.</p>
            <p>Společnost DSS, investor projektu, patří mezi české výrobce specializující se na ruční palné zbraně. Dlouhodobě působí v oblasti obranného průmyslu, kde staví na odbornosti, precizní strojírenské výrobě a respektu k tradičnímu československému zbrojařskému řemeslu.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="site-area" id="popis-arealu">
      <div class="wrap">
        <div class="site-area-heading">
          <h2>Šest částí, jeden živý areál</h2>
          <p class="lead">Promyšlené spojení různých funkcí vrací Statku Cholupice život.</p>
        </div>
        <div class="site-area-layout">
          <div class="site-area-tabs" role="tablist" aria-label="Části areálu" aria-orientation="horizontal">
            <button class="site-area-tab" id="site-area-tab-1" role="tab" aria-selected="true" aria-controls="site-area-panel-1" tabindex="0"><span>01</span>Bytový dům</button>
            <button class="site-area-tab" id="site-area-tab-2" role="tab" aria-selected="false" aria-controls="site-area-panel-2" tabindex="-1"><span>02</span>Komerční prostory</button>
            <button class="site-area-tab" id="site-area-tab-3" role="tab" aria-selected="false" aria-controls="site-area-panel-3" tabindex="-1"><span>03</span>Administrativní centrum</button>
            <button class="site-area-tab" id="site-area-tab-4" role="tab" aria-selected="false" aria-controls="site-area-panel-4" tabindex="-1"><span>04</span>Výrobní prostory</button>
            <button class="site-area-tab" id="site-area-tab-5" role="tab" aria-selected="false" aria-controls="site-area-panel-5" tabindex="-1"><span>05</span>Vývojové centrum</button>
            <button class="site-area-tab" id="site-area-tab-6" role="tab" aria-selected="false" aria-controls="site-area-panel-6" tabindex="-1"><span>06</span>Parkovací dům</button>
          </div>
          <div class="site-area-content">
            <article class="site-area-panel" id="site-area-panel-1" role="tabpanel" aria-labelledby="site-area-tab-1" aria-hidden="false">
              <figure class="site-area-media"><?php echo statek_cholupice_picture( 'images/statek_web_premium/1_16_photo_premium_residential.png', 'Prémiová vizualizace bytového domu', array( 'sizes' => '(max-width: 980px) 100vw, 58vw' ) ); ?></figure>
              <div class="site-area-copy"><p class="site-area-label">Popis areálu · 01</p><h3>Bytový dům</h3><p>Plánovaný bytový dům představuje novou tvář přestavby celého areálu. Navržen je s důrazem na kvalitní a klidné bydlení a počítá se samostatně řešeným parkováním v podzemí i na vlastním pozemku.</p><p>Dům bude mít dvě nadzemní podlaží a obytné podkroví, díky čemuž citlivě naváže na charakter okolní zástavby. Současně přirozeně oddělí veřejný prostor návsi od výrobní části areálu.</p></div>
            </article>
            <article class="site-area-panel" id="site-area-panel-2" role="tabpanel" aria-labelledby="site-area-tab-2" aria-hidden="true">
              <figure class="site-area-media"><?php echo statek_cholupice_picture( 'images/statek_web_premium/1_18_photo_premium_commerce_people_atm.png', 'Prémiová vizualizace komerčních prostor s bankomatem', array( 'sizes' => '(max-width: 980px) 100vw, 58vw' ) ); ?></figure>
              <div class="site-area-copy"><p class="site-area-label">Popis areálu · 02</p><h3>Komerční prostory</h3><p>V přízemí bytového domu vzniknou také komerční prostory. Mohou zde najít místo například menší obchod, kavárna nebo služby každodenní potřeby, jako jsou kadeřnictví, kosmetika či ordinace lékaře.</p><p>Cílem je vytvořit příjemné místo pro místní obyvatele i návštěvníky a podpořit přirozený život v centru Cholupic.</p></div>
            </article>
            <article class="site-area-panel" id="site-area-panel-3" role="tabpanel" aria-labelledby="site-area-tab-3" aria-hidden="true">
              <figure class="site-area-media"><?php echo statek_cholupice_picture( 'images/statek_web_premium/administrativni_centrum_premium.png', 'Vizualizace administrativního centra a špejcharu', array( 'sizes' => '(max-width: 980px) 100vw, 58vw' ) ); ?></figure>
              <div class="site-area-copy"><p class="site-area-label">Popis areálu · 03</p><h3>Administrativní centrum – Špejchar</h3><p>Špejchar projde pečlivou rekonstrukcí. Do původní historické budovy bude citlivě vsazena nová vnitřní konstrukce, která vytvoří moderní kancelářské a reprezentativní prostory například pro jednání s obchodními partnery a zahraničními delegacemi.</p><p>Součástí projektu je také statické zajištění a sanace historických konstrukcí s důrazem na jejich dlouhodobou ochranu.</p></div>
            </article>
            <article class="site-area-panel" id="site-area-panel-4" role="tabpanel" aria-labelledby="site-area-tab-4" aria-hidden="true">
              <figure class="site-area-media"><?php echo statek_cholupice_picture( 'images/statek_web_premium/hala_a_premium.png', 'Vizualizace výrobních prostor', array( 'sizes' => '(max-width: 980px) 100vw, 58vw' ) ); ?></figure>
              <div class="site-area-copy"><p class="site-area-label">Popis areálu · 04</p><h3>Výrobní prostory</h3><p>Ve výrobních prostorách bude probíhat zpracování kovových komponentů – od přípravy a prvotního opracování materiálu přes přesné CNC obrábění až po kontrolu kvality.</p><p>Součástí objektu budou moderní výrobní technologie, automatizované prvky a kontrolované pracovní prostředí odpovídající vysokým nárokům na kvalitu a bezpečnost. Veškeré činnosti budou probíhat uvnitř budovy s minimálním vlivem na okolí.</p></div>
            </article>
            <article class="site-area-panel" id="site-area-panel-5" role="tabpanel" aria-labelledby="site-area-tab-5" aria-hidden="true">
              <figure class="site-area-media"><?php echo statek_cholupice_picture( 'images/statek_web_premium/vyvojove_centrum_premium_engineers.png', 'Vizualizace vývojového centra se dvěma inženýry', array( 'sizes' => '(max-width: 980px) 100vw, 58vw' ) ); ?></figure>
              <div class="site-area-copy"><p class="site-area-label">Popis areálu · 05</p><h3>Vývojové centrum</h3><p>Vývojové centrum vznikne vestavbou nového objektu do stávajících historických konstrukcí areálu. Prostor bude sloužit pro vývoj, výzkum a technickou přípravu nových řešení.</p><p>Návrh zachová původní charakter objektu a zároveň mu přinese nové a dlouhodobě udržitelné využití.</p></div>
            </article>
            <article class="site-area-panel" id="site-area-panel-6" role="tabpanel" aria-labelledby="site-area-tab-6" aria-hidden="true">
              <figure class="site-area-media"><?php echo statek_cholupice_picture( 'images/statek_web_premium/parkovaci_dum_premium.png', 'Vizualizace parkovacího domu', array( 'sizes' => '(max-width: 980px) 100vw, 58vw' ) ); ?></figure>
              <div class="site-area-copy"><p class="site-area-label">Popis areálu · 06</p><h3>Parkovací dům</h3><p>Dvoupodlažní parkovací objekt určený pro zaměstnance nabídne 121 parkovacích míst. Navržen je tak, aby omezil dopravní zatížení okolí a umožnil plynulý vjezd i výjezd bez tvorby kolon.</p><p>Parkování bude oddělené od výrobních částí areálu.</p></div>
            </article>
          </div>
        </div>
      </div>
    </section>

    <section class="site-operation-section" id="provoz-arealu">
      <div class="wrap">
        <div class="site-operation-intro">
          <h2>Jak bude areál fungovat</h2>
          <p class="site-operation-motto">Promyšlený provoz uvnitř. Klid pro okolí.</p>
          <p>V areálu bude probíhat moderní strojírenská výroba založená na přesném CNC obrábění kovových komponentů, jejich kontrole a kompletaci. Součástí provozu budou také skladovací prostory, související logistické činnosti, administrativní zázemí a pracoviště zaměřená na vývoj a zdokonalování výrobních technologií.</p>
          <p>Výroba bude probíhat převážně v uzavřených halách s využitím moderních technologií, automatizace a kontrolovaných výrobních procesů. Provoz je navržen tak, aby splňoval všechny platné bezpečnostní, hygienické a environmentální požadavky a měl minimální dopad na okolní zástavbu i kvalitu života v městské části.</p>
        </div>
        <div class="site-operation-columns">
          <section class="site-operation-box good"><h4>Co bude součástí provozu</h4><ul class="site-operation-list"><li><strong>Přesné CNC obrábění</strong><span>Výroba kovových komponentů založená na přesném CNC obrábění.</span></li><li><strong>Kontrola a kompletace</strong><span>Kontrola vyrobených komponentů a jejich kompletace.</span></li><li><strong>Vývoj výrobních technologií</strong><span>Pracoviště zaměřená na vývoj a zdokonalování výrobních technologií.</span></li><li><strong>Skladování, logistika a administrativa</strong><span>Skladovací prostory, související logistické činnosti a administrativní zázemí provozu.</span></li></ul></section>
          <section class="site-operation-box neutral"><h4>Co nebude součástí provozu</h4><ul class="site-operation-list"><li><strong>Žádná střelba ani střelnice</strong><span>V areálu nebude probíhat střelba ani praktické testování hotových výrobků. Nebude zde střelnice.</span></li><li><strong>Žádné skladování ani nakládání s výbušninami a municí</strong><span>Výbušniny ani munice se zde nebudou skladovat a nebude s nimi ani jinak nakládáno.</span></li><li><strong>Žádné chemické zušlechťování oceli</strong><span>V areálu nebude probíhat chemické zušlechťování oceli ani jiné nebezpečné práce s chemikáliemi.</span></li><li><strong>Žádný těžký průmysl</strong><span>Provoz nebude zdrojem emisí, výrazného hluku ani znečištění, které by zatěžovalo okolí.</span></li></ul></section>
        </div>
      </div>
    </section>

      <section class="topics-section">
      <div class="wrap">
        <div class="topics">
          <article class="topic topic-stacked topic-safety" id="bezpecnost">
            <figure class="topic-illustration"><?php echo statek_cholupice_picture( 'images/statek_web_premium/bezpecnost-vstup-kontrola.png', 'Kontrolovaný vstup zaměstnance do výrobního prostoru', array( 'sizes' => '(max-width: 980px) 100vw, 46vw' ) ); ?></figure>
            <div class="topic-body">
              <h2>Bezpečnost</h2>
              <p class="topic-motto">Bezpečnost postavená na lidech, technologiích a jasných pravidlech</p>
              <p>Dělat věci správně je pro nás naprosto zásadní. Celý areál je proto navržen jako víceúrovňový systém kombinující fyzická, technologická i organizační opatření, na jejichž přípravě spolupracujeme s evropskými technologickými lídry a odbornými institucemi.</p>
              <details class="topic-accordion">
                <summary>Číst více</summary>
                <div class="safety-copy">
                  <p>Přesto naše společnost sází především na stabilní kmen zaměstnanců, převážně z České republiky a zemí Evropské unie. Jádro týmu tvoří dlouhodobě prověření zaměstnanci s potřebnou odborností a bezpečnostní spolehlivostí.</p>
                  <p>Provoz podléhá přísné legislativě České republiky, víceúrovňovým a periodickým kontrolám ze strany státních orgánů.</p>
                  <p>Výroba je komplexně monitorována a díky implementaci nejmodernějšího trasování a evidence není možné jakékoli zneužití nebo nedovolené vynášení.</p>
                  <p>Bezpečnost provozu, ochrana areálu i výrobních procesů budou odpovídat standardům ISO 9001 a AQAP 2110 používaným v rámci NATO. Tyto standardy vyžadují právě například přesnou evidenci výroby, dohledatelnost jednotlivých součástek a přísnou kontrolu kvality v celém výrobním procesu.</p>
                  <p>V areálu nebude probíhat žádná střelba ani praktické testování našich výrobků. Tyto činnosti probíhají výhradně mimo areál na specializovaných pracovištích.</p>
                </div>
              </details>
            </div>
          </article>
          <article class="topic topic-stacked" id="doprava">
            <figure class="topic-illustration"><?php echo statek_cholupice_picture( 'images/statek_web_premium/doprava-areal-delivery-truck-focus.png', 'Vizualizace dopravy v areálu s výraznou dodávkou před výrobní halou', array( 'sizes' => '(max-width: 980px) 100vw, 46vw' ) ); ?></figure>
            <div class="topic-body">
              <h2>Doprava</h2>
              <p class="topic-motto">Méně dopravy než v minulosti. Více klidu pro okolí.</p>
              <p>Doprava spojená s provozem areálu bude oproti původnímu využití areálu velmi omezená. Předpokládá se zhruba jedna až dvě menší dodávky (do 6 tun) denně, tedy objem zásobování srovnatelný například s běžným obchodem.</p>
              <details class="topic-accordion">
                <summary>Číst více</summary>
                <div class="safety-copy">
                  <p>Ve srovnání s minulostí tak půjde o výrazné zklidnění. V době fungování zemědělského družstva se v areálu běžně pohybovala těžká technika, traktory, kombajny, nákladní vozy, zemědělské stroje i zásobování spojené se sklady a chovem zvířat. S tím souviselo také znečišťování komunikací bahnem a vyšší provoz v okolí. To v tomto případě nehrozí.</p>
                  <p>Do areálu samozřejmě nebudou jezdit jen dodávky, ale také zaměstnanci. Pro ně jsme připravili krytý parkovací dům se 121 parkovacími místy. Vjezd do areálu je navržen tak, aby nevznikaly kolony, nedocházelo k omezení plynulosti dopravy a dopad na okolí byl co nejmenší.</p>
                </div>
              </details>
            </div>
          </article>
          <article class="topic topic-stacked" id="zivotni-prostredi">
            <figure class="topic-illustration"><?php echo statek_cholupice_picture( 'images/karousel3_premium_dss_v4_4k_preview.jpg', 'Prémiová vizualizace zeleného veřejného prostoru a výsadby v areálu', array( 'sizes' => '(max-width: 980px) 100vw, 46vw' ) ); ?></figure>
            <div class="topic-body">
              <h2>Životní prostředí</h2>
              <p class="topic-motto">Třikrát více zeleně a provoz šetrný k okolí.</p>
              <p>Výroba sama o sobě není hlučná a celý provoz je umístěn uvnitř hal. Vznikat budou především běžné odpady, jako jsou kovové odřezky, papír nebo dřevo. Veškeré odpady budou tříděny a předávány k recyklaci v souladu s platnou legislativou. Součástí přípravy projektu je také zpracování dokumentace k nakládání s odpady a posouzení vlivu provozu na okolí.</p>
              <details class="topic-accordion">
                <summary>Číst více</summary>
                <div class="safety-copy">
                  <p>Při výrobě, kterou plánujeme, se nepoužívají chemikálie, vyjma těch běžných jako jsou olej, ředidlo či úklidové prostředky. Náročnější procesy, jako je chemické nebo tepelné zpracování kovů, budou probíhat mimo areál u specializovaných partnerů.</p>
                  <p>Součástí projektu na přestavbu areálu je odstranění staré ekologické zátěže, která v místě zůstala. Budou tak odvezeny nevyhovující materiály, které zde zůstaly z minulosti.</p>
                  <p>Velký důraz klademe i na zeleň a celkovou podobu areálu.</p>
                  <p>Nemocné a nebezpečné stromy budou odstraněny na základě povolení příslušných orgánů státní správy a nahrazeny novou výsadbou. Celkové množství zeleně by mělo být přibližně trojnásobné oproti současnému stavu.</p>
                  <p>V plánu jsou také zelené střechy na halách a nová vodní plocha, která pomůže přirozeně ochlazovat okolní prostředí. Ta může zároveň posloužit jako zásobárna vody pro případ požáru.</p>
                  <p>Projekt počítá s dlouhodobě udržitelným provozem a splněním všech legislativních požadavků v oblasti ochrany životního prostředí, včetně standardů systému environmentálního řízení ISO 14000.</p>
                  <p>Významnou součástí projektu je také komplexní sanace celého areálu a odstranění historických ekologických zátěží, které zde zůstaly z minulého využívání území. V rámci přípravy projektu byly identifikovány a postupně odstraněny nevyhovující materiály a staré ekologické zátěže, včetně pozůstatků technické infrastruktury z minulosti. Z areálu již byly odvezeny stovky tun odpadu a kontaminovaných materiálů, které představovaly dlouhodobý problém pro lokalitu.</p>
                </div>
              </details>
            </div>
          </article>
        </div>
      </div>
    </section>

    <section class="band" id="prinosy">
      <div class="wrap">
        <div class="benefits-heading">
          <h2>Co projekt přinese Cholupicím</h2>
          <p class="benefits-motto">Nová pracovní místa, zachráněné dědictví a smysluplné využití zanedbaného areálu.</p>
        </div>
        <p class="benefits-intro">Proměna bývalého hospodářského areálu přinese více než jen nové budovy. Vytvoří pracovní příležitosti, podpoří místní podnikatele a služby, zachrání cenné historické objekty a přispěje ke kultivaci celého okolí. Chceme být dobrým sousedem, který svůj úspěch sdílí s místní komunitou a dlouhodobě podporuje život v Cholupicích.</p>
        <div class="benefits">
          <article class="benefit"><div class="icon"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="7" width="18" height="13" rx="2"></rect><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><path d="M3 12h18"></path><path d="M10 12v2h4v-2"></path></svg></div><h3>Práce blízko domova</h3><p>Nový areál vytvoří pracovní příležitosti pro různé profese a úrovně kvalifikace — od výroby, údržby a logistiky přes administrativu až po vývoj a vedoucí pozice. Nové zaměstnání tak mohou najít také lidé z Cholupic a blízkého okolí. Půjde o práci v technologicky vyspělém a perspektivním oboru, který nabízí dlouhodobou stabilitu i prostor pro profesní růst.</p></article>
          <article class="benefit"><div class="icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h11v9H3z"></path><path d="M14 9h4l3 3v3h-7z"></path><circle cx="7" cy="17" r="2"></circle><circle cx="18" cy="17" r="2"></circle></svg></div><h3>Zakázky a zákazníci pro místní</h3><p>Každodenní provoz areálu vytvoří poptávku po různých službách, například v oblasti autodopravy, údržby nebo zásobování. Do těchto činností se mohou zapojit podnikatelé, živnostníci a firmy z okolí. V areálu bude zároveň pracovat řada lidí, kteří si rádi zajdou na oběd nebo po práci posedět do místní hospody.</p></article>
          <article class="benefit"><div class="icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 10v10h16V10"></path><path d="m3 10 2-6h14l2 6"></path><path d="M3 10a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"></path><path d="M8 20v-5h8v5"></path></svg></div><h3>Nové služby v Cholupicích</h3><p>Část obnoveného areálu navazující na náves může nabídnout prostory pro menší provozovny a služby využitelné místními obyvateli. Může jít například o drobný obchod, občerstvení, ordinaci lékaře nebo zázemí pro místního podnikatele. Statek se tak může znovu přirozeně zapojit do každodenního života Cholupic.</p></article>
          <article class="benefit"><div class="icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6z"></path><path d="m8.5 12 2.2 2.2 4.8-5"></path></svg></div><h3>Nový život a větší bezpečí</h3><p>Dlouhodobě chátrající a nevyužívaný areál se stal útočištěm lidí bez domova a představoval bezpečnostní riziko pro své okolí. Kompletní rekonstrukce, vyčištění a každodenní péče vrátí místu důstojnou podobu a smysluplné využití. Zmizí staré ekologické zátěže i nevyhovující materiály a zanedbaný prostor se promění v bezpečný a upravený areál.</p></article>
          <article class="benefit"><div class="icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-6 9 6"></path><path d="M5 10h14"></path><path d="M6 10v8M10 10v8M14 10v8M18 10v8"></path><path d="M3 18h18v2H3z"></path></svg></div><h3>Historie, která zůstane</h3><p>Historický špejchar ze 16. století a votivní deska z roku 1883 budou zachovány a citlivě obnoveny. Špejchar bude adaptován pro nové využití formou vestavby „nového do starého“, aby mohla zůstat zachována jeho původní konstrukce. Bez rekonstrukce by objekt dál chátral a jeho zánik by byl jen otázkou času.</p></article>
          <article class="benefit benefit-neighbor"><div class="icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 8.6c0 5.3-8.8 10.4-8.8 10.4S3.2 13.9 3.2 8.6A4.6 4.6 0 0 1 12 6a4.6 4.6 0 0 1 8.8 2.6Z"></path></svg></div><h3>Dobrý soused</h3><p>Dobrý soused se nepozná podle slov, ale podle toho, co dělá pro své okolí. Když se bude areálu dařit, chceme část jeho úspěchu vracet místní komunitě — podporou spolků, kulturních a sportovních akcí, aktivit pro děti a seniory i dalších dobrých nápadů, které zlepšují život v Cholupicích.</p></article>
        </div>
      </div>
    </section>

    <?php get_template_part( 'template-parts/home/news' ); ?>

    <section class="faq-section" id="kontakt">
      <div class="wrap faq-contact">
        <div class="faq-heading">
          <h2><?php echo esc_html( statek_cholupice_home_meta( 'statek_home_faq_heading', 'Na co se nás lidé ptají nejčastěji' ) ); ?></h2>
          <p class="benefits-motto"><?php echo esc_html( statek_cholupice_home_meta( 'statek_home_faq_motto', 'Vše podstatné o proměně statku, budoucím provozu a jeho dopadech na okolí.' ) ); ?></p>
          <p class="faq-intro"><?php echo esc_html( statek_cholupice_home_meta( 'statek_home_faq_intro_1', 'Uvědomujeme si, že statek je významnou součástí Cholupic, a rozumíme proto tomu, že jeho plánovaná proměna vyvolává otázky. Na ty nejčastější zde otevřeně odpovídáme.' ) ); ?></p>
          <p class="faq-intro"><?php echo wp_kses_post( statek_cholupice_home_meta( 'statek_home_faq_intro_2', 'Pokud odpověď na svou otázku nenajdete, <a href="#faq-contact-form">napište nám</a>. Vaše podněty budeme průběžně zpracovávat a nejčastější otázky doplňovat.' ) ); ?></p>
        </div>

        <div class="faq-list">
          <?php foreach ( statek_cholupice_faq_items() as $index => $faq_item ) : ?>
            <?php $answer_id = 'faq-answer-' . ( $index + 1 ); ?>
            <div class="faq-item">
              <button class="faq-question" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $answer_id ); ?>"><?php echo esc_html( $faq_item['question'] ); ?></button>
              <div class="faq-answer" id="<?php echo esc_attr( $answer_id ); ?>" aria-hidden="true">
                <div class="faq-answer-inner"><?php echo wp_kses_post( $faq_item['answer'] ); ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="faq-contact-block">
          <div class="faq-contact-copy">
            <p class="faq-contact-kicker">KONTAKT</p>
            <h3><?php echo esc_html( statek_cholupice_home_meta( 'statek_home_contact_heading', 'Máte další otázku k projektu?' ) ); ?></h3>
            <p class="faq-contact-motto"><?php echo esc_html( statek_cholupice_home_meta( 'statek_home_contact_motto', 'Zajímá vás něco, co jsme nezodpověděli?' ) ); ?></p>
            <p class="faq-contact-text"><?php echo wp_kses_post( statek_cholupice_home_meta( 'statek_home_contact_text', 'Napište nám prostřednictvím formuláře nebo přímo na <a href="mailto:' . esc_attr( statek_cholupice_contact_email() ) . '">' . esc_html( statek_cholupice_contact_email() ) . '</a>. Vaše podněty nám pomohou průběžně doplňovat informace, které jsou pro Cholupice důležité.' ) ); ?></p>
          </div>

          <form class="faq-contact-form" id="faq-contact-form" novalidate data-contact-form>
            <div class="contact-fields">
              <div class="form-field">
                <label for="contact-name">Jméno <span>(nepovinné)</span></label>
                <input id="contact-name" name="name" type="text" autocomplete="name">
              </div>
              <div class="form-field">
                <label for="contact-email">E-mail</label>
                <input id="contact-email" name="email" type="email" autocomplete="email" required>
              </div>
            </div>
            <div class="form-field form-honeypot" aria-hidden="true">
              <label for="contact-company">Firma</label>
              <input id="contact-company" name="company" type="text" tabindex="-1" autocomplete="off">
            </div>
            <div class="form-field">
              <label for="contact-message">Váš dotaz</label>
              <textarea id="contact-message" name="message" required></textarea>
            </div>
            <button class="button" type="submit">Odeslat dotaz</button>
            <p class="contact-privacy">Odesláním souhlasíte se zpracováním osobních údajů. <a href="<?php echo esc_url( statek_cholupice_privacy_url() ); ?>">Zásady zpracování osobních údajů</a></p>
            <p class="form-status" id="contact-form-status" role="status" aria-live="polite"></p>
          </form>
        </div>
      </div>
    </section>
  
</main>
<?php
get_footer();
