<?php
/**
 * @package _s
 * @since _s 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php bedrock_postcontentstart(); ?>

	<header class="entry-header">
		
		<?php
			// BREADCRUMB
			echo '<ul class="breadcrumb">';
			echo 	'<li class="breadcrumb-home"><a href="'.get_home_url().'" title="Go back home.">Home</a> <span class="divider">/</span></li>';
			echo 	'<li class="breadcrumb-last active">'.get_the_title().'</li>';
			echo '</ul>';
		?>

		<?php bedrock_abovetitle(); ?>
		
		<h1 class="entry-title"><?php the_title(); ?></h1>
		
		<?php bedrock_belowtitle(); ?>
		
		<hr />

		<div class="entry-meta">
			<?php //_s_posted_on(); ?>
		</div><!-- .entry-meta -->
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php

		the_content();
		
		/*
		 * Function: Viewed Object
		 * Usage: Run when you want to add a single view to an object
		 * Parameters:
		 *    userID(int): The ID of the user you will attribute object view(s) to
		 *    postID(int): The ID of the specifci post you want to attribute the view to
		 *    batch(array,optional): Used to update multiple objects. Array format must look
		 *    similar to this:
		 *        array( (1,204,0,0,1),(1,205,0,0,1),(1,206,0,0,1) );  
		 *    This array adds a view to posts with the ID of 204, 205 and 206 for user 1.
		 */
		function viewed_object ($userID,$postID,$batch) {
		  if ( is_user_logged_in() ) {
		    global $wpdb;
		    $user_ID = $userID;
		    $currentObjectID = $postID;
		    
		    // Handle "Multiple" Values
		    if ( $batch ) {
		      $values = $batch;
		    } else { // Handle "Singlular" Values
		      $values = array('('.$user_ID.','.$currentObjectID.',0,0,1)'); 
		    }

		    $wpdb->query(
		    "
		    INSERT INTO wp_user_interactions
		    (user_id, post_id, times_correct, times_wrong, times_viewed)
		    VALUES ".implode(',',$values)."
		    ON DUPLICATE KEY UPDATE times_correct=times_correct+VALUES(times_correct), times_wrong=times_wrong+VALUES(times_wrong), times_viewed=times_viewed+VALUES(times_viewed)
		    ");
		  }
		}

		/*
		 * Update user interactions to reflect that a user has "viewed" a singular object
		 * NOTE: If user refreshes page, that will also count as a view
		 */
		$current_user = wp_get_current_user();
		$user_ID = $current_user->ID;
		$currentObjectID = $post->ID;
		viewed_object($user_ID, $currentObjectID,false);

		/*
		 * "Display all Modules associated with this particular Unit along with any associated lessons under each object."
		 */
		// MODULES
		$unitHasModules = p2p_connection_exists( 'modules_to_units', array('to'=> get_queried_object()) );
		
		// If this unit contains modules...
		if ( $unitHasModules ) {

			// Retrieve all modules associated with this unit.
			$modules = new WP_Query(array(
				'connected_type' => 'modules_to_units',
				'connected_items' => get_queried_object(),
				'nopaging' => true,
			));

			// Store all modules IDs in an array for use in the DB.
			$moduleIDs = array();
			while ( $modules->have_posts() ) : $modules->the_post();
				$moduleIDs[] = $post->ID;
			endwhile;
			rewind_posts();
			
			// If user is logged in...
			if ( is_user_logged_in() ) {
			 	$current_user = wp_get_current_user();
			 	$user_ID = $current_user->ID;
			 	$post_ids = implode(',',$moduleIDs);
				$post_ids_safe = mysql_real_escape_string($post_ids); // Just because I like being
			 	
			 	// Check if vocabulary have been completed (using the data later that's why)
			 	$modulesCompleted = $wpdb->get_results($wpdb->prepare(
			 		"
			 		SELECT times_completed
			 		FROM wp_user_interactions
					WHERE user_id = %d
						AND post_id IN (".$post_ids_safe.")
					LIMIT 0, 10
					"
					, $user_ID
				));

				// Checking to see if the total amount of published Modules equal the amount
				// of modules the user has seen or completed. If not, let's add some blank
				// entries for them.
				if ( count( $moduleIDs ) != count( $modulesCompleted ) ) {
					$values = array();
					$placeHolders = array();

					while ($modules->have_posts()) : $modules->the_post();
						$values[] = $user_ID.',';
						$values[] = $post->ID.',';
						$values[] = '0';
						$placeHolders[] = '(%d, %d, %d)';
					endwhile;
					rewind_posts();

					$placeHolderCreate = implode(', ', $placeHolders);

					// Insert records for the user
					$wpdb->query( $wpdb->prepare("
						INSERT IGNORE INTO wp_user_interactions
						( user_id, post_id, times_completed )
						VALUES ".$placeHolderCreate."
					", $values ));
				}
				rewind_posts();
			} // is_user_logged_in

			/*
			 * Display all modules and their associated lesson types here...
			 */
			echo '<ul class="unit-selections">';
			$indexCount = 0;

			while ( $modules->have_posts() ) : $modules->the_post();
				$postID = $post->ID;
				echo '<li>';
				echo 	'<a class="modules unit-selection" href="'.get_permalink().'" title="Go to the'.get_the_title().' activity"';
				if ( $modulesCompleted ) {
					if ( $modulesCompleted[$indexCount]->times_completed == 0 ) {
						echo 'data-complete="0"';
					} else {
						echo 'data-complete="1"';
					}
				}
				echo 	'>';
				echo 		'<div class="unit-selection-info"><h3>'.get_the_title().'</h3></div>';
				echo 	'</a>';

				/*
				 * Display connected content (i.e. Lesson Types)
				 * NOTE: If you can get each_connected to work, please use that instead.
				 */

				// VOCABULARY LESSONS
				$vocabLessons = new WP_Query(array(
					'connected_type' => 'vocabulary_lessons_to_modules',
					'connected_items' => $postID,
					'nopaging' => true,
					'orderby' => 'menu_order',
					'order' => 'ASC',
				));
				if ( $vocabLessons->have_posts() ) {
					echo '<h4>Vocabulary Lessons</h4>';
					while( $vocabLessons->have_posts() ) : $vocabLessons->the_post();
						echo get_the_title();
					endwhile;
					wp_reset_postdata();
				}

				// PHRASES LESSONS
				$phrasesLessons = new WP_Query(array(
					'connected_type' => 'phrases_lessons_to_modules',
					'connected_items' => $postID,
					'nopaging' => true,
					'orderby' => 'menu_order',
					'order' => 'ASC',
				));
				if ( $vocabLessons->have_posts() ) {
					echo '<h4>Phrases Lessons</h4>';
					while( $phrasesLessons->have_posts() ) : $phrasesLessons->the_post();
						echo get_the_title();
					endwhile;
					wp_reset_postdata();
				}

				// CHANTS LESSONS
				$chantsLessons = new WP_Query(array(
					'connected_type' => 'chants_lessons_to_modules',
					'connected_items' => $postID,
					'nopaging' => true,
					'orderby' => 'menu_order',
					'order' => 'ASC',
				));
				if ( $vocabLessons->have_posts() ) {
					echo '<h4>Chants Lessons</h4>';
					while( $chantsLessons->have_posts() ) : $chantsLessons->the_post();
						echo get_the_title();
					endwhile;
					wp_reset_postdata();
				}

				echo '</li>';
				$indexCount++;
			endwhile;
			echo '</ul>';
			wp_reset_postdata();

		} // MODULES ?>

		<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', '_s' ), 'after' => '</div>' ) ); ?>
	</div><!-- .entry-content -->

	<?php bedrock_postcontentend(); ?>

</article><!-- #post-<?php the_ID(); ?> -->