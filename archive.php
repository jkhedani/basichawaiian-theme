<?php
/**
 * The template for displaying Archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package _s
 * @since _s 1.0
 */

get_header(); ?>

		<section id="primary" class="content-area">
			<div id="content" class="site-content" role="main">

			<?php if ( have_posts() ) : ?>

				<?php if ( isset( $_REQUEST['archive-type']) && $_REQUEST['archive-type'] === 'screenshots' ) : ?>

				<div class="development-screenshots">
					<div class="padded">
						<h2>Development Screenshots</h2>
						<?php $idObj = get_category_by_slug('screenshots'); $id = $idObj->term_id; ?>
						<?php $latestScreenshotPosts = new WP_Query( array( 'post_type' => 'post', 'cat' => $id )); ?>
						<ul class="three-up">
						<?php while ( $latestScreenshotPosts->have_posts() ) : $latestScreenshotPosts->the_post(); ?>

							<?php $latestScreenshots = new WP_Query( array( 'post_type' => 'attachment', 'post_status' => 'any', 'post_parent' => $post->ID  )); ?>
							<?php while ( $latestScreenshots->have_posts() ) : $latestScreenshots->the_post(); ?>

							<?php $latestScreenshot = wp_get_attachment_image_src( $post->ID, 'large' ); ?> 
							<li>
								<a class="wrapper" href="<?php echo the_permalink(); ?>" target="_blank">
									<img src="<?php echo $latestScreenshot[0]; ?>" />
								</a>
							</li>

							<?php endwhile; ?>
							<?php wp_reset_postdata(); ?>

						<?php endwhile; ?>
						<?php wp_reset_postdata(); ?>
						</ul>
					</div>
				</div><!-- dev screenshots -->

				<?php else : ?>

					<?php while ( have_posts() ) : the_post(); ?>

						<?php
							/* Include the Post-Format-specific template for the content.
							 * If you want to overload this in a child theme then include a file
							 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
							 */
							if ( ! in_category('screenshots') )
								get_template_part( 'templates/post', 'single' );
						?>

					<?php endwhile; ?>

				<?php endif; ?>

			<?php else : ?>

				<?php get_template_part( 'no-results', 'archive' ); ?>

			<?php endif; ?>

			</div><!-- #content .site-content -->
		</section><!-- #primary .content-area -->

<?php get_footer(); ?>