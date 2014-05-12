<?php

/**
 * Description of sfImageProcessor
 *
 * @author Cyril Adelekan
 */
//require_once 'sf.php';
//require_once 'config/Config.php';
///////// Image Session start AND GLOBAL variables
//initialize the session
if (!isset($_SESSION)) {
    session_start();
} //Do not remove this
//only assign a new timestamp if the session variable is empty
if (!isset($_SESSION['ImageFile']) && !isset($_SESSION['ImageThumbFile']) && !isset($_SESSION['ImageCaption']) && !isset($_SESSION['ImageFiles']) && !isset($_SESSION['ImageThumbFiles']) && !isset($_SESSION['ImageCaptions'])) {
    $_SESSION['ImageFile'] = "";
    $_SESSION['ImageThumbFile'] = "";
    $_SESSION['ImageCaption'] = "";
    $_SESSION['ImageFiles'] = array();
    $_SESSION['ImageThumbFiles'] = array();
    $_SESSION['ImageCaptions'] = array();
}
/* class sfUploadsError extends SplEnum {

  const __default = self::UPLOAD_ERR_OK;
  const UPLOAD_ERR_OK = "No errors.";
  const UPLOAD_ERR_INI_SIZE = "File larger than the require .";
  const UPLOAD_ERR_FORM_SIZE = "Larger than maximun required file.";
  const UPLOAD_ERR_PARTIAL = "Partial upload, Could not complete image upload...";
  const UPLOAD_ERR_NO_FILE = "No file.";
  const UPLOAD_ERR_NO_TMP_DIR = "No temporary directory.";
  const UPLOAD_ERR_CANT_WRITE = "Can't write to disk.";
  const UPLOAD_ERR_EXTENSION = "file upload stopped by extension.";

  } */

class sfGalleryImage {

    var $m_FileName; // The image name
    var $m_FileTmpName; // the temporary image name
    var $m_FileSize; // the image size
    var $m_FileType; // the image type
    var $m_FileBaseName; // the image base name
    var $m_FileExtension; // the image extension
    var $m_MaxSize; // the maximum image size for upload
    var $m_Message; // success or error message
    var $m_Height; // the image height
    var $m_Weight; // the image height
    var $m_TargetFileName; // the Target file name to be uploaded
    var $m_TargetFileImageLoc; // the location where the image should be stored
    var $m_TargetFileThumbLoc; // the location where the thumbnail should be stored
    var $m_UploadImgDir; // the uploading image directory
    var $m_UploadThumbDir; // the uploading thumb directory
    var $m_thumbWidth; //the width of the thumb image file
    var $m_thumbHeight; // the height of the thumb image file
    var $m_MaxImageWidth; // The maximum image width
    var $m_File_is_Uploaded;
    var $m_Thumb_is_Uploaded;
    var $m_errorCode;
    var $m_ImageExtList;
    var $m_ImageTypesList;

    public function __construct($inifile = '../config/config.ini', $forGallery = true) {
        $settingsConfig = Config::getConfig("settings", $inifile);

        $this->m_UploadImgDir = $settingsConfig['galleryImgDir'];
        $this->m_UploadThumbDir = $settingsConfig['galleryThumbDir'];
        $this->m_MaxSize = $settingsConfig['maxImageSize'];
        $this->m_MaxImageWidth = $settingsConfig['maxImageWidth'];
        if ($forGallery) {
            $this->setThumbinit($settingsConfig['thumbWidth'], $settingsConfig['thumbHeight']);
        } else {
            $this->m_UploadImgDir = $settingsConfig['uploadGalleryDir'];
        }

        $this->m_ImageExtList = explode(",", $settingsConfig['imageExtList']);
        $this->m_ImageTypesList = explode(",", $settingsConfig['imageTypesList']);

        $this->m_errorCode = 0;
    }

