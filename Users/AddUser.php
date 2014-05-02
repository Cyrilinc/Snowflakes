<?php
require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';
require_once '../lib/sfImageProcessor.php';

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
//The Default image
$targetFile = "default.png";
if (empty($_FILES["uploadImage"]["name"])) {
    $File_is_Uploaded = True;
} else {
    $File_is_Uploaded = sfImageProcessor::uploadSingleImage($_FILES['uploadImage'], '../config/config.ini', $targetFile, $formmessage, false);
    //$formmessage .=" <br>" . $targetFile;
}

$config = Config::getConfig("db", '../config/config.ini');
$sqlArray = array('type' => $config['type'], 'host' => $config['host'], 'username' => $config['username'], 'password' => sfUtils::decrypt($config['password'], $config['key']), 'database' => $config['dbname']);
$SFconnects = new sfConnect($sqlArray);
$SFconnects->connect(); // Connect to database
// *** Redirect if username exists
$MM_flag = "MM_insert";
$loginFoundUser=0;
$MM_insert_flag = filter_input(INPUT_POST, $MM_flag);
$postUsername = filter_input(INPUT_POST, 'username');
if (isset($MM_insert_flag)) {
    $loginUsername = $postUsername;
    $loginFoundUser = sfUtils::userExits($SFconnects, $loginUsername);
}

