	<footer class="footer">
		<div class="container">
			<div class="row">
				<div class="col-xs-12">
					<div class="footer-widgets">
						<?php if (
							is_active_sidebar( 'footer-1' )
							|| is_active_sidebar( 'footer-2' )
							|| is_active_sidebar( 'footer-3' )
							|| is_active_sidebar( 'footer-4' )
						) : ?>
							<div class="row">
								<div class="col-lg-3 col-xs-12">
									<?php dynamic_sidebar( 'footer-1' ); ?>
								</div>

								<div class="col-lg-3 col-xs-12">
									<?php dynamic_sidebar( 'footer-2' ); ?>
								</div>

								<div class="col-lg-3 col-xs-12">
									<?php dynamic_sidebar( 'footer-3' ); ?>
								</div>

								<div class="col-lg-3 col-xs-12">
									<?php dynamic_sidebar( 'footer-4' ); ?>
								</div>
							</div>
						<?php endif; ?>
					</div>

					<?php if ( get_theme_mod( 'footer_text_left', specialty_get_default_footer_text( 'left' ) ) || get_theme_mod( 'footer_text_right', specialty_get_default_footer_text( 'right' ) ) ) : ?>
						<div class="footer-copy">
							<div class="row">
								<div class="col-sm-6 col-xs-12">
									<p><?php echo specialty_sanitize_footer_text( get_theme_mod( 'footer_text_left', specialty_get_default_footer_text( 'left' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
								</div>

								<div class="col-sm-6 col-xs-12 text-right">
									<p><?php echo specialty_sanitize_footer_text( get_theme_mod( 'footer_text_right', specialty_get_default_footer_text( 'right' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
								</div>
							</div>
						</div>
					<?php endif; ?>

				</div>
			</div>
		</div>
	</footer>
</div> <!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
