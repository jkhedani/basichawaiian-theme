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

// THINGS TO DO HERE:
// We should time stamp values every week to have some aspect of time.

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
						$userInteractionIDs = $wpdb->get_col(
							"
							SELECT user_id
							FROM wp_user_interactions
							GROUP BY user_id
							"
						);
						function studentCompletedModule( $unitID, $moduleArrayPosition ) {
						  global $wpdb, $post;

						  $connectedModuleIDs = array(); // instantiate first.
						  $query = new WP_Query( array(
						    'connected_type' => 'modules_to_units',
						    'connected_items' => $unitID,
						    'post_type' => 'module',
						    'orderby' => 'menu_order', // Relying on order to discover "module numbers"
						  ));
						  while ( $query->have_posts() ) : $query->the_post();
						    $connectedModuleIDs[] = $post->ID;
						  endwhile;
						  wp_reset_postdata();

						  // A. Find topics connected to unit one module one
						  $moduleOne = $connectedModuleIDs[$moduleArrayPosition];

						  // B. Within module one, find connected topics
						  $connectedTopicIDs = array(); // instantiate first
						  $query = new WP_Query( array(
						    'connected_type' => 'topics_to_modules',
						    'connected_items' => $moduleOne,
						    'post_type' => 'topic',
						  ));
						  while ( $query->have_posts() ) : $query->the_post();
						    $connectedTopicIDs[] = $post->ID;
						  endwhile;
						  wp_reset_postdata();

						  // Only run DB queries if we have data to pass to it...
						  if ( !empty($connectedModuleIDs) && !empty($connectedTopicIDs) ) {

						    // C. Process topic IDs to find connected lessons
						    $topicIDs = join(',',$connectedTopicIDs);
						    $lessonConnectionTypes =  '"instructional_lessons_to_topics","readings_to_topics","vocabulary_lessons_to_topics","phrases_lessons_to_topics","pronoun_lessons_to_topics","song_lessons_to_topics","chants_lessons_to_topics"';

						    // D. Find all topics connected to module one
						    $connectedTopics = $wpdb->get_results(
						      "
						      SELECT p2p_from
						      FROM wp_p2p
						      WHERE p2p_to IN ($topicIDs)
						      AND p2p_type IN ($lessonConnectionTypes)
						      ORDER BY p2p_from ASC
						      LIMIT 99999
						      "
						    );

						    // Filter out any posts that are not published and/or trashed
						    $publishedConnectedTopics = array(); // instantiate first
						    foreach ($connectedTopics as $lessonID) {
						      if ( get_post_status( $lessonID->p2p_from ) == 'publish' ) {
						        $publishedConnectedTopics[] = $lessonID->p2p_from;
						      }
						    } 

						    // E. Find all user IDs that have times_completed >= 1 for each of the lessons in Module One
						    $lessonIDs = join(',',$publishedConnectedTopics);
						    $usersProgressModuleOne = $wpdb->get_results(
						      "
						      SELECT *,
						      GROUP_CONCAT(post_id ORDER BY post_id ASC) posts_completed
						      FROM wp_user_interactions
						      WHERE post_id IN ($lessonIDs)
						      AND times_completed >= 1
						      GROUP BY user_id
						      LIMIT 99999
						      "
						    );

						    // F. Compare string of connected modules
						    $usersCompletedModuleOne = array(); // Instantiate first
						    foreach ($usersProgressModuleOne as $userRecord) {
						      if ( $userRecord->posts_completed == $lessonIDs ) {
						        $usersCompletedModuleOne[] = $userRecord->user_id;
						      }
						    }
						    return $usersCompletedModuleOne;
						  } else {
						    return false;
						  } // ifelse data exists
						}

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
						echo '<h3>Total number of active users: ' . $activeStudents . '</h3>';
						echo '</li>';

						/**
						 *	Question 2: How many users never accessed the site?
						 *	If user id does not exist in user_interactions table, they have not accessed the site.
						 */
						$studentsNotParticipating = $student_query->total_users - count($userInteractionIDs); // Total # of active students subtracted from total # of students
						echo '<li style="color:#F7464A;">';
						echo '<h3>Total number of users not participating: ' . $studentsNotParticipating . '</h3>';
						echo '</li>';

						/**
						 *	Question 3: Show Students who have completed module one
						 */
						if ( studentCompletedModule(204, 0) ) {
							$studentsCompletedModuleOne = count(studentCompletedModule(204, 0));
						} else {
							$studentsCompletedModuleOne = 0;
						}
						echo '<li style="color:#949FB1;">';
						echo '<h3>Total number of users who completed Module One: ' . $studentsCompletedModuleOne . '</h3>';
						echo '</li>';

						/**
						 *	Question 4: Show Students who have completed module three
						 */
						if ( studentCompletedModule(204, 2) ) { // make sure this value isn't empty
							$studentsCompletedModuleThree = count(studentCompletedModule(204, 2));	
						} else {
							$studentsCompletedModuleThree = 0;
						}
						echo '<li style="color:#749FB1;">';
						echo '<h3>Total number of users who completed Module Three: ' . $studentsCompletedModuleThree . '</h3>';
						echo '</li>';

						/**
						 *	Question 5: Show Students who have completed module six
						 */
						if ( studentCompletedModule(204, 5) ) { // make sure this value isn't empty
							$studentsCompletedModuleSix = count(studentCompletedModule(204, 5));	
						} else {
							$studentsCompletedModuleSix = 0;
						}
						echo '<li style="color:#947FC1;">';
						echo '<h3>Total number of users who completed Module Six: ' . $studentsCompletedModuleSix . '</h3>';
						echo '</li>';

						/**
						 *	Question 6: Show Students who have completed module eight
						 */
						if ( studentCompletedModule(204, 7) ) { // make sure this value isn't empty
							$studentsCompletedModuleEight = count(studentCompletedModule(204, 7));	
						} else {
							$studentsCompletedModuleEight = 0;
						}
						echo '<li style="color:#545FC1;">';
						echo '<h3>Total number of users who completed Module Eight: ' . $studentsCompletedModuleSix . '</h3>';
						echo '</li>';

						echo '</ul>'; // user-statistics
						?>

						<script>
						  var activeStudents = <?php echo json_encode($activeStudents); ?>;
						  var studentsNotParticipating = <?php echo json_encode($studentsNotParticipating); ?>;
						  var studentsCompletedModuleOne = <?php echo json_encode($studentsCompletedModuleOne); ?>;
						  var studentsCompletedModuleThree = <?php echo json_encode($studentsCompletedModuleThree); ?>;
						  var studentsCompletedModuleSix = <?php echo json_encode($studentsCompletedModuleSix); ?>;
						  var studentsCompletedModuleEight = <?php echo json_encode($studentsCompletedModuleEight); ?>;
						</script>

						<!-- Chart One: Number of users over time -->
						<canvas id="canvas-chart" class="home" data-chart="" height="450" width="600"></canvas>
						<!-- Total number of users registered -->


						<?php
						
						echo '<h1>Other User Data</h1>';
						echo '<h3>Active user emails:</h3>';
						/**
						 *	Retrieve active student emails
						 */
						$active_students_query = new WP_User_Query( array(
							'role' => 'student',
							'include' => $userInteractionIDs,
						));
						foreach ( $active_students_query->results as $active_student_record ) {
							echo $active_student_record->user_email . ', <br/>';
						}


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
					?>

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