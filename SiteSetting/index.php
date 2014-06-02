<?php
require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';
require_once '../lib/sfImageProcessor.php';
?>
<?php
//initialize the session
if (!isset($_SESSION))
{
    session_start();
}
$php_self = filter_input(INPUT_SERVER, 'PHP_SELF');
// ** Logout the current user. **
$logoutAction = $php_self . "?doLogout=true";
$query_string = filter_input(INPUT_SERVER, 'QUERY_STRING');
if ((isset($query_string)) && ($query_string != ""))
{
    $logoutAction .="&amp;" . htmlentities($query_string);
}
$doLogout = filter_input(INPUT_GET, 'doLogout');
$settingsConfig = Config::getConfig("settings", '../config/config.ini');
if ((isset($doLogout)) && ($doLogout == "true"))
{
    //to fully log out a visitor we need to clear the session varialbles
    $_SESSION['MM_Username'] = NULL;
    $_SESSION['MM_UserGroup'] = NULL;
    $_SESSION['PrevUrl'] = NULL;
    unset($_SESSION['MM_Username']);
    unset($_SESSION['MM_UserGroup']);
    unset($_SESSION['PrevUrl']);

    $logoutGoTo = $settingsConfig['loginUrl'];
    if ($logoutGoTo)
    {
        header("Location: $logoutGoTo");
        exit;
    }
}
?>
<?php
if (!isset($_SESSION))
{
    session_start();
}
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

