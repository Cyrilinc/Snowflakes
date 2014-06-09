<?php
require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';
require_once '../lib/sfImageProcessor.php';
?>
<?php
//The upload directory
$siteSettings = new settingsStruct('../config/config.ini');
//The upload Image directory
$sfGalleryImgUrl = $siteSettings->m_sfGalleryImgUrl;
$sfGalleryThumbUrl = $siteSettings->m_sfGalleryThumbUrl;
$imageMissing = $sfGalleryThumbUrl . "missing_default.png";
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
if ((isset($doLogout)) && ($doLogout == "true")) {
    //to fully log out a visitor we need to clear the session varialbles
    $_SESSION['MM_Username'] = NULL;
    $_SESSION['MM_UserGroup'] = NULL;
    $_SESSION['PrevUrl'] = NULL;
    unset($_SESSION['MM_Username']);
    unset($_SESSION['MM_UserGroup']);
    unset($_SESSION['PrevUrl']);

    $logoutGoTo = $siteSettings->m_loginUrl;
    header("Location: $logoutGoTo");
    exit;
}

if (isset($_SESSION['ImageFiles']) || isset($_SESSION['ImageThumbFiles'])) {
    sfImageProcessor::ResetAll();
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
?>
<?php
$colname_rsAdmin = "-1";
if (isset($_SESSION['MM_Username'])) {
    $colname_rsAdmin = $_SESSION['MM_Username'];
}

$colname_rsSFGallery = -1;
$Galleryid = filter_input(INPUT_GET, 'Galleryid');
if (isset($Galleryid)) {
    $colname_rsSFGallery = $Galleryid;
}

$config = new databaseParam('../config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$SFconnects->connect(); // Connect to database

$user = new userStruct();
$user->getUserByUsername($SFconnects, $colname_rsAdmin);

$galleryStruct = new galleryStruct();
$galleryStruct->getGalleryByid($SFconnects, $colname_rsSFGallery);
$totalRows_rsSFGallery = $SFconnects->recordCount();
?>
<?php
$url = $otherurl = sfUtils::curPageURL();
$Powerlink = "../resources/images/Snowflakes2.png";
?>
<!DOCTYPE HTML>
<html lang="en" ><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Cyril Inc">
        <meta name="viewport" content="width=device-width, maximum-scale = 1, minimum-scale=1" />
        <!-- InstanceBeginEditable name="doctitle" -->
        <title><?php echo htmlentities(strlen($galleryStruct->m_title) ? $galleryStruct->m_title : 'No Gallery to view'); ?></title>
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
        <script type="text/javascript">
            $(document).ready(function() {
                snowflakesCount("../sse/snowflakesCount.php");
            });
        </script>
        <link rel="stylesheet" type="text/css" href="../resources/css/stapel.css" />
        <script type="text/javascript" src="../resources/Js/jquery.stapel.js"></script>
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

                                <li><a href="../Events/index.php" title="Snowflake Events" class="yellow" id="SfEvents_total" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['total']); ?>"> <img src="../resources/images/Icons/Events.png" height="22" width="22"  alt="Events" /> Events </a>
                                    <ul>
                                        <li><a href="../Events/index.php?publish=1" title="View Published Events" class="yellow" id="SfEvents_published" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['published']); ?>"><img src="../resources/images/Icons/EventsPublished.png" height="22" width="22" alt="Published" /> Published </a></li>
                                        <li><a href="../Events/index.php?publish=0" title="View Unublished Events" class="yellow" id="SfEvents_unpublished" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['unpublished']); ?>"><img src="../resources/images/Icons/EventsUnpublished.png" height="22" width="22" alt="UnPublished" /> UnPublished </a></li>
                                        <li><a href="../Events/OutputView.php" title="View output Events" class="yellow" id="SfEvents_published2" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['published']); ?>"><img src="../resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
                                    </ul>
                                </li>
                                <li class="active" id="AtvNewButton"><a href="index.php"  title="Snowflakes Gallery" class="green" id="SfGallery_total" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['total']); ?>"> <img src="../resources/images/Icons/Gallery.png" height="22" width="22"  alt="+ " /> Gallery</a>
                                    <ul>
                                        <li><a href="index.php?publish=1" title="View Published Gallery" class="green" id="SfGallery_published" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"><img src="../resources/images/Icons/GalleryPublished.png" height="22" width="22" alt="View" /> Published </a></li>
                                        <li><a href="index.php?publish=0" title="View Unublished Gallery" class="green" id="SfGallery_published" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"><img src="../resources/images/Icons/GalleryUnpublished.png" height="22" width="22" alt="unpublish" /> UnPublished </a></li>
                                        <li><a href="OutputView.php" title="View Output Gallery" class="green" id="SfGallery_published2" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"><img src="../resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
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

                <h1><?php echo isset($Galleryid) ? $galleryStruct->m_title : 'No Gallery to view' ?></h1>

                <?php if (isset($Galleryid) && strlen($galleryStruct->m_image_name)) { ?>
                    <div class="NewButton"><a  href="EditGallery.php?Galleryid=<?php echo $galleryStruct->m_id; ?>" title="Edit This Gallery"> <img src="../resources/images/Icons/Edit.png" height="22" width="22" alt="Edit" />Edit</a></div>
                    <?php
                    $notTheOwner = $colname_rsAdmin != $galleryStruct->m_created_by ? true : false;
                    if ($notTheOwner == false || $user->m_access_level == 5) {
                        $theRemoveGallery = "EditGallery.php?DeleteId=" . $galleryStruct->m_id . "&amp;setDel=" . $notTheOwner;
                        ?>
                        <div class="NewButton"><a  onclick="deleteConfirmation('<?php echo $theRemoveGallery; ?>', '<?php echo "This Gallery (" . $galleryStruct->m_title . ")"; ?>',<?php echo $notTheOwner == false ? "false" : "true"; ?>);" href="#" title="Delete"> <img src="../resources/images/Icons/Delete.png" height="22" width="22" alt="-" />Delete</a></div>
                    <?php } ?>
                    <!-- Break -->
                    <div class="clear"></div>
                    <div class="Break"></div>
                    <!-- End of Break -->
                <?php } ?>
                <!-- PageWrap -->
                <div class="PageWrap">

                    <?php if ($Galleryid != Null) { ?>   

                        <!--wrapper-->
                        <div class="wrapper"> 

                            <!--topbar-->
                            <div class="topbar"> <span id="close" class="back">&larr;</span>
                                <div class="galleryName" id="name">
                                    <div class="owner"> Created |: <?php echo date(" F j, Y", $galleryStruct->m_created); ?>  | By - <?php echo $galleryStruct->m_created_by; ?> </div>
                                </div>
                            </div>
                            <!--topbar End--> 

                            <!--tp-grid-->
                            <ul id="tp-grid" class="tp-grid">
                                <?php if ($totalRows_rsSFGallery > 0) { ?>

                                    <?php
                                    // Get all the image name from database
                                    $DBImageFiles = explode(",", $galleryStruct->m_image_name);
                                    $DBImageThumbFiles = explode(",", $galleryStruct->m_thumb_name);
                                    $DBImageCaption = explode(",", $galleryStruct->m_image_caption);
                                    // Loop through the array and add directory prefix to each item in array
                                    foreach ($DBImageFiles as &$value) {
                                        $value = $sfGalleryImgUrl . $value;
                                    }

                                    // Loop through the array and add directory prefix to each item in array	
                                    foreach ($DBImageThumbFiles as &$value) {
                                        $value = $sfGalleryThumbUrl . $value;
                                    }

                                    //DataList
                                    foreach ($DBImageThumbFiles as $counter => $imageThumbLink) {
                                        ?>
                                        <li> 
                                            <span class="tp-title" ><?php echo htmlentities($DBImageCaption [$counter]); ?></span>
                                            <a class="colorbox" href="<?php echo $DBImageFiles[$counter]; ?>" onerror="this.href='<?php echo $sfGalleryImgUrl . "missing_default.png"; ?>'" title="<?php echo htmlentities($DBImageCaption[$counter]); ?>"> 
                                                <span class="tp-info"><span><?php echo htmlentities($DBImageCaption[$counter]); ?></span></span> 
                                                <img src="<?php echo $imageThumbLink; ?>" onerror="this.src='<?php echo $imageMissing; ?>'" alt="<?php echo htmlentities($DBImageCaption [$counter]); ?>"> 
                                            </a>
                                        </li>

                                        <?php
                                    }
                                    ?>
                                <?php } else { ?> 

                                    <!-- Snowflakes -->
                                    <li data-pile="Snowflakes :Gallery doesn't exist"> <a class="colorbox" href="../Uploads/GalleryImages/Snowflakes.png" > <span class="tp-info"><span>Gallery doesn't exist</span></span> <img src="../Uploads/GalleryThumbs/Snowflakes.png"  alt="Snowflakes"> </a> </li>
                                    <li data-pile="Snowflakes :Gallery doesn't exist"> <a class="colorbox" href="../Uploads/GalleryImages/Snowflakes.png" > <span class="tp-info"><span>Gallery doesn't exist</span></span> <img src="../Uploads/GalleryThumbs/Snowflakes.png"  alt="Snowflakes"> </a> </li>
                                    <li data-pile="Snowflakes :Gallery doesn't exist"> <a class="colorbox" href="../Uploads/GalleryImages/Snowflakes.png" > <span class="tp-info"><span>Gallery doesn't exist</span></span> <img src="../Uploads/GalleryThumbs/Snowflakes.png"  alt="Snowflakes"> </a> </li>
                                <?php } ?>     

                            </ul>
                            <!--tp-grid Ends--> 
                        </div>
                        <!--wrapper Ends--> 


                    <?php }
                    ?>

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
        <!-- InstanceBeginEditable name="FootEdit" --> 

        <!-- InstanceEndEditable -->
    </body>
    <!-- InstanceEnd --></html>
<?php
$SFconnects->close();
?>
