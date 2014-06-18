<?php
require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';
?>
<?php
//initialize the session
if (!isset($_SESSION)) {
    session_start();
}
$php_self = sfUtils::getFilterServer( 'PHP_SELF');
// ** Logout the current user. **
$logoutAction = $php_self . "?doLogout=true";
$query_string = sfUtils::getFilterServer( 'QUERY_STRING');
if ((isset($query_string)) && ($query_string != "")) {
    $logoutAction .="&amp;" . htmlentities($query_string);
}
$doLogout = filter_input(INPUT_GET, 'doLogout');
$siteSettings = new settingsStruct('../config/config.ini');
if ((isset($doLogout)) && ($doLogout == "true")) {
    //to fully log out a visitor we need to clear the session varialbles
    $_SESSION['MM_Username'] = NULL;
    $_SESSION['MM_UserGroup'] = NULL;
    $_SESSION['PrevUrl'] = NULL;
    unset($_SESSION['MM_Username']);
    unset($_SESSION['MM_UserGroup']);
    unset($_SESSION['PrevUrl']);

    $logoutGoTo = $siteSettings->m_loginUrl;
    if ($logoutGoTo) {
        header("Location: $logoutGoTo");
        exit;
    }
}
$sfGalleryImgUrl = $siteSettings->m_sfGalleryImgUrl;
$UploadThumbUrl = $siteSettings->m_sfGalleryThumbUrl;
$imageMissing = $UploadThumbUrl . "missing_default.png";
?>
<?php
if (!isset($_SESSION)) {
    session_start();
}
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

