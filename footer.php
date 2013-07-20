<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package _s
 * @since _s 1.0
 */
?>
	
	<?php bedrock_mainend(); ?>

	</div><!-- #main .site-main -->
	</div><!-- #page .hfeed .site -->

	<?php if ( is_user_logged_in() ) { // Is logged in ?>
		<footer id="colophon" class="site-footer container" role="contentinfo">
			<div class="site-info">
				<div class="container-narrow">
					<p>&copy; Basic Hawaiian <?php echo date('Y'); ?></p>
				</div>	
			</div><!-- .site-info -->
		</footer><!-- #colophon .site-footer -->
	<?php } else { // Not logged in ?>
		<footer id="colophon">
			<div class="container-narrow">
				<p>&copy; Basic Hawaiian <?php echo get_the_date('Y'); ?></p>
			</div>
		</footer>
	<?php } // not logged in ?>

<?php wp_footer(); ?>
<?php bedrock_after(); ?>

</body>
</html>