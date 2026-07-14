<?php
/**
 * Archiv novinek.
 *
 * @package StatekCholupice
 */

get_header();
?>
<main id="main-content">
	<section class="news-section news-archive">
		<div class="wrap">
			<div class="news-heading">
				<p class="news-kicker">Novinky</p>
				<h1>Novinky</h1>
				<p class="news-motto">Aktuální informace o projektu na jednom místě.</p>
			</div>

			<?php if ( have_posts() ) : ?>
				<div class="news-track news-archive-grid">
					<?php while ( have_posts() ) : the_post(); ?>
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
										)
									);
								} else {
									echo statek_cholupice_picture(
										'images/statek_web_premium/hero-vjezd-preview-v4-lide-obchod.png',
										'Vizualizace vstupu do areálu',
										array(
											'class' => 'news-card-image',
											'sizes' => '(max-width: 860px) 88vw, 360px',
										)
									);
								}
								?>
							</a>
							<div class="news-card-body">
								<time class="news-card-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
								<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<p class="news-card-excerpt"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
								<a class="news-card-link" href="<?php the_permalink(); ?>">Číst více</a>
							</div>
						</article>
					<?php endwhile; ?>
				</div>
				<?php
				the_posts_pagination(
					array(
						'mid_size'           => 1,
						'prev_text'          => 'Předchozí',
						'next_text'          => 'Další',
						'screen_reader_text' => 'Stránkování novinek',
					)
				);
				?>
			<?php else : ?>
				<p>Zatím zde nejsou žádné novinky.</p>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
