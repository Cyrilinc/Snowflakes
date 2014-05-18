<?php
require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';
?>
<?php
$maxRows_EventsRs = filter_input(INPUT_GET, 'MaxNumber');
$pageNum_EventsRs = 0;
$EventsRs = filter_input(INPUT_GET, 'pageNum_EventsRs');
if (isset($EventsRs)) {
    $pageNum_EventsRs = $EventsRs;
}

$startRow_EventsRs = $pageNum_EventsRs * $maxRows_EventsRs;

$config = new databaseParam('../config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$SFconnects->connect(); // Connect to database

$TodaysDate = sfUtils::todaysDate();
$query_EventsRs = "SELECT id,title,event_time,event_date,end_time,end_date,location,created,created_by,flake_it FROM snowflakes_events WHERE publish = 1 AND event_date >= '" . $TodaysDate . "'  ";
$query_limit_EventsRs = sprintf("%s LIMIT %d, %d", $query_EventsRs, $startRow_EventsRs, $maxRows_EventsRs);
$SFconnects->fetch($query_limit_EventsRs);
$row_EventsRs = $SFconnects->getResultArray();
$eventStructList = array();
foreach ($row_EventsRs as $key => $value) {
    $eventStructList[$key] = new eventStruct();
    $eventStructList[$key]->populate($value);
    //$eventStructList[$key]->printEvents();
}

$total_EventsRs = filter_input(INPUT_GET, 'totalRows_EventsRs');
if (isset($total_EventsRs)) {
    $totalRows_EventsRs = $total_EventsRs;
} else {
    $sql = str_replace("SELECT id,title,event_time,event_date,end_time,end_date,location,created,created_by,flake_it FROM", "SELECT COUNT(id) count FROM", $query_EventsRs);
    $SFconnects->fetch($sql);
    $result = $SFconnects->getResultArray();
    $totalRows_EventsRs = $result[0]['count'];
}
$totalPages_EventsRs = ceil($totalRows_EventsRs / $maxRows_EventsRs) - 1;

$query_SiteSettings = "SELECT sf_url, result_url, out_url, events_result_url, events_output_url, gallery_result_url, gallery_out_url FROM snowflakes_settings";
$SFconnects->fetch($query_SiteSettings);
$result = $SFconnects->getResultArray();
$row_SiteSettings = $result[0];
?>
<?php
$url = $otherurl = strtok(sfUtils::curPageURL(), '?');
$SnowflakesUrl = $row_SiteSettings['sf_url'];

if (isset($row_SiteSettings['events_result_url'])) {
    $SFEventsResultUrl = $row_SiteSettings['events_result_url'];
} else {
    $SFEventsResultUrl = 'notset';
}
// if viewing from Snowflakes's OutputView.php file
if (strpos($otherurl, 'OutputView.php') !== false) {
    $Shareurl = str_replace("OutputView.php", "OneView.php", $url);
    $Powerlink = str_replace("OutputView.php", "../resources/images/Snowflakes2.png", $otherurl);
} else if (strpos($otherurl, 'SummaryOut.php') !== false) {// else if viewing from Snowflakes's SummaryOut.php file
    $Shareurl = str_replace("SummaryOut.php", "OneView.php", $url);
    $Powerlink = str_replace("SummaryOut.php", "../resources/images/Snowflakes2.png", $otherurl);
    if ($SFEventsResultUrl !== 'notset') { /// if user provides result page in snowflakes settings
        $Shareurl = $SFEventsResultUrl;
    }
} else {// else if viewing Snowflakes from another file outside snowflakes
    $Shareurl = $SnowflakesUrl . "OneView.php";
    if ($SFEventsResultUrl !== 'notset') { /// if user provides result page in snowflakes settings
        $Shareurl = $SFEventsResultUrl;
    }

    $Powerlink = $SnowflakesUrl . "../resources/images/Snowflakes2.png";
}
?>
<?php if ($maxRows_EventsRs != Null) { ?>  
    <?php
    if ($totalRows_EventsRs > 0) {
        $i = 0;
        ?>
        <?php
        do {
            $eventdate = new DateTime($eventStructList[$i]->m_event_date);
            $enddate = new DateTime($eventStructList[$i]->m_end_date);
            ?>   
            <!--eventWrapper-->
            <div class="eventWrapper fl"> 

                <div class="Break2"></div>

                <!--SFEvent-->
                <div class="SFEvent clearfix">
                    <div class="SFEvent-date">
                        <ul class="startDate">
                            <li class="month"><?php echo $eventdate->format(" M"); ?></li>
                            <li class="day"><?php echo $eventdate->format("d"); ?></li>
                            <li class="year"><?php echo $eventdate->format(" Y"); ?></li>
                            <li class="time"><?php echo sfUtils::toAmPmTime($eventStructList[$i]->m_event_time); ?></li>
                        </ul>
                        <ul class="eventTitle">
                            <li><a href="#" rel="bookmark" title="<?php echo $eventStructList[$i]->m_title; ?>"><?php echo $eventStructList[$i]->m_title; ?></a></li>
                            <li><a href="#" rel="bookmark" title="location"><?php echo $eventStructList[$i]->m_location; ?></a></li>
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
        <h4 class="SummaryHead">There are no Events </h4>
    <?php } ?> 


<?php } else {
    ?>

    <h4 class="SummaryHead">No Max number of Snowflakes Events indicated </h4>

<?php }
?> 

<?php
$SFconnects->close();
?>
