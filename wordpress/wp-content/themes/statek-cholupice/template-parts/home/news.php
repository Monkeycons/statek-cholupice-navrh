<?php
/**
 * Sekce novinek.
 *
 * @package StatekCholupice
 */

$news_query = statek_cholupice_news_query( 6 );
?>
<section class="news-section" id="novinky" <?php echo $news_query->have_posts() ? '' : 'hidden'; ?>>
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
				<div class="news-track" data-news-track>
					<?php
					while ( $news_query->have_posts() ) :
						$news_query->the_post();
						?>
						<article class="news-card">
							<a href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( sprintf( 'Číst více: %s', get_the_title() ) ); ?>">
								<?php
								if ( has_post_thumbnail() ) {
									the_post_thumbnail(
										'large',
										array(
											'class'    => 'news-card-image',
											'loading'  => 'lazy',
											'decoding' => 'async',
											'sizes'    => '(max-width: 860px) 82vw, (max-width: 1100px) calc((100vw - 70px) / 2), 379px',
										)
									);
								} else {
									echo statek_cholupice_picture(
										'images/statek_web_premium/hero-vjezd-preview-v4-lide-obchod.png',
										'Vizualizace vstupu do areálu',
										array(
											'class' => 'news-card-image',
											'sizes' => '(max-width: 860px) 82vw, (max-width: 1100px) calc((100vw - 70px) / 2), 379px',
										)
									);
								}
								?>
							</a>
							<div class="news-card-body">
								<time class="news-card-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
								<h3><?php the_title(); ?></h3>
								<p class="news-card-excerpt"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
								<a class="news-card-link" href="<?php the_permalink(); ?>">Číst více</a>
							</div>
						</article>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				</div>
			</div>
			<p class="news-carousel-status visually-hidden" data-news-status aria-live="polite"></p>
		</div>
	</div>
</section>
