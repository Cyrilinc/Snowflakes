<?php
require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';
?>
<?php
$currentPage = sfUtils::getFilterServer( 'PHP_SELF');

$maxRows = 8;
$pageNum = 0;
$EventsRs = filter_input(INPUT_GET, 'pageNum');
if (isset($EventsRs)) {
    $pageNum = $EventsRs;
}
$startRow = $pageNum * $maxRows;

$config = new databaseParam('../config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$SFconnects->connect(); // Connect to database

$TodaysDate = sfUtils::todaysDate();
$query = "SELECT id,title,event_time,event_date,end_time,end_date,location,created,created_by,flake_it FROM snowflakes_events WHERE publish = 1 AND event_date >= '" . $TodaysDate . "'  ";
$query_limit = sprintf("%s LIMIT %d, %d", $query, $startRow, $maxRows);
$SFconnects->fetch($query_limit);
$row = $SFconnects->getResultArray();

$eventStructList = array();
foreach ($row as $key => $value) {
    $eventStructList[$key] = new eventStruct();
    $eventStructList[$key]->populate($value);
    //$eventStructList[$key]->printEvents();
}

$total = filter_input(INPUT_GET, 'totalRows');
if (isset($total)) {
    $totalRows = $total;
} else {
    $SFconnects->fetch("SELECT COUNT(id) count FROM snowflakes_events WHERE publish = 1 AND event_date >= '" . $TodaysDate . "'  ");
    $result = $SFconnects->getResultArray();
    $totalRows = $result[0]['count'];
}
$totalPages = ceil($totalRows / $maxRows) - 1;

$query_SiteSettings = "SELECT sf_url, result_url, out_url, events_result_url, events_output_url, gallery_result_url, gallery_out_url FROM snowflakes_settings";
$SFconnects->fetch($query_SiteSettings);
$result = $SFconnects->getResultArray();
$row_SiteSettings = $result[0];

$queryString = "";
$query_string = sfUtils::getFilterServer( 'QUERY_STRING');
if (!empty($query_string)) {
    $params = explode("&", $query_string);
    $newParams = array();
    foreach ($params as $param) {
        if (stristr($param, "pageNum") == false &&
                stristr($param, "totalRows") == false) {
            array_push($newParams, $param);
        }
    }
    if (count($newParams) != 0) {
        $queryString = "&amp;" . htmlentities(implode("&", $newParams));
    }
}
$queryString = sprintf("&amp;totalRows=%d%s", $totalRows, $queryString);
?>
<?php
$url = $otherurl = sfUtils::curPageURL();
$SnowflakesUrl = $row_SiteSettings['sf_url'];

$SFEventsResultUrl = $row_SiteSettings['events_result_url'];

if (isset($row_SiteSettings['events_output_url'])) {
    $currentPage = $row_SiteSettings['events_output_url'];
}

$Powerlink = $row_SiteSettings['sf_url'] . "resources/images/Snowflakes2.png";
$rsslink = $row_SiteSettings['sf_url'] . "resources/images/Icons/Rss.png";
$Shareurl = $row_SiteSettings['sf_url'] . "Events/OneView.php";
if (strlen($SFEventsResultUrl) > 0) { /// if user provides result page in snowflakes settings
    $Shareurl = $SFEventsResultUrl;
}

$sflogo = "transparent";
$rssflogo = filter_input(INPUT_GET, 'sflogo');
if (isset($rssflogo)) {
    $sflogo = "#" . $rssflogo;
}
$settingsConfig = Config::getConfig("settings", '../config/config.ini');
?>
<script type="text/javascript">
    var flakeitUrl = "<?php echo $settingsConfig['flakeItUrl']; ?>";
</script>
<script type="text/javascript" src="<?php echo $settingsConfig['m_sfUrl']; ?>resources/Js/flakeit.js"></script>

<div style="float: right; background-color:<?php echo $sflogo; ?>;"><a href="http://cyrilinc.co.uk/snowflakes/" target="_blank"><img src="<?php echo $Powerlink; ?>" width="120" height="40" alt="snowflakes"/></a> </div>
<div style="float: right; background-color:<?php echo $sflogo; ?>;" class="NewButton"><a href="<?php echo $row_SiteSettings['sf_url']; ?>rss.php?ty=events" title="Snowflakes event rss"> <img src="<?php echo $rsslink; ?>" height="22" width="22"  alt="Add" /></a></div>
<!-- Break -->
<div class="clear"></div>
<!-- End of Break --> 
<?php if ($pageNum > 0) { // Show if not first page       ?>
    <div class="smallNewButton"><a href="<?php printf("%s?pageNum=%d%s", $currentPage, 0, $queryString); ?>">First</a></div>
    <div class="smallNewButton"><a href="<?php printf("%s?pageNum=%d%s", $currentPage, max(0, $pageNum - 1), $queryString); ?>">Previous</a></div>
<?php } // Show if not first page      ?>
<?php if ($pageNum < $totalPages) { // Show if not last page    ?>
    <div class="smallNewButton"><a href="<?php printf("%s?pageNum=%d%s", $currentPage, min($totalPages, $pageNum + 1), $queryString); ?>">Next</a></div>
    <div class="smallNewButton"><a href="<?php printf("%s?pageNum=%d%s", $currentPage, $totalPages, $queryString); ?>">Last</a></div>
<?php } // Show if not last page      ?>
<!-- Break -->
<div class="clear"></div>
<div class="Break2"></div>
<!-- End of Break --> 
<?php
if ($totalRows > 0) {
    $i = 0;
    do {
        $eventdate = new DateTime($eventStructList[$i]->m_event_date);
        $enddate = new DateTime($eventStructList[$i]->m_end_date);
        ?>

        <!--eventWrapper-->
        <div class="eventWrapper fl"> 

            <!--SnowflakePanel-->
            <div class="SnowflakePanel"> 
                <span>View </span>
                <a href="<?php echo $Shareurl; ?>?Eventid=<?php echo $eventStructList[$i]->m_id; ?>" title="View this Event"> <img src="<?php echo $SnowflakesUrl . "resources/images/Icons/View.png"; ?>" height="22" width="22" alt="Edit" /> </a>  
                <span>Share </span> 
                <a href="http://twitter.com/home?status=<?php echo htmlentities(rawurlencode($eventStructList[$i]->m_title)); ?>%20<? echo "" . $Shareurl . "?Eventid=" . $eventStructList[$i]->m_id; ?>" title="Twitter" target="_blank"> <img src="<?php echo $SnowflakesUrl . "resources/images/Icons/Twitter.png"; ?>" height="22" width="22" alt="Twitter" /> </a> 
                <a href="http://www.facebook.com/sharer.php?u=<? echo "" . $Shareurl . "?Eventid=" . $eventStructList[$i]->m_id; ?>" title="Facebook" target="_blank"> <img src="<?php echo $SnowflakesUrl . "resources/images/Icons/Facebook.png"; ?>" height="22" width="22" alt="Facebook" /> </a> 
                <a href="https://plus.google.com/share?url=<? echo "" . $Shareurl . "?Eventid=" . $eventStructList[$i]->m_id; ?>&amp;title=<?php echo htmlentities(rawurlencode($eventStructList[$i]->m_title)); ?>" title="GooglePlus" target="_blank"> <img src="<?php echo $SnowflakesUrl . "resources/images/Icons/GooglePlus.png"; ?>" height="22" width="22" alt="GooglePlus" /> </a> 
                <a href="http://digg.com/submit?phase=2&amp;url=<? echo "" . $Shareurl . "?Eventid=" . $eventStructList[$i]->m_id; ?>&amp;title=<?php echo htmlentities(rawurlencode($eventStructList[$i]->m_title)); ?>" title="Digg" target="_blank"> <img src="<?php echo $SnowflakesUrl . "resources/images/Icons/Digg.png"; ?>" height="22" width="22" alt="Digg" /> </a> 
                <a href="http://stumbleupon.com/submit?url=<? echo "" . $Shareurl . "?Eventid=" . $eventStructList[$i]->m_id; ?>&amp;title=<?php echo htmlentities(rawurlencode($eventStructList[$i]->m_title)); ?>" title="stumbleupon" target="_blank"> <img src="<?php echo $SnowflakesUrl . "resources/images/Icons/stumbleupon.png"; ?>" height="22" width="22" alt="stumbleupon" /> </a> 
                <a href="http://del.icio.us/post?url=<? echo "" . $Shareurl . "?Eventid=" . $eventStructList[$i]->m_id; ?>&amp;title=<?php echo htmlentities(rawurlencode($eventStructList[$i]->m_title)); ?>" title="delicious" target="_blank"> <img src="<?php echo $SnowflakesUrl . "resources/images/Icons/delicious.png"; ?>" height="22" width="22" alt="delicious" /> </a>
                <a class="flakeit" id="flakeit<?php echo $eventStructList[$i]->m_id; ?>" title="flake it" data-type="event"><span>Flake it</span><img src="<?php echo $SnowflakesUrl . "resources/images/Icons/Snowflakes.png"; ?>" height="22" width="22" alt="flake it" /> </a> 
            </div>
            <!--End of SnowflakePanel-->

            <div class="Break2"></div>
            <!--SFEvent-->
            <div class="SFEvent">
                <div class="SFEvent-date">

                    <ul class="startDate">
                        <li class="month"> <?php echo $eventdate->format(" M"); ?></li>
                        <li class="day"><?php echo $eventdate->format("d"); ?></li>
                        <li class="year"><?php echo $eventdate->format(" Y"); ?></li>
                        <li class="time"><?php echo sfUtils::toAmPmTime($eventStructList[$i]->m_event_time); ?></li>
                    </ul>
                    <ul class="eventTitle">
                        <li><a href="<?php echo $Shareurl; ?>?Eventid=<?php echo $eventStructList[$i]->m_id; ?>" rel="bookmark" title="<?php echo $eventStructList[$i]->m_title; ?>"><?php echo $eventStructList[$i]->m_title; ?></a></li>
                        <li><a href="<?php echo $Shareurl; ?>?Eventid=<?php echo $eventStructList[$i]->m_id; ?>" rel="bookmark" title="location"><?php echo $eventStructList[$i]->m_location; ?></a></li>
                    </ul>
                    <ul class="endDate">
                        <li class="month"> <?php echo $enddate->format(" M"); ?></li>
                        <li class="day"><?php echo $enddate->format("d"); ?></li>
                        <li class="year"><?php echo $enddate->format(" Y"); ?></li>
                        <li class="time"><?php echo sfUtils::toAmPmTime($eventStructList[$i]->m_end_time); ?></li>
                    </ul>
                </div>
            </div>
            <!--SFEvent Ends--> 
            <div class="clear"></div>
            <div class="SnowflakeDate"> Posted |: <?php echo date(" F j, Y", $eventStructList[$i]->m_created); ?>  | By - <?php echo $eventStructList[$i]->m_created_by; ?> </div>
            <div class="SnowflakeIt"> 
                <img src="<?php echo $SnowflakesUrl . "resources/images/Icons/Snowflakes.png"; ?>" height="22" width="22" alt="flake it" /> 
                <span class="flakeitParam" id="flakecount<?php echo $eventStructList[$i]->m_id; ?>"> <?php echo $eventStructList[$i]->m_flake_it; ?> </span>
            </div>
        </div>
        <!--eventWrapper Ends-->
        <?php
        $i++;
    } while ($i < count($eventStructList));
    ?>

<?php } else { ?> 
    <h4 class="SummaryHead">There are no Snowflakes  Events </h4>
<?php } ?> 

<?php
$SFconnects->close();
?>
