<?php
/* Snowflakes 2.0 - CMS & Web Publishing
 * http://cyrilinc.co.uk/snowflakes/
 * Copyright (c) 2013 - 2014 Cyril inc
 * Licensed under MIT and GPL
 * Date: Thur, Dec 26 2013 11:33:31 -1100 
 */
// Start session.
session_start();

// Set a key, checked in mailer, prevents against spammers trying to hijack the mailer.
$security_token = $_SESSION['security_token'] = uniqid(rand());

if (!isset($_SESSION['formMessage'])) {
    $_SESSION['formMessage'] = 'Fill in the form Below to set up your Snowflake.';
}

if (!isset($_SESSION['formFooter'])) {
    $_SESSION['formFooter'] = '';
}

if (!isset($_SESSION['form'])) {
    $_SESSION['form'] = array();
}
?>
<!DOCTYPE HTML>
<html ><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Cyril Inc">
        <meta name="viewport" content="width=device-width, maximum-scale = 1, minimum-scale=1" />
        <!-- InstanceBeginEditable name="doctitle" -->
        <title>Snowflakes Setup</title>
        <!-- InstanceEndEditable -->
        <link rel="icon" href="../resources/images/favicon.ico" type="image/x-icon" />
        <link rel="shortcut icon" href="../resources/images/favicon.ico">
        <link rel="apple-touch-icon" href="../resources/images/apple-touch-icon.png">
        <link rel="stylesheet" type="text/css" href="../resources/css/Mobile.css" media="only screen and (max-width: 767px)" />
        <link rel="stylesheet" type="text/css" href="../resources/css/style.css" media="only screen and (min-width: 768px)" />
        <link rel="stylesheet" title="text/css" href="../resources/css/ColorBox.css" />
        <link rel="stylesheet" title="text/css" href="../resources/css/fontstyle.css" />
        <script type="text/javascript" src="../resources/Js/jquery-1.11.0.js"></script>
        <script type="text/javascript" src="../resources/Js/modernizr.custom.63321.js"></script>
        <script type="text/javascript" src="../resources/Js/jquery.cycle.all.js"></script>
        <script type="text/javascript" src="../resources/Js/jquery.colorbox.js"></script>
        <script type="text/javascript" src="../resources/Js/scrolltopcontrol.js"></script>
        <script type="text/javascript" src="../resources/Js/Snowflakes.js"></script>

        <!-- InstanceBeginEditable name="head" -->
        <script src="../SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
        <script src="../SpryAssets/SpryValidationPassword.js" type="text/javascript"></script>
        <script src="../SpryAssets/SpryValidationConfirm.js" type="text/javascript"></script>
        <script src="../SpryAssets/SpryValidationSelect.js" type="text/javascript"></script>
        <link href="../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
        <link href="../SpryAssets/SpryValidationPassword.css" rel="stylesheet" type="text/css" />
        <link href="../SpryAssets/SpryValidationConfirm.css" rel="stylesheet" type="text/css" />
        <link href="../SpryAssets/SpryValidationSelect.css" rel="stylesheet" type="text/css">
        <!--[if IE]>
        <link rel="stylesheet" type="text/css" href="../resources/css/style.css"/>
        <![endif]-->
        <!--[if IEMobile]> 
           <link rel="stylesheet" type="text/css" href="../resources/css/Mobile.css"/>
        <![endif]-->
        <!-- InstanceEndEditable -->
    </head>
    <body> 
        <!--HeaderWrapper-->
        <div class="HeaderWrapper"> 
            <!--pagewidth-->
            <div class="pagewidth">
                <header class="site-header">
                    <div class="pagewidth">
                        <div class="Logo"><img alt="Snowflakes" class="logo" src="../resources/images/Snowflakes.png" width="180" height="60" /></div>
                        <div class="SideMenu"> <a class="opener" id="touch-menu" href="#"><i class="icon-reorder"></i>Menu</a>
                            <ul id="primary_nav" class="primary_nav">
                                <!-- InstanceBeginEditable name="menuEdit" -->
                                <li>
                                    <a href="../login.php" title="Login to Snowflake"> <img src="../resources/images/Icons/Lock.png" height="22" width="22" alt="Login" /> Log in  </a>
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
                <h1>Set Up &amp; Install Snowflakes</h1>

                <!-- Break -->
                <div class="clear"></div>
                <div class="Break"></div>
                <!-- End of Break -->

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
                                    <div class="HalfSliderPage" id="Halfpage1"> <img src="../resources/images/SnowflakesBanner.png" alt="Snowflakes" /> </div>
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
                        <div class="message-text"><?php echo $_SESSION['formMessage']; ?></div>
                        <!-- Break -->
                        <div class="Break"></div>
                        <!-- End of Break --> 

                        <form id="installForm" action="sfInstall.php" method="post" class="updateForm" enctype="multipart/form-data" autocomplete="on">

                            <label>Connect to your server Database and Create a New Snowflake tables or migrate old version snowflakes.</label>
                            <br />
                            <br />	
                            <span id="sprytHostName">
                                <span class="textfieldRequiredMsg">Database host is required.<br/></span>
                                <input name="hostName" type="text" class="inputtext2" value="" placeholder="Host Name e.g localhost" />
                            </span><br />

                            <span id="spryDBUName">
                                <span class="textfieldRequiredMsg">Database username is required.<br/></span>
                                <input name="dbUsername" type="text" class="inputtext2" value="" placeholder="Database Username"  />
                            </span><br />

                            <span id="spryDBPass">
                                <span class="passwordRequiredMsg">Database user password is required.<br/></span>
                                <input name="dbPassword" type="password" class="inputtext2" value="" placeholder="Database Password" />
                            </span><br />

                            <span id="spryDBName">
                                <span class="textfieldRequiredMsg">Database name is required.<br/></span>
                                <input name="dbName" type="text" class="inputtext2" value="" placeholder="Database name to be created or used" />
                            </span><br />

                            <label>Database Type: </label>
                            <span id="databaseSelect">
                                <span class="selectRequiredMsg">Please select a database type.<br/></span>
                                <select name="dbType" class=" controls">
                                    <option value="MySQL">MySQL</option>
                                    <!--option value="SQLite">SQLite</option-->
                                </select>
                            </span><br/>
                            <label>Time zone:</label>
                            <span id="timezoneSelect">
                                <span class="selectRequiredMsg">Please select an a time zone.<br/></span>
                                <select class=" controls" name="time_zone">
                                    <?php
                                    require_once '../lib/sf.php';
                                    $tzlist = sfUtils::getTimeZoneList();
                                    foreach ($tzlist as $timeZone) {
                                        $selected = $timeZone == 'Europe/London' ? 'selected="selected"' : "";
                                        echo '<option value="' . $timeZone . '" ' . $selected . '>' . sfUtils::escape($timeZone) . '</option>';
                                    }
                                    ?>
                                </select>
                            </span>
                            <br/>
                            <br/>

                            <label>Choose an Administrator Name and Password to edit Snowflakes.</label>
                            <br />

                            <span id="spryAdminUName">
                                <span class="textfieldRequiredMsg">Admin username is required.<br/></span>
                                <input name="adminUsername" type="text" class="inputtext2"  value="" placeholder="Administrator Username" />
                            </span><br />

                            <span id="spryAdminEmail">
                                <span class="textfieldRequiredMsg">Admin email is required.<br/></span>
                                <span class="textfieldInvalidFormatMsg">Invalid format.<br/></span>
                                <input  name="adminEmail" class="inputtext2" type="text" value="" placeholder="Administrator's Email" />
                            </span><br />

                            <span id="spryAdminpass1">
                                <span class="passwordRequiredMsg">Admin password is required.<br/></span>
                                <input id="AdminPass" name="adminPass" type="password" class="inputtext2" value="" placeholder="Administartor Password" />
                            </span><br/>

                            <span id="spryAdminPass2">
                                <span class="confirmRequiredMsg">Confirmation password is required.<br/></span>
                                <span class="confirmInvalidMsg">The values don't match.<br/></span>
                                <input name="adminPass2" type="password" class="inputtext2"  value="" placeholder="Confirm Password" />
                            </span><br/>

                            <input class="NewButton" type="submit" name="submitButton" value="Setup CMS" />

                            <input type="hidden" name="MM_setup" value="setupform" />
                        </form>

                        <div class="form-footer">
                            <?php
                            echo $_SESSION['formFooter'];
                            unset($_SESSION['formFooter']);
                            ?>
                        </div>
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
        <script type="text/javascript">
            var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytHostName", "none", {validateOn: ["blur"]});
            var sprytextfield2 = new Spry.Widget.ValidationTextField("spryDBUName", "none", {validateOn: ["blur"]});
            var sprytextfield4 = new Spry.Widget.ValidationTextField("spryDBName", "none", {validateOn: ["blur"]});
            var sprytextfield5 = new Spry.Widget.ValidationTextField("spryAdminUName", "none", {validateOn: ["blur"]});
            var sprypassword1 = new Spry.Widget.ValidationPassword("spryAdminpass1", {validateOn: ["blur"]});
            var spryconfirm1 = new Spry.Widget.ValidationConfirm("spryAdminPass2", "AdminPass", {validateOn: ["blur", "change"]});
            var sprypassword2 = new Spry.Widget.ValidationPassword("spryDBPass", {validateOn: ["blur"]});
            var sprytextfield3 = new Spry.Widget.ValidationTextField("spryAdminEmail", "email");
            var spryselect1 = new Spry.Widget.ValidationSelect("timezoneSelect");
            var spryselect2 = new Spry.Widget.ValidationSelect("databaseSelect");
        </script>

        <!-- InstanceEndEditable -->
    </body>
    <!-- InstanceEnd --></html>