$editFormAction = $php_self;
if (isset($query_string)) {
    $editFormAction .= "?" . htmlentities($query_string);
}
$MM_insert = filter_input(INPUT_POST, "MM_insert");
$viewLink = "#";
if ((isset($MM_insert)) && ($MM_insert == "form1") && $loginFoundUser <= 0 && ($File_is_Uploaded == TRUE)) {

    $userStruct = new userStruct();
    $userStruct->init($postUsername, $_POST['password2'], $_POST['email'], $_POST['access_level'],$targetFile);

    if (!$userStruct->AddUser($SFconnects)) {
        $formmessage.= "Could not insert the new User. <br>" . $SFconnects->getMessage() . '<br>';
    } else {

        $newUserID = $userStruct->getUserID($SFconnects);
        $viewLink = "Account.php?userId=$newUserID";
        //$formmessage.=$userStruct->printuser();
        $formmessage.='<p>'
                . '<a href="' . $viewLink . '" title="view it">"' . $userStruct->m_username . '"</a> was added successfully. '
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
<html ><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Cyril Inc">
        <meta name="viewport" content="width=device-width, maximum-scale = 1, minimum-scale=1" />
        <!-- InstanceBeginEditable name="doctitle" -->
        <title>Add Snowflakes Admin User</title>
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
        <link rel="stylesheet" type="text/css" href="resources/css/style.css"/>
        <![endif]-->

        <!--[if IEMobile]> 
           <link rel="stylesheet" type="text/css" href="resources/css/Mobile.css"/>
        <![endif]-->

        <script src="../SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
        <script src="../SpryAssets/SpryValidationPassword.js" type="text/javascript"></script>
        <script src="../SpryAssets/SpryValidationConfirm.js" type="text/javascript"></script>
        <script src="../SpryAssets/SpryValidationSelect.js" type="text/javascript"></script>
        <link href="../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
        <link href="../SpryAssets/SpryValidationPassword.css" rel="stylesheet" type="text/css" />
        <link href="../SpryAssets/SpryValidationConfirm.css" rel="stylesheet" type="text/css" />
        <link href="../SpryAssets/SpryValidationSelect.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/jquery-ui-1.10.4.snowflakes.css" rel="stylesheet" type="text/css" />
        <script src="../resources/Js/jquery-ui-1.10.4.snowflakes.js"></script>
        <script>
            $(function() {
                $(".dialog-message").dialog({
                    modal: true,
                    buttons: {
                        "Add more": function() {
                            $(this).dialog("close");
                        },
                        "View": function() {
                            window.location = "<?php echo $viewLink ?>";
                        }
                    }
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
                                        <li><a href="../ViewSnowflakes.php?publish=1" title="View Published flakes" class="blue" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['published']); ?>"><img src="../resources/images/Icons/Publish.png" height="22" width="22" alt="View" /> Published </a></li>
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
                                <li><a href="../Gallery/index.php"  title="Snowflakes Gallery" class="green" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['total']); ?>"> <img src="../resources/images/Icons/Gallery.png" height="22" width="22"  alt="+ " /> Gallery</a>
                                    <ul>
                                        <li><a href="../Gallery/index.php?publish=1" title="View Published Gallery" class="green" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"><img src="../resources/images/Icons/GalleryPublished.png" height="22" width="22" alt="View" /> Published </a></li>
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
                <h1>Add Snowflakes Admin User</h1>

                <?php
//if there is a row in the database, the username was found - can not add the requested username
                if ($loginFoundUser >= 1) {
                    ?>
                    <div class="SnowflakeHead"><span class="icon error"></span> The username "<?php echo $postUsername; ?>" already exists </div>
                <?php }
                ?>
                <!-- Break -->
                <div class="clear"></div>
                <div class="Break"></div>
                <!-- End of Break -->
                <!-- PageWrap -->
                <div class="PageWrap">
                    <?php
                    if (!empty($formmessage)) {
                        echo sfUtils::dialogMessage("Add User", $formmessage);
                    }
                    ?>

                    <!--contactform-->
                    <div class="contactform">
                        <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="installForm">

                            <span id="spryAdminName">
                                <span class="textfieldRequiredMsg">Username is required.<br /></span>
                                <input class="inputtext2 controls" type="text" name="username" value="<?php
                                if (isset($postUsername)) {
                                    echo $postUsername;
                                }
                                ?>" placeholder="Admin Username must be between 8 and 20 characters" pattern="^[a-zA-Z][a-zA-Z0-9-_\.]{8,20}$" required="required"/>
                            </span><br />

                            <span id="spryAdminEmail">
                                <span class="textfieldRequiredMsg">Email is required.<br /></span><span class="textfieldInvalidFormatMsg">Invalid email format.<br /></span>
                                <input class="inputtext2 controls" type="text" name="email" value="<?php if (isset($_POST['password'])) echo $_POST['email']; ?>" placeholder="Admin's Email" />
                            </span><br />
                            <span id="spryAdminPass">
                                <span class="passwordRequiredMsg">Password is required.<br /></span>
                                <input class="inputtext2 controls" type="password" name="password" value="<?php if (isset($_POST['password'])) echo $_POST['password']; ?>" id="Password" placeholder="Password" />
                            </span><br />

                            <span id="spryPassconfirm">
                                <span class="confirmRequiredMsg">Confirmation password is required.<br /></span><span class="confirmInvalidMsg">The password don't match.<br /></span>
                                <input class="inputtext2 controls" type="password" name="password2" value="<?php if (isset($_POST['password'])) echo $_POST['password']; ?>" placeholder="Confirm Password" />
                            </span><br />
                            <label>Profile Image:</label>
                            <br />
                            <input type="file" class="inputtext2 controls" name="uploadImage" />
                            <br />
                            <br />
                            <span id="SelectAcLvl">

                                <?php
                                if (isset($_POST['access_level']))
                                    $value = $_POST['access_level'];
                                else
                                    $value = 3;
                                ?>
                                <select name="access_level" class="inputtext2 controls">
                                    <option value="1" <?php if ($value == 1) echo 'selected'; ?> >Author/ Editor 1</option>
                                    <option value="2" <?php if ($value == 2) echo 'selected'; ?> >Publisher 2</option>
                                    <option value="3" <?php if ($value == 3) echo 'selected'; ?> >Manager 3</option>
                                    <option value="4" <?php if ($value == 4) echo 'selected'; ?> >Administrator 4</option>
                                    <option value="5" <?php if ($value == 5) echo 'selected'; ?> >Super Administrator 5</option>
                                </select>
                                <span class="selectRequiredMsg">Please select an item.</span></span><br />
                            <input class="NewButton" type="submit" value="Add Admin" />
                            <input name="ALhidden" type="hidden" value="1" />
                            <input type="hidden" name="MM_insert" value="form1" />
                        </form>
                        <p>&nbsp;</p>
                    </div>
                    <!--End of contactform-->

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
        <script type="text/javascript">
            var sprytextfield1 = new Spry.Widget.ValidationTextField("spryAdminName", "none", {validateOn: ["blur"]});
            var sprypassword1 = new Spry.Widget.ValidationPassword("spryAdminPass", {validateOn: ["blur"]});
            var spryconfirm1 = new Spry.Widget.ValidationConfirm("spryPassconfirm", "Password", {validateOn: ["blur", "change"]});
            var sprytextfield2 = new Spry.Widget.ValidationTextField("spryAdminEmail", "email", {validateOn: ["blur"]});
            var spryselect1 = new Spry.Widget.ValidationSelect("SelectAcLvl");
        </script>

        <!-- InstanceEndEditable -->
    </body>
    <!-- InstanceEnd -->
</html>

<?php
$SFconnects->close();
?>