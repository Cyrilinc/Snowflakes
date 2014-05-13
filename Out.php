<?php
require_once 'lib/sf.php';
require_once 'lib/sfConnect.php';
require_once 'config/Config.php';
?>
<?php
$currentPage = filter_input(INPUT_SERVER, 'PHP_SELF');

$maxRows_rsOut = 5;
$pageNum_rsOut = 0;
$rsOut = filter_input(INPUT_GET, 'pageNum_rsOut');
if (isset($rsOut)) {
    $pageNum_rsOut = $rsOut;
}
$startRow_rsOut = $pageNum_rsOut * $maxRows_rsOut;

$config = new settingDBParam('config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$SFconnects->connect(); // Connect to database

$pageid = filter_input(INPUT_GET, 'pageid');
$flakeit = filter_input(INPUT_GET, 'flakeit');
if (isset($flakeit) && isset($pageid)) {
    sfUtils::flakeIt($SFconnects, $pageid, "snowflake");
}

$query_rsOut = "SELECT * FROM snowflakes WHERE publish = 1 ORDER BY created DESC";
$query_limit_rsOut = sprintf("%s LIMIT %d, %d", $query_rsOut, $startRow_rsOut, $maxRows_rsOut);
$SFconnects->fetch($query_limit_rsOut);
$row_rsOut = $SFconnects->getResultArray();
$flakeStructList = array();
foreach ($row_rsOut as $key => $value) {
    $flakeStructList[$key] = new snowflakeStruct();
    $flakeStructList[$key]->populate($value);
}

$total_rsOut = filter_input(INPUT_GET, 'totalRows_rsOut');
if (isset($total_rsOut)) {
    $totalRows_rsOut = $total_rsOut;
} else {
    $SFconnects->fetch("SELECT COUNT(id) count FROM snowflakes WHERE publish = 1 ORDER BY created DESC");
    $result = $SFconnects->getResultArray();
    $totalRows_rsOut = $result[0]['count'];
}
$totalPages_rsOut = ceil($totalRows_rsOut / $maxRows_rsOut) - 1;

$query_SiteSettings = "SELECT sf_url, result_url, out_url, events_result_url, events_output_url, gallery_result_url, gallery_out_url FROM snowflakes_settings";
$SFconnects->fetch($query_SiteSettings);
$result2 = $SFconnects->getResultArray();
$row_SiteSettings = $result2[0];

$queryString_rsOut = "";
$query_string = filter_input(INPUT_SERVER, 'QUERY_STRING');
if (!empty($query_string)) {
    $params = explode("&", $query_string);
    $newParams = array();
    foreach ($params as $param) {
        if (stristr($param, "pageNum_rsOut") == false &&
                stristr($param, "totalRows_rsOut") == false) {
            array_push($newParams, $param);
        }
    }
    if (count($newParams) != 0) {
        $queryString_rsOut = "&amp;" . htmlentities(implode("&", $newParams));
    }
}
$queryString_rsOut = sprintf("&amp;totalRows_rsOut=%d%s", $totalRows_rsOut, $queryString_rsOut);
?>
<?php
$url = $otherurl = sfUtils::curPageURL();
$SnowflakesUrl = $row_SiteSettings['sf_url'];
$SnowflakesResultUrl = $row_SiteSettings['result_url'];
$Powerlink = $row_SiteSettings['sf_url'] . "resources/images/Snowflakes2.png";
$Shareurl = $row_SiteSettings['sf_url'] . "OneView.php";
$rsslink = $row_SiteSettings['sf_url'] . "resources/images/Icons/Rss.png";

$settingsConfig = Config::getConfig("settings", 'config/config.ini');
$UploadImgUrl = $settingsConfig['m_sfGalleryUrl'];
$imageMissing = $UploadImgUrl . "missing_default.png";

if (strlen($SnowflakesResultUrl) > 0) {/// if user provides result page in snowflakes settings
    $Shareurl = $SnowflakesResultUrl;
}

if (isset($row_SiteSettings['out_url'])) {
    $currentPage = $row_SiteSettings['out_url'];
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
<div style="float: right; background-color:<?php echo $sflogo; ?>;"><a href="http://cyrilinc.co.uk/snowflakes/" target="_blank"><img src="<?php echo $Powerlink; ?>" width="120" height="40" alt="Powered by Snowflakes" /></a> </div>
<div style="float: right; background-color:<?php echo $sflogo; ?>;" class="NewButton"><a href="<?php echo $row_SiteSettings['sf_url']; ?>rss.php?ty=snowflakes" title="Snowflakes rss"> <img src="<?php echo $rsslink; ?>" height="22" width="22"  alt="Add" /></a></div>
<div class="clear"></div>

<?php if ($pageNum_rsOut > 0) { // Show if not first page    ?>
    <div class="smallNewButton"><a href="<?php printf("%s?pageNum_rsOut=%d%s", $currentPage, 0, $queryString_rsOut); ?>">First</a></div>
    <div class="smallNewButton"><a href="<?php printf("%s?pageNum_rsOut=%d%s", $currentPage, max(0, $pageNum_rsOut - 1), $queryString_rsOut); ?>">Previous</a></div>
<?php } // Show if not first page  ?>
<?php if ($pageNum_rsOut < $totalPages_rsOut) { // Show if not last page    ?>
    <div class="smallNewButton"><a href="<?php printf("%s?pageNum_rsOut=%d%s", $currentPage, min($totalPages_rsOut, $pageNum_rsOut + 1), $queryString_rsOut); ?>">Next</a></div>
    <div class="smallNewButton"><a href="<?php printf("%s?pageNum_rsOut=%d%s", $currentPage, $totalPages_rsOut, $queryString_rsOut); ?>">Last</a></div>
<?php } // Show if not last page    ?>


<?php
if ($totalRows_rsOut > 0) {
    $i = 0;
    ?>
    <!-- Snowflake -->
    <?php do { ?>
        <div class="Snowflake">
            <div class="SnowflakeHead"><a href="<?php echo $Shareurl; ?>?pageid=<?php echo $flakeStructList[$i]->m_id; ?>"><?php echo $flakeStructList[$i]->m_title; ?></a> </div>

            <!--SnowflakePanel-->
            <div class="SnowflakePanel"> <span> View </span> 
                <a href="<?php echo $Shareurl . "?pageid=" . $flakeStructList[$i]->m_id; ?>" title="View this post"> <img src="<?php echo $SnowflakesUrl . "resources/images/Icons/View.png"; ?>" height="22" width="22" alt="Edit" /> </a>
                <span>Share </span> 
                <a href="http://twitter.com/home?status=<?php echo htmlentities(rawurlencode($flakeStructList[$i]->m_title)); ?>%20<? echo "" . $Shareurl . "?pageid=" . $flakeStructList[$i]->m_id; ?>" title="Twitter" target="_blank"> <img src="<?php echo $SnowflakesUrl . "resources/images/Icons/Twitter.png"; ?>" height="22" width="22" alt="Twitter" /> </a> 
                <a href="http://www.facebook.com/sharer.php?u=<? echo "" . $Shareurl . "?pageid=" . $flakeStructList[$i]->m_id; ?>" title="Facebook" target="_blank"> <img src="<?php echo $SnowflakesUrl . "resources/images/Icons/Facebook.png"; ?>" height="22" width="22" alt="Facebook" /> </a> 
                <a href="https://plus.google.com/share?url=<? echo "" . $Shareurl . "?pageid=" . $flakeStructList[$i]->m_id; ?>&amp;title=<?php echo htmlentities(rawurlencode($flakeStructList[$i]->m_title)); ?>" title="GooglePlus" target="_blank"> <img src="<?php echo $SnowflakesUrl . "resources/images/Icons/GooglePlus.png"; ?>" height="22" width="22" alt="GooglePlus" /> </a> 
                <a href="http://digg.com/submit?phase=2&amp;url=<? echo "" . $Shareurl . "?pageid=" . $flakeStructList[$i]->m_id; ?>&amp;title=<?php echo htmlentities(rawurlencode($flakeStructList[$i]->m_title)); ?>" title="Digg" target="_blank"> <img src="<?php echo $SnowflakesUrl . "resources/images/Icons/Digg.png"; ?>" height="22" width="22" alt="Digg" /> </a> 
                <a href="http://stumbleupon.com/submit?url=<? echo "" . $Shareurl . "?pageid=" . $flakeStructList[$i]->m_id; ?>&amp;title=<?php echo htmlentities(rawurlencode($flakeStructList[$i]->m_title)); ?>" title="stumbleupon" target="_blank"> <img src="<?php echo $SnowflakesUrl . "resources/images/Icons/stumbleupon.png"; ?>" height="22" width="22" alt="stumbleupon" /> </a> 
                <a href="http://del.icio.us/post?url=<? echo "" . $Shareurl . "?pageid=" . $flakeStructList[$i]->m_id; ?>&amp;title=<?php echo htmlentities(rawurlencode($flakeStructList[$i]->m_title)); ?>" title="delicious" target="_blank"> <img src="<?php echo $SnowflakesUrl . "resources/images/Icons/delicious.png"; ?>" height="22" width="22" alt="delicious" /> </a> 
                <a class="flakeit" id="flakeit<?php echo $flakeStructList[$i]->m_id; ?>" title="flake it" data-type="snowflake"> <span>Flake it</span> <img src="<?php echo $SnowflakesUrl . "resources/images/Icons/Snowflakes.png"; ?>" height="22" width="22" alt="flake it" /> </a> 
            </div><!--End of SnowflakePanel-->

            <div class="PageBreak"></div>
            <div class="clear"></div>
            <!--SnowflakeDescr-->
            <div class="SnowflakeDescr">

                <div class="SnowflakeImage">
                    <a class="colorbox" href="<?php echo $UploadImgUrl . $flakeStructList[$i]->m_image_name; ?>"  onerror="this.href='<?php echo $imageMissing; ?>'"  title="<?php echo $flakeStructList[$i]->m_title; ?>" >
                        <img src="<?php echo $UploadImgUrl . $flakeStructList[$i]->m_image_name; ?>" onerror="this.src='<?php echo $imageMissing; ?>'"  alt="<?php echo $flakeStructList[$i]->m_image_name; ?>" />
                    </a>
                </div>

                <?php echo html_entity_decode($flakeStructList[$i]->m_body_text); ?> 

            </div><!--SnowflakeDescr Ends-->
            <div class="clear"></div>
            <div class="PageBreak"></div>
            <div class="SnowflakeDate"> Posted |: <?php echo date(" F j, Y", $flakeStructList[$i]->m_created); ?>  | By - <?php echo $flakeStructList[$i]->m_created_by; ?> </div>
            <div class="SnowflakeIt"> flakes <div class="flakeitParam" id="flakecount<?php echo $flakeStructList[$i]->m_id; ?>"> <?php echo $flakeStructList[$i]->m_flake_it; ?> </div></div>
            <div class="SharePost"> </div>
        </div>
        <!-- End of Snowflake -->
        <?php
        $i++;
    } while ($i < count($flakeStructList));
    ?>

<?php } else { ?> 
    <h4 class="SummaryHead">There are no published Snowflakes </h4>
<?php } ?> 

<?php
$SFconnects->close();
?>
