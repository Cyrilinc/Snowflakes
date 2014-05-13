<?php
require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';
require_once '../lib/sfImageProcessor.php';
?>
<?php
//The upload directory
$settingsConfig = Config::getConfig("settings", '../config/config.ini');
$UploadDir = $settingsConfig['uploadGalleryDir'];
//The upload Image directory
$UploadImgUrl = $settingsConfig['m_sfGalleryImgUrl'];
$UploadThumbUrl = $settingsConfig['m_sfGalleryThumbUrl'];
$imageMissing = $UploadThumbUrl . "missing_default.png";
?>
<?php
$colname_rsSFGallery = -1;
$Galleryid = filter_input(INPUT_GET, 'Galleryid');
if (isset($Galleryid)) {
    $colname_rsSFGallery = $Galleryid;
}

$config = new settingDBParam('../config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$SFconnects->connect(); // Connect to database

$galleryStruct = new galleryStruct();
$galleryStruct->getGalleryByid($SFconnects, $colname_rsSFGallery);
$totalRows_rsSFGallery = $SFconnects->recordCount();

$query_SiteSettings = "SELECT sf_url, result_url, out_url, events_result_url, events_output_url, gallery_result_url, gallery_out_url FROM snowflakes_settings";
$SFconnects->fetch($query_SiteSettings);
$result = $SFconnects->getResultArray();
$row_SiteSettings = $result[0];
?>
<?php
$url = $otherurl = sfUtils::curPageURL();
$SnowflakesUrl = $row_SiteSettings['sf_url'];
if (isset($row_SiteSettings['gallery_result_url'])) {
    $SFGalleryResultUrl = $row_SiteSettings['gallery_result_url'];
    $url = $otherurl = $SFGalleryResultUrl . "&amp;Galleryid=" . $Galleryid;
} else {
    $SFGalleryResultUrl = 'notset';
}

$Powerlink = $SnowflakesUrl . "resources/images/Snowflakes2.png";
?>
<script type="text/javascript">
    var flakeitUrl = "<?php echo $settingsConfig['flakeItUrl']; ?>";
</script>
<script type="text/javascript" src="<?php echo $settingsConfig['m_sfUrl']; ?>resources/Js/flakeit.js"></script>
<!-- PageWrap -->
<div class="PageWrap">

    <?php if ($Galleryid != Null) { ?>      
        <!--wrapper-->
        <div class="wrapper"> 

            <!--topbar-->
            <div class="topbar"> <span id="close" class="back">&larr;</span>
                <div class="galleryName" id="name"><?php echo $galleryStruct->m_title; ?>  
                    <br/>
                    <div class="owner"> Created |: <?php echo date(" F j, Y", $galleryStruct->m_created); ?>  | By - <?php echo $galleryStruct->m_created_by; ?> </div>
                </div>
            </div>
            <!--topbar End--> 

            <!--tp-grid-->
            <ul id="tp-grid" class="tp-grid">
                <?php if ($totalRows_rsSFGallery > 0) { ?>

                    <?php
                    // Get all the image name from database
                    $DBImageFiles = explode(",", $galleryStruct->m_image_name);
                    $DBImageThumbFiles = explode(",", $galleryStruct->m_thumb_name);
                    $DBImageCaption = explode(",", $galleryStruct->m_image_caption);
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

                        <li> <span class="tp-title" ><?php echo htmlentities($DBImageCaption [$counter]); ?></span>
                            <a class="colorbox" href="<?php echo $DBImageFiles[$counter]; ?>" onerror="this.href='<?php echo $UploadImgUrl . "missing_default.png"; ?>'"> 
                                <span class="tp-info"><span><?php echo htmlentities($DBImageCaption[$counter]); ?></span></span> 
                                <img src="<?php echo $imageThumbLink; ?>" onerror="this.src='<?php echo $imageMissing; ?>'"  alt="<?php echo htmlentities($DBImageCaption[$counter]); ?>"> 
                            </a>
                        </li>

                    <?php } ?>



                <?php } else { ?> 

                    <!-- Snowflakes -->
                    <li data-pile="Snowflakes :Gallery doesn't exist"> <a class="colorbox" href="../Uploads/GalleryImages/Snowflakes.png" > <span class="tp-info"><span>Gallery doesn't exist</span></span> <img src="../Uploads/GalleryThumbs/Snowflakes.png"  alt="Snowflakes"> </a> </li>
                    <li data-pile="Snowflakes :Gallery doesn't exist"> <a class="colorbox" href="../Uploads/GalleryImages/Snowflakes.png" > <span class="tp-info"><span>Gallery doesn't exist</span></span> <img src="../Uploads/GalleryThumbs/Snowflakes.png"  alt="Snowflakes"> </a> </li>
                    <li data-pile="Snowflakes :Gallery doesn't exist"> <a class="colorbox" href="../Uploads/GalleryImages/Snowflakes.png" > <span class="tp-info"><span>Gallery doesn't exist</span></span> <img src="../Uploads/GalleryThumbs/Snowflakes.png"  alt="Snowflakes"> </a> </li>
                        <?php } ?>     

            </ul>
            <!--tp-grid Ends--> 
        </div>
        <!--wrapper Ends--> 
    <?php } else { ?>
        <h4>No Gallery to view </h4>
    <?php } ?>

</div>
<!-- End of PageWrap --> 
<?php
$SFconnects->close();
?>