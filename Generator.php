<?php
require_once 'lib/sf.php';
require_once 'lib/sfConnect.php';
require_once 'config/Config.php';

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
$settingsConfig = Config::getConfig("settings", 'config/config.ini');
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

$colname_rscheckAdmin = "-1";
if (isset($_SESSION['MM_Username'])) {
    $colname_rscheckAdmin = $_SESSION['MM_Username'];
}

$config = new databaseParam('config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$SFconnects->connect(); // Connect to database

$user = new userStruct();
$user->getUserByUsername($SFconnects, $colname_rscheckAdmin);

$url = sfUtils::curPageURL();
$Shareurl = str_replace("Generator.php", "Out.php", $url);
$csslink = str_replace("Generator.php", "resources/css/", $url);
$JSlink = str_replace("Generator.php", "resources/Js/", $url);
$ShareEventsurl = str_replace("Generator.php", "Events/Out.php", $url);
$ShareGallerysurl = str_replace("Generator.php", "Gallery/Out.php", $url);
?>
<!DOCTYPE HTML>
<html lang="en" ><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Cyril Inc">
        <meta name="viewport" content="width=device-width, maximum-scale = 1, minimum-scale=1" />
        <!-- InstanceBeginEditable name="doctitle" -->
        <title>Snowflakes | Code Generator</title>
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
        <script src="SpryAssets/SpryTabbedPanels.js" type="text/javascript"></script>
        <script type="text/javascript">

            $(document).ready(function SnowflakesCG() {
                //jquery
                $(location).attr('href');
                var pageToGo = "Out.php";
                //pure javascript
                var pathname = window.location.pathname;
                var generatorLink = window.location + "";
                var NewgeneratorLink = generatorLink.replace("Generator.php", pageToGo);

                GeneratedLocation = NewgeneratorLink;
                return NewgeneratorLink;
            });
            
            $(document).ready(function() {
                snowflakesCount("sse/snowflakesCount.php");
            });
        </script>
        <link href="SpryAssets/SpryTabbedPanels.css" rel="stylesheet" type="text/css" />
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
                                <li><a href="ViewSnowflakes.php" title="Snowflakes" class="blue" data-bubble="<?php echo sfUtils::comapact99($_SESSION['Snowflakes']['total']); ?>"> <img src="resources/images/Icons/Snowflakes.png" height="22" width="22"  alt="Snowflakes" /> Snowflakes </a>
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
                                        <li><a href="Gallery/index.php?publish=0" title="View Unublished Gallery" class="green" id="SfGallery_published" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"><img src="resources/images/Icons/GalleryUnpublished.png" height="22" width="22" alt="unpublish" /> UnPublished </a></li>
                                        <li><a href="Gallery/OutputView.php" title="View output Gallery" class="green" id="SfGallery_published2" data-bubble="<?php echo sfUtils::comapact99($_SESSION['SfGallery']['published']); ?>"> <img src="resources/images/Icons/Output.png" height="22" width="22" alt="Output" /> View Output</a></li>
                                    </ul>
                                </li>
                                <?php
                                if ($user->m_access_level == 5 || $user->m_access_level == 4) {
                                    ?>
                                    <li class="active" id="AtvNewButton">
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
                <h1>Snowflakes Code Generator</h1>

                <!-- Break -->
                <div class="clear"></div>
                <div class="Break"></div>
                <!-- End of Break --> 

                <!--PageWrap-->
                <div class="PageWrap" id="Snowflakes">
                    <!--TabbedPanels-->
                    <div id="GeneratorPanel" class="TabbedPanels">
                        <ul class="TabbedPanelsTabGroup">
                            <li class="TabbedPanelsTab" tabindex="0">Snowflakes JPC Code</li>
                            <li class="TabbedPanelsTab" tabindex="0">Events JPC Code</li>
                            <li class="TabbedPanelsTab" tabindex="0">Gallery JPC Code</li>
                            <li class="TabbedPanelsTab" tabindex="0"> CSS &gt; 999px</li>
                            <li class="TabbedPanelsTab" tabindex="0"> CSS &lt; 999px</li>
                            <li class="TabbedPanelsTab" tabindex="0">Gallery CSS</li>
                        </ul>
                        <div class="TabbedPanelsContentGroup">
                            <div class="TabbedPanelsContent">
                                <p></p>
                                <h2>Snowflakes Javascript code</h2>
                                <p></p>
                                <p>This is Ideal for HTML files, first create a div with id called Snowflakes then add the code below inside the Head tag of the page you want to display your published snowflakes. This code is generated provided that you have installed snowflakes on your Hosting server and the location is uniquely generated to point directly to your snowflake output file.</p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
Copy &amp; paste Id tag below to the body tag of your webpage or optionally use the Php Code

	&lt;div id=&quot;Snowflakes&quot;&gt; &lt;/div&gt;

Copy &amp; paste the javascript below in Head tag of your webpage

    &lt;script type=&quot;text/javascript&quot;&gt;
    
    function GetQuery(){
			  // This function is anonymous, is executed immediately and 
			  // the return value is assigned to QueryString!
			  var query_string = {};
			  var query = window.location.search.substring(1);
			  var vars = query.split("&amp;");
			  for (var i=0;i&lt;vars.length;i++) {
				var pair = vars[i].split("=");
					// If first entry with this name
				if (typeof query_string[pair[0]] === "undefined") {
				  query_string[pair[0]] = pair[1];
					// If second entry with this name
				} else if (typeof query_string[pair[0]] === "string") {
				  var arr = [ query_string[pair[0]], pair[1] ];
				  query_string[pair[0]] = arr;
					// If third or later entry with this name
				} else {
				  query_string[pair[0]].push(pair[1]);
				}
			  } 
				return query_string;
			} 
			
    
           $(document).ready(
            function loadSnowflakes() {
            	var QueryString =GetQuery();
                 var output; 
				 for (property in QueryString) {
  				output += property + ': ' + QueryString[property]+'; ';
				}
                if(output.indexOf("undefined: undefined") !== -1){
                $('#Snowflakes').load('<?php echo $Shareurl; ?>');
                } else{
                $('#Snowflakes').load('<?php echo $Shareurl; ?>?pageNum_rsOut='+QueryString.pageNum_rsOut+'&amp;totalRows_rsOut=' + QueryString.totalRows_rsOut);
                }
            });
            
    &lt;/script&gt;
    
                                </pre>
                                <p></p>
                                <h2>CSS code</h2>
                                <p></p>
                                <p>You can also add the following style sheets links to the Head Tag or choose other tabs to see more about Snowflakes CSS.</p>
                                <pre class='code'>
<a style="float:right"><span class="icon success"></span></a>
 &lt;link rel="stylesheet" type="text/css" href="<?php echo $csslink . "SnowflakesMob.css"; ?>" media="only screen and (max-width: 767px)" /&gt;
&lt;link rel="stylesheet" type="text/css" href="<?php echo $csslink . "SnowflakesScrn.css"; ?>" media="only screen and (min-width: 768px)" /&gt;
                                </pre>
                                <p>For gallery within a snowflake add the following</p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
&lt;link rel="stylesheet" type="text/css" href="<?php echo $csslink . "stapel.css"; ?>" /&gt;

                                </pre>
                                <p>and for internet explorer Browsers and mobile browser include the following.</p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
  &lt;!--[if IE]&gt;
 &lt;link rel="stylesheet" type="text/css" href="<?php echo $csslink . "SnowflakesScrn.css"; ?>"/&gt;
 &lt;![endif]--&gt;

 &lt;!--[if IEMobile]&gt; 
    &lt;link rel="stylesheet" type="text/css" href="<?php echo $csslink . "SnowflakesMob.css"; ?>"/&gt;
 &lt;![endif]--&gt;
	
                                </pre>
                                <p></p>
                                <h2>PHP code</h2>
                                <p></p>
                                <p>This is Ideal for Php files, first create a div with id called Snowflakes then add the code below inside the id tag you've just created on the page you want to display your published snowflakes.</p>
                                <p>Copy &amp; paste Id tag in the body tag of your webpage</p>
                                <pre class='code'>
<a style="float:right"><span class="icon success"></span></a>
       &lt;div id=&quot;Snowflakes&quot;&gt;
            &lt;?php  include '<?php echo $Shareurl; ?>'; ?&gt;
        &lt;/div&gt;
                                </pre>
                                <p></p>
                                <h2>Custom Output </h2>
                                <p></p>
                                <p>If you wish to add snowflakes to a custom output or a result page that you have created yourself to view and share a single snowflake. Enter the url address of your result page in the settings page in snowflakes and save it. Then load the page with javascript or php as described above.  But change Out.php to OneView2.php?pageid= so that pageid will be queried automatically by snowflakes.  Thus</p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
Copy &amp; paste Id tag below to the body tag of your webpage or optionally use the Php Code

	&lt;div id=&quot;Snowflakes&quot;&gt; &lt;/div&gt;
                                </pre>
                                <p>Copy &amp; paste the javascript below in Head tag of your webpage</p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
    &lt;script type=&quot;text/javascript&quot;&gt;
    		
            function GetQuery(){
			  // This function is anonymous, is executed immediately and 
			  // the return value is assigned to QueryString!
			  var query_string = {};
			  var query = window.location.search.substring(1);
			  var vars = query.split("&amp;");
			  for (var i=0;i&lt;vars.length;i++) {
				var pair = vars[i].split("=");
					// If first entry with this name
				if (typeof query_string[pair[0]] === "undefined") {
				  query_string[pair[0]] = pair[1];
					// If second entry with this name
				} else if (typeof query_string[pair[0]] === "string") {
				  var arr = [ query_string[pair[0]], pair[1] ];
				  query_string[pair[0]] = arr;
					// If third or later entry with this name
				} else {
				  query_string[pair[0]].push(pair[1]);
				}
			  } 
				return query_string;
			} 
			
            $(document).ready(
			
            function loadSnowflakes() {	
			var QueryString =GetQuery();
                $('#Snowflakes').load('<?php echo str_replace("Out.php", "OneView2.php?pageid=", $Shareurl); ?>'+QueryString.pageid);
            }
		);
    &lt;/script&gt;
                                </pre>

                                <p>Because single custom result view page may contain snowflakes gallery, Add the CSS and javascript code in the Gallery section of this generator to display the gallery in snowflakes theme.</p>


                                <h4>php</h4>
                                <p>Copy &amp; paste Id tag in the body tag of your webpage </p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
           &lt;div id=&quot;Snowflakes&quot;&gt;
                &lt;?php  include '<?php echo str_replace("Out.php", "OneView2.php?pageid=", $Shareurl); ?>filter_input(INPUT_GET, 'pageid')'; ?&gt;
            &lt;/div&gt;
                                </pre>
                                <p> if you are loading snowflakes on a front page that requires minimum number of snowflakes in our case 3 summary snowflakes in the front page Change the Out.php to SummaryOut.php and then query for the maximum number of snowflakes to be any number you want making it "SummaryOut.php?MaxNumber=3" meaning that only 3 summary flakes will be shown. </p>
                                <h4>Javascript</h4>
                                <p>Copy &amp; paste Id tag below to the body tag of your webpage or optionally use the Php Code </p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
	&lt;div id=&quot;Snowflakes&quot;&gt; &lt;/div&gt;
                                </pre>
                                <p>Copy &amp; paste the javascript below in Head tag of your webpage</p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
    &lt;script type=&quot;text/javascript&quot;&gt;
           $(document).ready(
            function loadSnowflakes() {
                $('#Snowflakes').load('<?php echo str_replace("Out.php", "SummaryOut.php?MaxNumber=3", $Shareurl); ?>');
            });
            
    &lt;/script&gt;
                                </pre>
                                <h4>php</h4>
                                <p>Copy &amp; paste Id tag in the body tag of your webpage </p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
           &lt;div id=&quot;Snowflakes&quot;&gt;
                &lt;?php  include '<?php echo str_replace("Out.php", "SummaryOut.php?MaxNumber=3", $Shareurl); ?>'; ?&gt;
            &lt;/div&gt;
                                </pre>
                            </div>
                            <div class="TabbedPanelsContent">
                                <h2>Events Javascript Code</h2>
                                <p></p>
                                <p>Follow the same technique for Snowflakes for this events to work</p>
                                <p></p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
Copy &amp; paste Id tag below to the body tag of your webpage or optionally use the Php Code

	&lt;div id=&quot;SFEvents&quot;&gt; &lt;/div&gt;

Copy &amp; paste the javascript below in Head tag of your webpage

    &lt;script type=&quot;text/javascript&quot;&gt;
    
    		function GetQuery(){
    		// This function is anonymous, is executed immediately and 
			  // the return value is assigned to QueryString!
			  var query_string = {};
			  var query = window.location.search.substring(1);
			  var vars = query.split("&amp;");
			  for (var i=0;i&lt;vars.length;i++) {
				var pair = vars[i].split("=");
					// If first entry with this name
				if (typeof query_string[pair[0]] === "undefined") {
				  query_string[pair[0]] = pair[1];
					// If second entry with this name
				} else if (typeof query_string[pair[0]] === "string") {
				  var arr = [ query_string[pair[0]], pair[1] ];
				  query_string[pair[0]] = arr;
					// If third or later entry with this name
				} else {
				  query_string[pair[0]].push(pair[1]);
				}
			  } 
				return query_string;
			} 
			
    
           $(document).ready(
            function loadSFEvents() {
            var QueryString =GetQuery();
                 var output; 
				 for (property in QueryString) {
  				output += property + ': ' + QueryString[property]+'; ';
				}
                if(output.indexOf("undefined: undefined") !== -1){
                $('#SFEvents').load('<?php echo $ShareEventsurl; ?>');
                }else{
                	$('#SFEvents').load('<?php echo $ShareEventsurl; ?>?pageNum_rsOut='+QueryString.pageNum_rsOut+'&amp;totalRows_rsOut=' + QueryString.totalRows_rsOut);
                }
            });
            
    &lt;/script&gt;
    
    
                                </pre>
                                <p></p>
                                <h2>CSS code</h2>
                                <p></p>
                                <p>You can also add the following style sheets links to the Head Tag or choose other tabs to see more about Snowflakes CSS.</p>
                                <pre class='code'>
<a style="float:right"><span class="icon success"></span></a>


 &lt;link rel="stylesheet" type="text/css" href="<?php echo $csslink . "SnowflakesMob.css"; ?>" media="only screen and (max-width: 767px)" /&gt;
&lt;link rel="stylesheet" type="text/css" href="<?php echo $csslink . "SnowflakesScrn.css"; ?>" media="only screen and (min-width: 768px)" /&gt;
                                </pre>
                                <p>and for internet explorer Browsers and mobile browser include the following. </p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
  &lt;!--[if IE]&gt;
 &lt;link rel="stylesheet" type="text/css" href="<?php echo $csslink . "SnowflakesScrn.css"; ?>"/&gt;
 &lt;![endif]--&gt;

 &lt;!--[if IEMobile]&gt; 
    &lt;link rel="stylesheet" type="text/css" href="<?php echo $csslink . "SnowflakesMob.css"; ?>"/&gt;
 &lt;![endif]--&gt;
                                </pre>
                                <p></p>
                                <h2>PHP code</h2>
                                <p></p>
                                <p>This is Ideal for Php files, first create a div with id called <span class="code">SFEvents</span> then add the code below inside the id tag you've just created on the page you want to display your published Events.</p>
                                <p>Copy &amp; paste Id tag in the body tag of your webpage</p>
                                <pre class='code'>
<a style="float:right"><span class="icon success"></span></a>
	
       &lt;div id=&quot;SFEvents&quot;&gt;
            &lt;?php  include '<?php echo $ShareEventsurl; ?>'; ?&gt;
        &lt;/div&gt;
        
                                </pre>
                                <p></p>
                                <h2>Custom Output </h2>
                                <p></p>
                                <p>If you wish to add Events to a custom output or a result page that you have created yourself to view and share a single Event. Enter the url address of your result page in the settings page and save it. Then load the page with javascript or php as described above.  But change Out.php to OneView2.php?Eventid= so that Eventid will be queried automatically by snowflakes. Thus</p>
                                <p>Copy &amp; paste Id tag below to the body tag of your webpage or optionally use the Php Code </p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
	&lt;div id=&quot;SFEvents&quot;&gt; &lt;/div&gt;
                                </pre>
                                <p>Copy &amp; paste the javascript below in Head tag of your webpage</p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
    &lt;script type=&quot;text/javascript&quot;&gt;
    		
            function GetQuery(){
			  // This function is anonymous, is executed immediately and 
			  // the return value is assigned to QueryString!
			  var query_string = {};
			  var query = window.location.search.substring(1);
			  var vars = query.split("&amp;");
			  for (var i=0;i&lt;vars.length;i++) {
				var pair = vars[i].split("=");
					// If first entry with this name
				if (typeof query_string[pair[0]] === "undefined") {
				  query_string[pair[0]] = pair[1];
					// If second entry with this name
				} else if (typeof query_string[pair[0]] === "string") {
				  var arr = [ query_string[pair[0]], pair[1] ];
				  query_string[pair[0]] = arr;
					// If third or later entry with this name
				} else {
				  query_string[pair[0]].push(pair[1]);
				}
			  } 
				return query_string;
			} 
			
            $(document).ready(
			
            function loadSFEvents() {	
			var QueryString =GetQuery();
                $('#SFEvents').load('<?php echo str_replace("Out.php", "OneView2.php?Eventid=", $ShareEventsurl); ?>'+QueryString.Eventid);
            }
		);
    
    &lt;/script&gt;
                                </pre>
                                <h4>php</h4>
                                <p>Copy &amp; paste Id tag in the body tag of your webpage</p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>    
           &lt;div id=&quot;SFEvents&quot;&gt;
                &lt;?php  include '<?php echo str_replace("Out.php", "OneView2.php?Eventid=", $ShareEventsurl); ?>filter_input(INPUT_GET, 'Eventid')'; ?&gt;
            &lt;/div&gt;
                                </pre>
                                <p> if you are loading Events on a front page that requires minimum number of Events in our case 3 summary Events in the front page Change the Out.php to SummaryOut.php and then query for the maximum number of Events to be any number you want making it "SummaryOut.php?MaxNumber=3" meaning that only 3 summary Events will be shown. </p>
                                <h4>Javascript</h4>
                                <p>Copy &amp; paste Id tag below to the body tag of your webpage or optionally use the Php Code</p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
	&lt;div id=&quot;SFEvents&quot;&gt; &lt;/div&gt;
                                </pre>
                                <p>Copy &amp; paste the javascript below in Head tag of your webpage</p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
    &lt;script type=&quot;text/javascript&quot;&gt;
           $(document).ready(
            function loadSFEvents() {
                $('#SFEvents').load('<?php echo str_replace("Out.php", "SummaryOut.php?MaxNumber=3", $ShareEventsurl); ?>');
            });
            
    &lt;/script&gt;
                                </pre>
                                <h4>php</h4>
                                <p>Copy &amp; paste Id tag in the body tag of your webpage</p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>    
           &lt;div id=&quot;SFEvents&quot;&gt;
                &lt;?php  include '<?php echo str_replace("Out.php", "SummaryOut.php?MaxNumber=3", $ShareEventsurl); ?>'; ?&gt;
            &lt;/div&gt;
                                </pre>
                            </div>
                            <div class="TabbedPanelsContent">
                                <h2>Gallery Javascript Code</h2>
                                <p></p>
                                <p>Follow the same technique for Snowflakes for this Gallery to work but with addition of some code to display Snowflake style Gallery.</p>
                                <p>Copy &amp; paste Id tag below to the body tag of your webpage or optionally use the Php Code</p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
	&lt;div id=&quot;SFGallery&quot;&gt; &lt;/div&gt;
                                </pre>
                                <p>Copy &amp; paste the javascript below in Head tag of your webpage</p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
&lt;script type="text/javascript" src=&quot;<?php echo $JSlink . "jquery.stapel.js"; ?>&quot;&gt;&lt;/script&gt;
    &lt;script type=&quot;text/javascript&quot;&gt;
           $(document).ready(
            function loadSFGallery() {
                $('#SFGallery').load('<?php echo $ShareGallerysurl; ?>');
            });
            
            $(function() {

				var $grid = $( '#tp-grid' ),
					$name = $( '#name' ),
					$close = $( '#close' ),
					$loader = $( '&lt;div class="loader"&gt;&lt;span&gt;Loading&nbsp;&nbsp; &lt;/span&gt;&lt;i&gt;&lt;/i&gt;&lt;i&gt;&lt;/i&gt;&lt;i&gt;&lt;/i&gt;&lt;i&gt;&lt;/i&gt;&lt;i&gt;&lt;/i&gt;&lt;i&gt;&lt;/i&gt;&lt;/div&gt;' ).insertBefore( $grid ),
					stapel = $grid.stapel( {
						delay : 50,
						onLoad : function() {
							$loader.remove();
						},
						onBeforeOpen : function( pileName ) {
							$name.html( pileName );
						},
						onAfterOpen : function( pileName ) {
							$close.show();
						}
					} );

				$close.on( 'click', function() {
					$close.hide();
					$name.empty();
					stapel.closePile();
				} );

			} );
            
    &lt;/script&gt;
                                </pre>
                                <p></p>
                                <h2>CSS code</h2>
                                <p></p>
                                <p>You can also add the following style sheets links to the Head Tag or choose other tabs to see more about Snowflakes CSS.</p>
                                <pre class='code'>
<a style="float:right"><span class="icon success"></span></a>
&lt;link rel="stylesheet" type="text/css" href="<?php echo $csslink . "stapel.css"; ?>" /&gt;
                                </pre>
                                <p></p>
                                <h2>PHP code</h2>
                                <p></p>
                                <p>This is Ideal for Php files, first create a div with id called <span class="code">SFGallery</span> then add the code below inside the id tag you've just created on the page you want to display your published Gallery.</p>
                                <p>Copy &amp; paste Id tag in the body tag of your webpage</p>
                                <pre class='code'>
<a style="float:right"><span class="icon success"></span></a>
	     &lt;div id=&quot;SFGallery&quot;&gt;
                &lt;?php  include '<?php echo $ShareGallerysurl; ?>'; ?&gt;
            &lt;/div&gt;
        
                                </pre>
                                <p></p>
                                <h2>Custom Output </h2>
                                <p></p>
                                <p>If you wish to add <span class="code">SFGallery</span> to a custom output or a result page that you have created yourself to view and share a single snowflake Gallery. Enter the url address of your result page in the settings page and save it. Then load the page with javascript or php as described below by changing Out.php to OneView2.php?Galleryid= so that Galleryid will be queried automatically by snowflakes. Thus</p>
                                <p>Copy &amp; paste Id tag below to the body tag of your webpage or optionally use the Php Code</p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
	&lt;div id=&quot;SFGallery&quot;&gt; &lt;/div&gt;
                                </pre>
                                <p>Copy &amp; paste the javascript below in Head tag of your webpage</p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
    &lt;script type=&quot;text/javascript&quot;&gt;
    		
            function GetQuery(){
			  // This function is anonymous, is executed immediately and 
			  // the return value is assigned to QueryString!
			  var query_string = {};
			  var query = window.location.search.substring(1);
			  var vars = query.split("&amp;");
			  for (var i=0;i&lt;vars.length;i++) {
				var pair = vars[i].split("=");
					// If first entry with this name
				if (typeof query_string[pair[0]] === "undefined") {
				  query_string[pair[0]] = pair[1];
					// If second entry with this name
				} else if (typeof query_string[pair[0]] === "string") {
				  var arr = [ query_string[pair[0]], pair[1] ];
				  query_string[pair[0]] = arr;
					// If third or later entry with this name
				} else {
				  query_string[pair[0]].push(pair[1]);
				}
			  } 
				return query_string;
			} 
			
            $(document).ready(
			
            function loadSFGallery() {	
					var QueryString =GetQuery();
                	$('#SFGallery').load('<?php echo str_replace("Out.php", "OneView2.php?Galleryid=", $ShareGallerysurl); ?>'+QueryString.Galleryid);
            }
		);
    
    &lt;/script&gt;
                                </pre>
                                <h4>php</h4>
                                <p>Copy &amp; paste Id tag in the body tag of your web page</p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>    
       &lt;div id=&quot;SFGallery&quot;&gt;
            &lt;?php  include '<?php echo str_replace("Out.php", "OneView2.php?Galleryid=", $ShareGallerysurl); ?>filter_input(INPUT_GET, 'Galleryid')'; ?&gt;
        &lt;/div&gt;
    
                                </pre>
                                <p> if you are loading <span class="code">SFGallery</span> on a front page that requires minimum number of <span class="code">SFGallery</span> in our case 3 summary <span class="code">SFGallery</span> in the front page Change the Out.php to SummaryOut.php and then query for the maximum number of <span class="code">SFGallery</span> to be any number you want making it "SummaryOut.php?MaxNumber=3" meaning that only 3 summary <span class="code">SFGallery</span> will be shown. </p>
                                <h4>Javascript</h4>
                                <p>Copy &amp; paste Id tag below to the body tag of your webpage or optionally use the Php Code</p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
	&lt;div id=&quot;SFGallery&quot;&gt; &lt;/div&gt;
                                </pre>
                                <p>Copy &amp; paste the javascript below in Head tag of your webpage </p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
    &lt;script type=&quot;text/javascript&quot;&gt;
           $(document).ready(
            function loadSFGallery() {
                $('#SFGallery').load('<?php echo str_replace("Out.php", "SummaryOut.php?MaxNumber=3", $ShareGallerysurl); ?>');
            });
            
    &lt;/script&gt;
                                </pre>
                                <h4>php</h4>
                                <p>Copy &amp; paste Id tag in the body tag of your webpage</p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>    
           &lt;div id=&quot;SFGallery&quot;&gt;
                &lt;?php  include '<?php echo str_replace("Out.php", "SummaryOut.php?MaxNumber=3", $ShareGallerysurl); ?>'; ?&gt;
            &lt;/div&gt; 
                                </pre>
                            </div>
                            <div class="TabbedPanelsContent">
                                <p>You can use Snowflakes default CSS for styling your flakes. You can download</p>
                                <form>
                                    <input class="NewButton" type="button" value="Download CSS" onClick="window.location.href = 'resources/css/SnowflakesScrn.css'">
                                </form>

                                <br />
                                <br />
                                <br />
                                <p> Displays Larger than 999px: </p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
      
Copy &amp; paste the CSS below Stylesheet of your webpage


/* CSS Document */
/*
 * Snowflakes 1.0 - CMS & Web Publishing
 * http://cyrilinc.co.uk/snowflakes/
 * Copyright (c) 2013 Cyril inc
 * Licensed under MIT and GPL
 * Date: Tues, Jan 15 2013 11:33:31 -1100
 */
 
 .Snowflake{
	width:100%;
	min-height:200px;
	border: 1px dashed #AAA;
	-moz-border-radius: 8px;
	-webkit-border-radius: 8px;
	-khtml-border-radius: 8px;
	border-radius: 8px;
	background:#2b2b2b;
	/* IE 8 */
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=90)";
	/* IE 5-7 */
  filter: alpha(opacity=90);
	/* Netscape */
  -moz-opacity: 0.9;
	-webkit-opacity:0.9;
	/* Safari 1.x */
  -khtml-opacity: 0.9;
	/* Good browsers */
  opacity: 0.9;
	float:left;
	position: relative;
	padding-top: 8px;
	padding-bottom:10px;
	margin:5px 5px;
}


.SnowflakeHead{

	text-align:center;
	color: #40add8;
	font-size:22px;
	margin-top: 5px;
	margin-bottom:5px;
}


.SnowflakePanel{
	color:#fafafa;
	padding-left:5px;
}

.SnowflakePanel a {
	height:20px;
	width:20px;
	/* IE 8 */
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=90)";
	/* IE 5-7 */
  filter: alpha(opacity=90);
	/* Netscape */
  -moz-opacity: 0.9;
	-webkit-opacity:0.9;
	/* Safari 1.x */
  -khtml-opacity: 0.9;
	/* Good browsers */
  opacity: 0.9;
	background:#2b2b2b;
	text-align:center;
}

