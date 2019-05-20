<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<div id="page">
	<header class="header">
		<div class="container">
			<div class="row">
				<div class="col-xs-12">
					<div class="mast-head">
						<div class="site-identity">
							<h1 class="site-logo">
								<?php if ( function_exists( 'the_custom_logo' ) ) {
									the_custom_logo();
								} ?>

								<?php if ( get_theme_mod( 'logo_site_title', 1 ) ) : ?>
									<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo-textual">
										<?php bloginfo( 'name' ); ?>
									</a>
								<?php endif; ?>
							</h1>

							<?php if ( get_theme_mod( 'logo_tagline' ) && get_bloginfo( 'description' ) ) : ?>
								<p class="site-tagline"><?php bloginfo( 'description' ); ?></p>
							<?php endif; ?>
						</div>

						<nav class="nav">
							<?php wp_nav_menu( array(
								'theme_location' => 'main_menu',
								'container'      => '',
								'menu_id'        => '',
								'menu_class'     => 'navigation-main',
							) ); ?>

							<a href="#mobilemenu" class="mobile-nav-trigger">
								<i class="fa fa-navicon"></i> <?php esc_html_e( 'Menu', 'specialty' ); ?>
							</a>
						</nav>

						<div id="mobilemenu"></div>
					</div>
				</div>
			</div>
		</div>
	</header>
