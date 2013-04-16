<?php
/**
 * Template name: Scoresheet
 *
 * @package _s
 * @since _s 1.0
 */

get_header(); ?>

	<div class="row"><!-- Bootstrap: REQUIRED! -->
		<div id="primary" class="content-area span10">
			<div id="content" class="site-content" role="main">

			<?php bedrock_contentstart(); ?>

			<?php bedrock_get_breadcrumbs(); ?>

			<?php bedrock_abovepostcontent(); ?>
				
			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'content', 'page' ); ?>

				<?php comments_template( '', true ); ?>

			<?php endwhile; // end of the loop. ?>

			<?php
				// If the user is logged in,
				// Grab all the content they've seen
			  // and display the scores for each

				if(is_user_logged_in()) {
				 	$current_user = wp_get_current_user();
				 	$user_ID = $current_user->ID;
				 	$record_limit = 1000;
				 	$viewedCardIDs = $wpdb->get_results($wpdb->prepare(
				 		"
				 		SELECT *
				 		FROM wp_user_interactions
						WHERE user_id = %d
						LIMIT 0, %d
						"
						, $user_ID, $record_limit
					), ARRAY_A);
				}

				// Using queried data, find Vocabulary Game scores

				// REMOVE AFTER TESTING!!!! //
				echo '<p>DANGER! For testing purposes only!';
				echo '<a href="javascript:void(0);" class="btn btn-danger reset-scores">Reset</a>';
				// REMOVE AFTER TESTING!!!! //
				
				echo '<h3>Vocabulary Terms</h3>';
				echo '<ul class="vocabulary-terms scoresheet">';
				// Render 'table' legend
				echo '<li class="vocabulary-term record legend">
								Term name
								<ul class="scores">
									<li class="times-correct span2">Times Correct</li>
									<li class="times-wrong span2">Times Wrong</li>
									<li class="times-viewed span2">Times Viewed</li>
								</ul>
							</li>';
				// Render each record
				$index = 0;
				foreach ($viewedCardIDs as $key => $value) {
					// Grab all vocabulary terms viewed and display their values
					if(get_post_type($value['post_id']) == 'vocabulary_terms') {
						echo '<li class="vocabulary-term record">';
						echo get_the_title($value['post_id']);
						echo '<ul class="scores">';
						echo '<li class="times-correct span2">'.$value['times_correct'].'</li>';
						echo '<li class="times-wrong span2">'.$value['times_wrong'].'</li>';
						echo '<li class="times-viewed span2">'.$value['times_viewed'].'</li>';
						echo '</ul>';
						echo '</li>';
					} 
				}
				echo '</ul>';
			?>

			<?php bedrock_belowpostcontent(); ?>

			<?php bedrock_contentend(); ?>
			
			</div><!-- #content .site-content -->
		</div><!-- #primary .content-area -->
		<?php //get_sidebar(); ?>
	</div><!-- .row-fluid -->

<?php get_footer(); ?>