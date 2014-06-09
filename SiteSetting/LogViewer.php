<?php
require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';
?>
<?php
//initialize the session
if (!isset($_SESSION))
{
    session_start();
}
$php_self = sfUtils::getFilterServer( 'PHP_SELF');
// ** Logout the current user. **
$logoutAction = $php_self . "?doLogout=true";
$query_string = sfUtils::getFilterServer( 'QUERY_STRING');
if ((isset($query_string)) && ($query_string != ""))
{
    $logoutAction .="&amp;" . htmlentities($query_string);
}
$doLogout = filter_input(INPUT_GET, 'doLogout');
$siteSettings = new settingsStruct('../config/config.ini');
if ((isset($doLogout)) && ($doLogout == "true"))
{
    //to fully log out a visitor we need to clear the session varialbles
    $_SESSION['MM_Username'] = NULL;
    $_SESSION['MM_UserGroup'] = NULL;
    $_SESSION['PrevUrl'] = NULL;
    unset($_SESSION['MM_Username']);
    unset($_SESSION['MM_UserGroup']);
    unset($_SESSION['PrevUrl']);

    $logoutGoTo = $siteSettings->m_loginUrl;
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
$Message = "";
$config = new databaseParam('../config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$connected = $SFconnects->connect(); // Connect to database

if(!$connected){
    $Message.= sfUtils::sfPromptMessage("Snowflakes could not connect to database.".$SFconnects->getMessage(),'error');
}
$colname_rsAdmin = "-1";
if (isset($_SESSION['MM_Username']))
{
    $colname_rsAdmin = $_SESSION['MM_Username'];
}

$user = new userStruct();
$user->getUserByUsername($SFconnects, $colname_rsAdmin);
sfUtils::getAllCounts($SFconnects, $user->m_username);

$datadir = new dataDirParam("../config/config.ini");
$logDir = $datadir->m_logdir;

$logfile = filter_input(INPUT_GET, 'logfile');
$deletefile = filter_input(INPUT_GET, 'delfile');
if (isset($deletefile) && !$logfile)
{
    sfUtils::Deletefile($logDir . $deletefile);
}
?>
<!DOCTYPE HTML>
<html lang="en" ><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Cyril Inc">
        <meta name="viewport" content="width=device-width, maximum-scale = 1, minimum-scale=1" />
        <!-- InstanceBeginEditable name="doctitle" -->
        <title>Snowflakes | Log Viewer</title>
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
        <script type="text/javascript" src="https://www.google.com/jsapi"></script>
<?php if (!$logfile)
{ ?>
            <script type='text/javascript'>
            google.load('visualization', '1', {packages: ['table']});
            google.setOnLoadCallback(drawTable);
            function drawTable() {
                var data = new google.visualization.DataTable();
                data.addColumn('date', 'Log date');
                data.addColumn('string', 'Log Files');
                data.addRows([
    <?php
    $logArray = sfUtils::getfileList2($logDir);
    foreach ($logArray as $key => $file)
    {
        $date = explode("-", $file['Date']);
        $date[1] = $date[1] - 1;
        $loglink = $file['LogFile'];
        $newdate = implode(",", $date);
        echo "[new Date($newdate), '$loglink'],";
    }
    ?>
                ]);

                var formatter = new google.visualization.PatternFormat('<div>\n\
                    <a href="LogViewer.php?logfile={1}" data-log-date="{0}">{1}</a>\n\
                    <a href="LogViewer.php?delfile={1}" title="Delete this Logfile"><img src="../resources/images/Icons/Delete.png" height="22" width="22" alt="Delete" /></a>\n\
                    </div>');
                formatter.format(data, [1, 1]);

                //var formatter2 = new google.visualization.PatternFormat('');
                //formatter2.format(data, [2, 2]);


                var table = new google.visualization.Table(document.getElementById('table_div'));
                table.draw(data, {allowHtml: true});
            }
            </script>
    <?php
}
else
{
    $info = sfUtils::viewLogFile($logDir . $logfile);
    ?>
            <script type='text/javascript'>
                google.load('visualization', '1', {packages: ['table']});
                google.setOnLoadCallback(drawTable);
                function drawTable() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', 'Status');
                    data.addColumn('datetime', 'DateTime');
                    data.addColumn('string', 'Operation');
                    data.addRows([
    <?php
    $datadir = new dataDirParam("../config/config.ini");
    $logDir = $datadir->m_logdir;

    $info = sfUtils::viewLogFile($logDir . $logfile);
    $errorcount = $info['errorcount'];
    $warningCount = $info['warningCount'];
    $successCount = $info['successCount'];
    unset($info['errorcount']);
    unset($info['warningCount']);
    unset($info['successCount']);
    $count = 1;
    foreach ($info as $key => $value)
    {
        $date = str_replace(" ", ",", str_replace("-", ",", str_replace(":", ",", trim($value['datetime']))));
        $expdate = explode(",", $date);
        $expdate[1] = $expdate[1] - 1;
        $newdate = implode(",", $expdate);

        echo "['" . trim($value['Status']) . "',new Date($newdate), '<h5>User:</h5>" .
        trim($value['User']) . "<br><h5>Execute:</h5>" .
        trim(sfUtils::sfescape($value['Execute'])) . "<br><h5>Error File:</h5>" .
        trim(sfUtils::sfescape($value['File'])) . "<br><h5>Error Reason:</h5>" .
        trim(sfUtils::sfescape($value['Reason'])) . "'],\n";
        $count++;
    }
    ?>
                    ]);

                    var table = new google.visualization.Table(document.getElementById('table_div'));
                    table.draw(data, {allowHtml: true});
                }
            </script>
<?php } ?>
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
<?php
echo sfUtils::dialogMessage("Log Message", $Message);
if (!$logfile)
{
    ?>
                    <header>
                        <h1>Snowflakes Log Viewer </h1>	
                    </header>
    <?php
}
else
{
    echo sfUtils::dialogMessage("Viewer", $Message);
    ?>
                    <header>
                        <h1>Snowflakes Log Viewer(<?php echo count($info); ?>)
                            <span class="success"> <?php echo $successCount; ?> Successes</span>
                            <span class="warning"> <?php echo $warningCount; ?> Warnings</span>
                            <span class="error"> <?php echo $errorcount; ?> Errors</span>
                        </h1>	
                    </header>
<?php } ?>
                <!-- Break -->
                <div class="clear"></div>
                <div class="Break"></div>
                <!-- End of Break --> 

                <!-- PageWrap -->
                <div class="PageWrap">
                    <div class="tablepage">
                        <div id='table_div'></div>
                    </div>
                </div>
                <!-- End of PageWrap --> 
                <!-- Break -->
                <div class="clear"></div>
                <div class="Break2"></div>
                <!-- End of Break --> 

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