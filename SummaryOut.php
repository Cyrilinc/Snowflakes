<?php
require_once 'lib/sf.php';
require_once 'lib/sfConnect.php';
require_once 'config/Config.php';
require_once 'lib/sfSettings.php';
?>
<?php
$maxRows_rsOut = filter_input(INPUT_GET, 'MaxNumber', FILTER_VALIDATE_INT);
$pageNum_rsOut = 0;
$snowflakesRS = filter_input(INPUT_GET, 'pageNum_rsOut', FILTER_VALIDATE_INT);
if (isset($snowflakesRS))
{
    $pageNum_rsOut = $snowflakesRS;
}
$startRow_rsOut = $pageNum_rsOut * $maxRows_rsOut;

$config = new databaseParam('config/config.ini');
$SFconnects = new sfConnect($config->dbArray());
$SFconnects->connect(); // Connect to database

$TodaysDate = sfUtils::todaysDate();

$query_rsOut = "SELECT * FROM snowflakes WHERE publish = 1 ORDER BY created DESC";
$query_limit_rsOut = sprintf("%s LIMIT %d, %d", $query_rsOut, $startRow_rsOut, $maxRows_rsOut);

$SFconnects->fetch($query_limit_rsOut);
$row_snowflakesRs = $SFconnects->getResultArray();
$snowflakesStructList = array();
foreach ($row_snowflakesRs as $key => $value)
{
    $snowflakesStructList[$key] = new snowflakeStruct();
    $snowflakesStructList[$key]->populate($value);
    //$snowflakesStructList[$key]->printsnowlakes();
}
$total_SnowflakesRs = filter_input(INPUT_GET, 'totalRows_rsOut', FILTER_VALIDATE_INT);
if (isset($total_SnowflakesRs))
{
    $totalRows_rsOut = $total_SnowflakesRs;
}
else
{
    $sql = str_replace("SELECT * FROM", "SELECT COUNT(id) count FROM", $query_rsOut);
    $SFconnects->fetch($sql);
    $result = $SFconnects->getResultArray();
    $totalRows_rsOut = $result[0]['count'];
}
$totalPages_rsOut = ceil($totalRows_rsOut / $maxRows_rsOut) - 1;

$siteSettings = new sfSettings('config/config.ini');
$imageMissing = $siteSettings->m_sfGalleryUrl . "missing_default.png";
?>
<?php
$url = $otherurl = strtok(sfUtils::curPageURL(), '?');
$SnowflakesUrl = $siteSettings->m_sfUrl;

if (isset($siteSettings->m_snowflakesResultUrl))
{
    $SnowflakesResultUrl = $siteSettings->m_snowflakesResultUrl;
}
else
{
    $SnowflakesResultUrl = 'notset';
}

// if viewing from Snowflakes's OutputView.php file
if (strpos($otherurl, 'OutputView.php') !== false)
{
    $Shareurl = str_replace("OutputView.php", "OneView.php", $url);
    $Powerlink = str_replace("OutputView.php", "resources/images/Snowflakes2.png", $otherurl);
}
else if (strpos($otherurl, 'SummaryOut.php') !== false)
{// else if viewing from Snowflakes's Out.php file
    $Shareurl = str_replace("SummaryOut.php", "OneView.php", $url);
    $Powerlink = str_replace("SummaryOut.php", "resources/images/Snowflakes2.png", $otherurl);
    if ($SnowflakesResultUrl !== 'notset')
    { /// if user provides result page in snowflakes settings
        $Shareurl = $SnowflakesResultUrl;
    }
}
else
{// else if viewing Snowflakes from another file outside snowflakes
    $Shareurl = $SnowflakesUrl . "OneView.php";
    if ($SnowflakesResultUrl !== 'notset')
    { /// if user provides result page in snowflakes settings
        $Shareurl = $SnowflakesResultUrl;
    }
    $Powerlink = $SnowflakesUrl . "resources/images/Snowflakes2.png";
}
?>
<?php
if ($maxRows_rsOut != Null)
{
    ?>   

    <?php
    if ($totalRows_rsOut > 0)
    {
        $i = 0;
        ?>
        <!-- Snowflake --> 
        <?php
        do
        {
            ?>
            <div class="Snowflake">
                <div class="SnowflakeHead"><a href="<?php echo $Shareurl; ?>?pageid=<?php echo $snowflakesStructList[$i]->m_id; ?>"><?php echo $snowflakesStructList[$i]->m_title; ?></a> </div>

                <div class="Break2"></div>

                <div class="SnowflakeDescrSmall">
                    <div class="SnowflakeImageSmall">
                        <a class="colorbox" href="<?php echo $siteSettings->m_sfGalleryUrl . $snowflakesStructList[$i]->m_image_name; ?>" title="<?php echo $snowflakesStructList[$i]->m_title; ?>" onerror="this.href='<?php echo $siteSettings->m_sfGalleryUrl . "missing_default.png"; ?>'" >
                            <img src="<?php echo $siteSettings->m_sfGalleryUrl . $snowflakesStructList[$i]->m_image_name; ?>" onerror="this.src='<?php echo $imageMissing; ?>'" alt="<?php echo $snowflakesStructList[$i]->m_image_name; ?>" />
                        </a>
                    </div>

                    <?php
                    $SFSumLink = "<p><a href=\"" . $Shareurl . "?pageid=" . $snowflakesStructList[$i]->m_id . "\">Read More &raquo;</a></p>";
                    $BodyString = $snowflakesStructList[$i]->m_body_text;
// strip tags to avoid breaking any html
                    $BodyString = strip_tags($BodyString);

                    if (strlen($BodyString) > 350)
                    {

                        // truncate string
                        $stringCut = substr($BodyString, 0, 350);

                        // make sure it ends in a word so assassinate doesn't become ass...
                        $BodyString = substr($stringCut, 0, strrpos($stringCut, ' ')) . '...' . $SFSumLink;
                    }

                    echo $BodyString;
                    ?>

                </div>

                <div class="PageBreak"></div>
                <div class="SnowflakeDate"> Posted |: <?php echo date(" F j, Y", $snowflakesStructList[$i]->m_created); ?>  | By - <?php echo $snowflakesStructList[$i]->m_created_by; ?> </div>
                <div class="SharePost"> </div>
            </div>
            <!-- End of Snowflake -->
            <?php
            $i++;
        } while ($i < count($snowflakesStructList));
        ?>
        <?php
    }
    else
    {
        ?> 
        <h4 class="SummaryHead">There are no published Snowflakes </h4>
    <?php } ?> 

    <?php
}
else
{
    ?>

    <h4 class="SummaryHead">No Max number of Snowflakes indicated </h4>

<?php }
?> 


<?php
$SFconnects->close();
?>
