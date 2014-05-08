<?php
require_once 'lib/sf.php';
require_once 'lib/sfConnect.php';
require_once 'config/Config.php';
?>
<?php
$colname_rsOut = -1;
$pageid = filter_input(INPUT_GET, 'pageid');
if (isset($pageid)) {
    $colname_rsOut = $pageid;
}
$config = Config::getConfig("db", 'config/config.ini');
$sqlArray = array('type' => $config['type'], 'host' => $config['host'], 'username' => $config['username'], 'password' => sfUtils::decrypt($config['password'], $config['key']), 'database' => $config['dbname']);
$SFconnects = new sfConnect($sqlArray);
$SFconnects->connect(); // Connect to database

$flakeStruct = new snowflakeStruct();
$flakeStruct->getSnowflakesByid($SFconnects, $colname_rsOut);
$totalRows_rsOut = $SFconnects->recordCount();

$query_SiteSettings = "SELECT sf_url, result_url, out_url, events_result_url, events_output_url, gallery_result_url, gallery_out_url FROM snowflakes_settings";
$SFconnects->fetch($query_SiteSettings);
$result= $SFconnects->getResultArray();
$row_SiteSettings = $result[0];
?>

<?php
$url = $otherurl = sfUtils::curPageURL();
$SnowflakesUrl = $row_SiteSettings['sf_url'];
$settingsConfig = Config::getConfig("settings", 'config/config.ini');
if (isset($row_SiteSettings['result_url'])) {
    $SnowflakesResultUrl = $row_SiteSettings['result_url'];
    $url = $otherurl = $SnowflakesResultUrl . "&amp;pageid=" . $pageid;
} else {
    $SnowflakesResultUrl = 'notset';
}
$Powerlink = $settingsConfig['m_sfUrl'] . "resources/images/Snowflakes2.png";

