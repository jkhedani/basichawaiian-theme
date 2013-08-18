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

<article
	id="post-<?php the_ID(); ?>" 
	<?php post_class(); ?> 
	data-post-id="<?php echo $post->ID; ?>" 
	data-viewed="<?php echo is_first_object_visit( $post->ID ); ?>"
	data-complete="<?php echo is_object_complete( $post->ID ) ? "1" : "0"; ?>">

	<?php bedrock_postcontentstart(); ?>

	<header class="entry-header">
		<h1 class="entry-title"><?php echo get_the_title(); ?></h1>
		<a class="btn btn-inverse btn-back" href="<?php echo $previousPageURL; ?>"><i class="icon-arrow-left icon-white" style="padding-right:10px;"></i>Back to Unit View</a>
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
					<?php for ( $i = 0; $i < $moduleCount; $i++ ) { ?>
						<?php if ( $i == 0 ) { ?>
						<li data-target="#<?php echo $carouselID; ?>" data-slide-to="0" class="first active"></li>
	    			<?php } elseif ( $i == $moduleCount - 1 ) { ?>
	    			<li data-target="#<?php echo $carouselID; ?>" data-slide-to="0" class="last"></li>
	    			<?php } else { ?>
	    			<li data-target="#<?php echo $carouselID; ?>" data-slide-to="1"></li>
	    			<?php } ?>
	    		<?php } ?>
	    	</ol>

				<ul class="modules carousel-inner">
			
				<?php
				$indexCount = 0;
				while ( $modules->have_posts() ) : $modules->the_post();
					$moduleID = $post->ID;
					create_object_record( $moduleID ); // May be redundant but for first time visitors, this prevents missing records errors.
				?>

				<li class="module item <?php if ( $indexCount == 0 ) echo 'active'; ?>" data-complete="<?php echo is_object_complete( $moduleID ) ? "1" : "0"; ?>">
					<h2 class="module-title"><?php the_title(); ?></h2>

				<?php
					// Connected Modules
					$lessons = new WP_Query( array(
						'connected_type' => 'topics_to_modules',
						'connected_items' => $moduleID,
						'nopaging' => true,
					));
					if ( $lessons->have_posts() ) :
						echo '<ul class="topics row">';
						while( $lessons->have_posts() ) : $lessons->the_post();
							$topicID = $post->ID;
							create_object_record( $topicID ); // May be redundant but for first time visitors, this prevents missing records errors.
							?>
							<li 
								class="topic span4 pull-left" 
								data-topic-id="<?php echo $topicID; ?>" 
								data-complete="<?php echo is_topic_complete( $topicID ) ? "1" : "0"; ?>"
								data-exercise-complete="<?php echo scene_viewed( $topicID ) ? "1" : "0"; ?>"
							>
							<?php
							echo 	'<a href="'.get_permalink($topicID).'"><h4>' . get_the_title($topicID) . '</h4></a>';
							echo '</li>';
						endwhile;
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
			<a class="carousel-control left" href="#<?php echo $carouselID ?>" data-slide="prev">&lsaquo;</a>
			<a class="carousel-control right" href="#<?php echo $carouselID ?>" data-slide="next">&rsaquo;</a>
			</div><!-- #moduleList -->
		
		<?php } // MODULES ?>

	</div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->