<?php
require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';
require_once '../lib/sfSettings.php';
require_once '../lib/sfImageProcessor.php';
?>
<?php
$currentPage = sfUtils::getFilterServer('PHP_SELF');

$maxRows = 6;
$pageNum = 0;
$pageSFGallery = filter_input(INPUT_GET, 'pageNum');
if (isset($pageSFGallery))
{
    $pageNum = $pageSFGallery;
}
$startRow = $pageNum * $maxRows;

$config = new databaseParam('../config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$SFconnects->connect(); // Connect to database

$query = "SELECT * FROM snowflakes_gallery WHERE publish=1 ORDER BY id DESC";
$query_limit = sprintf("%s LIMIT %d, %d", $query, $startRow, $maxRows);
$SFconnects->fetch($query_limit);
$row = $SFconnects->getResultArray();

$galleryStructList = array();
foreach ($row as $key => $value)
{
    $galleryStructList[$key] = new galleryStruct();
    $galleryStructList[$key]->populate($value);
}


$totalSFGallery = filter_input(INPUT_GET, 'totalRows');
if (isset($totalSFGallery))
{
    $totalRows = $totalSFGallery;
}
else
{
    $SFconnects->fetch("SELECT COUNT(id) count FROM snowflakes_gallery WHERE publish=1 ORDER BY id DESC");
    $result = $SFconnects->getResultArray();
    $totalRows = $result[0]['count'];
}
$totalPages = ceil($totalRows / $maxRows) - 1;

$siteSettings = new sfSettings('../config/config.ini');
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
?>
<?php
//The upload Image directory
$sfGalleryImgUrl = $siteSettings->m_sfGalleryImgUrl;
$sfGalleryThumbUrl = $siteSettings->m_sfGalleryThumbUrl;
$imageMissing = $sfGalleryThumbUrl . "missing_default.png";
?>
<?php
$url = $otherurl = sfUtils::curPageURL();
$SnowflakesUrl = $siteSettings->m_sfUrl;

$Powerlink = $siteSettings->m_sfUrl . "resources/images/Snowflakes2.png";
$rsslink = $siteSettings->m_sfUrl . "resources/images/Icons/Rss.png";
$Shareurl = $siteSettings->m_sfUrl . "Gallery/OneView.php";

if (strlen($siteSettings->m_galleryResultUrl) > 0)
{ /// if user provides result page in snowflakes settings
    $Shareurl = $siteSettings->m_galleryResultUrl;
}

if (isset($siteSettings->m_galleryOutUrl))
{
    $currentPage = $siteSettings->m_galleryOutUrl;
}

$sflogo = "transparent";
$rssflogo = filter_input(INPUT_GET, 'sflogo');
if (isset($rssflogo))
{
    $sflogo = "#" . $rssflogo;
}
?>
<script type="text/javascript">
    var flakeitUrl = "<?php echo $siteSettings->m_flakeItUrl; ?>";
</script>
<script type="text/javascript" src="<?php echo $siteSettings->m_sfUrl; ?>resources/Js/flakeit.js"></script>

<div style="float: right; background-color:<?php echo $sflogo; ?>; z-index:1000;"><a href="http://cyrilinc.co.uk/snowflakes/" target="_blank"><img src="<?php echo $Powerlink; ?>" width="120" height="40" alt="snonwflakes" /></a> </div>
<div style="float: right; background-color:<?php echo $sflogo; ?>; z-index:1000;" class="NewButton"><a href="<?php echo $siteSettings->m_sfUrl; ?>rss.php?ty=gallery" title="Snowflakes gallery rss"> <img src="<?php echo $rsslink; ?>" height="22" width="22"  alt="Add" /></a></div>
<div class="clear"></div>
<!--wrapper-->
<div class="wrapper"> 
    <?php
    if ($pageNum > 0)
    {
// Show if not first page     
        ?>
        <div class="smallNewButton"><a href="<?php printf("%s?pageNum=%d%s", $currentPage, 0, $queryString); ?>">First</a></div>
        <div class="smallNewButton"><a href="<?php printf("%s?pageNum=%d%s", $currentPage, max(0, $pageNum - 1), $queryString); ?>">Previous</a></div>
        <?php
    }
// Show if not first page    
    ?>
    <?php
    if ($pageNum < $totalPages)
    {
// Show if not last page   
        ?>
        <div class="smallNewButton"><a href="<?php printf("%s?pageNum=%d%s", $currentPage, min($totalPages, $pageNum + 1), $queryString); ?>">Next</a></div>
        <div class="smallNewButton"><a href="<?php printf("%s?pageNum=%d%s", $currentPage, $totalPages, $queryString); ?>">Last</a></div>
        <?php
    }
// Show if not last page     
    ?>
    <div class=" clear Break2"></div>


    <!--topbar-->
    <div class="topbar"> <span id="close" class="back">&larr;</span>
        <div class="galleryName" id="name"></div>
    </div>
    <!--topbar End--> 

    <!--tp-grid-->
    <ul id="tp-grid" class="tp-grid">
        <?php
        if ($totalRows > 0)
        {
            $i = 0;
            ?>
            <?php
            do
            {
                // Get all the image name from database
                $DBImageFiles = explode(",", $galleryStructList[$i]->m_image_name);
                $DBImageThumbFiles = explode(",", $galleryStructList[$i]->m_thumb_name);
                $DBImageCaption = explode(",", $galleryStructList[$i]->m_image_caption);

                // Loop through the array and add directory prefix to each item in array
                foreach ($DBImageFiles as &$value)
                {
                    $value = $sfGalleryImgUrl . $value;
                }

                // Loop through the array and add directory prefix to each item in array	
                foreach ($DBImageThumbFiles as &$value)
                {
                    $value = $sfGalleryThumbUrl . $value;
                }

                //DataList
                foreach ($DBImageThumbFiles as $counter => $imageThumbLink)
                {
                    ?>
                    <li data-pile="<?php
                        echo htmlentities($galleryStructList[$i]->m_title . " "
                                . '<br/><div class="owner"> By -' . $galleryStructList[$i]->m_created_by . '</div> ');
                        ?>"> 
                        <a class="colorbox" href="<?php echo $DBImageFiles[$counter]; ?>" onerror="this.href='<?php echo $sfGalleryImgUrl . "missing_default.png"; ?>'" title="<?php echo htmlentities($DBImageCaption[$counter]); ?>"> 
                            <span class="tp-info"><span><?php echo htmlentities($DBImageCaption[$counter]); ?></span></span> 
                            <img src="<?php echo $imageThumbLink; ?>" onerror="this.src='<?php echo $imageMissing; ?>'" alt="<?php echo htmlentities($DBImageCaption[$counter]); ?>"> 
                        </a>
                    </li>
                <?php } ?>
                <?php
                $i++;
            } while ($i < count($galleryStructList));
            ?>
        <?php
        }
        else
        {
            ?>
            <!-- Snowflakes -->
            <li data-pile="Snowflakes : No images yet"> <a class="colorbox" href="<?php echo $SnowflakesUrl . 'Uploads/GalleryImages/Snowflakes.png'; ?>" > <span class="tp-info"><span>No Images in Gallery</span></span> <img src="<?php echo $SnowflakesUrl . 'Uploads/GalleryThumbs/Snowflakes.png'; ?>"  alt="Snowflakes"> </a> </li>
            <!-- Snowflakes -->
            <li data-pile="Snowflakes : No images yet"> <a class="colorbox" href="<?php echo $SnowflakesUrl . 'Uploads/GalleryImages/Snowflakes.png'; ?>" > <span class="tp-info"><span>No Images in Gallery</span></span> <img src="<?php echo $SnowflakesUrl . 'Uploads/GalleryThumbs/Snowflakes.png'; ?>"  alt="Snowflakes"> </a> </li>
            <!-- Snowflakes -->
            <li data-pile="Snowflakes : No images yet"> <a class="ViewThumb flakeit" title="flake it" data-type="gallery"> <img src="<?php echo $SnowflakesUrl . "resources/images/Icons/Snowflakes.png"; ?>" height="22" width="22" alt="View" /> </a> <a class="colorbox" href="<?php echo $SnowflakesUrl . 'Uploads/GalleryImages/Snowflakes.png'; ?>" > <span class="tp-info"><span>No Images in Gallery</span></span> <img src="<?php echo $SnowflakesUrl . 'Uploads/GalleryThumbs/Snowflakes.png'; ?>"  alt="Snowflakes"> </a> </li>
<?php } ?>
    </ul>
    <!--/tp-grid--> 
</div>
<!--/wrapper-->
<?php
$SFconnects->close();
?>
