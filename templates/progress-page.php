<?php
/**
 * @package _s
 * @since _s 1.0
 */

$current_user = wp_get_current_user();
$current_user_id = $current_user->ID;
$current_user_data = get_userdata( $current_user_id );
$previousPageURL = get_home_URL();

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> data-lesson-id="<?php echo $post->ID; ?>">

	<header class="page-header">
		<a class="btn btn-back" href="<?php echo $previousPageURL; ?>"><i class="icon-arrow-left" style="padding-right:10px;"></i>Back to Unit View</a>
		<h1 class="page-title"><?php the_title(); ?></h1>
		<div><?php echo $current_user_data->user_firstname; ?> <?php echo $current_user_data->user_lastname; ?></div>
		<div>
			<?php
			// REMOVE AFTER TESTING!!!! //
			echo '<p>DANGER! For testing purposes only!';
			echo '<a href="javascript:void(0);" class="btn btn-danger reset-scores">Reset</a>';
			// REMOVE AFTER TESTING!!!! //
			?>
		</div>
	</header><!-- .page-header -->

	<div class="page-content">
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

				// Show all kukui's and their associated game objects
				$units = new WP_Query( array( 'post_type' => 'units', ));
				while( $units->have_posts() ) : $units->the_post();
					$objectTotal = 0;
					$objectCompleteTotal = 0;
					/* 
					 * UNITS
					 */
					echo '<h1>'. get_the_title() .'</h1>';
					echo '<p>Percent Complete:</p>';
					$unitID = $post->ID;
					$modules = new WP_Query( array(
						'connected_type' => 'modules_to_units',
						'connected_items' => $unitID,
						'nopaging' => true,
						'post_type' => 'modules',
					));
					while( $modules->have_posts() ) : $modules->the_post();
						/*
						 * MODULES
						 */
						echo '<h2>'. get_the_title() .'</h2>';
						$moduleID = $post->ID;
						$topics = new WP_Query( array(
							'connected_type' => 'topics_to_modules',
							'connected_items' => $moduleID,
							'nopaging' => true,
							'post_type' => 'topics',
						));
						while( $topics->have_posts() ) : $topics->the_post();
							
							/*
							 * TOPICS
							 */
							echo '<h3>'. get_the_title() .'</h3>';
							$topicID = $post->ID;

							/*
							 * LECTURES
							 */
							$lessons = new WP_Query( array(
								'connected_type' => 'lectures_to_topics',
								'connected_items' => $topicID,
								'nopaging' => true,
							));
							while( $lessons->have_posts() ) : $lessons->the_post();
								if ( is_object_complete($post->ID) ) :
									echo '<p class="completed">'. get_the_title() .'</p>';
									$objectCompleteTotal++;
								else :
									echo '<p>'. get_the_title() .'</p>';
								endif;	
								$objectTotal++;
							endwhile;

							/*
							 * VOCABULARY LESSONS
							 */
							$lessons = new WP_Query( array(
								'connected_type' => 'vocabulary_lessons_to_topics',
								'connected_items' => $topicID,
								'nopaging' => true,
							));
							while( $lessons->have_posts() ) : $lessons->the_post();
								echo '<p>'. get_the_title() .'</p>';
								$objectTotal++;
							endwhile;

							/*
							 * PRONOUN LESSONS
							 */
							$lessons = new WP_Query( array(
								'connected_type' => 'pronoun_lessons_to_topics',
								'connected_items' => $topicID,
								'nopaging' => true,
							));
							while( $lessons->have_posts() ) : $lessons->the_post();
								echo '<p>'. get_the_title() .'</p>';
								$objectTotal++;
							endwhile;

							/*
							 * PHRASES LESSONS
							 */
							$lessons = new WP_Query( array(
								'connected_type' => 'phrases_lessons_to_topics',
								'connected_items' => $topicID,
								'nopaging' => true,
							));
							while( $lessons->have_posts() ) : $lessons->the_post();
								echo '<p>'. get_the_title() .'</p>';
								$objectTotal++;
							endwhile;

							/*
							 * ACTIVITIES
							 */
							$lessons = new WP_Query( array(
								'connected_type' => 'activities_to_topics',
								'connected_items' => $topicID,
								'nopaging' => true,
							));
							while( $lessons->have_posts() ) : $lessons->the_post();
								echo '<p>'. get_the_title() .'</p>';
								$objectTotal++;
							endwhile;

							wp_reset_postdata();
						endwhile; // topics
						wp_reset_postdata();
					endwhile; // modules
					wp_reset_postdata();

					echo '<span class="unit-total-objects-complete">'.$objectCompleteTotal.'</span>';
					echo '<span class="unit-total-objects">'.$objectTotal.'</span>';
				endwhile; // units
				wp_reset_postdata();


				echo '<hr />';



				// Using queried data, find Vocabulary Game scores				
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
		
	</div><!-- .page-content -->

	<footer class="page-footer">
		
	</footer>

</article><!-- #post-<?php the_ID(); ?> -->