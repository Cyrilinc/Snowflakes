<?php

require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';
require_once '../lib/sfSettings.php';

$sftyval = filter_input(INPUT_GET, 'sfty');
$sfty = isset($sftyval) && $sftyval ? $sftyval : 'snowflake';
$cty = filter_input(INPUT_GET, 'cty');
$contentType = isset($cty) && $cty ? $cty : 'html';
$config = new databaseParam('../config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$SFconnects->connect(); // Connect to database

$siteSettings = new sfSettings('../config/config.ini');
$UploadImgUrl = $siteSettings->m_sfGalleryUrl;
$imageMissing = $UploadImgUrl . "missing_default.png";

$maxRows = 5; // Maximum Number of record per pagination
$pageNum = 0; // The starting page Number
$rsmaxOut = filter_input(INPUT_GET, 'maxout', FILTER_VALIDATE_INT); // get the Maximum output number if otherwise set
if (isset($rsmaxOut)) // if it is set 
{
    $maxRows = $rsmaxOut; // Change the Maximum output number
}
$rsOut = filter_input(INPUT_GET, 'pageNum', FILTER_VALIDATE_INT); // get the page number if otherwise set
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

$total = filter_input(INPUT_GET, 'totalRows', FILTER_VALIDATE_INT);
if (isset($total))
{
    $totalRows = $total;
}
else
{
    $SFconnects->fetch("SELECT COUNT(id) count FROM $tablename WHERE publish = 1 ORDER BY id DESC;");
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
        if (stristr($param, "pageNum") == false &&  stristr($param, "totalRows") == false)
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

if (($sfty == 'snowflake' || $sfty == 'snowflakes') && ($contentType == 'html' || $contentType == 'jsonhtml'))
{
    $Shareurl = strlen($siteSettings->m_snowflakesResultUrl) > 0 ? $siteSettings->m_snowflakesResultUrl : $siteSettings->m_sfUrl . "OneView.php";

    $val = '
        <div style="float: right; background-color:transparent;"><a href="http://cyrilinc.co.uk/snowflakes/" target="_blank"><img src="#POWERLINK#" width="120" height="40" alt="Powered by Snowflakes" /></a> </div>
        <div style="float: right; background-color:transparent;" class="NewButton"><a href="#SNOWFLAKESURL#rss.php?ty=snowflakes" title="Snowflakes rss"> <img src="#SNOWFLAKESURL#resources/images/Icons/Rss.png" height="22" width="22"  alt="Add" /></a></div>
        <div class="clear"></div>';
    if ($contentType == 'html')
    {
        $data = $val;
    }
    else
    {
        $data["html"] = $val;
    }
}
else if (($sfty == 'event' || $sfty == 'events') && ($contentType == 'html' || $contentType == 'jsonhtml'))
{
    $Shareurl = strlen($siteSettings->m_eventsResultUrl) > 0 ? $siteSettings->m_eventsResultUrl : $siteSettings->m_sfUrl . "Events/OneView.php";
    $val = '
        <div style="float: right; background-color:transparent;"><a href="http://cyrilinc.co.uk/snowflakes/" target="_blank"><img src="#POWERLINK#" width="120" height="40" alt="Powered by Snowflakes" /></a> </div>
        <div style="float: right; background-color:transparent;" class="NewButton"><a href="#SNOWFLAKESURL#rss.php?ty=events" title="Snowflakes event rss"> <img src="#SNOWFLAKESURL#resources/images/Icons/Rss.png" height="22" width="22"  alt="Add" /></a></div>
        <div class="clear"></div>';

    if ($contentType == 'html')
    {
        $data = $val;
    }
    else
    {
        $data["html"] = $val;
    }
}
else if ($sfty == 'gallery' && ($contentType == 'html' || $contentType == 'jsonhtml'))
{
    $Shareurl = strlen($siteSettings->m_galleryResultUrl) > 0 ? $siteSettings->m_galleryResultUrl : $siteSettings->m_sfUrl . "Gallery/OneView.php";
    $val = '
        <div style="float: right; background-color:transparent;"><a href="http://cyrilinc.co.uk/snowflakes/" target="_blank"><img src="#POWERLINK#" width="120" height="40" alt="Powered by Snowflakes" /></a> </div>
        <div style="float: right; background-color:transparent;" class="NewButton"><a href="#SNOWFLAKESURL#rss.php?ty=gallery" title="Snowflakes gallery rss"> <img src="#SNOWFLAKESURL#resources/images/Icons/Rss.png" height="22" width="22"  alt="Add" /></a></div>
        <div class="clear"></div>
        <!--wrapper-->
        <div class="wrapper">
        ';

    if ($contentType == 'html')
    {
        $data = $val;
    }
    else
    {
        $data["topHtml"] = $val;
        $deduct = array('<!--wrapper-->', '<div class="wrapper">');
        $data["rssAndSnowflakes"] = str_replace($deduct, "", $val);
    }
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
    if ($contentType == 'html')
    {
        
        $data.='
    <div id="sfFirst" class="smallNewButton"><a href="' . $firstLink . '">First</a></div>
    <div  id="sfPrevious" class="smallNewButton"><a href="' . $previousLink . '">Previous</a></div>
        ';
    }
    else if($contentType == 'jsonhtml')
    {
        $data["paging"] .= '
    <div id="sfFirst" class="smallNewButton"><a onclick="loadSnowflakesApi(\'' . $firstLink . '\')">First</a></div>
    <div id="sfPrevious" class="smallNewButton"><a onclick="loadSnowflakesApi(\'' . $previousLink . '\')">Previous</a></div>
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
    if ($contentType == 'html')
    {
  
        $data.='
    <div id="sfNext" class="smallNewButton"><a href="' . $nextLink . '">Next</a></div>
    <div id="sfLast" class="smallNewButton"><a href="' . $lastLink . '">Last</a></div>
        ';
    }
    else if ($contentType == 'jsonhtml')
    {
        $data["paging"].='
    <div id="sfNext" class="smallNewButton"><a onclick="loadSnowflakesApi(\'' . $nextLink . '\')">Next</a></div>
    <div id="sfLast" class="smallNewButton"><a onclick="loadSnowflakesApi(\'' . $lastLink . '\')">Last</a></div>
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

if ($sfty == 'gallery' && ($contentType == 'html' || $contentType == 'jsonhtml'))
{
    $val = '<div class=" clear Break2"></div>
            <div class="topbar"> <span id="close" class="back">&larr;</span>
                <div class="galleryName" id="name"></div>
            </div>
            <!--/topbar--> 
            <ul id="tp-grid" class="tp-grid">';

    if ($contentType == 'html')
    {
        $data.=$val;
    }
    else
    {
        $data["topHtml2"] =$val;
        $data['imagelist']='';
        $data["bottomHtml"]='';
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

    if ($sfty == 'snowflake' || $sfty == 'snowflakes')
    {
        $snowflakeTypeList[$key] = new snowflakeStruct();
    }
    else if ($sfty == 'event' || $sfty == 'events')
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

        if ($sfty == 'gallery')
        {
            $data['imagelist'] .= $some;
        }
        else
        {
            $data['html'].= $some;
        }
    }
    elseif (strcasecmp($contentType, 'html') == 0)
    {
        $some = $snowflakeTypeList[$key]->toHTML();
        sfUtils::replaceSFHashes($some, '../config/config.ini', $Shareurl);
        $data.= $some;
    }
}

if ($sfty == 'gallery' && ($contentType == 'html' || $contentType == 'jsonhtml'))
{
    $val = '</ul><!--/tp-grid--> 
        </div><!--/wrapper-->';
    if ($contentType == 'html')
    {
        $data .= $val;
    }
    else
    {
        $data["bottomHtml"].=$val;
    }
}

sfUtils::deliverResponseAndExit($data, empty($data) ? 204 : 200, $contentType);

