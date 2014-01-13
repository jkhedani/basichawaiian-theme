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

	<?php if ( ! is_user_logged_in() ) { ?>
		
		<div id="primary" class="content-area">
			<div id="content" class="site-content row" role="main">
				
				<!-- Hero -->
				<div class="hero span12">
					<div class="padded">
						<div class="aunty-aloha-hero"></div>
						<?php while ( have_posts() ) : the_post(); ?>
						<?php echo get_the_content();?>
						<?php endwhile; ?>
						<?php
							echo smlsubform( array( // http://wordpress.org/plugins/mail-subscribe-list/
								'emailtxt' 	=> 'We are still in beta! Sign up to hear the latest:',
								'submittxt' => 'Subscribe',
							));
						?>
					</div>
				</div><!-- .hero -->
				<!-- Development Screenshots -->
				<div class="development-screenshots span12">
					<div class="padded">
						<h2>Development Screenshots</h2>
						<a class="view-more" href="<?php echo get_home_url(); ?>/2013?archive-type=screenshots">View All Screenshots<i class="icon-chevron-right icon-white"></i></a>
						<?php $idObj = get_category_by_slug('screenshots'); $id = $idObj->term_id; ?>
						<?php $latestScreenshotPosts = new WP_Query( array( 'post_type' => 'post', 'cat' => $id, 'posts_per_page' => 3 )); ?>
						<ul class="three-up">
						<?php while ( $latestScreenshotPosts->have_posts() ) : $latestScreenshotPosts->the_post(); ?>

							<?php $latestScreenshots = new WP_Query( array( 'post_type' => 'attachment', 'post_status' => 'any', 'post_parent' => $post->ID  )); ?>
							<?php while ( $latestScreenshots->have_posts() ) : $latestScreenshots->the_post(); ?>
							<?php if ( $latestScreenshots->current_post < 3 ) : ?>
							<?php $latestScreenshot = wp_get_attachment_image_src( $post->ID, 'large' ); ?> 
							<li>
								<a class="wrapper" href="<?php echo the_permalink(); ?>" target="_blank">
									<img src="<?php echo $latestScreenshot[0]; ?>" />
								</a>
							</li>
							<?php endif; ?>
							<?php endwhile; ?>
							<?php wp_reset_postdata(); ?>

						<?php endwhile; ?>
						<?php wp_reset_postdata(); ?>
						</ul>
					</div>
				</div><!-- dev screenshots -->

				<!-- Latest Updates (posts) -->
				<div class="latest-updates span12">
					<div class="padded">
						<h2>Latest Updates</h2>
						<a class="view-more" href="<?php echo get_home_url(); ?>/2013">View All Posts<i class="icon-chevron-right icon-white"></i></a>
						<?php
							$idObj = get_category_by_slug('screenshots');
							$id = $idObj->term_id;
							$latestPosts = new WP_Query( array(
								'post_type' => 'post',
								'category__not_in' => $id,
								'posts_per_page' => 3
							));
						?>
						<ul class="three-up">
							<?php while ( $latestPosts->have_posts() ) : $latestPosts->the_post(); ?>
								<li>
									<span class="post-date"><?php echo get_the_date('F j, Y'); ?></span>
									<h3><a href="<?php echo the_permalink(); ?>"><?php echo get_the_title(); ?></a></h3>
									<div><?php echo the_excerpt_max_charlength(100); ?></div>
									<a class="btn btn-primary read-more" href="<?php echo the_permalink(); ?>" target="_blank">Read More</a>
								</li>
							<?php endwhile; ?>
							<?php wp_reset_postdata(); ?>
						</ul>


					</div>
				</div>

	<?php } else { ?>

	<!-- Learner's Dashboard -->

	<?php $sceneID = check_scene_progress( $post->ID ); ?>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main"
			data-post-id="<?php echo $post->ID; ?>" 
			data-viewed="<?php echo is_first_object_visit( $post->ID ); ?>"
			data-complete="<?php echo is_object_complete( $post->ID ) ? "1" : "0"; ?>"
			data-assoc-scene="<?php echo $sceneID; ?>"
			data-scene-viewed="<?php echo scene_viewed( $sceneID ); ?>">


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
					$popoverContent = "<h1>&#8216;Anak&#275; Aloha</h1><i>Topics in this module: </i><ul><li>Introductions</li><li>Greetings</li><li>Family</li><li>Gardening</li><li>Food</li></ul><a class='btn btn-primary' href='$unitLink' title='Go to this unit'>Visit $unitTitle</a>";
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

				/**
				 *	User Wallet
				 */
				echo '<div class="wallet-balance">';
				echo 	'<div class="currency-type kukui">';
				echo  	'<div class="currency-icon kukui"></div>';
				echo  	'<div class="currency-balance kukui">0</div>';
				echo 	'</div>';
				echo '</div>';

			} // End Dashboard ?>

		</div><!-- #content .site-content -->
	</div><!-- #primary .content-area -->
	<?php //get_sidebar(); ?>

<?php get_footer(); ?>