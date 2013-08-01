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

get_header();
increment_object_value ( $post->ID, 'times_viewed' );

?>
	
	<!-- Public Home -->

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
		<div id="content" class="site-content" role="main"
			data-post-id="<?php echo $post->ID; ?>" 
			data-viewed="<?php echo is_first_object_visit( $post->ID ); ?>"
			data-complete="<?php echo is_object_complete( $post->ID ) ? "1" : "0"; ?>">
			
			<!-- User Metadata -->

			<header>
				<?php if ( !is_unit_complete( 204 ) ) { // Checking if Aunty Aloha is done ?>
				<h1><?php _e('Visit <span class="inline-kukui aunty-aloha">&#8216;Anak&#275; Aloha</span> in her Garden','hwn'); ?></h1>
				<?php } else { ?>
				<h1><?php _e('Visit a kukui to further your knowledge.','hwn'); ?></h1>
				<?php } ?>
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

				// Determine if unit is complete prior to loop (bug)
				foreach ($unitIDs as $unitID) {
					if ( is_unit_complete($unitID) ) {
						$unitsCompleted[] = 1;
					} else {
						$unitsCompleted[] = 0;
					}
				}

				// Create fresh object records if they do not have any for this page.
				create_object_record( $unitIDs );

				// Show all units and check if they are complete
				echo '<ul class="units dashboard-selections row">';
				$unitCount = 0;
				while ( $units->have_posts() ) : $units->the_post();
					$unitID = $post->ID;
					$unitLink = get_permalink();
					$unitTitle = get_the_title();
					$popoverContent = "Topics in this module: <ul><li>topicone</li><li>topictwo</li><li>topicthree</li></ul><a class='btn btn-primary' href='$unitLink' title='Go to this unit'>Visit $unitTitle</a>";
					echo '<li class="unit pull-left">';
						echo 	'<a class="dashboard-selection post'.$post->ID.'" href="javascript:void(0);" data-title="'.get_the_title().'" data-content="'.$popoverContent.'" data-complete="'.$unitsCompleted[$unitCount].'">';
						echo 		'<div class="dashboard-selection-info"><h4>'.get_the_title().'</h4></div>';
						echo 	'</a>';
					echo '</li>';
					$unitCount++;
				endwhile;
				echo '</ul>';
				wp_reset_postdata();

				/**
				 * User Avatar
				 */
				$user = wp_get_current_user();
    		$user_id = $user->ID;
				$gender = get_user_meta( $user_id, 'gender', true );
				echo '<div class="user-avatar '.$gender.' default"></div>';

			} // End Dashboard ?>

		</div><!-- #content .site-content -->
	</div><!-- #primary .content-area -->
	<?php //get_sidebar(); ?>

<?php get_footer(); ?>