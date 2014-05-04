<?php
require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';
require_once '../lib/sfImageProcessor.php';
?>
<?php
//initialize the session
if (!isset($_SESSION)) {
    session_start();
}

//The upload directory
$settingsConfig = Config::getConfig("settings", '../config/config.ini');
$UploadDir = $settingsConfig['m_sfGalleryUrl'];
//The upload Image directory
$UploadImgDir = $settingsConfig['galleryImgDir'];
$UploadImgUrl = $settingsConfig['m_sfGalleryImgUrl'];
$UploadThumbDir = $settingsConfig['galleryThumbDir'];
$UploadThumbUrl = $settingsConfig['m_sfGalleryThumbUrl'];
$GalleryMessage = '';

$imageMissing = $UploadThumbUrl . "missing_default.png";
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

if (!isset($_SESSION['DBImageFiles'])) {
    $_SESSION['DBImageFiles'] = array();
}

if (!isset($_SESSION['DBImageThumbFiles'])) {
    $_SESSION['DBImageThumbFiles'] = array();
}

if (!isset($_SESSION['DBImageCaptions'])) {
    $_SESSION['DBImageCaptions'] = array();
}
$Post_upload = filter_input(INPUT_POST, 'upload');
if (isset($Post_upload)) {
    sfImageProcessor::UploadMultiImages($_FILES['uploadImage'], "../config/config.ini", $GalleryMessage);
}

$Post_uploadThumb = filter_input(INPUT_POST, 'uploadThumb');
if (isset($Post_uploadThumb)) {
    if (!sfImageProcessor::saveThumbImage()) {
        $GalleryMessage.="Could not Save the thumb file";
    }
}

$get_cover = filter_input(INPUT_GET, 'cover');
if (isset($get_cover) && $get_cover != "") {
    $cover = $get_cover;
    if (!sfImageProcessor::makeCover($cover)) {
        $GalleryMessage.= " Could not make the image the album cover";
    }
}

/// Delete an image
$Delete_One = filter_input(INPUT_GET, 'DeleteOne');
if (isset($Delete_One) && $Delete_One != "") {
    $DeleteOne = $Delete_One;
    if (!sfImageProcessor::RemoveOne($DeleteOne)) {
        $GalleryMessage.= " Could not remove the image";
    }
}