.SnowflakePanel a:hover{
	background-color:#40add8;
	/* IE 8 */
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=100)";
	/* IE 5-7 */
  filter: alpha(opacity=100);
	/* Netscape */
  -moz-opacity: 1;
	-webkit-opacity:1;
	/* Safari 1.x */
  -khtml-opacity: 1;
	/* Good browsers */
  opacity: 1;
	cursor:pointer;
}

.SnowflakePanel a img{
	margin:0 5px;
}



.PageBreak{
	margin-top: 5px;	
	margin-bottom:5px;
	height:8px;
	background-image:url(../images/break.png);
	background-position: center top;
	background-repeat: no-repeat;
}


.SnowflakeDescr {
	min-height:210px;
	text-align:justify;
	font-size:15px;
	padding-top: 10px;
	padding-left: 10px;
	padding-right: 10px;
	padding-bottom:40px;
	color:#fafafa;
	-moz-border-radius: 10px;
	-webkit-border-radius: 10px;
	-khtml-border-radius: 10px;
	border-radius: 10px;
}

.SnowflakeDescrSmall{
	min-height: 60px;
	text-align:justify;
	font-size:15px;
	padding-top: 10px;
	padding-left: 10px;
	padding-right: 10px;
	padding-bottom:40px;
	color:#fafafa;
	-moz-border-radius: 10px;
	-webkit-border-radius: 10px;
	-khtml-border-radius: 10px;
	border-radius: 10px;
	overflow:hidden;
}

