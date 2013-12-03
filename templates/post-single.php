<?php
/**
 * @package _s
 * @since _s 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="entry-header">
		
		<span class="post-date"><?php echo get_the_date('F j, Y'); ?></span>
		<h1 class="entry-title"><?php the_title(); ?></h1>
		<span class="post-author">By <?php the_author(); ?></span>
		<hr />

	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php the_content(); ?>
	</div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->