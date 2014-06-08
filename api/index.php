<?php

require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';

$sfty = filter_input(INPUT_GET, 'sfty') ? filter_input(INPUT_GET, 'sfty') : 'snowflake';
$contentType = filter_input(INPUT_GET, 'cty') ? filter_input(INPUT_GET, 'cty') : 'html';
$config = new databaseParam('../config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$SFconnects->connect(); // Connect to database

$settingsConfig = Config::getConfig("settings", '../config/config.ini');
$UploadImgUrl = $settingsConfig['m_sfGalleryUrl'];
$imageMissing = $UploadImgUrl . "missing_default.png";

$maxRows = 5; // Maximum Number of record per pagination
$pageNum = 0; // The starting page Number
$rsmaxOut = filter_input(INPUT_GET, 'maxout'); // get the Maximum output number if otherwise set
if (isset($rsmaxOut)) // if it is set 
{
    $maxRows = $rsmaxOut; // Change the Maximum output number
}
$rsOut = filter_input(INPUT_GET, 'pageNum'); // get the page number if otherwise set
if (isset($rsOut)) // if it is set 
{
    $pageNum = $rsOut; // Change the page number
}
$startRow = $pageNum * $maxRows;

$tablename = sfUtils::tablenameFromType($sfty);

$query = "SELECT * FROM $tablename WHERE publish = 1 ORDER BY created DESC";
$query_limit = sprintf("%s LIMIT %d, %d", $query, $startRow, $maxRows);
$SFconnects->fetch($query_limit);
$result = $SFconnects->getResultArray();
$snowflakeTypeList = array();
$data;
$Shareurl;

$total = filter_input(INPUT_GET, 'totalRows');
if (isset($total))
{
    $totalRows = $total;
}
else
{
    $SFconnects->fetch("SELECT COUNT(id) count FROM $tablename WHERE publish = 1;");
    $countResult = $SFconnects->getResultArray();
    $totalRows = $countResult[0]['count'];
}
$totalPages = ceil($totalRows / $maxRows) - 1;

$queryString = "";
$query_string = sfUtils::getFilterServer('QUERY_STRING');
if (!empty($query_string))
{
    $params = explode("&", $query_string);
    $newParams = array();
    foreach ($params as $param)
    {
        if (stristr($param, "pageNum") == false &&
                stristr($param, "totalRows") == false)
        {
            array_push($newParams, $param);
        }
    }
    if (count($newParams) != 0)
    {
        $queryString = "&amp;" . htmlentities(implode("&", $newParams));
    }
}
$queryString = sprintf("&amp;totalRows=%d%s", $totalRows, $queryString);

if (($sfty == 'snowflake' || $type == 'snowflakes') && ($contentType == 'html' || $contentType == 'jsonhtml'))
{
    $Shareurl = isset($settingsConfig["snowflakesResultUrl"]) ? $settingsConfig["snowflakesResultUrl"] : $settingsConfig['m_sfUrl'] . "OneView.php";

    $data = '
        <div style="float: right; background-color:transparent;"><a href="http://cyrilinc.co.uk/snowflakes/" target="_blank"><img src="#POWERLINK#" width="120" height="40" alt="Powered by Snowflakes" /></a> </div>
        <div style="float: right; background-color:transparent;" class="NewButton"><a href="#SNOWFLAKESURL#rss.php?ty=snowflakes" title="Snowflakes rss"> <img src="#SNOWFLAKESURL#resources/images/Icons/Rss.png" height="22" width="22"  alt="Add" /></a></div>
        <div class="clear"></div>';
}
else if (($sfty == 'event' || $type == 'events') && ($contentType == 'html' || $contentType == 'jsonhtml'))
{
    $Shareurl = isset($settingsConfig["eventsResultUrl"]) ? $settingsConfig["eventsResultUrl"] : $settingsConfig['m_sfUrl'] . "Events/OneView.php";
    $data = '
        <div style="float: right; background-color:transparent;"><a href="http://cyrilinc.co.uk/snowflakes/" target="_blank"><img src="#POWERLINK#" width="120" height="40" alt="Powered by Snowflakes" /></a> </div>
        <div style="float: right; background-color:transparent;" class="NewButton"><a href="#SNOWFLAKESURL#rss.php?ty=events" title="Snowflakes event rss"> <img src="#SNOWFLAKESURL#resources/images/Icons/Rss.png" height="22" width="22"  alt="Add" /></a></div>
        <div class="clear"></div>';
}
else if ($sfty == 'gallery' && ($contentType == 'html' || $contentType == 'jsonhtml'))
{
    $Shareurl = isset($settingsConfig["galleryResultUrl"]) ? $settingsConfig["galleryResultUrl"] : $settingsConfig['m_sfUrl'] . "Gallery/OneView.php";
    $data = '
        <div style="float: right; background-color:transparent;"><a href="http://cyrilinc.co.uk/snowflakes/" target="_blank"><img src="#POWERLINK#" width="120" height="40" alt="Powered by Snowflakes" /></a> </div>
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
else if ($contentType == 'xml')
{
    $xmlData = new SimpleXMLElement('<paging></paging>');
}

$currentPage = sfUtils::getFilterServer('PHP_SELF');
$firstLink = $currentPage . "?pageNum=0" . $queryString;
$previousLink = $currentPage . "?pageNum=" . max(0, $pageNum - 1) . $queryString;
$nextLink = $currentPage . "?pageNum=" . min($totalPages, $pageNum + 1) . $queryString;
$lastLink = $currentPage . "?pageNum=" . $totalPages . $queryString;

if ($pageNum > 0)
{
    // Show if not first page  
    if ($contentType == 'html' || $contentType == 'jsonhtml')
    {
        $data.='
    <div class="smallNewButton"><a href="' . $firstLink . '">First</a></div>
    <div class="smallNewButton"><a href="' . $previousLink . '">Previous</a></div>
        ';
    }
    else if ($contentType == 'json')
    {
        $data['paging']['First'] = $firstLink;
        $data['paging']['Previous'] = $previousLink;
    }
    else if ($contentType == 'xml')
    {
        $xmlData->addChild('First', $firstLink);
        $xmlData->addChild('Previous', $previousLink);
    }
}

if ($pageNum < $totalPages)
{
    // Show if not last page
    if ($contentType == 'html' || $contentType == 'jsonhtml')
    {
        $data.='
    <div class="smallNewButton"><a href="' . $nextLink . '">Next</a></div>
    <div class="smallNewButton"><a href="' . $lastLink . '">Last</a></div>
        ';
    }
    else if ($contentType == 'json')
    {
        $data['paging']['Next'] = $nextLink;
        $data['paging']['Last'] = $lastLink;
    }
    else if ($contentType == 'xml')
    {
        $xmlData->addChild('Next', $nextLink);
        $xmlData->addChild('Last', $lastLink);
    }
}

if ($contentType == 'xml')
{
    $xmlData = str_replace('<?xml version="1.0"?>', '', $xmlData->asXML());
    $data .= $xmlData;
}

sfUtils::replaceSFHashes($data, '../config/config.ini');

foreach ($result as $key => $value)
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
        sfUtils::replaceSFHashes($some, '../config/config.ini', $Shareurl);
        $data .= $some;
    }
    elseif (strcasecmp($contentType, 'json') == 0)
    {
        if (!is_array($data) && !$data)
        {
            $data = array();
        }
        $arr = $snowflakeTypeList[$key]->toArray();
        sfUtils::replaceSFHashes($arr, '../config/config.ini', $Shareurl);
        array_push($data, $arr);
    }
    elseif (strcasecmp($contentType, 'jsonhtml') == 0)
    {
        $some = $snowflakeTypeList[$key]->toHTML();
        sfUtils::replaceSFHashes($some, '../config/config.ini', $Shareurl);
        $data.= $some;
        if ($sfty == 'gallery')
        {
            $data.='
        </div>
        <!--wrapper Ends-->';
        }
    }
    elseif (strcasecmp($contentType, 'html') == 0)
    {
        $some = $snowflakeTypeList[$key]->toHTML();
        sfUtils::replaceSFHashes($some, '../config/config.ini', $Shareurl);
        $data.= $some;
        if ($sfty == 'gallery')
        {
            $data.='
        </div>
        <!--wrapper Ends-->';
        }
    }
}

sfUtils::deliverResponseAndExit($data, empty($data) ? 204 : 200, $contentType);

