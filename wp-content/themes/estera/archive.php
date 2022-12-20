<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Estera
 */

get_header(); ?>

<div class="container">
	<div class="wrapper">
		<main id="primary" class="site-main">

			<?php if ( have_posts() ) : ?>

				<header class="page-header">
					<?php
					the_archive_title( '<h1 class="page-title">', '</h1>' );
					the_archive_description( '<div class="archive-description">', '</div>' );
					?>
				</header><!-- .page-header -->

				<?php ?>
				
				<div class="post-wrapper"> 
					
				<?php /* Start the Loop */
				while ( have_posts() ) :
					the_post();

					/*
					* Include the Post-Type-specific template for the content.
					* If you want to override this in a child theme, then include a file
					* called content-___.php (where ___ is the Post Type name) and that will be used instead.
					*/
					
					/* Display full post content or post excerpt depending on user settings in the theme customizer.*/
					if ( estera_is_excerpt() ) :
						get_template_part( 'template-parts/content-excerpt', get_post_type() );
					else : 
						get_template_part( 'template-parts/content', get_post_type() );
					endif;
					
				endwhile; ?>
				</div>

				<?php estera_the_posts_navigation();

			else :

				get_template_part( 'template-parts/content', 'none' );

			endif;
			?>

		</main><!-- #main -->

	<?php get_sidebar(); ?>

	</div>
</div>

<?php get_footer();
