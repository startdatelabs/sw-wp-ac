<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-item' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="entry-item-thumb">
			<a href="<?php the_permalink(); ?>">
				<?php the_post_thumbnail( 'specialty_entry_item' ); ?>
			</a>
		</figure>
	<?php endif; ?>

	<div class="entry-item-content-wrap">
		<?php if ( 'post' === get_post_type() ) : ?>
			<div class="entry-meta">
				<time class="entry-time" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo get_the_date(); ?></time>
				<span class="entry-categories">
					<?php the_category( ', ' ); ?>
				</span>
			</div>
		<?php endif; ?>

		<h2 class="entry-item-title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>

		<div class="entry-content">
			<?php the_excerpt(); ?>
		</div>
	</div>
</article>
