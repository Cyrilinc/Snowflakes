<?php
require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';
require_once '../lib/sfImageProcessor.php';
?>

<?php
//The upload directory
$siteSettings = new settingsStruct('../config/config.ini');
$datadir=new dataDirParam("../config/config.ini");
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
$Powerlink = "../resources/images/Snowflakes2.png";
?>
<!DOCTYPE HTML>
<html lang="en" ><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Cyril Inc">
        <meta name="viewport" content="width=device-width, maximum-scale = 1, minimum-scale=1" />
        <!-- InstanceBeginEditable name="doctitle" -->
        <title><?php echo htmlentities($galleryStruct->m_title); ?></title>
        <!-- InstanceEndEditable -->
        <link rel="icon" href="../resources/images/favicon.ico" type="image/x-icon" />
        <link rel="shortcut icon" href="../resources/images/favicon.ico">
        <link rel="apple-touch-icon" href="../resources/images/apple-touch-icon.png">
        <link rel="stylesheet" type="text/css" href="../resources/css/Mobile.css" media="only screen and (max-width: 767px)" />
        <link rel="stylesheet" type="text/css" href="../resources/css/style.css" media="only screen and (min-width: 768px)" />
        <link rel="stylesheet" title="text/css" href="../resources/css/ColorBox.css" />
        <link rel="stylesheet" title="text/css" href="../resources/css/fontstyle.css" />
        <script type="text/javascript" src="../resources/Js/jquery-1.11.0.js"></script>
        <script type="text/javascript" src="../resources/Js/modernizr.custom.63321.js"></script>
        <script type="text/javascript" src="../resources/Js/jquery.cycle.all.js"></script>
        <script type="text/javascript" src="../resources/Js/jquery.colorbox.js"></script>
        <script type="text/javascript" src="../resources/Js/scrolltopcontrol.js"></script>
        <script type="text/javascript" src="../resources/Js/Snowflakes.js"></script>

        <!-- InstanceBeginEditable name="head" -->
        <script type="text/javascript">

            $(document).ready(function() {
                $(".HeaderWrapper, #SnowFooter").hide();

            });

            var flakeitUrl = "<?php echo $siteSettings->m_flakeItUrl; ?>";
        </script>
        <script type="text/javascript" src="<?php echo $siteSettings->m_sfUrl; ?>resources/Js/flakeit.js"></script>

        <link rel="stylesheet" type="text/css" href="../resources/css/stapel.css" />
        <script type="text/javascript" src="../resources/Js/jquery.stapel.js"></script>
        <script type="text/javascript">
            $(function() {

                var $grid = $('#tp-grid'),
                        $name = $('#name'),
                        $close = $('#close'),
                        $loader = $('<div class="loader"><i></i><i></i><i></i><i></i><i></i><i></i><span>Loading...</span></div>').insertBefore($grid),
                        stapel = $grid.stapel({
                            delay: 50,
                            onLoad: function() {
                                $loader.remove();
                            },
                            onBeforeOpen: function(pileName) {
                                $name.html(pileName);
                            },
                            onAfterOpen: function(pileName) {
                                $close.show();
                            }
                        });

                $close.on('click', function() {
                    $close.hide();
                    $name.empty();
                    stapel.closePile();
                });

            });
        </script>

        <!-- InstanceEndEditable -->
    </head>
    <body> 
        <!--HeaderWrapper-->
        <div class="HeaderWrapper"> 
            <!--pagewidth-->
            <div class="pagewidth">
                <header class="site-header">
                    <div class="pagewidth">
                        <div class="Logo"><img alt="Snowflakes" class="logo" src="../resources/images/Snowflakes.png" width="180" height="60" /></div>
                        <div class="SideMenu"> <a class="opener" id="touch-menu" href="#"><i class="icon-reorder"></i>Menu</a>
                            <ul id="primary_nav" class="primary_nav">
                                <!-- InstanceBeginEditable name="menuEdit" -->

                                <!-- InstanceEndEditable -->
                            </ul>
                        </div>
                        <!--SideMenu--> 
                    </div>
                    <!--pagewidth--> 

                </header>
            </div>
            <!--pagewidth--> 
        </div>
        <!-- End HeaderWrapper-->

        <div class="clear"></div>
        <div class="Break2"></div>

        <!-- ContentWrapper -->
        <div class="ContentWrapper"> 
            <!-- Content -->
            <div class="Content"> <!-- InstanceBeginEditable name="BodyRegion" -->
                <!-- PageWrap -->
                <div class="PageWrap">

                    <script type="text/javascript">
                        var flakeitUrl = "<?php echo $siteSettings->m_flakeItUrl; ?>";
                    </script>
                    <script type="text/javascript" src="../resources/Js/flakeit.js"></script>


                    <div style="float: right; background-color:#2b2b2b;"><a href="http://cyrilinc.co.uk/snowflakes/" target="_blank"><img src="<?php echo $Powerlink; ?>" width="120" height="40" alt="Powered by Snowflakes" /></a> </div>
                    <?php if ($Galleryid != Null) { ?>   
                        <!--SnowflakePanel-->
                        <div class="SnowflakePanel"> <div style="color:#000; float:left">Share </div> 
                            <a href="http://twitter.com/home?status=<?php echo $galleryStruct->m_title; ?>%20<? echo "" . $url; ?>" title="Twitter" target="_blank"> <img src="../resources/images/Icons/Twitter.png" height="30" width="30" alt="Twitter" /> </a> 
                            <a href="http://www.facebook.com/sharer.php?u=<? echo "" . $url; ?>" title="Facebook" target="_blank"> <img src="../resources/images/Icons/Facebook.png" height="30" width="30" alt="Facebook" /> </a> 
                            <a href="https://plus.google.com/share?url=<? echo "" . $url; ?>&amp;title=<?php echo $galleryStruct->m_title; ?>" title="GooglePlus" target="_blank"> <img src="../resources/images/Icons/GooglePlus.png" height="30" width="30" alt="GooglePlus" /> </a> 
                            <a href="http://digg.com/submit?phase=2&url=<? echo "" . $url; ?>&amp;title=<?php echo $galleryStruct->m_title; ?>" title="Digg" target="_blank"> <img src="../resources/images/Icons/Digg.png" height="30" width="30" alt="Digg" /> </a> 
                            <a href="http://stumbleupon.com/submit?url=<? echo "" . $url; ?>&amp;title=<?php echo $galleryStruct->m_title; ?>" title="stumbleupon" target="_blank"> <img src="../resources/images/Icons/stumbleupon.png" height="30" width="30" alt="stumbleupon" /> </a> 
                            <a href="http://del.icio.us/post?url=<? echo "" . $url; ?>&amp;title=<?php echo $galleryStruct->m_title; ?>" title="delicious" target="_blank"> <img src="../resources/images/Icons/delicious.png" height="30" width="30" alt="delicious" /> </a> 
                            <a class="flakeit" id="flakeit<?php echo $galleryStruct->m_id; ?>" title="flake it" data-type="gallery"> <span>Flake it</span> <img src="../resources/images/Icons/Snowflakes.png" height="22" width="22" alt="flake it" /> </a> 
                        </div>
                        <!--End of SnowflakePanel-->


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
                                                <img src="<?php echo $imageThumbLink; ?>" onerror="this.src='<?php echo $imageMissing; ?>'" alt="<?php echo htmlentities($DBImageCaption [$counter]); ?>"> 
                                            </a>
                                        </li>

                                        <?php
                                    }
                                    ?>
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


                    <?php } else {
                        ?>

                        <h1>No Gallery to view </h1>

                    <?php }
                    ?>

                </div>
                <!-- End of PageWrap --> 
                <!-- InstanceEndEditable -->  </div>
            <!-- end of Content --> 
        </div>
        <!-- end of ContentWrapper -->

        <footer id="SnowFooter"> 
            <!-- CMSFooterWrapper -->
            <div class="CMSFooterWrapper"> 

                <!--CopyRight-->
                <div class="CopyRight">
                    <p>&copy; 2013 Cyril Inc. All Rights Reserved. | <a href="http://cyrilinc.co.uk/Legal.html"> Legal information</a> | <a href="mailto:contactus@cyrilinc.co.uk" id="CopyRContactus">Contact Us </a>|</p>
                </div>
                <!--END of  CopyRight--> 

                <!--SocialBar-->
                <div class="SocialBar"> 
                    <!--Socialtable-->
                    <div class="Socialtable">
                        <ul>
                            <li><a href="http://cyrilinc.blogspot.co.uk/" target="_blank" title="Cyril Inc on Blogger.com"><span class="icon-blogger blogger"></span></a></li>
                            <li><a href="https://www.facebook.com/pages/Cyril-Inc/151728454900027" target="_blank" title="Cyril Inc on Facebook"><span class="icon-facebook facebook"></span></a></li>
                            <li><a href="https://twitter.com/intent/follow?original_referer=http%3A%2F%2Ftwitter.com%2Fabout%2Fresources%2Ffollowbutton&amp;region=follow&amp;screen_name=CyrilInc&amp;source=followbutton&amp;variant=1.0" target="_blank" > <span class="icon-twitter twitter"></span></a></li>
                            <li><a href="http://delicious.com/cyrilinc" target="_blank" title="Cyril Inc on delicious"><span class="icon-delicious delicious"></span></a></li>
                            <li><a href="http://pinterest.com/cyrilinc" target="_blank" title="Cyril Inc on Pinterest"><span class="icon-pinterest pinterest"></span></a></li>
                            <li><a href="https://plus.google.com/117444390192468783291" target="_blank" title="Cyril Inc on GooglePlus"><span class="icon-googleplus googleplus"></span></a></li>
                            <li><a href="http://www.stumbleupon.com/stumbler/cyrilinc" target="_blank" title="Cyril Inc on stumbleupon"><span class="icon-stumbleupon stumbleupon"></span></a></li>
                            <li><a href="http://www.youtube.com/CyrilIncBroadcast" target="_blank" title="Cyril Inc on YouTube"><span class="icon-youtube youtube"></span></a></li>
                        </ul>
                    </div>
                    <!--End Socialtable--> 
                </div>
                <!--End SocialBar--> 

            </div>
            <!-- End of CMSFooterWrapper --> 

        </footer>
        <!-- InstanceBeginEditable name="FootEdit" --> 

        <!-- InstanceEndEditable -->
    </body>
    <!-- InstanceEnd --></html>
<?php
$SFconnects->close();
?>