.SnowflakeImage, .SnowflakeImageSmall {
	background-color:#fafafa;
	border:1px solid #CCC;
	margin:5px auto;
	height:auto;
	max-width:214px;
	width: auto\9; /* ie8 */
	float:left;
	position: relative;
	margin: 2px 4px;
}

.SnowflakeImage img {
	max-width:200px;
	height:auto;
	width: auto\9; /* ie8 */
	padding:7px;
	-moz-border-radius: 10px;
	-webkit-border-radius: 10px;
	-khtml-border-radius: 10px;
	border-radius: 10px;
	
	-webkit-transition: all 0.3s ease-in-out;
	-moz-transition: all 0.3s ease-in-out;
	-o-transition: all 0.3s ease-in-out;
	-ms-transition: all 0.3s ease-in-out;
	transition: all 0.3s ease-in-out;
}
 
.SnowflakeImageSmall img {
	max-width:100px;
	height:auto;
	width: auto\9; /* ie8 */
	padding:7px;
	-moz-border-radius: 10px;
	-webkit-border-radius: 10px;
	-khtml-border-radius: 10px;
	border-radius: 10px;
	-webkit-transition: all 0.7s ease-in-out;
	-moz-transition: all 0.7s ease-in-out;
	-o-transition: all 0.7s ease-in-out;
	-ms-transition: all 0.7s ease-in-out;
	transition: all 0.7s ease-in-out;
}


