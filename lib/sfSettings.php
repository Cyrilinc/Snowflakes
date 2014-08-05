<?php
require_once 'sf.php';

class sfSettings
{

    //Db Info           //config Name [db]
    var $m_hostName;    //host
    var $m_dbName;      //dbname
    var $m_dbType;      //type
    var $m_dbUsername;  //username
    var $m_dbPassword;  //password
    var $m_key;         //key
    var $m_admin_email; //admin_email
    var $m_time_zone;   //time_zone
    //Settings Info     //[settings]
    var $m_setUp;         //Setup
    var $m_url;         //url
    var $m_sfUrl;       //m_sfUrl
    var $m_loginUrl;    //loginUrl
    var $m_flakeItUrl;  //flakeItUrl
    var $m_sfGalleryUrl; //m_sfGalleryUrl
    var $m_sfGalleryImgUrl; //m_sfGalleryImgUrl
    var $m_sfGalleryThumbUrl; //m_sfGalleryThumbUrl
    var $m_thumbWidth; //thumbWidth
    var $m_thumbHeight; //thumbHeight
    var $m_maxImageWidth; //maxImageWidth
    var $m_imageExtList; //imageExtList
    var $m_imageTypesList; //imageTypesList
    var $m_snowflakesResultUrl; //snowflakesResultUrl// One snowflakes result
    var $m_snowflakesOutUrl; // snowflakesOutUrl     // All snowflakes output
    var $m_eventsResultUrl; //eventsResultUrl        // One event result
    var $m_eventsOutputUrl; //eventsOutputUrl        //All event output
    var $m_galleryResultUrl; //galleryResultUrl      // One gallery result
    var $m_galleryOutUrl; //galleryOutUrl           //All gallery output
    var $m_maxImageSize; //maxImageSize
    //datadir Info           //config Name [datadir]
    var $m_logdir;  //logdir
    var $m_path;        //path
    var $m_resources; //resources
    var $m_uploadGalleryDir; //uploadGalleryDir
    var $m_galleryImgDir; //galleryImgDir
    var $m_galleryThumbDir; //galleryThumbDir
    var $m_settingsarray;

    /**
     * Initialize the data directory parameter by loading data from the 
     * configuration file 
     * 
     * @param String $inifile the ini config file for snowflakes API
     */
    public function __construct($inifile)
    {
        if ($inifile)
        {
            $this->init($inifile);
        }
    }

