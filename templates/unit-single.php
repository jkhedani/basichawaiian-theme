<?php
/**
 *
 * Unit Page (Kukuis)
 *
 * @package _s
 * @since _s 1.0
 */

// "Previous" page (doesn't work very well if user is logged in and edits a page...)
$previousPageURL = htmlspecialchars($_SERVER['HTTP_REFERER']);

/**
 * Check users interaction status with object. 
 * Usage: Helps determine how stories/scenes are displayed and how to handle interaction values
 * Requires: user-interactions-functions.php
 */

$objectInteractionStatus = get_object_record( $post->ID );
$objectViewed = 0; // Assume current object hasn't been viewed.
$objectComplete = 0; // Assume current object hasn't been completed.

/* 
 * Is this the first time a user is visiting this page?
 */
if ( $objectInteractionStatus[0]->times_viewed > 0 ) : // If not...
	$objectViewed = 1; // Mark data object "viewed" as viewed.
else :	// If so...
	// Use javascript to send for modal result is in scene-scripts.js
	$objectViewed = 0; // Mark data object as not viewed for js	
endif;

/*
 * Did the user complete this object/page?
 */
if ( $objectInteractionStatus[0]->times_completed == 0 ) :
	$objectComplete = 0;
else :
	$objectComplete = 1;
endif;

// View should be incremented regardless of conditions above. I promise.
increment_object_value ( $post->ID, 'times_viewed' );

?>

<article
	id="post-<?php the_ID(); ?>" 
	<?php post_class(); ?> 
	data-postid="<?php echo $post->ID; ?>" 
	data-viewed="<?php echo $objectViewed ?>"
	data-complete="<?php echo $objectComplete ?>">

	<?php bedrock_postcontentstart(); ?>

	<header class="entry-header">

		<?php bedrock_abovetitle(); ?>
		
		<a class="btn btn-back" href="<?php echo get_home_URL(); ?>"><i class="icon-arrow-left" style="padding-right:10px;"></i>Back to Unit View</a>
		<h1 class="entry-title"><?php the_title(); ?></h1>
		
		<?php bedrock_belowtitle(); ?>
		
		<hr />

		<div class="entry-meta">
			<?php //_s_posted_on(); ?>
		</div><!-- .entry-meta -->
	</header><!-- .entry-header -->

	<div class="entry-content row">
		<?php

		the_content();

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
				'nopaging' => true,
				'orderby' => 'menu_order',
			));

			// Store all modules IDs in an array for use in the DB.
			$moduleIDs = array();
			while ( $modules->have_posts() ) : $modules->the_post();
				$moduleIDs[] = $post->ID;
			endwhile;
			
			// Create fresh object records if they do not have any for this page.
			create_object_record( $moduleIDs );

			/*
			 * Display all modules and their associated lesson types here...
			 */
			$carouselID = 'module-list'; ?>

			<div id="<?php echo $carouselID; ?>" class="span12 carousel slide">
				<ol class="carousel-indicators row">
					<li data-target="#<?php echo $carouselID; ?>" data-slide-to="0" class="active"></li>
	    		<li data-target="#<?php echo $carouselID; ?>" data-slide-to="1"></li>
	    	</ol>

				<ul class="modules carousel-inner row">
			
				<?php
				$indexCount = 0;
				while ( $modules->have_posts() ) : $modules->the_post();
				$moduleID = $post->ID; ?>

				<li class="module span12 item <?php if ( $indexCount == 0 ) echo 'active'; ?>" <?php if ( is_object_complete( $post->ID ) ) { echo 'data-complete="1"'; } else { echo 'data-complete="0"'; } ?>>
					<h3 class="module-title"><?php the_title(); ?></h3>

				<?php
					// Connected Modules
					$lessons = new WP_Query( array(
						'connected_type' => 'topics_to_modules',
						'connected_items' => $moduleID,
						'nopaging' => true
					));
					if ( $lessons->have_posts() ) :
						echo '<ul class="topics row">';
						while( $lessons->have_posts() ) : $lessons->the_post();
							$topicID = $post->ID;
							echo '<li class="topic span4 pull-left">';
							echo 	'<a href="'.get_permalink().'"><h4>' . get_the_title() . '</h4></a>';
							echo '</li>';
						endwhile;
						wp_reset_postdata();
						echo '</ul>'; // .topics
					endif;
				?>
				</li>
				<?php $indexCount++; ?>
				<?php endwhile; ?>
			</ul>
			<?php wp_reset_postdata(); ?>
			<a class="carousel-control left" href="#<?php echo $carouselID ?>" data-slide="prev">&lsaquo;</a>
			<a class="carousel-control right" href="#<?php echo $carouselID ?>" data-slide="next">&rsaquo;</a>
			</div><!-- #moduleList -->
		
		<?php } // MODULES ?>



		<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', '_s' ), 'after' => '</div>' ) ); ?>
	</div><!-- .entry-content -->

	<?php bedrock_postcontentend(); ?>

</article><!-- #post-<?php the_ID(); ?> -->