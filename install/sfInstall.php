<?php
/*
 * Snowflakes 2.0 - CMS & Web Publishing
 * http://cyrilinc.co.uk/snowflakes/
 * Copyright (c) 2013 Cyril inc
 * Licensed under MIT and GPL
 * Date: Thu, Dec 19 2013 22:56:31 
 */

//start the session
session_start();
//set the return URL
$MM_setup = filter_input(INPUT_POST, 'MM_setup');
$obj = new snowflakesSetUp();
if ((isset($MM_setup)) && ($MM_setup == "setupform")) {
    $return_url = "index.php";
    $LogIn_url = "../login.php";
    $Host_Name = filter_input(INPUT_POST, "hostName");
    $Database_Username = filter_input(INPUT_POST, "dbUsername");
    $Database_Password = filter_input(INPUT_POST, "dbPassword");
    $Database_Name = trim(str_replace(" ", "_", filter_input(INPUT_POST, "dbName"))); //Eliminate Database Name with spaces
    $Database_Type = filter_input(INPUT_POST, "dbType");

    $Admin_Username = filter_input(INPUT_POST, "adminUsername");
    $Admin_Password = filter_input(INPUT_POST, "adminPass");
    $Admin_Email = filter_input(INPUT_POST, "adminEmail");
    $time_zone = filter_input(INPUT_POST, "time_zone");

    $somem_Message = "<p>Host Name: " . $Host_Name . " <br>";
    $somem_Message = $somem_Message . "Database User Name:  " . $Database_Username . " <br>";
    $somem_Message = $somem_Message . "Database Password: " . $Database_Password . "<br>";
    $somem_Message = $somem_Message . "DataBase Name: " . $Database_Name . "<br>";
    $somem_Message = $somem_Message . "Admin Name: " . $Admin_Username . "<br>";
    $somem_Message = $somem_Message . "Admin Email: " . $Admin_Email . "<br>";
    $somem_Message = $somem_Message . "Admin Password: " . $Admin_Password . " <br>";

    include_once '../lib/sf.php';
    include_once '../lib/sfConnect.php';
    include_once '../config/Config.php';
    include_once '../lib/sfSetup.php';
    $url = sfUtils::curPageURL();
    $m_sfUrl = str_replace("install/sfInstall.php", "", $url);
    $somem_Message = $somem_Message . "Snowflakes Base: " . $m_sfUrl . " <br></p>";

    $obj->m_hostName = $Host_Name;
    $obj->m_dbUsername = $Database_Username;
    $obj->m_dbPassword = $Database_Password;
    $obj->m_dbName = $Database_Name;
    $obj->m_dbType = $Database_Type;
    $obj->m_adminUsername = $Admin_Username;
    $obj->m_adminPassword = $Admin_Password;
    $obj->m_adminEmail = $Admin_Email;
    $obj->m_returnUrl = $return_url;
    $obj->m_logInUrl = $LogIn_url;
    $obj->m_sfUrl = $m_sfUrl;
    $obj->m_timeZone = $time_zone;
    $obj->Setup();
}
$migrated = false;
$MM_migrate = filter_input(INPUT_POST, "MM_migrate");
$dbName = filter_input(INPUT_POST, "dbName");
$oldUpdloadDir = filter_input(INPUT_POST, "oldUpdloadDir");
$Username = filter_input(INPUT_POST, "username");
if ((isset($MM_migrate)) && ($MM_migrate == "migrateform") && ( isset($dbName))) {
    require_once '../lib/sf.php';
    require_once '../lib/sfConnect.php';
    require_once '../config/Config.php';

    $getspace = str_replace(" ", "_", $dbName);
    $Database_Name = trim($getspace);

    $config = new databaseParam('../config/config.ini');
    $SFconnects = new sfConnect($config->dbArray());
    $SFconnects->connect(); // Connect to new database
    $migrateMessage = "";
    $migrated = sfUtils::migrate($SFconnects, $Database_Name, $Username, $migrateMessage);
    $somem_Message = $migrateMessage;

    if (!sfUtils::migrateUpdir($oldUpdloadDir, '../config/config.ini')) {
        $somem_Message.=sfUtils::sfPromptMessage('Snowflakes Could not Copy/Migrate old snowflakes data from' . $oldUpdloadDir . '.<br /> Please check that the directory exists.','error');
    } else {
        $somem_Message.=sfUtils::sfPromptMessage('Snowflakes Copied/Migrated old snowflakes data from' . $oldUpdloadDir . '.','success');
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
        <title><?php
if (!$migrated || $migrated == false) {
    echo 'Migrate old Snowflakes';
} else {
    echo "Snowflakes Setup Results";
}
?></title>
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
        <link href="../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
        <link href="../resources/css/jquery-ui-1.10.4.snowflakes.css" rel="stylesheet" type="text/css" />
        <script src="../resources/Js/jquery-ui-1.10.4.snowflakes.js"></script>
        <script>
            $(function() {
                $(".dialog-message").dialog({
                    modal: true,
                    buttons: {
                        "OK": function() {
                            $(this).dialog("close");
                        }
                    }
                });
            });
            $(function() {
                $("#dialog").dialog({
                    autoOpen: false,
                    show: {
                        effect: "blind",
                        duration: 500
                    },
                    hide: {
                        effect: "explode",
                        duration: 500
                    }
                });

                $("#setupOpener").click(function() {
                    $("#dialog").dialog("open");
                });
            });

            $(function() {
                $("#setupdialog").dialog({
                    autoOpen: false,
                    height: 400,
                    show: {
                        effect: "blind",
                        duration: 500
                    },
                    hide: {
                        effect: "explode",
                        duration: 500
                    }
                });

                $("#setupLogOpener").click(function() {
                    $("#setupdialog").dialog("open");
                });
            });

        </script>
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
                                <li >
                                    <a href="../login.php" title="Login to Snowflake"> <img src="../resources/images/Icons/Lock.png" height="22" width="22" alt="Login" /> Log in </a>
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
                <h1><?php
            if ((!$migrated || $migrated == false) && strpos($obj->m_outcomeMessage, "Set Up Successful")) {
                echo 'Migrate old Snowflakes';
            } else {
                echo "Snowflakes Set up Results";
            }
?></h1>

                <!-- Break -->
                <div class="clear"></div>
                <div class="Break"></div>
                <!-- End of Break --> 

                <!-- PageWrap -->
                <div class="PageWrap">
<?php
echo '<div id="dialog" title="View snowflakes configuration">' . $somem_Message . '</div>'
 . '<div class="NewButton" id="setupOpener">View Config</div>';
if (isset($obj->m_Message) || isset($obj->m_outcomeMessage)) {
    ?>
                        <div id="setupdialog" title="Setup Log"> <?php echo $obj->m_Message . "<br/><br/>"; ?></div>
                        <div class="NewButton" id="setupLogOpener">Setup Log</div>
                        <?php
                        echo sfUtils::dialogMessage("Snowflakes Set up", $obj->m_outcomeMessage . "");
                        if (strpos($obj->m_outcomeMessage, "Set Up Unsuccessful")) {
                            echo '<div class="NewButton"><a href="' . $obj->m_returnUrl . '"> Back to Form </a></div>';
                        } else {
                            echo '<div class="NewButton"><a href="' . $obj->m_logInUrl . '"> Admin Log In  <img src="../resources/images/Icons/User.png" height="22" width="22" alt="Admin" /></a></div><br/>';
                        }
                        ?>
                        <div class="clear"></div>
                        <?php
                        if (strpos($obj->m_outcomeMessage, "Set Up Successful")) {
                            echo '<h2> OR </h2>';
                        }
                    }
                    if ((!$migrated || $migrated == false) && strpos($obj->m_outcomeMessage, "Set Up Successful")) {
                        $php_self = filter_input(INPUT_SERVER, 'PHP_SELF');
                        ?>
                        <h4> Migrate Old Snowflakes</h4>
                        <form id="installForm" action="<?php echo $php_self; ?>" method="post" class="updateForm" enctype="multipart/form-data" autocomplete="on">
                            <span id="spryDBName">
                                <span class="textfieldRequiredMsg">Database name is required.<br/>Note that the database has to be on the same host<br/></span>
                                <input name="dbName" type="text" class="inputtext2" value="" placeholder="Database name for old snowflakes" />
                            </span><br />

                            <span id="spryuploadDir">
                                <span class="textfieldRequiredMsg">Upload directory is required.<br/></span>
                                <input name="oldUpdloadDir" type="text" class="inputtext2" value="" placeholder="Upload directory for old snowflakes e.g ../../SnowflakesV1/Uploads/" />
                            </span><br />
                            <input class="NewButton" type="submit" name="submitButton" value="Migrate" />
                            <input type="hidden" name="username" value="<?php echo $Admin_Username;?>" />
                            <input type="hidden" name="MM_migrate" value="migrateform" />

                        </form>
<?php } ?>
                    <!-- Break -->
                    <div class="clear"></div>
                    <div class="Break"></div>
                    <!-- End of Break --> 
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
            var sprytextfield4 = new Spry.Widget.ValidationTextField("spryDBName", "none", {validateOn: ["blur", "change"]});
            var sprytextfield1 = new Spry.Widget.ValidationTextField("spryuploadDir", "none", {validateOn: ["blur", "change"]});
        </script>
        <!-- InstanceEndEditable -->

    </body>
    <!-- InstanceEnd -->
</html>

