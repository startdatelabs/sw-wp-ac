<?php if ( post_password_required() ) {
	return;
} ?>

<?php if ( have_comments() || comments_open() ) : ?>
	<div class="content-wrap">
		<div id="comments">
<?php endif; ?>

<?php if ( have_comments() ) : ?>
	<div class="post-comments group">
		<h3><?php comments_number(); ?></h3>

		<?php
			$args = array(
				'echo'      => false,
				'prev_text' => sprintf( '<i class="fa fa-long-arrow-left"></i><span class="screen-reader-text">%s</span>',
					esc_html__( 'Previous comments', 'specialty' )
				),
				'next_text' => sprintf( '<span class="screen-reader-text">%s</span><i class="fa fa-long-arrow-right"></i>',
					esc_html__( 'Next comments', 'specialty' )
				),
			);

			$comments_pagination = paginate_comments_links( $args ); ?>
		<?php if ( ! empty( $comments_pagination ) ) : ?>
			<div class="comments-pagination"><?php echo paginate_comments_links( $args ); ?></div>
		<?php endif; ?>

		<ol id="comment-list">
			<?php
				wp_list_comments( array(
					'style'       => 'ol',
					'type'        => 'comment',
					'avatar_size' => 110,
				) );
				wp_list_comments( array(
					'style'      => 'ol',
					'short_ping' => true,
					'type'       => 'pings',
				) );
			?>
		</ol>
		<?php if ( ! empty( $comments_pagination ) ) : ?>
			<div class="comments-pagination"><?php echo paginate_comments_links( $args ); ?></div>
		<?php endif; ?>
	</div><!-- .post-comments -->
<?php endif; ?>

<?php if ( comments_open() ) : ?>
	<section id="respond">
		<div id="form-wrapper" class="group">
			<?php comment_form(); ?>
		</div><!-- #form-wrapper -->
	</section>
<?php endif; ?>

<?php if ( have_comments() || comments_open() ) : ?>
		</div><!-- .content-wrap -->
	</div><!-- #comments -->
<?php endif;
