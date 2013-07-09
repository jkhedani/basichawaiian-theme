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
		
	</div><!-- .page-content -->

	<footer class="page-footer">
		
	</footer>

</article><!-- #post-<?php the_ID(); ?> -->