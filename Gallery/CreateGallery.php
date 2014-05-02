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
$GalleryMessage = '';

$Post_upload = filter_input(INPUT_POST, 'upload');
if (isset($Post_upload)) {
    sfImageProcessor::UploadMultiImages($_FILES['uploadImage'], "../config/config.ini", $GalleryMessage);
}

$Post_uploadThumb = filter_input(INPUT_POST, 'uploadThumb');
if (isset($Post_uploadThumb)) {
    sfImageProcessor::saveThumbImage();
}

/// Delete an image
$Delete_One = filter_input(INPUT_GET, 'DeleteOne');
if (isset($Delete_One) && $Delete_One != "") {
    $DeleteOne = $Delete_One;
    $Delresult = sfImageProcessor::RemoveOne($DeleteOne);
    if ($Delresult == false)
        $GalleryMessage.= " Could not remove the image";
}

/// Delete All  images in Gallery
$Remove_All = filter_input(INPUT_GET, 'RemoveAll');
if (isset($Remove_All) && ($Remove_All != "")) {
    $RemoveAll = $Remove_All;
    if ($RemoveAll == True) {
        $Delresult = sfImageProcessor::RemoveAll();
        if ($Delresult == false)
            $GalleryMessage.= " Could not remove the image";
    }
}
?>
<?php
//initialize the session
if (!isset($_SESSION)) {
    session_start();
}

$GalleryImages = array();
$GalleryThumbImages = array();