$MM_restrictGoTo = "../login.php";
if (!((isset($_SESSION['MM_Username'])) && (sfUtils::isAuthorized("", $MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {
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
$config = new databaseParam('../config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$SFconnects->connect(); // Connect to database

$deleteId = -1; // check for delete id first before selecting
$delete = filter_input(INPUT_GET, 'deleteId');
if (isset($delete)) {
    $deleteId = $delete;
    $setDel = filter_input(INPUT_GET, 'setDel');
    $datadir = new dataDirParam("../config/config.ini");
    $eventStruct = new eventStruct();
    $eventStruct->getEventByid($SFconnects, $deleteId);
    $eventStruct->m_image_dir = $datadir->m_uploadGalleryDir;
    $eventStruct->deleteEvent($SFconnects, $setDel);
    // Check Trigger exist , if not then use manual trigger
    sfUtils::checkTrigger($SFconnects, $deleteId, 'event', "DELETE");

    if (isset($_SESSION['back'])) {
        $change = $_SESSION['back'];
        header("Location: $change");
        exit;
    }
}

$colname_EventsRs = -1;
$Eventid = filter_input(INPUT_GET, 'Eventid');
if (isset($Eventid)) {
    $colname_EventsRs = $Eventid;
}

$eventStruct = new eventStruct();
$eventStruct->getEventByid($SFconnects, $colname_EventsRs);
$totalRows_EventsRs = $SFconnects->recordCount();

$colname_rsAdmin = "-1";
if (isset($_SESSION['MM_Username'])) {
    $colname_rsAdmin = $_SESSION['MM_Username'];
}
$user = new userStruct();
$user->getUserByUsername($SFconnects, $colname_rsAdmin);
?>
<!DOCTYPE HTML>
<html ><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Cyril Inc">
        <meta name="viewport" content="width=device-width, maximum-scale = 1, minimum-scale=1" />
        <!-- InstanceBeginEditable name="doctitle" -->
        <title><?php echo $eventStruct->m_title; ?></title>
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
        <link rel="stylesheet" type="text/css" href="../resources/css/style.css"/>
        <![endif]-->

        <!--[if IEMobile]> 
           <link rel="stylesheet" type="text/css" href="../resources/css/Mobile.css"/>
        <![endif]-->
        <script type="text/javascript">
            $(document).ready(function() {
                snowflakesCount("../sse/snowflakesCount.php");
            });
        </script>
        <script src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>
        <script type="text/javascript">
            function initialize() {
<?php
$latlong = explode(",", $eventStruct->m_lat_long);
$lat = 0;
$long = 0;
foreach ($latlong as $key => $value) {
    if ($key == 0) {
        $lat = $value;
    } else if ($key == 1) {
        $long = $value;
    } else {
        break;
    }
}
?>
                var lat =<?php echo $lat; ?>;
                var long =<?php echo $long; ?>;
                var map_canvas = document.getElementById('map-canvas2');
                var latlngPos = new google.maps.LatLng(lat, long);

                var map_options = {
                    center: latlngPos,
                    zoom: 17,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                }
                var map = new google.maps.Map(map_canvas, map_options)
                // Add the marker
                var marker = new google.maps.Marker({
                    position: latlngPos,
                    map: map,
                    title: "<?php echo $eventStruct->m_title; ?>"
                });
            }
            google.maps.event.addDomListener(window, 'load', initialize);
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
                                        <li><a href="../ViewSnowflakes.php?publish=1" title="View Published flakes" class="blue" id="Snowflakes_published" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['published']); ?>"><img src="../resources/images/Icons/Publish.png" height="22" width="22" alt="View" /> Published </a></li>
                                        <li><a href="../ViewSnowflakes.php?publish=0" title="View Unublished flakes" class="blue" id="Snowflakes_unpublished" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['unpublished']); ?>"><img src="../resources/images/Icons/UnPublish.png" height="22" width="22" alt="unpublish" /> UnPublished </a></li>
                                        <li><a href="../OutputView.php" title="View output flakes" class="blue" id="Snowflakes_published2" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['published']); ?>"><img src="../resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
                                    </ul>
                                </li>
                                <li class="active" id="AtvNewButton"><a href="index.php" title="Snowflake Events" class="yellow" id="SfEvents_total" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['total']); ?>"> <img src="../resources/images/Icons/Events.png" height="22" width="22"  alt="Events" /> Events </a>
                                    <ul>
                                        <li><a href="index.php?publish=1" title="View Published Events" class="yellow" id="SfEvents_published" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['published']); ?>"><img src="../resources/images/Icons/EventsPublished.png" height="22" width="22" alt="Published" /> Published </a></li>
                                        <li><a href="index.php?publish=0" title="View Unublished Events" class="yellow" id="SfEvents_unpublished" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['unpublished']); ?>"><img src="../resources/images/Icons/EventsUnpublished.png" height="22" width="22" alt="UnPublished" /> UnPublished </a></li>
                                        <li><a href="OutputView.php" title="View output Events" class="yellow" id="SfEvents_published2" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['published']); ?>"><img src="../resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
                                    </ul>
                                </li>
                                <li><a href="../Gallery/index.php"  title="Snowflakes Gallery" class="green" id="SfGallery_total" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['total']); ?>"> <img src="../resources/images/Icons/Gallery.png" height="22" width="22"  alt="+ " /> Gallery</a>
                                    <ul>
                                        <li><a href="../Gallery/index.php?publish=1" title="View Published Gallery" class="green" id="SfGallery_published" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"><img src="../resources/images/Icons/Publish.png" height="22" width="22" alt="View" /> Published </a></li>
                                        <li><a href="../Gallery/index.php?publish=0" title="View Unublished Gallery" class="green" id="SfGallery_unpublished" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['unpublished']); ?>"><img src="../resources/images/Icons/UnPublish.png" height="22" width="22" alt="unpublish" /> UnPublished </a></li>
                                        <li><a href="../Gallery/OutputView.php" title="View Output Gallery" class="green" id="SfGallery_published2" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"><img src="../resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
                                    </ul>
                                </li>
                                <?php
                                if ($user->m_access_level == 5 || $user->m_access_level == 4) {
                                    ?>
                                    <li>
                                        <a href="../SiteSetting/index.php" title="Settings"> <img src="../resources/images/Icons/Settings.png" height="22" width="22" alt="Settings" /> Settings </a>
                                        <ul>
                                            <li><a href="../Users/index.php" title="Users" class="pink" id="SFUsers_total" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SFUsers']['total']); ?>"> <img src="../resources/images/Icons/User.png" height="22" width="22" alt="Admin" /> Admin Users </a></li> 
                                            <li><a href="../SiteSetting/LogViewer.php" title="Code Generator"> <img src="../resources/images/Icons/Log.png" height="22" width="22" alt="Log" /> Log Viewer </a></li>
                                            <li><a href="../Generator.php" title="Code Generator"> <img src="../resources/images/Icons/Key.png" height="22" width="22" alt="Code Generator" /> Code Generator </a></li>
                                            <li><a href="<?php echo $logoutAction ?>" title="Log out"> <img src="../resources/images/Icons/Logout.png"  height="22" width="22" alt="Log out" /> Log Out </a></li>
                                        </ul>
                                    </li>
                                    <?php
                                } else {
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
                <h1><?php
                    if (isset($eventStruct->m_title)) {
                        echo $eventStruct->m_title;
                    } else {
                        echo 'Snowflakes Event';
                    }
                    ?></h1>

                <?php if ($Eventid != Null) { ?>
                    <!-- Break -->
                    <div class="clear"></div>
                    <div class="Break"></div>
                    <!-- End of Break --> 

                    <!-- PageWrap -->
                    <div class="PageWrap"> 

                        <!--eventWrapper-->
                        <div class="eventWrapper2 fl"> 

                            <!--SnowflakePanel-->
                            <div class="SnowflakePanel"> 
                                <a href="EditEvents.php?Eventid=<?php echo $eventStruct->m_id; ?>" title="Edit and Publish"> 
                                    <img src="../resources/images/Icons/Edit.png" height="22" width="22" alt="Edit" /> 
                                </a> 
                                <?php
                                $notTheOwner = $colname_rsAdmin != $eventStruct->m_created_by ? true : false;
                                if ($notTheOwner == false || $user->m_access_level == 5) {
                                    $thedeletelink = "ViewEvent.php?deleteId=" . $eventStruct->m_id . "&amp;setDel=" . $notTheOwner;
                                    ?>
                                    <a onclick="deleteConfirmation('<?php echo $thedeletelink; ?>', '<?php echo htmlentities($eventStruct->m_title); ?>',<?php echo $notTheOwner == false ? "false" : "true"; ?>)"  title="Delete this event">
                                        <img src="../resources/images/Icons/Delete.png" height="22" width="22" alt="Delete" /> </a> 
                                    <?php if ($eventStruct->m_deleted) { ?>
                                        <a  href="#" onclick="deleteConfirmation('<?php echo $thedeletelink; ?>', '<?php echo htmlentities($eventStruct->m_title); ?>',<?php echo $notTheOwner == false ? "false" : "true"; ?>)"  title="Request delete by <?php echo $eventStruct->m_edited_by; ?>">
                                            R
                                            <img src="../resources/images/Icons/Delete.png" height="22" width="22" alt="Delete" />
                                        </a>
                                    <?php } ?>
                                <?php } ?>

                            </div><!--End of SnowflakePanel-->

                            <div class="Break2"></div>
                            <?php
                            $eventdate = new DateTime($eventStruct->m_event_date);
                            $enddate = new DateTime($eventStruct->m_end_date);
                            ?>
                            <!--SFEvent-->
                            <div class="SFEvent clearfix">
                                <div class="SFEvent-date">
                                    <ul class="startDate">
                                        <li class="month"> <?php echo $eventdate->format(" M"); ?></li>
                                        <li class="day"><?php echo $eventdate->format("d"); ?></li>
                                        <li class="year"><?php echo $eventdate->format(" Y"); ?></li>
                                        <li class="time"><?php echo sfUtils::toAmPmTime($eventStruct->m_event_time); ?></li>
                                    </ul>
                                    <ul class="eventTitle">
                                        <li><a href="#" rel="bookmark" title="<?php echo $eventStruct->m_title; ?>"><?php echo $eventStruct->m_title; ?></a></li>
                                        <li><a href="#" rel="bookmark" title="location"><?php echo $eventStruct->m_location; ?></a></li>
                                    </ul>
                                    <ul class="endDate">
                                        <li class="month"> <?php echo $eventdate->format(" M"); ?></li>
                                        <li class="day"><?php echo $eventdate->format("d"); ?></li>
                                        <li class="year"><?php echo $eventdate->format(" Y"); ?></li>
                                        <li class="time"><?php echo sfUtils::toAmPmTime($eventStruct->m_end_time); ?></li>
                                    </ul>
                                </div>
                                <div class="SFEvent-content"></div>
                                <div class="clear PageBreak"></div>
                                <div class="SnowflakeDescr">
                                    <div class="SnowflakeImage">
                                        <a class="colorbox" href="../Uploads/<?php echo $eventStruct->m_image_name; ?>"  onerror="this.href='<?php echo $sfGalleryImgUrl . "missing_default.png"; ?>'"  title="<?php echo $eventStruct->m_title; ?>" >
                                            <img src="../Uploads/<?php echo $eventStruct->m_image_name; ?>" onerror="this.src='<?php echo $imageMissing; ?>'"  alt="Image" />
                                        </a> 
                                    </div>
                                    <?php echo html_entity_decode($eventStruct->m_body_text); ?>
                                </div>
                                <div class="clear"></div>
                                <div id="map-canvas2"></div>
                            </div>
                            <!--SFEvent Ends--> 
                            <div class="SnowflakeDate"> Date Created |: <?php echo date(" F j, Y", $eventStruct->m_created); ?>  | By - <?php echo $eventStruct->m_created_by; ?> </div>
                            <div class="SnowflakeIt">  
                                <img src="../resources/images/Icons/Snowflakes.png" height="22" width="22" alt="flake it" /> 
                                <span class="flakeitParam" id="flakecount<?php echo $eventStruct->m_id; ?>"> <?php echo $eventStruct->m_flake_it; ?> </span>
                            </div>
                        </div>
                        <!--eventWrapper Ends--> 
                    </div>
                    <!--END of PageWrap--> 
                <?php } else {
                    ?>
                    <div class="SnowflakeHead">No Snowflake to view</div>
                <?php } ?>

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