    public function init($FileName, $FileTmpName, $FileSize, $FileType) {
        $this->m_FileName = $FileName;
        $this->m_FileTmpName = $FileTmpName;
        $this->m_FileSize = $FileSize;
        $this->m_FileType = $FileType;
        $this->m_FileBaseName = basename($FileName, '.' . substr($FileName, strrpos($FileName, '.') + 1));
        $this->m_FileExtension = strtolower(substr($FileName, strrpos($FileName, '.') + 1));

        require_once 'sf.php';
        //Image File Name Stamp Random sfUUID
        $refineName = sfUtils::UUID(); // or time(); // function
        /// Set the target file to be a sfUUID or time stamp "refineName" and the Original file name
        $refineName = strtoupper($refineName);
        $this->m_TargetFileName = trim($refineName . "." . $this->m_FileExtension);

        $this->m_TargetFileImageLoc = $this->m_UploadImgDir . $this->m_TargetFileName;
        $this->m_TargetFileThumbLoc = $this->m_UploadThumbDir . $this->m_TargetFileName;
    }

    public function setThumbinit($thumbWidth, $thumbHeight) {
        if (!$thumbWidth && !$thumbHeight) {
            return false;
        }

        $this->m_thumbWidth = $thumbWidth;
        $this->m_thumbHeight = $thumbHeight;
    }

    //You do not need to alter these function
    public static function getImageHeight($image) {
        if (!$image) {
            return false;
        }

        $size = getimagesize($image);
        $Height = $size[1];
        return $Height;
    }

    //You do not need to alter these function
    public static function getImageWidth($image) {
        if (!$image) {
            return false;
        }

        $size = getimagesize($image);
        $Weight = $size[0];
        return $Weight;
    }

    function nameImage($imgExtension) {
        return time() . substr(md5(microtime()), 0, rand(5, 12)) . $imgExtension;
    }

    function getMessage() {
        return $this->m_errorCode . " ==> " . $this->m_Message;
    }

    //You do not need to alter these function
    public static function resizeImage($image, $width, $height, $scale) {
        list($imagewidth, $imageheight, $imageType) = getimagesize($image);
        $imageType = image_type_to_mime_type($imageType);
        $newImageWidth = ceil($width * $scale);
        $newImageHeight = ceil($height * $scale);

        switch ($imageType) {
            case "image/gif":
                $source = imagecreatefromgif($image);
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                $source = imagecreatefromjpeg($image);
                break;
            case "image/png":
            case "image/x-png":
                $source = imagecreatefrompng($image);
                break;
        }
        $newImage = imagecreatetruecolor($newImageWidth, $newImageHeight);


        if (($imageType == "image/gif") || ($imageType == "image/png") || ($imageType == "image/x-png")) {

            $colourTotal = imagecolorstotal($source);
            imagetruecolortopalette($newImage, true, $colourTotal <= 0 ? 1 : $colourTotal);
            $currentTransparent = imagecolortransparent($source);

            // If we have a specific transparent color
            if ($currentTransparent >= 0) {

                // Get the original image's transparent color's RGB values
                $transparentColor = imagecolorsforindex($source, $currentTransparent);

                // Allocate the same color in the new image resource
                $currentTransparent = imagecolorallocate($newImage, $transparentColor['red'], $transparentColor['green'], $transparentColor['blue']);

                // Completely fill the background of the new image with allocated color.
                imagefill($newImage, 0, 0, $currentTransparent);

                // Set the background color for new image to transparent
                imagecolortransparent($newImage, $currentTransparent);
            }
            // Always make a transparent background color for PNGs that don't have one allocated already
            elseif (($imageType == "image/png") || ($imageType == "image/x-png")) {

                // Turn off transparency blending (temporarily)
                imagealphablending($newImage, false);

                // Create a new transparent color for image
                $color = imagecolorallocatealpha($newImage, 0, 0, 0, 127);

                // Completely fill the background of the new image with allocated color.
                imagefill($newImage, 0, 0, $color);

                // Restore transparency blending
                imagesavealpha($newImage, true);
            }
        }

        imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newImageWidth, $newImageHeight, $width, $height);

        switch ($imageType) {
            case "image/gif":
                imagegif($newImage, $image);
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                imagejpeg($newImage, $image, 100);
                break;
            case "image/png":
            case "image/x-png":
                imagepng($newImage, $image);
                break;
        }

