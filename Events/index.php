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
$php_self = filter_input(INPUT_SERVER, 'PHP_SELF');
// ** Logout the current user. **
$logoutAction = $php_self . "?doLogout=true";
$query_string = filter_input(INPUT_SERVER, 'QUERY_STRING');
if ((isset($query_string)) && ($query_string != "")) {
    $logoutAction .="&amp;" . htmlentities($query_string);
}
$settingsConfig = Config::getConfig("settings", '../config/config.ini');
$doLogout = filter_input(INPUT_GET, 'doLogout');
if ((isset($doLogout)) && ($doLogout == "true")) {
    //to fully log out a visitor we need to clear the session varialbles
    $_SESSION['MM_Username'] = NULL;
    $_SESSION['MM_UserGroup'] = NULL;
    $_SESSION['PrevUrl'] = NULL;
    unset($_SESSION['MM_Username']);
    unset($_SESSION['MM_UserGroup']);
    unset($_SESSION['PrevUrl']);

    $logoutGoTo = $settingsConfig['loginUrl'];
    if ($logoutGoTo) {
        header("Location: $logoutGoTo");
        exit;
    }
}
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

$currentPage = filter_input(INPUT_SERVER, 'PHP_SELF');
$_SESSION['back'] = htmlentities(filter_input(INPUT_SERVER, 'REQUEST_URI'));

$maxRows_EventsRs = 10;
$pageNum_EventsRs = 0;
$EventsRs = filter_input(INPUT_GET, 'pageNum_EventsRs');
if (isset($EventsRs)) {
    $pageNum_EventsRs = $EventsRs;
}
$startRow_EventsRs = $pageNum_EventsRs * $maxRows_EventsRs;

