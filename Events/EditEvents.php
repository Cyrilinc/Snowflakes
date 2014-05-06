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
$php_self = filter_input(INPUT_SERVER, 'PHP_SELF');
// ** Logout the current user. **
$logoutAction = $php_self . "?doLogout=true";
$query_string = filter_input(INPUT_SERVER, 'QUERY_STRING');
if ((isset($query_string)) && ($query_string != "")) {
    $logoutAction .="&amp;" . htmlentities($query_string);
}
$doLogout = filter_input(INPUT_GET, 'doLogout');
$settingsConfig = Config::getConfig("settings", '../config/config.ini');
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
//The Default image
$uploadedFile = "";
// this will be the image that already exsits in the database record
// check if the upload file array is not empty
$formmessage = "";
if (empty($_FILES["uploadImage"]["name"])) {
    $File_is_Uploaded = true;
} else {
    $File_is_Uploaded = sfImageProcessor::uploadSingleImage($_FILES['uploadImage'], '../config/config.ini', $uploadedFile, $formmessage, false);
    $formmessage .=" <br>" . $targetFile;
}

//echo "<pre>";
//print_r($_FILES['uploadImage']);
//echo "</pre>";
//echo "<hr />";
//echo $formmessage;

$editFormAction = $php_self;
if (isset($query_string)) {
    $editFormAction .= "?" . htmlentities($query_string);
}

$config = Config::getConfig("db", '../config/config.ini');
$sqlArray = array('type' => $config['type'], 'host' => $config['host'], 'username' => $config['username'], 'password' => sfUtils::decrypt($config['password'], $config['key']), 'database' => $config['dbname']);
$SFconnects = new sfConnect($sqlArray);
$SFconnects->connect(); // Connect to database

$MM_update = filter_input(INPUT_POST, 'MM_update');
$viewLink = "#";
if ((isset($MM_update)) && ($MM_update == "editform") && ($File_is_Uploaded == TRUE)) {

    $oldImage = eventStruct::getImageNameById($SFconnects, $_POST['id']);
    $_POST['image_name'] = strlen($uploadedFile) == 0 ? $oldImage : $uploadedFile;
    $_POST['publish'] = isset($_POST['publish']) ? "1" : "0";
    $_POST['event_date'] = sfUtils::dateToSql(filter_input(INPUT_POST, 'event_date'));
    $_POST['end_date'] = sfUtils::dateToSql(filter_input(INPUT_POST, 'end_date'));

    $eventStruct = new eventStruct();
    $eventStruct->populate($_POST);

    if (!$eventStruct->UpdateEvent($SFconnects)) {
        $formmessage.= " <br>" . $SFconnects->getMessage() . '<br>';
    } else {
        if ($eventStruct->m_image_name != $oldImage) {
            sfUtils::Deletefile($settingsConfig['uploadGalleryDir'] . $oldImage);
        }
        $eventID = $eventStruct->getEventID($SFconnects);
        $viewLink = "ViewEvent.php?Eventid=$eventID";
        // Check Trigger exist , if not then use manual trigger
        sfUtils::checkTrigger($SFconnects, $eventID, 'event', "UPDATE");
        $formmessage.='<p>'
                . '<a href="' . $viewLink . '" title="view it">"' . $eventStruct->m_title . '"</a> was editted successfully. '
                . '<span class="icon success"></span>'
                . '</p>';
    }
}

$colname_RSEditevent = -1;
$Eventid = filter_input(INPUT_GET, 'Eventid');
if (isset($Eventid)) {
    $colname_RSEditevent = $Eventid;
}
$eventStruct = new eventStruct();
$eventStruct->getEventByid($SFconnects, $colname_RSEditevent);

$colname_rsAdmin = "-1";
if (isset($_SESSION['MM_Username'])) {
    $colname_rsAdmin = $_SESSION['MM_Username'];
}

$user = new userStruct();
$user->getUserByUsername($SFconnects, $colname_rsAdmin);

$UploadImgUrl = $settingsConfig['m_sfGalleryUrl'];
$imageMissing = $UploadImgUrl . "missing_default.png";
?>

