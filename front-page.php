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

get_header(); ?>
	
	<!-- Basic Hawaiian Home -->

	<?php
		if ( ! is_user_logged_in() ) {
		echo '<div id="primary" class="content-area row">';
		echo '<div id="content" class="site-content" role="main">';
			while ( have_posts() ) : the_post();
			echo get_the_content();
			endwhile; // end of the loop.
			echo '<a class="btn btn-large btn-success href="#">Sign up today</a>';
			echo '<hr />';
			echo '<div class="row marketing">';
			echo 	'<div class="span4"><h4>Subheading</h4><p>Donec id elit non mi porta gravida at eget metus. Maecenas faucibus mollis interdum.</p></div>';
			echo 	'<div class="span4"><h4>Subheading</h4><p>Donec id elit non mi porta gravida at eget metus. Maecenas faucibus mollis interdum.</p></div>';
			echo 	'<div class="span4"><h4>Subheading</h4><p>Donec id elit non mi porta gravida at eget metus. Maecenas faucibus mollis interdum.</p></div>';
			echo '</div>';
		} else {
	?>

	<!-- Learner's Dashboard -->
			
	<div id="primary" class="content-area row">
		<div id="content" class="site-content span12" role="main">
			
			<!-- User Metadata -->

			<div class="row">
				<header class="span12">
					<h1><?php _e('Welcome','hwn'); ?></h1>
					<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo massa. Etiam lorem eros, ullamcorper a rutrum non.</p>
				</header>
				<div class="span6">
					<?php // Get User Profile
						$current_user = wp_get_current_user();
						echo '<h3>'. $current_user->user_firstname . '&nbsp;' . $current_user->user_lastname .'</h3>'; // There's a space
					?>
				</div>
			</div>

			<!-- Content Navigation -->

			<div class="row">
			<?php

				// Display all Modules
				$modules = new WP_Query(array(
					'post_type' => 'modules',
					'orderby' => 'menu_order',
					'order' => 'ASC',
				));
				echo '<ul>';
				while ($modules->have_posts()) : $modules->the_post();
					echo '<li>';
					echo 	'<a class="module" href="'.get_permalink().'" title="Go to the'.get_the_title().' activity">';
					echo 		'<h2>'.get_the_title().'</h2>';
					echo 	'</a>';
					echo '</li>';
				endwhile;
				echo '</ul>';
				wp_reset_postdata();

			?>
			</div><!-- .row -->

			<?php } // End Dashboard ?>

		</div><!-- #content .site-content -->
	</div><!-- #primary .content-area -->
	<?php //get_sidebar(); ?>

<?php get_footer(); ?>