    /**
     * Initialize the settings struct by loading data from the config file
     *
     * @param String $inifile the ini config file for snowflakes API
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function init($inifile = '../config/config.ini')
    {
        // create ini file if it doesn't exists
        Config::createConfig($inifile, true);

        $m_data = Config::getConfig(null, $inifile);
        $this->m_settingsarray = array();
        return $this->populate($m_data);
    }

    /**
     * Populate each member of {@link sfSettings} given the input parameters
     *
     * @param array $array to be used to populate members of {@link sfSettings}
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function populate($array)
    {
        if (empty($array))
        {
            return false;
        }
        $this->m_settingsarray = $array;

        //Db Info           //config Name [db]
        $this->m_hostName = $this->m_settingsarray['db']['host'];
        $this->m_dbName = $this->m_settingsarray['db']['dbname'];
        $this->m_dbType = $this->m_settingsarray['db']['type'];
        $this->m_dbUsername = $this->m_settingsarray['db']['username'];
        $this->m_dbPassword = $this->m_settingsarray['db']['password'];
        $this->m_key = $this->m_settingsarray['db']['key'];
        $this->m_admin_email = $this->m_settingsarray['db']['admin_email'];
        $this->m_time_zone = $this->m_settingsarray['db']['time_zone'];
        //Settings Info     //[settings]
        $this->m_setUp = $this->m_settingsarray['settings']['Setup'];
        $this->m_url = $this->m_settingsarray['settings']['url'];
        $this->m_sfUrl = $this->m_settingsarray['settings']['m_sfUrl'];
        $this->m_loginUrl = $this->m_settingsarray['settings']['loginUrl'];
        $this->m_flakeItUrl = $this->m_settingsarray['settings']['flakeItUrl'];
        $this->m_sfGalleryUrl = $this->m_settingsarray['settings']['m_sfGalleryUrl'];
        $this->m_sfGalleryImgUrl = $this->m_settingsarray['settings']['m_sfGalleryImgUrl'];
        $this->m_sfGalleryThumbUrl = $this->m_settingsarray['settings']['m_sfGalleryThumbUrl'];
        $this->m_thumbWidth = $this->m_settingsarray['settings']['thumbWidth'];
        $this->m_thumbHeight = $this->m_settingsarray['settings']['thumbHeight'];
        $this->m_maxImageWidth = $this->m_settingsarray['settings']['maxImageWidth'];
        $this->m_imageExtList = $this->m_settingsarray['settings']['imageExtList'];
        $this->m_imageTypesList = $this->m_settingsarray['settings']['imageTypesList'];
        $this->m_snowflakesResultUrl = $this->m_settingsarray['settings']['snowflakesResultUrl'];
        $this->m_snowflakesOutUrl = $this->m_settingsarray['settings']['snowflakesOutUrl'];
        $this->m_eventsResultUrl = $this->m_settingsarray['settings']['eventsResultUrl'];
        $this->m_eventsOutputUrl = $this->m_settingsarray['settings']['eventsOutputUrl'];
        $this->m_galleryResultUrl = $this->m_settingsarray['settings']['galleryResultUrl'];
        $this->m_galleryOutUrl = $this->m_settingsarray['settings']['galleryOutUrl'];
        $this->m_maxImageSize = $this->m_settingsarray['settings']['maxImageSize'];
        //datadir Info           //config Name [datadir]
        $this->m_logdir = $this->m_settingsarray['datadir']['logdir'];
        $this->m_resources = $this->m_settingsarray['datadir']['resources'];
        $this->m_path = $this->m_settingsarray['datadir']['path'];
        $this->m_uploadGalleryDir = $this->m_settingsarray['datadir']['uploadGalleryDir'];
        $this->m_galleryImgDir = $this->m_settingsarray['datadir']['galleryImgDir'];
        $this->m_galleryThumbDir = $this->m_settingsarray['datadir']['galleryThumbDir'];


        return true;
    }

    /**
     * Stores all the new configuration set into the configuration file
     * 
     * @param String $inifile <p> The configuration file </p>
     * 
     * @return mixed The <b>configuration data </b> in form of an array on success or <b>FALSE</b> on failure.
     */
    public function setConfigItems($inifile = '../config/config.ini')
    {
        if (empty($this->m_settingsarray))
        {
            return false;
        }
        return Config::saveConfig($this->m_settingsarray, $inifile);
    }

    /**
     * sets the database host name of the db in the  configuration file 
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     */
    public function SethostName($value)
    {
        //host
        $this->m_settingsarray["db"]["host"] = $value;
    }

    /**
     * sets the database name of the db in the  configuration file 
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     */
    public function SetdbName($value)
    {
        //dbname
        $this->m_settingsarray["db"]["dbname"] = $value;
    }

    /**
     * sets the database type of the db in the  configuration file 
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     */
    public function SetdbType($value)
    {
        //type
        $this->m_settingsarray["db"]["type"] = $value;
    }

    /**
     * sets the database username of the db in the  configuration file 
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     */
    public function SetdbUsername($value)
    {
        //username
        $this->m_settingsarray["db"]["username"] = $value;
    }

