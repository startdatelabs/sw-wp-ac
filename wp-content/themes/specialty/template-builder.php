<?php
/*
 * Template Name: Page Builder
 */
?>
<?php get_header(); ?>
<?php get_template_part( 'part-hero', get_post_type() ); ?>

<?php while ( have_posts() ) : the_post(); ?>
	<?php the_content(); ?>
<?php endwhile; ?>

<?php get_footer();