        chmod($image, 0777);
        return $image;
    }

    //You do not need to alter these function
    public static function resizeThumbnailImage($ThumbImageName, $image, $width, $height, $start_width, $start_height, $scale) {
        list($imagewidth, $imageheight, $imageType) = getimagesize($image);
        $imageType = image_type_to_mime_type($imageType);

        $newImageWidth = ceil($width * $scale);
        $newImageHeight = ceil($height * $scale);
        $newImage = imagecreatetruecolor($newImageWidth, $newImageHeight);

        switch ($imageType) {
            case "image/gif":
                $source = imagecreatefromgif($image);
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                $source = imagecreatefromjpeg($image);
                break;
            case "image/png":
            case "image/x-png":
                $source = imagecreatefrompng($image);
                // Turn off alpha blending and set alpha flag
                imagealphablending($source, false);
                imagesavealpha($source, true);
                break;
        }

        if (($imageType == "image/gif") || ($imageType == "image/png") || ($imageType == "image/x-png")) {
            $colourTotal = imagecolorstotal($source);
            imagetruecolortopalette($newImage, true, $colourTotal <= 0 ? 1 : $colourTotal);
            $currentTransparent = imagecolortransparent($source);

            // If we have a specific transparent color
            if ($currentTransparent >= 0) {

                // Get the original image's transparent color's RGB values
                $transparentColor = imagecolorsforindex($source, $currentTransparent);

                // Allocate the same color in the new image resource
                $currentTransparent = imagecolorallocate($newImage, $transparentColor['red'], $transparentColor['green'], $transparentColor['blue']);

                // Completely fill the background of the new image with allocated color.
                imagefill($newImage, 0, 0, $currentTransparent);

                // Set the background color for new image to transparent
                imagecolortransparent($newImage, $currentTransparent);
            }
            // Always make a transparent background color for PNGs that don't have one allocated already
            elseif (($imageType == "image/png") || ($imageType == "image/x-png")) {

                // Turn off transparency blending (temporarily)
                imagealphablending($newImage, false);

                // Create a new transparent color for image
                $color = imagecolorallocatealpha($newImage, 0, 0, 0, 127);

                // Completely fill the background of the new image with allocated color.
                imagefill($newImage, 0, 0, $color);

                // Restore transparency blending
                imagesavealpha($newImage, true);
            }
        }


        imagecopyresampled($newImage, $source, 0, 0, $start_width, $start_height, $newImageWidth, $newImageHeight, $width, $height);
        switch ($imageType) {
            case "image/gif":
                imagegif($newImage, $ThumbImageName);
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                imagejpeg($newImage, $ThumbImageName, 100);
                break;
            case "image/png":
            case "image/x-png":
                imagepng($newImage, $ThumbImageName);
                break;
        }
        chmod($ThumbImageName, 0777);
        return $ThumbImageName;
    }

    /// Create Thumb Function
    public function CreateThumb($x1, $y1, $x2, $y2, $w, $h) {
        //Scale the image to the thumb_width set above
        $scale = $this->m_thumbWidth / $w;
        $cropped = self::resizeThumbnailImage($this->m_TargetFileThumbLoc, $this->m_TargetFileImageLoc, $w, $h, $x1, $y1, $scale);
        return $cropped;
    }

    public function TargetFileImageLoc() {
        return $this->m_TargetFileImageLoc;
    }

    public function TargetFileThumbLoc() {
        return $this->m_TargetFileThumbLoc;
    }

    public function imageUploaded() {
        return $this->m_File_is_Uploaded;
    }

    public function thumbUploaded() {
        return $this->m_Thumb_is_Uploaded;
    }

    ///Upload an image size and resize it to default width
    public function UploadImage() {

        $fileSizeString = sfUtils::formatSizeUnits($this->m_MaxSize);
        // Check if the file size is greater that the Maximum file size
        if ($this->m_FileSize > $this->m_MaxSize) {
            $this->m_Message .= "<p>" . $this->m_FileName . " is larger than the required " . $fileSizeString . " . Image must be ";
            $this->m_Message .= $fileSizeString . "  or less than " . $fileSizeString . ' in size. <span class="icon error"></span><p>';
            $this->m_errorCode = 1;
            return false;
        }

        $this->m_Message .= '<p> ' . $this->m_FileName . ' Size ' . sfUtils::formatSizeUnits($this->m_FileSize);
        $this->m_Message .= ' is Okay <span class="icon success"></span> </p>';

        if (!in_array($this->m_FileType, $this->m_ImageTypesList) && !in_array($this->m_FileExtension, $this->m_ImageExtList)) {
            $this->m_Message .= "<p>Invalid file. Only <strong>" . implode(",", $this->m_ImageExtList);
            $this->m_Message .='</strong> images accepted for upload. <span class="icon error"></span></p>';
            $this->m_errorCode = 2;
            return false;
        }
        // Check for the Image type and its extension
        $this->m_Message .= "<p> " . $this->m_FileName . " Type " . $this->m_FileExtension;
        $this->m_Message .= ' is Okay <span class="icon success"></span> </p>';

        //echo "1 -> I was here in the make image section<br>";//DEBUG
        // Check if the image Exists
        if (file_exists($this->m_UploadImgDir . $this->m_FileName)) {
            $this->m_Message .= " <p>" . $this->m_FileName;
            $this->m_Message .= ' File already exists. <span class="icon error"></span></p>';
            //echo "2 -> I was here in the image Exits Section<br>";//DEBUG
            $this->m_errorCode = 3;
            return false;
        }

        $this->m_TargetFileImageLoc = $this->m_UploadImgDir . $this->m_TargetFileName;
        $this->m_TargetFileThumbLoc = $this->m_UploadThumbDir . $this->m_TargetFileName;

        // Move the original image from the temporary directory to the our default image Directory
        $isMoved = move_uploaded_file($this->m_FileTmpName, $this->m_TargetFileImageLoc);
        if (!$isMoved) {
            $this->m_Message .= " <p>" . $this->m_FileName;
            $this->m_Message .= ' File Could not be moved. <span class="icon error"></span></p>';
            //echo "2 -> I was here in the image Exits Section<br>";//DEBUG
            $this->m_errorCode = 4;
            return false;
        }

        chmod($this->m_TargetFileImageLoc, 0777); // give altimate permissions
        // if we havent set the thumbnail width and height
        // meaning tha we havent uploaded this image for gallery purposes 
        // because gallery images always have a thumb version
        if (!$this->m_thumbHeight && !$this->m_thumbHeight) {
            $this->m_File_is_Uploaded = True;
            $this->m_Message .= "<p>" . $this->m_FileName . ' Upload successful.. <span class="icon success"></span> </p>';
            return true;
        }

        //echo "3 -> I was here in the image dont Exits Section and move file<br> saved at ".$this->m_TargetFileImageLoc." <br>";//DEBUG
        // get the width and the height of the image
        $width = self::getImageWidth($this->m_TargetFileImageLoc);
        $height = self::getImageHeight($this->m_TargetFileImageLoc);

        //echo "3 -> image width and height ".$width." And ". $height. " <br>";//DEBUG
        //Scale the image if it is greater than the width or lesser that width scale to maximum width
        $scale = $this->m_MaxImageWidth / $width;
        $uploaded = $this->resizeImage($this->m_TargetFileImageLoc, $width, $height, $scale);

        $this->m_File_is_Uploaded = True;
        $_SESSION['ImageFile'] = $this->m_TargetFileImageLoc;
        $_SESSION['ImageThumbFile'] = $this->m_TargetFileThumbLoc;
        $_SESSION['ImageFiles'][] = $this->m_TargetFileImageLoc;
        $_SESSION['ImageThumbFiles'][] = $this->m_TargetFileThumbLoc;
        //echo "4 -> I was here in the image Has been saved sucessfully section<br> saved at ".$_SESSION['ImageFile'] ." <br>";//DEBUG
        $this->m_Message .= "<p>" . $this->m_FileName . ' Upload successful.. <span class="icon success"></span> </p>';

        // now automatically Create a thumbnail file
        //Get the new coordinates to crop the image.
        if (($width > $this->m_thumbWidth) && ($height > $this->m_thumbHeight)) {
            //Scale the image to the thumb_width set above
            $scale = $this->m_thumbWidth / $this->m_thumbWidth;

            $cropped = $this->resizeThumbnailImage($this->m_TargetFileThumbLoc, $this->m_TargetFileImageLoc, $this->m_thumbWidth, $this->m_thumbHeight, 0, 0, $scale);
            if ($cropped)
                $this->m_Thumb_is_Uploaded = True;
        } elseif (($width > $this->m_thumbWidth) && ($height < $this->m_thumbHeight)) {
            //Scale the image to the thumb_width set above
            $scale = $this->m_thumbWidth / $this->m_thumbWidth;
            $cropped = $this->resizeThumbnailImage($this->m_TargetFileThumbLoc, $this->m_TargetFileImageLoc, $this->m_thumbWidth, $height, 0, 0, $scale);
            if ($cropped)
                $this->m_Thumb_is_Uploaded = True;
        } elseif (($width < $this->m_thumbWidth) && ($height > $this->m_thumbHeight)) {
            //Scale the image to the thumb_width set above
            $scale = $this->m_thumbWidth / $this->m_thumbWidth;
            $cropped = $this->resizeThumbnailImage($this->m_TargetFileThumbLoc, $this->m_TargetFileImageLoc, $width, $this->m_thumbHeight, 0, 0, $scale);
            if ($cropped)
                $this->m_Thumb_is_Uploaded = True;
        } elseif (($width < $this->m_thumbWidth) && ($height < $this->m_thumbHeight)) {
            //Scale the image to the thumb_width set above
            $scale = $this->m_thumbWidth / $this->m_thumbWidth;
            $cropped = $this->resizeThumbnailImage($this->m_TargetFileThumbLoc, $this->m_TargetFileImageLoc, $width, $height, 0, 0, $scale);
            if ($cropped)
                $this->m_Thumb_is_Uploaded = True;
        }
        $Caption = "";

        if (empty($_REQUEST["Caption"]))
            $Caption = addslashes($this->m_FileBaseName);
        else
            $Caption = addslashes($_REQUEST["Caption"]);

        $_SESSION['ImageCaptions'][] = $Caption;
        $_SESSION['ImageCaption'] = $Caption;

        return true;
    }

}

