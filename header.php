<!doctype html>
<!--[if lt IE 8]>      <html class="no-js lt-ie9 lt-ie8 lt-ie10" <?php language_attributes(); ?>> <![endif]-->
<!--[if lt IE 8]>         <html class="no-js lt-ie9 lt-ie10" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 9]>         <html class="no-js lt-ie10" <?php language_attributes(); ?>> <![endif]-->
<!--[if gt IE 9]><!--> 
<html class="no-js" <?php language_attributes(); ?>> 
<!--<![endif]-->
	<head>
		<meta charset="utf-8">
		<title><?php wp_title( '|', true, 'right' ); ?><?php bloginfo('name'); ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1, minimal-ui">

		<link rel="profile" href="http://gmpg.org/xfn/11" />
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

		<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,400italic,600italic' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" href="<?php bloginfo( 'template_directory' ) ;?>/assets/css/style<?php if ( PRODUCTION_MODE ) echo '.min'; ?>.css">

		<?php /*<script src="<?php bloginfo( 'template_directory' ) ;?>/assets/js/lib/modernizr-2.8.1.min.js"></script> Currently not used. Uncomment if needed. */ ?>
		
		<?php wp_head(); ?>
	</head>
	<body <?php body_class( 'sk-theme-' . get_field( 'site_color', 'option' ) ); ?>>
		<!--[if lt IE 9]>
			<div class="outdated-browser">
				<p>
					<img src="<?php echo get_template_directory_uri(); ?>/assets/images/sad.png" alt="Inget browserstöd">
					Din webbläsare är <em>väldigt</em> gammal. Den är så pass gammal att den här webbplatsen inte kommer att fungera riktigt som den ska. På <a href="http://browsehappy.com/">browsehappy.com</a> kan du få hjälp med att uppgradera din webbläsare och förbättra upplevelsen.
				</p>
			</div>
		<![endif]-->

		<?php //require_once( dirname( __FILE__ ) .'/assets/images/icons.svg' ); ?>
		<?php //require_once( dirname( __FILE__ ) .'/assets/of/of-assets/images/icons.svg' ); ?>
		<?php require_once( get_template_directory() .'/assets/of/of-assets/images/icons.svg' ); ?>
		<?php //require_once( get_template_directory() .'/assets/of/of-assets/images/checkbox.svg' ); ?>
		
		<?php /*<div id="fb-root"></div>
		<script>(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.0";
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));</script> */ ?>

		<?php if ( PRODUCTION_MODE === false ) : ?>
			<div class="screen"></div>
		<?php endif; ?>
		<div class="of-sidebar-menu-advanced-content-wrap">
			<a href="#content" class="sk-skip-to-content"><?php _e( 'Hoppa till innehåll', 'sk' ); ?></a>

			<header role="banner" class="sk-site">
				<?php if ( get_field('site_header_bg', 'option' ) ) : ?>
					<?php $header_bg = wp_get_attachment_image_src( get_field('site_header_bg', 'option' ), 'full' ); ?>
				<?php endif; ?>

				<div class="top-banner" <?php if ( isset( $header_bg ) && $header_bg !== false ) : ?> style="background-image: url(<?php echo $header_bg[0]; ?>)"<?php endif; ?>>
					<div class="of-wrap">
						<h1 class="sk-site-title">
							<a href="<?php echo bloginfo( 'url' ); ?>">
								<?php if ( get_field( 'site_logo', 'option' ) ) :
									$logo = wp_get_attachment_image_src( get_field( 'site_logo', 'option' ), 'full' ); ?>
									
									<?php if( $logo !== false ) : ?>
										<img src="<?php echo $logo[0]; ?>" alt="Logotyp"> 
									<?php endif; ?>
								<?php else : ?>
									<?php bloginfo( 'title' ); ?>
								<?php endif; ?>
							</a>
						</h1>
					</div>
				</div>
				
				<div class="sk-main-menu-outer-wrap">
					<div class="sk-main-menu-wrap">
						<div class="of-wrap">
							<a class="of-menu-toggle of-icon of-icon-lg of-hide-from-lg sk-mobile-menu-toggle js-mobile-menu-toggle" href="#">
								<em class="of-menu-open">
									<i><?php icon('menu'); ?></i>
									<span>Meny</span>
								</em>

								<em class="of-menu-close">
									<i><?php icon('close-lg'); ?></i>
									<span>Stäng</span>
								</em>
							</a>

							<?php wp_nav_menu( array(
								'theme_location' => 'main-menu',
								'container' => '',
								//'container' => 'nav',
								//'container_class' => 'of-sidebar-menu-advanced js-mobile-menu',
								'walker' => new SK_Walker_Top_Menu(),
							) ); ?>


							<a class="sk-search-toggle js-search-toggle of-icon of-icon-lg of-hide-from-lg" href="#">
								<em class="sk-search-open">
									<i><?php icon('search'); ?></i>
									<span>Sök</span>
								</em>

								<em class="sk-search-close">
									<i><?php icon('close-lg'); ?></i>
									<span>Stäng</span>
								</em>
							</a>
							<form method="get" id="searchform" class="sk-search-form js-search-form of-hide-to-lg" action="<?php bloginfo( 'url' ); ?>/">
								<input type="text" size="18" value="<?php echo esc_html( $s ); ?>" name="s" id="s" placeholder="<?php _e('Sök', 'sk'); ?>" />
							</form>
						</div>
					</div>
				</div>
			</header>

			<main role="main" id="content">
