<?php
/**
 * Sekce novinek.
 *
 * @package StatekCholupice
 */

$news_items = statek_cholupice_news_items();
?>
<section class="news-section" id="novinky" <?php echo empty( $news_items ) ? 'hidden' : ''; ?>>
	<div class="wrap">
		<div class="news-heading">
			<p class="news-kicker">Novinky</p>
			<h2>Co je nového na Statku Cholupice</h2>
			<p class="news-motto">Aktuální informace o projektu na jednom místě.</p>
			<p class="news-intro">Sledujte průběh přípravy projektu, důležité milníky a nové odpovědi na otázky, které se kolem proměny statku objevují.</p>
		</div>
		<div class="news-carousel" aria-label="Novinky">
			<div class="news-carousel-toolbar">
				<button class="news-carousel-button" type="button" data-news-prev aria-label="Předchozí novinky">‹</button>
				<button class="news-carousel-button" type="button" data-news-next aria-label="Další novinky">›</button>
				<a class="read-more news-all-link" data-news-all href="<?php echo esc_url( get_post_type_archive_link( 'post' ) ?: home_url( '/novinky/' ) ); ?>">Všechny novinky</a>
			</div>
			<div class="news-carousel-viewport" data-news-viewport tabindex="0">
				<div class="news-track" data-news-track></div>
			</div>
			<p class="news-carousel-status visually-hidden" data-news-status aria-live="polite"></p>
		</div>
	</div>
</section>
