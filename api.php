<?php

require_once 'lib/sf.php';
require_once 'lib/sfConnect.php';
require_once 'config/Config.php';
?>
<?php

$sfty = filter_input(INPUT_GET, 'sfty') ? filter_input(INPUT_GET, 'sfty') : 'snowflake';
$contentType = filter_input(INPUT_GET, 'cty') ? filter_input(INPUT_GET, 'cty') : 'html';
$config = new databaseParam('config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$SFconnects->connect(); // Connect to database

$settingsConfig = Config::getConfig("settings", 'config/config.ini');
$UploadImgUrl = $settingsConfig['m_sfGalleryUrl'];
$imageMissing = $UploadImgUrl . "missing_default.png";

$query_rsOut = "SELECT * FROM " . sfUtils::tablenameFromType($sfty) . " WHERE publish = 1 ORDER BY created DESC";
$SFconnects->fetch($query_rsOut);
$row_rsOut = $SFconnects->getResultArray();
$snowflakeTypeList = array();
$data;
$Shareurl;
if (($sfty == 'snowflake' || $type == 'snowflakes') && $contentType == 'html')
{
    $Shareurl = isset($settingsConfig["snowflakesResultUrl"])? $settingsConfig["snowflakesResultUrl"] : $settingsConfig['m_sfUrl'] . "OneView.php";
    
    $data = '
        <div style="float: right; background-color:<?php echo $sflogo; ?>;"><a href="http://cyrilinc.co.uk/snowflakes/" target="_blank"><img src="#POWERLINK#" width="120" height="40" alt="Powered by Snowflakes" /></a> </div>
        <div style="float: right; background-color:transparent;" class="NewButton"><a href="#SNOWFLAKESURL#rss.php?ty=snowflakes" title="Snowflakes rss"> <img src="#SNOWFLAKESURL#resources/images/Icons/Rss.png" height="22" width="22"  alt="Add" /></a></div>
        <div class="clear"></div>';
}
else if (($sfty == 'event' || $type == 'events') && $contentType == 'html')
{
    $Shareurl = isset($settingsConfig["eventsResultUrl"])? $settingsConfig["eventsResultUrl"] : $settingsConfig['m_sfUrl'] . "Events/OneView.php";
    $data = '
        <div style="float: right; background-color:<?php echo $sflogo; ?>;"><a href="http://cyrilinc.co.uk/snowflakes/" target="_blank"><img src="#POWERLINK#" width="120" height="40" alt="Powered by Snowflakes" /></a> </div>
        <div style="float: right; background-color:transparent;" class="NewButton"><a href="#SNOWFLAKESURL#rss.php?ty=events" title="Snowflakes event rss"> <img src="#SNOWFLAKESURL#resources/images/Icons/Rss.png" height="22" width="22"  alt="Add" /></a></div>
        <div class="clear"></div>';
}
else if ($sfty == 'gallery' && $contentType == 'html')
{
    $Shareurl = isset($settingsConfig["galleryResultUrl"])? $settingsConfig["galleryResultUrl"] : $settingsConfig['m_sfUrl'] . "Gallery/OneView.php";
    $data = '
        <div style="float: right; background-color:<?php echo $sflogo; ?>;"><a href="http://cyrilinc.co.uk/snowflakes/" target="_blank"><img src="#POWERLINK#" width="120" height="40" alt="Powered by Snowflakes" /></a> </div>
        <div style="float: right; background-color:transparent;" class="NewButton"><a href="#SNOWFLAKESURL#rss.php?ty=gallery" title="Snowflakes gallery rss"> <img src="#SNOWFLAKESURL#resources/images/Icons/Rss.png" height="22" width="22"  alt="Add" /></a></div>
        <div class="clear"></div>
        <!--wrapper-->
        <div class="wrapper">
            <!--topbar-->
            <div class="topbar"> <span id="close" class="back">&larr;</span>
                <div class="galleryName" id="name"></div>
            </div>
            <!--topbar End--> ';
}

sfUtils::replaceSFHashes($data, 'config/config.ini');

foreach ($row_rsOut as $key => $value)
{

    if ($sfty == 'snowflake' || $type == 'snowflakes')
    {
        $snowflakeTypeList[$key] = new snowflakeStruct();
    }
    else if ($sfty == 'event' || $type == 'events')
    {
        $snowflakeTypeList[$key] = new eventStruct();
    }
    else if ($sfty == 'gallery')
    {
        $snowflakeTypeList[$key] = new galleryStruct();
    }
    else
    {
        sfUtils::deliverResponseAndExit(' ', 400, $contentType);
    }

    $snowflakeTypeList[$key]->populate($value);
    $snowflakeTypeList[$key]->m_image_dir = $UploadImgUrl;

    if (strcasecmp($contentType, 'xml') == 0)
    {
        $some = $snowflakeTypeList[$key]->toXml() . "\n";
        sfUtils::replaceSFHashes($some, 'config/config.ini',$Shareurl);
        $data .= $some;
    }
    elseif (strcasecmp($contentType, 'json') == 0)
    {
        if(!is_array($data) && !$data){
            $data = array();
        }
        $arr= $snowflakeTypeList[$key]->toArray();
        sfUtils::replaceSFHashes($arr, 'config/config.ini',$Shareurl);
        array_push($data, $arr);
    }
    elseif (strcasecmp($contentType, 'html') == 0)
    {
        $some = $snowflakeTypeList[$key]->toHTML();
        sfUtils::replaceSFHashes($some, 'config/config.ini',$Shareurl);
        $data.= $some;
        if ($sfty == 'gallery'){
            $data.='
        </div>
        <!--wrapper Ends-->';
        }
    }
}

sfUtils::deliverResponseAndExit($data, empty($data) ? 204 : 200, $contentType);

