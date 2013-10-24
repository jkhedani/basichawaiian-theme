<?php
/**
 * Template Name: Statistics
 * Use this template to display statistics about the site to admins
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package _s
 * @since _s 1.0
 */

get_header();

global $wpdb;

?>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
			<?php
				// Allow only admins to see statistics
				if ( current_user_can('edit_posts') ) : ?>
					<?php

						// Retrieve data on all students that are _registered_
						$student_query = new WP_User_Query( array(
							'role' 		=> 'student',
							'fields'	=> 'ID'
						));
						// Retrieve data regarding users who have interacted with the site
						$userInteractionIDs = $wpdb->get_col( $wpdb->prepare(
							"
							SELECT user_id
							FROM wp_user_interactions
							GROUP BY user_id
							"
						));

						/**
						 *	Display Statistics
						 */
						echo '<h1>Statistics</h1>';
						echo '<ul class="user-statistics">';
						
						/**
						 * 	Question 1: Find active students
						 *	Active users are defined as users who have interacted with the site in some form or manner.
						 */
						$activeStudents = count($userInteractionIDs);
						echo '<li style="color:#4D5360;">';
						echo '<h2>Total number of active users: ' . $activeStudents . '</h2>';
						echo '</li>';

						/**
						 *	Question 2: How many users never accessed the site?
						 *	If user id does not exist in user_interactions table, they have not accessed the site.
						 */
						$studentsNotParticipating = $student_query->total_users - count($userInteractionIDs); // Total # of active students subtracted from total # of students
						echo '<li style="color:#F7464A;">';
						echo 'Total number of users not participating: ' . $studentsNotParticipating;
						echo '</li>';

						/**
						 *	Question 2: How many users completed _All Sections of Module One_?
						 *  Find all modules associated with a given unit
						 */
						$auntyAlohaID = 204;
						$query = new WP_Query( array(
							'connected_type' => 'modules_to_units',
							'connected_items' => $auntyAlohaID,
							'post_type' => 'module',
							'orderby' => 'menu_order', // Relying on order to discover "module numbers"
						));
						while ( $query->have_posts() ) : $query->the_post();
							$connectedModuleIDs[] = $post->ID;
						endwhile;
						wp_reset_postdata();

						// Find topics connected to unit one module one
						$moduleOne = $connectedModuleIDs[0];

						// Within each module, find associated topics
						$query = new WP_Query( array(
							'connected_type' => 'topics_to_modules',
							'connected_items' => $moduleOne,
							'post_type' => 'topic',
						));
						while ( $query->have_posts() ) : $query->the_post();
							$connectedTopicIDs[] = $post->ID;
						endwhile;
						wp_reset_postdata();

						// Find all user IDs that have times_completed >= 1 for each of the topics in Module One
						$ids = join(',',$connectedTopicIDs); 
						echo $ids;
						$usersCompletedModuleOne = $wpdb->get_results(
							"
							SELECT user_id
							FROM wp_user_interactions
							WHERE post_id IN ($ids)
							AND times_completed >= 1
							LIMIT 9999
							"
						);
						echo '<pre>';
						var_dump($usersCompletedModuleOne);
						echo '</pre>';
						/**
						 *	Most Difficult Piece of content
						 */
						// $mostDifficultPiecesOfContent = $wpdb->get_results( $wpdb->prepare(
						// 	"
						// 	SELECT post_id
						// 	FROM wp_user_interactions
						// 	ORDER BY times_wrong DESC
						// 	"
						// ));
						// var_dump($mostDifficultPiecesOfContent);

						/**
						 *	Retrieve active student emails
						 */
						// $active_students_query = new WP_User_Query( array(
						// 	'role' => 'student',
						// 	'include' => $userInteractionIDs,
						// ));
						// echo '<li>';
						// echo 'Active user emails:';
						// foreach ( $active_students_query->results as $active_student_record ) {
						// 	echo $active_student_record->user_email . ', <br/>';
						// }
						// echo '</li>';
					?>
					<script>
					  var activeStudents = <?php echo json_encode($activeStudents); ?>;
					  var studentsNotParticipating = <?php echo json_encode($studentsNotParticipating); ?>;
					</script>

					<!-- Chart One: Number of users over time -->
					<canvas id="canvas-chart" class="home" data-chart="" height="450" width="600"></canvas>
					<!-- Total number of users registered -->

				<?php
				// If user is not an admin, display useful feedback.
				else :
					echo 'This page is for administrators only. Please contact us if you reached this message in error.';
				endif;

			?>
		</div><!-- #content .site-content -->
	</div><!-- #primary .content-area -->
	<?php //get_sidebar(); ?>

<?php get_footer(); ?>