<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package _s
 * @since _s 1.0
 */
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title><?php
	//Print the <title> tag based on what is being viewed. 
	global $page, $paged;

  //Add page/content title
	wp_title( '|', true, 'right' );

	// Add the site name.
	bloginfo( 'name' );

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', '_s' ), max( $paged, $page ) );

?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<link rel="shortcut icon" href="<?php bloginfo('stylesheet_directory'); ?>/images/favicon.png" />
<!--[if lt IE 9]>
<script src="<?php echo get_template_directory_uri(); ?>/inc/js/html5.js" type="text/javascript"></script>
<![endif]-->

<?php wp_head(); ?>

<?php
  // ADMIN: Move navbar down from under admin when user is
  // logged in but not in the theme customizer previewer
  global $wp_customize;
  if( current_user_can('edit_posts') && ! isset( $wp_customize )) {
    echo '
    <style type="text/css">
      #navbar { margin-top: 28px; } /* Positions navbar below admin bar */
      #main { padding-top: 88px; } /* Lowers all content below navbar to approximate position */
      .drawer { top: 32px; }
      @media (max-width: 979px) {
        #main { padding-top: 0px; } /* Navbar turns static, no need for compensation here*/
      }
    </style>';
  }
?></head>

<body <?php body_class(); ?>>

  <?php if ( is_user_logged_in() ) {  ?>
  <header id="navbar">
    <div class="navbar navbar-inverse">
      <div class="navbar-inner">

        <!-- Site Brand/Title -->
        <a class="brand site-title" href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><i class="icon icon-logo-greens"></i><?php bloginfo( 'name' ); ?></a>

        <!-- Show user earnings and points -->
        <div class="user-achievements">
          <?php $walletBalance = get_wallet_balance( 204 ); ?>
          <div class="currency-balance flower"><?php echo !empty($walletBalance) ? $walletBalance : "0"; ?></div>
        </div>

        <!-- Show user name and settings -->
        <div class="user-settings">
          <?php
            global $current_user;
            $current_user_info = wp_get_current_user();
          ?>
          <p>Aloha, <?php echo $current_user_info->user_login; ?></p>          
          <div class="settings-panel">
            <a data-toggle="drawer" class="drawer-closed" href="#"><i class="fa fa-bars"></i></a>
            <ul class="drawer">
              <li><a class="edit-profile" href="<?php echo get_edit_user_link(); ?>">Edit your profile</a></li>
              <?php if ( current_user_can('edit_posts') ) // Reset only for those who can edit the site ?>
              <li><a href="#" class="reset-scores">Reset Score</a></li>
              <li><a href="<?php echo wp_logout_url(); ?>" title="Logout">Logout</a></li>
            </ul>
          </div><!-- .settings-panel -->
        </div><!-- .user-meta -->

      </div><!-- .navbar-inner -->
    </div>
  </header>
  <?php } else { // user is not logged in ?>
  <header id="navbar-basic">
    <div class="container">
      <h2 class="site-title"><a href="<?php echo get_site_url(); ?>"><?php bloginfo('name'); ?></a></h2>
      <a class="btn btn-primary log-in" href="<?php echo get_home_url(); ?>/wp-login.php" title="Sign into your account here.">Log In</a>
    </div>
  </header>
  <?php } // user not logged in ?>

  <div id="page" class="hfeed site container">

  	<div id="main" role="main" class="site-main">
