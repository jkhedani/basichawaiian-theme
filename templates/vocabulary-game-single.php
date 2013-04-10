<?php
/**
 * @package _s
 * @since _s 1.0
 */

$postType = get_post_type($post->ID);
$postTypeObject = get_post_type_object($postType);

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php bedrock_postcontentstart(); ?>

	<header class="entry-header">
		
		<?php
			// BREADCRUMB
			// Retrieve the connected module parent
			$moduleParent = new WP_Query(array(
			  'connected_type' => 'vocabulary_games_to_modules',
			  'connected_items' => get_queried_object(),
			  'nopaging' => true,
			));
			echo '<ul class="breadcrumb">';
			echo 	'<li class="breadcrumb-home"><a href="'.get_home_url().'" title="Go back home.">Home</a> <span class="divider">/</span></li>';
			while ($moduleParent->have_posts()) : $moduleParent->the_post();
				echo 	'<li><a href="'.get_permalink().'" title="Go back to the '.get_the_title().' module.">'.get_the_title().'</a> <span class="divider">/</span></li>';
				echo 	'<li><a href="'.get_permalink().'" title="Go back to the '.get_the_title().' module.">'.$postTypeObject->labels->name.'</a> <span class="divider">/</span></li>';
			endwhile;
			wp_reset_postdata();
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

	<div class="entry-content row"><?php
	
		the_content();

		echo '<div class="span12" id="vocabulary-games">';
		echo '<a class="btn btn-primary vocabulary-category" href="javascript:void(0);" data-category="Hua" data-connected-to-id="'.$post->ID.'">Start</a>';
		echo '</div>';
	
	?>

	<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', '_s' ), 'after' => '</div>' ) ); ?>
	</div><!-- .entry-content -->

	<?php bedrock_postcontentend(); ?>

</article><!-- #post-<?php the_ID(); ?> -->