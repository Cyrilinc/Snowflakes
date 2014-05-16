<?php

header("Content-Type: text/event-stream");
header("Cache-Control:no-cache");
// user activity Server Sent Event
//initialize the session
if (!isset($_SESSION)) {
    session_start();
}

$startedAt = time();
$userName = filter_input(INPUT_GET, 'userName');
do {
    // Cap connections at 10 seconds. The browser will reopen the connection on close
    if ((time() - $startedAt) > 10) {
        die();
    }

    if (!isset($_SESSION['MM_Username']) && !isset($userName)) {
        die();
    }

    $userName = $userName ? $userName : $_SESSION['MM_Username'];
    require_once '../lib/sf.php';
    require_once '../lib/sfConnect.php';
    require_once '../config/Config.php';

    $config = new databaseParam('../config/config.ini');
    $SFconnects = new sfConnect($config->dbArray());
    $SFconnects->connect(); // Connect to database

    $activities = sfUtils::getActivities($SFconnects, $userName, '../config/config.ini');
    
    sfUtils::sendSSEMsg($startedAt, $activities);
    $SFconnects->close();
    sleep(5);

    // If we didn't use a while loop, the browser would essentially do polling
    // every ~3seconds. Using the while, we keep the connection open and only make
    // one request.
} while (true);
?>