$php_self = filter_input(INPUT_SERVER, 'PHP_SELF');
// ** Logout the current user. **
$logoutAction = $php_self . "?doLogout=true";
$query_string = filter_input(INPUT_SERVER, 'QUERY_STRING');
if ((isset($query_string)) && ($query_string != "")) {
    $logoutAction .= "&" . htmlentities($query_string);
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
$config = Config::getConfig("db", '../config/config.ini');
$sqlArray = array('type' => $config['type'], 'host' => $config['host'], 'username' => $config['username'], 'password' => sfUtils::decrypt($config['password'], $config['key']), 'database' => $config['dbname']);
$SFconnects = new sfConnect($sqlArray);
$SFconnects->connect(); // Connect to database

$editFormAction = $php_self;
if (isset($query_string)) {
    $editFormAction .= "?" . htmlentities($query_string);
}

$MM_insert = filter_input(INPUT_POST, 'MM_insert');
$viewLink = $EditLink = "#";
if ((isset($MM_insert)) && ($MM_insert == "AddGallery")) {

    $_POST['publish'] = isset($_POST['publish']) ? "1" : "0";
    $galleryStruct = new galleryStruct();
    $galleryStruct->populate($_POST);

    if (!$galleryStruct->isSfGPopulated()) {
        $GalleryMessage.= "You must add images to the gallery. <br>";
    }

    if (!$galleryStruct->addSfGallery($SFconnects)) {
        $GalleryMessage.= "Could not add the new snowflake Gallery. <br>" . $SFconnects->getMessage() . '<br>';
    } else {
        $GalleryID = $galleryStruct->getGalleryID($SFconnects);
        $viewLink = "ViewOne.php?Galleryid=$GalleryID";
        $EditLink = "EditGallery.php?Eventid=$GalleryID";
        $GalleryMessage.='<p>'
                . '<a href="' . $viewLink . '" title="view it">"' . $galleryStruct->m_title . '"</a> was added successfully. '
                . '<span class="icon success"></span>'
                . '</p>';
    }
}

$colname_rsAdmin = "-1";
if (isset($_SESSION['MM_Username'])) {
    $colname_rsAdmin = $_SESSION['MM_Username'];
}

$user = new userStruct();
$user->getUserByUsername($SFconnects, $colname_rsAdmin);
?>
<!DOCTYPE HTML>
<html lang="en" ><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Cyril Inc">
        <meta name="viewport" content="width=device-width, maximum-scale = 1, minimum-scale=1" />
        <!-- InstanceBeginEditable name="doctitle" -->
        <title>Create Gallery</title>
        <!-- InstanceEndEditable -->
        <link rel="icon" href="../resources/images/favicon.ico" type="image/x-icon" />
        <link rel="shortcut icon" href="../resources/images/favicon.ico">
        <link rel="apple-touch-icon" href="../resources/images/apple-touch-icon.png">
        <link rel="stylesheet" type="text/css" href="../resources/css/Mobile.css" media="only screen and (max-width: 767px)" />
        <link rel="stylesheet" type="text/css" href="../resources/css/style.css" media="only screen and (min-width: 768px)" />
        <link rel="stylesheet" title="text/css" href="../resources/css/ColorBox.css" />
        <script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
        <script src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
        <script type="text/javascript" src="../resources/Js/modernizr.custom.63321.js"></script>
        <script type="text/javascript" src="../resources/Js/jquery.cycle.all.js"></script>
        <script type="text/javascript" src="../resources/Js/jquery.colorbox.js"></script>
        <script type="text/javascript" src="../resources/Js/scrolltopcontrol.js"></script>
        <script type="text/javascript" src="../resources/Js/Snowflakes.js"></script>

        <!-- InstanceBeginEditable name="head" -->
        <link rel="stylesheet" type="text/css" href="../resources/css/stapel.css" />
        <script type="text/javascript" src="../resources/Js/jquery.stapel.js"></script>
        <script src="../SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
        <script type="text/javascript">
                    $(function() {

                    var $grid = $('#tp-grid'),
                            $name = $('#name'),
                            $close = $('#close'),
                            $loader = $('<div class="loader"><span>Loading &nbsp; &nbsp;</span><i></i><i></i><i></i><i></i><i></i><i></i></div>').insertBefore($grid),
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
                    });</script>

        <link href="../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css">
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
<?php
if ((isset($MM_insert)) && ($MM_insert == "AddGallery") && $viewLink != "#") {
    ?>
                                ,
                                        "View"
                                        : function() {
                                        window.location = "<?php echo $viewLink ?>";
                                        }
<?php } ?>
                            }
                    });
                    });</script>
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
                <h1> Create Gallery</h1>
                <div class="NewButton"><a class="colorboxLink" href="Upload.php" title="Add image to Gallery"> <img src="../resources/images/Icons/Add.png" height="22" width="22" alt="+" />Add image </a></div>
                <?php
                $theRemoveAlllink = "CreateGallery.php?RemoveAll=True";
                ?>
                <div class="NewButton"><a  onclick="deleteConfirmation('<?php echo $theRemoveAlllink; ?>', 'All Images')" href="#" title="Remove All Images"> <img src="../resources/images/Icons/Delete.png" height="22" width="22" alt="-" />Remove All </a></div>


                <!-- Break -->
                <div class="clear"></div>
                <div class="Break"></div>
                <!-- End of Break --> 

                <!-- PageWrap -->
                <div class="PageWrap"> 
                    <!--contactform-->
                    <div class="contactform">
                        <form action="<?php echo $editFormAction; ?>" method="POST" name="AddGallery" id="addGalleryForm">
                            <span id="SpryGallerytitle">
                                <span class="textfieldRequiredMsg">Gallery Title  is required.<br/></span><span class="textfieldMaxCharsMsg">Exceeded maximum number of characters of 120.<br /></span>
                                <input type="text" name="title" value=""  class="inputtext2 controls" placeholder="Gallery Title:" >
                            </span><br/><br/>

                            <div class="publishswitch">
                                <input type="checkbox" name="publish" class="publishswitch-checkbox" id="mypublishswitch" value="">
                                <label class="publishswitch-label" for="mypublishswitch">
                                    <div class="publishswitch-inner"></div>
                                    <div class="publishswitch-switch"></div>
                                </label>
                            </div>  

                            <br />
                            <br />

                            <span id="spryImages">
                                <input type="text" name="numberofimages" value="<?php
                                $countImages = count($_SESSION['ImageFiles']);
                                if ($countImages >= 1) {
                                    echo $countImages . " Images";
                                }
                                ?>"  readonly>
                                <span class="textfieldRequiredMsg">An image is required.</span></span>
                            <?php
                            if (isset($_SESSION['ImageFiles']) && ($_SESSION['ImageFiles'] != "")) {

                                $GalleryImages = $_SESSION['ImageFiles'];
                                $GalleryThumbImages = $_SESSION['ImageThumbFiles'];
                                $GalleryDisplayImages = str_replace($UploadImgDir, $UploadImgUrl, $GalleryImages);
                                $GalleryDisplayThumb = str_replace($UploadThumbDir, $UploadThumbUrl, $GalleryThumbImages);

                                $DBGalleryImages = $DBGalleryThumbImages = str_replace($UploadImgDir, "", $GalleryImages);
                                $DBImageCaptions = $_SESSION['ImageCaptions'];
                            }
                            if (!empty($GalleryMessage)) {
                                echo sfUtils::dialogMessage("Create Gallery", $GalleryMessage);
                            }
                            ?>
                            <!--wrapper-->
                            <div class="wrapper"> 

                                <!--topbar-->
                                <div class="topbar"> </div>
                                <!--topbar End--> 

                                <!--tp-grid-->
                                <ul id="tp-grid" class="tp-grid">

                                    <!-- DataList -->
                                    <?php
                                    foreach ($GalleryDisplayThumb as $counter => $imageThumbLink) {
                                        ?>
                                        <li> 
                                            <a class="EditThumb" title="Edit Thumbnail" href="Thumbnail.php?index=<?php echo $counter; ?>&amp;ActionPage=<?php echo sfUtils::curPageURL(); ?>"> <img src="../resources/images/Icons/Edit.png" width="22" height="22" alt="Edit" /></a>
                                            <?php $thedeletelink = "CreateGallery.php?DeleteOne=" . $counter; ?>
                                            <a onclick="deleteConfirmation('<?php echo $thedeletelink; ?>', '<?php echo htmlentities($DBImageCaptions[$counter]); ?>')"  class="DeleteImage" title="Remove image" href="#"><img src="../resources/images/Icons/Delete.png" width="22" height="22" alt="Delete" /> </a> 
                                            <a class="colorbox" href="<?php echo $GalleryDisplayImages[$counter]; ?>" title="<?php echo htmlentities($DBImageCaptions[$counter]); ?>" ><img src="<?php echo $imageThumbLink; ?>"  alt="<?php echo htmlentities($DBImageCaptions[$counter]); ?>"> </a>  

                                        </li>
                                        <?php
                                    }
                                    ?>
                                </ul>
                                <!--tp-grid Ends--> 
                            </div>
                            <!--wrapper Ends--> 
                            <br />
                            <input class="NewButton" type="submit" value="Save Gallery"  name="AddGallery">
                            <input type="hidden" name="image_name" value="<?php echo implode(",", $DBGalleryImages); ?>">
                            <input type="hidden" name="thumb_name" value="<?php echo implode(",", $DBGalleryThumbImages); ?>">
                            <input type="hidden" name="image_caption" value="<?php echo implode(",", $DBImageCaptions); ?>">
                            <input type="hidden" name="created" value="<?php echo time(); ?>">
                            <input type="hidden" name="created_by" value="<?php echo $_SESSION['MM_Username']; ?>">
                            <input type="hidden" name="edited" value="<?php echo time(); ?>">
                            <input type="hidden" name="edited_by" value="<?php echo $_SESSION['MM_Username']; ?>">
                            <input type="hidden" name="MM_insert" value="AddGallery">
                        </form>
                        <p>&nbsp;</p>
                    </div>
                    <!--contactform Ends--> 

                </div>
                <!-- PageWrap Ends --> 

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
        <script type="text/javascript">
                    var sprytextfield1 = new Spry.Widget.ValidationTextField("SpryGallerytitle", "none", {validateOn: ["change"], maxChars: 120});
                    var sprytextfield2 = new Spry.Widget.ValidationTextField("spryImages", "none");
        </script>
        <!-- InstanceEndEditable -->
    </body>
    <!-- InstanceEnd --></html>
<?php
$SFconnects->close();
?>
