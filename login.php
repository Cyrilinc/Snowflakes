<?php
require_once 'lib/sf.php';
require_once 'lib/sfConnect.php';
require_once 'config/Config.php';

$loginMessage = "";

$config = new databaseParam('config/config.ini');
if (!$config) {
    $loginMessage.= sfUtils::sfPromptMessage("Snowflakes could not find the Configuration file. make sure your snowflakes is setup correctly.",'error');
    $setupLink = "install/index.php";
}
$SFconnects = new sfConnect($config->dbArray());
$connected = $SFconnects->connect(); // Connect to database

if(!$connected){
    $loginMessage.= sfUtils::sfPromptMessage("Snowflakes could not connect to database.".$SFconnects->getMessage(),'error');
}

if (!sfUtils::settimezone($config->m_time_zone)) {
    $loginMessage.=sfUtils::sfPromptMessage('Snowflakes could not set the site timezone.','error');
}
?>
<?php
// *** Validate request to login to this site.
if (!isset($_SESSION)) {
    session_name("Snowflakes");
    session_start();
}
$php_self = sfUtils::getFilterServer( 'PHP_SELF');
$loginFormAction = $php_self;
$accesscheck = filter_input(INPUT_GET, 'accesscheck');
if (isset($accesscheck)) {
    $_SESSION['PrevUrl'] = $accesscheck;
}
$post_username = filter_input(INPUT_POST, 'username');