class sfImageProcessor {

    public static function UploadMultiImages($imageFiles, $inifile = '../config/config.ini', &$message = "") {
        //Check if the image field is not empty
        if (!empty($imageFiles)) {
            $TotalFiles = count($imageFiles['name']);
            $sucessCount = 0;
            $failureCount = 0;

            for ($i = 0; $i < $TotalFiles; $i++) {
                $FileName = $imageFiles["name"][$i];
                $FileTmpName = $imageFiles['tmp_name'][$i];
                $FileSize = $imageFiles['size'][$i];
                $FileType = $imageFiles['type'][$i];

                $sfimage = new sfGalleryImage($inifile);
                $sfimage->init($FileName, $FileTmpName, $FileSize, $FileType);
                $sfimage->UploadImage();

                //$message.= $i."   |Name = ".$FileName."| Temp Name = " .$FileTmpName."| File Size = ".  sfUtils::formatSizeUnits($FileSize)."| File Type = ".$FileType;// DEBUG
                // $message.= "| Base Name = ".$FileBaseName."| File Extension = ".$FileExtension." AND Max Size =".  formatSizeUnits($sfimage->m_MaxSize)."<br />";// DEBUG
                //echo  $sfimage->m_Message."<br>";
                switch ($sfimage->m_errorCode) {
                    case 0:
                        $sucessCount++;
                        break;
                    default :
                        $failureCount++;
                        break;
                }
                //TODO log this message
                //$message.= $sfimage->m_Message . "<br>";
            }
            if ($sucessCount > 0 || $failureCount > 0) {
                $message.='<p><strong>[' . $sucessCount . ']</strong> Sucessful <span class="icon success"></span> ';
                $message.='<strong>[' . $failureCount . ']</strong> Unsucessful <span class="icon error"></span><p>';
            }
        } else {
            $message .= "<p>Please select an image to upload.<p>";
        }

        //echo $message;
        return $message;
    }

