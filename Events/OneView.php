<?php
require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';
?>
<?php
$siteSettings = new settingsStruct('../config/config.ini');
//The upload Image directory
$sfGalleryImgUrl = $siteSettings->m_sfGalleryImgUrl;
$imageMissing = $sfGalleryImgUrl . "missing_default.png";

$config = new databaseParam('../config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$SFconnects->connect(); // Connect to database

$colname_EventsRs = -1;
$Eventid = filter_input(INPUT_GET, 'Eventid');
if (isset($Eventid)) {
    $colname_EventsRs = $Eventid;
}
$eventStruct = new eventStruct();
$eventStruct->getEventByid($SFconnects, $colname_EventsRs);
$totalRows_EventsRs = $SFconnects->recordCount();

$url = $otherurl = sfUtils::curPageURL();
$Powerlink = "../resources/images/Snowflakes2.png";
?>
<!DOCTYPE HTML>
<html ><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Cyril Inc">
        <meta name="viewport" content="width=device-width, maximum-scale = 1, minimum-scale=1" />
        <!-- InstanceBeginEditable name="doctitle" -->
        <title><?php echo $eventStruct->m_title; ?></title>
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
                $(".HeaderWrapper").hide();
                $("#SnowFooter").hide();

            });
        </script>
        <script type="text/javascript">
            var flakeitUrl = "<?php echo $siteSettings->m_flakeItUrl; ?>";
        </script>
        <script type="text/javascript" src="<?php echo $siteSettings->m_sfUrl; ?>resources/Js/flakeit.js"></script>
        <script src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>
        <script type="text/javascript">

            function initialize() {
<?php $latlong = explode(",", $eventStruct->m_lat_long); ?>
                var lat =<?php echo $latlong[0]; ?>;
                var long =<?php echo $latlong[1]; ?>;
                var map_canvas = document.getElementById('map-canvas2');
                var latlngPos = new google.maps.LatLng(lat, long);

                var map_options = {
                    center: latlngPos,
                    zoom: 17,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                }
                var map = new google.maps.Map(map_canvas, map_options)
                // Add the marker
                var marker = new google.maps.Marker({
                    position: latlngPos,
                    map: map,
                    title: "Venue"
                });
            }
            google.maps.event.addDomListener(window, 'load', initialize);

            $(document).ready(function() {
                $(".HeaderWrapper").hide();
                $("#SnowFooter").hide();

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
                        <div class="Logo"><img alt="Snowflakes" class="logo" src="../resources/images/Snowflakes.png" width="165" height="55" /></div>
                        <div class="SideMenu"> <a class="opener" id="touch-menu" href="#"><i class="icon-reorder"></i>Menu</a>
                            <ul id="primary_nav" class="primary_nav">
                                <!-- InstanceBeginEditable name="menuEdit" -->

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

                    <div style="float: right; background-color:#2b2b2b;"><a href="http://cyrilinc.co.uk/snowflakes/" target="_blank"><img src="<?php echo $Powerlink; ?>" width="120" height="40" alt="Powered by Snowflakes" /></a> </div>

                    <?php if ($Eventid != Null) { ?>
                        <!--eventWrapper-->
                        <div class="eventWrapper2 fl"> 

                            <!--SnowflakePanel-->
                            <div class="SnowflakePanel"> 
                                <a href="http://twitter.com/home?status=<?php echo htmlentities(rawurlencode($eventStruct->m_title)); ?>%20<? echo "" . $url; ?>" title="Twitter" target="_blank"> <img src="../resources/images/Icons/Twitter.png" height="30" width="30" alt="Twitter" /> </a> 
                                <a href="http://www.facebook.com/sharer.php?u=<? echo "" . $url; ?>" title="Facebook" target="_blank"> <img src="../resources/images/Icons/Facebook.png" height="30" width="30" alt="Facebook" /> </a> 
                                <a href="https://plus.google.com/share?url=<? echo "" . $url; ?>&amp;title=<?php echo htmlentities(rawurlencode($eventStruct->m_title)); ?>" title="GooglePlus" target="_blank"> <img src="../resources/images/Icons/GooglePlus.png" height="30" width="30" alt="GooglePlus" /> </a> 
                                <a href="http://digg.com/submit?phase=2&amp;url=<? echo "" . $url; ?>&amp;title=<?php echo htmlentities(rawurlencode($eventStruct->m_title)); ?>" title="Digg" target="_blank"> <img src="../resources/images/Icons/Digg.png" height="30" width="30" alt="Digg" /> </a> 
                                <a href="http://stumbleupon.com/submit?url=<? echo "" . $url; ?>&amp;title=<?php echo htmlentities(rawurlencode($eventStruct->m_title)); ?>" title="stumbleupon" target="_blank"> <img src="../resources/images/Icons/stumbleupon.png" height="30" width="30" alt="stumbleupon" /> </a> 
                                <a href="http://del.icio.us/post?url=<? echo "" . $url; ?>&amp;title=<?php echo htmlentities(rawurlencode($eventStruct->m_title)); ?>" title="delicious" target="_blank"> <img src="../resources/images/Icons/delicious.png" height="30" width="30" alt="delicious" /> </a> 
                                <a class="flakeit" id="flakeit<?php echo $eventStruct->m_id; ?>" title="flake it" data-type="event"> <span>Flake it</span> <img src="../resources/images/Icons/Snowflakes.png" height="22" width="22" alt="flake it" /> </a> 
                            </div>
                            <!--End of SnowflakePanel-->

                            <div class="Break2"></div>

                            <!--SFEvent-->
                            <div class="SFEvent clearfix">
                                <?php
                                if ($totalRows_EventsRs > 0) {
                                    $i = 0;
                                    $eventdate = new DateTime($eventStruct->m_event_date);
                                    $enddate = new DateTime($eventStruct->m_end_date);
                                    ?>
                                    <div class="SFEvent-date">
                                        <ul class="startDate">
                                            <li class="month"> <?php echo $eventdate->format(" M"); ?></li>
                                            <li class="day"><?php echo $eventdate->format("d"); ?></li>
                                            <li class="year"><?php echo $eventdate->format(" Y"); ?></li>
                                            <li class="time"><?php echo sfUtils::toAmPmTime($eventStruct->m_event_time); ?></li>
                                        </ul>
                                        <ul class="eventTitle">
                                            <li><a href="#" rel="bookmark" title="<?php echo $eventStruct->m_title; ?>"><?php echo $eventStruct->m_title; ?></a></li>
                                            <li><a href="#" rel="bookmark" title="location"><?php echo $eventStruct->m_location; ?></a></li>
                                        </ul>
                                        <ul class="endDate">
                                            <li class="month"> <?php echo $enddate->format(" M"); ?></li>
                                            <li class="day"><?php echo $enddate->format("d"); ?></li>
                                            <li class="year"><?php echo $enddate->format(" Y"); ?></li>
                                            <li class="time"><?php echo sfUtils::toAmPmTime($eventStruct->m_end_time); ?></li>
                                        </ul>
                                    </div>
                                    <div class="SFEvent-content"></div>
                                    <div class="clear PageBreak"></div>
                                    <div class="SnowflakeDescr">
                                        <div class="SnowflakeImage">
                                            <a class="colorbox" href="../Uploads/<?php echo $eventStruct->m_image_name; ?>"  onerror="this.href='<?php echo $imageMissing; ?>'"  title="<?php echo $eventStruct->m_title; ?>" >
                                                <img src="../Uploads/<?php echo $eventStruct->m_image_name; ?>"  onerror="this.src='<?php echo $imageMissing; ?>'"  alt="Image" />
                                            </a> 
                                        </div>
                                        <?php echo html_entity_decode($eventStruct->m_body_text); ?>
                                    </div>
                                    <div class="clear"></div>
                                    <div id="map-canvas2"></div>
                                    <div class="clear PageBreak"></div>
                                    <div class="SnowflakeDate"> Date Created |: <?php echo date(" F j, Y", $eventStruct->m_created); ?>  | By - <?php echo $eventStruct->m_created_by; ?> </div>
                                    <div class="SnowflakeIt">
                                        <img src="../resources/images/Icons/Snowflakes.png" height="22" width="22" alt="flake it" /> 
                                        <span class="flakeitParam" id="flakecount<?php echo $eventStruct->m_id; ?>"> <?php echo $eventStruct->m_flake_it; ?> </span>
                                    </div>

                                <?php } else { ?> 

                                    <h1 style="color:#fafafa;">This snowflake event doesn't exist </h1>

                                <?php } ?> 
                            </div>
                            <!--SFEvent Ends--> 
                        </div>
                        <!--eventWrapper Ends--> 
                    <?php } else {
                        ?>

                        <h1>No Event to view </h1>

                    <?php }
                    ?>

                </div>
                <!-- End of PageWrap --> 
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
