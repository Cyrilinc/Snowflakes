<?php

include_once '../lib/sf.php';
include_once '../lib/sfConnect.php';
include_once '../config/Config.php';

class Snowflakes {

    var $m_hostName;
    var $m_dbUsername;
    var $m_dbPassword;
    var $m_dbName;
    var $m_dbType; // MySQL,SQLite,MSSQL, Sybase
    var $m_adminUsername;
    var $m_adminPassword;
    var $m_adminEmail;
    var $m_returnUrl;
    var $m_logInUrl;
    var $m_sfUrl;
    var $m_sfMigrationDb;
    var $m_timeZone;
    public $m_Message = "<b>Snowflakes </b> <br>";
    public $m_outcomeMessage = "<b>Snowflakes </b> <br>";

    /*
     * Tables for Snowflakes
     */
    public $m_sfTable = "snowflakes";

    /*
     * Tables for Users of Snowflakes
     */
    public $m_usersTable = "snowflakes_users";

    /*
     * Tables for Event Creation
     */
    public $m_eventsTable = "snowflakes_events";
    /*
     * Tables for Gallery Creation
     */
    public $m_galleryTable = "snowflakes_gallery";

    /*
     * Tables for Snowflakes settings Creation
     */
    public $m_settingTable = "snowflakes_settings";

    /*
     * Tables for Snowflakes flake it Creation
     */
    public $m_flakeItTable = "snowflakes_flakeit";
    /*
     * Tables for Snowflakes change log Creation
     */
    public $m_changeLogTable = "snowflakes_change_log";
    public $m_connection;

    public function Setup() {
        if (!$this->connect()) {
            $this->m_outcomeMessage='<div class="SnowflakeHead">Set Up Unsuccessful <img src="../resources/images/Icons/Cross.png" height="32" width="32" alt="cross!" /> </div>';
            return false;
        }
        $gallery = realpath("../Uploads/") . '/';
        $galleryimg = $gallery . "GalleryImages/";
        $gallerythumb = $gallery . "GalleryThumbs/";
        $loginUrl = str_replace("install/sfInstall.php", "login.php", sfUtils::curPageURL());
        $key="$this->m_hostName$this->m_dbName$this->m_dbType$this->m_dbUsername";
        $encryptedPassword=sfUtils::encrypt($this->m_adminPassword,$key);
        
        $sfConfig = "[db]\n" .
                'host = "' . $this->m_hostName . "\"\n" .
                'dbname = "' . $this->m_dbName . "\"\n" .
                'type = "' . $this->m_dbType . "\"\n" .
                'username = "' . $this->m_dbUsername . "\"\n" .
                'password = "' . $encryptedPassword . "\"\n" .
                'key = "'.$key."\"\n".
                'admin_email = "' . $this->m_adminEmail . "\"\n" .
                "time_zone = \"$this->m_timeZone\"\n\n" .
                "[settings]\n" .
                "Setup = \"True\"\n" .
                'url = "' . sfUtils::curPageURL() . "\"\n" .
                'loginUrl = "' . $loginUrl . "\"\n" .
                'path = "' . realpath("../") . "/\"\n" .
                'datapath = "' . realpath("../") . "/data/\"\n" .
                'm_sfUrl = "' . $this->m_sfUrl . "\"\n" .
                'm_sfGalleryUrl = "' . $this->m_sfUrl . "Uploads/\"\n" .
                'm_sfGalleryImgUrl = "' . $this->m_sfUrl . "Uploads/GalleryImages/\"\n" .
                'm_sfGalleryThumbUrl = "' . $this->m_sfUrl . "Uploads/GalleryThumbs/\"\n" .
                'm_sfProfileUrl = "' . $this->m_sfUrl . "Uploads/profile/\"\n" .
                'uploadGalleryDir = "' . $gallery . "\"\n" .
                'galleryImgDir = "' . $galleryimg . "\"\n" .
                'galleryThumbDir = "' . $gallerythumb . "\"\n" .
                'maxImageSize = "1048576"' . "\n" .
                'resources = "' . realpath("../resources/") . "/\"\n" .
                'thumbWidth = "250"' . "\n" .
                'thumbHeight = "250"' . "\n" .
                'maxImageWidth = "620"' . "\n" .
                'imageExtList = "jpeg,jpg,png,gif"' . "\n" .
                'imageTypesList = "image/pjpeg,image/jpg,image/png,image/gif,image/tiff,image/bmp"' . "\n\n" .
                "[datadir]\n" .
                'logdir = "' . realpath("../data/") . "/\"\n";

        $fp = fopen("../config/config.ini", "w");
        fwrite($fp, $sfConfig);
        fclose($fp);

        if (!$fp) {
            $this->m_Message .= '<br> Could not open or write CMS configuration file. <span class="icon error"></span><br />';
        } else {
            $this->m_Message .= '<br>CMS configuration file written successfully.<span class="icon success"></span><br />';
        }
        $this->m_outcomeMessage='<div class="SnowflakeHead">Set Up Successful <img src="../resources/images/Icons/Tick.png" height="32" width="32" alt="Tick" /></div>';
    }