    public static function uploadSingleImage($imageFile, $inifile = '../config/config.ini', &$imageLoc = "", &$message = "", $forGallery = true) {

        //Check if the image field is not empty
        if (!empty($imageFile)) {
            $sucessCount = 0;
            $failureCount = 0;

            $FileName = $imageFile["name"];
            $FileTmpName = $imageFile['tmp_name'];
            $FileSize = $imageFile['size'];
            $FileType = $imageFile['type'];

            $sfimage = new sfGalleryImage($inifile, $forGallery);
            $sfimage->init($FileName, $FileTmpName, $FileSize, $FileType);
            if (!$sfimage->UploadImage()) {
                $message.= $sfimage->m_Message . "<br>";
                return false;
            }

            $imageLoc = $sfimage->m_TargetFileName;
            //$message.= $i."   |Name = ".$FileName."| Temp Name = " .$FileTmpName."| File Size = ".formatSizeUnits($FileSize)."| File Type = ".$FileType;// DEBUG
            //$message.= "| Base Name = ".$FileBaseName."| File Extension = ".$FileExtension." AND Max Size =".  formatSizeUnits($sfimage->m_MaxSize)."<br />";// DEBUG
            //$message.  $sfimage->m_Message."<br>";
            switch ($sfimage->m_errorCode) {
                case 0:
                    $sucessCount++;
                    break;
                default :
                    $failureCount++;
                    break;
            }
            $message.= $sfimage->m_Message;

            $message.='<p><strong>[' . $sucessCount . ']</strong> Sucessful <span class="icon success"></span> ';
            if ($failureCount > 0) {
                $message.='<strong>[' . $failureCount . ']</strong> Unsucessful <span class="icon error"></span>';
            }
            $message.='</p>';
        } else {
            $message .= "<p>Please select an image to upload.<p>";
            return false;
        }
        return true;
    }

