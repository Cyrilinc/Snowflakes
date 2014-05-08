<?php
require_once 'lib/sf.php';
require_once 'lib/sfConnect.php';
require_once 'config/Config.php';

// *** Validate request to login to this site.
if (!isset($_SESSION)) {
    session_start();
}
$php_self = filter_input(INPUT_SERVER, 'PHP_SELF');
$loginFormAction = $php_self;
$rs_reset = filter_input(INPUT_GET, 'reset');
if (isset($rs_reset)) {
    $oldResetLink = $rs_reset;
}

$Post_password = filter_input(INPUT_POST, 'password');
$resetMessage = "";
if (isset($Post_password)) {
    $password = filter_input(INPUT_POST, 'password2');
    $oldResetLink = filter_input(INPUT_POST, 'reset_link');
    $MM_redirectLoginSuccess = "login.php";
    $MM_redirectLoginFailed = $php_self;

    $config = Config::getConfig("db", 'config/config.ini');
    $sqlArray = array('type' => $config['type'], 'host' => $config['host'], 'username' => $config['username'], 'password' => sfUtils::decrypt($config['password'], $config['key']), 'database' => $config['dbname']);
    $SFconnects = new sfConnect($sqlArray);
    $SFconnects->connect(); // Connect to database

    $passwordReset = sfUtils::resetPassword($SFconnects, $password, $oldResetLink);
    if ($passwordReset) {
        $resetMessage.= "Your password has been reset Successfully!";
        $resetMessage.='<span class="icon success"></span>';
    } else {
        $resetMessage.= "Your password could not reset properly!";
        $resetMessage.='<span class="icon error"></span><br/>'
                . $SFconnects->getMessage() . '<br/>';
    }
}
?>
<!DOCTYPE HTML>
<html lang="en" ><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Cyril Inc">
        <meta name="viewport" content="width=device-width, maximum-scale = 1, minimum-scale=1" />
        <!-- InstanceBeginEditable name="doctitle" -->
        <title>Reset snowflakes password</title>
        <!-- InstanceEndEditable -->
        <link rel="icon" href="resources/images/favicon.ico" type="image/x-icon" />
        <link rel="shortcut icon" href="resources/images/favicon.ico">
        <link rel="apple-touch-icon" href="resources/images/apple-touch-icon.png">
        <link rel="stylesheet" type="text/css" href="resources/css/Mobile.css" media="only screen and (max-width: 767px)" />
        <link rel="stylesheet" type="text/css" href="resources/css/style.css" media="only screen and (min-width: 768px)" />
        <link rel="stylesheet" title="text/css" href="resources/css/ColorBox.css" />
        <link rel="stylesheet" title="text/css" href="resources/css/fontstyle.css" />
        <script type="text/javascript" src="resources/Js/jquery-1.11.0.js"></script>
        <script type="text/javascript" src="resources/Js/modernizr.custom.63321.js"></script>
        <script type="text/javascript" src="resources/Js/jquery.cycle.all.js"></script>
        <script type="text/javascript" src="resources/Js/jquery.colorbox.js"></script>
        <script type="text/javascript" src="resources/Js/scrolltopcontrol.js"></script>
        <script type="text/javascript" src="resources/Js/Snowflakes.js"></script>

        <!-- InstanceBeginEditable name="head" -->
        <!--[if IE]>
        <link rel="stylesheet" type="text/css" href="resources/css/style.css"/>
        <![endif]-->

        <!--[if IEMobile]> 
           <link rel="stylesheet" type="text/css" href="resources/css/Mobile.css"/>
        <![endif]-->
        <?php if (isset($rs_reset)) { ?>
            <script src="SpryAssets/SpryValidationPassword.js" type="text/javascript"></script>
            <script src="SpryAssets/SpryValidationConfirm.js" type="text/javascript"></script>
            <link href="SpryAssets/SpryValidationPassword.css" rel="stylesheet" type="text/css" />
            <link href="SpryAssets/SpryValidationConfirm.css" rel="stylesheet" type="text/css" />

        <?php } ?>
        <link href="resources/css/jquery-ui-1.10.4.snowflakes.css" rel="stylesheet" type="text/css" />
        <script src="resources/Js/jquery-ui-1.10.4.snowflakes.js"></script>
        <script>
                    $(function() {
                    $(".dialog-message").dialog({
                    modal: true,
                            buttons: {
<?php if ($passwordReset) { ?>
                                "Log In"
                                        : function() {
                                        window.location = "<?php echo $MM_redirectLoginSuccess ?>";
                                        }
<?php } else { ?>
                                "Ok": function() {
                                $(this).dialog("close");
                                }
<?php } ?>

                            }
                    });
                    });</script>
        <!-- InstanceEndEditable -->
    </head>
    <body> 
        <!--HeaderWrapper-->
        <div class="HeaderWrapper"> 
            <!--pagewidth-->
            <div class="pagewidth">
                <header class="site-header">
                    <div class="pagewidth">
                        <div class="Logo"><img alt="Snowflakes" class="logo" src="resources/images/Snowflakes.png" width="180" height="60" /></div>
                        <div class="SideMenu"> <a class="opener" id="touch-menu" href="#"><i class="icon-reorder"></i>Menu</a>
                            <ul id="primary_nav" class="primary_nav">
                                <!-- InstanceBeginEditable name="menuEdit" -->
                                <li class="active" id="AtvNewButton"><a href="Home.php" title="Snowflake Home"> <img src="resources/images/Icons/Home.png" height="22" width="22"  alt="Add" /> Home </a></li>
                                <li><a href="#" title="miscellaneous"> <img src="resources/images/Icons/Misc.png" height="22" width="22"  alt="Misc" /> Misc </a>
                                    <ul>
                                        <li><a href="ViewSnowflakes.php" title="Snowflakes"> <img src="resources/images/Icons/Snowflakes.png" height="22" width="22"  alt="Snowflakes" /> Snowflakes </a></li>
                                        <li><a href="Events/index.php" title="Snowflake Events"> <img src="resources/images/Icons/Events.png" height="22" width="22"  alt="Events" /> Events </a></li>
                                        <li><a href="Gallery/index.php" title="Gallery"> <img src="resources/images/Icons/Gallery.png" height="22" width="22"  alt="+ " /> Gallery</a></li>
                                    </ul>
                                </li>
                                <!-- InstanceEndEditable -->
                            </ul>
                        </div>
                        <!--SideMenu--> 
                    </div>
                    <!--pagewidth--> 

                </header>
            </div>
            <!--pagewidth--> 
        </div>
        <!-- End HeaderWrapper-->

        <div class="clear"></div>
        <div class="Break2"></div>

        <!-- ContentWrapper -->
        <div class="ContentWrapper"> 
            <!-- Content -->
            <div class="Content"> <!-- InstanceBeginEditable name="BodyRegion" -->

                <h1>Reset your password</h1>

                <!-- PageWrap -->
                <div class="PageWrap"> 

                    <!-- HalfBannerWrapper -->
                    <div class="HalfBannerWrapper"> 
                        <!--HalfBanner-->
                        <div class="HalfBanner"> 
                            <!--Slider-->
                            <div class="HalfSlider">
                                <!--SliderMain-->
                                <div class="HalfSliderMain"> 
                                    <!--HalfPage1-->
                                    <div class="HalfSliderPage" id="Halfpage1"> <img src="resources/images/SnowflakesBanner.png" alt="Snowflakes" /> </div>
                                    <!--End HalfPage1 --> 
                                </div>
                                <!--End of HalfSliderMain--> 
                            </div>
                            <!--End of HalfSlider--> 
                        </div>
                        <!--End HalfBanner--> 
                    </div>
                    <!-- End of HalfBannerWrapper -->

                    <!--contactform-->
                    <div class="contactform2">
                        <?php
                        if (strlen($resetMessage) > 0) {
                            echo sfUtils::dialogMessage("Reset Password", $resetMessage);
                        }
                        ?>
                        <?php if (isset($rs_reset)) { ?>

                            <form action="<?php echo $loginFormAction; ?>" method="POST" class="loginform" id="loginform">
                                <input type="hidden" name="reset_link" value="<?php echo $oldResetLink; ?>">
                                <span id="spryAdminPass">
                                    <span class="passwordRequiredMsg">A password is required.</span><br />
                                    <input class="inputtext2" type="password" name="password" value="<?php if (isset($Post_password)) echo $Post_password; ?>" id="Password" placeholder="Password" />

                                </span><br />

                                <span id="spryPassconfirm">
                                    <span class="confirmRequiredMsg">password confirmation is required.<br /></span>
                                    <span class="confirmInvalidMsg">The password do not match.<br /></span>
                                    <input class="inputtext2" type="password" name="password2" value="<?php if (isset($Post_password)) echo $Post_password; ?>" placeholder="Confirm Password" />
                                </span><br />
                                <label for="button"> </label>
                                <input class="NewButton" id="button" type="submit" value="Reset Password" />
                            </form>

                        <?php } else { ?>
                            <h2 class="SummaryHead">No reset link provided</h2>
                            <label class="right"><a href="ForgottenPassword.php">Forgotten password?</a></label>
                        <?php } ?>
                    </div>
                    <!--END of contactform--> 

                </div>
                <!--END of PageWrap--> 
                <!-- InstanceEndEditable -->  </div>
            <!-- end of Content --> 
        </div>
        <!-- end of ContentWrapper -->

        <footer id="SnowFooter"> 
            <!-- CMSFooterWrapper -->
            <div class="CMSFooterWrapper"> 
                <!--CopyRight-->
                <div class="CopyRight">
                    <p>&copy; 2013 Cyril Inc. All Rights Reserved. | <a href="http://cyrilinc.co.uk/Legal.html"> Legal information</a> | <a href="mailto:contactus@cyrilinc.co.uk" id="CopyRContactus">Contact Us </a>|</p>
                </div>
                <!--END of  CopyRight--> 
                <!--SocialBar-->
                <div class="SocialBar"> 
                    <!--Socialtable-->
                    <div class="Socialtable">
                        <ul>
                            <li><a href="http://cyrilinc.blogspot.co.uk/" target="_blank" title="Cyril Inc on Blogger.com"> <span class="icon-blogger blogger"></span></a></li>
                            <li><a href="https://www.facebook.com/pages/Cyril-Inc/151728454900027" target="_blank" title="Cyril Inc on Facebook"><span class="icon-facebook facebook"></span></a></li>
                            <li><a href="https://twitter.com/intent/follow?original_referer=http%3A%2F%2Ftwitter.com%2Fabout%2Fresources%2Ffollowbutton&amp;region=follow&amp;screen_name=CyrilInc&amp;source=followbutton&amp;variant=1.0" target="_blank" > <span class="icon-twitter twitter"></span></a></li>
                            <li><a href="http://delicious.com/cyrilinc" target="_blank" title="Cyril Inc on delicious"><span class="icon-delicious delicious"></span></a></li>
                            <li><a href="http://pinterest.com/cyrilinc" target="_blank" title="Cyril Inc on Pinterest"><span class="icon-pinterest pinterest"></span></a></li>
                            <li><a href="https://plus.google.com/117444390192468783291" target="_blank" title="Cyril Inc on GooglePlus"><span class="icon-googleplus googleplus"></span></a></li>
                            <li><a href="http://www.stumbleupon.com/stumbler/cyrilinc" target="_blank" title="Cyril Inc on stumbleupon"><span class="icon-stumbleupon stumbleupon"></span></a></li>
                            <li><a href="http://www.youtube.com/CyrilIncBroadcast" target="_blank" title="Cyril Inc on YouTube"><span class="icon-youtube youtube"></span></a></li>
                        </ul>
                    </div>
                    <!--End Socialtable--> 
                </div>
                <!--End SocialBar--> 

            </div>
            <!-- End of CMSFooterWrapper --> 

        </footer>
        <!-- InstanceBeginEditable name="FootEdit" --> 
        <?php if (isset($rs_reset)) { ?>
            <script type="text/javascript">
                        var sprypassword1 = new Spry.Widget.ValidationPassword("spryAdminPass", {validateOn: ["blur"]});
                        var spryconfirm1 = new Spry.Widget.ValidationConfirm("spryPassconfirm", "Password", {validateOn: ["blur", "change"]});
            </script> 
        <?php } ?>
        <!-- InstanceEndEditable -->
    </body>
    <!-- InstanceEnd --></html>
<?php
$SFconnects->close();
?>
