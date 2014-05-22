<?php
/**
 *
 * Unit Page (Kukuis)
 *
 * @package _s
 * @since _s 1.0
 */

// Reflect a view for user on this object (object created if it doesn't already exist)
increment_object_value ( $post->ID, 'times_viewed' );

$previousPageURL = get_home_URL();

?>

<?php $sceneID = check_scene_progress( $post->ID ); ?>

<article
	id="post-<?php the_ID(); ?>" 
	<?php post_class(); ?> 
	data-post-id="<?php echo $post->ID; ?>" 
	data-viewed="<?php echo is_first_object_visit( $post->ID ); ?>"
	data-complete="<?php echo is_object_complete( $post->ID ) ? "1" : "0"; ?>"
	data-assoc-scene="<?php echo $sceneID ?>"
	data-scene-viewed="<?php echo scene_viewed( $sceneID ); ?>">

	<header class="entry-header">

		<!-- <a class="btn btn-inverse btn-back" href=""><i class="icon-arrow-left icon-white" style="padding-right:10px;"></i>Back to Unit View</a> -->
	</header><!-- .entry-header -->

	<div class="unit-banner">
		<h1><?php echo get_the_title(); ?></h1>
		<?php if ( get_field('unit_location') ) : ?>
		<h2><?php echo get_field('unit_location'); ?></h2>
		<?php endif; ?>
		<!-- User Avatar -->
		<?php 
			$user = wp_get_current_user();
  		$user_id = $user->ID;
			$gender = get_user_meta( $user_id, 'gender', true );
		?>
		<div class="user-avatar <?php echo $gender; ?> default"></div>
	</div>

	<div class="entry-content">
		<div class="helper-text">Weeks with <?php echo get_the_title(); ?></div>
		<?php

		/*
		 * "Display all Modules associated with this particular Unit along with any associated lessons under each object."
		 */
		$unitHasModules = p2p_connection_exists( 'modules_to_units', array('to'=> get_queried_object()) );

		// If this unit contains modules...
		if ( $unitHasModules ) {

			// Retrieve all modules associated with this unit.
			$modules = new WP_Query( array(
				'connected_type' => 'modules_to_units',
				'connected_items' => get_queried_object(),
				'orderby' => 'menu_order',
				'order' => 'ASC',
				'nopaging' => true,
			));
			$moduleCount = $modules->post_count;

			// Store all modules IDs in an array for use in the DB.
			$moduleIDs = array();
			while ( $modules->have_posts() ) : $modules->the_post();
				$moduleIDs[] = $post->ID;
			endwhile;
			wp_reset_postdata();
			
			/*
			 * Display all modules and their associated lesson types here...
			 */
			$carouselID = 'module-list'; ?>

			<div id="<?php echo $carouselID; ?>" class="carousel slide">
				<ol class="carousel-indicators">
					<li><a class="carousel-indicator-control left" href="#<?php echo $carouselID ?>" data-slide="prev">&lsaquo;</a></li>
					<?php for ( $i = 0; $i < $moduleCount; $i++ ) { ?>
						<?php if ( $i == 0 ) { ?>
						<li data-target="#<?php echo $carouselID; ?>" data-slide-to="0" class="first active"><?php echo $i + 1; ?></li>
	    			<?php } elseif ( $i == $moduleCount - 1 ) { ?>
	    			<li data-target="#<?php echo $carouselID; ?>" data-slide-to="0" class="last"><?php echo $i + 1; ?></li>
	    			<?php } else { ?>
	    			<li data-target="#<?php echo $carouselID; ?>" data-slide-to="1"><?php echo $i + 1; ?></li>
	    			<?php } ?>
	    		<?php } ?>
	    		<li><a class="carousel-indicator-control right" href="#<?php echo $carouselID ?>" data-slide="next">&rsaquo;</a></li>
	    	</ol>

	    	
				

				<ul class="modules carousel-inner">
			
				<?php
				$indexCount = 0;
				while ( $modules->have_posts() ) : $modules->the_post();
					$moduleID = $post->ID;
					create_object_record( $moduleID ); // May be redundant but for first time visitors, this prevents missing records errors.
				?>

				<li class="module item <?php if ( $indexCount == 0 ) echo 'active'; ?>" data-complete="<?php echo is_object_complete( $moduleID ) ? "1" : "0"; ?>">
					<h2 class="module-title"><?php //the_title(); ?></h2>

				<?php
					// Connected Modules
					$topicNumber = 1;
					$topics = new WP_Query( array(
						'connected_type' => 'topics_to_modules',
						'connected_items' => $moduleID,
						'nopaging' => true,
					));

					if ( $topics->have_posts() ) : ?>

					<ul class="topics">
					<?php while( $topics->have_posts() ) : $topics->the_post();
							$topicID = $post->ID;
							create_object_record( $topicID ); // May be redundant but for first time visitors, this prevents missing records errors.
							?>
							<h3 class="topic-spacer"><?php echo get_the_title(); ?></h3>
<!-- 							<li 
								class="topic" 
								data-topic-id="<?php //echo $topicID; ?>" 
								data-complete="<?php //echo is_topic_complete( $topicID ) ? "1" : "0"; ?>"
								data-exercise-complete="<?php //echo scene_viewed( $topicID ) ? "1" : "0"; ?>"
							> -->
							<ul class="lessons">

							<?php
								switch ( $topicNumber ) {
									case '1': // Vocabulary
										$postTypes = array( 'instruction_lessons', 'vocabulary_lessons' );
										$connectedTypes = array( 'instructional_lessons_to_topics', 'vocabulary_lessons_to_topics' );
										break;
									case '2': // Basics
										$postTypes = array( 'instruction_lessons', 'phrases_lessons' );
										$connectedTypes = array( 'instructional_lessons_to_topics', 'phrases_lessons_to_topics' );
										break;
									case '3': // Phrases
										$postTypes = array( 'instruction_lessons', 'phrases_lessons' );
										$connectedTypes = array( 'instructional_lessons_to_topics', 'phrases_lessons_to_topics' );
										break;
									case '4': // Proverbs
										$postTypes = array( 'instruction_lessons' );
										$connectedTypes = array( 'instructional_lessons_to_topics' );
										break;
									case '5': // Songs
										$postTypes = array( 'instruction_lessons', 'song_lessons' );
										$connectedTypes = array( 'instructional_lessons_to_topics', 'song_lessons_to_topics' );
										break;
									case '6': // Chants
										$postTypes = array( 'instruction_lessons', 'chant_lessons' );
										$connectedTypes = array( 'instructional_lessons_to_topics', 'chant_lessons_to_topics' );
										break;
									case '7': // Readings
										$postTypes = array( 'instruction_lessons', 'readings' );
										$connectedTypes = array( 'instructional_lessons_to_topics', 'readings_to_topics' );
										break;
									case '8': // Ohana Learning Activities
										$postTypes = array( 'instruction_lessons', 'vocabulary_lessons' );
										$connectedTypes = array( 'instructional_lessons_to_topics', 'vocabulary_lessons_to_topics' );
										break;
								}
								//Display lessons associated with topic
								$lessons = new WP_Query( array(
								  'post_type' => $postTypes,
								  'suppress_filters' => false,
								  'connected_type' => $connectedTypes,
								  'connected_items' => $topicID,
								));
								while ( $lessons->have_posts() ) : $lessons->the_post(); ?>
								<li class="lesson">
										<span class="supertitle"><?php echo get_the_title($topicID); ?></span>
										<h3><?php echo get_the_title(); ?></h3>
										<?php if ( is_object_complete( $post->ID ) ) { ?>
											<div class="lesson-point earned"></div>
										<?php } else { ?>
											<div class="lesson-point unearned"></div>
										<?php } ?>
										
										<a class="prompt-lesson-start start btn btn-cta blue" href="#lesson-start-modal" data-lesson-title="<?php echo get_the_title(); ?>" data-lesson-url="<?php echo get_permalink($post->ID); ?>">Start</a>
									</a>
								</li>
								<?php
								endwhile;
								wp_reset_postdata(); ?>
							</ul>

							<?php 
							
							//echo '</li>'; // .topic
							$topicNumber++;
						endwhile; // topics
						wp_reset_postdata();
						echo '</ul>'; // .topics
					else:
						// If there are no topics in a module
						echo '<h3 class="no-content">Content Coming Soon</h3>';
					endif;
				?>
				</li>
				<?php $indexCount++; ?>
				<?php endwhile; ?>

			</ul>
			<?php wp_reset_postdata(); ?>

			</div><!-- #moduleList -->
		
		<?php } // MODULES ?>

	</div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->

<div id="lesson-start-modal" class="modal <?php echo $post->post_name; ?> hide fade">
  <!-- <div class="modal-header"></div> -->
  <div class="modal-body">
  	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h2><?php echo __('M&#257;kaukau?'); ?></h2>
    <h3>Start your lesson</h3>
  </div>
  <div class="modal-footer">
    <a class="btn btn-primary start-lesson" href="javascript:void(0);" data-lesson-type="lecture" data-connected-to-id="<?php echo $post->ID; ?>"><?php echo __('&#8216;Ae'); ?></a>
		<a class="btn btn-primary abort-lesson" href="javascript:void(0);"><?php echo __('&#8216;A&#8216;ole'); ?></a>
  </div>
</div>