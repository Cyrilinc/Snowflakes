<?php

header("Content-Type: text/event-stream");
header("Cache-Control:no-cache"); // recommended to prevent caching of event data.
/// Get all count Server Sent Event
//initialize the session
if (!isset($_SESSION))
{
    session_name("Snowflakes");
    session_start();
}

if (!isset($_SESSION['MM_Username']))
{
    die();
}

require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';
$startedAt = time();

$config = new databaseParam('../config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$SFconnects->connect(); // Connect to database
$thedata = sfUtils::getAllCounts($SFconnects, $_SESSION['MM_Username'], true);
$msg = $thedata ? "Count Successful" : "Count Unsuccessful";
//var_dump($thedata);
echo "retry: 30000\n"; // reconnect after 30 seconds
echo "id: $startedAt" . PHP_EOL;
echo "data: {\n";

foreach ($thedata as $key => $value)
{
    echo "data: \"$key\": \"$value\", \n";
}
echo "data: \"msg\": \"$msg\", \n";
echo "data: \"id\": $startedAt\n";
echo "data: }\n\n";
flush();

$SFconnects->close();
?>
