<?php
/**
 * Template Name: Pronouns
 * "Displays list of associated vocabulary categories."
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

			<?php bedrock_contentstart(); ?>

			<?php bedrock_get_breadcrumbs(); ?>

			<?php bedrock_abovepostcontent(); ?>
				
			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'content', 'page' ); ?>

				<?php comments_template( '', true ); ?>

			<?php endwhile; // end of the loop. ?>

			<?php

				// Vocabulary Game Display
				while ( have_posts() ) : the_post();
					echo '<div class="row" id="vocabulary-games">';
					$vocabularyCategories = get_terms('vocabulary_categories', array(
						'order' => 'DESC',
						'hide_empty' => 0, // TURN OFF AFTER DEVELOPMENT
					));
					echo '<ul>';
					foreach ($vocabularyCategories as $vocabularyCategory) {
					echo '<li>';
					echo 	'<a class="module vocabulary-category" href="javascript:void(0);" title="View '.$vocabularyCategory->slug.' words" data-category="'.$vocabularyCategory->name.'">';
					echo 	'<h1>'.$vocabularyCategory->name.'</h1>';
					echo 	'<h2>'.$vocabularyCategory->description.'</h2>';
					echo 	'</a>';
					echo '</li>';
					}
					echo '</ul>';
					echo '</div>'; // #vocabulary-categories
				endwhile;

			?>

			<?php bedrock_belowpostcontent(); ?>

			<?php bedrock_contentend(); ?>
			
			</div><!-- #content .site-content -->
		</div><!-- #primary .content-area -->
		<?php get_sidebar(); ?>
	</div><!-- .row-fluid -->

<?php get_footer(); ?>