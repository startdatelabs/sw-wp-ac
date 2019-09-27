<div class="entry-sharing">
	<?php esc_html_e( 'Share it:', 'specialty' ); ?>

	<?php
		$thumb_id = get_post_thumbnail_id();

		$target_safe = '';
		if ( 1 === get_theme_mod( 'social_target', 1 ) ) {
			$target_safe = 'target="_blank"';
		}

		$facebook = add_query_arg( array(
			'u' => get_permalink(),
		), 'https://www.facebook.com/sharer.php' );

		$twitter = add_query_arg( array(
			'url' => get_permalink(),
		), 'https://twitter.com/share' );

		$linkedin = add_query_arg( array(
			'mini'    => 'true',
			'url'     => get_permalink(),
			'title'   => get_the_title(),
			'summary' => get_the_excerpt(),
			'source'  => get_bloginfo( 'name' ),
		), 'https://www.linkedin.com/shareArticle' );

		$email = add_query_arg( array(
			'url'     => get_permalink(),
			'subject' => get_the_title(),
			'body'    => get_permalink(),
		), 'mailto:' );
	?>
	<a class="entry-share entry-share-facebook" href="<?php echo esc_url( $facebook ); ?>" <?php echo $target_safe; ?>><?php esc_html_e( 'Facebook', 'specialty' ); ?></a>
	<a class="entry-share entry-share-twitter" href="<?php echo esc_url( $twitter ); ?>" <?php echo $target_safe; ?>><?php esc_html_e( 'Twitter', 'specialty' ); ?></a>
	<a class="entry-share entry-share-linkedin" href="<?php echo esc_url( $linkedin ); ?>" <?php echo $target_safe; ?>><?php esc_html_e( 'LinkedIn', 'specialty' ); ?></a>
	<a class="entry-share entry-share-email" href="<?php echo esc_url( $email ); ?>" <?php echo $target_safe; ?>><?php esc_html_e( 'Email', 'specialty' ); ?></a>
</div>
