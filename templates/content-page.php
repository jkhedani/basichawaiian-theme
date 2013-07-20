<?php
/**
 * @package _s
 * @since _s 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> data-lesson-id="<?php echo $post->ID; ?>">

	<header class="page-header">
		<h1 class="page-title"><?php the_title(); ?></h1>
	</header><!-- .page-header -->

	<div class="page-content">

		<?php
			$splitContent = split_the_content( get_the_content('more') );
			$splitTranslation = split_the_content( get_field( 'hawaiian_translation' ) );

			// Proper translation check (checks if there are the same amount of more tag breaks)
			if ( count($splitContent) != count($splitTranslation) ) {
				echo '<p>Check your translation. It appears something is missing.</p>';
			} else {
				// Return the content
				$slideCount = count($splitContent);
				for($i = 0; $i < $slideCount; $i++) {
			  	echo $splitContent[$i];
			  	echo $splitTranslation[$i];
					echo '<hr />';
				}
			}

		?>
	</div><!-- .page-content -->

	<footer class="page-footer">
		
	</footer>

</article><!-- #post-<?php the_ID(); ?> -->