.SnowflakeDate{
	margin: 5px;
	float:left;
	color:#40add8;
	font-size:13px;
}



.smallNewButton{
	height:30px;
	width: 80px;
	-moz-border-radius: 4px;
	-webkit-border-radius: 4px;
	-khtml-border-radius: 4px;
	border-radius: 4px;
		font-size:14px;
		/* IE 8 */
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=90)";
	/* IE 5-7 */
  filter: alpha(opacity=90);
	/* Netscape */
  -moz-opacity: 0.9;
	-webkit-opacity:0.9;
	/* Safari 1.x */
  -khtml-opacity: 0.9;
	/* Good browsers */
  opacity: 0.9;
	background:#2b2b2b;
	text-align:center;
	line-height:30px;
	padding-left: 1px;
	color:#fafafa;
	float:left;
	margin: 3px;
}

.smallNewButton a{
	color:#fafafa;
	font-family: Strait, sans-serif;
}

.smallNewButton img{
	height:20px;
	width:15px;
	padding-top: 7px;
	float:right;
}

.smallNewButton:hover {
	background-color:#40add8;
	/* IE 8 */
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=100)";
	/* IE 5-7 */
  filter: alpha(opacity=100);
	/* Netscape */
  -moz-opacity: 1;
	-webkit-opacity:1;
	/* Safari 1.x */
  -khtml-opacity: 1;
	/* Good browsers */
  opacity: 1;
	cursor:pointer;
}