    public function db_connect() {
        $sqlArray = array('type' => $this->m_dbType, 'host' => $this->m_hostName, 'username' => $this->m_dbUsername, 'password' => $this->m_dbPassword, 'database' => $this->m_dbName, 'datapath' => realpath("../") . "/data/");

        $conn = new sfConnect($sqlArray);
        $conn->connect(); // Connect to database via writer connection

        if ($conn->getStatus() == false) {
            $this->m_Message .="Database Connection Unsuccessful. Could not connect." . $conn->getMessage() . '<span class="icon error"></span> <br />';
            return false;
        }

        $this->m_Message .='<br>Database Connection Successful. <span class="icon success"></span><br />';

        if (!$this->createSfDB($conn)) {
            return false;
        }

        return $conn;
    }

    public function connect() {
        /// Check 1 Start connection
        $conn = $this->db_connect();

        if (!$conn) {
            return false;
        }

        // Create change log table 
        if ($this->createChangeLog($conn) == false) {
            return false;
        }

        //Create Flake it Table and trigger 
        if ($this->createFlakeItTable($conn) == false) {
            return false;
        }

        /// Check 2 Create Snowflakes table
        if ($this->createSfTable($conn) == false) {
            return false;
        }
        $this->insertDefaultPosts($conn);

        // Check 3 Create Admin
        if ($this->createUserTable($conn) == false) {
            return false;
        }
        $this->insertAdmin($conn);

        // Check 4  Create events
        if ($this->createEventsTable($conn) == false) {
            return false;
        }
        // Insert snowflakes events
        $this->insertDefaultEvents($conn);

        // Check 5  Create Gallery
        if ($this->createGalleryTable($conn) == false) {
            return false;
        }
        // Insert snowflakes gallery
        $this->insertDefaultGallery($conn);

        // Check 6  Create Snowflakes Setting
        if ($this->createSettingsTable($conn) == false) {
            return false;
        }
        // Insert Snowflakes settings
        $this->insertSnowflakesSettings($conn);

        return true;
    }

    private function createSfDB($conn) {
        //sanity check
        if (!$conn) {
            return false;
        }

        $sql = "CREATE DATABASE IF NOT EXISTS " . $this->m_dbName . ";";
        if (!$conn->execute($sql)) {
            $this->m_Message .= "<br/>Could not select/Create database" . $this->m_dbName . ".<br/> " . $conn->getMessage() . '<span class="icon error"></span> <br />';
            return false;
        }
        return true;
    }