$UploadDir = $settingsConfig['m_sfGalleryUrl'];
//The upload Image directory
$UploadImgUrl = $settingsConfig['m_sfGalleryImgUrl'];
$UploadThumbUrl = $settingsConfig['m_sfGalleryThumbUrl'];
?>
<!-- PageWrap -->
<div class="PageWrap">
    <script type="text/javascript">
        var flakeitUrl = "<?php echo $settingsConfig['flakeItUrl']; ?>";
    </script>
    <script type="text/javascript" src="<?php echo $settingsConfig['m_sfUrl']; ?>resources/Js/flakeit.js"></script>

    <div style="float: right; background-color:transparent;"><a href="http://cyrilinc.co.uk/snowflakes/" target="_blank"><img src="<?php echo $Powerlink; ?>" width="120" height="40" alt="Powered by Snowflakes" /></a> </div>

    <?php if ($pageid != Null) { ?>
        <!--Snowflake-->
        <div class="Snowflake">
            <div class="SnowflakeHead"><?php echo $flakeStruct->m_title; ?> </div>

            <!--SnowflakePanel-->
            <div class="SnowflakePanel"> <span>Share </span> 
                <a href="http://twitter.com/home?status=<?php echo htmlentities(rawurlencode($flakeStruct->m_title)); ?>%20<? echo "" . $url; ?>" title="Twitter" target="_blank"> <img src="<?php echo $SnowflakesUrl . 'resources/images/Icons/Twitter.png'; ?>" height="22" width="22" alt="Twitter" /> </a> 
                <a href="http://www.facebook.com/sharer.php?u=<? echo "" . $url; ?>" title="Facebook" target="_blank"> <img src="<?php echo $SnowflakesUrl . 'resources/images/Icons/Facebook.png'; ?>" height="22" width="22" alt="Facebook" /> </a> 
                <a href="https://plus.google.com/share?url=<? echo "" . $url; ?>&amp;title=<?php echo htmlentities(rawurlencode($flakeStruct->m_title)); ?>" title="GooglePlus" target="_blank"> <img src="<?php echo $SnowflakesUrl . 'resources/images/Icons/GooglePlus.png'; ?>" height="22" width="22" alt="GooglePlus" /> </a> 
                <a href="http://digg.com/submit?phase=2&amp;url=<? echo "" . $url; ?>&amp;title=<?php echo htmlentities(rawurlencode($flakeStruct->m_title)); ?>" title="Digg" target="_blank"> <img src="<?php echo $SnowflakesUrl . 'resources/images/Icons/Digg.png'; ?>" height="22" width="22" alt="Digg" /> </a> 
                <a href="http://stumbleupon.com/submit?url=<? echo "" . $url; ?>&amp;title=<?php echo htmlentities(rawurlencode($flakeStruct->m_title)); ?>" title="stumbleupon" target="_blank"> <img src="<?php echo $SnowflakesUrl . 'resources/images/Icons/Stumbleupon.png'; ?>" height="22" width="22" alt="stumbleupon" /> </a> 
                <a href="http://del.icio.us/post?url=<? echo "" . $url; ?>&amp;title=<?php echo htmlentities(rawurlencode($flakeStruct->m_title)); ?>" title="delicious" target="_blank"> <img src="<?php echo $SnowflakesUrl . 'resources/images/Icons/delicious.png'; ?>" height="22" width="22" alt="delicious" /> </a> 
                <a class="flakeit" id="flakeit<?php echo $row_rsOut[$i]['id']; ?>" title="flake it" data-type="snowflake"> <span>Flake it</span> <img src="<?php echo $SnowflakesUrl . "resources/images/Icons/Snowflakes.png"; ?>" height="22" width="22" alt="flake it" /> </a> 
            </div>
            <!--End of SnowflakePanel-->

            <div class="PageBreak"></div>
            <div class="clear"></div>
            <?php if ($totalRows_rsOut > 0) { ?>

                <!--SnowflakeDescr-->
                <div class="SnowflakeDescr">

                    <?php
                    if ($flakeStruct->m_gallery == NULL) {
                        $imageMissing = $UploadImgUrl . "missing_default.png";
                        ?> 
                        <div class="SnowflakeImage">
                            <a class="colorbox" href="<?php echo $UploadDir . $flakeStruct->m_image_name; ?>"  onerror="this.href='<?php echo $imageMissing; ?>'"  title="<?php echo $flakeStruct->m_title; ?>" >
                                <img src="<?php echo $UploadDir . $flakeStruct->m_image_name; ?>" onerror="this.src='<?php echo $imageMissing; ?>'"  alt="<?php echo $flakeStruct->m_image_name; ?>" />
                            </a>
                        </div>

                        <?php
                    }
                    ?>

                    <?php echo html_entity_decode($flakeStruct->m_body_text); ?> 

                    <!--Place Gallery Here if its not null-->
                    <?php
                    if ($flakeStruct->m_gallery != NULL) {
                        $imageMissing = $UploadThumbUrl . "missing_default.png";
                        $GalleryName = explode(",", $flakeStruct->m_gallery);

                        $query_rsGallery = "SELECT * FROM snowflakes_gallery WHERE id=" . $GalleryName[0] . " AND title ='" . sfUtils::escape($GalleryName[1]) . "'";
                        $SFconnects->fetch($query_rsGallery);
                        $row_rsGallery = $SFconnects->getResultArray();
                        $totalRows_rsGallery = $SFconnects->recordCount();


                        if ($totalRows_rsGallery > 0) {
                            ?>  

                            <!--wrapper-->
                            <div class="wrapper clearfix"> 
                                <h4> <?php echo $row_rsGallery[0]['title']; ?></h4> 
                                <!--tp-grid-->
                                <ul id="tp-grid" class="tp-grid">
                                    <?php
                                    // Get all the image name from database
                                    $DBImageFiles = explode(",", $row_rsGallery[0]['image_name']);
                                    $DBImageThumbFiles = explode(",", $row_rsGallery[0]['thumb_name']);
                                    $DBImageCaption = explode(",", $row_rsGallery[0]['image_caption']);

                                    // Loop through the array and add directory prefix to each item in array
                                    foreach ($DBImageFiles as &$value) {
                                        $value = $UploadImgUrl . $value;
                                    }
                                    // Loop through the array and add directory prefix to each item in array	
                                    foreach ($DBImageThumbFiles as &$value) {
                                        $value = $$UploadThumbUrl . $value;
                                    }
                                    //DataList
                                    $counter = 0;
                                    foreach ($DBImageThumbFiles as $imageThumbLink) {
                                        ?>
                                        <li> <span class="tp-title" ><?php echo $DBImageCaption[$counter]; ?></span> 
                                            <a class="colorbox" href="<?php echo $DBImageFiles[$counter]; ?>"  onerror="this.href='<?php echo $imageMissing; ?>'" > <span class="tp-info"><span><?php echo $DBImageCaption[$counter]; ?></span></span> 
                                                <img src="<?php echo $imageThumbLink; ?>"  onerror="this.src='<?php echo $imageMissing; ?>'"  alt="<?php echo $DBImageCaption[$counter]; ?>"> 
                                            </a>
                                        </li>
                                        <?php
                                        $counter++;
                                    }
                                    ?>
                                </ul>
                                <!--tp-grid Ends--> 
                            </div>
                            <!--wrapper Ends--> 
                            <?php
                        }
                    }
                    ?>
                </div><!--SnowflakeDescr Ends-->
                <div class="clear"></div>
                <div class="PageBreak"></div>
                <div class="SnowflakeDate"> Published |: <?php echo date(" F j, Y", $flakeStruct->m_created); ?>  | By - <?php echo $flakeStruct->m_createdby; ?></div>
                <div class="SnowflakeIt"> flakes <div class="flakeitParam" id="flakecount<?php echo $flakeStruct->m_id; ?>"> <?php echo $flakeStruct->m_flake_it; ?> </div></div>
                <div class="SharePost"> </div>
            </div>
        <?php } else { ?> 
            <h4>This snowflake doesn't exist </h4>
        <?php } ?> 
        <!-- End of Snowflake -->
    <?php } else {
        ?>
        <h4>No Snowflake to view </h4>
    <?php }
    ?>
</div>
<!-- End of PageWrap --> 
<?php
$SFconnects->close();
?>