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
	
	<div class="row-fluid"><!-- Bootstrap: REQUIRED! -->
		<div id="primary" class="content-area span15">
			<div id="content" class="site-content" role="main">
				<h1 class="entry-title"><?php the_title(); ?></h1>
				<?php
					if ($post->post_name == 'vocabulary-building') {
						while ( have_posts() ) : the_post();
							echo '<div class="row-fluid" id="vocabulary-games">';
							$vocabularyCategories = get_terms('vocabulary_categories', array(
								'order' => 'DESC',
								'hide_empty' => 0, // TURN OFF AFTER DEVELOPMENT
							));
							foreach ($vocabularyCategories as $vocabularyCategory) {
							//echo '<div class="span5">';
							echo 	'<a class="module vocabulary-category span5" href="javascript:void(0);" title="View '.$vocabularyCategory->slug.' words" data-category="'.$vocabularyCategory->name.'">';
							echo 	'<h1>'.$vocabularyCategory->name.'</h1>';
							echo 	'<h2>'.$vocabularyCategory->description.'</h2>';
							echo 	'</a>';
							//echo '</div>';
							}
							echo '</div>'; // #vocabulary-categories
						endwhile;
					} else { // end if vocabulary-building
						while ( have_posts() ) : the_post();
						get_template_part( 'content', 'page' );
						comments_template( '', true );
						endwhile;
					}
				?>
				</div><!-- .row-fluid -->

			</div><!-- #content .site-content -->
		</div><!-- #primary .content-area -->
		<?php //get_sidebar(); ?>
	</div><!-- .row-fluid -->

<?php get_footer(); ?>