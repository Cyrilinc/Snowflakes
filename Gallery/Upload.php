<?php
require_once '../lib/sf.php';
require_once '../lib/sfConnect.php';
require_once '../config/Config.php';
require_once '../lib/sfImageProcessor.php';
//ini_set('max_file_uploads', 50);

$settingsConfig = Config::getConfig("settings", '../config/config.ini');

$Post_upload = filter_input(INPUT_POST, 'upload');
if (isset($Post_upload)) {
    sfImageProcessor::UploadMultiImages($_FILES['uploadImage'], "../config/config.ini", $GalleryMessage);
}
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Cyril Inc">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Upload Images</title>
        <link rel="stylesheet" type="text/css" href="../resources/css/Mobile.css" media="only screen and (max-width: 767px)" />
        <link rel="stylesheet" type="text/css" href="../resources/css/style.css" media="only screen and (min-width: 768px)" />
        <script type="text/javascript">
            $('UploadForm').submit(function() {
                $('input:file[value=""]').attr('disabled', true);
            });
            function handleFileSelect(evt) {
                var files = evt.target.files; // FileList object

                // Loop through the FileList and render image files as thumbnails.
                for (var i = 0, f; f = files[i]; i++) {

                    // Only process image files.
                    if (!f.type.match('image.*')) {
                        continue;
                    }

                    var reader = new FileReader();

                    // Closure to capture the file information.
                    reader.onload = (function(theFile) {
                        return function(e) {
                            // Render thumbnail.
                            var span = document.createElement('span');
                            span.innerHTML = ['<img class="Uploadthumb" src="', e.target.result, '" title="', escape(theFile.name), '"/>'].join('');
                            document.getElementById('list').insertBefore(span, null);
                        };
                    })(f);

                    // Read in the image file as a data URL.
                    reader.readAsDataURL(f);
                }
            }

            document.getElementById('files').addEventListener('change', handleFileSelect, false);
        </script>
    </head>

    <body>
        <h4>Add Image to Gallery</h4>
        <!-- uploadContainer-->
        <div class=" uploadContainer">
            <!-- PageWrap -->
            <div class="PageWrap">
                <?php
                if (!empty($GalleryMessage)) {
                    echo "<p> " . $GalleryMessage . " </p>";
                }
                ?>
                <!--contactform-->
                <div class="contactform">
                    <form action="#" method="post" enctype="multipart/form-data" name="UploadForm" id="installForm">
                        <input type="file" id="files" name="uploadImage[]" class="inputtext2 controls" placeholder="Image" size="30" multiple="multiple"/> <br /> 
                        <label>Maximum allowed for each file size is <?php echo sfUtils::formatSizeUnits($settingsConfig['maxImageSize']); ?></label><br /> 
                        <label>Maximum number of images to upload at once is <?php echo ini_get('max_file_uploads'); ?></label><br /> 
                        <input type="hidden" name="MaxSize" value="<?php echo $settingsConfig['maxImageSize']; ?>">
                        <input class="NewButton" type="submit" name="upload" value="Upload" />
                        <div id="progressNumber"></div>
                    </form>
                    <div class="clear"></div>
                    <div class="Break2"></div>
                    <output id="list"></output>

                </div><!--contactform Ends-->

            </div> <!-- PageWrap Ends -->
        </div> <!-- uploadContainer Ends-->
    </body>
</html>
