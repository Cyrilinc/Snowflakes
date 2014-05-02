<?php

header("Content-Type: application/xml; charset=ISO-8859-1");
require_once 'lib/sf.php';
require_once 'lib/sfConnect.php';
require_once 'config/Config.php';

$rssTypepost = -1;
$RssType = filter_input(INPUT_GET, 'ty');
if (isset($RssType)) {
    $rssTypepost = $RssType;
}

$config = Config::getConfig("db", 'config/config.ini');
$sqlArray = array('type' => $config['type'], 'host' => $config['host'], 'username' => $config['username'], 'password' => sfUtils::decrypt($config['password'], $config['key']), 'database' => $config['dbname']);
$SFconnects = new sfConnect($sqlArray);
$SFconnects->connect(); // Connect to database

if ($rssTypepost == "snowflakes") {
    $query_rsOut = "SELECT * FROM snowflakes WHERE publish = 1 ORDER BY created DESC";
    $SFconnects->fetch($query_rsOut);
    $row_rsOut = $SFconnects->getResultArray();
    $flakeStructList = array();
    foreach ($row_rsOut as $key => $value) {
        $flakeStructList[$key] = new snowflakeStruct();
        $flakeStructList[$key]->populate($value);
    }
    echo sfUtils::createSnowflakesRss($SFconnects, $flakeStructList, 'config/Config.php');
}
else if ($rssTypepost == "events") {
    $TodaysDate = sfUtils::todaysDate();
    $query_rsOut = "SELECT * FROM snowflakes_events WHERE publish = 1 AND event_date >= '" . $TodaysDate . "';";
    $SFconnects->fetch($query_rsOut);
    $row_rsOut = $SFconnects->getResultArray();
    $eventStructList = array();
    foreach ($row_rsOut as $key => $value) {
        $eventStructList[$key] = new eventStruct();
        $eventStructList[$key]->populate($value);
    }
    echo sfUtils::createEventRss($SFconnects, $eventStructList, 'config/Config.php');
}
else if ($rssTypepost == "gallery") {
    $query_rsOut = "SELECT * FROM snowflakes_gallery WHERE publish=1 ORDER BY id DESC";
    $SFconnects->fetch($query_rsOut);
    $row_rsOut = $SFconnects->getResultArray();
    $GalleryList = array();
    foreach ($row_rsOut as $key => $value) {
        $GalleryList[$key] = new galleryStruct();
        $GalleryList[$key]->populate($value);
    }
    echo sfUtils::createGalleryRss($SFconnects, $GalleryList, 'config/Config.php');
}