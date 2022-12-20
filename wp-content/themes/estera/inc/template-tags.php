<?php
/**
 * Custom template tags for Estera theme
 *
 * @package Estera
 */

if ( ! function_exists( 'estera_posted_on' ) ) :
	/**
	 * Prints HTML with meta information for the current post-date/time.
	 */
	function estera_posted_on() {
		//check user settings in theme customizer
		$show_post_published_date = get_theme_mod( 'show_post_date', 1 );

		if($show_post_published_date){

			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';

			$time_string = sprintf(
				$time_string,
				esc_attr( get_the_date( DATE_W3C ) ),
				esc_html( get_the_date() )
			);
	
			$posted_on = sprintf(
				esc_html( '%s', 'post date'),
				'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
			);
	
			echo '<span class="posted-on">' . $posted_on . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	
		}
	}
endif;

if ( ! function_exists( 'estera_posted_by' ) ) :
	/**
	 * Prints HTML with meta information for the current author.
	 */
	function estera_posted_by() {

		$show_post_author = get_theme_mod( 'show_post_author', 1 );

		if($show_post_author){

			$byline = sprintf(
				esc_html( '%s', 'post author' ),
				'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
			);
	
			echo '<span class="byline"> ' . $byline . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	
		}
		
	}
endif;

if ( ! function_exists( 'estera_entry_footer' ) ) :
	/**
	 * Prints HTML with meta information for the categories, tags and comments.
	 */
	function estera_entry_footer() {
		// Hide category and tag text for pages.
		if ( 'post' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */

			$display_category_list = get_theme_mod( 'show_post_categories', 1 );
			$display_tag_list = get_theme_mod( 'show_post_tags', 1 );
			$display_comments = get_theme_mod( 'show_post_comments', 1 );

			if($display_category_list) {

				$category_list = get_the_category_list(esc_html(', '));?>
				<?php if ($category_list): ?>
				<span class="cat-links">
					<?php printf( /* category list */esc_html('%s'), $category_list); // xss ok. ?>
				</span>
					<?php endif;
			
			}

			if($display_tag_list) {

				/* translators: used between list items, there is a space after the comma */
				$tags_list = get_the_tag_list('', esc_html(', '));
				if ( $tags_list ) {
				/* translators: 1: list of tags. */
					printf( '<span class="tags-links">' . esc_html( '%s') . '</span>', $tags_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			
			}
			
			if($display_comments){
			
				if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
					echo '<span class="comments-link">';
					comments_popup_link(
						sprintf(
							wp_kses(
								/* translators: %s: post title */
								__( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'estera' ),
								array(
									'span' => array(
										'class' => array(),
									),
								)
							),
							wp_kses_post( get_the_title() )
						)
					);
					echo '</span>';
				}
		
			}

		}
		
		edit_post_link(
			sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers */
					__( 'Edit <span class="screen-reader-text">%s</span>', 'estera' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				wp_kses_post( get_the_title() )
			),
			'<span class="edit-link">',
			'</span>'
		);
	}
endif;

if ( ! function_exists( 'estera_post_thumbnail' ) ) :
	/**
	 * Displays an optional post thumbnail.
	 *
	 * Wraps the post thumbnail in an anchor element on index views, or a div
	 * element when on single views.
	 */
	function estera_post_thumbnail( $size='' ) {
		if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
			return;
		}

		if ( is_singular() ) :
			?>

			<div class="post-thumbnail">
				<?php the_post_thumbnail( $size ); ?>
			</div><!-- .post-thumbnail -->

		<?php else : ?>

			<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<?php
					the_post_thumbnail(
						$size,
						array(
							'alt' => the_title_attribute(
								array(
									'echo' => false,
								)
							),
						)
					);
				?>
			</a>

			<?php
		endif; // End is_singular().
	}
endif;

if ( ! function_exists( 'wp_body_open' ) ) :
	/**
	 * Shim for sites older than 5.2.
	 *
	 * @link https://core.trac.wordpress.org/ticket/12563
	 */
	function wp_body_open() {
		do_action( 'wp_body_open' );
	}
endif;