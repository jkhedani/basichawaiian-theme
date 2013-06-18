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
		 * "Display all Modules associated with this particular Unit along with any associated lessons under each object."
		 */
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
			
			// Create fresh object records if they do not have any for this page.
			create_object_record( $moduleIDs );

			/*
		 	 * Update user interactions to reflect that a user has "viewed" a singular object
		   * NOTE: If user refreshes page, that will also count as a view
		   */
			increment_object_value ( $post->ID, 'times_viewed' );

			/*
			 * Display all modules and their associated lesson types here...
			 */
			$carouselID = 'moduleList';

			echo '<div id="'.$carouselID.'" class="carousel slide">';
			echo 	'<ol class="carousel-indicators">';
			echo 		'<li data-target="#'.$carouselID.'" data-slide-to="0" class="active"></li>';
    	echo 		'<li data-target="#'.$carouselID.'" data-slide-to="1"></li>';
    	echo 	'</ol>';

			echo '<ul class="carousel-inner">';
			$indexCount = 0;

			while ( $modules->have_posts() ) : $modules->the_post();
				$postID = $post->ID;
				echo '<li class="item '; // yes, there's a space.
				if ( $indexCount == 0 )
					echo 'active';
				echo '">';
				echo 	'<a class="modules unit-selection" href="'.get_permalink().'" title="Go to the'.get_the_title().' activity"';
				if ( is_object_complete( $post->ID ) ) {
					echo 'data-complete="1"';
				} else {
					echo 'data-complete="0"';
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
			echo '<a class="carousel-control left" href="#'.$carouselID.'" data-slide="prev">&lsaquo;</a>';
			echo '<a class="carousel-control right" href="#'.$carouselID.'" data-slide="next">&rsaquo;</a>';
			echo '</div>'; // #moduleList

		} // MODULES ?>



		<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', '_s' ), 'after' => '</div>' ) ); ?>
	</div><!-- .entry-content -->

	<?php bedrock_postcontentend(); ?>

</article><!-- #post-<?php the_ID(); ?> -->