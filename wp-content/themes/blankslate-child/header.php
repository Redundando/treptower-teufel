<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <main id="main">
 *
 * @package Sela
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">


<?php wp_head(); ?>
	

</head>

<body <?php body_class(); ?>>
<div id="page" class="hfeed site">
	<header id="masthead" class="site-header" role="banner">
	
	
	<div class="top-header">
		<span class="menu-icon">  
			<div id="hamburger">
				<span></span>
				<span></span>
				<span></span>
			</div>
		</span>

		<a href="<?php echo pll_home_url()?>"><img class="site-logo" src="https://www.treptower-teufel.de/wp-content/uploads/2018/02/csm_logo_498da7ba5c.png"  alt="Logo Treptower Teufel"/></a>
		<div class="top-header-right">
			<span class="site-title">
				<a href="<?php echo pll_home_url()?>"><?php bloginfo( 'name' ); ?></a>
                <div class="social-icons">
                    <a href="https://www.facebook.com/TreptowerTeufelTC" class="facebook-icon same-window"></a>
                </div>
				<ul class="mobile-menu-language"><?php pll_the_languages(array('show_flags'=>1,'show_names'=>0, 'hide_current'=>1)); ?></ul>
			</span>
			<nav class="navbar" >
				<div class="sitenavigation">
					<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => '' ) ); ?>
				</div>
			</nav>
		</div>
	</div>
	<nav class="mobile-nav" >
		<div class="sitenavigation">
			<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => '' ) ); ?>
		</div>
	</nav>
	



	</header><!-- #masthead -->

	<div id="content" class="site-content">

