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
	
	<!-- Basic Hawaiian Home -->

	<?php
		if ( ! is_user_logged_in() ) {
		echo '<div id="primary" class="content-area row">';
		echo '<div id="content" class="site-content" role="main">';
			while ( have_posts() ) : the_post();
			echo get_the_content();
			endwhile; // end of the loop.
			echo '<a class="btn btn-large btn-success" href="'.site_url().'/registration">Sign up today</a>';
		} else {
	?>

	<!-- Learner's Dashboard -->
			
	<div id="primary" class="content-area row">
		<div id="content" class="site-content span12" role="main">
			
			<!-- User Metadata -->

			<div class="row">
				<header class="span12">
					<h1><?php _e('Welcome','hwn'); ?></h1>
				</header>
				<div class="span6">
					<?php // Get User Profile
						$current_user = wp_get_current_user();
						echo '<h3>'. $current_user->user_firstname . '&nbsp;' . $current_user->user_lastname .'</h3>'; // There's a space
					?>
				</div>
			</div>

			<!-- Content Navigation -->

			<div class="row">
			<?php
				
				// PROBLEMS:
				// If areModulesCompleted has one good value, record interactions passes
				// Rows now error out if they are duplicates. Might need to create ON DUPLICATE KEY UPDATE clause.
				// EXCELLENT! http://stackoverflow.com/questions/779986/insert-multiple-rows-via-a-php-array-into-mysql
				// Object completion check doesn't associate completion with an ID but with a particular order instead.

				// Possibly create graceful degredation if there are no Kukui's (more useful error)

				// Retrieve all units...
				$units = new WP_Query(array(
					'post_type' => 'units',
					'orderby' => 'ID',
					'order' => 'DESC',
					'posts_per_page' => 10,
				));

				// Grab all post IDs from units...
				$unitIDs = array();
				while ( $units->have_posts() ) : $units->the_post();
					$unitIDs[] = $post->ID;
				endwhile;
				rewind_posts();

				// Check if user has interacted with any of the ids above...
				if ( is_user_logged_in() &&  $units->have_posts() ) {

					$current_user = wp_get_current_user();
					$user_ID = $current_user->ID;
					$post_ids = implode(', ',$unitIDs); // Prepare IDs to get passed to the query
					$post_ids_safe = mysql_real_escape_string($post_ids); // Just because I like being safe.

					/*
					 * Check if user has interacted with object before (using the data later that's why)
					 * Tells us if they have a record or not.
					 */
					$areUnitsCompleted = $wpdb->get_results( $wpdb->prepare(
						"
						SELECT times_completed
						FROM wp_user_interactions
						WHERE user_id = %d
							AND post_id IN (".$post_ids_safe.")
						ORDER BY post_id DESC
						LIMIT 0, 10
						"
						, $user_ID
					));

					/*
					 * Create initial records for users who haven't seen this queried object.
					 */
					// If no record of interactions with modules exist in the database...
					// Error check: This database will only be updated if the amount
					// of IDs that exist are different from the IDs the user has interacted with.
					if ( count( $unitIDs ) != count( $areUnitsCompleted ) ) {
						// construct the loop for publishing
						$values = array();
						$placeHolders = array();

						// Prepare individual values separately to get passed to the query
						while ($units->have_posts()) : $units->the_post();
							$values[] = $user_ID.',';
							$values[] = $post->ID.',';
							$values[] = '0';
							$placeHolders[] = '(%d, %d, %d)';
						endwhile;
						rewind_posts();

						// Prepare placeholders for the query
						$placeHolderCreate = implode( ', ', $placeHolders );

						// Insert records for the user
						$wpdb->query( $wpdb->prepare("
							INSERT INTO wp_user_interactions
							( user_id, post_id, times_completed )
							VALUES ".$placeHolderCreate."
						", $values ));
					}

					// Rewind query for use in final call
					wp_reset_postdata();

				} else { ?>
					<p>Currently, there are no published units available.</p>
				<?php } // is_user_logged_in

				/*
				 * Let's display all our Units now...
				 */
				echo '<ul class="units dashboard-selections">';
				$unitCount = 0;
				while ( $units->have_posts() ) : $units->the_post();
					echo '<li>';
						echo 	'<a class="units dashboard-selection" href="'.get_permalink().'" title="Go to the'.get_the_title().' activity"';
						// Check to see if we user has completed any modules
						if ( $areUnitsCompleted ) {
							if ( $areUnitsCompleted[$unitCount]->times_completed == 0 ) {
								echo 'data-complete="0"';
							} else {
								echo 'data-complete="1"';
							}
						}
						echo 	'>';
						echo 		'<div class="dashboard-selection-info"><h3>'.get_the_title().'</h3></div>';
						echo 	'</a>';
					echo '</li>';
					$unitCount++;
				endwhile;
				echo '</ul>';
				wp_reset_postdata();

			?>
			</div><!-- .row -->

			<?php } // End Dashboard ?>

		</div><!-- #content .site-content -->
	</div><!-- #primary .content-area -->
	<?php //get_sidebar(); ?>

<?php get_footer(); ?>