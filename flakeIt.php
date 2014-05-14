<?php

$Post_submit = filter_input(INPUT_POST, 'submit');
$Post_flakeit = filter_input(INPUT_POST, 'flakeit');
$Post_flakeitID = filter_input(INPUT_POST, 'id');
$Post_flakeitType = filter_input(INPUT_POST, 'type');

$submit = isset($Post_submit) ? $Post_submit : filter_input(INPUT_GET, 'submit');
$flakeit = isset($Post_flakeit) ? $Post_flakeit : filter_input(INPUT_GET, 'flakeit');
$flakeitID = isset($Post_flakeitID) ? $Post_flakeitID : filter_input(INPUT_GET, 'id');
$flakeitType = isset($Post_flakeitType) ? $Post_flakeitType : filter_input(INPUT_GET, 'type');

if (isset($submit)) {
    require_once 'lib/sf.php';
    require_once 'lib/sfConnect.php';
    require_once 'config/Config.php';

    $config = new databaseParam('config/config.ini');
    $SFconnects = new sfConnect($config->dbArray());
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