<!DOCTYPE HTML>
<html ><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Cyril Inc">
        <meta name="viewport" content="width=device-width, maximum-scale = 1, minimum-scale=1" />
        <!-- InstanceBeginEditable name="doctitle" -->
        <title>Edit Events</title>
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
        <!--[if IE]>
        <link rel="stylesheet" type="text/css" href="../resources/css/style.css"/>
        <![endif]-->

        <!--[if IEMobile]> 
           <link rel="stylesheet" type="text/css" href="../resources/css/Mobile.css"/>
        <![endif]-->
        <?php if (isset($Eventid)) { ?>
            <script type="text/javascript" src="../resources/Js/sfEditor.js"></script>
            <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&amp;sensor=false&amp;libraries=places"></script>
            <script type="text/javascript">
    <?php $latlong = explode(",", $eventStruct->m_lat_long); ?>
                var preLat =<?php echo $latlong[0]; ?>;
                var preLng =<?php echo $latlong[1]; ?>;
            </script>
            <script type="text/javascript" src="../resources/Js/location.js"></script>
            <script src="../SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
            <script src="../SpryAssets/SpryValidationTextarea.js" type="text/javascript"></script>
            <link href="../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css">
            <link href="../SpryAssets/SpryValidationTextarea.css" rel="stylesheet" type="text/css">
            <link href="../resources/css/jquery-ui-1.10.4.snowflakes.css" rel="stylesheet" type="text/css" />
            <script src="../resources/Js/jquery-ui-1.10.4.snowflakes.js"></script>
            <link rel="stylesheet" title="text/css" href="../resources/css/fontstyle.css" />
            <script type="text/javascript">
                var datefield = document.createElement("input");
                datefield.setAttribute("type", "date");
                if (datefield.type !== "date") { //if browser doesn't support input type="date", load files for jQuery UI Date Picker

                    $(function($) { //on document.ready
                        $('#snowdate').datepicker({
                            defaultDate: "+1w",
                            changeMonth: true,
                            changeYear: true,
                            onClose: function(selectedDate) {
                                $("#snowdate2").datepicker("option", "minDate", selectedDate);
                            }
                        });
                        $('#snowdate2').datepicker({
                            defaultDate: "+1w",
                            changeMonth: true,
                            changeYear: true,
                            onClose: function(selectedDate) {
                                $("#snowdate2").datepicker("option", "maxDate", selectedDate);
                            }
                        });
                        $("#snowdate,#snowdate2").datepicker("option", "dateFormat", 'dd/mm/yy');
                        $('#snowdate').datepicker('setDate', '<?php if (isset($eventStruct->m_event_date)) echo sfUtils::dateFromSql($eventStruct->m_event_date); ?>');
                        $('#snowdate2').datepicker('setDate', '<?php if (isset($eventStruct->m_end_date)) echo sfUtils::dateFromSql($eventStruct->m_end_date); ?>');
                        $("#snowdate,#snowdate2").datepicker("option", "showAnim", "clip");

                    });

                }

                $(function() {
                    $(".dialog-message").dialog({
                        modal: true,
                        buttons: {
                            "Edit": function() {
                                $(this).dialog("close");
                            },
                            "View": function() {
                                window.location = "<?php echo $viewLink ?>";
                            }
                        }
                    });
                });
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
                        <div class="Logo"><img alt="Snowflakes" class="logo" src="../resources/images/Snowflakes.png" width="165" height="55" /></div>
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

                                <li class="active" id="AtvNewButton"><a href="index.php" title="Snowflake Events" class="yellow" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['total']); ?>"> <img src="../resources/images/Icons/Events.png" height="22" width="22"  alt="Events" /> Events </a>
                                    <ul>
                                        <li><a href="index.php?publish=1" title="View Published Events" class="yellow" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['published']); ?>"><img src="../resources/images/Icons/EventsPublished.png" height="22" width="22" alt="Published" /> Published </a></li>
                                        <li><a href="index.php?publish=0" title="View Unublished Events" class="yellow" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['unpublished']); ?>"><img src="../resources/images/Icons/EventsUnpublished.png" height="22" width="22" alt="UnPublished" /> UnPublished </a></li>
                                        <li><a href="OutputView.php" title="View output Events" class="yellow" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfEvents']['published']); ?>"><img src="../resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
                                    </ul>
                                </li>
                                <li><a href="../Gallery/index.php"  title="Snowflakes Gallery" class="green" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['total']); ?>"> <img src="../resources/images/Icons/Gallery.png" height="22" width="22"  alt="+ " /> Gallery</a>
                                    <ul>
                                        <li><a href="../Gallery/index.php?publish=1" title="View Published Gallery" class="green" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"><img src="../resources/images/Icons/Publish.png" height="22" width="22" alt="View" /> Published </a></li>
                                        <li><a href="../Gallery/index.php?publish=0" title="View Unublished Gallery" class="green" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['unpublished']); ?>"><img src="../resources/images/Icons/UnPublish.png" height="22" width="22" alt="unpublish" /> UnPublished </a></li>
                                        <li><a href="../Gallery/OutputView.php" title="View Output Gallery" class="green" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"><img src="../resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
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

                <h1>Edit Events</h1>

                <!-- Break -->
                <div class="clear"></div>
                <div class="Break"></div>
                <!-- End of Break --> 

                <!-- PageWrap -->
                <div class="PageWrap">
                    <?php if (isset($Eventid)) { ?>
                        <?php
                        if (!empty($formmessage)) {
                            echo sfUtils::dialogMessage("Edit Event", $formmessage);
                        }
                        ?>

                        <!--contactform-->
                        <div class="contactform">
                            <form action="<?php echo $editFormAction; ?>" method="POST" enctype="multipart/form-data" name="editform" id="installForm">
                                <span id="spryTitle">
                                    <span class="textfieldRequiredMsg">A Title is required.<br /></span><span class="textfieldMaxCharsMsg">Exceeded maximum number of characters of 120.<br /></span>
                                    <input type="text" name="title" value="<?php echo $eventStruct->m_title; ?>" size="32" class="inputtext2 controls" placeholder="Event Title">
                                </span><br />
                                <div id="sfeditor_toolbar" class="inputtext2 controls">
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('bold');" title="bold" class="switchButton" /><label class="icon-bold" id="sfeditor_bold"></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('italic');" title="italic"/> <label class="icon-italic" id="sfeditor_italic"></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('underline');" title="underline"/><label class="icon-underline" id="sfeditor_underline"></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('strikethrough');" title="strikethrough"/><label class="icon-strikethrough" id="sfeditor_strikethrough"></label></span>
                                    <span class="switchSpacer"></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('createLink', promptValue('Link Url'));" title="link"/><label class="icon-link" id="sfeditor_link"></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('unlink');" title="unlink"/><label class="icon-link2" id="sfeditor_unlink"></label></span>
                                    <span class="switchSpacer"></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('RemoveFormat', 'h1');" title="remove format"/><label id="sfeditor_removeformat"></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('formatBlock', 'h1');" title="h1"/><label class="icon-h1" id="sfeditor_h1"></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('formatBlock', 'h2');" title="h2"/><label class="icon-h2" id="sfeditor_h2"></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('formatBlock', 'h3');" title="h3"/><label class="icon-h3" id="sfeditor_h3"></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('formatBlock', 'h4');" title="h4"/><label class="icon-h4" id="sfeditor_h4"></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('formatBlock', 'h5');" title="h5"/><label class="icon-h5" id="sfeditor_h5"></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('formatBlock', 'h6');" title="h6"/><label class="icon-h6" id="sfeditor_h6"></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('insertparagraph');" title="paragraph"/><label class="icon-paragraph" id="sfeditor_paragraph"></label></span>
                                    <span class="switchSpacer"></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('insertimage', promptValue('Image Url'));" title="image"/><label class="icon-image" id="sfeditor_image"></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('inserthorizontalrule');" title="hr"/><label id="sfeditor_hr"></label></span>
                                    <span class="switchSpacer"></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('undo');" title="undo"/><label class="icon-undo" id="sfeditor_undo"></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('redo');" title="redo"/><label class="icon-redo" id="sfeditor_redo"></label></span>
                                    <span class="switchSpacer"></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('backcolor', promptValue('Background Color'));" title="backcolor"/><label class="icon-droplet" id="sfeditor_backcolor"></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('forecolor', promptValue('Foreground Color'));" title="fontcolor"/><label class="icon-droplet2" id="sfeditor_fontcolor"></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('hilitecolor', promptValue('Hilight Color'));" title="hilightcolor"/><label class="icon-palette" id="sfeditor_hilightcolor"></label></span>
                                    <span class="switchSpacer"></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('subscript');" title="subscript"/><label class="icon-subscript" id="sfeditor_subscript"><sub>2</sub></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('superscript');" title="superscript"/><label class="icon-superscript" id="sfeditor_superscript"><sup>2</sup></label></span>
                                    <span class="switchSpacer"></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('justifyleft');" title="left"/><label class="icon-paragraph-left" id="sfeditor_left"></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('justifycenter');" title="center"/><label  class="icon-paragraph-center" id="sfeditor_center"></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('justifyright');" title="right"/><label  class="icon-paragraph-right" id="sfeditor_right"></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('justifyfull');" title="justify"/><label  class="icon-paragraph-justify" id="sfeditor_justify"></label></span>
                                    <span class="switchSpacer"></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('insertorderedlist');" title="ol"/><label class="icon-numbered-list" id="sfeditor_ol"></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('insertunorderedlist');" title="ul"/><label class="icon-list" id="sfeditor_ul"></label></span>
                                    <span class="switchSpacer"></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('inserthtml', promptValue('Html'));" title="html"/><label class="icon-file-xml" id="sfeditor_html"></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('indent');" title="indent"/><label class="icon-indent-increase" id="sfeditor_indent"></label></span>
                                    <span class="switchButton"><input type="checkbox" onclick="sfEditorAction('outdent');" title="outdent"/><label class="icon-indent-decrease" id="sfeditor_outdent"></label></span>
                                    <span class="switchSpacer"></span>

                                    <select onchange="sfEditorAction('fontname', this.value);" class="inputtext3 sfeditor_select">
                                        <option style="font-family:Courier New">Courier New</option>
                                        <option style="font-family:Times New Roman">Times New Roman</option>
                                        <option style="font-family:Tahoma">Tahoma</option>
                                        <option style="font-family:Verdana" value="Verdana">Verdana</option>
                                        <option style="font-family:Georgia">Georgia</option>
                                        <option style="font-family:Impact">Impact</option>
                                    </select>
                                    <select onchange="sfEditorAction('FontSize', this.value);" class="inputtext3 sfeditor_select">
                                        <option value="1">10</option>
                                        <option value="2">12</option>
                                        <option value="3">16</option>
                                        <option value="4">18</option>
                                        <option value="5">24</option> 
                                        <option value="6">32</option>
                                        <option value="7">48</option>
                                    </select>
                                </div>
                                <span id="sprytBodytext">
                                    <span class="textareaRequiredMsg">A Description is required.<br /></span>
                                    <iframe name="richTextField" id="richTextField" style="min-height:300px;" class="sfeditor_textbox inputtext2 controls"></iframe><br>
                                    <textarea style="display:none;" name="body_text" cols="50" rows="8"  id="Pmessage2" class="controls" placeholder="Event description"> <?php echo $eventStruct->m_body_text; ?> </textarea>
                                </span><br />
                                <label>Change Image:</label>
                                <br />
                                <div class="SnowflakeImageSmall">
                                    <a class="colorbox controls" href="<?php echo $UploadImgUrl . $eventStruct->m_image_name; ?>" onerror="this.href='<?php echo $imageMissing; ?>'" title="Event Image" > 
                                        <img src="<?php echo $UploadImgUrl . $eventStruct->m_image_name; ?>" onerror="this.src='<?php echo $imageMissing; ?>'"   alt="Event Image" />
                                    </a>
                                </div>
                                <input type="file" class="inputtext3 controls" name="uploadImage" />
                                <br />

                                <div class="publishswitch">
                                    <input type="checkbox" name="publish" class="publishswitch-checkbox" id="mypublishswitch" <?php if (!(strcmp($eventStruct->m_publish, 1))) echo "checked"; ?>/>
                                    <label class="publishswitch-label" for="mypublishswitch">
                                        <div class="publishswitch-inner"></div>
                                        <div class="publishswitch-switch"></div>
                                    </label>
                                </div> 
                                <div class="clear"></div>
                                <br />

                                <span id="sprystartTime" class="spanside">
                                    <label for="snowtime">Start Time:</label>
                                    <br />
                                    <span class="textfieldRequiredMsg">Event start time is required.</span>
                                    <input type="time" name="event_time" id="snowtime" value="<?php echo substr($eventStruct->m_event_time, 0, 5); ?>" class="inputtext2 controls">
                                </span>

                                <span id="sprystartDate" class="spanside">
                                    <label for="snowdate">Start Date:</label>
                                    <br />
                                    <span class="textfieldRequiredMsg">Event start date is required.</span>
                                    <input type="date" name="event_date"  id="snowdate" value="<?php if (isset($eventStruct->m_event_date)) echo $eventStruct->m_event_date; ?>" class="inputtext2 controls">
                                </span>

                                <span id="spryendTime" class="spanside">
                                    <label for="snowtime2">End Time:</label>
                                    <br />
                                    <span class="textfieldRequiredMsg">Event end time is required.</span>
                                    <input type="time" name="end_time" id="snowtime2" value="<?php echo substr($eventStruct->m_end_time, 0, 5); ?>" class="inputtext2 controls">
                                </span>


                                <span id="spryendDate" class="spanside">
                                    <label for="snowdate2">End Date:</label>
                                    <br />
                                    <span class="textfieldRequiredMsg">Event end date is required.</span>
                                    <input type="date" name="end_date" id="snowdate2" value="<?php
                                    if (isset($eventStruct->m_end_date)) {
                                        echo $eventStruct->m_end_date;
                                    }
                                    ?>" class="inputtext2 controls">
                                </span><br />

                                <span id="spryLocation">
                                    <span class="textfieldRequiredMsg">A Location is required.<br /></span>
                                    <input type="text" id="pac-input" name="location" value="<?php echo $eventStruct->m_location; ?>" class="inputtext3 controls">
                                    <div id="type-selector" class="controls">
                                        <input type="radio" name="type" id="changetype-all" checked="checked">
                                        <label for="changetype-all">All</label>

                                        <input type="radio" name="type" id="changetype-establishment">
                                        <label for="changetype-establishment">Establishments</label>

                                        <input type="radio" name="type" id="changetype-geocode">
                                        <label for="changetype-geocode">Geocodes</label>
                                    </div>
                                    <div id="map-canvas"></div>
                                </span>
                                <br />
                                <input type="submit" value="Change Event" class="NewButton" onClick="transferEditorData('editform');">
                                <input type="hidden" name="image_name" value="<?php
                                if (strlen($uploadedFile) == 0) {
                                    $uploadedFile = $eventStruct->m_image_name;
                                    echo $uploadedFile;
                                } else {
                                    echo $uploadedFile;
                                }
                                ?>" />
                                <input name="id" type="hidden" id="Eventid" value="<?php echo $eventStruct->m_id; ?>" />
                                <input type="hidden" name="lat_long" id="latlong" value="<?php echo $eventStruct->m_lat_long; ?>">
                                <input type="hidden" name="edited" value="<?php echo time(); ?>">
                                <input type="hidden" name="edited_by" value="<?php echo $_SESSION['MM_Username']; ?>">
                                <input type="hidden" name="MM_update" value="editform">
                            </form>
                            <p>&nbsp;</p>
                        </div>
                        <!--End of contactform--> 
                    <?php } else { ?>
                        <h2 class="SummaryHead">No Event id provided</h2>
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
                            <li><a href="http://cyrilinc.blogspot.co.uk/" target="_blank" title="Cyril Inc on Blogger.com"> <img src="../resources/images/Icons/Blogger.png" alt="Blogger" /></a></li>
                            <li><a href="https://www.facebook.com/pages/Cyril-Inc/151728454900027" target="_blank" title="Cyril Inc on Facebook"> <img src="../resources/images/Icons/Facebook.png" alt="Facebook" /></a></li>
                            <li><a href="https://twitter.com/intent/follow?original_referer=http%3A%2F%2Ftwitter.com%2Fabout%2Fresources%2Ffollowbutton&amp;region=follow&amp;screen_name=CyrilInc&amp;source=followbutton&amp;variant=1.0" target="_blank" > <img src="../resources/images/Icons/Twitter.png" alt="Twitter" /></a></li>
                            <li><a href="http://delicious.com/cyrilinc" target="_blank" title="Cyril Inc on delicious"> <img src="../resources/images/Icons/delicious.png" alt="delicious" /></a></li>
                            <li><a href="http://pinterest.com/cyrilinc" target="_blank" title="Cyril Inc on Pinterest"> <img src="../resources/images/Icons/Pinterest.png" alt="Pinterest" /></a></li>
                            <li><a href="https://plus.google.com/117444390192468783291" target="_blank" title="Cyril Inc on GooglePlus"> <img src="../resources/images/Icons/GooglePlus.png" alt="GooglePlus" /></a></li>
                            <li><a href="http://www.stumbleupon.com/stumbler/cyrilinc" target="_blank" title="Cyril Inc on stumbleupon"> <img src="../resources/images/Icons/stumbleupon.png" alt="stumbleupon" /></a></li>
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
        <?php if (isset($Eventid)) { ?>
            <script type="text/javascript">
                var sprytextfield1 = new Spry.Widget.ValidationTextField("spryTitle", "none", {validateOn: ["blur", "change"], maxChars: 120});
                var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytBodytext", {validateOn: ["blur"]});
                var sprytextfield2 = new Spry.Widget.ValidationTextField("spryLocation", "none", {validateOn: ["blur"]});
                var sprystartDate = new Spry.Widget.ValidationTextField("sprystartDate");
                var sprystartTime = new Spry.Widget.ValidationTextField("sprystartTime");
                var spryendTime = new Spry.Widget.ValidationTextField("spryendTime");
                var spryendDate = new Spry.Widget.ValidationTextField("spryendDate");
            </script>
        <?php } ?>
        <!-- InstanceEndEditable -->
    </body>
    <!-- InstanceEnd --></html>
<?php
$SFconnects->close();
?>