/*###################################*/
/*## Events Structure Rules Begins ##*/
/*###################################*/
.eventWrapper{
	border: 1px dashed #AAA;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	-o-border-radius: 3px;
	border-radius: 3px;
	padding:5px;
	min-height:80px;
	width:48%;
	color:#fafafa;
	background:#2b2b2b;
	/* IE 8 */
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=90)";
	/* IE 5-7 */
  filter: alpha(opacity=90);
	/* Netscape */
  -moz-opacity: 0.9;
	-webkit-opacity:0.9;
	/* Safari 1.x */
  -khtml-opacity: 0.9;
	/* Good browsers */
  opacity: 0.9;
}

.eventWrapper2{
	border: 1px dashed #AAA;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	-o-border-radius: 3px;
	border-radius: 3px;
	padding:5px;
	min-height:80px;
	width:100%;
	color:#fafafa;
	background:#2b2b2b;
	/* IE 8 */
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=90)";
	/* IE 5-7 */
  filter: alpha(opacity=90);
	/* Netscape */
  -moz-opacity: 0.9;
	-webkit-opacity:0.9;
	/* Safari 1.x */
  -khtml-opacity: 0.9;
	/* Good browsers */
  opacity: 0.9;
}

.SFEvent {
	border-bottom: #bdbbbc 1px solid;
	padding: 0 0 18px 0;
	margin: 0 0 20px 0;
}
.SFEvent:last-child {
	border-bottom: none;
	}
	
	.SFEvent:nth-last-child(1) {
	margin: 0 0 40px 0;
}