$MM_restrictGoTo = "../login.php";
if (!((isset($_SESSION['MM_Username'])) && (sfUtils::isAuthorized("", $MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup']))))
{
    $MM_qsChar = "?";
    $MM_referrer = $php_self;
    if (strpos($MM_restrictGoTo, "?"))
        $MM_qsChar = "&";
    if (isset($query_string) && strlen($query_string) > 0)
        $MM_referrer .= "?" . $query_string;
    $MM_restrictGoTo = $MM_restrictGoTo . $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
    header("Location: " . $MM_restrictGoTo);
    exit;
}
?>
<?php
$editFormAction = $php_self;
if (isset($query_string))
{
    $editFormAction .= "?" . htmlentities($query_string);
}
$Message = "";
$config = new databaseParam('../config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$connected = $SFconnects->connect(); // Connect to database

if(!$connected){
    $Message.= sfUtils::sfPromptMessage("Snowflakes could not connect to database.".$SFconnects->getMessage(),'error');
}

$MM_update = filter_input(INPUT_POST, 'MM_update');
if ((isset($MM_update)) && ($MM_update == "form1"))
{
    $updateSQL = 'UPDATE snowflakes_settings ' .
            'SET result_url="' . sfUtils::escape($_POST['result_url']) . '",' .
            'out_url="' . sfUtils::escape($_POST['out_url']) . '",' .
            'events_result_url="' . sfUtils::escape($_POST['events_result_url']) . '",' .
            'events_output_url="' . sfUtils::escape($_POST['events_output_url']) . '",' .
            'gallery_result_url="' . sfUtils::escape($_POST['gallery_result_url']) . '",' .
            'gallery_out_url="' . sfUtils::escape($_POST['gallery_out_url']) . '",' .
            'max_upload_size=' . $_POST['max_upload_size'] . ',' .
            'time_zone="' . $_POST['time_zone'] . '" ' .
            'WHERE setting_id=' . $_POST['Settingid'];

    if (!$SFconnects->execute($updateSQL))
    {
        $Message.=$SFconnects->getMessage();
    }
    else
    {
        $settingsStruct = new settingsStruct();
        $settingsStruct->init('../config/config.ini');
        $settingsStruct->SetsnowflakesResultUrl($_POST['result_url']);
        $settingsStruct->SetsnowflakesOutUrl($_POST['out_url']);
        $settingsStruct->SeteventsResultUrl($_POST['events_result_url']);
        $settingsStruct->SeteventsOutputUrl($_POST['events_output_url']);
        $settingsStruct->SetgalleryResultUrl($_POST['gallery_result_url']);
        $settingsStruct->SetgalleryOutUrl($_POST['gallery_out_url']);
        $settingsStruct->SetmaxImageSize($_POST['max_upload_size'] . 'MB');
        $settingsStruct->SettimeZone($_POST['time_zone']);
        $settingsStruct->setConfigItems('../config/config.ini');

        if (!sfUtils::settimezone($config->m_time_zone))
        {
            $loginMessage.=sfUtils::sfPromptMessage('Snowflakes could not set the site timezone.', 'error');
        }
    }
}

$colname_rsAdmin = "-1";
if (isset($_SESSION['MM_Username']))
{
    $colname_rsAdmin = $_SESSION['MM_Username'];
}

$user = new userStruct();
$user->getUserByUsername($SFconnects, $colname_rsAdmin);

sfUtils::getAllCounts($SFconnects, $user->m_username);

$query_SiteSettings = "SELECT sf_url, result_url, out_url, events_result_url, events_output_url, gallery_result_url, gallery_out_url, max_upload_size,time_zone FROM snowflakes_settings";
$SFconnects->fetch($query_SiteSettings);
$row_SiteSettings = $SFconnects->getResultArray();

$Message .= filter_input(INPUT_GET, 'Message');
$MainTain = filter_input(INPUT_GET, 'mt');
if (isset($MainTain) && $MainTain == "True")
{

    $cleaned = sfImageProcessor::cleanUploadDir($SFconnects, '../config/config.ini');
    if ($cleaned >= 1)
    {
        $Message .= sfUtils::sfPromptMessage("Cleaned $cleaned images.", 'success');
    }
    else
    {
        $Message.=sfUtils::sfPromptMessage("Cleaned $cleaned images.", 'error');
    }
    $resized = sfImageProcessor::resizeGalleryImages($SFconnects, '../config/config.ini');
    if ($resized >= 1)
    {
        $Message .= sfUtils::sfPromptMessage("$resized images re-sized.", 'success');
    }
    else
    {
        $Message .= sfUtils::sfPromptMessage("$resized images re-sized.", 'error');
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
        <title>Snowflakes | Settings</title>
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
        <!--[if IE]>
        <link rel="stylesheet" type="text/css" href="resources/css/style.css"/>
        <![endif]-->

        <!--[if IEMobile]> 
           <link rel="stylesheet" type="text/css" href="resources/css/Mobile.css"/>
        <![endif]-->

        <script type="text/javascript">
            $(document).ready(function() {
                snowflakesCount("../sse/snowflakesCount.php");
            });
        </script>
        <link href="../resources/css/jquery-ui-1.10.4.snowflakes.css" rel="stylesheet" type="text/css" />
        <script src="../resources/Js/jquery-ui-1.10.4.snowflakes.js"></script>
        <script>
            $(function() {
                $(".dialog-message").dialog({
                    modal: true,
                    buttons: {
                        "Ok": function() {
                            $(this).dialog("close");
                        }
                    }
                });
            });
        </script>

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
                                <li><a href="../Home.php" title="Snowflake Home"> <img src="../resources/images/Icons/Home.png" height="22" width="22"  alt="Add" /> Home </a>
                                    <ul>
                                        <li><a href="../ViewSnowflakes.php?userSf=<?php echo $user->m_username; ?>" title="My Snowflakes" class="blue" id="Snowflakes_user_total" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['user_total']); ?>"> <img src="../resources/images/Icons/Snowflakes.png" height="22" width="22" alt="View" /> My Snowflakes </a></li>
                                        <li><a href="../Events/index.php?userSf=<?php echo $user->m_username; ?>" title="My Events" class="yellow" id="SfEvents_user_total" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['user_total']); ?>"> <img src="../resources/images/Icons/Events.png" height="22" width="22" alt="View" /> My Events </a></li>
                                        <li><a href="../Gallery/index.php?userSf=<?php echo $user->m_username; ?>" title="My Gallery" class="green" id="SfGallery_user_total" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['user_total']); ?>"> <img src="../resources/images/Icons/Gallery.png" height="22" width="22" alt="View" /> My Gallery </a></li>
                                    </ul>
                                </li>
                                <li><a href="../ViewSnowflakes.php" title="Snowflakes" class="blue" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['total']); ?>"> <img src="../resources/images/Icons/Snowflakes.png" height="22" width="22"  alt="Snowflakes" /> Snowflakes </a>
                                    <ul>
                                     <!--<li><a href="../AddFlake.php" title="Add a New flake"> <img src="../resources/images/Icons/Add.png" height="22" width="22"  alt="Add" /> Add New Flake </a></li>-->
                                        <li><a href="../ViewSnowflakes.php?publish=1" title="View Published flakes" class="blue" id="Snowflakes_published" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['published']); ?>"> <img src="../resources/images/Icons/Publish.png" height="22" width="22" alt="View" /> Published </a></li>
                                        <li><a href="../ViewSnowflakes.php?publish=0" title="View Unublished flakes" class="blue" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['unpublished']); ?>"><img src="../resources/images/Icons/UnPublish.png" height="22" width="22" alt="unpublish" /> UnPublished </a></li>
                                        <li><a href="../OutputView.php" title="View output flakes" class="blue" id="Snowflakes_published2" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['published']); ?>"><img src="../resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
                                    </ul>
                                </li>
                                <li><a href="../Events/index.php" title="Snowflake Events" class="yellow" id="SfEvents_total" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['total']); ?>"> <img src="../resources/images/Icons/Events.png" height="22" width="22"  alt="Events" /> Events </a>
                                    <ul>
                                        <li><a href="../Events/index.php?publish=1" title="View Published Events" class="yellow" id="SfEvents_published" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['published']); ?>"><img src="../resources/images/Icons/EventsPublished.png" height="22" width="22" alt="Published" /> Published </a></li>
                                        <li><a href="../Events/index.php?publish=0" title="View Unublished Events" class="yellow" id="SfEvents_unpublished" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['unpublished']); ?>"><img src="../resources/images/Icons/EventsUnpublished.png" height="22" width="22" alt="UnPublished" /> UnPublished </a></li>
                                        <li><a href="../Events/OutputView.php" title="View output Events" class="yellow" id="SfEvents_published2" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['published']); ?>"><img src="../resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
                                    </ul>
                                </li>
                                <li><a href="../Gallery/index.php"  title="Snowflakes Gallery" class="green" id="SfGallery_total" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['total']); ?>"> <img src="../resources/images/Icons/Gallery.png" height="22" width="22"  alt="+ " /> Gallery</a>
                                    <ul>
                                        <li><a href="index.php?publish=1" title="View Published Gallery" class="green" id="SfGallery_published" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"><img src="../resources/images/Icons/GalleryPublished.png" height="22" width="22" alt="View" /> Published </a></li>
                                        <li><a href="index.php?publish=0" title="View Unublished Gallery" class="green" id="SfGallery_published" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"><img src="../resources/images/Icons/GalleryUnpublished.png" height="22" width="22" alt="unpublish" /> UnPublished </a></li>
                                        <li><a href="OutputView.php" title="View Output Gallery" class="green" id="SfGallery_published2" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"><img src="../resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
                                    </ul>
                                </li>
                                <?php
                                if ($user->m_access_level == 5 || $user->m_access_level == 4)
                                {
                                    ?>
                                    <li class="active" id="AtvNewButton">
                                        <a href="../SiteSetting/index.php" title="Settings"> <img src="../resources/images/Icons/Settings.png" height="22" width="22" alt="Settings" /> Settings </a>
                                        <ul>
                                            <li><a href="../Users/index.php" title="Users" class="pink" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SFUsers']['total']); ?>"> <img src="../resources/images/Icons/User.png" height="22" width="22" alt="Admin" /> Admin Users </a></li> 
                                            <li><a href="LogViewer.php" title="Code Generator"> <img src="../resources/images/Icons/Log.png" height="22" width="22" alt="Log" /> Log Viewer </a></li>
                                            <li><a href="../Generator.php" title="Code Generator"> <img src="../resources/images/Icons/Key.png" height="22" width="22" alt="Code Generator" /> Code Generator </a></li>
                                            <li><a href="<?php echo $logoutAction ?>" title="Log out"> <img src="../resources/images/Icons/Logout.png"  height="22" width="22" alt="Log out" /> Log Out </a></li>
                                        </ul>
                                    </li>
    <?php
}
else
{
    ?>
                                    <li>
                                        <a href="<?php echo $logoutAction ?>" title="Log out"> <img src="../resources/images/Icons/Logout.png"  height="22" width="22" alt="Log out" /> Log Out </a>
                                    </li>
    <?php
}
?>
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
                <h1>Snowflakes Settings</h1>
                <div class="NewButton"><a href="index.php?mt=True" title="Maintain snowflakes"> <img src="../resources/images/Icons/Settings.png" height="22" width="22" alt="Maintenance" />Maintenance</a></div>
                <!-- Break -->
                <div class="clear"></div>
                <div class="Break"></div>
                <!-- End of Break --> 

                <!-- PageWrap -->
                <div class="PageWrap">
