<?php
require_once 'lib/sf.php';
require_once 'lib/sfConnect.php';
require_once 'config/Config.php';
require_once 'lib/sfSettings.php';

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
$siteSettings = new sfSettings('config/config.ini');
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

$MM_restrictGoTo = "login.php";
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

$colname_rsOnePost = -1;
$pageid = filter_input(INPUT_GET, 'pageid');
if (isset($pageid)) {
    $colname_rsOnePost = $pageid;
} else {
    unset($_SESSION['pageid']);
}
$colfunctionPost = "-1";
$function = filter_input(INPUT_GET, 'function');
if (isset($function)) {
    $colfunctionPost = $function;
}

$config = new databaseParam('config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$SFconnects->connect(); // Connect to database

$deleteId = "-1"; // check for delete id first before selecting
$delete_Id = filter_input(INPUT_GET, 'deleteId');
if (isset($delete_Id)) {

    $deleteId = $delete_Id;
    $setDel = filter_input(INPUT_GET, 'setDel');
    $datadir = new dataDirParam("config/config.ini");
    $flakeStruct = new snowflakeStruct();
    $flakeStruct->getSnowflakesByid($SFconnects, $deleteId);
    $flakeStruct->m_image_dir = $datadir->m_uploadGalleryDir;
    $flakeStruct->deleteSnowflake($SFconnects, $setDel);
    // Check Trigger exist , if not then use manual trigger
    sfUtils::checkTrigger($SFconnects, $deleteId, 'snowflake', "DELETE");

    if (isset($_SESSION['back'])) {
        $change = $_SESSION['back'];
        header("Location: $change");
        exit;
    }
}
$flakeStruct = new snowflakeStruct();
$flakeStruct->getSnowflakesByid($SFconnects, $colname_rsOnePost);
$row_rsOnePostCount = $SFconnects->recordCount();

$colname_rsAdmin = "-1";
if (isset($_SESSION['MM_Username'])) {
    $colname_rsAdmin = $_SESSION['MM_Username'];
}
$user = new userStruct();
$user->getUserByUsername($SFconnects, $colname_rsAdmin);

if ($flakeStruct->m_gallery != NULL) {
    $GalleryName = explode(",", $flakeStruct->m_gallery);
    //print_r($GalleryName );
    $query_rsGallery = "SELECT * FROM snowflakes_gallery WHERE id=" . $GalleryName[0] . " AND title = '" . $GalleryName[1] . "'";
    $SFconnects->fetch($query_rsGallery);
    $result = $SFconnects->getResultArray();
    $row_rsGallery = $result[0];
    $galleryStruct = new galleryStruct();
    $galleryStruct->populate($row_rsGallery);
    $totalRows_rsGallery = $SFconnects->recordCount();
}

//The upload base Image url
$sfGalleryImgUrl = $siteSettings->m_sfGalleryImgUrl;
$sfGalleryThumbUrl = $siteSettings->m_sfGalleryThumbUrl;
?>
<!DOCTYPE HTML>
<html ><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Cyril Inc">
        <meta name="viewport" content="width=device-width, maximum-scale = 1, minimum-scale=1" />
        <!-- InstanceBeginEditable name="doctitle" -->
        <title><?php echo $flakeStruct->m_title; ?></title>
        <!-- InstanceEndEditable -->
        <link rel="icon" href="resources/images/favicon.ico" type="image/x-icon" />
        <link rel="shortcut icon" href="resources/images/favicon.ico">
        <link rel="apple-touch-icon" href="resources/images/apple-touch-icon.png">
        <link rel="stylesheet" type="text/css" href="resources/css/Mobile.css" media="only screen and (max-width: 767px)" />
        <link rel="stylesheet" type="text/css" href="resources/css/style.css" media="only screen and (min-width: 768px)" />
        <link rel="stylesheet" title="text/css" href="resources/css/ColorBox.css" />
        <link rel="stylesheet" title="text/css" href="resources/css/fontstyle.css" />
        <script type="text/javascript" src="resources/Js/jquery-1.11.0.js"></script>
        <script type="text/javascript" src="resources/Js/modernizr.custom.63321.js"></script>
        <script type="text/javascript" src="resources/Js/jquery.cycle.all.js"></script>
        <script type="text/javascript" src="resources/Js/jquery.colorbox.js"></script>
        <script type="text/javascript" src="resources/Js/scrolltopcontrol.js"></script>
        <script type="text/javascript" src="resources/Js/Snowflakes.js"></script>

        <!-- InstanceBeginEditable name="head" -->
        <!--[if IE]>
        <link rel="stylesheet" type="text/css" href="resources/css/style.css"/>
        <![endif]-->

        <!--[if IEMobile]> 
           <link rel="stylesheet" type="text/css" href="resources/css/Mobile.css"/>
        <![endif]-->

        <script type="text/javascript">
            $(document).ready(function() {
                snowflakesCount("sse/snowflakesCount.php");
            });
        </script>
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
                                        <li><a href="ViewSnowflakes.php?userSf=<?php echo $user->m_username; ?>" title="My Snowflakes" class="blue" id="Snowflakes_user_total" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['user_total']); ?>"> <img src="resources/images/Icons/Snowflakes.png" height="22" width="22" alt="View" /> My Snowflakes </a></li>
                                        <li><a href="Events/index.php?userSf=<?php echo $user->m_username; ?>" title="My Events" class="yellow" id="SfEvents_user_total" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['user_total']); ?>"> <img src="resources/images/Icons/Events.png" height="22" width="22" alt="View" /> My Events </a></li>
                                        <li><a href="Gallery/index.php?userSf=<?php echo $user->m_username; ?>" title="My Gallery" class="green" id="SfGallery_user_total" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['user_total']); ?>"> <img src="resources/images/Icons/Gallery.png" height="22" width="22" alt="View" /> My Gallery </a></li>
                                    </ul>
                                </li>
                                <li class="active" id="AtvNewButton"><a href="ViewSnowflakes.php" title="Snowflakes" class="blue" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['total']); ?>"> <img src="resources/images/Icons/Snowflakes.png" height="22" width="22"  alt="Snowflakes" /> Snowflakes </a>
                                    <ul>
                                      <!--<li><a href="AddFlake.php" title="Add a New flake"> <img src="resources/images/Icons/Add.png" height="22" width="22"  alt="Add" /> Add New Flake </a></li>-->
                                        <li><a href="ViewSnowflakes.php?publish=1" title="View Published flakes" class="blue" id="Snowflakes_published" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['published']); ?>"> <img src="resources/images/Icons/Publish.png" height="22" width="22" alt="View" /> Published </a></li>
                                        <li><a href="ViewSnowflakes.php?publish=0" title="View Unublished flakes" class="blue" id="Snowflakes_unpublished" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['unpublished']); ?>"><img src="resources/images/Icons/UnPublish.png" height="22" width="22" alt="unpublish" /> UnPublished </a></li>
                                        <li><a href="OutputView.php" title="View output flakes" class="blue" id="Snowflakes_published2" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['published']); ?>"> <img src="resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
                                    </ul>
                                </li>
                                <li><a href="Events/index.php" title="Snowflake Events" class="yellow" id="SfEvents_total" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['total']); ?>"> <img src="resources/images/Icons/Events.png" height="22" width="22"  alt="Events" /> Events </a>
                                    <ul>
                                        <li><a href="Events/index.php?publish=1" title="View Published Events" class="yellow" id="SfEvents_published" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['published']); ?>"> <img src="resources/images/Icons/EventsPublished.png" height="22" width="22" alt="Published" /> Published </a></li>
                                        <li><a href="Events/index.php?publish=0" title="View Unublished Events" class="yellow" id="SfEvents_unpublished" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['unpublished']); ?>"><img src="resources/images/Icons/EventsUnpublished.png" height="22" width="22" alt="UnPublished" /> UnPublished </a></li>
                                        <li><a href="Events/OutputView.php" title="View output Events" class="yellow" id="SfEvents_published2" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['published']); ?>"> <img src="resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
                                    </ul>
                                </li>
                                <li><a href="Gallery/index.php"  title="Snowflakes Gallery" class="green" id="SfGallery_total" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['total']); ?>"> <img src="resources/images/Icons/Gallery.png" height="22" width="22"  alt="+ " /> Gallery</a>
                                    <ul>
                                        <li><a href="Gallery/index.php?publish=1" title="View Published Gallery" class="green" id="SfGallery_published" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"> <img src="resources/images/Icons/GalleryPublished.png" height="22" width="22" alt="View" /> Published </a></li>
                                        <li><a href="Gallery/index.php?publish=0" title="View Unublished Gallery" class="green" id="SfGallery_unpublished" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['unpublished']); ?>"><img src="resources/images/Icons/GalleryUnpublished.png" height="22" width="22" alt="unpublish" /> UnPublished </a></li>
                                        <li><a href="Gallery/OutputView.php" title="View output Gallery" class="green" id="SfGallery_published2" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"> <img src="resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
                                    </ul>
                                </li>                                <?php
                                if ($user->m_access_level == 5 || $user->m_access_level == 4) {
                                    ?>
                                    <li>
                                        <a href="SiteSetting/index.php" title="Settings"> <img src="resources/images/Icons/Settings.png" height="22" width="22" alt="Settings" /> Settings </a>
                                        <ul>
                                            <li><a href="Users/index.php" title="Users" class="pink" id="SFUsers_total" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SFUsers']['total']); ?>"> <img src="resources/images/Icons/User.png" height="22" width="22" alt="Admin" /> Admin Users </a></li> 
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

                <!-- PageWrap -->
                <div class="PageWrap">
                    <?php if ($pageid != NULL && $row_rsOnePostCount > 0) { ?>
                        <!-- Snowflake -->
                        <div class="Snowflake">
                            <div class="SnowflakeHead"><?php echo $flakeStruct->m_title; ?></div> 

                            <!--SnowflakePanel-->
                            <div class="SnowflakePanel">
                                <a href="EditFlake.php?pageid=<?php echo $flakeStruct->m_id; ?>" title="Edit &amp; Publish"> <img src="resources/images/Icons/Edit.png" height="22" width="22" alt="Edit" /> </a>
                                <?php
                                $notTheOwner = $colname_rsAdmin != $flakeStruct->m_created_by ? true : false;
                                if ($notTheOwner == false || $user->m_access_level == 5) {
                                    $thedeletelink = "Viewflake.php?deleteId=" . $flakeStruct->m_id . "&amp;setDel=" . $notTheOwner;
                                    ?>
                                    <a onclick="deleteConfirmation('<?php echo $thedeletelink; ?>', '<?php echo htmlentities($flakeStruct->m_title); ?>',<?php echo $notTheOwner == false ? "false" : "true"; ?>)"  title="Delete this snowflake"><img src="resources/images/Icons/Delete.png" height="22" width="22" alt="Delete" /> </a> 
                                    <?php if ($flakeStruct->m_deleted) { ?>
                                        <a  href="#" onclick="deleteConfirmation('<?php echo $thedeletelink; ?>', '<?php echo htmlentities($flakeStruct->m_title); ?>',<?php echo $notTheOwner == false ? "false" : "true"; ?>)"  title="Request delete by <?php echo $flakeStruct->m_edited_by; ?>">R<img src="resources/images/Icons/Delete.png" height="22" width="22" alt="Delete" /> </a>
                                    <?php } ?>
                                <?php } ?>

                            </div><!--/SnowflakePanel-->

                            <div class="PageBreak"></div>
                            <div class="clear"></div>

                            <!--SnowflakeDescr-->
                            <div class="SnowflakeDescr">

                                <?php
                                if ($flakeStruct->m_gallery == NULL) {
                                    $imageMissing = $sfGalleryImgUrl . "missing_default.png";
                                    ?> 
                                    <div class="SnowflakeImage">
                                        <a class="colorbox" href="Uploads/<?php echo $flakeStruct->m_image_name; ?>" onerror="this.href='<?php echo $imageMissing; ?>'" title="<?php echo $flakeStruct->m_title; ?>" >
                                            <img src="Uploads/<?php echo $flakeStruct->m_image_name; ?>" onerror="this.src='<?php echo $imageMissing; ?>'" alt="Image" />
                                        </a>
                                    </div>
                                    <?php
                                }
                                ?>
                                <?php echo html_entity_decode($flakeStruct->m_body_text); ?>

                                <!--Place Gallery Here if its not null-->	
                                <?php
                                if ($flakeStruct->m_gallery != NULL) {
                                    $imageMissing = $sfGalleryThumbUrl . "missing_default.png";
                                    if ($totalRows_rsGallery > 0) {
                                        ?>   
                                        <!--wrapper-->
                                        <div class="wrapper clearfix"> 
                                            <h4> <?php echo $galleryStruct->m_title; ?></h4> 
                                            <!--tp-grid-->
                                            <ul id="tp-grid" class="tp-grid">
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
                                                $counter = 0;
                                                foreach ($DBImageThumbFiles as $imageThumbLink) {
                                                    ?>
                                                    <li> <span class="tp-title" ><?php echo $DBImageCaption[$counter]; ?></span> 
                                                        <a class="colorbox" href="<?php echo $DBImageFiles[$counter]; ?>" onerror="this.href='<?php echo $imageMissing; ?>'" > <span class="tp-info"><span><?php echo $DBImageCaption[$counter]; ?></span></span> 
                                                            <img src="<?php echo $imageThumbLink; ?>" onerror="this.src='<?php echo $imageMissing; ?>'"  alt="<?php echo $DBImageCaption[$counter]; ?>"> 
                                                        </a>
                                                    </li>
                                                    <?php
                                                    $counter++;
                                                }
                                                ?>
                                            </ul>
                                            <!--tp-grid Ends--> 
                                        </div>
                                        <!--wrapper Ends--> 
                                        <?php
                                    }
                                }
                                ?>
                            </div><!--SnowflakeDescr Ends-->
                            <div class="clear"></div>
                            <div class="PageBreak"></div>
                            <div class="SnowflakeDate"> Date Created |: <?php echo date(" F j, Y", $flakeStruct->m_created); ?>  | By - <?php echo $flakeStruct->m_created_by; ?> </div>
                            <div class="SnowflakeIt">  
                                <img src="resources/images/Icons/Snowflakes.png" height="22" width="22" alt="flake it" /> 
                                <span class="flakeitParam" id="flakecount<?php echo $flakeStruct->m_id; ?>"> <?php echo $flakeStruct->m_flake_it; ?> </span>
                            </div>
                            <div class="SharePost"> </div>
                        </div>
                        <!--/Snowflake -->
                    <?php } else {
                        ?>
                        <h1>No Snowflake to view</h1>
                    <?php } ?>


                </div> <!--/PageWrap -->

                <!-- InstanceEndEditable -->  </div>
            <!--/Content --> 
        </div>
        <!--/ContentWrapper -->

        <footer id="SnowFooter"> 
            <!-- CMSFooterWrapper -->
            <div class="CMSFooterWrapper"> 

                <!--CopyRight-->
                <div class="CopyRight">
                    <p>&copy; 2013 Cyril Inc. All Rights Reserved. | <a href="http://cyrilinc.co.uk/Legal.html"> Legal information</a> | <a href="mailto:contactus@cyrilinc.co.uk" id="CopyRContactus">Contact Us </a>|</p>
                </div>
                <!--/CopyRight--> 

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
                    <!--/Socialtable--> 
                </div>
                <!--/SocialBar--> 

            </div>
            <!--/CMSFooterWrapper --> 

        </footer>
        <!-- InstanceBeginEditable name="FootEdit" -->

        <!-- InstanceEndEditable -->
    </body>
    <!-- InstanceEnd --></html>
<?php
$SFconnects->close();
?>