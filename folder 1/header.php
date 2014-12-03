<?php 
global $g_imagesfolder;
global $g_blogurl;
global $g_bodystyle;

$g_imagesfolder = get_bloginfo('stylesheet_directory')."/images/";
$g_blogurl = site_url();

/*
if (isset($_POST)) {
   if (array_key_exists("formaction",$_POST)) {
      switch ($_POST["formaction"]) {
      case "login":
         $creds = array();
         $creds['user_login'] = $_POST['email'];
         $creds['user_password'] = $_POST['pwd'];
         $creds['remember'] = false;
         wp_signon($creds,false);
         // we don't check return values, if its succeeds, fine, if not, also fine
         break;
      }
   }
}
*/
?>
<!DOCTYPE html>

<html>
<head>
<?php /* FeedGrabbr - Choose the news. Make a free RSS widget. Use it anywhere */ ?>
<title>FeedGrabbr | Make a great looking, free RSS news widget for your website</title> 
<!-- IE8 and below cannot handle loading multiple weights of Open Sans in one link, so we split it into two -->
<!--[if lt IE 9]>
<link href='https://fonts.googleapis.com/css?family=Open+Sans:700' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Open+Sans:400' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Nothing+You+Could+Do' rel='stylesheet' type='text/css'>
<![endif]-->
<!--[if gte IE 9]>
<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Nothing+You+Could+Do' rel='stylesheet' type='text/css'>
<![endif]-->
<!--[if !IE]> -->
<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>
<!--<link href='https://fonts.googleapis.com/css?family=Source Sans Pro:400,700' rel='stylesheet' type='text/css'>-->
<link href='https://fonts.googleapis.com/css?family=Nothing+You+Could+Do' rel='stylesheet' type='text/css'>
<!-- <![endif]-->


<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<?php /*  FeedGrabbr embeds news into your website. Create a news widget, combine RSS feeds, filter stories by keyword and customize everything. Easy to use and free */ ?>
<meta name="description" content="Create an RSS news widget for your website. Unique, customisable styles. Multiple RSS feeds. Keyword filtering. Easy to use and free" /> 
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-35587085-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</head>
<body <?php if (isset($g_bodystyle)) echo " style='".$g_bodystyle."' "; ?> >
   <div class="logo"><a href="<?php echo $g_blogurl; ?>"><img src="<?php echo $g_imagesfolder; ?>fg_logo_x2.png"/></a></div>

<div id="fg-header">
   <div class="nav content">
<?php /*
      <!--<div id="nav-1">
         <a href="<?php echo $g_blogurl; ?>">Home</a><span> | </span>
         <a href="<?php echo $g_blogurl; ?>/features">Features</a><span> | </span>
         <a href="<?php echo $g_blogurl; ?>/pricing">Pricing</a> 
      </div>--> */ 
?>
      
      <?php 
         if (is_user_logged_in()) {
            global $user_identity;
            global $user_ID;
            global $g_userinfo;
            global $g_freeplan;

            get_currentuserinfo();
            $g_userinfo = fg_getUserInfo("all");

            $plan = $g_userinfo->Type;

            $g_freeplan = ($plan == "F" || $plan == "B" || $plan == "F1");


            ?>
               <div id="nav-2" class="loggedin">
               <span class="welcome"><?php echo $user_identity; ?></span>

			   <?php
					if ($g_freeplan)
            // if ($user_identity == "braintree" || $user_identity=="admin") 
						printf("<a href='%s/upgrade' id='headerupgradelink'>Upgrade to Pro</a>",fg_getHTTPSSiteURL());

               // we don't show the 'my account' link for ELN, because the page tries to pull down customer data from BT
               if ($plan == 'P' || $plan == "PU" || $plan=="PP" || $plan == "PL") 
               {
                  echo '<span class="vdivider"></span>';
						printf("<a href='%s/myaccount'>Account</a>",fg_getHTTPSSiteURL());
               }
			   ?>
               <a href="<?php echo fg_getHTTPSiteURL(); ?>/mywidgets/">Your Widgets</a>
               <span class="vdivider"></span>
               <?php if ($g_freeplan || $plan == "A") { ?>
               <a href="<?php echo $g_blogurl ?>/pricing" >Pricing</a>
               <?php } ?>
               <a href="<?php echo wp_logout_url($g_blogurl); ?>">Logout</a> 
		         <a href="http://support.feedgrabbr.com/">Support</a>

               <?php
         }
         else
         {  ?>
            <div id="nav-2">
            <?php /* <a href="<?php echo $g_blogurl; ?>/fg-widgetconfig/">Create a Widget & Sign Up</a> */ ?>
            <?php /* <!--<a href="#" class="betasignupbtn" id="signupbeta">Sign up for the beta</a>-->
            <!--<a id="betalogin" href="<?php echo wp_login_url(home_url()); ?>">Login to private beta</a>--> */ ?>
			   <a href="<?php echo $g_blogurl ?>/pricing" >Pricing</a>
			   <a id="betalogin" href="#" rel="nofollow">Login</a>
            <a href='#' class='signupbtn' rel="nofollow">Sign Up</a>
			         <a href="http://support.feedgrabbr.com/">Support</a>

            <?php
         }
         ?>
      </div>

   </div>
<?php
   // the login opage wont terminate the fg-header div
   if (in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php')))
       echo "</div>";
?>
</div>

