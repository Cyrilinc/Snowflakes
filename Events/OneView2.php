<?php
require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';
require_once '../lib/sfSettings.php';
?>
<?php
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

$siteSettings = new sfSettings('../config/config.ini');

$url = $otherurl = sfUtils::curPageURL();
if (isset($siteSettings->m_eventsResultUrl)) {
    $events_result_url = $siteSettings->m_eventsResultUrl;
    $url = $otherurl = $events_result_url . "&amp;Eventid=" . $Eventid;
} else {
    $SFEventsResultUrl = 'notset';
}
$SnowflakesUrl = $siteSettings->m_sfUrl;
$Powerlink = $SnowflakesUrl . "resources/images/Snowflakes2.png";

$sfGalleryImgUrl = $siteSettings->m_sfGalleryImgUrl;
$imageMissing = $sfGalleryImgUrl . "missing_default.png";
?>
<style>
    #map_canvas {
        min-height: 200px;
    }
</style>
<script type="text/javascript" src="../resources/Js/jquery-1.11.0.js"></script>
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
</script>

<!-- PageWrap -->
<div class="PageWrap">

    <div style="float: right; background-color:transparent;"><a href="http://cyrilinc.co.uk/snowflakes/" target="_blank"><img src="<?php echo $Powerlink; ?>" width="120" height="40" alt="Powered by Snowflakes" /></a> </div>

    <?php if ($Eventid != Null) { ?>
        <!--eventWrapper-->
        <div class="eventWrapper2 fl"> 

            <!--SnowflakePanel-->
            <div class="SnowflakePanel"> 
                <a href="http://twitter.com/home?status=<?php echo $eventStruct->m_title; ?>%20<? echo htmlentities(rawurlencode($url)); ?>" title="Twitter" target="_blank"> <img src="<?php echo $SnowflakesUrl . 'resources/images/Icons/Twitter.png'; ?>" height="22" width="22" alt="Twitter" /> </a> 
                <a href="http://www.facebook.com/sharer.php?u=<? echo htmlentities(rawurlencode($url)); ?>" title="Facebook" target="_blank"> <img src="<?php echo $SnowflakesUrl . 'resources/images/Icons/Facebook.png'; ?>" height="22" width="22" alt="Facebook" /> </a> 
                <a href="https://plus.google.com/share?url=<? echo htmlentities(rawurlencode($url)); ?>&amp;title=<?php echo $eventStruct->m_title; ?>" title="GooglePlus" target="_blank"> <img src="<?php echo $SnowflakesUrl . 'resources/images/Icons/GooglePlus.png'; ?>" height="22" width="22" alt="GooglePlus" /> </a> 
                <a href="http://digg.com/submit?phase=2&url=<? echo htmlentities(rawurlencode($url)); ?>&amp;title=<?php echo $eventStruct->m_title; ?>" title="Digg" target="_blank"> <img src="<?php echo $SnowflakesUrl . 'resources/images/Icons/Digg.png'; ?>" height="22" width="22" alt="Digg" /> </a> 
                <a href="http://stumbleupon.com/submit?url=<? echo htmlentities(rawurlencode($url)); ?>&amp;title=<?php echo $eventStruct->m_title; ?>" title="stumbleupon" target="_blank"> <img src="<?php echo $SnowflakesUrl . 'resources/images/Icons/Stumbleupon.png'; ?>" height="22" width="22" alt="stumbleupon" /> </a> 
                <a href="http://del.icio.us/post?url=<? echo htmlentities(rawurlencode($url)); ?>&amp;title=<?php echo $eventStruct->m_title; ?>" title="delicious" target="_blank"> <img src="<?php echo $SnowflakesUrl . 'resources/images/Icons/delicious.png'; ?>" height="22" width="22" alt="delicious" /> </a> 
                <a class="flakeit" id="flakeit<?php echo $eventStruct->m_id; ?>" title="flake it" data-type="event"> <span>Flake it</span> <img src="<?php echo $SnowflakesUrl . "resources/images/Icons/Snowflakes.png"; ?>" height="22" width="22" alt="flake it" /> </a> 
            </div>
            <!--/SnowflakePanel-->

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
                    <div class="SFEvent-content">
                    </div>
                    <div class="clear"></div>
                    <div class="SnowflakeDescr">
                        <div class="SnowflakeImage">
                            <a class="colorbox" href="../Uploads/<?php echo $eventStruct->m_image_name; ?>" onerror="this.href='<?php echo $imageMissing; ?>'" title="<?php echo $eventStruct->m_title; ?>" >
                                <img src="../Uploads/<?php echo $eventStruct->m_image_name; ?>"  onerror="this.src='<?php echo $imageMissing; ?>'"  alt="Image" />
                            </a> 
                        </div>
                        <?php echo html_entity_decode($eventStruct->m_body_text); ?>
                    </div>
                    <div id="map-canvas2"></div>
                    <div class="clear PageBreak"></div>
                    <div class="SnowflakeDate"> Date Created |: <?php echo date(" F j, Y", $eventStruct->m_created); ?>  | By - <?php echo $eventStruct->m_created_by; ?> </div>
                    <div class="SnowflakeIt"> 
                        <img src="../resources/images/Icons/Snowflakes.png" height="22" width="22" alt="flake it" /> 
                        <span class="flakeitParam" id="flakecount<?php echo $eventStruct->m_id; ?>"> <?php echo $eventStruct->m_flake_it; ?> </span>
                    </div>


                <?php } else { ?> 
                    <h4>This snowflake event doesn't exist </h4>
                <?php } ?> 
            </div>
            <!--SFEvent Ends--> 
        </div>
        <!--eventWrapper Ends--> 
    <?php } else {
        ?>

        <h4>No Event to view </h4>

    <?php }
    ?>

</div>
<!--/PageWrap --> 
<?php
$SFconnects->close();
?>