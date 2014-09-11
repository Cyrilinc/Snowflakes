<?php
require_once 'lib/sf.php';
require_once 'lib/sfConnect.php';
require_once 'config/Config.php';
require_once 'lib/sfSettings.php';
?>
<?php
$contentType = filter_input(INPUT_GET, 'type') ? filter_input(INPUT_GET, 'type') : 'html';

$currentPage = sfUtils::getFilterServer( 'PHP_SELF');

$maxRows = 5;
$pageNum = 0;
$rsOut = filter_input(INPUT_GET, 'pageNum');
if (isset($rsOut))
{
    $pageNum = $rsOut;
}
$startRow = $pageNum * $maxRows;

$config = new databaseParam('config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$SFconnects->connect(); // Connect to database

$pageid = filter_input(INPUT_GET, 'pageid');
$flakeit = filter_input(INPUT_GET, 'flakeit');
if (isset($flakeit) && isset($pageid))
{
    sfUtils::flakeIt($SFconnects, $pageid, "snowflake");
}

$query = "SELECT * FROM snowflakes WHERE publish = 1 ORDER BY created DESC";
$query_limit = sprintf("%s LIMIT %d, %d", $query, $startRow, $maxRows);
$SFconnects->fetch($query_limit);
$row = $SFconnects->getResultArray();
$flakeStructList = array();
foreach ($row as $key => $value)
{
    $flakeStructList[$key] = new snowflakeStruct();
    $flakeStructList[$key]->populate($value);
}

$total = filter_input(INPUT_GET, 'totalRows');
if (isset($total))
{
    $totalRows = $total;
}
else
{
    $SFconnects->fetch("SELECT COUNT(id) count FROM snowflakes WHERE publish = 1 ORDER BY created DESC");
    $result = $SFconnects->getResultArray();
    $totalRows = $result[0]['count'];
}
$totalPages = ceil($totalRows / $maxRows) - 1;

$siteSettings = new sfSettings('config/config.ini');
$queryString='';
$query_string = sfUtils::getFilterServer( 'QUERY_STRING');
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
$url = $otherurl = sfUtils::curPageURL();
$SnowflakesUrl =$siteSettings->m_sfUrl;
$SnowflakesResultUrl = $siteSettings->m_snowflakesResultUrl;
$Powerlink = $siteSettings->m_sfUrl . "resources/images/Snowflakes2.png";
$Shareurl = $siteSettings->m_sfUrl . "OneView.php";
$rsslink = $siteSettings->m_sfUrl . "resources/images/Icons/Rss.png";

$UploadUrl =$siteSettings->m_sfGalleryUrl;
$imageMissing = $UploadUrl . "missing_default.png";

if (strlen($SnowflakesResultUrl) > 0)
{/// if user provides result page in snowflakes settings
    $Shareurl = $SnowflakesResultUrl;
}

if (isset($siteSettings->m_snowflakesOutUrl))
{
    $currentPage = $siteSettings->m_snowflakesOutUrl;
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
<div style="float: right; background-color:<?php echo $sflogo; ?>;"><a href="http://cyrilinc.co.uk/snowflakes/" target="_blank"><img src="<?php echo $Powerlink; ?>" width="120" height="40" alt="Powered by Snowflakes" /></a> </div>
<div style="float: right; background-color:<?php echo $sflogo; ?>;" class="NewButton"><a href="<?php echo $siteSettings->m_sfUrl; ?>rss.php?ty=snowflakes" title="Snowflakes rss"> <img src="<?php echo $rsslink; ?>" height="22" width="22"  alt="Add" /></a></div>
<div class="clear"></div>

<?php
if ($pageNum > 0)
{ // Show if not first page     
    ?>
    <div class="smallNewButton"><a href="<?php printf("%s?pageNum=%d%s", $currentPage, 0, $queryString); ?>">First</a></div>
    <div class="smallNewButton"><a href="<?php printf("%s?pageNum=%d%s", $currentPage, max(0, $pageNum - 1), $queryString); ?>">Previous</a></div>
<?php } // Show if not first page   ?>
<?php
if ($pageNum < $totalPages)
{ // Show if not last page     
    ?>
    <div class="smallNewButton"><a href="<?php printf("%s?pageNum=%d%s", $currentPage, min($totalPages, $pageNum + 1), $queryString); ?>">Next</a></div>
    <div class="smallNewButton"><a href="<?php printf("%s?pageNum=%d%s", $currentPage, $totalPages, $queryString); ?>">Last</a></div>
<?php } // Show if not last page     ?>

<?php
if ($totalRows > 0)
{
    $i = 0;
    do
    {
        ?>
        <!-- Snowflake -->
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
            </div><!--/SnowflakePanel-->

            <div class="PageBreak"></div>
            <div class="clear"></div>
            <!--SnowflakeDescr-->
            <div class="SnowflakeDescr">

                <div class="SnowflakeImage">
                    <a class="colorbox" href="<?php echo $UploadUrl . $flakeStructList[$i]->m_image_name; ?>"  onerror="this.href='<?php echo $imageMissing; ?>'"  title="<?php echo $flakeStructList[$i]->m_title; ?>" >
                        <img src="<?php echo $UploadUrl . $flakeStructList[$i]->m_image_name; ?>" onerror="this.src='<?php echo $imageMissing; ?>'"  alt="<?php echo $flakeStructList[$i]->m_image_name; ?>" />
                    </a>
                </div>

                <?php echo html_entity_decode($flakeStructList[$i]->m_body_text); ?> 

            </div><!--/SnowflakeDescr-->
            <div class="clear"></div>
            <div class="PageBreak"></div>
            <div class="SnowflakeDate"> Posted |: <?php echo date(" F j, Y", $flakeStructList[$i]->m_created); ?>  | By - <?php echo $flakeStructList[$i]->m_created_by; ?> </div>
            <div class="SnowflakeIt">
                <img src="<?php echo $SnowflakesUrl . "resources/images/Icons/Snowflakes.png"; ?>" height="22" width="22" alt="flake it" /> 
                <span class="flakeitParam" id="flakecount<?php echo $flakeStructList[$i]->m_id; ?>"> <?php echo $flakeStructList[$i]->m_flake_it; ?> </span>
            </div>
            <div class="SharePost"> </div>
        </div>
        <!--/Snowflake -->
        <?php
        $i++;
    } while ($i < count($flakeStructList));
}
else
{
    ?> 
    <h4 class="SummaryHead">There are no published Snowflakes </h4>
<?php } ?> 

<?php
$SFconnects->close();
?>
