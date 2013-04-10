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
			echo '<a class="btn btn-large btn-success href="#">Sign up today</a>';
			echo '<hr />';
			echo '<div class="row marketing">';
			echo 	'<div class="span4"><h4>Subheading</h4><p>Donec id elit non mi porta gravida at eget metus. Maecenas faucibus mollis interdum.</p></div>';
			echo 	'<div class="span4"><h4>Subheading</h4><p>Donec id elit non mi porta gravida at eget metus. Maecenas faucibus mollis interdum.</p></div>';
			echo 	'<div class="span4"><h4>Subheading</h4><p>Donec id elit non mi porta gravida at eget metus. Maecenas faucibus mollis interdum.</p></div>';
			echo '</div>';
		} else {
	?>

	<!-- Learner's Dashboard -->
			
	<div id="primary" class="content-area row">
		<div id="content" class="site-content span12" role="main">
			
			<!-- User Metadata -->

			<div class="row">
				<header class="span12">
					<h1><?php _e('Welcome','hwn'); ?></h1>
					<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo massa. Etiam lorem eros, ullamcorper a rutrum non.</p>
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

				// Retrieve all modules...
				$modules = new WP_Query(array(
					'post_type' => 'modules',
					'orderby' => 'menu_order',
					'order' => 'ASC',
				));

				// Grab all post IDs from modules...
				$moduleIDs = array();
				while ($modules->have_posts()) : $modules->the_post();
					$moduleIDs[] = $post->ID;
				endwhile;
				rewind_posts();

				// Check if user has interacted with any of the ids above...
				if(is_user_logged_in()) {
					$current_user = wp_get_current_user();
					$user_ID = $current_user->ID;
					$post_ids = implode(', ',$moduleIDs); // Prepare IDs to get passed to the query
					$post_ids_safe = mysql_real_escape_string($post_ids); // Just because I like being

					// Check if modules have been completed (using the data later that's why)
					$areModulesCompleted = $wpdb->get_results($wpdb->prepare(
						"
						SELECT times_completed
						FROM wp_user_interactions
						WHERE user_id = %d
							AND post_id IN (".$post_ids_safe.")
						LIMIT 0, 10
						"
						, $user_ID
					));

					// If no record of interactions with modules exist in the database...
					// Error check: This database will only be updated if the amount
					// of IDs that exist are different from the IDs the user has interacted with.
					if(count($moduleIDs) != count($areModulesCompleted)) {
						// construct the loop for publishing
						$values = array();
						$placeHolders = array();

						// Prepare individual values separately to get passed to the query
						while ($modules->have_posts()) : $modules->the_post();
							$values[] = $user_ID.',';
							$values[] = $post->ID.',';
							$values[] = '0';
							$placeHolders[] = '(%d, %d, %d)';
						endwhile;
						rewind_posts();

						// Prepare placeholders for the query
						$placeHolderCreate = implode(', ', $placeHolders);

						// Insert records for the user
						$wpdb->query( $wpdb->prepare("
							INSERT INTO wp_user_interactions
							( user_id, post_id, times_completed )
							VALUES ".$placeHolderCreate."
						", $values ));
					}
				} // is_user_logged_in

				rewind_posts();

				echo '<ul>';
				// Display all modules
				$moduleCount = 0;
				while ($modules->have_posts()) : $modules->the_post();
					echo '<li>';
					echo 	'<a class="module" href="'.get_permalink().'" title="Go to the'.get_the_title().' activity"';
					// THIS IS NOW WRONG AS IT ONLY GRABS THE FIRST RECORD OF $areModulesCOmpleted
					if ($areModulesCompleted[$moduleCount]->times_completed == 0) {
						echo 'data-complete="0"';
					} else {
						echo 'data-complete="1"';
					}
					echo 	'>';
					echo 		'<h2>'.get_the_title().'</h2>';
					echo 	'</a>';
					echo '</li>';
					$moduleCount++;
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