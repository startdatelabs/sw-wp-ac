<?php get_header(); ?>

<?php get_template_part( 'part-hero', get_post_type() ); ?>

<main class="main main-elevated">
	<div class="container">
		<div class="row">
			<div class="col-xl-9 col-lg-8 col-xs-12">
				<div class="content-wrap">
					<?php while ( have_posts() ) : the_post();  ?>
						<?php get_template_part( 'listing-items/article', get_post_type() ); ?>
					<?php endwhile; ?>
				</div>

				<?php specialty_posts_pagination(); ?>
			</div>

			<div class="col-xl-3 col-lg-4 col-xs-12">
				<?php get_sidebar(); ?>
			</div>
		</div>
	</div>
</main>

<?php get_footer();
