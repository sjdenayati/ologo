<html>
<head>
   <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
         
 
    <title><?php

	/*

	 * Print the <title> tag based on what is being viewed.

	 */

	global $page, $paged;



	wp_title( '|', true, 'right' );



	// Add the blog name.

	bloginfo( 'name' );



	// Add the blog description for the home/front page.

	$site_description = get_bloginfo( 'description', 'display' );

	if ( $site_description && ( is_home() || is_front_page() ) )

		echo " | $site_description";



	// Add a page number if necessary:

	if ( $paged >= 2 || $page >= 2 )

		echo ' | ' . sprintf( __( 'برگه %s', 'autofocus' ), max( $paged, $page ) );



	?></title>
<meta name="google-site-verification" content="4I3WAAc3OwJSJwD7nPefl6Tz9vPH5moMwTVciYGO96A" />
<link rel="stylesheet" href="<?php bloginfo('template_url');?>/style.css">

 <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1" />

<!--link rel="stylesheet" href="<?php bloginfo('template_url');?>/css/bootstrap.min.css">
<link rel="stylesheet" href="<?php bloginfo('template_url');?>/css/bootstrap.css">
<link rel="stylesheet" href="<?php bloginfo('template_url');?>/css/bootstrap-theme.css">
<link rel="stylesheet" href="<?php bloginfo('template_url');?>/css/bootstrap-theme.min.css">
 <link href=" https://bootstrapmade.com/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

<!-- Latest compiled and minified JavaScript -->
<!-- script src="<?php bloginfo('template_url');?>/js/bootstrap.js"></script>
<script src="<?php bloginfo('template_url');?>/js/bootstrap.min.js"></script>
<script src="<?php bloginfo('template_url');?>/js/npm.js"></script>


<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
	
	
	
	
	
	
	<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap-theme.min.css" integrity="sha384-6pzBo3FDv/PJ8r2KRkGHifhEocL+1X2rVCTTkUfGk7/0pbek5mMa1upzvWbrUbOZ" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=shopping_cart" />

<style>
@font-face {
	font-family: PeydaWeb;
	font-style: normal;
	font-weight: bold;
	src: url('<?php bloginfo('template_url');?>/fonts/PeydaWeb-Bold.woff2') format('woff2'),  /* FF39+,Chrome36+, Opera24+*/
		 url('<?php bloginfo('template_url');?>/fonts/PeydaWeb-Bold.woff') format('woff');  /* FF3.6+, IE9, Chrome6+, Saf5.1+*/
}
@font-face {
	font-family: PeydaWeb;
	font-style: normal;
	font-weight: normal;
	src: url('<?php bloginfo('template_url');?>/fonts/PeydaWeb-Regular.woff2') format('woff2'),  /* FF39+,Chrome36+, Opera24+*/
		 url('<?php bloginfo('template_url');?>/fonts/PeydaWeb-Regular.woff') format('woff');  /* FF3.6+, IE9, Chrome6+, Saf5.1+*/
}
:root{
 scroll-behavior: smooth;
}

</style>
<?php wp_head(); ?>


</head>
<div class="container">
 
<header>
  <div class="col-md-1 col-xs-3 pull-right">
    <img src="<?php bloginfo('template_url');?>/images/logo.png" style="
    width: 100%;
    float: right;
">
</div>
  <div class="col-md-6 col-xs-4 pull-right">
    <div class="mmm">
		محتویات منو در اینجا قرار میگیرد
	  </div>
	
    </div>
    <div class="col-md-3 col-xs-4 pull-left">
	 <img src="<?php bloginfo('template_url');?>/images/oo.png" style="
    width: 30%;
">
		<style>
			p.ozv:hover {
    background: #fff !important;
    color: #196ab4 !important;
    border: 1px solid;
}</style>
		
		
		<?php if ( is_user_logged_in() ) { ?>
    <a href="<?php echo site_url('/?page_id=305'); ?>"><p  class="ozv" style="
    background: #196ab4;
    width: 102px;
    text-align: center;
    padding: 6px;
    border-radius: 13px;
    color: #fff;
    font-family: 'PeydaWeb';
">
	ناحیه کاربری
	</p></a>
<?php } else { ?>
    <a href="<?php echo wp_login_url('https://olgoosci.com/wp-login.php'); ?>"><p  class="ozv" style="
    background: #196ab4;
    width: 102px;
    text-align: center;
    padding: 6px;
    border-radius: 13px;
    color: #fff;
    font-family: 'PeydaWeb';
">
	ورود / عضویت
	</p></a>
<?php } ?>
		
		
		
    </div>
</header>
</div>

<body>


