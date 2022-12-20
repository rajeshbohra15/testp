<?php

/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Estera
 */
?>

<article id="post-<?php 
the_ID();
?>" <?php 
post_class();
?>>

	<?php 
estera_post_thumbnail( 'large' );
?>

	<header class="entry-header">
		<?php 

if ( is_singular() ) {
    the_title( '<h1 class="entry-title">', '</h1>' );
} else {
    the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
}


if ( 'post' === get_post_type() ) {
    ?>
			<div class="entry-meta">
				<?php 
    estera_posted_on();
    estera_posted_by();
    ?>
			</div><!-- .entry-meta -->
		<?php 
}

?>
	</header><!-- .entry-header -->
	<?php 

if ( !estera_remove_post_content() ) {
    ?>
	<div class="entry-content">
		<?php 
    the_excerpt();
    ?>
	</div><!-- .entry-content -->
	<?php 
}

?>
	<footer class="entry-footer">
		<?php 
estera_entry_footer();
?>
	</footer><!-- .entry-footer -->
</article>