$config = new databaseParam('../config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$SFconnects->connect(); // Connect to database

$query_EventsRs = "SELECT * FROM snowflakes_events";
$publish = -1;
$publishRs = filter_input(INPUT_GET, 'publish');
if (isset($publishRs)) {
    $publish = $publishRs;
    $query_EventsRs.=" WHERE publish=" . $publish;
}
$publishStatus = sfUtils::getPublishStatus($publish);

$userSnowflakes = filter_input(INPUT_GET, 'userSf');
if (isset($userSnowflakes)) {
    $publish = $userSnowflakes;
    $prefix = strpos($query_EventsRs, " WHERE ") === false ? " WHERE " : " AND ";
    $query_EventsRs.=$prefix . 'created_by="' . sfUtils::escape($userSnowflakes) . '"';

    $userSnowflakes.="'s ";
}

$query_EventsRs.=" ORDER BY event_date DESC";
$query_limit_EventsRs = sprintf("%s LIMIT %d, %d", $query_EventsRs, $startRow_EventsRs, $maxRows_EventsRs);
$SFconnects->fetch($query_limit_EventsRs);
$row_EventsRs = $SFconnects->getResultArray();
$row_EventsRsCount = $SFconnects->recordCount();
$eventStructList = array();
foreach ($row_EventsRs as $key => $value) {
    $eventStructList[$key] = new eventStruct();
    $eventStructList[$key]->populate($value);
    //$eventStructList[$key]->printEvents();
}

$total_EventsRs = filter_input(INPUT_GET, 'totalRows_EventsRs');
if (isset($total_EventsRs)) {
    $totalRows_EventsRs = $total_EventsRs;
} else {
    $sql = str_replace("SELECT * FROM", "SELECT COUNT(id) count FROM", $query_EventsRs);
    $SFconnects->fetch($sql);
    $result = $SFconnects->getResultArray();
    $totalRows_EventsRs = $result[0]['count'];
}
$totalPages_EventsRs = ceil($totalRows_EventsRs / $maxRows_EventsRs) - 1;

$colname_rsAdmin = "-1";
if (isset($_SESSION['MM_Username'])) {
    $colname_rsAdmin = $_SESSION['MM_Username'];
}

$user = new userStruct();
$user->getUserByUsername($SFconnects, $colname_rsAdmin);
$sfuser = $colname_rsAdmin . "'s ";
if ($userSnowflakes === $sfuser) {
    $userSnowflakes = "Your ";
}

$queryString_EventsRs = "";
if (!empty($query_string)) {
    $params = explode("&", $query_string);
    $newParams = array();
    foreach ($params as $param) {
        if (stristr($param, "pageNum_EventsRs") == false &&
                stristr($param, "totalRows_EventsRs") == false) {
            array_push($newParams, $param);
        }
    }
    if (count($newParams) != 0) {
        $queryString_EventsRs = "&" . htmlentities(implode("&", $newParams));
    }
}
$queryString_EventsRs = sprintf("&totalRows_EventsRs=%d%s", $totalRows_EventsRs, $queryString_EventsRs);
?>
<!DOCTYPE HTML>
<html ><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Cyril Inc">
        <meta name="viewport" content="width=device-width, maximum-scale = 1, minimum-scale=1" />
        <!-- InstanceBeginEditable name="doctitle" -->
        <title>Snowflakes | <?php echo $userSnowflakes . $publishStatus; ?> Events</title>
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
                                        <li><a href="../Gallery/index.php?publish=0" title="View Unublished Gallery" class="green" id="SfGallery_published" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"><img src="../resources/images/Icons/UnPublish.png" height="22" width="22" alt="unpublish" /> UnPublished </a></li>
                                        <li><a href="../Gallery/OutputView.php" title="View Output Gallery" class="green" id="SfGallery_published2" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"><img src="../resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
                                    </ul>
                                </li>

                                <?php
                                if ($user->m_access_level == 5 || $user->m_access_level == 4) {
                                    ?>
                                    <li>
                                        <a href="../SiteSetting/index.php" title="Settings"> <img src="../resources/images/Icons/Settings.png" height="22" width="22" alt="Settings" /> Settings </a>
                                        <ul>
                                            <li><a href="../Users/index.php" title="Users" class="pink" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SFUsers']['total']); ?>"> <img src="../resources/images/Icons/User.png" height="22" width="22" alt="Admin" /> Admin Users </a></li> 
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
                    echo $userSnowflakes;
                    if (strlen($publishStatus)) {
                        echo $publishStatus;
                    }
                    ?> Events [<?php echo $totalRows_EventsRs; ?>]</h1>
                <div class="Search">
                    <form id="search-box" action="../searchResult.php" method="post">
                        <select name="filter" class="controls">
                            <option value="Whole site" >Whole Site</option>
                            <option value="snowflakes">Snowflakes</option>
                            <option value="events" selected="selected">Events</option>
                            <option value="gallery">Gallery</option>
                            <option value="users">Users</option>
                        </select>
                        <input type="search" name="searchString" class="sinputtext controls" value="<?php if (isset($_POST["searchString"])) echo $_POST["searchString"]; ?>" placeholder="Search ..."> 
                        <input type="hidden" name="search" value="searchform">
                        <br />
                    </form>
                </div>
                <div class="NewButton"><a href="CreateEvents.php" title="Add a New Event"> <img src="../resources/images/Icons/Add.png" height="22" width="22"  alt="+ " /> Create Event </a></div>

                <!-- Break -->
                <div class="clear"></div>
                <div class="Break"></div>
                <!-- End of Break --> 

                <!-- PageWrap -->
                <div class="PageWrap">

                    <?php if ($pageNum_EventsRs > 0) { // Show if not first page   ?>
                        <div class="smallNewButton"><a href="<?php printf("%s?pageNum_EventsRs=%d%s", $currentPage, 0, $queryString_EventsRs); ?>">First</a></div>
                        <div class="smallNewButton"><a href="<?php printf("%s?pageNum_EventsRs=%d%s", $currentPage, max(0, $pageNum_EventsRs - 1), $queryString_EventsRs); ?>">Previous</a></div>
                    <?php } // Show if not first page   ?>
                    <?php if ($pageNum_EventsRs < $totalPages_EventsRs) { // Show if not last page   ?>
                        <div class="smallNewButton"><a href="<?php printf("%s?pageNum_EventsRs=%d%s", $currentPage, min($totalPages_EventsRs, $pageNum_EventsRs + 1), $queryString_EventsRs); ?>">Next</a></div>
                        <div class="smallNewButton"><a href="<?php printf("%s?pageNum_EventsRs=%d%s", $currentPage, $totalPages_EventsRs, $queryString_EventsRs); ?>">Last</a></div>
                    <?php } // Show if not last page   ?>
                    <div class=" clear Break2"></div>

                    <?php
                    if ($totalRows_EventsRs > 0) {
                        $i = 0;
                        do {
                            $eventdate = new DateTime($eventStructList[$i]->m_event_date);
                            $enddate = new DateTime($eventStructList[$i]->m_end_date);
                            ?>
                            <!--eventWrapper-->
                            <div class="eventWrapper fl"> 
                                <!--SnowflakePanel-->
                                <div class="SnowflakePanel"> <a href="ViewEvent.php?Eventid=<?php echo $eventStructList[$i]->m_id; ?>" title="View this Event"> <img src="../resources/images/Icons/View.png" height="22" width="22" alt="Edit" /> </a>  
                                </div>
                                <!--End of SnowflakePanel-->
                                <!--SFEvent-->
                                <div class="SFEvent">
                                    <div class="SFEvent-date">
                                        <ul class="startDate">
                                            <li class="month"><?php echo $eventdate->format(" M"); ?></li>
                                            <li class="day"><?php echo $eventdate->format("d"); ?></li>
                                            <li class="year"><?php echo $eventdate->format(" Y"); ?></li>
                                            <li class="time"><?php echo sfUtils::toAmPmTime($eventStructList[$i]->m_event_time); ?></li>
                                        </ul>

                                        <ul class="eventTitle">
                                            <li><a href="ViewEvent.php?Eventid=<?php echo $eventStructList[$i]->m_id; ?>" rel="bookmark" title="<?php echo $eventStructList[$i]->m_title; ?>"><?php echo $eventStructList[$i]->m_title; ?></a></li>
                                            <li><a href="ViewEvent.php?Eventid=<?php echo $eventStructList[$i]->m_id; ?>" rel="bookmark" title="location"><?php echo $eventStructList[$i]->m_location; ?></a></li>
                                        </ul>

                                        <ul class="endDate">
                                            <li class="month"> <?php echo $enddate->format(" M"); ?></li>
                                            <li class="day"><?php echo $enddate->format("d"); ?></li>
                                            <li class="year"><?php echo $enddate->format(" Y"); ?></li>
                                            <li class="time"><?php echo sfUtils::toAmPmTime($eventStructList[$i]->m_end_time); ?></li>
                                        </ul>

                                    </div>
                                </div>
                                <!--SFEvent Ends--> 
                                <div class="clear"></div>
                                <div class="SnowflakeDate"> Posted |: <?php echo date(" F j, Y", $eventStructList[$i]->m_created); ?>  | By - <?php echo $eventStructList[$i]->m_created_by; ?> </div>
                                <div class="SnowflakeIt"> flakes <div class="flakeitParam" id="flakecount<?php echo $eventStructList[$i]->m_id; ?>"> <?php echo $eventStructList[$i]->m_flake_it; ?> </div></div>
                            </div>
                            <!--eventWrapper Ends-->
                            <?php
                            $i++;
                        } while ($i < count($eventStructList));
                        ?>

                    <?php } else { ?> 
                        <h4 class="SummaryHead">There are no Events </h4>
                        <div class="NewButton"><a href="CreateEvents.php"> Create One <img src="../resources/images/Icons/Add.png" height="22" width="22" alt="Create" /></a></div>
                    <?php } ?> 
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
        <!-- InstanceBeginEditable name="FootEdit" --> <!-- InstanceEndEditable -->
    </body>
    <!-- InstanceEnd --></html>
<?php
$SFconnects->close();
?>