<?php
if (strlen($Message) > 0)
{
    echo sfUtils::dialogMessage("Settings/Maintenance", $Message);
}
?>
                    <!--contactform-->
                    <div class="contactform"> 
                        <form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="installForm">
                            <fieldset>
                                <legend>Snowfalkes single view output URL</legend>
                                <input class="inputtext controls" type="url" name="result_url" value="<?php echo $row_SiteSettings[0]['result_url']; ?>" placeholder="Snowfalkes output URL" />
                            </fieldset>

                            <fieldset>
                                <legend>Snowfalkes all output  URL</legend>
                                <input class="inputtext controls" type="url" name="out_url" value="<?php echo $row_SiteSettings[0]['out_url']; ?>" placeholder="Snowfalkes output URL" />
                            </fieldset>

                            <fieldset>
                                <legend>Snowfalkes single event output URL</legend>
                                <input class="inputtext controls" type="url" name="events_result_url" value="<?php echo $row_SiteSettings[0]['events_result_url']; ?>" placeholder="Snowfalkes Event output URL" />
                            </fieldset>

                            <fieldset>
                                <legend>Snowfalkes all event output URL</legend>
                                <input class="inputtext controls" type="url" name="events_output_url" value="<?php echo $row_SiteSettings[0]['events_output_url']; ?>" placeholder="Snowfalkes Event output URL" />
                            </fieldset>

                            <fieldset>
                                <legend>Snowfalkes gallery output URL</legend>
                                <input class="inputtext controls" type="url" name="gallery_result_url" value="<?php echo $row_SiteSettings[0]['gallery_result_url']; ?>" placeholder="Snowfalkes Gallery output URL" />
                            </fieldset>

                            <fieldset>
                                <legend>Snowfalkes all gallery output URL</legend>
                                <input class="inputtext controls" type="url" name="gallery_out_url" value="<?php echo $row_SiteSettings[0]['gallery_out_url']; ?>" placeholder="Snowfalkes Gallery output URL" />
                            </fieldset>

                            <fieldset>
                                <legend>Set the maximum image upload size in MB</legend>
                                <input class="inputtext controls" type="text" name="max_upload_size" value="<?php echo $row_SiteSettings[0]['max_upload_size']; ?>" placeholder="Snowfalkes Gallery Image Size in MB" />
                            </fieldset>

                            <fieldset>
                                <legend>Site Time zone</legend>
                                <select class="inputtext controls" name="time_zone">
<?php
$tzlist = sfUtils::getTimeZoneList();
foreach ($tzlist as $timeZone)
{
    $selected = $row_SiteSettings[0]['time_zone'] == $timeZone ? 'selected="selected"' : "";
    echo '<option value="' . $timeZone . '" ' . $selected . '>' . sfUtils::escape($timeZone) . '</option>';
}
?>                                   
                                </select>
                            </fieldset>

                            <input class="NewButton" type="submit" value="Update" />
                            <input name="Settingid" type="hidden" id="Settingid" value="1" />
                            <input type="hidden" name="MM_update" value="form1">
                        </form>
                        <p>&nbsp;</p>
                    </div>
                    <!--End of contactform--> 
                </div>
                <!-- End of PageWrap --> 

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
        <!-- InstanceBeginEditable name="FootEdit" --> <!-- InstanceEndEditable -->
    </body>
    <!-- InstanceEnd --></html>
<?php
$SFconnects->close();
?>
