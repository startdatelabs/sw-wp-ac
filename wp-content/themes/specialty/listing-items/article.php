<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
	<?php if ( has_post_thumbnail() && ! is_search() ) : ?>
		<figure class="entry-thumb">
			<a href="<?php the_permalink(); ?>">
				<?php the_post_thumbnail(); ?>
			</a>
		</figure>
	<?php endif; ?>

	<?php if ( 'post' === get_post_type() ) : ?>
		<div class="entry-meta">
			<time class="entry-time" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo get_the_date(); ?></time>
			<span class="entry-categories">
				<?php the_category( ', ' ); ?>
			</span>
		</div>
	<?php endif; ?>

	<h1 class="entry-title">
		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	</h1>

	<div class="entry-content">
		<?php the_excerpt(); ?>
	</div>

	<a href="<?php the_permalink(); ?>" class="btn btn-read-more"><?php esc_html_e( 'Read More', 'specialty' ); ?></a>
</article>
