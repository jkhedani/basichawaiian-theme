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
$sceneID = check_scene_progress( $post->ID );

?>

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

	<!-- Left Sidebar -->
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

	<!-- Right Content Area -->
	<div class="entry-content">
		<div class="helper-text">Weeks with <?php echo get_the_title(); ?></div>
		<?php
		/*
		 * DISPLAY MODULES
		 * "Display all Modules associated with this particular Unit along with any associated lessons under each object."
		 */
		$unitHasModules = p2p_connection_exists( 'modules_to_units', array('to'=> get_queried_object()) );
		if ( $unitHasModules ) { // If this unit contains modules...

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
			$carouselID = 'module-list';
			?>

			<div id="<?php echo $carouselID; ?>" class="carousel slide">
				<ol class="carousel-indicators">
					<li><a class="carousel-indicator-control left" href="javascript:void(0);">&lsaquo;</a></li>
					<?php for ( $i = 0; $i < $moduleCount; $i++ ) { ?>
						<?php if ( $i == 0 ) { ?>
						<li data-target="#<?php echo $carouselID; ?>" data-slide-to="<?php echo $i; ?>" class="first active"><?php echo $i + 1; ?></li>
	    			<?php } elseif ( $i == $moduleCount - 1 ) { ?>
	    			<li data-target="#<?php echo $carouselID; ?>" data-slide-to="<?php echo $i; ?>" class="last"><?php echo $i + 1; ?></li>
	    			<?php } else { ?>
	    			<li data-target="#<?php echo $carouselID; ?>" data-slide-to="<?php echo $i; ?>"><?php echo $i + 1; ?></li>
	    			<?php } ?>
	    		<?php } ?>
	    		<li><a class="carousel-indicator-control right" href="javascript:void(0);">&rsaquo;</a></li>
	    	</ol>

				<!-- MODULES -->
				<ul class="modules carousel-inner">

				<?php
					$indexCount = 0;
					while ( $modules->have_posts() ) : $modules->the_post();
						$moduleID = $post->ID;
						create_object_record( $moduleID ); // May be redundant but for first time visitors, this prevents missing records errors.
				?>

					<!-- MODULE -->
					<li class="module item <?php if ( $indexCount == 0 ) echo 'active'; ?>" data-slide-postion="<?php echo $indexCount; ?>" data-complete="<?php echo is_object_complete( $moduleID ) ? "1" : "0"; ?>">
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

						<!-- TOPICS -->
						<ul class="topics">
						<?php
							while( $topics->have_posts() ) : $topics->the_post();
								$topicID = $post->ID;
								create_object_record( $topicID ); // May be redundant but for first time visitors, this prevents missing records errors.
								switch ( $topicNumber ) {
									case '1': // Basics/Cultural Foundations
										$postTypes = array( 'instruction_lessons', 'video_lessons', 'phrases_lessons' );
										$connectedTypes = array( 'instructional_lessons_to_topics', 'video_lessons_to_topics', 'phrases_lessons_to_topics' );
										$topicTitleEng = 'Cultural Foundations';
										break;
									case '2': // Vocabulary
										$postTypes = array( 'instruction_lessons', 'vocabulary_lessons', 'video_lessons' );
										$connectedTypes = array( 'instructional_lessons_to_topics', 'vocabulary_lessons_to_topics','video_lessons_to_topics' );
										$topicTitleEng = 'Vocabulary';
										break;
									case '3': // Lessons
										$postTypes = array( 'instruction_lessons', 'video_lessons' );
										$connectedTypes = array( 'instructional_lessons_to_topics', 'video_lessons_to_topics' );
										$topicTitleEng = 'Lessons';
										break;
									case '4': // Exercises
										$postTypes = array( 'instruction_lessons', 'phrases_lessons', 'video_lessons' );
										$connectedTypes = array( 'instructional_lessons_to_topics', 'phrases_lessons_to_topics', 'video_lessons_to_topics' );
										$topicTitleEng = 'Exercises';
										break;
									case '5': // Proverbs
										$postTypes = array( 'instruction_lessons', 'video_lessons' );
										$connectedTypes = array( 'instructional_lessons_to_topics', 'phrases_lessons_to_topics', 'video_lessons_to_topics' );
										$topicTitleEng = 'Proverbs';
										break;
									case '6': // Songs
										$postTypes = array( 'instruction_lessons', 'song_lessons', 'video_lessons' );
										$connectedTypes = array( 'instructional_lessons_to_topics', 'song_lessons_to_topics', 'video_lessons_to_topics' );
										$topicTitleEng = 'Songs';
										break;
									case '7': // Chants
										$postTypes = array( 'instruction_lessons', 'chants_lessons', 'video_lessons' );
										$connectedTypes = array( 'instructional_lessons_to_topics', 'chants_lessons_to_topics', 'video_lessons_to_topics' );
										$topicTitleEng = 'Chants';
										break;
									case '8': // Readings
										$postTypes = array( 'instruction_lessons', 'readings', 'video_lessons' );
										$connectedTypes = array( 'instructional_lessons_to_topics', 'readings_to_topics', 'video_lessons_to_topics' );
										$topicTitleEng = 'Readings';
										break;
									case '9': // Group Activities
										$postTypes = array( 'instruction_lessons', 'video_lessons' );
										$connectedTypes = array( 'instructional_lessons_to_topics', 'video_lessons_to_topics' );
										$topicTitleEng = 'Family Lessons';
										break;
								}
								//Display lessons associated with topic
								$lessons = new WP_Query( array(
									'post_type' => $postTypes,
									'suppress_filters' => false,
									'connected_type' => $connectedTypes,
									'connected_items' => $topicID,
									'order' => 'ASC',
									'orderby' => 'menu_order',
									'nopaging' => true,
								));

								?>

								<?php if ( $lessons->have_posts() ) : ?>
								<!-- TOPIC -->
								<h3 class="topic-title topic-title-hwn"><?php echo get_the_title(); ?></h3>
								<h4 class="topic-title topic-title-eng topic-spacer"><?php echo $topicTitleEng; ?></h4>
<!-- 							<li
								class="topic"
								data-topic-id="<?php //echo $topicID; ?>"
								data-complete="<?php //echo is_topic_complete( $topicID ) ? "1" : "0"; ?>"
								data-exercise-complete="<?php //echo scene_viewed( $topicID ) ? "1" : "0"; ?>"
							> -->

								<!-- LESSONS -->
								<ul class="lessons">

									<?php while ( $lessons->have_posts() ) : $lessons->the_post();



									// Create special display for video content type.
									if ( get_post_type( $post->ID ) === 'video_lessons' ) : ?>

									<!-- VIDEO LESSON -->
									<li class="lesson">
											<span class="supertitle"><?php echo get_the_title($topicID); ?></span>
											<h3><?php echo get_the_title(); ?></h3>
											<?php if ( is_object_complete( $post->ID ) ) { ?>
												<div class="lesson-point earned"></div>
											<?php } else { ?>
												<div class="lesson-point unearned"></div>
											<?php } ?>
											<a class="prompt-lesson-start start btn btn-cta blue" href="#lesson-start-modal" data-lesson-title="<?php echo get_the_title(); ?>" data-lesson-url="<?php echo get_permalink($post->ID); ?>" data-module-number="<?php echo $indexCount; ?>" data-post-id="<?php echo $post->ID; ?>">Start</a>
										</a>
									</li>

									<?php else : ?>

									<!-- LESSON -->
									<li class="lesson">
											<span class="supertitle"><?php echo get_the_title($topicID); ?></span>
											<h3><?php echo get_the_title(); ?></h3>
											<?php if ( is_object_complete( $post->ID ) ) { ?>
												<div class="lesson-point earned"></div>
											<?php } else { ?>
												<div class="lesson-point unearned"></div>
											<?php } ?>
											<a class="prompt-lesson-start start btn btn-cta blue" href="#lesson-start-modal" data-lesson-title="<?php echo get_the_title(); ?>" data-lesson-url="<?php echo get_permalink($post->ID); ?>" data-module-number="<?php echo $indexCount; ?>" data-post-id="<?php echo $post->ID; ?>">Start</a>
										</a>
									</li>

									<?php endif; // special post display conditional ?>

									<?php endwhile; ?>
									<?php wp_reset_postdata(); ?>
								</ul><!-- ul.lessons -->
							<?php endif; // if lessons ?>
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
			</li><!-- .module -->
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
    <a class="btn btn-primary start-lesson" href="javascript:void(0);" data-lesson-type="lecture" data-connected-to-id="<?php echo $post->ID; ?>" data-carousel-position="0"><?php echo __('&#8216;Ae'); ?></a>
		<a class="btn btn-primary abort-lesson" href="javascript:void(0);"><?php echo __('&#8216;A&#8216;ole'); ?></a>
  </div>
</div>
