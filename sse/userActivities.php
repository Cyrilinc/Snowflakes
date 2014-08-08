<?php

header("Content-Type: text/event-stream");
header("Cache-Control:no-cache"); // recommended to prevent caching of event data.
// user activity Server Sent Event
//initialize the session
if (!isset($_SESSION))
{
    session_name("Snowflakes");
    session_start();
}

$userName = filter_input(INPUT_GET, 'userName');

if (!isset($_SESSION['MM_Username']) && !isset($userName))
{
    die();
}
$startedAt = time();
$userName = $userName ? $userName : $_SESSION['MM_Username'];
require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';

$config = new databaseParam('../config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$SFconnects->connect(); // Connect to database

$activities = sfUtils::getActivities($SFconnects, $userName, '../config/config.ini');

sfUtils::sendSSEMsg($startedAt, $activities, '30000'); // reconnect after 30 seconds
$SFconnects->close();
?>