.prev-image {
	margin: 0 0 9px 0;
}

.SFEvent-date {
	float: left;
	width: 52px;
	text-align: center;
	-webkit-border-radius: 5px;
	-moz-border-radius: 5px;
	-o-border-radius: 5px;
	border-radius: 5px;
	background: #fafafa;
}
.SFEvent .SFEvent-date .month {
	color:#2b2b2b;
	text-transform: uppercase;
	font:20px/26px;
	line-height: 1;
	margin: 0;
	padding:4px;
	background: url(../images/date-bg2.png) repeat-x;
	text-align:center
}
.SFEvent .SFEvent-date .day {
	font-size: 24px;
	line-height: 1;
	background: url(../images/date-bg.png) repeat-x;
	border: #bdbbbc 1px solid;
	margin: 0;
	padding: 5px 0 0 0;
	height: 28px;
	text-align:center;
	color:#2b2b2b;
}

.SFEvent .SFEvent-date .year {
	font-size: 14px;
	line-height: 1;
	background: url(../images/date-bg3.png) repeat-x;
	border: #bdbbbc 1px solid;
	margin: 0;
	padding: 5px 0 0 0;
	height: 18px;
	text-align:center;
	color:#2b2b2b;
}

.SFEvent-content {
	 float: left;
	 padding-left: 5px;
}
.SFEvent-content h4 {
	line-height: 1;
	padding:2px;
	color:#40add8;
	font:20px/26px;
}
.SFEvent-content h4 a {
	text-decoration: none;
}
.SFEvent-content p {
	line-height: 1.3;
	margin: 0;
}
.SFEvent-content p strong {
	font-weight: bold;
}

                                </pre>
                                <p> </p>
                            </div>
                            <div class="TabbedPanelsContent">You can use Snowflakes default CSS for styling your flakes. You can download
                                <form>
                                    <input class="NewButton" type="button" value="Download CSS" onClick="window.location.href = 'resources/css/SnowflakesMob.css'">
                                </form>
                                <p></p>
                                <br />
                                <br />
                                <br />
                                <p> Displays Smaller than 999px: </p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
Copy & paste the CSS below Stylesheet of your webpage


/* CSS Document */
/*
 * Snowflakes 1.0 - CMS & Web Publishing
 * http://cyrilinc.co.uk/snowflakes/
 * Copyright (c) 2013 Cyril inc
 * Licensed under MIT and GPL
 * Date: Tues, Jan 15 2013 11:33:31 -1100
 */
 
 
 .Snowflake {
	width:100%;
	min-height:200px;
	border: 1px dashed #AAA;
	-webkit-border-radius:8px;
	-moz-border-radius:8px;
	border-radius:8px;
	background:#2b2b2b;
	float:left;
	position: relative;
	padding-top: 8px;
	padding-bottom:10px;
	margin:5px 5px;
}
.SnowflakeHead {
	text-align:center;
	color: #40add8;
	font-size:22px;
	margin-top: 5px;
	margin-bottom:5px;
}
.SnowflakePanel {
	color:#fafafa;
	padding-left:5px;
}
.SnowflakePanel a {
	height:20px;
	width:20px;
	/* IE 8 */
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=90)";
	/* IE 5-7 */
  filter: alpha(opacity=90);
	/* Netscape */
  -moz-opacity: 0.9;
	-webkit-opacity:0.9;
	/* Safari 1.x */
  -khtml-opacity: 0.9;
	/* Good browsers */
  opacity: 0.9;
	background-color:#2b2b2b;
	text-align:center;
}
.SnowflakePanel a:hover {
	background-color:#40add8;
	/* IE 8 */
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=100)";
	/* IE 5-7 */
  filter: alpha(opacity=100);
	/* Netscape */
  -moz-opacity: 1;
	-webkit-opacity:1;
	/* Safari 1.x */
  -khtml-opacity: 1;
	/* Good browsers */
  opacity: 1;
	cursor:pointer;
}
.SnowflakePanel a img {
	margin:0 5px;
}
.PageBreak {
	margin-top: 5px;
	margin-bottom:5px;
	height:8px;
	background-image:url(../images/break.png);
	background-position: center top;
	background-repeat: no-repeat;
}


.SnowflakeDescr {
	min-height: 200px;
	text-align:justify;
	font-size:15px;
	padding-top: 10px;
	padding-left: 10px;
	padding-right: 10px;
	padding-bottom:40px;
	color:#fafafa;
	-moz-border-radius: 10px;
	-webkit-border-radius: 10px;
	-khtml-border-radius: 10px;
	border-radius: 10px;
}
.SnowflakeDescrSmall{
	min-height: 60px;
	text-align:justify;
	font-size:15px;
	padding-top: 10px;
	padding-left: 10px;
	padding-right: 10px;
	padding-bottom:40px;
	color:#fafafa;
	-moz-border-radius: 10px;
	-webkit-border-radius: 10px;
	-khtml-border-radius: 10px;
	border-radius: 10px;
	overflow:hidden;
}

