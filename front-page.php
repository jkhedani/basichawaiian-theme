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
			
	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
			
			<!-- User Metadata -->

			<header>
				<h1><?php _e('Welcome','hwn'); ?></h1>
				<hr />
			</header>

			<!-- Content Navigation -->
			
			<?php

				/*
				 * Display user primary "dashboard" (All Kukui People)
				 */
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

				// Create fresh object records if they do not have any for this page.
				create_object_record( $unitIDs );

				// Show all units and check if they are complete
				echo '<ul class="units dashboard-selections row">';
				$unitCount = 0;
				while ( $units->have_posts() ) : $units->the_post();
					echo '<li class="unit span4 pull-left">';
						echo 	'<a class="dashboard-selection" href="'.get_permalink().'" title="Go to the'.get_the_title().' activity"';
						// Check to see if we user has completed any modules
						if ( is_object_complete( $post->ID ) ) {
							echo 'data-complete="1"';
						} else {
							echo 'data-complete="0"';
						}
						echo 	'>';
						echo 		'<div class="dashboard-selection-info"><h4>'.get_the_title().'</h4></div>';
						echo 	'</a>';
					echo '</li>';
					$unitCount++;
				endwhile;
				echo '</ul>';
				wp_reset_postdata();
			} // End Dashboard ?>

		</div><!-- #content .site-content -->
	</div><!-- #primary .content-area -->
	<?php //get_sidebar(); ?>

<?php get_footer(); ?>