    private function createChangeLog($conn) {
        //sanity check
        if (!$conn) {
            return false;
        }

        $sql = trim("CREATE TABLE IF NOT EXISTS " . $this->m_changeLogTable . " 
        (log_id             INTEGER NOT NULL AUTO_INCREMENT, 
	change_datetime     timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	change_action       enum('added','modified','requested to delete', 'deleted','logged on','logged off','published','unpublished') DEFAULT NULL,
	change_on           enum('snowflake','event','gallery','user') DEFAULT NULL, 
        action_id           int(11) NOT NULL DEFAULT 0, 
        created_by          VARCHAR(50) NOT NULL,
	change_by           VARCHAR(50) NOT NULL,
	PRIMARY KEY (log_id),
        KEY changeaction (change_action),
	KEY changeon (change_on),
	KEY changetime (change_datetime)
	)ENGINE = MYISAM;");

        if (!$conn->execute($sql)) {
            $this->m_Message .="<br>" . 'Change log table named "' . $this->m_changeLogTable . '" cannot be created.' . ".<br/> " . $conn->getMessage() . ' <span class="icon error"></span><br />';
            return false;
        }

        $this->m_Message .="<br>" . 'Change log table named "' . $this->m_sfTable . '" has been created.<span class="icon success"></span><br />';

        return true;
    }

    private function createIUDTriggers($conn, $tablename, $Name) {

        //sanity check
        if (!$conn || !$tablename || !$Name) {
            return false;
        }

        $sql = trim("DROP TRIGGER IF EXISTS " . $tablename . "_in_trig;");
        $sql .=trim("CREATE TRIGGER " . $tablename . "_in_trig AFTER INSERT ON " . $tablename . " 
            FOR EACH ROW
           BEGIN
                DECLARE log_action VARCHAR(20);
                DECLARE log_change_on VARCHAR(20);
                SET log_action='added', log_change_on= '" . $Name . "';
                INSERT INTO " . $this->m_changeLogTable . " (change_action,change_on,created_by,change_by,action_id)
                VALUES (log_action,log_change_on,NEW.created_by,NEW.edited_by,NEW.id);
                
                IF NEW.publish THEN
                    SET log_action = 'published';
                ELSE
                    SET log_action = 'unpublished'; 
                END IF;
                
                INSERT INTO " . $this->m_changeLogTable . " SET change_action=log_action,change_on=log_change_on,created_by=NEW.created_by,change_by=NEW.edited_by,action_id=NEW.id;
                INSERT INTO " . $this->m_flakeItTable . " SET flake_on=log_change_on,flake_it=NEW.flake_it,flake_on_id=NEW.id;
                
            END;");
        $sql .=trim("DROP TRIGGER IF EXISTS " . $tablename . "_up_trig;");
        $sql .=trim("CREATE TRIGGER " . $tablename . "_up_trig AFTER UPDATE ON " . $tablename . " 
            FOR EACH ROW
           BEGIN
                DECLARE log_action VARCHAR(20);
                DECLARE log_change_on VARCHAR(20);
                
                SET log_change_on='" . $Name . "';
                IF NEW.deleted THEN
                    SET log_action = 'requested to delete';
                    INSERT INTO " . $this->m_changeLogTable . " (change_action,change_on,created_by,change_by,action_id)
                    VALUES (log_action,log_change_on,NEW.created_by,NEW.edited_by,NEW.id);
                END IF;
                
                IF NEW.publish THEN
                    SET log_action = 'published';
                ELSE
                    SET log_action = 'unpublished'; 
                END IF;
                
                INSERT INTO " . $this->m_changeLogTable . " SET change_action=log_action,change_on=log_change_on,created_by=NEW.created_by,change_by=NEW.edited_by,action_id=NEW.id;
                UPDATE " . $this->m_flakeItTable . " SET flake_it=NEW.flake_it WHERE flake_on_id=NEW.id AND flake_on=log_change_on;
                
            END;");
        $sql .=trim("DROP TRIGGER IF EXISTS " . $tablename . "_del_trig;");
        $sql .=trim("CREATE TRIGGER " . $tablename . "_del_trig AFTER DELETE ON " . $tablename . " 
            FOR EACH ROW
           BEGIN
                DECLARE log_action VARCHAR(20);
                DECLARE log_change_on VARCHAR(20);
                SET log_action='deleted', log_change_on='" . $Name . "';
                INSERT INTO " . $this->m_changeLogTable . " SET change_action=log_action,change_on=log_change_on,created_by=OLD.created_by,change_by=OLD.edited_by,action_id=OLD.id;
                DELETE FROM " . $this->m_flakeItTable . " WHERE flake_on_id=OLD.id AND flake_on=log_change_on;
            END;");
        if (!$conn->execute($sql)) {
            $this->m_Message .='<br>Could not create ' . $Name . ' trigger due to error.' . ".<br/> " . $conn->getMessage() . ' <span class="icon error"></span><br />';
            return false;
        }
        $this->m_Message .='<br>' . $Name . ' trigger has been created.<span class="icon success"></span><br />';
        return true;
    }

    private function createSfTable($conn) {

        //sanity check
        if (!$conn) {
            return false;
        }

        $sql = trim("CREATE TABLE IF NOT EXISTS " . $this->m_sfTable . " 
        (id        INTEGER NOT NULL AUTO_INCREMENT, 
	title		VARCHAR(150), 
	body_text	TEXT,
	publish 	TINYINT(1),
	image_name	VARCHAR(300), 
	gallery         VARCHAR(500), 
	created		VARCHAR(100), 
	created_by      VARCHAR(50) NOT NULL,
        edited		VARCHAR(100), 
	edited_by       VARCHAR(50) NOT NULL,
        deleted         TINYINT(1) unsigned NOT NULL DEFAULT '0',
        flake_it        INT(11) NOT NULL DEFAULT 0,
	PRIMARY KEY (id),
        KEY (title),
        KEY (deleted),
        FULLTEXT snowflakes_search(title,created_by,edited_by,body_text)
	) ENGINE = MYISAM;");

        if (!$conn->execute($sql)) {
            $this->m_Message .="<br>" . 'CMS table named "' . $this->m_sfTable . '" cannot be created.' . ".<br/> " . $conn->getMessage() . ' <span class="icon error"></span><br />';
            return false;
        }

        $this->m_Message .="<br>" . 'CMS table named "' . $this->m_sfTable . '" has been created.<span class="icon success"></span><br />';

        return $this->createIUDTriggers($conn, $this->m_sfTable, 'snowflake');
    }

    private function createUserTable($conn) {

        //sanity check
        if (!$conn) {
            return false;
        }

        $sql = "CREATE TABLE IF NOT EXISTS " . $this->m_usersTable . " 
        (id       int(11) NOT NULL AUTO_INCREMENT,
        username        VARCHAR(50) NOT NULL,
        password        VARCHAR(50) NOT NULL,
        reset_link      VARCHAR(120) NOT NULL,
        email           VARCHAR(120) NOT NULL,
        access_level    int(4) NOT NULL,
        access_name     enum('Author/Editor','Publisher','Manager','Administrator','Super Administrator') DEFAULT NULL,
        image_name      VARCHAR(300) NOT NULL DEFAULT 'default.png',
        deleted         TINYINT(1) unsigned NOT NULL DEFAULT '0',
        flake_it        INT(11) NOT NULL DEFAULT 0,
        logged_in       TINYINT(1) unsigned NOT NULL DEFAULT '0',
        last_login      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        ip              VARCHAR(40) NOT NULL DEFAULT '',
	PRIMARY KEY (id),
        UNIQUE KEY user_email (username, email),
        KEY (deleted),
        FULLTEXT users_search(username,email)
	)ENGINE = MYISAM;";

        if (!$conn->execute($sql)) {
            $this->m_Message .='<br>Could not create users table named "' . $this->m_usersTable . '" due to error.' . ".<br/> " . $conn->getMessage() . ' <span class="icon error"></span><br />';
            return false;
        }

        $this->m_Message .='<br>Users table named "' . $this->m_usersTable . '" Has been Created.<span class="icon success"></span><br />';
        //echo $this->$Message;

        $sql = trim("DROP TRIGGER IF EXISTS " . $this->m_usersTable . "_in_trig;");
        $sql .=trim("CREATE TRIGGER " . $this->m_usersTable . "_in_trig AFTER INSERT ON " . $this->m_usersTable . " 
            FOR EACH ROW
           BEGIN
                DECLARE log_action VARCHAR(20);
                DECLARE log_change_on VARCHAR(20);
                SET log_action='added', log_change_on= 'user';
                INSERT INTO " . $this->m_changeLogTable . " (change_action,change_on,created_by,change_by,action_id)
                VALUES (log_action,log_change_on,NEW.username,NEW.username,NEW.id);
            END;");
        $sql .=trim("DROP TRIGGER IF EXISTS " . $this->m_usersTable . "_up_trig;");
        $sql .=trim("CREATE TRIGGER " . $this->m_usersTable . "_up_trig AFTER UPDATE ON " . $this->m_usersTable . " 
            FOR EACH ROW
           BEGIN
                DECLARE log_action VARCHAR(20);
                DECLARE log_change_on VARCHAR(20);
                
                IF NEW.deleted=1 THEN
                    SET log_action = 'deleted';
                ELSE
                    SET log_action = 'modified'; 
                END IF;
                
                SET log_change_on='user';
                INSERT INTO " . $this->m_changeLogTable . " (change_action,change_on,created_by,change_by,action_id)
                VALUES (log_action,log_change_on,NEW.username,NEW.username,NEW.id);
                
                IF NEW.logged_in THEN
                    SET log_action = 'logged on';
                ELSE
                    SET log_action = 'logged off'; 
                END IF;
                
                SET log_change_on='user';
                INSERT INTO " . $this->m_changeLogTable . " (change_action,change_on,created_by,change_by,action_id)
                VALUES (log_action,log_change_on,NEW.username,NEW.username,NEW.id);

            END;");

        if (!$conn->execute($sql)) {
            $this->m_Message .='<br>Could not create Users trigger due to error.' . ".<br/> " . $conn->getMessage() . ' <span class="icon error"></span><br />';
            return false;
        }
        $this->m_Message .='<br>Users trigger has been created.<span class="icon success"></span><br />';

        return true;
    }

    private function createEventsTable($conn) {

        //sanity check
        if (!$conn) {
            return false;
        }

        $sql = "CREATE TABLE IF NOT EXISTS " . $this->m_eventsTable . " 
        ( id  INTEGER NOT NULL AUTO_INCREMENT, 
	title       VARCHAR(150), 
	body_text   TEXT,
	publish     TINYINT(1),
	image_name  VARCHAR(300),  
	event_time  TIME,
	event_date  DATE,
        end_time    TIME,
	end_date    DATE,
	location    VARCHAR(500) NOT NULL,
        lat_long    VARCHAR(200) NOT NULL,
        created     VARCHAR(100), 
	created_by  VARCHAR(50) NOT NULL,
        edited      VARCHAR(100), 
	edited_by   VARCHAR(50) NOT NULL,
	deleted         TINYINT(1) unsigned NOT NULL DEFAULT '0',
        flake_it        INT(11) NOT NULL DEFAULT 0,
	PRIMARY KEY (id),
        KEY (deleted),
        KEY (title),
        FULLTEXT events_search(title,created_by,edited_by,location,body_text)
	) ENGINE = MYISAM;";
        if (!$conn->execute($sql)) {
            $this->m_Message .='<br>Could not create Event table named "' . $this->m_eventsTable . '" due to error.' . ".<br/> " . $conn->getMessage() . ' <span class="icon error"></span><br />';
            return false;
        }

        $this->m_Message .='<br>Events table named "' . $this->m_eventsTable . '" Has been Created.<span class="icon success"></span><br />';

        return $this->createIUDTriggers($conn, $this->m_eventsTable, 'event');
    }

    private function createGalleryTable($conn) {

        //sanity check
        if (!$conn) {
            return false;
        }

        $sql = "CREATE TABLE IF NOT EXISTS " . $this->m_galleryTable . " 
        (id	INT NOT NULL AUTO_INCREMENT, 
	title		VARCHAR(150)  NOT NULL, 
	thumb_name	TEXT  NOT NULL, 
	image_name	TEXT  NOT NULL, 
	image_caption	TEXT  NOT NULL,
        publish         TINYINT(1) DEFAULT 0,
	created		VARCHAR(100), 
	created_by      VARCHAR(50) NOT NULL,
        edited		VARCHAR(100), 
	edited_by       VARCHAR(50) NOT NULL,
	deleted         TINYINT(1) unsigned NOT NULL DEFAULT '0',
        flake_it        INT(11) NOT NULL DEFAULT 0,
	PRIMARY KEY (id),
        KEY (deleted),
        KEY (title),
        FULLTEXT search_gallery (title,created_by,edited_by,image_caption)
	) ENGINE = MYISAM;";
        if (!$conn->execute($sql)) {
            $this->m_Message .='<br>Could not create Gallery table named "' . $this->m_galleryTable . '" due to error.' . ".<br/> " . $conn->getMessage() . ' <span class="icon error"></span><br />';
            return false;
        }
        $this->m_Message .='<br>Gallery table named "' . $this->m_galleryTable . '" Has been Created.<span class="icon success"></span><br />';

        if (!$this->createIUDTriggers($conn, $this->m_galleryTable, 'gallery'))
            return false;
        return true;
    }

    private function createFlakeItTable($conn) {

        //sanity check
        if (!$conn) {
            return false;
        }

        $sql = "CREATE TABLE IF NOT EXISTS " . $this->m_flakeItTable . " 
        (id                 INT NOT NULL AUTO_INCREMENT,
        flake_on            enum('snowflake','event','gallery','user') DEFAULT NULL,  
        flake_on_id         INT(11) NOT NULL DEFAULT 0,
        flake_it            INT(11) NOT NULL DEFAULT 0,
	PRIMARY KEY (id),
        UNIQUE KEY unique_flake_it (flake_on, flake_on_id)
	)ENGINE = MYISAM;";
        if (!$conn->execute($sql)) {
            $this->m_Message .='<br>Could not create flakeIt table named "' . $this->m_flakeItTable . '" due to error.' . ".<br/> " . $conn->getMessage() . ' <span class="icon error"></span><br />';
            return false;
        }
        $this->m_Message .='<br>flakeIt table named "' . $this->m_flakeItTable . '" Has been Created.<span class="icon success"></span><br />';

        return true;
    }

    private function createSettingsTable($conn) {

        //sanity check
        if (!$conn) {
            return false;
        }

        $sql = "CREATE TABLE IF NOT EXISTS " . $this->m_settingTable . " 
        ( setting_id         INT NOT NULL AUTO_INCREMENT, 
	sf_host_name        VARCHAR(500) NOT NULL, 
	sf_db               VARCHAR(500) NOT NULL, 
	sf_db_username      VARCHAR(50) NOT NULL, 
	sf_db_password      VARCHAR(50) NOT NULL,
        sf_db_type          VARCHAR(50) NOT NULL,
	sf_url              VARCHAR(500) NOT NULL,  
	result_url          VARCHAR(500),
	out_url             VARCHAR(500), 
	events_result_url   VARCHAR(500), 
	events_output_url   VARCHAR(500), 
	gallery_result_url  VARCHAR(500), 
	gallery_out_url     VARCHAR(500), 
	upload_gallery_dir  VARCHAR(500), 
	max_upload_size     DOUBLE NOT NULL DEFAULT '1',
        time_zone           VARCHAR(200) NOT NULL DEFAULT 'Europe/London',
	PRIMARY KEY (setting_id) 
	)ENGINE = MYISAM;";
        if (!$conn->execute($sql)) {
            $this->m_Message .='<br>Could not create Snowflakes Setting table named "' . $this->m_settingTable . '" due to error.<br/>' . $conn->getMessage() . ' <span class="icon error"></span><br />';
            return false;
        }

        $this->m_Message .='<br>Snowflakes Setting table named "' . $this->m_settingTable . '" Has been Created.<span class="icon success"></span><br />';
        return true;
    }

    private function insertSnowflakesSettings($conn) {

        //sanity check
        if (!$conn) {
            return false;
        }
        $key="$this->m_hostName$this->m_dbName$this->m_dbType$this->m_dbUsername";
        $encryptedPassword=sfUtils::encrypt($this->m_adminPassword,$key);

        $sql = "INSERT IGNORE INTO " . $this->m_settingTable . " SET
		sf_host_name='" . $this->m_hostName . "' ,
		sf_db='" . $this->m_dbName . "' ,
		sf_db_username='" . $this->m_dbUsername . "' ,
                sf_db_type='" . $this->m_dbType . "' ,
		sf_db_password='" . sfUtils::escape($encryptedPassword). "' ,
		sf_url='" . $this->m_sfUrl . "',
                time_zone='". $this->m_timeZone."';";

        if (!$conn->execute($sql)) {
            $this->m_Message .="<br>" . 'Could not insert into Snowflakes Setting table named "' . $this->m_settingTable . '" due to error.' . ".<br/> " . $conn->getMessage() . ' <span class="icon error"></span><br />';
            return false;
        }

        $this->m_Message .='<br> Snowflakes Setting successfully added.<span class="icon success"></span><br />';
        return true;
    }

    private function insertAdmin($conn) {

        //sanity check
        if (!$conn) {
            return false;
        }

        $adminPass = md5($this->m_adminPassword);
        $sql = "INSERT IGNORE INTO " . $this->m_usersTable . " SET
		username='" . $this->m_adminUsername . "',
		password='" . $adminPass . "',
		reset_link='" . hash("sha256", $adminPass . $this->m_adminEmail . " " . $this->m_adminUsername) . "',
		email='" . $this->m_adminEmail . "',
		access_level=5,
                access_name='Super Administrator',
                image_name='default.png'";
        if (!$conn->execute($sql)) {
            $this->m_Message .="<br>" . 'Could not insert into admin table named "' . $this->m_usersTable . '" due to error.<br/> ' . $conn->getMessage() . ' <span class="icon error"></span><br />';
            return false;
        }

        $this->m_Message .='<br> Administrator username and password successfully added.<span class="icon success"></span><br />';
        return true;
    }

    private function insertDefaultEvents($conn) {

        //sanity check
        if (!$conn) {
            return false;
        }

        $eventDate = date('Y-m-d');
        $timeNumber = date('H:i');
        $endtimeNumber = date('H:i', time() + (2 * 60 * 60));
        $create = time();
        $loginUsername = $this->m_adminUsername;
        $sql = "INSERT IGNORE INTO " . $this->m_eventsTable . " SET
                id=1,
		title='Create More Snowflakes',
		body_text='This event involves adding new snowflakes to our site to fill it with information',
		publish=1,
		image_name='default.png',
		event_time='$timeNumber',
		event_date='$eventDate',
                end_time='$endtimeNumber',
                end_date='$eventDate',
		location='Houston, Texas, United States',
                lat_long='29.7601927,-95.36938959999998',
		created='$create',
		created_by='$loginUsername',
                edited='$create',
		edited_by='$loginUsername'";
        if (!$conn->execute($sql)) {
            $this->m_Message .="<br>" . 'Could not insert into Events table named "' . $this->m_eventsTable . '" due to error.<br/> ' . $conn->getMessage() . ' <span class="icon error"></span><br />';
            return false;
        }

        $this->m_Message .='<br> First Event successfully added.<span class="icon success"></span><br />';
        return true;
    }

    private function insertDefaultGallery($conn) {

        //sanity check
        if (!$conn) {
            return false;
        }

        $create = time();
        $loginUsername = $this->m_adminUsername;
        $sql = "INSERT IGNORE INTO " . $this->m_galleryTable . " SET
                id=1,
		title='Snowflakes',
		thumb_name='Snowflakes1.png,Snowflakes2.png,Snowflakes3.png,Snowflakes.png',
		image_name='Snowflakes1.png,Snowflakes2.png,Snowflakes3.png,Snowflakes.png',
		image_caption='About, Demo, Features, Logo',
                publish=0,
		created='$create',
		created_by='$loginUsername',
                edited='$create',
		edited_by='$loginUsername'";
        if (!$conn->execute($sql)) {
            $this->m_Message .='<br>Could not insert into Gallery table named "' . $this->m_galleryTable . '" due to error.<br/> ' . $conn->getMessage() . ' <span class="icon error"></span><br />';
            return false;
        }

        $this->m_Message .='<br> First Gallery successfully added.<span class="icon success"></span><br />';
        return true;
    }

    private function insertDefaultPosts($conn) {

        //sanity check
        if (!$conn) {
            return false;
        }

        $create = time();
        $loginUsername = $this->m_adminUsername;
        $sql = "INSERT IGNORE INTO " . $this->m_sfTable . " (
                id,
		title ,
		body_text,
		publish,
		image_name,
		gallery,
		created, 
		created_by,
                edited,
                edited_by
		)
		VALUES (1, 'Welcome to Snowflakes 1', '<h3> Introduction & Instructions</h3>\r\nThis is the first snowflake of the page. You can view this snowflake on its own by clicking the view button at the top right hand side of this snowflake. On the view page you can Edit or delete this first snowflake by using the Edit or delete function on the top right hand corner of this view page. you can add snowflake by clicking Add New Post button on the Side menu. you can view all snowflakes and you can also go back to the home page to view the 3 most recent snowflakes you have made. Delete or edit this snowflake after reading this snowflake, so as to make room for your own snowflakes. You can include one image per snowflake or use the default image provided for a blank image format. You can view admin users by clicking the admin panel on the Side Menu. You can only add one image to a snowflake in this demo version, in the full version you will be able to add galleries to a snowflake. You can also add html div or a tags for links to other sites such as youtube, Facebook  and links to other sites <a href= \" https://www.facebook.com/pages/Cyril-Inc/151728454900027 \" target=\"_blank\" > Like this </a>. for example a paragraph <br ><br ><p>This a Paragraph text after Two breaks. However still in the same paragraph you can create header tags like the \" Introduction  & Instructions \" above.</p>\r\nA simple enter key can make a new line, check the \" Welcome to Cyril Inc CMS 1 \" for further instructions.', 1, 'default.png','1,Snowflakes', '$create', '$loginUsername', '$create', '$loginUsername'), 
		(2, 'Welcome to Snowflakes 2', '<h3>More Instructions</h3>\r\nThis is an unpublished Page in the database used for demonstration purposes, This is good in a way that it allows you to create pages and store them on the database for edition later if more materials needs to be added on a later date. Like the other snowflake you can make use of the side menu to navigate through the CMS delete or edit this snowflake to make it our own so as to be published on the main view page on your website.  To publish a snowflake click on the edit icon on the snowflake and tick the \" Publish this snowflake now \"  Checkbox and submit the snowflake. it is that easy.\r\n <br /><br />\r\nIn the Admin Panel you can add users delete users and edit users how ever way you want, but make sure that when you do add a user that the user is part of your staff or an editor who will not tamper with your snowflakes but put new snowflake authorised by you. A page with all your published snowflakes and are sorted by the date they were most recently edited. To learn more about Cyril inc CMS visit our site at <a href=\" http://www.cyrilinc.co.uk \" target=\"_blank\"> Cyril Inc Website</a> ', 0, 'default.png','1,Snowflakes', '$create', '$loginUsername', '$create', '$loginUsername'), 
		(3, 'Snowflakes Features', 'You can add a snowflake without an image and the default snowflake icon will appear in the snowflake. This is so because a snowflake always has an image to go with it.  you can also share a flake on social sites, it has already been pre-configured to be shared and you can customise it by including it removing images, description, title and link.<br><br> \r\nSnowflake is an ultimate Content management System design for news on your website  and uses mysql database. Manage flakes and have them shared by your viewers, You can also customise your output to better suit the theme of your website by including the generated output on one of your site pages. Snowflakes is able to generate php code or javascript code to post your flakes on.\r\n<br><br>\r\n<p> This can be done by using a php code \"  include \"http://www.Yoursite.com/Snowflakes/Out.php\";  \" in the page that you want your snow to appear in and use css to customise how it looks.</p>  \r\n<br><br>\r\na flake class structure for css is thus:\r\n<br>\r\nSnowflake class- for the whole flake\r\n<br>SnowflakeHead class - for the title\r\n<br>SnowflakePanel class for the view and share icons\r\n<br>PageBreak class - for the white  break line \r\n<br>	 SnowflakeDescr class - for the body text of a flake\r\n<br>		 SnowflakeImage class - is the div that contains the image of a snowflake. contained within the SnowflakeDescr class\r\n<br> PageBreak Class - another white break line\r\n<br> SnowflakeDate - the snowflake date of modification & create\r\n<br><br>\r\nyou can choose to hide any of the features such as the box panel and date or the image by writing your own css for the snowflake structure. ', 0, 'default.png','1,Snowflakes', '$create', '$loginUsername', '$create', '$loginUsername'),		
		(4,'Snowflakes Features 2', '<p> Given the that you have added and published snowflakes, you can get a generated code to add anywhere on the website at the settings menu of snowflakes, follow the instructions there at the <a href=\"#\">Snowflakes Generator</a>. </p>\r\n<br />\r\n<p>Once you have Set up your Snowflakes and display the custom Output Snowflakes on your website, given that there are sharing icons on the output Snowflakes you need to dedicate a page to to viewing Snowflakes for when your website vistor decides to view your snowflakes. The <a href=\"#\">Snowflakes Generator</a> will generate the code once you have installed added and published snowflakes. make sure you follow the instructions on the   <a href=\"#\">Snowflakes Generator</a> to the later to ensure that it works. </p>\r\n<br />\r\n<p> Visit <a href=\"http://cyrilinc.co.uk/ \"> Cyril Inc Website </a> and contact us if you spot a bug or require technical help.</p>', 0, 'default.png','1,Snowflakes', '$create', '$loginUsername', '$create', '$loginUsername')";

        if (!$conn->execute($sql)) {
            $this->m_Message .="<br>" . 'Could not insert first snowflake due to Error.<br/> ' . $conn->getMessage() . ' <span class="icon error"></span><br />';
            return false;
        }

        $this->m_Message .='<br> First snowflake has been added successfully. <span class="icon success"></span><br />';
        return true;
    }

}