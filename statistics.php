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

get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
			<?php
				// Allow only admins to see statistics
				if ( current_user_can('edit_posts') ) : ?>
					<?php

						// Current Students Registered
						$student_query = new WP_User_Query( array(
							'role' 		=> 'student',
							'fields'	=> 'ID'
						));
						// All Current Student's Records
						global $wpdb;
						$userInteractionIDs = $wpdb->get_col( $wpdb->prepare(
							"
							SELECT user_id
							FROM wp_user_interactions
							GROUP BY user_id
							"
						));

						echo '<ul class="user-statistics">';
						// Question A: Find active students
						$activeStudents = count($userInteractionIDs);
						echo '<li style="color:#4D5360;">';
						echo 'Total number of active users: ' . $activeStudents;
						echo '</li>';

						// Question 2: How many users never accessed the site?
						// If user id does not exist in user_interactions table, they have not accessed the site.
						$studentsNotParticipating = $student_query->total_users - count($userInteractionIDs); // Total # of active students subtracted from total # of students
						echo '<li style="color:#F7464A;">';
						echo 'Total number of users not participating: ' . $studentsNotParticipating;
						echo '</li>';

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