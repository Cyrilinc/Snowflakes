<?php
require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';
require_once '../lib/sfImageProcessor.php';
?>

<?php
$currentPage = filter_input(INPUT_SERVER, 'PHP_SELF');

$maxRows_rsSFGallery = 6;
$pageNum_rsSFGallery = 0;
$pageSFGallery = filter_input(INPUT_GET, 'pageNum_rsSFGallery');
if (isset($pageSFGallery)) {
    $pageNum_rsSFGallery = $pageSFGallery;
}
$startRow_rsSFGallery = $pageNum_rsSFGallery * $maxRows_rsSFGallery;

$config = new databaseParam('../config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$SFconnects->connect(); // Connect to database

$query_rsSFGallery = "SELECT * FROM snowflakes_gallery WHERE publish=1 ORDER BY id DESC";
$query_limit_rsSFGallery = sprintf("%s LIMIT %d, %d", $query_rsSFGallery, $startRow_rsSFGallery, $maxRows_rsSFGallery);
$SFconnects->fetch($query_limit_rsSFGallery);
$row_rsSFGallery = $SFconnects->getResultArray();

$galleryStructList = array();
foreach ($row_rsSFGallery as $key => $value) {
    $galleryStructList[$key] = new galleryStruct();
    $galleryStructList[$key]->populate($value);
}


$totalSFGallery = filter_input(INPUT_GET, 'totalRows_rsSFGallery');
if (isset($totalSFGallery)) {
    $totalRows_rsSFGallery = $totalSFGallery;
} else {
    $SFconnects->fetch("SELECT COUNT(id) count FROM snowflakes_gallery WHERE publish=1 ORDER BY id DESC");
    $result = $SFconnects->getResultArray();
    $totalRows_rsSFGallery = $result[0]['count'];
}
$totalPages_rsSFGallery = ceil($totalRows_rsSFGallery / $maxRows_rsSFGallery) - 1;

$query_SiteSettings = "SELECT sf_url, result_url, out_url, events_result_url, events_output_url, gallery_result_url, gallery_out_url FROM snowflakes_settings";
$SFconnects->fetch($query_SiteSettings);
$result2 = $SFconnects->getResultArray();
$row_SiteSettings = $result2[0];

$queryString_rsSFGallery = "";
$query_string = filter_input(INPUT_SERVER, 'QUERY_STRING');
if (!empty($query_string)) {
    $params = explode("&", $query_string);
    $newParams = array();
    foreach ($params as $param) {
        if (stristr($param, "pageNum_rsSFGallery") == false &&
                stristr($param, "totalRows_rsSFGallery") == false) {
            array_push($newParams, $param);
        }
    }
    if (count($newParams) != 0) {
        $queryString_rsSFGallery = "&amp;" . htmlentities(implode("&", $newParams));
    }
}
$queryString_rsSFGallery = sprintf("&amp;totalRows_rsSFGallery=%d%s", $totalRows_rsSFGallery, $queryString_rsSFGallery);
?>

<?php
//The upload directory
$settingsConfig = Config::getConfig("settings", '../config/config.ini');
$datadir=new dataDirParam("../config/config.ini");
$UploadDir = $datadir->m_uploadGalleryDir;
//The upload Image directory
$UploadImgUrl = $settingsConfig['m_sfGalleryImgUrl'];
$UploadThumbDir = $datadir->m_galleryThumbDir;
$UploadThumbUrl = $settingsConfig['m_sfGalleryThumbUrl'];
$imageMissing = $UploadThumbUrl . "missing_default.png";
?>
<?php
$url = $otherurl = sfUtils::curPageURL();
$SnowflakesUrl = $row_SiteSettings['sf_url'];

$SFGalleryResultUrl = $row_SiteSettings['gallery_result_url'];
$Powerlink = $row_SiteSettings['sf_url'] . "resources/images/Snowflakes2.png";
$rsslink = $row_SiteSettings['sf_url'] . "resources/images/Icons/Rss.png";
$Shareurl = $row_SiteSettings['sf_url'] . "Gallery/OneView.php";

if (strlen($SFGalleryResultUrl) > 0) { /// if user provides result page in snowflakes settings
    $Shareurl = $SFGalleryResultUrl;
}
$sflogo = "transparent";
$rssflogo = filter_input(INPUT_GET, 'sflogo');
if (isset($rssflogo)) {
    $sflogo = "#" . $rssflogo;
}
?>
<script type="text/javascript">
    var flakeitUrl = "<?php echo $settingsConfig['flakeItUrl']; ?>";
</script>
<script type="text/javascript" src="<?php echo $settingsConfig['m_sfUrl']; ?>resources/Js/flakeit.js"></script>

<div style="float: right; background-color:<?php echo $sflogo; ?>; z-index:1000;"><a href="http://cyrilinc.co.uk/snowflakes/" target="_blank"><img src="<?php echo $Powerlink; ?>" width="120" height="40" alt="snonwflakes" /></a> </div>
<div style="float: right; background-color:<?php echo $sflogo; ?>; z-index:1000;" class="NewButton"><a href="<?php echo $row_SiteSettings['sf_url']; ?>rss.php?ty=gallery" title="Snowflakes gallery rss"> <img src="<?php echo $rsslink; ?>" height="22" width="22"  alt="Add" /></a></div>
<div class="clear"></div>
<!--wrapper-->
<div class="wrapper"> 
    <?php if ($pageNum_rsSFGallery > 0) { // Show if not first page    ?>
        <div class="smallNewButton"><a href="<?php printf("%s?pageNum_rsSFGallery=%d%s", $currentPage, 0, $queryString_rsSFGallery); ?>">First</a></div>
        <div class="smallNewButton"><a href="<?php printf("%s?pageNum_rsSFGallery=%d%s", $currentPage, max(0, $pageNum_rsSFGallery - 1), $queryString_rsSFGallery); ?>">Previous</a></div>
    <?php } // Show if not first page   ?>
    <?php if ($pageNum_rsSFGallery < $totalPages_rsSFGallery) { // Show if not last page  ?>
        <div class="smallNewButton"><a href="<?php printf("%s?pageNum_rsSFGallery=%d%s", $currentPage, min($totalPages_rsSFGallery, $pageNum_rsSFGallery + 1), $queryString_rsSFGallery); ?>">Next</a></div>
        <div class="smallNewButton"><a href="<?php printf("%s?pageNum_rsSFGallery=%d%s", $currentPage, $totalPages_rsSFGallery, $queryString_rsSFGallery); ?>">Last</a></div>
    <?php } // Show if not last page    ?>
    <div class=" clear Break2"></div>


    <!--topbar-->
    <div class="topbar"> <span id="close" class="back">&larr;</span>
        <div class="galleryName" id="name"></div>
    </div>
    <!--topbar End--> 

    <!--tp-grid-->
    <ul id="tp-grid" class="tp-grid">
        <?php
        if ($totalRows_rsSFGallery > 0) {
            $i = 0;
            ?>
            <?php
            do {
                // Get all the image name from database
                $DBImageFiles = explode(",", $galleryStructList[$i]->m_image_name);
                $DBImageThumbFiles = explode(",", $galleryStructList[$i]->m_thumb_name);
                $DBImageCaption = explode(",", $galleryStructList[$i]->m_image_caption);

                // Loop through the array and add directory prefix to each item in array
                foreach ($DBImageFiles as &$value) {
                    $value = $UploadImgUrl . $value;
                }

                // Loop through the array and add directory prefix to each item in array	
                foreach ($DBImageThumbFiles as &$value) {
                    $value = $UploadThumbUrl . $value;
                }

                //DataList
                foreach ($DBImageThumbFiles as $counter => $imageThumbLink) {
                    ?>
                    <li data-pile="<?php
                    echo htmlentities($galleryStructList[$i]->m_title . " "
                            . '<br/><div class="owner"> By -' . $galleryStructList[$i]->m_created_by . '</div> ');
                    ?>"> 
                        <a class="colorbox" href="<?php echo $DBImageFiles[$counter]; ?>" onerror="this.href='<?php echo $UploadImgUrl . "missing_default.png"; ?>'" title="<?php echo htmlentities($DBImageCaption[$counter]); ?>"> 
                            <span class="tp-info"><span><?php echo htmlentities($DBImageCaption[$counter]); ?></span></span> 
                            <img src="<?php echo $imageThumbLink; ?>" onerror="this.src='<?php echo $imageMissing; ?>'" alt="<?php echo htmlentities($DBImageCaption[$counter]); ?>"> 
                        </a>
                    </li>
                <?php } ?>
                <?php
                $i++;
            } while ($i < count($galleryStructList));
            ?>
        <?php } else { ?>
            <!-- Snowflakes -->
            <li data-pile="Snowflakes : No images yet"> <a class="colorbox" href="<?php echo $SnowflakesUrl . 'Uploads/GalleryImages/Snowflakes.png'; ?>" > <span class="tp-info"><span>No Images in Gallery</span></span> <img src="<?php echo $SnowflakesUrl . 'Uploads/GalleryThumbs/Snowflakes.png'; ?>"  alt="Snowflakes"> </a> </li>
            <!-- Snowflakes -->
            <li data-pile="Snowflakes : No images yet"> <a class="colorbox" href="<?php echo $SnowflakesUrl . 'Uploads/GalleryImages/Snowflakes.png'; ?>" > <span class="tp-info"><span>No Images in Gallery</span></span> <img src="<?php echo $SnowflakesUrl . 'Uploads/GalleryThumbs/Snowflakes.png'; ?>"  alt="Snowflakes"> </a> </li>
            <!-- Snowflakes -->
            <li data-pile="Snowflakes : No images yet"> <a class="ViewThumb flakeit" title="flake it" data-type="gallery"> <img src="<?php echo $SnowflakesUrl . "resources/images/Icons/Snowflakes.png"; ?>" height="22" width="22" alt="View" /> </a> <a class="colorbox" href="<?php echo $SnowflakesUrl . 'Uploads/GalleryImages/Snowflakes.png'; ?>" > <span class="tp-info"><span>No Images in Gallery</span></span> <img src="<?php echo $SnowflakesUrl . 'Uploads/GalleryThumbs/Snowflakes.png'; ?>"  alt="Snowflakes"> </a> </li>
                <?php } ?>
    </ul>
    <!--tp-grid Ends--> 
</div>
<!--wrapper Ends-->
<?php
$SFconnects->close();
?>