    /**
     * sets the database password of the db in the  configuration file 
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     * @param String $key <p> The password encryption key for the password</p> 
     * @param bool $encrypt <p> The flag to detemine if the password should be encrypted or not</p> 
     */
    public function SetdbPassword($value, $key = "", $encrypt = true)
    {
        if (!$value)
        {
            return false;
        }
        //password
        if ($key !== "" && $encrypt == 'Y')
        {
            $this->m_key = $key;
            $password = sfUtils::encrypt($value, $this->m_key);
            if ($password == $value) // if the encryption returns the same value
            {
                $encrypt = 'N';
            }
            else
            {
                $this->m_settingsarray["db"]["key"] = $key;
            }
        }
        else
        {
            $password = $value;
        }
        $this->m_settingsarray["db"]["isenc"] = $encrypt == 'Y' ? $encrypt : 'N';
        $this->m_settingsarray["db"]["password"] = $password;
    }

    /**
     * sets the administration email of the db in the  configuration file
     * 
     * @param String $value <p> The value of  configuration element to set</p>  
     */
    public function Setadmin_email($value)
    {
        //admin_email
        $this->m_settingsarray["db"]["admin_email"] = $value;
    }

    /**
     * sets the site time zone of the db in the  configuration file 
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     */
    public function SettimeZone($value)
    {
        //time_zone
        $this->m_settingsarray["db"]["time_zone"] = $value;
    }

    //Settings Info     //[settings]

    /**
     * sets the snowflakes installation url of the settings in the  configuration file
     * 
     * @param String $value <p> The value of  configuration element to set</p>  
     */
    public function Seturl($value)
    {
        //url
        $this->m_settingsarray["settings"]["url"] = $value;
    }

    /**
     * sets the snowflake url of the settings in the  configuration file 
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     */
    public function SetsfUrl($value)
    {
        //m_sfUrl
        $this->m_settingsarray["settings"]["m_sfUrl"] = $value;
    }

    /**
     * sets the snowflake gallery url of the settings in the  configuration file
     * 
     * @param String $value <p> The value of  configuration element to set</p>  
     */
    public function SetsfGalleryUrl($value)
    {
        //m_sfGalleryUrl
        $this->m_settingsarray["settings"]["m_sfGalleryUrl"] = $value;
    }

    /**
     * sets the snowflakes Gallery image url of the settings in the  configuration file 
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     */
    public function SetsfGalleryImgUrl($value)
    {
        //m_sfGalleryImgUrl
        $this->m_settingsarray["settings"]["m_sfGalleryImgUrl"] = $value;
    }

    /**
     * sets the snowflake gallery thumbnail image url of the settings in the  
     * configuration file 
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     */
    public function SetsfGalleryThumbUrl($value)
    {
        //m_sfGalleryThumbUrl 
        $this->m_settingsarray["settings"]["m_sfGalleryThumbUrl"] = $value;
    }

    /**
     * sets the snowflake gallery thumbnail image width of the settings in the  
     * configuration file 
     * 
     * @param int $value <p> The value of  configuration element to set</p> 
     */
    public function SetthumbWidth($value)
    {
        //thumbWidth
        $this->m_settingsarray["settings"]["thumbWidth"] = $value;
    }

    /**
     * sets the snowflake gallery thumbnail image height of the settings in the
     * configuration file 
     * 
     * @param int $value <p> The value of  configuration element to set</p> 
     */
    public function SetthumbHeight($value)
    {
        //thumbHeight
        $this->m_settingsarray["settings"]["thumbHeight"] = $value;
    }

    /**
     * sets the snowflake gallery maximum image width of the settings in the  
     * configuration file 
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     */
    public function SetmaxImageWidth($value)
    {
        //maxImageWidth
        $this->m_settingsarray["settings"]["maxImageWidth"] = $value;
    }

    /**
     * sets the snowflake gallery supported image extesion list of the settings 
     * in the  configuration file 
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     */
    public function SetimageExtList($value)
    {
        //imageExtList
        $this->m_settingsarray["settings"]["imageExtList"] = $value;
    }

    /**
     * sets the snowflake gallery supported image type list  of the settings 
     * in the  configuration file 
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     */
    public function SetimageTypesList($value)
    {
        //imageTypesList
        $this->m_settingsarray["settings"]["imageTypesList"] = $value;
    }

