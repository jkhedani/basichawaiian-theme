<?php
/**
 * The Template for displaying all single posts.
 *
 * @package _s
 * @since _s 1.0
 */

get_header(); ?>

	<div class="row"><!-- Bootstrap: REQUIRED! -->
		<div id="primary" class="content-area span12">
			<div id="content" class="site-content" role="main">
			
			<?php bedrock_contentstart(); ?>

			<?php bedrock_get_breadcrumbs(); ?>

			<?php bedrock_abovepostcontent(); ?>

			<?php
				// MODULE PAGE CONTENT
				if(get_post_type($post->ID) == 'units') {
					get_template_part( 'templates/unit', 'single' );
				
				// VOCABULARY GAMES CONTENT
				} elseif(get_post_type($post->ID) == 'vocabulary_games') {
					get_template_part( 'templates/vocabulary-game', 'single' );

				// DEFAULT SINGLE LOOP
				} else {
					while ( have_posts() ) : the_post();
						get_template_part( 'templates/content', 'single' );
						// If comments are open or we have at least one comment, load up the comment template
						if ( comments_open() || '0' != get_comments_number() )
							comments_template( '', true );
					endwhile;
				} // endif;
			?>

			<?php bedrock_belowpostcontent(); ?>

			<?php bedrock_contentend(); ?>

			</div><!-- #content .site-content -->
		</div><!-- #primary .content-area -->
		<?php //get_sidebar(); ?>
	</div><!-- .row-fluid -->

<?php get_footer(); ?>