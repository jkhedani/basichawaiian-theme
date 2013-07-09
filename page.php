<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package _s
 * @since _s 1.0
 */

get_header(); ?>

	<div class="row"><!-- Bootstrap: REQUIRED! -->
		<div id="primary" class="content-area span8">
			<div id="content" class="site-content" role="main">

			<?php bedrock_get_breadcrumbs(); ?>
				
			<?php
				while ( have_posts() ) : the_post();
					/*
					 * NOTE: Ensure we move these calls to a setting somewhere.
					 */
					if ( is_page() == 'progress' ) {
						get_template_part( 'templates/progress', 'page' );
					} else {
						get_template_part( 'templates/content', 'page' );
					}
				endwhile; // end of the loop.
			?>

			</div><!-- #content .site-content -->
		</div><!-- #primary .content-area -->
		<?php //get_sidebar(); ?>
	</div><!-- .row-fluid -->

<?php get_footer(); ?>