    public static function saveThumbImage() {

        $x1 = $_POST["x1"];
        $y1 = $_POST["y1"];
        $x2 = $_POST["x2"];
        $y2 = $_POST["y2"];
        $w = $_POST["w"];
        $h = $_POST["h"];
        $Caption = $_POST["Caption"];
        $TargetFileImageLoc = $_POST["TargetFileImageLoc"];
        $TargetFileThumbLoc = $_POST["TargetFileThumbLoc"];
        $key = array_search($TargetFileImageLoc, $_SESSION['ImageFiles']);
        $_SESSION['ImageCaptions'][$key] = $Caption;

        //Scale the image to the thumb_width set above
        $scale = $_POST["thumbWidth"] / $w;
        if (!sfGalleryImage::resizeThumbnailImage($TargetFileThumbLoc, $TargetFileImageLoc, $w, $h, $x1, $y1, $scale)) {
            return false;
        }

        unset($_SESSION['ImageFile']);
        unset($_SESSION['ImageThumbFile']);
        unset($_SESSION['ImageCaption']);
        return true;
    }

    public static function ResetAll() {

        $_SESSION['ImageFiles'] = NULL;
        $_SESSION['ImageThumbFiles'] = NULL;
        $_SESSION['ImageFile'] = NULL;
        $_SESSION['ImageThumbFile'] = NULL;
        $_SESSION['ImageCaption'] = NULL;
        $_SESSION['ImageCaptions'] = NULL;

        unset($_SESSION['ImageFiles']);
        unset($_SESSION['ImageThumbFiles']);
        unset($_SESSION['ImageFile']);
        unset($_SESSION['ImageThumbFile']);
        unset($_SESSION['ImageCaption']);
        unset($_SESSION['ImageCaptions']);
    }

    public static function RemoveAll() {

        foreach ($_SESSION['ImageFiles'] as $DeleteimageLink)
            sfUtils::Deletefile($DeleteimageLink);

        foreach ($_SESSION['ImageThumbFiles'] as $DeleteThumbLink)
            sfUtils::Deletefile($DeleteThumbLink);

        self::ResetAll();

        return true;
    }

