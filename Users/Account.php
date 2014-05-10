<?php
require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';

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
?>
<?php
$colname_userName = "-1";
$userName = filter_input(INPUT_GET, 'userName');
if (isset($userName)) {
    $colname_userName = $userName;
}
$colname_id = -1;
$userId = filter_input(INPUT_GET, 'userId');
if (isset($userId)) {
    $colname_id = $userId;
}

$config = Config::getConfig("db", '../config/config.ini');
$sqlArray = array('type' => $config['type'], 'host' => $config['host'], 'username' => $config['username'], 'password' => sfUtils::decrypt($config['password'], $config['key']), 'database' => $config['dbname']);
$SFconnects = new sfConnect($sqlArray);
$SFconnects->connect(); // Connect to database

$sfuser = new userStruct();
if (isset($userName)) {
    $sfuser->getUserByUsername($SFconnects, $userName);
} else if ($colname_id != -1) {
    $sfuser->getUserByid($SFconnects, $colname_id);
}
$levelname = sfUtils::UserLevelName($sfuser->m_access_level);

sfUtils::getAllCounts($SFconnects, $sfuser->m_username);
$activities = sfUtils::getActivities($SFconnects, $sfuser->m_username, '../config/config.ini');

$colname_rsAdmin = "-1";
if (isset($_SESSION['MM_Username'])) {
    $colname_rsAdmin = $_SESSION['MM_Username'];
}

$user = new userStruct();
$user->getUserByUsername($SFconnects, $colname_rsAdmin);

$usersnowflakeitCount = sfUtils::snowFlakeItCount($SFconnects, $sfuser->m_username);
$usereventflakeitCount = sfUtils::eventFlakeItCount($SFconnects, $sfuser->m_username);
$usergalleyflakeitCount = sfUtils::galleryFlakeItCount($SFconnects, $sfuser->m_username);

$sfuser->changeUserFlakeit($SFconnects, $usersnowflakeitCount+$usereventflakeitCount+$usergalleyflakeitCount);

$snowflakeitCount = sfUtils::snowFlakeItCount($SFconnects);
$eventflakeitCount = sfUtils::eventFlakeItCount($SFconnects);
$galleyflakeitCount = sfUtils::galleryFlakeItCount($SFconnects);

$diffsnowflakeit = $snowflakeitCount - $usersnowflakeitCount;
$diffeventflakeit = $eventflakeitCount - $usereventflakeitCount;
$diffgalleryflakeit = $galleyflakeitCount - $usergalleyflakeitCount;

