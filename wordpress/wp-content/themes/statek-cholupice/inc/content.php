<?php
/**
 * Editovatelný obsah homepage se schválenými fallbacky.
 *
 * @package StatekCholupice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function statek_cholupice_home_json_meta( string $key, array $fallback ): array {
	$post_id = (int) get_option( 'page_on_front' );
	if ( is_front_page() ) {
		$post_id = get_queried_object_id() ?: $post_id;
	}
	$value = $post_id ? (string) get_post_meta( $post_id, $key, true ) : '';
	if ( '' === trim( $value ) ) {
		return $fallback;
	}
	$decoded = json_decode( $value, true );
	return is_array( $decoded ) ? $decoded : $fallback;
}

function statek_cholupice_text_or_fallback( $value, string $fallback ): string {
	$value = is_string( $value ) ? trim( $value ) : '';
	return '' !== $value ? $value : $fallback;
}

function statek_cholupice_paragraphs_or_fallback( $value, array $fallback, int $limit = 8 ): array {
	if ( ! is_array( $value ) ) {
		return $fallback;
	}
	$paragraphs = array();
	foreach ( array_slice( $value, 0, $limit ) as $paragraph ) {
		$paragraph = is_string( $paragraph ) ? trim( $paragraph ) : '';
		if ( '' !== $paragraph ) {
			$paragraphs[] = $paragraph;
		}
	}
	return $paragraphs ?: $fallback;
}

function statek_cholupice_image_data( $value, array $fallback ): array {
	$value = is_array( $value ) ? $value : array();
	return array(
		'id'   => isset( $value['id'] ) ? absint( $value['id'] ) : 0,
		'path' => $fallback['path'] ?? '',
		'alt'  => statek_cholupice_text_or_fallback( $value['alt'] ?? '', $fallback['alt'] ?? '' ),
	);
}

function statek_cholupice_editable_image( array $image, array $args = array() ): string {
	$defaults = array(
		'class'         => '',
		'picture_class' => '',
		'sizes'         => '100vw',
		'loading'       => 'lazy',
		'decoding'      => 'async',
		'fetchpriority' => '',
	);
	$args = wp_parse_args( $args, $defaults );
	$alt  = $image['alt'] ?? '';

	if ( ! empty( $image['id'] ) ) {
		$attrs = array(
			'alt'      => $alt,
			'loading'  => $args['loading'],
			'decoding' => $args['decoding'],
			'sizes'    => $args['sizes'],
		);
		if ( $args['class'] ) {
			$attrs['class'] = $args['class'];
		}
		if ( $args['fetchpriority'] ) {
			$attrs['fetchpriority'] = $args['fetchpriority'];
		}
		$html = wp_get_attachment_image( (int) $image['id'], 'full', false, $attrs );
		if ( $html ) {
			return $html;
		}
	}

	return statek_cholupice_picture( $image['path'] ?? '', $alt, $args );
}

function statek_cholupice_default_project_blocks(): array {
	return array(
		array(
			'title'      => 'Nové využití pro místo, které dlouho čekalo na změnu',
			'paragraphs' => array(
				'Naším cílem je vrátit život místu, které bylo dlouhé roky nevyužité a chátralo. Přestavbou nefunkčního statku postupně vznikne moderní a funkční prostor, který propojí kvalitní českou strojírenskou výrobu, nové pracovní příležitosti, bydlení i občanskou vybavenost.',
				'Bytový dům nabídne klidné bydlení citlivě navazující na okolní zástavbu. V jeho přízemí vzniknou komerční prostory pro obchod, kavárnu nebo služby každodenní potřeby, které mohou přirozeně oživit cholupickou náves a vytvořit zázemí pro místní obyvatele.',
				'Výroba bude probíhat uvnitř moderních budov bez výrazného hluku, zápachu nebo intenzivní dopravy.',
			),
			'image'      => array(
				'path' => 'images/statek_web_premium/hero-vjezd-preview-v4-lide-obchod.png',
				'alt'  => 'Vizualizace vstupu do areálu s obchody a novým vjezdem',
			),
		),
		array(
			'title'      => 'Revitalizace brownfieldu s respektem k historii',
			'paragraphs' => array(
				'Součástí projektu je rozsáhlá přestavba celého areálu, odstranění starých ekologických zátěží i záchrana historického špejcharu ze 16. století.',
				'Celková investice dosáhne přibližně jedné miliardy korun. Věříme, že projekt může být příkladem moderní a odpovědné přestavby brownfieldu, která propojí průmysl, služby, bydlení i respekt k historii a okolnímu prostředí.',
				'Společnost DSS, investor projektu, patří mezi české výrobce specializující se na ruční palné zbraně. Dlouhodobě působí v oblasti obranného průmyslu, kde staví na odbornosti, precizní strojírenské výrobě a respektu k tradičnímu československému zbrojařskému řemeslu.',
			),
			'before'     => array(
				'path' => 'images/statek_web_premium/pred_premium_documentary.png',
				'alt'  => 'Současný stav areálu před revitalizací',
			),
			'after'      => array(
				'path' => 'images/statek_web_premium/po_premium_dss_van_logo_left.png',
				'alt'  => 'Prémiová vizualizace navrženého stavu areálu s dodávkou DSS',
			),
		),
	);
}

function statek_cholupice_project_blocks(): array {
	$defaults = statek_cholupice_default_project_blocks();
	$saved    = statek_cholupice_home_json_meta( 'statek_home_project_blocks', array() );
	$blocks   = array();

	foreach ( $defaults as $index => $default ) {
		$item     = is_array( $saved[ $index ] ?? null ) ? $saved[ $index ] : array();
		$block    = $default;
		$block['title'] = statek_cholupice_text_or_fallback( $item['title'] ?? '', $default['title'] );
		$block['paragraphs'] = statek_cholupice_paragraphs_or_fallback( $item['paragraphs'] ?? array(), $default['paragraphs'], 5 );
		if ( isset( $default['image'] ) ) {
			$block['image'] = statek_cholupice_image_data( $item['image'] ?? array(), $default['image'] );
		}
		if ( isset( $default['before'] ) ) {
			$block['before'] = statek_cholupice_image_data( $item['before'] ?? array(), $default['before'] );
			$block['after']  = statek_cholupice_image_data( $item['after'] ?? array(), $default['after'] );
		}
		$blocks[] = $block;
	}

	return $blocks;
}

function statek_cholupice_default_area_items(): array {
	return array(
		array( 'title' => 'Bytový dům', 'image' => array( 'path' => 'images/statek_web_premium/1_16_photo_premium_residential.png', 'alt' => 'Prémiová vizualizace bytového domu' ), 'paragraphs' => array( 'Plánovaný bytový dům představuje novou tvář přestavby celého areálu. Navržen je s důrazem na kvalitní a klidné bydlení a počítá se samostatně řešeným parkováním v podzemí i na vlastním pozemku.', 'Dům bude mít dvě nadzemní podlaží a obytné podkroví, díky čemuž citlivě naváže na charakter okolní zástavby. Současně přirozeně oddělí veřejný prostor návsi od výrobní části areálu.' ) ),
		array( 'title' => 'Komerční prostory', 'image' => array( 'path' => 'images/statek_web_premium/1_18_photo_premium_commerce_people_atm.png', 'alt' => 'Prémiová vizualizace komerčních prostor s bankomatem' ), 'paragraphs' => array( 'V přízemí bytového domu vzniknou také komerční prostory. Mohou zde najít místo například menší obchod, kavárna nebo služby každodenní potřeby, jako jsou kadeřnictví, kosmetika či ordinace lékaře.', 'Cílem je vytvořit příjemné místo pro místní obyvatele i návštěvníky a podpořit přirozený život v centru Cholupic.' ) ),
		array( 'title' => 'Administrativní centrum – Špejchar', 'image' => array( 'path' => 'images/statek_web_premium/administrativni_centrum_premium.png', 'alt' => 'Vizualizace administrativního centra a špejcharu' ), 'paragraphs' => array( 'Špejchar projde pečlivou rekonstrukcí. Do původní historické budovy bude citlivě vsazena nová vnitřní konstrukce, která vytvoří moderní kancelářské a reprezentativní prostory například pro jednání s obchodními partnery a zahraničními delegacemi.', 'Součástí projektu je také statické zajištění a sanace historických konstrukcí s důrazem na jejich dlouhodobou ochranu.' ) ),
		array( 'title' => 'Výrobní prostory', 'image' => array( 'path' => 'images/statek_web_premium/hala_a_premium.png', 'alt' => 'Vizualizace výrobních prostor' ), 'paragraphs' => array( 'Ve výrobních prostorách bude probíhat zpracování kovových komponentů – od přípravy a prvotního opracování materiálu přes přesné CNC obrábění až po kontrolu kvality.', 'Součástí objektu budou moderní výrobní technologie, automatizované prvky a kontrolované pracovní prostředí odpovídající vysokým nárokům na kvalitu a bezpečnost. Veškeré činnosti budou probíhat uvnitř budovy s minimálním vlivem na okolí.' ) ),
		array( 'title' => 'Vývojové centrum', 'image' => array( 'path' => 'images/statek_web_premium/vyvojove_centrum_premium_engineers.png', 'alt' => 'Vizualizace vývojového centra se dvěma inženýry' ), 'paragraphs' => array( 'Vývojové centrum vznikne vestavbou nového objektu do stávajících historických konstrukcí areálu. Prostor bude sloužit pro vývoj, výzkum a technickou přípravu nových řešení.', 'Návrh zachová původní charakter objektu a zároveň mu přinese nové a dlouhodobě udržitelné využití.' ) ),
		array( 'title' => 'Parkovací dům', 'image' => array( 'path' => 'images/statek_web_premium/parkovaci_dum_premium.png', 'alt' => 'Vizualizace parkovacího domu' ), 'paragraphs' => array( 'Dvoupodlažní parkovací objekt určený pro zaměstnance nabídne 121 parkovacích míst. Navržen je tak, aby omezil dopravní zatížení okolí a umožnil plynulý vjezd i výjezd bez tvorby kolon.', 'Parkování bude oddělené od výrobních částí areálu.' ) ),
	);
}

function statek_cholupice_stable_order_sort( array $items ): array {
	usort(
		$items,
		static fn( $first, $second ) => ( $first['order'] <=> $second['order'] ) ?: ( $first['_position'] <=> $second['_position'] )
	);
	return array_map(
		static function ( $item ) {
			unset( $item['_position'] );
			return $item;
		},
		$items
	);
}

function statek_cholupice_area_items(): array {
	$defaults = statek_cholupice_default_area_items();
	$saved    = statek_cholupice_home_json_meta( 'statek_home_area_items', array() );
	$items    = array();

	foreach ( $defaults as $index => $default ) {
		$item = is_array( $saved[ $index ] ?? null ) ? $saved[ $index ] : array();
		$items[] = array(
			'order'      => isset( $item['order'] ) ? max( 1, min( 6, (int) $item['order'] ) ) : $index + 1,
			'_position'  => $index,
			'title'      => statek_cholupice_text_or_fallback( $item['title'] ?? '', $default['title'] ),
			'paragraphs' => statek_cholupice_paragraphs_or_fallback( $item['paragraphs'] ?? array(), $default['paragraphs'], 3 ),
			'image'      => statek_cholupice_image_data( $item['image'] ?? array(), $default['image'] ),
		);
	}

	return array_slice( statek_cholupice_stable_order_sort( $items ), 0, 6 );
}

function statek_cholupice_default_operation(): array {
	return array(
		'heading' => 'Jak bude areál fungovat',
		'motto'   => 'Promyšlený provoz uvnitř. Klid pro okolí.',
		'intro'   => array(
			'V areálu bude probíhat moderní strojírenská výroba založená na přesném CNC obrábění kovových komponentů, jejich kontrole a kompletaci. Součástí provozu budou také skladovací prostory, související logistické činnosti, administrativní zázemí a pracoviště zaměřená na vývoj a zdokonalování výrobních technologií.',
			'Výroba bude probíhat převážně v uzavřených halách s využitím moderních technologií, automatizace a kontrolovaných výrobních procesů. Provoz je navržen tak, aby splňoval všechny platné bezpečnostní, hygienické a environmentální požadavky a měl minimální dopad na okolní zástavbu i kvalitu života v městské části.',
		),
		'include' => array(
			array( 'title' => 'Přesné CNC obrábění', 'text' => 'Výroba kovových komponentů založená na přesném CNC obrábění.' ),
			array( 'title' => 'Kontrola a kompletace', 'text' => 'Kontrola vyrobených komponentů a jejich kompletace.' ),
			array( 'title' => 'Vývoj výrobních technologií', 'text' => 'Pracoviště zaměřená na vývoj a zdokonalování výrobních technologií.' ),
			array( 'title' => 'Skladování, logistika a administrativa', 'text' => 'Skladovací prostory, související logistické činnosti a administrativní zázemí provozu.' ),
		),
		'exclude' => array(
			array( 'title' => 'Žádná střelba ani střelnice', 'text' => 'V areálu nebude probíhat střelba ani praktické testování hotových výrobků. Nebude zde střelnice.' ),
			array( 'title' => 'Žádné skladování ani nakládání s výbušninami a municí', 'text' => 'Výbušniny ani munice se zde nebudou skladovat a nebude s nimi ani jinak nakládáno.' ),
			array( 'title' => 'Žádné chemické zušlechťování oceli', 'text' => 'V areálu nebude probíhat chemické zušlechťování oceli ani jiné nebezpečné práce s chemikáliemi.' ),
			array( 'title' => 'Žádný těžký průmysl', 'text' => 'Provoz nebude zdrojem emisí, výrazného hluku ani znečištění, které by zatěžovalo okolí.' ),
		),
	);
}

function statek_cholupice_operation_data(): array {
	$default = statek_cholupice_default_operation();
	$saved   = statek_cholupice_home_json_meta( 'statek_home_operation', array() );
	return array(
		'heading' => statek_cholupice_text_or_fallback( $saved['heading'] ?? '', $default['heading'] ),
		'motto'   => statek_cholupice_text_or_fallback( $saved['motto'] ?? '', $default['motto'] ),
		'intro'   => statek_cholupice_paragraphs_or_fallback( $saved['intro'] ?? array(), $default['intro'], 4 ),
		'include' => statek_cholupice_list_or_fallback( $saved['include'] ?? array(), $default['include'], 6 ),
		'exclude' => statek_cholupice_list_or_fallback( $saved['exclude'] ?? array(), $default['exclude'], 6 ),
	);
}

function statek_cholupice_list_or_fallback( $value, array $fallback, int $limit ): array {
	if ( ! is_array( $value ) ) {
		return $fallback;
	}
	$items = array();
	foreach ( array_slice( $value, 0, $limit ) as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$title = trim( (string) ( $item['title'] ?? '' ) );
		$text  = trim( (string) ( $item['text'] ?? '' ) );
		if ( '' !== $title && '' !== $text ) {
			$items[] = compact( 'title', 'text' );
		}
	}
	return $items ?: $fallback;
}

function statek_cholupice_default_topics(): array {
	return array(
		array( 'id' => 'bezpecnost', 'class' => 'topic-safety', 'title' => 'Bezpečnost', 'motto' => 'Bezpečnost postavená na lidech, technologiích a jasných pravidlech', 'image' => array( 'path' => 'images/statek_web_premium/bezpecnost-vstup-kontrola.png', 'alt' => 'Kontrolovaný vstup zaměstnance do výrobního prostoru' ), 'intro' => 'Dělat věci správně je pro nás naprosto zásadní. Celý areál je proto navržen jako víceúrovňový systém kombinující fyzická, technologická i organizační opatření, na jejichž přípravě spolupracujeme s evropskými technologickými lídry a odbornými institucemi.', 'details' => array( 'Přesto naše společnost sází především na stabilní kmen zaměstnanců, převážně z České republiky a zemí Evropské unie. Jádro týmu tvoří dlouhodobě prověření zaměstnanci s potřebnou odborností a bezpečnostní spolehlivostí.', 'Provoz podléhá přísné legislativě České republiky, víceúrovňovým a periodickým kontrolám ze strany státních orgánů.', 'Výroba je komplexně monitorována a díky implementaci nejmodernějšího trasování a evidence není možné jakékoli zneužití nebo nedovolené vynášení.', 'Bezpečnost provozu, ochrana areálu i výrobních procesů budou odpovídat standardům ISO 9001 a AQAP 2110 používaným v rámci NATO. Tyto standardy vyžadují právě například přesnou evidenci výroby, dohledatelnost jednotlivých součástek a přísnou kontrolu kvality v celém výrobním procesu.', 'V areálu nebude probíhat žádná střelba ani praktické testování našich výrobků. Tyto činnosti probíhají výhradně mimo areál na specializovaných pracovištích.' ) ),
		array( 'id' => 'doprava', 'class' => '', 'title' => 'Doprava', 'motto' => 'Méně dopravy než v minulosti. Více klidu pro okolí.', 'image' => array( 'path' => 'images/statek_web_premium/doprava-areal-delivery-truck-focus.png', 'alt' => 'Vizualizace dopravy v areálu s výraznou dodávkou před výrobní halou' ), 'intro' => 'Doprava spojená s provozem areálu bude oproti původnímu využití areálu velmi omezená. Předpokládá se zhruba jedna až dvě menší dodávky (do 6 tun) denně, tedy objem zásobování srovnatelný například s běžným obchodem.', 'details' => array( 'Ve srovnání s minulostí tak půjde o výrazné zklidnění. V době fungování zemědělského družstva se v areálu běžně pohybovala těžká technika, traktory, kombajny, nákladní vozy, zemědělské stroje i zásobování spojené se sklady a chovem zvířat. S tím souviselo také znečišťování komunikací bahnem a vyšší provoz v okolí. To v tomto případě nehrozí.', 'Do areálu samozřejmě nebudou jezdit jen dodávky, ale také zaměstnanci. Pro ně jsme připravili krytý parkovací dům se 121 parkovacími místy. Vjezd do areálu je navržen tak, aby nevznikaly kolony, nedocházelo k omezení plynulosti dopravy a dopad na okolí byl co nejmenší.' ) ),
		array( 'id' => 'zivotni-prostredi', 'class' => '', 'title' => 'Životní prostředí', 'motto' => 'Třikrát více zeleně a provoz šetrný k okolí.', 'image' => array( 'path' => 'images/karousel3_premium_dss_v4_4k_preview.jpg', 'alt' => 'Prémiová vizualizace zeleného veřejného prostoru a výsadby v areálu' ), 'intro' => 'Výroba sama o sobě není hlučná a celý provoz je umístěn uvnitř hal. Vznikat budou především běžné odpady, jako jsou kovové odřezky, papír nebo dřevo. Veškeré odpady budou tříděny a předávány k recyklaci v souladu s platnou legislativou. Součástí přípravy projektu je také zpracování dokumentace k nakládání s odpady a posouzení vlivu provozu na okolí.', 'details' => array( 'Při výrobě, kterou plánujeme, se nepoužívají chemikálie, vyjma těch běžných jako jsou olej, ředidlo či úklidové prostředky. Náročnější procesy, jako je chemické nebo tepelné zpracování kovů, budou probíhat mimo areál u specializovaných partnerů.', 'Součástí projektu na přestavbu areálu je odstranění staré ekologické zátěže, která v místě zůstala. Budou tak odvezeny nevyhovující materiály, které zde zůstaly z minulosti.', 'Velký důraz klademe i na zeleň a celkovou podobu areálu.', 'Nemocné a nebezpečné stromy budou odstraněny na základě povolení příslušných orgánů státní správy a nahrazeny novou výsadbou. Celkové množství zeleně by mělo být přibližně trojnásobné oproti současnému stavu.', 'V plánu jsou také zelené střechy na halách a nová vodní plocha, která pomůže přirozeně ochlazovat okolní prostředí. Ta může zároveň posloužit jako zásobárna vody pro případ požáru.', 'Projekt počítá s dlouhodobě udržitelným provozem a splněním všech legislativních požadavků v oblasti ochrany životního prostředí, včetně standardů systému environmentálního řízení ISO 14000.', 'Významnou součástí projektu je také komplexní sanace celého areálu a odstranění historických ekologických zátěží, které zde zůstaly z minulého využívání území. V rámci přípravy projektu byly identifikovány a postupně odstraněny nevyhovující materiály a staré ekologické zátěže, včetně pozůstatků technické infrastruktury z minulosti. Z areálu již byly odvezeny stovky tun odpadu a kontaminovaných materiálů, které představovaly dlouhodobý problém pro lokalitu.' ) ),
	);
}

function statek_cholupice_topic_sections(): array {
	$defaults = statek_cholupice_default_topics();
	$saved    = statek_cholupice_home_json_meta( 'statek_home_topics', array() );
	$topics   = array();
	foreach ( $defaults as $index => $default ) {
		$item = is_array( $saved[ $index ] ?? null ) ? $saved[ $index ] : array();
		$topics[] = array(
			'id'      => $default['id'],
			'class'   => $default['class'],
			'title'   => statek_cholupice_text_or_fallback( $item['title'] ?? '', $default['title'] ),
			'motto'   => statek_cholupice_text_or_fallback( $item['motto'] ?? '', $default['motto'] ),
			'intro'   => statek_cholupice_text_or_fallback( $item['intro'] ?? '', $default['intro'] ),
			'details' => statek_cholupice_paragraphs_or_fallback( $item['details'] ?? array(), $default['details'], 8 ),
			'image'   => statek_cholupice_image_data( $item['image'] ?? array(), $default['image'] ),
		);
	}
	return $topics;
}

function statek_cholupice_default_benefits(): array {
	return array(
		'heading' => 'Co projekt přinese Cholupicím',
		'motto'   => 'Nová pracovní místa, zachráněné dědictví a smysluplné využití zanedbaného areálu.',
		'intro'   => 'Proměna bývalého hospodářského areálu přinese více než jen nové budovy. Vytvoří pracovní příležitosti, podpoří místní podnikatele a služby, zachrání cenné historické objekty a přispěje ke kultivaci celého okolí. Chceme být dobrým sousedem, který svůj úspěch sdílí s místní komunitou a dlouhodobě podporuje život v Cholupicích.',
		'cards'   => array(
			array( 'order' => 1, 'icon' => 'work', 'title' => 'Práce blízko domova', 'text' => 'Nový areál vytvoří pracovní příležitosti pro různé profese a úrovně kvalifikace — od výroby, údržby a logistiky přes administrativu až po vývoj a vedoucí pozice. Nové zaměstnání tak mohou najít také lidé z Cholupic a blízkého okolí. Půjde o práci v technologicky vyspělém a perspektivním oboru, který nabízí dlouhodobou stabilitu i prostor pro profesní růst.' ),
			array( 'order' => 2, 'icon' => 'delivery', 'title' => 'Zakázky a zákazníci pro místní', 'text' => 'Každodenní provoz areálu vytvoří poptávku po různých službách, například v oblasti autodopravy, údržby nebo zásobování. Do těchto činností se mohou zapojit podnikatelé, živnostníci a firmy z okolí. V areálu bude zároveň pracovat řada lidí, kteří si rádi zajdou na oběd nebo po práci posedět do místní hospody.' ),
			array( 'order' => 3, 'icon' => 'shop', 'title' => 'Nové služby v Cholupicích', 'text' => 'Část obnoveného areálu navazující na náves může nabídnout prostory pro menší provozovny a služby využitelné místními obyvateli. Může jít například o drobný obchod, občerstvení, ordinaci lékaře nebo zázemí pro místního podnikatele. Statek se tak může znovu přirozeně zapojit do každodenního života Cholupic.' ),
			array( 'order' => 4, 'icon' => 'shield', 'title' => 'Nový život a větší bezpečí', 'text' => 'Dlouhodobě chátrající a nevyužívaný areál se stal útočištěm lidí bez domova a představoval bezpečnostní riziko pro své okolí. Kompletní rekonstrukce, vyčištění a každodenní péče vrátí místu důstojnou podobu a smysluplné využití. Zmizí staré ekologické zátěže i nevyhovující materiály a zanedbaný prostor se promění v bezpečný a upravený areál.' ),
			array( 'order' => 5, 'icon' => 'heritage', 'title' => 'Historie, která zůstane', 'text' => 'Historický špejchar ze 16. století a votivní deska z roku 1883 budou zachovány a citlivě obnoveny. Špejchar bude adaptován pro nové využití formou vestavby „nového do starého“, aby mohla zůstat zachována jeho původní konstrukce. Bez rekonstrukce by objekt dál chátral a jeho zánik by byl jen otázkou času.' ),
			array( 'order' => 6, 'icon' => 'heart', 'title' => 'Dobrý soused', 'text' => 'Dobrý soused se nepozná podle slov, ale podle toho, co dělá pro své okolí. Když se bude areálu dařit, chceme část jeho úspěchu vracet místní komunitě — podporou spolků, kulturních a sportovních akcí, aktivit pro děti a seniory i dalších dobrých nápadů, které zlepšují život v Cholupicích.' ),
		),
	);
}

function statek_cholupice_benefits_data(): array {
	$default = statek_cholupice_default_benefits();
	$saved   = statek_cholupice_home_json_meta( 'statek_home_benefits', array() );
	$cards   = array();

	foreach ( array_slice( $default['cards'], 0, 6 ) as $index => $fallback_card ) {
		$item    = is_array( $saved['cards'][ $index ] ?? null ) ? $saved['cards'][ $index ] : array();
		$title   = statek_cholupice_text_or_fallback( $item['title'] ?? '', $fallback_card['title'] );
		$text    = statek_cholupice_text_or_fallback( $item['text'] ?? '', $fallback_card['text'] );
		$order   = isset( $item['order'] ) ? max( 1, min( 6, (int) $item['order'] ) ) : (int) $fallback_card['order'];
		$icon    = statek_cholupice_valid_icon( $item['icon'] ?? $fallback_card['icon'] );
		$cards[] = compact( 'order', 'icon', 'title', 'text' ) + array( '_position' => $index );
	}
	$cards = statek_cholupice_stable_order_sort( $cards );

	return array(
		'heading' => statek_cholupice_text_or_fallback( $saved['heading'] ?? '', $default['heading'] ),
		'motto'   => statek_cholupice_text_or_fallback( $saved['motto'] ?? '', $default['motto'] ),
		'intro'   => statek_cholupice_text_or_fallback( $saved['intro'] ?? '', $default['intro'] ),
		'cards'   => $cards ?: $default['cards'],
	);
}

function statek_cholupice_valid_icon( string $icon ): string {
	return in_array( $icon, array( 'work', 'delivery', 'shop', 'shield', 'heritage', 'heart' ), true ) ? $icon : 'work';
}

function statek_cholupice_icon_svg( string $icon ): string {
	$icons = array(
		'work'     => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="7" width="18" height="13" rx="2"></rect><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><path d="M3 12h18"></path><path d="M10 12v2h4v-2"></path></svg>',
		'delivery' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h11v9H3z"></path><path d="M14 9h4l3 3v3h-7z"></path><circle cx="7" cy="17" r="2"></circle><circle cx="18" cy="17" r="2"></circle></svg>',
		'shop'     => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 10v10h16V10"></path><path d="m3 10 2-6h14l2 6"></path><path d="M3 10a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"></path><path d="M8 20v-5h8v5"></path></svg>',
		'shield'   => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6z"></path><path d="m8.5 12 2.2 2.2 4.8-5"></path></svg>',
		'heritage' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-6 9 6"></path><path d="M5 10h14"></path><path d="M6 10v8M10 10v8M14 10v8M18 10v8"></path><path d="M3 18h18v2H3z"></path></svg>',
		'heart'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 8.6c0 5.3-8.8 10.4-8.8 10.4S3.2 13.9 3.2 8.6A4.6 4.6 0 0 1 12 6a4.6 4.6 0 0 1 8.8 2.6Z"></path></svg>',
	);
	return $icons[ statek_cholupice_valid_icon( $icon ) ];
}

function statek_cholupice_default_footer_data(): array {
	return array(
		'investor_name'    => 'DSS a.s.',
		'investor_address' => 'Kloboučnická 1735/26, Nusle, 140 00 Praha 4',
		'investor_id'      => 'IČ: 26161541, DIČ: CZ26161541',
		'investor_registry' => 'Zapsaná v obchodním rejstříku vedeném Městským soudem v Praze, oddíl B, vložka 6434.',
		'info_heading'     => 'Aktuálnost informací',
		'info_text'        => 'Informace uvedené na tomto webu odpovídají stavu projektu v době jejich zveřejnění. Průběžně je aktualizujeme, mezi změnou projektu a jejím zveřejněním na webu však může vzniknout časová prodleva.',
		'visuals_heading'  => 'Vizualizace projektu',
		'visuals_text'     => 'Vizualizace mají ilustrativní charakter a zachycují předpokládanou podobu projektu v době svého vzniku. V průběhu další přípravy, povolování a realizace může dojít k dílčím změnám architektonického, technického nebo materiálového řešení.',
	);
}

function statek_cholupice_footer_data(): array {
	$default = statek_cholupice_default_footer_data();
	$saved   = statek_cholupice_home_json_meta( 'statek_home_footer', array() );
	foreach ( $default as $key => $value ) {
		$default[ $key ] = statek_cholupice_text_or_fallback( $saved[ $key ] ?? '', $value );
	}
	return $default;
}