    /**
     * sets the snowflakes output url where snowflakes audience can see one 
     * snowflakes published of the settings in the  configuration file 
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     */
    public function SetsnowflakesResultUrl($value)
    {
        //snowflakesResultUrl   // One snowflakes result
        $this->m_settingsarray["settings"]["snowflakesResultUrl"] = $value;
    }

    /**
     * sets the snowflakes output url where snowflakes audience can see all 
     * snowflakes published of the settings in the  configuration file 
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     */
    public function SetsnowflakesOutUrl($value)
    {
        // snowflakesOutUrl     // All snowflakes output
        $this->m_settingsarray["settings"]["snowflakesOutUrl"] = $value;
    }

    /**
     * sets the events output url where snowflakes audience can see one 
     * snowflakes published of the settings in the  configuration file 
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     */
    public function SeteventsResultUrl($value)
    {
        //eventsResultUrl        // One event result
        $this->m_settingsarray["settings"]["eventsResultUrl"] = $value;
    }

    /**
     * sets the events output url where snowflakes audience can see all 
     * snowflakes published of the settings in the  configuration file 
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     */
    public function SeteventsOutputUrl($value)
    {
        //eventsOutputUrl        //All event output
        $this->m_settingsarray["settings"]["eventsOutputUrl"] = $value;
    }

    /**
     * sets the gallery output url where snowflakes audience can see one 
     * snowflakes published of the settings in the  configuration file 
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     */
    public function SetgalleryResultUrl($value)
    {
        //galleryResultUrl      // One gallery result
        $this->m_settingsarray["settings"]["galleryResultUrl"] = $value;
    }

    /**
     * sets the gallery output url where snowflakes audience can see all 
     * snowflakes published of the settings in the  configuration file
     * 
     * @param String $value <p> The value of  configuration element to set</p>  
     */
    public function SetgalleryOutUrl($value)
    {
        //galleryOutUrl           //All gallery output

        $this->m_settingsarray["settings"]["galleryOutUrl"] = $value;
    }

    /**
     * sets the maximum image size allowed for upload of the settings in the  
     * configuration file 
     * 
     * @param int $value <p> The value of  configuration element to set</p> 
     */
    public function SetmaxImageSize($value)
    {
        //maxImageSize
        $this->m_settingsarray["settings"]["maxImageSize"] = sfUtils::toByteSize($value);
    }

    /**
     * Adds a new configuration element to the configuration file
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     * @param String $tag <p> The tag/element name of the configuration element to set </p>
     * @param String $section <p> The tag/element header name of the configuration element to set </p> 
     * 
     */
    public function setCustom($section, $tag, $value)
    {
        $this->m_settingsarray[$section][$tag] = $value;
    }

    /**
     * sets the data path of the datadir in the  configuration file 
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     */
    public function Setpath($value)
    {
        //path
        $this->m_settingsarray["datadir"]["path"] = $value;
    }

    /**
     * sets the data resources of the datadir in the  configuration file 
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     */
    public function Setresources($value)
    {
        //resources
        $this->m_settingsarray["datadir"]["resources"] = $value;
    }

    /**
     * sets the gallery upload directory of the datadir in the  configuration file 
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     */
    public function SetuploadGalleryDir($value)
    {
        //uploadGalleryDir
        $this->m_settingsarray["datadir"]["uploadGalleryDir"] = $value;
    }

    /**
     * sets the gallery image upload directory of the datadir in the  configuration file
     * 
     * @param String $value <p> The value of  configuration element to set</p>  
     */
    public function SetgalleryImgDir($value)
    {
        //galleryImgDir
        $this->m_settingsarray["datadir"]["galleryImgDir"] = $value;
    }

    /**
     * sets the gallery image thumbnail upload directory of the datadir in the  configuration file 
     * 
     * @param String $value <p> The value of  configuration element to set</p> 
     */
    public function SetgalleryThumbDir($value)
    {
        //galleryThumbDir
        $this->m_settingsarray["datadir"]["galleryThumbDir"] = $value;
    }

}

