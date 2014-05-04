<?php
require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';
require_once '../lib/sfImageProcessor.php';
?>

<?php
//The upload directory
$settingsConfig = Config::getConfig("settings", '../config/config.ini');
$UploadDir = $settingsConfig['m_sfGalleryUrl'];
//The upload Image directory

$UploadImgDir = $settingsConfig['galleryImgDir'];
$UploadImgUrl = $settingsConfig['m_sfGalleryImgUrl'];
$UploadThumbDir = $settingsConfig['galleryThumbDir'];
$UploadThumbUrl = $settingsConfig['m_sfGalleryThumbUrl'];
$imageMissing = $UploadThumbUrl . "missing_default.png";
?>

<?php
//initialize the session
if (!isset($_SESSION)) {
    session_start();
}

if (isset($_SESSION['ImageFiles']) || isset($_SESSION['ImageThumbFiles'])) {
    sfImageProcessor::ResetAll();
}
$php_self = filter_input(INPUT_SERVER, 'PHP_SELF');
// ** Logout the current user. **
$logoutAction = $php_self . "?doLogout=true";
$query_string = filter_input(INPUT_SERVER, 'QUERY_STRING');
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
$config = Config::getConfig("db", '../config/config.ini');
$sqlArray = array('type' => $config['type'], 'host' => $config['host'], 'username' => $config['username'], 'password' => sfUtils::decrypt($config['password'], $config['key']), 'database' => $config['dbname']);
$SFconnects = new sfConnect($sqlArray);
$SFconnects->connect(); // Connect to database

$DeleteId = filter_input(INPUT_GET, 'DeleteId');
if (isset($DeleteId)) {
    $colname_rsDeleteAll = $DeleteId;

    $galleryStruct = new galleryStruct();
    $galleryStruct->getGalleryByid($SFconnects, $DeleteId);

    // Get all the image name from database
    $_SESSION['ImageFiles'] = explode(",", $galleryStruct->m_image_name);
    $_SESSION['ImageThumbFiles'] = explode(",", $galleryStruct->m_thumb_name);

    $setDel = filter_input(INPUT_GET, 'setDel');
    if ($galleryStruct->deleteGallery($SFconnects, $setDel)) {
        // Loop through the array and add directory prefix to each item in array
        foreach ($_SESSION['ImageFiles'] as &$value) {
            $value = $UploadImgDir . $value;
        }

        // Loop through the array and add directory prefix to each item in array	
        foreach ($_SESSION['ImageThumbFiles'] as &$value) {
            $value = $UploadThumbDir . $value;
        }

        //remove all images in the physical location if owner
        if ($setDel == false) {
            sfImageProcessor::RemoveAll();
        }
        // Check Trigger exist , if not then use manual trigger
        sfUtils::checkTrigger($SFconnects, $DeleteId, 'gallery', "DELETE");
    }
}

$colname_rsAdmin = "-1";
if (isset($_SESSION['MM_Username'])) {
    $colname_rsAdmin = $_SESSION['MM_Username'];
}

$user = new userStruct();
$user->getUserByUsername($SFconnects, $colname_rsAdmin);

