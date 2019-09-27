<?php get_header(); ?>

<?php get_template_part( 'part-hero', get_post_type() ); ?>

<main class="main main-elevated">
	<div class="container">
		<div class="row">
			<div class="col-xl-10 offset-xl-1 col-lg-10 offset-lg-1 col-xs-12">
				<div class="content-wrap">
					<article class="entry">
						<div class="entry-content">
							<p><?php esc_html_e( 'The page you were looking for can not be found! Perhaps try searching?', 'specialty' ); ?></p>
							<?php get_search_form(); ?>
						</div>
					</article>
				</div>
			</div>
		</div>
	</div>
</main>

<?php get_footer();
