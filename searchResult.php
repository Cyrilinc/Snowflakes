<?php
require_once 'lib/sf.php';
require_once 'lib/sfConnect.php';
require_once 'config/Config.php';

//The upload directory
$settingsConfig = Config::getConfig("settings", 'config/config.ini');
$UploadDir = $settingsConfig['uploadGalleryDir'];
//The upload Image directory
$UploadImgUrl = $settingsConfig['m_sfGalleryImgUrl'];
$UploadThumbUrl = $settingsConfig['m_sfGalleryThumbUrl'];
$imageMissing = $UploadThumbUrl . "missing_default.png";

//initialize the session
if (!isset($_SESSION)) {
    session_start();
}
$php_self = filter_input(INPUT_SERVER, 'PHP_SELF');
// ** Logout the current user. **/
$logoutAction = $php_self . "?doLogout=true";
$query_string = filter_input(INPUT_SERVER, 'QUERY_STRING');
if ((isset($query_string)) && ($query_string != "")) {
    $logoutAction .="&amp;" . htmlentities($query_string);
}
$doLogout = filter_input(INPUT_GET, 'doLogout');
if ((isset($doLogout)) && ($doLogout == "true")) {
    $username = $_SESSION['MM_Username'];
    sfUtils::setUserLoginOut($username, false, 'config/config.ini');
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

if (!isset($_SESSION)) {
    session_start();
}
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";
$MM_restrictGoTo = "login.php";
if (!((isset($_SESSION['MM_Username'])) && (sfUtils::isAuthorized("", $MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {
    $MM_qsChar = "?";
    $MM_referrer = $php_self;
    if (strpos($MM_restrictGoTo, "?")) {
        $MM_qsChar = "&";
    }
    if (isset($query_string) && strlen($query_string) > 0) {
        $MM_referrer .= "?" . $query_string;
    }
    $MM_restrictGoTo = $MM_restrictGoTo . $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
    header("Location: " . $MM_restrictGoTo);
    exit;
}

$config = Config::getConfig("db", 'config/config.ini');
$sqlArray = array('type' => $config['type'], 'host' => $config['host'], 'username' => $config['username'], 'password' => sfUtils::decrypt($config['password'], $config['key']), 'database' => $config['dbname']);
$SFconnects = new sfConnect($sqlArray);
$SFconnects->connect(); // Connect to database

$colname_rsAdmin = "-1";
if (isset($_SESSION['MM_Username'])) {
    $colname_rsAdmin = $_SESSION['MM_Username'];
    sfUtils::setUserLoginOut($colname_rsAdmin, true, 'config/config.ini');
}

$user = new userStruct();
$user->getUserByUsername($SFconnects, $colname_rsAdmin);
sfUtils::getAllCounts($SFconnects, $user->m_username);

$post_search = filter_input(INPUT_POST, 'search');
$post_filter = filter_input(INPUT_POST, 'filter');
$post_searchStr = filter_input(INPUT_POST, 'searchString');
if ((isset($post_search)) && ($post_search == "searchform")) {
    $search = sfUtils::searchString($SFconnects, $post_searchStr, $post_filter);
}
?>
<!DOCTYPE HTML>
<html lang="en" ><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Cyril Inc">
        <meta name="viewport" content="width=device-width, maximum-scale = 1, minimum-scale=1" />
        <!-- InstanceBeginEditable name="doctitle" -->
        <title>Snowflakes | Search result</title>
        <!-- InstanceEndEditable -->
        <link rel="icon" href="resources/images/favicon.ico" type="image/x-icon" />
        <link rel="shortcut icon" href="resources/images/favicon.ico">
        <link rel="apple-touch-icon" href="resources/images/apple-touch-icon.png">
        <link rel="stylesheet" type="text/css" href="resources/css/Mobile.css" media="only screen and (max-width: 767px)" />
        <link rel="stylesheet" type="text/css" href="resources/css/style.css" media="only screen and (min-width: 768px)" />
        <link rel="stylesheet" title="text/css" href="resources/css/ColorBox.css" />
        <script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
        <script src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
        <script type="text/javascript" src="resources/Js/modernizr.custom.63321.js"></script>
        <script type="text/javascript" src="resources/Js/jquery.cycle.all.js"></script>
        <script type="text/javascript" src="resources/Js/jquery.colorbox.js"></script>
        <script type="text/javascript" src="resources/Js/scrolltopcontrol.js"></script>
        <script type="text/javascript" src="resources/Js/Snowflakes.js"></script>

        <!-- InstanceBeginEditable name="head" -->
        <link rel="stylesheet" type="text/css" href="resources/css/stapel.css" />
        <script type="text/javascript" src="resources/Js/jquery.stapel.js"></script>
        <script type="text/javascript">
            $(function() {

                var $grid = $('#tp-grid'),
                        $name = $('#name'),
                        $close = $('#close'),
                        $loader = $('<div class="loader"><i></i><i></i><i></i><i></i><i></i><i></i><span>Loading...</span></div>').insertBefore($grid),
                        stapel = $grid.stapel({
                            delay: 50,
                            onLoad: function() {
                                $loader.remove();
                            },
                            onBeforeOpen: function(pileName) {
                                $name.html(pileName);
                            },
                            onAfterOpen: function(pileName) {
                                $close.show();
                            }
                        });

                $close.on('click', function() {
                    $close.hide();
                    $name.empty();
                    stapel.closePile();
                });

            });
        </script>
        <!--[if IE]>
        <link rel="stylesheet" type="text/css" href="resources/css/style.css"/>
        <![endif]-->

        <!--[if IEMobile]> 
           <link rel="stylesheet" type="text/css" href="resources/css/Mobile.css"/>
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
                        <div class="Logo"><img alt="Snowflakes" class="logo" src="resources/images/Snowflakes.png" width="180" height="60" /></div>
                        <div class="SideMenu"> <a class="opener" id="touch-menu" href="#"><i class="icon-reorder"></i>Menu</a>
                            <ul id="primary_nav" class="primary_nav">
                                <!-- InstanceBeginEditable name="menuEdit" -->
                                <li><a href="Home.php" title="Snowflake Home"> <img src="resources/images/Icons/Home.png" height="22" width="22"  alt="Add" /> Home </a>
                                    <ul>
                                        <li><a href="ViewSnowflakes.php?userSf=<?php echo $user->m_username; ?>" title="My Snowflakes" class="blue" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['user_total']); ?>"> <img src="resources/images/Icons/Snowflakes.png" height="22" width="22" alt="View" /> My Snowflakes </a></li>
                                        <li><a href="Events/index.php?userSf=<?php echo $user->m_username; ?>" title="My Events" class="yellow" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['user_total']); ?>"> <img src="resources/images/Icons/Events.png" height="22" width="22" alt="View" /> My Events </a></li>
                                        <li><a href="Gallery/index.php?userSf=<?php echo $user->m_username; ?>" title="My Gallery" class="green" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['user_total']); ?>"> <img src="resources/images/Icons/Gallery.png" height="22" width="22" alt="View" /> My Gallery </a></li>
                                    </ul>
                                </li>
                                <li class="active" id="AtvNewButton"><a href="ViewSnowflakes.php" title="Snowflakes" class="blue" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['total']); ?>"> <img src="resources/images/Icons/Snowflakes.png" height="22" width="22"  alt="Snowflakes" /> Snowflakes </a>
                                    <ul>
                                      <!--<li><a href="AddFlake.php" title="Add a New flake"> <img src="resources/images/Icons/Add.png" height="22" width="22"  alt="Add" /> Add New Flake </a></li>-->
                                        <li><a href="ViewSnowflakes.php?publish=1" title="View Published flakes" class="blue" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['published']); ?>"> <img src="resources/images/Icons/Publish.png" height="22" width="22" alt="View" /> Published </a></li>
                                        <li><a href="ViewSnowflakes.php?publish=0" title="View Unublished flakes" class="blue" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['unpublished']); ?>"><img src="resources/images/Icons/UnPublish.png" height="22" width="22" alt="unpublish" /> UnPublished </a></li>
                                        <li><a href="OutputView.php" title="View output flakes" class="blue" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['published']); ?>"> <img src="resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
                                    </ul>
                                </li>

                                <li><a href="Events/index.php" title="Snowflake Events" class="yellow" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['total']); ?>"> <img src="resources/images/Icons/Events.png" height="22" width="22"  alt="Events" /> Events </a>
                                    <ul>
                                        <li><a href="Events/index.php?publish=1" title="View Published Events" class="yellow" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['published']); ?>"> <img src="resources/images/Icons/EventsPublished.png" height="22" width="22" alt="Published" /> Published </a></li>
                                        <li><a href="Events/index.php?publish=0" title="View Unublished Events" class="yellow" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['unpublished']); ?>"><img src="resources/images/Icons/EventsUnpublished.png" height="22" width="22" alt="UnPublished" /> UnPublished </a></li>
                                        <li><a href="Events/OutputView.php" title="View output Events" class="yellow" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['published']); ?>"> <img src="resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
                                    </ul>
                                </li>
                                <li><a href="Gallery/index.php"  title="Snowflakes Gallery" class="green" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['total']); ?>"> <img src="resources/images/Icons/Gallery.png" height="22" width="22"  alt="+ " /> Gallery</a>
                                    <ul>
                                        <li><a href="Gallery/index.php?publish=1" title="View Published Gallery" class="green" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"><img src="resources/images/Icons/GalleryPublished.png" height="22" width="22" alt="View" /> Published </a></li>
                                        <li><a href="Gallery/index.php?publish=0" title="View Unublished Gallery" class="green" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['unpublished']); ?>"><img src="resources/images/Icons/GalleryUnpublished.png" height="22" width="22" alt="unpublish" /> UnPublished </a></li>
                                        <li><a href="Gallery/OutputView.php" title="View output Gallery" class="green" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"><img src="resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
                                    </ul>
                                </li>
                                <?php
                                if ($user->m_access_level == 5 || $user->m_access_level == 4) {
                                    ?>
                                    <li>
                                        <a href="SiteSetting/index.php" title="Settings"> <img src="resources/images/Icons/Settings.png" height="22" width="22" alt="Settings" /> Settings </a>
                                        <ul>
                                            <li><a href="Users/index.php" title="Users" class="pink" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SFUsers']['total']); ?>"> <img src="resources/images/Icons/User.png" height="22" width="22" alt="Admin" /> Admin Users </a></li> 
                                            <li><a href="SiteSetting/LogViewer.php" title="Code Generator"> <img src="resources/images/Icons/Log.png" height="22" width="22" alt="Log" /> Log Viewer </a></li>
                                            <li><a href="Generator.php" title="Code Generator"> <img src="resources/images/Icons/Key.png" height="22" width="22" alt="Code Generator" /> Code Generator </a></li>
                                            <li><a href="<?php echo $logoutAction ?>" title="Log out"> <img src="resources/images/Icons/Logout.png"  height="22" width="22" alt="Log out" /> Log Out </a></li>
                                        </ul>
                                    </li>
                                    <?php
                                } else {
                                    ?>
                                    <li>
                                        <a href="<?php echo $logoutAction ?>" title="Log out"> <img src="resources/images/Icons/Logout.png"  height="22" width="22" alt="Log out" /> Log Out </a>
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
                <h1>Search Results</h1>

                <!-- PageWrap -->
                <div class="PageWrap">

                    <div class="Search">
                        <form id="search-box" action="<?php echo $php_self; ?>" method="post">
                            <select name="filter" class="controls">
                                <option value="Whole site" <?php if (isset($post_filter) && !strcmp($post_filter, "Whole site")) echo "selected=\"selected\""; ?>>Whole Site</option>
                                <option value="snowflakes" <?php if (isset($post_filter) && !strcmp($post_filter, "snowflakes")) echo "selected=\"selected\""; ?>>Snowflakes</option>
                                <option value="events" <?php if (isset($post_filter) && !strcmp($post_filter, "events")) echo "selected=\"selected\""; ?>>Events</option>
                                <option value="gallery" <?php if (isset($post_filter) && !strcmp($post_filter, "gallery")) echo "selected=\"selected\""; ?>>Gallery</option>
                                <option value="users" <?php if (isset($post_filter) && !strcmp($post_filter, "users")) echo "selected=\"selected\""; ?>>Users</option>
                            </select>
                            <input type="search" name="searchString" class="sinputtext controls" value="<?php if (isset($post_searchStr)) echo $post_searchStr; ?>" placeholder="Search ..."> 
                            <input type="hidden" name="search" value="searchform">
                            <br />
                        </form>
                    </div>

                    <?php
                    $snowflakeResult = empty($search["snowflakes"]) ? array() : $search["snowflakes"];
                    $eventsResult = empty($search["events"]) ? array() : $search["events"];
                    $galleryResult = empty($search["gallery"]) ? array() : $search["gallery"];
                    $usersResult = empty($search["users"]) ? array() : $search["users"];
                    if ($post_filter == "Whole site" || $post_filter == "snowflakes") {
                        ?>
                        <!-- Break -->
                        <div class="clear"></div>
                        <div class="Break"></div>
                        <!-- End of Break --> 
                        <h3><?php if (count($snowflakeResult) > 0) {
                        echo count($snowflakeResult);
                    } else {
                        echo 'No';
                    } ?> Snowflakes result for "<?php echo $post_searchStr; ?>"</h3>
                        <?php
                        $searchResultString = '<ul>';
                        $i = 0;
                        while ($i < count($snowflakeResult) && $snowflakeResult != "") {
                            $record = $snowflakeResult[$i];
                            if (!empty($record)) {
                                $searchResultString.= "<li>" . '<a href="' . $settingsConfig['m_sfUrl'] . "Viewflake.php?pageid=" . $record['id'] . '">' . $record['title'] . "</a></li>";
                            }
                            $i++;
                        }
                        $searchResultString .= "</ul>";

                        echo "$searchResultString";
                    }
                    if ($post_filter == "Whole site" || $post_filter == "events") {
                        ?>
                        <!-- Break -->
                        <div class="clear"></div>
                        <div class="Break"></div>
                        <!-- End of Break --> 
                        <h3><?php if (count($eventsResult) > 0) {
                            echo count($eventsResult);
                        } else {
                            echo 'No';
                        } ?> Events result for "<?php echo $post_searchStr; ?>"</h3>
                        <?php
                        $searchResultString = '<ul>';
                        $i = 0;
                        while ($i < count($eventsResult) && $eventsResult != "") {
                            $record = $eventsResult[$i];
                            if (!empty($record)) {
                                $searchResultString.= "<li>" . '<a href="' . $settingsConfig['m_sfUrl'] . "Events/ViewEvent.php?Eventid=" . $record['id'] . '">' . $record['title'] . "</a></li>";
                            }
                            $i++;
                        }
                        $searchResultString .= "</ul>";

                        echo "$searchResultString";
                    }
                    if ($post_filter == "Whole site" || $post_filter == "gallery") {
                        ?>
                        <!-- Break -->
                        <div class="clear"></div>
                        <div class="Break"></div>
                        <!-- End of Break --> 
                        <h3><?php if (count($galleryResult) > 0) {
                        echo count($galleryResult);
                    } else {
                        echo 'No';
                    } ?> Gallery result for "<?php echo $post_searchStr; ?>"</h3>
                        <?php
                        $searchResultString = '<ul>';
                        $i = 0;
                        while ($i < count($galleryResult) && $galleryResult != "") {
                            $record = $galleryResult[$i];
                            if (!empty($record)) {
                                $searchResultString.= "<li>" . '<a href="' . $settingsConfig['m_sfUrl'] . "Gallery/ViewOne.php?Galleryid=" . $record['id'] . '">' . $record['title'] . "</a></li>";
                            }
                            $i++;
                        }
                        $searchResultString .= "</ul>";

                        echo "$searchResultString";
                    }
                    if ($post_filter == "Whole site" || $post_filter == "users") {
                        ?>
                        <!-- Break -->
                        <div class="clear"></div>
                        <div class="Break"></div>
                        <!-- End of Break -->   
                        <h3><?php if (count($usersResult) > 0) {
                        echo count($usersResult);
                    } else {
                        echo 'No';
                    } ?> User result for "<?php echo $post_searchStr; ?>"</h3>

    <?php
    $searchResultString = '<ul>';
    $i = 0;
    while ($i < count($usersResult) && $usersResult != "") {
        $record = $usersResult[$i];
        if (!empty($record)) {
            $searchResultString.= "<li>" . '<a href="' . $settingsConfig['m_sfUrl'] . "Users/Account.php?userName=" . $record['username'] . '">' . $record['username'] . "</a> with email " . $record['email'] . "</li>";
        }
        $i++;
    }
    $searchResultString .= "</ul>";

    echo "$searchResultString";
}
?>

                    <!-- Break -->
                    <div class="clear"></div>
                    <div class="Break2"></div>
                    <!-- End of Break -->

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
                            <li><a href="http://cyrilinc.blogspot.co.uk/" target="_blank" title="Cyril Inc on Blogger.com"> <img src="resources/images/Icons/Blogger.png" alt="Blogger" /></a></li>
                            <li><a href="https://www.facebook.com/pages/Cyril-Inc/151728454900027" target="_blank" title="Cyril Inc on Facebook"> <img src="resources/images/Icons/Facebook.png" alt="Facebook" /></a></li>
                            <li><a href="https://twitter.com/intent/follow?original_referer=http%3A%2F%2Ftwitter.com%2Fabout%2Fresources%2Ffollowbutton&amp;region=follow&amp;screen_name=CyrilInc&amp;source=followbutton&amp;variant=1.0" target="_blank" > <img src="resources/images/Icons/Twitter.png" alt="Twitter" /></a></li>
                            <li><a href="http://delicious.com/cyrilinc" target="_blank" title="Cyril Inc on delicious"> <img src="resources/images/Icons/delicious.png" alt="delicious" /></a></li>
                            <li><a href="http://pinterest.com/cyrilinc" target="_blank" title="Cyril Inc on Pinterest"> <img src="resources/images/Icons/Pinterest.png" alt="Pinterest" /></a></li>
                            <li><a href="https://plus.google.com/117444390192468783291" target="_blank" title="Cyril Inc on GooglePlus"> <img src="resources/images/Icons/GooglePlus.png" alt="GooglePlus" /></a></li>
                            <li><a href="http://www.stumbleupon.com/stumbler/cyrilinc" target="_blank" title="Cyril Inc on stumbleupon"> <img src="resources/images/Icons/Stumbleupon.png" alt="stumbleupon" /></a></li>
                            <li><a href="http://www.youtube.com/CyrilIncBroadcast" target="_blank" title="Cyril Inc on YouTube"> <img src="resources/images/Icons/YouTube.png" alt="YouTube" /></a></li>
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