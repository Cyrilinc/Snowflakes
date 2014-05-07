<?php
$submit = filter_input(INPUT_POST, 'submit');
$flakeit = filter_input(INPUT_POST, 'flakeit');
$flakeitID = filter_input(INPUT_POST, 'id');
$flakeitType = filter_input(INPUT_POST, 'type');
if (isset($submit)) {
    require_once 'lib/sf.php';
    require_once 'lib/sfConnect.php';
    require_once 'config/Config.php';

    $config = Config::getConfig("db", 'config/config.ini');
    $sqlArray = array('type' => $config['type'], 'host' => $config['host'], 'username' => $config['username'], 'password' => sfUtils::decrypt($config['password'], $config['key']), 'database' => $config['dbname']);
    $SFconnects = new sfConnect($sqlArray);
    $SFconnects->connect(); // Connect to database

    if (isset($flakeit) && isset($flakeitID) && isset($flakeitType)) {
        $data = json_encode(sfUtils::flakeIt($SFconnects, $flakeitID, $flakeitType, $flakeit));
        $jsonError = json_last_error();
        if ($jsonError > 0) {
            trigger_error("Error on Json Code $jsonError ", E_USER_NOTICE);
            echo 0;
        } else {
            echo $data;
        }
    } else {
        echo 0;
    }

    $SFconnects->close();
}
?>