.SnowflakeImage, .SnowflakeImageSmall {
	background-color:#fafafa;
	border:1px solid #CCC;
	margin:5px auto;
	height:auto;
	max-width:214px;
	width: auto\9; /* ie8 */
	float:left;
	position: relative;
	margin: 2px 4px;
}
.SnowflakeImage img {
	max-width:200px;
	height:auto;
	width: auto\9; /* ie8 */
	padding:7px;
	-moz-border-radius: 10px;
	-webkit-border-radius: 10px;
	-khtml-border-radius: 10px;
	border-radius: 10px;
	-webkit-transition: all 0.7s ease-in-out;
	-moz-transition: all 0.7s ease-in-out;
	-o-transition: all 0.7s ease-in-out;
	-ms-transition: all 0.7s ease-in-out;
	transition: all 0.7s ease-in-out;
}

.SnowflakeImageSmall img {
	max-width:100px;
	height:auto;
	width: auto\9; /* ie8 */
	padding:7px;
	-moz-border-radius: 10px;
	-webkit-border-radius: 10px;
	-khtml-border-radius: 10px;
	border-radius: 10px;
	-webkit-transition: all 0.7s ease-in-out;
	-moz-transition: all 0.7s ease-in-out;
	-o-transition: all 0.7s ease-in-out;
	-ms-transition: all 0.7s ease-in-out;
	transition: all 0.7s ease-in-out;
}
.SnowflakeDate {
	margin: 5px;
	float:left;
	color:#40add8;
	font-size:12px;
}


.smallNewButton {
	height:30px;
	width: 80px;
	-webkit-border-radius:4px;
	-moz-border-radius:4px;
	border-radius:4px;
	font-size:14px;
	/* IE 8 */
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=80)";
	/* IE 5-7 */
  filter: alpha(opacity=80);
	/* Netscape */
  -moz-opacity: 0.8;
	-webkit-opacity:0.8;
	/* Safari 1.x */
  -khtml-opacity: 0.8;
	/* Good browsers */
  opacity: 0.8;
	background-color:#2b2b2b;
	text-align:center;
	line-height:30px;
	padding-left: 1px;
	color:#fafafa;
	float:left;
	margin: 3px;
}
.smallNewButton a {
	color:#fafafa;
	font-family: Strait, sans-serif;
}
.smallNewButton img {
	height:20px;
	width:15px;
	padding-top: 7px;
	float:right;
}
.smallNewButton:hover {
	background-color:#40add8;
	/* IE 8 */
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=100)";
	/* IE 5-7 */
  filter: alpha(opacity=100);
	/* Netscape */
  -moz-opacity: 1;
	-webkit-opacity:1;
	/* Safari 1.x */
  -khtml-opacity: 1;
	/* Good browsers */
  opacity: 1;
	cursor:pointer;
}

/*###################################*/
/*## Events Structure Rules Begins ##*/
/*###################################*/
.eventWrapper, .eventWrapper2 {
	border: 1px dashed #AAA;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	-o-border-radius: 3px;
	border-radius: 3px;
	padding:5px;
	width:100%;
	color:#fafafa;
	background:#2b2b2b;
	/* IE 8 */
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=90)";
	/* IE 5-7 */
  filter: alpha(opacity=90);
	/* Netscape */
  -moz-opacity: 0.9;
	-webkit-opacity:0.9;
	/* Safari 1.x */
  -khtml-opacity: 0.9;
	/* Good browsers */
  opacity: 0.9;
}
.SFEvent {
	border-bottom: #bdbbbc 1px solid;
	padding: 0 0 18px 0;
	margin: 0 0 20px 0;
}
.SFEvent:last-child {
	border-bottom: none;
}
.SFEvent:nth-last-child(1) {
	margin: 0 0 40px 0;
}
.prev-image {
	margin: 0 0 9px 0;
}
.SFEvent-date {
	float: left;
	width: 50px;
	text-align: center;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	-o-border-radius: 3px;
	border-radius: 3px;
	background: #fafafa;
}
.SFEvent .SFEvent-date .month {
	color: #2b2b2b;
	text-transform: uppercase;
	font:20px/26px;
	line-height: 1;
	margin: 0;
	padding:4px;
	background: url(../images/date-bg2.png) repeat-x;
	text-align:center;
	
	
}
.SFEvent .SFEvent-date .day {
	font-size: 24px;
	line-height: 1;
	background: url(../images/date-bg.png) repeat-x;
	border: #bdbbbc 1px solid;
	margin: 0;
	padding: 5px 0 0 0;
	height: 28px;
	text-align:center;
	color:#2b2b2b;
}

.SFEvent .SFEvent-date .year {
	font-size: 14px;
	line-height: 1;
	background: url(../images/date-bg3.png) repeat-x;
	border: #bdbbbc 1px solid;
	margin: 0;
	padding: 5px 0 0 0;
	height: 18px;
	text-align:center;
	color:#2b2b2b;
}
.SFEvent-content {
	float: left;
	padding-left: 5px;
}
.SFEvent-content h4 {
	line-height: 1;
	margin: 0 0 6px 0;
	color:#40add8;
	font:20px/26px;
}
.SFEvent-content h4 a {
	text-decoration: none;
}
.SFEvent-content p {
	line-height: 1.3;
	margin: 0;
}
.SFEvent-content p strong {
	font-weight: bold;
}


                                </pre>
                            </div>
                            <div class="TabbedPanelsContent">
                                <p>You can use Snowflakes Gallery default CSS for styling your flakes. You can download</p>
                                <form>
                                    <input class="NewButton" type="button" value="Download CSS" onClick="window.location.href = 'resources/css/stapel.css'">
                                </form>

                                <br />
                                <br />
                                <br />
                                <p>For all displays on mobile, tablets and large displays</p>
                                <pre class='code'> 
<a style="float:right"><span class="icon success"></span></a>
      
Copy &amp; paste the CSS below Stylesheet of your webpage


/* Custom elements style */
.wrapper {
	position: relative;
	margin: 20px auto;
}

.topbar {
	position: relative;
	padding: 20px;
	margin: 0 auto;
	/*box-shadow: 0 1px 0 #aaa, 0 -1px 0 #aaa;*/
}

.back {
	width: 40px;
	height: 40px;
	position: absolute;
	left: 50%;
	top: 50%;
	margin: -20px 0 0 -20px;
	border-radius: 50%;
	text-align: center;
	line-height: 38px;
	color: #fafafa;
	background: #2b2b2b;
	background: rgba(43,43,43,0.5);
	cursor: pointer;
	display: none;
	-webkit-touch-callout: none;
	-webkit-user-select: none;
	-khtml-user-select: none;
	-moz-user-select: none;
	-ms-user-select: none;
	user-select: none;
}