    public static function RemoveOne($Index) {

        if (!$Index)
            return false;

        if (!sfUtils::Deletefile($_SESSION['ImageFiles'][$Index]))
            return false;

        if (!sfUtils::Deletefile($_SESSION['ImageThumbFiles'][$Index]))
            return false;

        unset($_SESSION['ImageFiles'][$Index]);
        unset($_SESSION['ImageThumbFiles'][$Index]);
        unset($_SESSION['ImageCaptions'][$Index]);

        return true;
    }

    public static function deleteFileDBGallery($conn, $galleryID, $inifile = '../config/config.ini') {

        if (!$conn || !$galleryID)
            return false;

        $settingsConfig = Config::getConfig("settings", $inifile);
        $query_rsSFGallery = "SELECT id,thumb_name,image_name FROM snowflakes_gallery WHERE id=" . $galleryID;
        $conn->fetch($query_rsSFGallery);

        $result = $conn->getResultArray();
        $deleteSFGallery = $result[0];
        // Get all the image name from database
        $_SESSION['ImageFiles'] = explode(",", $deleteSFGallery['image_name']);
        $_SESSION['ImageThumbFiles'] = explode(",", $deleteSFGallery['thumb_name']);
        // Loop through the array and add directory prefix to each item in array
        foreach ($_SESSION['ImageFiles'] as &$value) {
            $value = $settingsConfig['galleryImgDir'] . $value;
        }

        // Loop through the array and add directory prefix to each item in array	
        foreach ($_SESSION['ImageThumbFiles'] as &$value) {
            $value = $settingsConfig['galleryThumbDir'] . $value;
        }

        //remove all images in the physical location
        self::RemoveAll();

        sfUtils::deleteGallery($conn, $galleryID);
    }

    /* tool to resize images stored in the gallery tables */

    public static function resizeGalleryImages($conn, $inifile = '../config/config.ini') {

        if (!$conn) {
            return false;
        }

        //The upload directory
        $settingsConfig = Config::getConfig("settings", $inifile);
        //The upload Image directory
        $UploadImgDir = $settingsConfig['galleryImgDir'];
        $maxImageWidth = $settingsConfig['maxImageWidth'];

        $sql = "SELECT image_name FROM snowflakes_gallery";
        $conn->fetch($sql);
        $row_rsImages = $conn->getResultArray();
        $row_total = $conn->recordCount();

        $i = 0;
        $resized = 0;
        do {
            $oneRec = explode(",", $row_rsImages[$i]['image_name']);
            foreach ($oneRec as $value) {
                $m_TargetFileImageLoc = $UploadImgDir . $value;
                // get the width and the height of the image
                $width = sfGalleryImage::getImageWidth($m_TargetFileImageLoc);
                $height = sfGalleryImage::getImageHeight($m_TargetFileImageLoc);
                //Scale the image if it is greater than the width or lesser that width scale to maximum width
                if ($width >= $maxImageWidth) {
                    continue;
                }

                $scale = $maxImageWidth / $width;
                sfGalleryImage::resizeImage($m_TargetFileImageLoc, $width, $height, $scale);
                $resized++;
            }

            $i++;
        } while ($i < $row_total);

        return $resized;
    }

    public static function makeCover($index) {
        if (sfUtils::isEmpty($index) || empty($_SESSION['ImageFiles'])) {
            return false;
        }
        $imagefile = $_SESSION['ImageFiles'][$index];
        $imagethumb = $_SESSION['ImageThumbFiles'][$index];
        $imagecaption = $_SESSION['ImageCaptions'][$index];
        unset($_SESSION['ImageFiles'][$index]);
        unset($_SESSION['ImageThumbFiles'][$index]);
        unset($_SESSION['ImageCaptions'][$index]);
        $_SESSION['ImageFiles'][] = $imagefile;
        $_SESSION['ImageThumbFiles'][] = $imagethumb;
        $_SESSION['ImageCaptions'][] = $imagecaption;
    }

    /* Clean up upload directory and remove files that are not in all the tables */

