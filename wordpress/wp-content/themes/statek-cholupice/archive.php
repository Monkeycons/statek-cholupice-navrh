<?php
/**
 * Archiv
 *
 * @package StatekCholupice
 */

get_header();
?>
<main id="main-content">
	<section>
		<div class="wrap">
			<h1><?php the_archive_title(); ?></h1>
			<?php if ( have_posts() ) : ?>
				<div class="benefits">
				<?php while ( have_posts() ) : the_post(); ?>
					<article class="benefit">
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p><?php echo esc_html( get_the_excerpt() ); ?></p>
					</article>
				<?php endwhile; ?>
				</div>
			<?php else : ?>
				<p>Zatím zde nejsou žádné novinky.</p>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
