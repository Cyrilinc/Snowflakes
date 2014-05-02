<?php

$Post_submit = filter_input(INPUT_POST, 'submit');
if (isset($Post_submit)) {
    require_once 'lib/sf.php';
    require_once 'lib/sfConnect.php';
    require_once 'config/Config.php';

    $config = Config::getConfig("db", 'config/config.ini');
    $sqlArray = array('type' => $config['type'], 'host' => $config['host'], 'username' => $config['username'], 'password' => sfUtils::decrypt($config['password'], $config['key']), 'database' => $config['dbname']);
    $SFconnects = new sfConnect($sqlArray);
    $SFconnects->connect(); // Connect to database

    if (isset($_POST['flakeit']) && isset($_POST['id']) && isset($_POST['type'])) {
        $data = json_encode(sfUtils::flakeIt($SFconnects, $_POST['id'], $_POST['type'], $_POST['flakeit']));
        $jsonError = json_last_error();
        if ($jsonError > 0) {
            trigger_error("Error on Json Code $jsonError ", E_USER_ERROR);
            echo 0;
        } else {
            echo $data;
        }
    } else {
        echo 0;
    }

    /* if (isset($_POST['flakeit']) && isset($_POST['id']) && isset($_POST['type']))
      echo json_encode(sfUtils::flakeIt($SFconnects, $_POST['id'], $_POST['type'],$_POST['flakeit']));
      else
      echo 0; */

    $SFconnects->close();
}
?>