if (isset($post_username)) {
    $loginUsername = $post_username;
    $password = md5(filter_input(INPUT_POST, 'password'));
    $MM_redirectLoginSuccess = "Home.php";
    $MM_redirectLoginFailed = "login.php";

    $loginUser = new userStruct();

    if ($loginUser->loginUser($SFconnects, $loginUsername, $password)) {
        sfUtils::setUserLoginOut($loginUser->m_username, true, 'config/config.ini');

        $loginStrGroup = "";

        if (PHP_VERSION >= 5.1) {
            session_regenerate_id(true);
        } else {
            session_regenerate_id();
        }
        //declare two session variables and assign them
        $_SESSION['MM_Username'] = $loginUser->m_username;
        $_SESSION['MM_UserGroup'] = $loginStrGroup;

        if (isset($_SESSION['PrevUrl']) && false) {
            $MM_redirectLoginSuccess = $_SESSION['PrevUrl'];
        }

        header("Location: " . $MM_redirectLoginSuccess);
    } else {
        $loginMessage.= sfUtils::sfPromptMessage("Login was unsuccessful! check that your Username and password is correct. <br/>" . $SFconnects->getMessage() . '<br/>','error');
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
        <title>Snowflakes Login</title>
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

        <script src="SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
        <script src="SpryAssets/SpryValidationPassword.js" type="text/javascript"></script>
        <link href="SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
        <link href="SpryAssets/SpryValidationPassword.css" rel="stylesheet" type="text/css" />
        <link href="resources/css/jquery-ui-1.10.4.snowflakes.css" rel="stylesheet" type="text/css" />
        <script src="resources/Js/jquery-ui-1.10.4.snowflakes.js"></script>
        <script>
                    $(function() {
                    $(".dialog-message").dialog({
                    modal: true,
                            buttons: {
                            "Ok": function() {
                            $(this).dialog("close");
                            }
<?php if (!$config) { ?>
                                ,
                                        "Set up Snowflakes": function() {
                                        window.location = "<?php echo $setupLink ?>";
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
                <h1>Log in</h1>
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
                                    <!--/HalfPage1 -->
                                    <!--HalfPage2-->
                                    <div class="HalfSliderPage" id="HalfPage2"> <img src="resources/images/SnowflakesBanner2.png" alt="Snowflakes" /> </div>
                                    <!--/HalfPage2 --> 
                                    <!--HalfPage3-->
                                    <div class="HalfSliderPage" id="HalfPage3"> <img src="resources/images/SnowflakesBanner3.png" alt="Snowflakes" /> </div>
                                    <!--/HalfPage3 --> 
                                    <!--HalfPage4-->
                                    <div class="HalfSliderPage" id="HalfPage4"> <img src="resources/images/SnowflakesBanner4.png" alt="Snowflakes" /> </div>
                                    <!--/HalfPage4 --> 
                                </div>
                                <!--/HalfSliderMain--> 
                            </div>
                            <!--/HalfSlider--> 
                        </div>
                        <!--/HalfBanner--> 
                    </div>
                    <!--/HalfBannerWrapper -->

                    <!--contactform-->
                    <div class="contactform2">
                        <?php
                        if (strlen($loginMessage) > 0) {
                            echo sfUtils::dialogMessage("Snowflakes Login", $loginMessage);
                        }
                        ?>
                        <form action="<?php echo $loginFormAction; ?>" method="POST" class="loginform" id="loginform">
                            <span id="sprytUserName">
                                <span class="textfieldRequiredMsg">Your Username or Email is required.<br /></span>
                                <input id="username" name="username" type="text" value="" placeholder="Username or Email" class="inputtext2" />
                            </span><br />
                            <span id="sprypassword">
                                <span class="passwordRequiredMsg">Your Password is required.<br /></span>
                                <input id="password2" name="password" type="password" value="" placeholder="Password" class="inputtext2" />
                            </span><br />

                            <label for="button"> </label>
                            <input class="NewButton" id="button" type="submit" value="Log in" />
                            <br />
                            <label class="right"><a href="ForgottenPassword.php">Forgotten password?</a></label>
                        </form>
                    </div>
                    <!--/contactform--> 

                </div>
                <!--/PageWrap--> 
                <!-- InstanceEndEditable -->  </div>
            <!--/Content --> 
        </div>
        <!--/ContentWrapper -->

        <footer id="SnowFooter"> 
            <!-- CMSFooterWrapper -->
            <div class="CMSFooterWrapper"> 
                <!--CopyRight-->
                <div class="CopyRight">
                    <p>&copy; 2013 Cyril Inc. All Rights Reserved. | <a href="http://cyrilinc.co.uk/Legal.html"> Legal information</a> | <a href="mailto:contactus@cyrilinc.co.uk" id="CopyRContactus">Contact Us </a>|</p>
                </div>
                <!--/ CopyRight--> 
                <!--SocialBar-->
                <div class="SocialBar"> 
                    <!--Socialtable-->
                    <div class="Socialtable">
                        <ul>
                            <li><a href="http://cyrilinc.blogspot.co.uk/" target="_blank" title="Cyril Inc on Blogger.com"><span class="icon-blogger blogger"></span></a></li>
                            <li><a href="https://www.facebook.com/pages/Cyril-Inc/151728454900027" target="_blank" title="Cyril Inc on Facebook"><span class="icon-facebook facebook"></span></a></li>
                            <li><a href="https://twitter.com/intent/follow?original_referer=http%3A%2F%2Ftwitter.com%2Fabout%2Fresources%2Ffollowbutton&amp;region=follow&amp;screen_name=CyrilInc&amp;source=followbutton&amp;variant=1.0" target="_blank" > <span class="icon-twitter twitter"></span></a></li>
                            <li><a href="http://delicious.com/cyrilinc" target="_blank" title="Cyril Inc on delicious"><span class="icon-delicious delicious"></span></a></li>
                            <li><a href="http://pinterest.com/cyrilinc" target="_blank" title="Cyril Inc on Pinterest"><span class="icon-pinterest pinterest"></span></a></li>
                            <li><a href="https://plus.google.com/117444390192468783291" target="_blank" title="Cyril Inc on GooglePlus"><span class="icon-googleplus googleplus"></span></a></li>
                            <li><a href="http://www.stumbleupon.com/stumbler/cyrilinc" target="_blank" title="Cyril Inc on stumbleupon"><span class="icon-stumbleupon stumbleupon"></span></a></li>
                            <li><a href="http://www.youtube.com/CyrilIncBroadcast" target="_blank" title="Cyril Inc on YouTube"><span class="icon-youtube youtube"></span></a></li>
                        </ul>
                    </div>
                    <!--/Socialtable--> 
                </div>
                <!--/SocialBar--> 

            </div>
            <!--/CMSFooterWrapper --> 

        </footer>
        <!-- InstanceBeginEditable name="FootEdit" --> 
        <script type="text/javascript">
                    var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytUserName", "none", {validateOn: ["blur"]});
                    var sprypassword1 = new Spry.Widget.ValidationPassword("sprypassword", {validateOn: ["blur"]});
        </script> 
        <!-- InstanceEndEditable -->
    </body>
    <!-- InstanceEnd --></html>
<?php
$SFconnects->close();
?>