$query_rsSFGallery = "SELECT * FROM snowflakes_gallery ORDER BY id DESC";
$SFconnects->fetch($query_rsSFGallery);
$row_rsSFGallery = $SFconnects->getResultArray();
$galleryStructList = array();
foreach ($row_rsSFGallery as $key => $value) {
    $galleryStructList[$key] = new galleryStruct();
    $galleryStructList[$key]->populate($value);
}
$totalRows_rsSFGallery = $SFconnects->recordCount();
?>
<!DOCTYPE HTML>
<html lang="en" ><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Cyril Inc">
        <meta name="viewport" content="width=device-width, maximum-scale = 1, minimum-scale=1" />
        <!-- InstanceBeginEditable name="doctitle" -->
        <title>Snowflakes | Edit Gallery</title>
        <!-- InstanceEndEditable -->
        <link rel="icon" href="../resources/images/favicon.ico" type="image/x-icon" />
        <link rel="shortcut icon" href="../resources/images/favicon.ico">
        <link rel="apple-touch-icon" href="../resources/images/apple-touch-icon.png">
        <link rel="stylesheet" type="text/css" href="../resources/css/Mobile.css" media="only screen and (max-width: 767px)" />
        <link rel="stylesheet" type="text/css" href="../resources/css/style.css" media="only screen and (min-width: 768px)" />
        <link rel="stylesheet" title="text/css" href="../resources/css/ColorBox.css" />
        <script type="text/javascript" src="../resources/Js/jquery-1.11.0.js"></script>
        <script type="text/javascript" src="../resources/Js/modernizr.custom.63321.js"></script>
        <script type="text/javascript" src="../resources/Js/jquery.cycle.all.js"></script>
        <script type="text/javascript" src="../resources/Js/jquery.colorbox.js"></script>
        <script type="text/javascript" src="../resources/Js/scrolltopcontrol.js"></script>
        <script type="text/javascript" src="../resources/Js/Snowflakes.js"></script>

        <!-- InstanceBeginEditable name="head" -->
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
                                        <li><a href="../ViewSnowflakes.php?userSf=<?php echo $user->m_username; ?>" title="My Snowflakes" class="blue" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['user_total']); ?>"> <img src="../resources/images/Icons/Snowflakes.png" height="22" width="22" alt="View" /> My Snowflakes </a></li>
                                        <li><a href="../Events/index.php?userSf=<?php echo $user->m_username; ?>" title="My Events" class="yellow" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['user_total']); ?>"> <img src="../resources/images/Icons/Events.png" height="22" width="22" alt="View" /> My Events </a></li>
                                        <li><a href="../Gallery/index.php?userSf=<?php echo $user->m_username; ?>" title="My Gallery" class="green" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['user_total']); ?>"> <img src="../resources/images/Icons/Gallery.png" height="22" width="22" alt="View" /> My Gallery </a></li>
                                    </ul>
                                </li>
                                <li><a href="../ViewSnowflakes.php" title="Snowflakes" class="blue" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['total']); ?>"> <img src="../resources/images/Icons/Snowflakes.png" height="22" width="22"  alt="Snowflakes" /> Snowflakes </a>
                                    <ul>
                                      <!--<li><a href="../AddFlake.php" title="Add a New flake"> <img src="../resources/images/Icons/Add.png" height="22" width="22"  alt="Add" /> Add New Flake </a></li>-->
                                        <li><a href="../ViewSnowflakes.php?publish=1" title="View Published flakes" class="blue" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['published']); ?>"> <img src="../resources/images/Icons/Publish.png" height="22" width="22" alt="View" /> Published </a></li>
                                        <li><a href="../ViewSnowflakes.php?publish=0" title="View Unublished flakes" class="blue" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['unpublished']); ?>"><img src="../resources/images/Icons/UnPublish.png" height="22" width="22" alt="unpublish" /> UnPublished </a></li>
                                        <li><a href="../OutputView.php" title="View output flakes" class="blue" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['published']); ?>"><img src="../resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
                                    </ul>
                                </li>

                                <li><a href="../Events/index.php" title="Snowflake Events" class="yellow" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['total']); ?>"> <img src="../resources/images/Icons/Events.png" height="22" width="22"  alt="Events" /> Events </a>
                                    <ul>
                                        <li><a href="../Events/index.php?publish=1" title="View Published Events" class="yellow" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['published']); ?>"><img src="../resources/images/Icons/EventsPublished.png" height="22" width="22" alt="Published" /> Published </a></li>
                                        <li><a href="../Events/index.php?publish=0" title="View Unublished Events" class="yellow" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['unpublished']); ?>"><img src="../resources/images/Icons/EventsUnpublished.png" height="22" width="22" alt="UnPublished" /> UnPublished </a></li>
                                        <li><a href="../Events/OutputView.php" title="View output Events" class="yellow" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['published']); ?>"><img src="../resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
                                    </ul>
                                </li>
                                <li class="active" id="AtvNewButton"><a href="index.php"  title="Snowflakes Gallery" class="green" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['total']); ?>"> <img src="../resources/images/Icons/Gallery.png" height="22" width="22"  alt="+ " /> Gallery</a>
                                    <ul>
                                        <li><a href="index.php?publish=1" title="View Published Gallery" class="green" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"><img src="../resources/images/Icons/GalleryPublished.png" height="22" width="22" alt="View" /> Published </a></li>
                                        <li><a href="index.php?publish=0" title="View Unublished Gallery" class="green" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['unpublished']); ?>"><img src="../resources/images/Icons/GalleryUnpublished.png" height="22" width="22" alt="unpublish" /> UnPublished </a></li>
                                        <li><a href="OutputView.php" title="View Output Gallery" class="green" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"><img src="../resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
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
                <h1>Snowflakes | Edit Gallery</h1>
                <div class="NewButton"><a  href="index.php" title="Gallery"> <img src="../resources/images/Icons/View.png" height="22" width="22" alt="Edit" />View Gallery </a></div>       
                <!-- Break -->
                <div class="clear"></div>
                <div class="Break"></div>
                <!-- End of Break -->

                <!--wrapper-->
                <div class="wrapper"> 

                    <!--topbar-->
                    <div class="topbar"> <span id="close" class="back">&larr;</span>
                        <h3 id="name"></h3>
                    </div>
                    <!--topbar End--> 

                    <!--tp-grid-->
                    <ul id="tp-grid" class="tp-grid">
                        <?php
                        if ($totalRows_rsSFGallery > 0) {
                            $i = 0;
                            ?>
                            <?php do { ?>
                                <?php
                                // Get all the image name from database
                                $DBImageFiles = explode(",", $galleryStructList[$i]->m_image_name);
                                $DBImageThumbFiles = explode(",", $galleryStructList[$i]->m_thumb_name);

                                // Loop through the array and add directory prefix to each item in array
                                foreach ($DBImageFiles as &$value) {
                                    $value = $UploadImgUrl . $value;
                                }

                                // Loop through the array and add directory prefix to each item in array	
                                foreach ($DBImageThumbFiles as &$value) {
                                    $value = $UploadThumbUrl . $value;
                                }

                                //DataList
                                ?>

                                <li> <span class="tp-title" ><?php echo $galleryStructList[$i]->m_title; ?></span>
                                    <a class="ViewThumb" title="View Gallery" href="ViewOne.php?Galleryid=<?php echo $galleryStructList[$i]->m_id; ?>" > <img src="../resources/images/Icons/View.png" height="22" width="22" alt="View" /> </a> 
                                    <a class="EditThumb" title="Edit Gallery" href="EditGallery.php?Galleryid=<?php echo $galleryStructList[$i]->m_id; ?>"> <img src="../resources/images/Icons/Edit.png" width="22" height="22" alt="Edit" /></a>

                                    <?php
                                    $notTheOwner = $colname_rsAdmin != $galleryStructList[$i]->m_created_by ? true : false;
                                    if ($notTheOwner == false || $user->m_access_level == 5) {
                                        $thedeletelink = "GalleryEdition.php?DeleteId=" . $galleryStructList[$i]->m_id . "&amp;setDel=" . $notTheOwner;
                                        ?>
                                        <a onclick="deleteConfirmation('<?php echo $thedeletelink; ?>', '<?php echo $galleryStructList[$i]->m_title; ?>',<?php echo $notTheOwner == false ? "false" : "true"; ?>)"  class="DeleteImage" title="Delete Gallery" href="#"><img src="../resources/images/Icons/Delete.png" width="22" height="22" alt=" X " /> </a> 

        <?php } ?>

                                    <a class="colorbox" href="<?php echo end($DBImageFiles); ?>" onerror="this.href='<?php echo $UploadImgUrl . "missing_default.png"; ?>'" ><span class="tp-info"><span><?php echo $galleryStructList[$i]->m_title; ?></span></span> 
                                        <img src="<?php echo end($DBImageThumbFiles); ?>" onerror="this.src='<?php echo $imageMissing; ?>'" alt="<?php echo $galleryStructList[$i]->m_title; ?>"> 
                                    </a>
                                </li>

                                <?php
                                $i++;
                            } while ($i < count($galleryStructList));
                            ?>

<?php } else { ?> 

                            <!-- Snowflakes -->
                            <li data-pile="Snowflakes : No images yet"> <a class="colorbox" href="../Uploads/GalleryImages/Snowflakes.png" > <span class="tp-info"><span>No Images in Gallery</span></span> <img src="../Uploads/GalleryThumbs/Snowflakes.png"  alt="Snowflakes"> </a> </li>
                            <li data-pile="Snowflakes : No images yet"> <a class="colorbox" href="../Uploads/GalleryImages/Snowflakes.png" > <span class="tp-info"><span>No Images in Gallery</span></span> <img src="../Uploads/GalleryThumbs/Snowflakes.png"  alt="Snowflakes"> </a> </li>
                            <li data-pile="Snowflakes : No images yet"> <a class="colorbox" href="../Uploads/GalleryImages/Snowflakes.png" > <span class="tp-info"><span>No Images in Gallery</span></span> <img src="../Uploads/GalleryThumbs/Snowflakes.png"  alt="Snowflakes"> </a> </li>
<?php } ?>     

                    </ul>
                    <!--tp-grid Ends--> 
                </div>
                <!--wrapper Ends--> 

                <!-- Break -->
                <div class="clear"></div>
                <div class="Break"></div>
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
                            <li><a href="http://cyrilinc.blogspot.co.uk/" target="_blank" title="Cyril Inc on Blogger.com"> <img src="../resources/images/Icons/Blogger.png" alt="Blogger" /></a></li>
                            <li><a href="https://www.facebook.com/pages/Cyril-Inc/151728454900027" target="_blank" title="Cyril Inc on Facebook"> <img src="../resources/images/Icons/Facebook.png" alt="Facebook" /></a></li>
                            <li><a href="https://twitter.com/intent/follow?original_referer=http%3A%2F%2Ftwitter.com%2Fabout%2Fresources%2Ffollowbutton&amp;region=follow&amp;screen_name=CyrilInc&amp;source=followbutton&amp;variant=1.0" target="_blank" > <img src="../resources/images/Icons/Twitter.png" alt="Twitter" /></a></li>
                            <li><a href="http://delicious.com/cyrilinc" target="_blank" title="Cyril Inc on delicious"> <img src="../resources/images/Icons/delicious.png" alt="delicious" /></a></li>
                            <li><a href="http://pinterest.com/cyrilinc" target="_blank" title="Cyril Inc on Pinterest"> <img src="../resources/images/Icons/Pinterest.png" alt="Pinterest" /></a></li>
                            <li><a href="https://plus.google.com/117444390192468783291" target="_blank" title="Cyril Inc on GooglePlus"> <img src="../resources/images/Icons/GooglePlus.png" alt="GooglePlus" /></a></li>
                            <li><a href="http://www.stumbleupon.com/stumbler/cyrilinc" target="_blank" title="Cyril Inc on stumbleupon"> <img src="../resources/images/Icons/Stumbleupon.png" alt="stumbleupon" /></a></li>
                            <li><a href="http://www.youtube.com/CyrilIncBroadcast" target="_blank" title="Cyril Inc on YouTube"> <img src="../resources/images/Icons/YouTube.png" alt="YouTube" /></a></li>
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