.no-touch .back:hover {
	background: #fff;
	background: rgba(43,43,43,0.9);
}

.topbar h2,
.topbar h3 {
	display: inline-block;
}

.topbar h2 {
	color: #666;
}

.topbar h3 {
	padding-left: 20px;
	text-align:center;
}

/* Loader */

.loader {
	left: 50%;
	position: absolute;
	margin-left: -120px;
}

.loader i {
	display: inline-block;
	width: 20px;
	height: 20px;
	border-radius:10px;
	-webkit-animation: loading 1s linear infinite forwards;
	-moz-animation: loading 1s linear infinite forwards;
	-o-animation: loading 1s linear infinite forwards;
	-ms-animation: loading 1s linear infinite forwards;
	animation: loading 1s linear infinite forwards;
}

.cssanimations .loader span {
	display: none;
}

.no-cssanimations .loader i {
	display: none;
}

.loader i:nth-child(2){
	-webkit-animation-delay: 0.1s;
	-moz-animation-delay: 0.1s;
	-o-animation-delay: 0.1s;
	-ms-animation-delay: 0.1s;
	animation-delay: 0.1s;
}

.loader i:nth-child(3){
	-webkit-animation-delay: 0.2s;
	-moz-animation-delay: 0.2s;
	-o-animation-delay: 0.2s;
	-ms-animation-delay: 0.2s;
	animation-delay: 0.2s;
}

.loader i:nth-child(4){
	-webkit-animation-delay: 0.3s;
	-moz-animation-delay: 0.3s;
	-o-animation-delay: 0.3s;
	-ms-animation-delay: 0.3s;
	animation-delay: 0.3s;
}

.loader i:nth-child(5){
	-webkit-animation-delay: 0.4s;
	-moz-animation-delay: 0.4s;
	-o-animation-delay: 0.4s;
	-ms-animation-delay: 0.4s;
	animation-delay: 0.4s;
}

.loader i:nth-child(6){
	-webkit-animation-delay: 0.5s;
	-moz-animation-delay: 0.5s;
	-o-animation-delay: 0.5s;
	-ms-animation-delay: 0.5s;
	animation-delay: 0.5s;
}

@-webkit-keyframes loading{
	0%{
		opacity: 0;
		background-color: rgba(255,255,255,0.9);
	}

	100%{
		opacity: 1;
		-webkit-transform: scale(0.25) rotate(75deg);
		background-color: rgba(155,155,155,0.9);
	}
}

@-moz-keyframes loading{
	0%{
		opacity: 0;
		background-color: rgba(255,255,255,0.9);
	}

	100%{
		opacity: 1;
		-moz-transform: scale(0.25) rotate(75deg);
		background-color: rgba(155,155,155,0.9);
	}
}

@-o-keyframes loading{
	0%{
		opacity: 0;
		background-color: rgba(255,255,255,0.9);
	}

	100%{
		opacity: 1;
		-o-transform: scale(0.25) rotate(75deg);
		background-color: rgba(155,155,155,0.9);
	}
}

@-ms-keyframes loading{
	0%{
		opacity: 0;
		background-color: rgba(255,255,255,0.9);
	}

	100%{
		opacity: 1;
		-ms-transform: scale(0.25) rotate(75deg);
		background-color: rgba(155,155,155,0.9);
	}
}

@keyframes loading{
	0%{
		opacity: 0;
		background-color: rgba(255,255,255,0.9);
	}

	100%{
		opacity: 1;
		transform: scale(0.25) rotate(75deg);
		background-color: rgba(155,155,155,0.9);
	}
}

@media screen and (max-width: 680px){
	.topbar h2, .topbar h3 { text-align: left; padding: 0; display: block;}
	.back { left: auto; right: 0px; margin-left: 0px;}
}
/*****Portfolio Grid*********/
.tp-grid {
	list-style-type: none;
	position: relative;
	display: block;
}

.tp-grid li {
	position: absolute;
	cursor: pointer;
	border: 10px solid #fff;
	box-shadow: 0 2px 3px rgba(0,0,0,0.2);
	display: none;
	overflow: hidden;
	-webkit-backface-visibility: hidden;
	-moz-backface-visibility: hidden;
	-o-backface-visibility: hidden;
	-ms-backface-visibility: hidden;
	backface-visibility: hidden;
}

.no-js .tp-grid li {
	position: relative;
	display: inline-block;
}

.tp-grid li a {
	display: block;
	outline: none;
}

.tp-grid li img {
	display: block;
	border: none;
}

.tp-info,
.tp-title {
	position: absolute;
	background: #fff;
	line-height: 20px;
	color: #333;
	top: 40%;
	width: 75%;
	padding: 10px;
	font-weight: 700;
	text-align: right;
	left: -100%;
	box-shadow: 
		1px 1px 1px rgba(0,0,0,0.1),
		5px 0 5px -3px rgba(0,0,0,0.4),
		inset 0 0 5px rgba(0,0,0,0.04);
}

.touch .tp-info {
	left: 0px;
}

.no-touch .tp-info {
	-webkit-transition: all 0.3s ease-in-out;
	-moz-transition: all 0.3s ease-in-out;
	-o-transition: all 0.3s ease-in-out;
	-ms-transition: all 0.3s ease-in-out;
	transition: all 0.3s ease-in-out;
}

.no-touch .tp-grid li:hover .tp-info {
	-webkit-transition-delay: 150ms;
	-moz-transition-delay: 150ms;
	-o-transition-delay: 150ms;
	-ms-transition-delay: 150ms;
	transition-delay: 150ms;
}

.no-touch .tp-open li:hover .tp-info {
	left: 0px;
}

.tp-title {
	padding: 10px 35px 10px 10px;
	left: 0px;
}

.tp-title span:nth-child(2){
	color: #aaa;
	padding: 0 5px;
	background: #F7F7F7;
	right: 0px;
	height: 100%;
	line-height: 40px;
	top: 0px;
	position: absolute;
	display: block;
}

                                </pre>
                                <p> </p>
                            </div>
                        </div>
                    </div>

                </div><!--End of PageWrap--> 

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
        <script type="text/javascript">
            var TabbedPanels1 = new Spry.Widget.TabbedPanels("GeneratorPanel");
        </script> 
        <!-- InstanceEndEditable -->
    </body>
    <!-- InstanceEnd --></html>
<?php
$SFconnects->close();
?>
