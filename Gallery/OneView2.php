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
$datadir = new dataDirParam("../config/config.ini");
$UploadDir = $datadir->m_uploadGalleryDir;
//The upload Image directory
$UploadImgDir = $datadir->m_galleryImgDir;
$sfGalleryImgUrl = $siteSettings->m_sfGalleryImgUrl;
$UploadThumbDir = $datadir->m_galleryThumbDir;
$sfGalleryThumbUrl = $siteSettings->m_sfGalleryThumbUrl;
$imageMissing = $sfGalleryThumbUrl . "missing_default.png";
?>
<?php
$colname_rsSFGallery = -1;
$Galleryid = filter_input(INPUT_GET, 'Galleryid');
if (isset($Galleryid)) {
    $colname_rsSFGallery = $Galleryid;
}

$config = new databaseParam('../config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$SFconnects->connect(); // Connect to database

$galleryStruct = new galleryStruct();
$galleryStruct->getGalleryByid($SFconnects, $colname_rsSFGallery);
$totalRows_rsSFGallery = $SFconnects->recordCount();

?>
<?php
$url = $otherurl = sfUtils::curPageURL();
$SnowflakesUrl = $siteSettings->m_sfUrl;
if (isset($siteSettings->m_galleryResultUrl)) {
    $SFGalleryResultUrl = $siteSettings->m_galleryResultUrl;
    $url = $otherurl = $SFGalleryResultUrl . "&amp;Galleryid=" . $Galleryid;
} else {
    $SFGalleryResultUrl = 'notset';
}

$Powerlink = $SnowflakesUrl . "resources/images/Snowflakes2.png";
?>
<!-- PageWrap -->
<div class="PageWrap">

    <script type="text/javascript">
        var flakeitUrl = "<?php echo $siteSettings->m_flakeItUrl; ?>";
    </script>
    <script type="text/javascript" src="<?php echo $siteSettings->m_sfUrl; ?>resources/Js/flakeit.js"></script>

    <div style="float: right; background-color:transparent;"><a href="http://cyrilinc.co.uk/snowflakes/" target="_blank"><img src="<?php echo $Powerlink; ?>" width="120" height="40" alt="Powered by Snowflakes" /></a> </div>
<?php if ($Galleryid != Null) { ?>   
        <!--SnowflakePanel-->
        <div class="SnowflakePanel"> 
            <div style="color:#000; float:left">Share </div> 
            <a href="http://twitter.com/home?status=<?php echo $galleryStruct->m_title; ?>%20<? echo "" . $url; ?>" title="Twitter" target="_blank"> <img src="<?php echo $SnowflakesUrl . 'resources/images/Icons/Twitter.png'; ?>" height="22" width="22" alt="Twitter" /> </a> 
            <a href="http://www.facebook.com/sharer.php?u=<? echo "" . $url; ?>" title="Facebook" target="_blank"> <img src="<?php echo $SnowflakesUrl . 'resources/images/Icons/Facebook.png'; ?>" height="22" width="22" alt="Facebook" /> </a> 
            <a href="https://plus.google.com/share?url=<? echo "" . $url; ?>&amp;title=<?php echo $galleryStruct->m_title; ?>" title="GooglePlus" target="_blank"> <img src="<?php echo $SnowflakesUrl . 'resources/images/Icons/GooglePlus.png'; ?>" height="22" width="22" alt="GooglePlus" /> </a> 
            <a href="http://digg.com/submit?phase=2&url=<? echo "" . $url; ?>&amp;title=<?php echo $galleryStruct->m_title; ?>" title="Digg" target="_blank"> <img src="<?php echo $SnowflakesUrl . 'resources/images/Icons/Digg.png'; ?>" height="22" width="22" alt="Digg" /> </a> 
            <a href="http://stumbleupon.com/submit?url=<? echo "" . $url; ?>&amp;title=<?php echo $galleryStruct->m_title; ?>" title="stumbleupon" target="_blank"> <img src="<?php echo $SnowflakesUrl . 'resources/images/Icons/stumbleupon.png'; ?>" height="22" width="22" alt="stumbleupon" /> </a> 
            <a href="http://del.icio.us/post?url=<? echo "" . $url; ?>&amp;title=<?php echo $galleryStruct->m_title; ?>" title="delicious" target="_blank"> <img src="<?php echo $SnowflakesUrl . 'resources/images/Icons/delicious.png'; ?>" height="22" width="22" alt="delicious" /> </a> 
            <a class="flakeit" id="flakeit<?php echo $galleryStruct->m_id; ?>" title="flake it" data-type="gallery"> <span>Flake it</span> <img src="<?php echo $SnowflakesUrl . 'resources/images/Icons/Snowflakes.png'; ?>" height="22" width="22" alt="flake it" /> </a> 
        </div>
        <!--/SnowflakePanel-->


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
                        $value = $sfGalleryImgUrl . $value;
                    }

                    // Loop through the array and add directory prefix to each item in array	
                    foreach ($DBImageThumbFiles as &$value) {
                        $value = $sfGalleryThumbUrl . $value;
                    }

                    //DataList
                    foreach ($DBImageThumbFiles as $counter => $imageThumbLink) {
                        ?>

                        <li> 
                            <span class="tp-title" ><?php echo htmlentities($DBImageCaption [$counter]); ?></span>
                            <a class="colorbox" href="<?php echo $DBImageFiles[$counter]; ?>" onerror="this.href='<?php echo $sfGalleryImgUrl . "missing_default.png"; ?>'" title="<?php echo htmlentities($DBImageCaption[$counter]); ?>"> 
                                <span class="tp-info"><span><?php echo htmlentities($DBImageCaption[$counter]); ?></span></span> 
                                <img src="<?php echo $imageThumbLink; ?>" onerror="this.src='<?php echo $imageMissing; ?>'"  alt="<?php echo htmlentities($DBImageCaption [$counter]); ?>"> 
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
            <!--/tp-grid--> 
        </div>
        <!--/wrapper--> 


    <?php } else { ?>
        <h4>No Gallery to view </h4>
<?php } ?>

</div>
<!--/PageWrap --> 
<?php
$SFconnects->close();
?>