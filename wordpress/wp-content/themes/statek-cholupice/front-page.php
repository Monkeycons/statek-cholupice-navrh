<?php
/**
 * Úvodní stránka.
 *
 * @package StatekCholupice
 */

$project_blocks = statek_cholupice_project_blocks();
$area_items     = statek_cholupice_area_items();
$operation      = statek_cholupice_operation_data();
$topics         = statek_cholupice_topic_sections();
$benefits       = statek_cholupice_benefits_data();

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
        <?php foreach ( $project_blocks as $index => $block ) : ?>
          <div class="story-block<?php echo 1 === $index ? ' reverse' : ''; ?>">
            <?php if ( 1 === $index ) : ?>
              <div class="before-after" data-before-after>
                <?php echo statek_cholupice_editable_image( $block['before'], array( 'sizes' => '(max-width: 900px) 100vw, 50vw' ) ); ?>
                <?php echo statek_cholupice_editable_image( $block['after'], array( 'class' => 'after', 'sizes' => '(max-width: 900px) 100vw, 50vw' ) ); ?>
                <span class="ba-label before">Současný stav</span>
                <span class="ba-label after-label">Navrhovaná podoba</span>
                <span class="ba-handle" aria-hidden="true"></span>
                <input type="range" min="0" max="100" value="50" aria-label="Porovnání současného stavu a navrhované podoby">
              </div>
            <?php else : ?>
              <div class="story-copy">
                <h3><?php echo esc_html( $block['title'] ); ?></h3>
                <?php foreach ( $block['paragraphs'] as $paragraph ) : ?>
                  <p><?php echo esc_html( $paragraph ); ?></p>
                <?php endforeach; ?>
              </div>
              <figure class="story-media">
                <?php echo statek_cholupice_editable_image( $block['image'], array( 'sizes' => '(max-width: 900px) 100vw, 50vw' ) ); ?>
              </figure>
            <?php endif; ?>

            <?php if ( 1 === $index ) : ?>
              <div class="story-copy">
                <h3><?php echo esc_html( $block['title'] ); ?></h3>
                <?php foreach ( $block['paragraphs'] as $paragraph ) : ?>
                  <p><?php echo esc_html( $paragraph ); ?></p>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="site-area" id="popis-arealu">
      <div class="wrap">
        <div class="site-area-heading">
          <h2><?php echo esc_html( statek_cholupice_home_meta( 'statek_home_area_heading', 'Šest částí, jeden živý areál' ) ); ?></h2>
          <p class="lead"><?php echo esc_html( statek_cholupice_home_meta( 'statek_home_area_motto', 'Promyšlené spojení různých funkcí vrací Statku Cholupice život.' ) ); ?></p>
        </div>
        <div class="site-area-layout">
          <div class="site-area-tabs" role="tablist" aria-label="Části areálu" aria-orientation="horizontal">
            <?php foreach ( $area_items as $index => $item ) : ?>
              <?php $tab_number = $index + 1; ?>
              <button class="site-area-tab" id="site-area-tab-<?php echo esc_attr( (string) $tab_number ); ?>" role="tab" aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>" aria-controls="site-area-panel-<?php echo esc_attr( (string) $tab_number ); ?>" tabindex="<?php echo 0 === $index ? '0' : '-1'; ?>"><span><?php echo esc_html( str_pad( (string) $tab_number, 2, '0', STR_PAD_LEFT ) ); ?></span><?php echo esc_html( $item['title'] ); ?></button>
            <?php endforeach; ?>
          </div>
          <div class="site-area-content">
            <?php foreach ( $area_items as $index => $item ) : ?>
              <?php $panel_number = $index + 1; ?>
              <article class="site-area-panel" id="site-area-panel-<?php echo esc_attr( (string) $panel_number ); ?>" role="tabpanel" aria-labelledby="site-area-tab-<?php echo esc_attr( (string) $panel_number ); ?>" aria-hidden="<?php echo 0 === $index ? 'false' : 'true'; ?>">
                <figure class="site-area-media"><?php echo statek_cholupice_editable_image( $item['image'], array( 'sizes' => '(max-width: 980px) 100vw, 58vw' ) ); ?></figure>
                <div class="site-area-copy">
                  <p class="site-area-label">Popis areálu · <?php echo esc_html( str_pad( (string) $panel_number, 2, '0', STR_PAD_LEFT ) ); ?></p>
                  <h3><?php echo esc_html( $item['title'] ); ?></h3>
                  <?php foreach ( $item['paragraphs'] as $paragraph ) : ?>
                    <p><?php echo esc_html( $paragraph ); ?></p>
                  <?php endforeach; ?>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>

    <section class="site-operation-section" id="provoz-arealu">
      <div class="wrap">
        <div class="site-operation-intro">
          <h2><?php echo esc_html( $operation['heading'] ); ?></h2>
          <p class="site-operation-motto"><?php echo esc_html( $operation['motto'] ); ?></p>
          <?php foreach ( $operation['intro'] as $paragraph ) : ?>
            <p><?php echo esc_html( $paragraph ); ?></p>
          <?php endforeach; ?>
        </div>
        <div class="site-operation-columns">
          <section class="site-operation-box good">
            <h4>Co bude součástí provozu</h4>
            <ul class="site-operation-list">
              <?php foreach ( $operation['include'] as $item ) : ?>
                <li><strong><?php echo esc_html( $item['title'] ); ?></strong><span><?php echo esc_html( $item['text'] ); ?></span></li>
              <?php endforeach; ?>
            </ul>
          </section>
          <section class="site-operation-box neutral">
            <h4>Co nebude součástí provozu</h4>
            <ul class="site-operation-list">
              <?php foreach ( $operation['exclude'] as $item ) : ?>
                <li><strong><?php echo esc_html( $item['title'] ); ?></strong><span><?php echo esc_html( $item['text'] ); ?></span></li>
              <?php endforeach; ?>
            </ul>
          </section>
        </div>
      </div>
    </section>

    <section class="topics-section">
      <div class="wrap">
        <div class="topics">
          <?php foreach ( $topics as $topic ) : ?>
            <article class="topic topic-stacked <?php echo esc_attr( $topic['class'] ); ?>" id="<?php echo esc_attr( $topic['id'] ); ?>">
              <figure class="topic-illustration"><?php echo statek_cholupice_editable_image( $topic['image'], array( 'sizes' => '(max-width: 980px) 100vw, 46vw' ) ); ?></figure>
              <div class="topic-body">
                <h2><?php echo esc_html( $topic['title'] ); ?></h2>
                <p class="topic-motto"><?php echo esc_html( $topic['motto'] ); ?></p>
                <p><?php echo esc_html( $topic['intro'] ); ?></p>
                <details class="topic-accordion">
                  <summary>Číst více</summary>
                  <div class="safety-copy">
                    <?php foreach ( $topic['details'] as $paragraph ) : ?>
                      <p><?php echo esc_html( $paragraph ); ?></p>
                    <?php endforeach; ?>
                  </div>
                </details>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="band" id="prinosy">
      <div class="wrap">
        <div class="benefits-heading">
          <h2><?php echo esc_html( $benefits['heading'] ); ?></h2>
          <p class="benefits-motto"><?php echo esc_html( $benefits['motto'] ); ?></p>
        </div>
        <p class="benefits-intro"><?php echo esc_html( $benefits['intro'] ); ?></p>
        <div class="benefits">
          <?php foreach ( $benefits['cards'] as $card ) : ?>
            <article class="benefit<?php echo 'heart' === $card['icon'] ? ' benefit-neighbor' : ''; ?>">
              <div class="icon"><?php echo statek_cholupice_icon_svg( $card['icon'] ); ?></div>
              <h3><?php echo esc_html( $card['title'] ); ?></h3>
              <p><?php echo esc_html( $card['text'] ); ?></p>
            </article>
          <?php endforeach; ?>
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
