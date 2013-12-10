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

	<?php
	if ( get_post_type( $post->ID ) == 'topics' || get_post_type( $post->ID ) == 'units' ) :
		if ( is_user_logged_in() ) {
			/**
			 * User Avatar
			 */
			$user = wp_get_current_user();
			$user_id = $user->ID;
			$gender = get_user_meta( $user_id, 'gender', true );
			echo '<div class="avatars">';
			echo 	'<div class="avatar-wrapper">';
			if ( get_post_type( $post->ID ) == 'units' ) {
				echo 		'<div class="user-avatar '.$gender.' backturn"></div>';
				echo 		'<div class="kukui-avatar aunty-aloha backturn"></div>';	
			} else {
				echo 		'<div class="user-avatar '.$gender.' default"></div>';
				echo 		'<div class="kukui-avatar aunty-aloha default"></div>';	
			}
			echo  '</div>';
			echo '</div>';

			/**
			 *	User Currency
			 */
			$walletBalance = get_wallet_balance($post->ID); ?>
			<div class="wallet-balance span4 pull-right">
				<div class="currency-type flower">
					<div class="currency-icon flower"></div>
					<div class="currency-balance flower"><?php echo !empty($walletBalance) ? $walletBalance : "0"; ?></div>
				</div>

				<div class="currency-type kukui">
					<div class="currency-icon kukui"></div>
					<div class="currency-balance kukui">0</div>
					<?php //if ( $walletBalance > 1 ) { echo '<a class="btn btn-small pull-right claim-kukui" href="javascript:void(0);">Claim a kukui</a>'; } ?>
				</div>
			</div>
		<?php }
	endif;
	?>

	<?php if ( is_user_logged_in() ) { // Is logged in ?>
<!-- 		<footer id="colophon" class="site-footer container" role="contentinfo">
			<div class="site-info">
				<div class="container-narrow">
					<p>&copy; Basic Hawaiian -->
						<?php // echo date('Y'); ?>
					<!-- </p>
				</div>	
			</div> --><!-- .site-info -->
		<!-- </footer> --><!-- #colophon .site-footer -->
	<?php } else { // Not logged in ?>
		<footer id="colophon">
			<div class="footer-art container"></div>
			<div class="footer-content container">
				<span class="copyright">&copy; Basic Hawaiian <?php echo get_the_date('Y'); ?></span>
				<?php  wp_nav_menu( array('menu' => 'Project Nav' )); ?>
			</div>
		</footer>
	<?php } // not logged in ?>

<?php wp_footer(); ?>
<?php bedrock_after(); ?>

<!-- Google Analytics Tracking Code -->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-44955670-1', 'basichawaiian.com');
  ga('send', 'pageview');

</script>

</body>
</html>