/// Delete All  images in Gallery
$Remove_All = filter_input(INPUT_GET, 'RemoveAll');
if (isset($Remove_All) && ($Remove_All != "")) {
    $RemoveAll = $Remove_All;
    if ($RemoveAll == True) {
        if (!sfImageProcessor::RemoveAll()) {
            $GalleryMessage.= " Could not remove All the images";
        }
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

$MM_update = filter_input(INPUT_POST, 'MM_update');
$viewLink = $EditLink = "#";
if ((isset($MM_update)) && ($MM_update == "EditGallery")) {

    $_POST['publish'] = isset($_POST['publish']) ? "1" : "0";
    $galleryStruct = new galleryStruct();
    $galleryStruct->populate($_POST);

    if (!$galleryStruct->updateGallery($SFconnects)) {
        $GalleryMessage.= "Could not Update this snowflake Gallery. <br>" . $SFconnects->getMessage() . '<br>';
    } else {
        $GalleryID = $galleryStruct->getGalleryID($SFconnects);
        $viewLink = "ViewOne.php?Galleryid=$GalleryID";
        $EditLink = "EditGallery.php?Eventid=$GalleryID";
        // Check Trigger exist , if not then use manual trigger
        sfUtils::checkTrigger($SFconnects, $GalleryID, 'gallery', "UPDATE");
        $GalleryMessage.='<p>'
                . '<a href="' . $viewLink . '" title="view it">"' . $galleryStruct->m_title . '"</a> was editted successfully. '
                . '<span class="icon success"></span>'
                . '</p>';
    }
}

$DeleteId = filter_input(INPUT_GET, 'DeleteId');
if (isset($DeleteId)) {

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
        
        $updateGoTo = "index.php";
        header(sprintf("Location: %s", $updateGoTo));
    }
}

$colname_rsAdmin = "-1";
if (isset($_SESSION['MM_Username'])) {
    $colname_rsAdmin = $_SESSION['MM_Username'];
}
$user = new userStruct();
$user->getUserByUsername($SFconnects, $colname_rsAdmin);

$colname_rsGallery = -1;
$Galleryid = filter_input(INPUT_GET, 'Galleryid');
if (isset($Galleryid)) {
    $colname_rsGallery = $Galleryid;
}

$galleryStruct = new galleryStruct();
$galleryStruct->getGalleryByid($SFconnects, $colname_rsGallery);
?>
<!DOCTYPE HTML>
<html lang="en" ><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Cyril Inc">
        <meta name="viewport" content="width=device-width, maximum-scale = 1, minimum-scale=1" />
        <!-- InstanceBeginEditable name="doctitle" -->
        <title>Edit Gallery</title>
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
        <script src="../SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
        <script type="text/javascript">
                    $(function() {

                    var $grid = $('#tp-grid'),
                            $name = $('#name'),
                            $close = $('#close'),
                            $loader = $('<div class="loader"><span>Loading&nbsp; &nbsp;</span><i></i><i></i><i></i><i></i><i></i><i></i></div>').insertBefore($grid),
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
                            "Edit": function() {
                            $(this).dialog("close");
                            }
<?php
if ((isset($MM_update)) && ($MM_update == "EditGallery") && $viewLink != "#") {
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
                <h1> Edit Gallery</h1>
                <?php if (isset($Galleryid)) { ?>
                    <div class="NewButton"><a  class="colorboxLink" href="Upload.php" title="Add image to Gallery"> <img src="../resources/images/Icons/Add.png" height="22" width="22" alt="+" />Add image(s) </a></div>
                    <?php
                    $notTheOwner = $colname_rsAdmin != $galleryStruct->m_created_by ? true : false;

                    $theRemoveAlllink = "EditGallery.php?RemoveAll=True";
                    ?>
                    <div class="NewButton">
                        <a  onclick="deleteConfirmation('<?php echo $theRemoveAlllink; ?>', 'All Images');" href="#" title="Remove All Images"> 
                            <img src="../resources/images/Icons/Delete.png" height="22" width="22" alt="-" />Remove All 
                        </a>
                    </div>


                                                                <!--<div class="NewButton"><a href="#" title="Undo Changes"> <img src="../resources/images/Icons/Undo.png" height="22" width="22" alt="&sup;" />Undo Changes</a></div>-->

                    <?php
                    if ($notTheOwner == false || $user->m_access_level == 5) {
                        $theRemoveGallery = "EditGallery.php?DeleteId=" . $galleryStruct->m_id . "&amp;setDel=" . $notTheOwner;
                        ?>
                        <div class="NewButton">
                            <a  onclick="deleteConfirmation('<?php echo $theRemoveGallery; ?>', '<?php echo "This Gallery (" . $galleryStruct->m_title . ")"; ?>',<?php echo $notTheOwner == false ? "false" : "true"; ?>);" href="#" title="Delete Gallery"> 
                                <img src="../resources/images/Icons/Delete.png" height="22" width="22" alt="-" />Delete Gallery
                            </a>
                        </div>
                    <?php } ?>

                    <!-- Break -->
                    <div class="clear"></div>
                    <div class="Break"></div>
                    <!-- End of Break --> 
                    <!-- PageWrap -->
                    <div class="PageWrap"> 
                        <!--contactform-->
                        <div class="contactform">
                            <form action="<?php echo $editFormAction; ?>" method="POST" name="EditGallery" id="installForm">
                                <span id="sprytGalleryTitle">
                                    <span class="textfieldRequiredMsg">Gallery Title  is required.<br/></span><span class="textfieldMaxCharsMsg">Exceeded maximum number of characters of 120.<br /></span>
                                    <input type="text" name="title" value="<?php echo $galleryStruct->m_title; ?>"  class="inputtext2 controls" placeholder="Gallery Title:" >
                                </span><br />


                                <div class="publishswitch">
                                    <input type="checkbox" name="publish" class="publishswitch-checkbox" id="mypublishswitch" <?php if (!(strcmp($galleryStruct->m_publish, 1))) echo "checked"; ?>>
                                    <label class="publishswitch-label" for="mypublishswitch">
                                        <div class="publishswitch-inner"></div>
                                        <div class="publishswitch-switch"></div>
                                    </label>
                                </div> 

                                <br />
                                <?php
                                if (isset($Galleryid)) {
                                    // Get all the image name from database
                                    $_SESSION['DBImageFiles'] = explode(",", $galleryStruct->m_image_name);
                                    $_SESSION['DBImageThumbFiles'] = explode(",", $galleryStruct->m_thumb_name);
                                    $_SESSION['DBImageCaptions'] = (explode(",", $galleryStruct->m_image_caption));
                                    //print_r($_SESSION['DBImageFiles'] );
                                    // Loop through the array and add directory prefix to each item in array
                                    foreach ($_SESSION['DBImageFiles'] as &$value)
                                        $value = $UploadImgDir . $value;


                                    // Loop through the array and add directory prefix to each item in array	
                                    foreach ($_SESSION['DBImageThumbFiles'] as &$value)
                                        $value = $UploadThumbDir . $value;

                                    // TRUE if ImageFiles and imagename from database have the same key/value pairs.		
                                    $arraysAreEqual = ($_SESSION['ImageFiles'] == $_SESSION['DBImageFiles']) && ($_SESSION['ImageThumbFiles'] == $_SESSION['DBImageThumbFiles']) &&
                                            ($_SESSION['ImageCaptions'] == $_SESSION['DBImageCaptions']);

                                    // if the image files have already been set and is equal to the database images
                                    if ($arraysAreEqual == TRUE) {
                                        $GalleryImages = $_SESSION['ImageFiles'];
                                        $GalleryThumbImages = $_SESSION['ImageThumbFiles'];
                                        $GalleryCaption = $_SESSION['ImageCaptions'];

                                        //print_r($GalleryThumbImages);
                                        $GalleryDisplayImages = str_replace($UploadImgDir, $UploadImgUrl, $GalleryImages);
                                        $GalleryDisplayThumb = str_replace($UploadThumbDir, $UploadThumbUrl, $GalleryThumbImages);
                                    }
                                    // if the image files have not already been set and is not equal to the database images
                                    elseif (($arraysAreEqual != TRUE)) {
                                        $containAnyDBValue = False;
                                        ///loop through the database image files
                                        foreach ($_SESSION['DBImageFiles'] as $containValue) {
                                            /// check id the value of image files in the database exist in the imagefiles Session
                                            if (in_array($containValue, $_SESSION['ImageFiles'])) {
                                                $containAnyDBValue = True;
                                                break;
                                            }
                                        }

                                        if (empty($_SESSION['ImageFiles'])) {
                                            // Get all the image name from database
                                            $_SESSION['ImageFiles'] = $_SESSION['DBImageFiles'];
                                            $_SESSION['ImageThumbFiles'] = $_SESSION['DBImageThumbFiles'];
                                            $_SESSION['ImageCaptions'] = $_SESSION['DBImageCaptions'];

                                            $GalleryImages = $_SESSION['ImageFiles'];
                                            $GalleryThumbImages = $_SESSION['ImageThumbFiles'];
                                            $GalleryCaption = $_SESSION['ImageCaptions'];
                                            $GalleryDisplayImages = str_replace($UploadImgDir, $UploadImgUrl, $GalleryImages);
                                            $GalleryDisplayThumb = str_replace($UploadThumbDir, $UploadThumbUrl, $GalleryThumbImages);
                                        } elseif ($containAnyDBValue) {
                                            $GalleryImages = $_SESSION['ImageFiles'];
                                            $GalleryThumbImages = $_SESSION['ImageThumbFiles'];
                                            //print_r($GalleryThumbImages);
                                            $GalleryCaption = $_SESSION['ImageCaptions'];
                                            $GalleryDisplayImages = str_replace($UploadImgDir, $UploadImgUrl, $GalleryImages);
                                            $GalleryDisplayThumb = str_replace($UploadThumbDir, $UploadThumbUrl, $GalleryThumbImages);
                                        }
                                        /// if none of the values in the ImageFiles session contains the image in the database 
                                        elseif (($containAnyDBValue == False) && !empty($_SESSION['ImageFiles'])) {
                                            // this is a new Gallery to edit
                                            // remove a single variable in the session 
                                            unset($_SESSION['ImageFiles']);
                                            unset($_SESSION['ImageThumbFiles']);
                                            unset($_SESSION['ImageFile']);
                                            unset($_SESSION['ImageThumbFile']);
                                            unset($_SESSION['ImageCaptions']);
                                            unset($_SESSION['ImageCaption']);
                                            //Refresh the page to show the new Gallery image
                                            $refreshpage = filter_input(INPUT_SERVER, 'PHP_SELF');
                                            if (isset($query_string)) {
                                                $refreshpage .= (strpos($refreshpage, '?')) ? "&" : "?";
                                                $refreshpage .= $query_string;
                                            }
                                            //echo $refreshpage;
                                            ?>
                                            <div class="NewButton"><a href="<?php echo $refreshpage; ?>" title="Load Images"> <img src="../resources/images/Icons/Reload.png" height="22" width="22" alt="+" />Load Images</a></div>
                                            <?php
                                            //header("Location: $refreshpage");
                                        }
                                    }

                                    $DBGalleryImages = str_replace($UploadImgDir, "", ($_SESSION['ImageFiles']));
                                    $DBGalleryThumbImages = str_replace($UploadImgDir, "", ($_SESSION['ImageFiles']));
                                    $DBImageCaptions = $_SESSION['ImageCaptions'];
                                    //print_r ($DBGalleryImages);
                                    //print_r ($DBImageCaptions);
                                } else {
                                    // remove a single variable in the session 
                                    unset($_SESSION['ImageFiles']);
                                    unset($_SESSION['ImageThumbFiles']);
                                    unset($_SESSION['ImageFile']);
                                    unset($_SESSION['ImageThumbFile']);
                                    unset($_SESSION['ImageCaptions']);
                                    unset($_SESSION['ImageCaption']);
                                    unset($_SESSION['DBImageFiles']);
                                    unset($_SESSION['DBImageThumbFiles']);
                                    unset($_SESSION['DBImageCaptions']);
                                    ?>
                                    <h3> No Gallery is set for editing</h3>
                                    <?php
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
                                        $total = count($GalleryDisplayThumb) - 1;
                                        foreach ($GalleryDisplayThumb as $counter => $imageThumbLink) {
                                            ?>
                                            <li>
                                                <?php
                                                if ($counter != $total) {
                                                    $thecoverlink = "EditGallery.php?Galleryid=" . $galleryStruct->m_id . "&amp;cover=" . $counter;
                                                    ?>
                                                    <a class="ViewThumb" title="Make Album Cover" href="<?php echo $thecoverlink; ?>"> <img src="../resources/images/Icons/Gallery.png" width="22" height="22" alt="cover" /></a>
                                                <?php } ?>
                                                <a class="EditThumb" title="Edit Thumbnail" href="Thumbnail.php?index=<?php echo $counter; ?>&amp;ActionPage=<?php echo sfUtils::curPageURL(); ?>"> <img src="../resources/images/Icons/Edit.png" width="22" height="22" alt="Edit" /></a>
                                                <?php $thedeletelink = "EditGallery.php?Galleryid=" . $galleryStruct->m_id . "&amp;DeleteOne=" . $counter; ?>
                                                <a onclick="deleteConfirmation('<?php echo $thedeletelink; ?>', '<?php echo $GalleryCaption[$counter]; ?>');"  class="DeleteImage" title="Remove image" href="#"><img src="../resources/images/Icons/Delete.png" width="22" height="22" alt="Delete" /> </a> 
                                                <a class="colorbox" href="<?php echo $GalleryDisplayImages[$counter]; ?>" onerror="this.href='<?php echo $imageMissing; ?>'" title="<?php echo htmlentities($GalleryCaption[$counter]); ?>"> 
                                                    <img src="<?php echo $imageThumbLink; ?>"  onerror="this.src='<?php echo $imageMissing; ?>'" alt="<?php echo $galleryStruct->m_title; ?>"> </a> 
                                            </li>
                                            <?php
                                        }
                                        ?>

                                        <!--Default--> 
                                        <!--<li> <a class="EditThumb" title="Edith Thumbnail"> <img src="../resources/images/Icons/Edit.png" width="22" height="22" alt="Edit" /></a> <a class="DeleteImage" title="Remove image"><img src="../resources/images/Icons/Delete.png" width="22" height="22" alt="Delete" /> </a>  <a class="colorbox" href="../Uploads/default.png"> <span class="tp-info"><span>Snowflakes</span></span> <img src="../Uploads/GalleryThumbs/Snowflakes.png"   alt="Snowflakes"> </a> </li>-->

                                    </ul>
                                    <!--tp-grid Ends--> 
                                </div>
                                <!--wrapper Ends--> 
                                <br />
                                <input class="NewButton" type="submit" value="Save Gallery"  name="EditGallery">
                                <input name="id" type="hidden" id="Galleryid" value="<?php echo $galleryStruct->m_id; ?>" />
                                <input type="hidden" name="image_name" value="<?php echo implode(",", $DBGalleryImages); ?>">
                                <input type="hidden" name="thumb_name" value="<?php echo implode(",", $DBGalleryThumbImages); ?>">
                                <input type="hidden" name="image_caption" value="<?php echo implode(",", $DBImageCaptions); ?>">
                                <input type="hidden" name="edited" value="<?php echo time(); ?>">
                                <input type="hidden" name="edited_by" value="<?php echo $_SESSION['MM_Username']; ?>">
                                <input type="hidden" name="MM_update" value="EditGallery">
                            </form>
                            <p>&nbsp;</p>
                        </div>
                        <!--contactform Ends--> 

                    </div>
                    <!-- PageWrap Ends --> 
                <?php } else { ?>
                    <!-- Break -->
                    <div class="clear"></div>
                    <div class="Break"></div>
                    <!-- End of Break --> 

                    <!-- PageWrap -->
                    <div class="PageWrap">
                        <h2 class="SummaryHead">No Gallery id provided</h2>
                    </div>
                    <!--END of PageWrap--> 
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
                    var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytGalleryTitle", "none", {validateOn: ["change"], maxChars: 120});
        </script> 
        <!-- InstanceEndEditable -->
    </body>
    <!-- InstanceEnd --></html>
<?php
$SFconnects->close();
?>
