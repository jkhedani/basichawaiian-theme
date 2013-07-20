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
$walletBalance = get_wallet_balance($post->ID);

?>

<article
	id="post-<?php the_ID(); ?>" 
	<?php post_class(); ?> 
	data-post-id="<?php echo $post->ID; ?>" 
	data-viewed="<?php echo is_first_object_visit( $post->ID ); ?>"
	data-complete="<?php echo is_object_complete( $post->ID ) ? "1" : "0"; ?>">

	<?php bedrock_postcontentstart(); ?>

	<header class="entry-header">

		<div class="wallet-balance span4 pull-right">
			<?php if ( $walletBalance > 1 ) { echo '<a class="btn btn-small pull-right claim-kukui" href="javascript:void(0);">Claim a kukui</a>'; } ?>
			<p class="pull-right">Flowers: <strong><?php echo !empty($walletBalance) ? $walletBalance : "0"; ?></strong></p>
		</div>
		
		<a class="btn btn-back" href="<?php echo $previousPageURL; ?>"><i class="icon-arrow-left" style="padding-right:10px;"></i>Back to Unit View</a>
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
			wp_reset_postdata();
			
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
					$moduleID = $post->ID;
					create_object_record( $moduleID ); // May be redundant but for first time visitors, this prevents missing records errors.
				?>

				<li class="module span12 item <?php if ( $indexCount == 0 ) echo 'active'; ?>" data-complete="<?php echo is_object_complete( $moduleID ) ? "1" : "0"; ?>">
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