$totalOtherFlakeitCount = $diffsnowflakeit + $diffeventflakeit + $diffgalleryflakeit;
?>
<!DOCTYPE HTML>
<html ><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Cyril Inc">
        <meta name="viewport" content="width=device-width, maximum-scale = 1, minimum-scale=1" />
        <!-- InstanceBeginEditable name="doctitle" -->
        <title><?php echo strlen($sfuser->m_username) > 0 ? htmlentities($sfuser->m_username) : 'No User to view'; ?></title>
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
        <script type="text/javascript" src="https://www.google.com/jsapi"></script>
        <?php ?>
        <script type="text/javascript">
            google.load("visualization", "1", {packages: ["corechart"]});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Module', 'Flake it'],
                    ['Snowflake', <?php echo $usersnowflakeitCount ? $usersnowflakeitCount : 0; ?>],
                    ['Event', <?php echo $usereventflakeitCount ? $usereventflakeitCount : 0; ?>],
                    ['Gallery', <?php echo $usergalleyflakeitCount ? $usergalleyflakeitCount : 0; ?>],
                    ['Other Flake it', <?php echo $totalOtherFlakeitCount ? $totalOtherFlakeitCount : 0; ?>]
                ]);
                var windowwidth = $(".snowflake").width();
                var options = {
                    title: '<?php echo $sfuser->m_username == $user->m_username ? 'Your' : $sfuser->m_username; ?>  \'Flake it\' Stats',
                    pieHole: 0.4,
                    height: 400,
                    width: windowwidth,
                    backgroundColor: 'transparent',
                    colors: ['#ace4f8', '#feda71', '#9dd53a', '#fafafa'],
                    pieSliceTextStyle: {
                        color: 'black'
                    },
                    titleTextStyle: {
                        color: '#fafafa'
                    },
                    legend: {position: 'top', textStyle: {color: '#fafafa'}},
                    slices: {1: {offset: 0.1},
                        2: {offset: 0.1},
                        3: {offset: 0.1},
                        4: {offset: 0.1},
                    },
                    is3D: true
                };
                var chart = new google.visualization.PieChart(document.getElementById('donutchart'));
                chart.draw(data, options);
            }
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
                                        <li><a href="../ViewSnowflakes.php?userSf=<?php echo $user->m_username; ?>" title="My Snowflakes" class="blue" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['user_total']); ?>"> <img src="../resources/images/Icons/Snowflakes.png" height="22" width="22" alt="View" /> My Snowflakes </a></li>
                                        <li><a href="../Events/index.php?userSf=<?php echo $user->m_username; ?>" title="My Events" class="yellow" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['user_total']); ?>"> <img src="../resources/images/Icons/Events.png" height="22" width="22" alt="View" /> My Events </a></li>
                                        <li><a href="../Gallery/index.php?userSf=<?php echo $user->m_username; ?>" title="My Gallery" class="green" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['user_total']); ?>"> <img src="../resources/images/Icons/Gallery.png" height="22" width="22" alt="View" /> My Gallery </a></li>
                                    </ul>
                                </li>
                                <li><a href="../ViewSnowflakes.php" title="Snowflakes" class="blue" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['total']); ?>"> <img src="../resources/images/Icons/Snowflakes.png" height="22" width="22"  alt="Snowflakes" /> Snowflakes </a>
                                    <ul>
                                      <!--<li><a href="../AddFlake.php" title="Add a New flake"> <img src="../resources/images/Icons/Add.png" height="22" width="22"  alt="Add" /> Add New Flake </a></li>-->
                                        <li><a href="../ViewSnowflakes.php?publish=1" title="View Published flakes" class="blue" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['published']); ?>"><img src="../resources/images/Icons/Publish.png" height="22" width="22" alt="View" /> Published </a></li>
                                        <li><a href="../ViewSnowflakes.php?publish=0" title="View Unublished flakes" class="blue" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['unpublished']); ?>"><img src="../resources/images/Icons/UnPublish.png" height="22" width="22" alt="unpublish" /> UnPublished </a></li>
                                        <li><a href="../OutputView.php" title="View output flakes" class="blue" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['published']); ?>"><img src="../resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
                                    </ul>
                                </li>

                                <li><a href="../Events/index.php" title="Snowflake Events" class="yellow" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['total']); ?>"> <img src="../resources/images/Icons/Events.png" height="22" width="22"  alt="Events" /> Events </a>
                                    <ul>
                                        <li><a href="../Events/index.php?publish=1" title="View Published Events" class="yellow" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['published']); ?>"> <img src="../resources/images/Icons/EventsPublished.png" height="22" width="22" alt="Published" /> Published </a></li>
                                        <li><a href="../Events/index.php?publish=0" title="View Unublished Events" class="yellow" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['unpublished']); ?>"><img src="../resources/images/Icons/EventsUnpublished.png" height="22" width="22" alt="UnPublished" /> UnPublished </a></li>
                                        <li><a href="../Events/OutputView.php" title="View output Events" class="yellow" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['published']); ?>"><img src="../resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
                                    </ul>
                                </li>
                                <li><a href="../Gallery/index.php"  title="Snowflakes Gallery" class="green" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['total']); ?>"> <img src="../resources/images/Icons/Gallery.png" height="22" width="22"  alt="+ " /> Gallery</a>
                                    <ul>
                                        <li><a href="../Gallery/index.php?publish=1" title="View Published Gallery" class="green" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"> <img src="../resources/images/Icons/GalleryPublished.png" height="22" width="22" alt="View" /> Published </a></li>
                                        <li><a href="../Gallery/index.php?publish=0" title="View Unublished Gallery" class="green" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['unpublished']); ?>"><img src="../resources/images/Icons/GalleryUnpublished.png" height="22" width="22" alt="unpublish" /> UnPublished </a></li>
                                        <li><a href="../Gallery/OutputView.php" title="View output Gallery" class="green" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"><img src="../resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
                                    </ul>
                                </li>

                                <?php
                                if ($user->m_access_level == 5 || $user->m_access_level == 4) {
                                    ?>
                                    <li>
                                        <a href="../SiteSetting/index.php" title="Settings"> <img src="../resources/images/Icons/Settings.png" height="22" width="22" alt="Settings" /> Settings </a>
                                        <ul>
                                            <li><a href="index.php" title="Users" class="pink" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SFUsers']['total']); ?>"> <img src="../resources/images/Icons/User.png" height="22" width="22" alt="Admin" /> Admin Users </a></li> 
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

                <!-- PageWrap -->
                <div class="PageWrap">
                    <?php if ($userName != Null || $userId != Null || $colname_id != -1) { ?>
                        <div class="Snowflake">
                            <!--SnowflakeDescr-->
                            <div class="SnowflakeDescr">
                                <div class="SnowflakeHead"><?php echo $sfuser->m_username; ?></div>
                                <div class="SnowflakeImage"><a class="colorbox" href="../Uploads/<?php echo $sfuser->m_image_name; ?>" title="<?php echo $sfuser->m_username; ?>" ><img src="../Uploads/<?php echo $sfuser->m_image_name; ?>"  alt="Image" /></a></div>
                                <p>&nbsp;</p>
                                <p> Access  : <?php echo $levelname; ?></p>
                                <p> Email   : <?php echo $sfuser->m_email; ?></p>
                                <p> Last in : <?php
                                    $lastin = new DateTime($sfuser->m_last_login);
                                    echo $lastin->format(" F j, Y g:h a");
                                    ?></p>
                                <p> Status  : <?php echo $sfuser->m_logged_in == 1 ? "Online" : "Offline"; ?></p>
                                <p> Flakes  : <?php echo $sfuser->m_flake_it; ?></p>

                                <!--Userside starts-->
                                <ul class="Userside">
                                    <li><span class="SummaryPara" ><?php echo $_SESSION['Snowflakes']['user_published'] ?></span> Published Snowflakes<a href="../ViewSnowflakes.php?publish=1&amp;userSf=<?php echo $sfuser->m_username; ?>"> view </a></li>
                                    <li><span class="SummaryPara" ><?php echo $_SESSION['Snowflakes']['user_unpublished']; ?></span> unPublished Snowflakes<a href="../ViewSnowflakes.php?publish=0&amp;userSf=<?php echo $sfuser->m_username; ?>"> view </a></li>
                                    <li><span class="SummaryPara" ><?php echo $_SESSION['SfEvents']['user_published']; ?></span> Published Events<a href="../Events/index.php?publish=1&amp;userSf=<?php echo $sfuser->m_username; ?>"> view </a></li>
                                    <li><span class="SummaryPara" ><?php echo $_SESSION['SfEvents']['user_unpublished']; ?></span> unPublished Events<a href="../Events/index.php?publish=0&amp;userSf=<?php echo $sfuser->m_username; ?>"> view </a></li>
                                    <li><span class="SummaryPara" ><?php echo $_SESSION['SfGallery']['user_published']; ?></span> Published Gallery<a href="../Gallery/index.php?publish=1&amp;userSf=<?php echo $sfuser->m_username; ?>"> view </a></li>
                                    <li><span class="SummaryPara" ><?php echo $_SESSION['SfGallery']['user_unpublished']; ?></span> unPublished Gallery<a href="../Gallery/index.php?publish=0&amp;userSf=<?php echo $sfuser->m_username; ?>"> view </a></li>
                                </ul><!--Userside Ends-->
                                <div class="clear"></div>
                                <div id="donutchart"></div>
                                <div class="clear"></div>
                                <div class="SummaryDescBtnDown"><span>more</span></div>
                                <!--SummaryDescription starts-->
                                <div class="SummaryDescription">
                                    <h4 class="SummaryHead">Recent Activities</h4>
                                    <?php echo $activities; ?>
                                </div><!--SummaryDescription Ends-->

                            </div><!--SnowflakeDescr Ends-->
                        </div>
                        <!-- End of Snowflake -->

                    <?php } else {
                        ?>
                        <h1>No User to view</h1>
                    <?php } ?>

                </div><!-- End of PageWrap --> 
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
        <!-- InstanceEndEditable -->
    </body>
    <!-- InstanceEnd --></html>

<?php
$SFconnects->close();
?>