    public static function cleanUploadDir($conn, $inifile = '../config/config.ini') {

        //sanity Check
        if (!$conn) {
            return false;
        }

        //The upload directory
        $settingsConfig = Config::getConfig("settings", $inifile);
        $UploadDir = $settingsConfig['uploadGalleryDir'];
        //The upload Image directory
        $UploadImgDir = $settingsConfig['galleryImgDir'];
        $UploadThumbDir = $settingsConfig['galleryThumbDir'];

        $cleaned = 0;

        $dirImageList = scandir($UploadDir); //get all the filenames in the upload 
        //var_dump($dirImageList);

        $dbImageList = array();

        $sql = "SELECT image_name FROM snowflakes";
        $conn->fetch($sql);
        $row_rsImages = $conn->getResultArray();
        $row_total = $conn->recordCount();

        $i = 0;
        do {
            $dbImageList [] = $row_rsImages[$i]['image_name'];
            $i++;
        } while ($i < $row_total);

        $sql = "SELECT image_name FROM snowflakes_users";
        $conn->fetch($sql);
        $row_rsImages = $conn->getResultArray();
        $row_total = $conn->recordCount();

        $i = 0;
        do {
            $dbImageList [] = $row_rsImages[$i]['image_name'];
            $i++;
        } while ($i < $row_total);

        $sql = "SELECT image_name FROM snowflakes_events";
        $conn->fetch($sql);
        $row_rsImages = $conn->getResultArray();
        $row_total = $conn->recordCount();

        $i = 0;
        do {
            $dbImageList [] = $row_rsImages[$i]['image_name'];
            $i++;
        } while ($i < $row_total);

        $remaining = array_diff($dirImageList, $dbImageList);
        foreach ($remaining as $value) {
            if (!is_dir($UploadDir . $value)) {
                sfUtils::Deletefile($UploadDir . $value);
                $cleaned++;
            }
        }

        $sql = "SELECT image_name FROM snowflakes_gallery";
        $conn->fetch($sql);
        $row_rsImages = $conn->getResultArray();
        $row_total = $conn->recordCount();

        $i = 0;
        do {
            $oneRec = explode(",", $row_rsImages[$i]['image_name']);
            foreach ($oneRec as $key => $value) {
                $dbImageList [] = $value;
            }

            $i++;
        } while ($i < $row_total);

        $dbImageList = array_unique($dbImageList);
        $dirImageList = scandir($UploadImgDir);
        $remaining = array_diff($dirImageList, $dbImageList);
        foreach ($remaining as $value) {
            if (!is_dir($UploadImgDir . $value)) {
                sfUtils::Deletefile($UploadImgDir . $value);
                $cleaned++;
            }
        }

        $dirImageList = scandir($UploadThumbDir);
        $remaining = array_diff($dirImageList, $dbImageList);
        foreach ($remaining as $value) {
            if (!is_dir($UploadThumbDir . $value)) {
                sfUtils::Deletefile($UploadThumbDir . $value);
                $cleaned++;
            }
        }
        return $cleaned;
    }

    public static function UndoChanges() {

        ///loop through the database image files
        foreach ($_SESSION['DBImageFiles'] as $key => $containValue) {
            /// check id the value of image files in the database exist in the imagefiles
            if (in_array($containValue, $_SESSION['ImageFiles'])) {
                sfUtils::removeElement($_SESSION['ImageFiles'], $key); // successfully remove an element from the array
            }
        }
        //print_r($_SESSION['ImageFiles']);
        //echo "<br />";
        ///loop through the database image files
        foreach ($_SESSION['DBImageThumbFiles'] as $key => $containValue) {
            /// check id the value of image files in the database exist in the imagefiles
            if (in_array($containValue, $_SESSION['ImageThumbFiles'])) {
                sfUtils::removeElement($_SESSION['ImageThumbFiles'], $key); // successfully remove an element from the array
            }
        }


        //print_r($_SESSION['ImageThumbFiles']);
        //echo "<br />";
        ///loop through the database image files
        foreach ($_SESSION['DBImageCaptions'] as $key => $containValue) {
            /// check id the value of image files in the database exist in the imagefiles
            if (in_array($containValue, $_SESSION['ImageCaptions'])) {
                remove_element($_SESSION['ImageCaptions'], $key); // successfully remove an element from the array
            }
        }

        //print_r($_SESSION['ImageCaptions']);
        //echo "<br />";
        return self::RemoveAll();
    }

}

?>
