<?php
require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';
require_once '../lib/sfSettings.php';
require_once '../lib/sfImageProcessor.php';
?>

<?php
//The upload directory
$siteSettings = new sfSettings('../config/config.ini');
//The upload Image directory
$sfGalleryImgUrl = $siteSettings->m_sfGalleryImgUrl;
$sfGalleryThumbUrl = $siteSettings->m_sfGalleryThumbUrl;
$imageMissing = $sfGalleryThumbUrl . "missing_default.png";
?>

<?php
$maxRows_rsSFGallery = filter_input(INPUT_GET, 'MaxNumber');
$pageNum_rsSFGallery = 0;
$GalleryRs = filter_input(INPUT_GET, 'pageNum_rsSFGallery');
if (isset($GalleryRs))
{
    $pageNum_rsSFGallery = $GalleryRs;
}
$startRow_rsSFGallery = $pageNum_rsSFGallery * $maxRows_rsSFGallery;

$config = new databaseParam('../config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$SFconnects->connect(); // Connect to database

$query_rsSFGallery = "SELECT * FROM snowflakes_gallery ORDER BY id DESC";
$query_limit_rsSFGallery = sprintf("%s LIMIT %d, %d", $query_rsSFGallery, $startRow_rsSFGallery, $maxRows_rsSFGallery);
$SFconnects->fetch($query_limit_rsSFGallery);
$row_rsSFGallery = $SFconnects->getResultArray();

$galleryStructList = array();
foreach ($row_rsSFGallery as $key => $value)
{
    $galleryStructList[$key] = new galleryStruct();
    $galleryStructList[$key]->populate($value);
}

$total_GalleryRs = filter_input(INPUT_GET, 'totalRows_rsSFGallery');
if (isset($total_GalleryRs))
{
    $totalRows_rsSFGallery = $total_GalleryRs;
}
else
{
    $SFconnects->fetch("SELECT COUNT(id) count FROM snowflakes_gallery ORDER BY id DESC");
    $result = $SFconnects->getResultArray();
    $totalRows_rsSFGallery = $result[0]['count'];
}
$totalPages_rsSFGallery = ceil($totalRows_rsSFGallery / $maxRows_rsSFGallery) - 1;

$SnowflakesUrl = $siteSettings->m_sfUrl;
?>

<!-- PageWrap -->
<div class="PageWrap">
    <?php
    if ($maxRows_rsSFGallery != Null)
    {
        ?>   
        <!--wrapper-->
        <div class="wrapper"> 

            <!--topbar-->
            <div class="topbar"> <span id="close" class="back">&larr;</span>
                <h3 id="name"></h3>
            </div>
            <!--topbar End--> 

            <!--tp-grid-->
            <ul id="tp-grid" class="tp-grid">
                <?php
                if ($totalRows_rsSFGallery > 0)
                {
                    $i = 0;
                    ?>

                    <?php
                    do
                    {
                        ?>
                        <?php
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
                            <li> 
                                <span class="tp-title" ><?php echo htmlentities($DBImageCaption[$counter]); ?></span>
                                <a class="colorbox" href="<?php echo $DBImageFiles[$counter]; ?>" onerror="this.href='<?php echo $sfGalleryImgUrl . "missing_default.png"; ?>'"  > 
                                    <span class="tp-info"><span><?php echo htmlentities($DBImageCaption[$counter]); ?></span></span> 
                                    <img src="<?php echo $imageThumbLink; ?>" onerror="this.src='<?php echo $imageMissing; ?>'" alt="<?php echo htmlentities($DBImageCaption [$counter]); ?>"> 
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
                    <li data-pile="Snowflakes : No images yet"> <a class="colorbox" href="<?php echo $SnowflakesUrl . 'Uploads/GalleryImages/Snowflakes.png'; ?>" > <span class="tp-info"><span> No images yet</span></span> <img src="<?php echo $SnowflakesUrl . 'Uploads/GalleryThumbs/Snowflakes.png'; ?>"  alt="Snowflakes"> </a> </li>
                    <li data-pile="Snowflakes : No images yet"> <a class="colorbox" href="<?php echo $SnowflakesUrl . 'Uploads/GalleryImages/Snowflakes.png'; ?>" > <span class="tp-info"><span> No images yet</span></span> <img src="<?php echo $SnowflakesUrl . 'Uploads/GalleryThumbs/Snowflakes.png'; ?>"  alt="Snowflakes"> </a> </li>
                    <li data-pile="Snowflakes : No images yet"> <a class="colorbox" href="<?php echo $SnowflakesUrl . 'Uploads/GalleryImages/Snowflakes.png'; ?>" > <span class="tp-info"><span> No images yet</span></span> <img src="<?php echo $SnowflakesUrl . 'Uploads/GalleryThumbs/Snowflakes.png'; ?>"  alt="Snowflakes"> </a> </li>
    <?php } ?>     

            </ul>
            <!--/tp-grid--> 
        </div>
        <!--/wrapper-->
        <?php
    }
    else
    {
        ?>
        <h4 class="SummaryHead">No Max number Snowflakes Gallery indicated </h4>

<?php }
?>            
</div>
<!--/PageWrap --> 
<?php
$SFconnects->close();
?>