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
							// echo smlsubform( array( // http://wordpress.org/plugins/mail-subscribe-list/
							// 	'emailtxt' 	=> 'We are still in beta! Sign up to hear the latest:',
							// 	'submittxt' => 'Subscribe',
							// ));
						?>
						<!-- Sign Up -->
						<a class="btn btn-primary sign-up" href="<?php echo get_home_url(); ?>/wp-login.php?action=register" target="_blank">Sign Up</a>
						<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/available-app-store.png" />
					</div>
				</div><!-- .hero -->

				<!-- Testimonials -->
				<?php
					$testimonials = new WP_Query( array(
						'post_type' => 'testimonials',
						'posts_per_page' => 1,
						'orderby' => 'rand'
					));
				?>
				<div class="testimonials span12">
					<?php while ( $testimonials->have_posts() ) : $testimonials->the_post(); ?>
						<div class="image-container">
							<?php echo get_the_post_thumbnail(); ?>
						</div>
						<div class="content-container">
							<h3><?php echo get_the_content(); ?></h3>
							<h4 class="client">- <?php echo get_the_title(); ?></h4>
						</div>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				</div>

				<!-- Home Page Slides -->
				<div class="home-page-slides span12">

					<!-- Slide 1 -->
					<!-- <div id="visit-the-kukui" class="home-page-slide text-right padded">
						<img class="home-page-featured-image" />
						<div class="home-page-slide-content-container">
							<h3 class="home-page-slide-title">Test Title</h3>
							<p class="home-page-slide-content">Bacon ipsum dolor sit amet rump meatloaf flank, jerky frankfurter swine pork loin bresaola porchetta shank chuck.</p>
							<a class="view-more" href="#">Learn More <i class="icon-chevron-right"></i></a>
						</div>
					</div> -->


					<?php if ( have_rows('home_info_panels') ) : ?>
						<?php $index = 0; ?>
						<?php while ( have_rows('home_info_panels') ) : the_row(); ?>
							<?php $row_id = str_replace(' ', '-', strtolower( get_sub_field('home_info_panel_title') ) ); ?>
							<div id="<?php echo substr( $row_id, 0, 14);  ?>" class="home-page-slide <?php if ( $index%2 === 0 ) { echo "text-left"; } else { echo "text-right"; } ?> padded">
								<div class="home-page-featured-image-container">
									<img src="<?php the_sub_field('home_info_panel_image'); ?>" class="home-page-featured-image" />
								</div>
								<div class="home-page-slide-content-container">
									<h3 class="home-page-slide-title"><?php the_sub_field('home_info_panel_title'); ?></h3>
									<p class="home-page-slide-content"><?php the_sub_field('home_info_panel_content'); ?></p>
									<a class="view-more" href="<?php the_sub_field('home_info_panel_link'); ?>">Learn More <i class="icon-chevron-right"></i></a>
								</div>
								<hr / >
							</div>
							<?php $index++; ?>
						<?php endwhile; ?>
					<?php endif; // have_rows ?>

				</div><!-- .home-page-slides -->

				<!-- Latest Updates (posts) -->
				<div class="latest-updates span12">
					<div class="padded">
						<h2>Latest Updates</h2>
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
			<header id="primary-alert">
				<a data-toggle="close-alert" href="#primary-alert"><i class="fa fa-times"></i></a>
				<div class="alert-avatar aunty-aloha"></div>
				<div class="alert-content">
					<?php if ( !is_unit_complete( 204 ) ) { // Checking if Aunty Aloha is done ?>
					<span class="alert-content-title"><?php _e('E kipa mai i&#257; <span class="inline-kukui aunty-aloha">&#8216;Anak&#275; Aloha</span> ma ka m&#257;la.','hwn'); ?></span>
					<span class="alert-content-subtitle">Completing the first Kukui will unlock the rest!</span>
					<?php } else { ?>
					<h1><?php _e('Visit a kukui to further your knowledge.','hwn'); ?></h1>
					<?php } ?>
				</div>
			</header>

			<!-- Unit Navigation -->
			<?php
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
			?>

			<div class="coverflow">
				<ul class="units">
					<li class="unit shop">
						<a class="unit-info" href="#">?</a>
						<h1>Shop</h1>
						<h2>Redeem points for gear & upgrades!</h2>
						<a class="view-unit btn btn-cta blue" href="#">Shop</a>
					</li>
					<?php $unitCount = 0; ?>
					<?php while ( $units->have_posts() ) : $units->the_post(); ?>
					<?php if ( !$unitCount++ ) : ?>
					<li id="<?php echo $post->post_name; ?>" class="unit active <?php echo $post->post_name; ?>">
					<?php else : ?>
					<li id="<?php echo $post->post_name; ?>" class="unit <?php echo $post->post_name; ?>">
					<?php endif; ?>
						<a class="unit-info" href="#<?php echo $post->post_name; ?>">?</a>
						<h1><?php echo get_the_title(); ?></h1>
						<?php if ( get_field('unit_location') ) : ?>
						<h2><?php echo get_field('unit_location'); ?></h2>
						<?php endif; ?>
						<a class="view-unit btn btn-cta blue" href="<?php echo get_permalink(); ?>">E komo mai</a>
					</li>
					<?php endwhile;
								wp_reset_postdata(); ?>
				</ul>
				<div class="coverflow-controls">
					<a href="#" data-slide-to="prev"><i class="fa fa-chevron-left"></i></a>
					<a href="#" data-slide-to="next"><i class="fa fa-chevron-right"></i></a>
				</div>
				<div class="coverflow-counter-container">
					<div class="coverflow-counter"></div>
					<?php $unitCount = 0; ?>
					<?php while ( $units->have_posts() ) : $units->the_post(); ?>
						<?php if ( !$unitCount++ ) : ?>
						<div class="coverflow-counter active"></div>
						<?php else : ?>
						<div class="coverflow-counter"></div>
					<?php endif; ?>
					<?php endwhile;
								wp_reset_postdata(); ?>
				</div>

				<!-- User Avatar -->
				<?php
					$user = wp_get_current_user();
	    		$user_id = $user->ID;
					$gender = get_user_meta( $user_id, 'gender', true );
				?>
				<div class="user-avatar <?php echo $gender; ?> default"></div>
			</div><!-- .coverflow -->

			<?php
				// $units = new WP_Query(array(
				// 	'post_type' => 'units',
				// 	'orderby' => 'ID',
				// 	'order' => 'DESC',
				// 	'posts_per_page' => 10,
				// ));

				// // Grab all post IDs from units...
				// $unitIDs = array();
				// while ( $units->have_posts() ) : $units->the_post();
				// 	$unitIDs[] = $post->ID;
				// endwhile;

				// // Determine if unit is complete prior to loop (bug)
				// foreach ($unitIDs as $unitID) {
				// 	if ( is_unit_complete($unitID) ) {
				// 		$unitsCompleted[] = 1;
				// 	} else {
				// 		$unitsCompleted[] = 0;
				// 	}
				// }

				// // Create fresh object records if they do not have any for this page.
				// create_object_record( $unitIDs );

				// // Show all units and check if they are complete
				// echo '<ul class="units dashboard-selections">';
				// $unitCount = 0;
				// while ( $units->have_posts() ) : $units->the_post();
				// 	$unitID = $post->ID;
				// 	$unitLink = get_permalink();
				// 	$unitTitle = get_the_title();

				// 	// Modify markup for each individual unit for now

				// 	if ( $unitID == 204 ) {
				// 		$popoverContent = "
				// 			<h1>&#8216;Anak&#275; Aloha</h1>
				// 			<i>Topics in this module: </i>
				// 			<ul>
				// 				<li>KA HOʻOLAUNA (Introductions)</li>
				// 				<li>KA ʻOHANA (Family)</li>
				// 				<li>KA MOʻOKŪʻAUHAU (Genealogy)</li>
				// 				<li>NĀ LANI ʻEHĀ: Liliʻuokalani (The Royal Four: Liliʻuokalani)</li>
				// 				<li>KA ʻAI ME KA ʻAI ʻANA (Food and Food Preparation)</li>
				// 				<li>NĀ HELU/NĀ WAIHOʻOLUʻU (Numbers and Colors)</li>
				// 			</ul>
				// 			<a class='btn btn-primary' href='$unitLink' title='Go to this unit'>Visit $unitTitle</a>
				// 		";
				// 	} elseif ( $unitID == 203 ) {
				// 		$popoverContent = "
				// 			<h1>&#8216;Anakala Ikaika</h1>
				// 			<i>Topics in this module: </i>
				// 			<ul>
				// 				<li>NĀ MĀMALA ʻŌLELO MAʻAMAU (Everyday Phrases)</li>
				// 				<li>NĀ KAUOHA (Commands)</li>
				// 				<li>NĀ KUHIKUHI (Directions) </li>
				// 				<li>NĀ LANI ʻEHĀ: Kalākaua (The Royal Four: Kalākaua)</li>
				// 				<li>KE KAI (The Ocean)</li>
				// 				<li>NĀ HAʻUKI ME NĀ PĀʻANI LIKE ʻOLE (Sports and Games)</li>
				// 				<li>KA ʻALEMANAKA/KA MANAWA (Calendar and Dates)</li>
				// 			</ul>
				// 			<a class='btn btn-primary' href='$unitLink' title='Go to this unit'>Visit $unitTitle</a>
				// 		";
				// 	} else {
				// 		$popoverContent = "<h1>&#8216;Anak&#275; Aloha</h1><i>Topics in this module: </i><ul><li>Introductions</li><li>Greetings</li><li>Family</li><li>Gardening</li><li>Food</li></ul><a class='btn btn-primary' href='$unitLink' title='Go to this unit'>Visit $unitTitle</a>";
				// 	}
				// 		echo '<li class="unit ">';
				// 		echo 	'<a class="dashboard-selection post'.$post->ID.'" href="javascript:void(0);" data-title="'.get_the_title().'" data-content="'.$popoverContent.'" data-complete="'.$unitsCompleted[$unitCount].'">';
				// 		echo 		'<img src="'.get_stylesheet_directory_uri().'/images/mug-icons.png" />';
				// 		echo 		'<div class="dashboard-selection-info"><h4>'.get_the_title().'</h4></div>';
				// 		echo 	'</a>';
				// 	echo '</li>';

				// 	$unitCount++;
				// endwhile;
				// echo '</ul>';
				// wp_reset_postdata();

				/**
				 * User Avatar
				 */
				// $user = wp_get_current_user();
    // 		$user_id = $user->ID;
				// $gender = get_user_meta( $user_id, 'gender', true );
				// echo '<div class="user-avatar '.$gender.' default"></div>';
				// echo '<div class="content-fluff rock-platform"></div>';

				/**
				 *	User Wallet
				 */
				// echo '<div class="wallet-balance">';
				// echo 	'<div class="currency-type kukui">';
				// echo  	'<div class="currency-icon kukui"></div>';
				// echo  	'<div class="currency-balance kukui">0</div>';
				// echo 	'</div>';
				// echo '</div>';

			} // End Dashboard ?>

		</div><!-- #content .site-content -->
	</div><!-- #primary .content-area -->
	<?php //get_sidebar(); ?>

<?php get_footer(); ?>
