<?php

/**
 * This Contains all the classes and tools for handling Snowflakes data 
 *
 * @author Cyril Adelekan
 */
//Disable error reporting
error_reporting(0);
//declare(ticks = 5);
//register_tick_function('sfUtils::memoryTickHandler');
//unregister_tick_function('sfUtils::memoryTickHandler');
set_error_handler("sfLogError::sfErrorHandler");
date_default_timezone_set('Europe/London');

/**
 * Class thats stores information about a Snowflake
 * from 
 * <p>The identifier(id) of the snowflake.</p>
 * <p>The title of the Snowflake.</p>
 * <p>The body text of the snowflake.</p>
 * <p>The Image name of the snowflake usually stored in the default upload directory.</p>
 * <p>The gallery {@link galleryStruct identifier and title} attached to this snowflake which usually require the gallery to 
 * already be created before it is attached to a Snowflake.</p>
 * <p>The date and time the snowflake was created.</p>
 * <p>The User {@link userStruct username} who created the Snowflake.</p>
 * <p>The date and time the snowflake was modified.</p>
 * <p>The User {@link userStruct username} who modified the Snowflake</p>
 * <p>The deleted value if a user who is not the owner of this snowflake has requested a delete</p>
 * <p>The flake it value of a snowflake usually an integer, people who likes or supports the snowflake flakes it.</p>
 * <p>The Image Directory where the snowflake image is stored</p>
 * 
 *
 * @author Cyril Adelekan
 */
class snowflakeStruct
{

    var $m_id;
    var $m_uuid;
    var $m_title;
    var $m_body_text;
    var $m_publish;
    var $m_image_name;
    var $m_gallery;
    var $m_created;
    var $m_created_by;
    var $m_edited;
    var $m_edited_by;
    var $m_deleted;
    var $m_flake_it;
    var $m_image_dir;

    /**
     * Check that all the required fields in a {@link snowflakeStruct}
     * is populated
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function isSfPopulated()
    {
        return isset($this->m_title) && isset($this->m_body_text) && (isset($this->m_created_by) || isset($this->m_edited_by)) && (isset($this->m_created) || isset($this->m_edited));
    }

    /**
     * Populate each member of {@link snowflakeStruct} given the input parameters
     *
     * @param array $array to be used to populate members of {@link snowflakeStruct}
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function populate($array)
    {

        if (empty($array) && !is_array($array))
        {
            return false;
        }

        $this->m_id = array_key_exists('id', $array) ? $array['id'] : "";
        $this->m_uuid = array_key_exists('uuid', $array) ? $array['uuid'] : "";
        $this->m_title = array_key_exists('title', $array) ? $array['title'] : "";
        $this->m_body_text = array_key_exists('body_text', $array) ? $array['body_text'] : "";
        $this->m_publish = array_key_exists('publish', $array) ? $array['publish'] : "";
        $this->m_image_name = array_key_exists('image_name', $array) ? $array['image_name'] : "";
        $this->m_gallery = array_key_exists('gallery', $array) ? $array['gallery'] : "";
        $this->m_created = array_key_exists('created', $array) ? $array['created'] : "";
        $this->m_created_by = array_key_exists('created_by', $array) ? $array['created_by'] : "";
        $this->m_edited = array_key_exists('edited', $array) ? $array['edited'] : "";
        $this->m_edited_by = array_key_exists('edited_by', $array) ? $array['edited_by'] : "";
        $this->m_deleted = array_key_exists('deleted', $array) ? $array['deleted'] : "";
        $this->m_flake_it = array_key_exists('flake_it', $array) ? $array['flake_it'] : "";

        return true;
    }

    /**
     * Get all the values of all members of {@link snowflakeStruct} given the id(identifier),
     * the data is obtained from the database and then the result is used to populate the 
     * members of this class {@see populate}
     * 
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param int $id {@link snowflakeStruct} the id(identifier) or uuid(universally 
     * unique identifier) of the snowflake record
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function getSnowflakesByid($conn, $id)
    {
        if (!$conn || !$id || $id == -1)
        {
            return false;
        }

        $sql = "SELECT * FROM snowflakes WHERE ";
        $sql.=strpos($id, '-') ? "uuid='$id';" : "id=$id;";
        if (!$conn->fetch($sql))
        {
            return false;
        }

        $result = $conn->getResultArray();
        if (empty($result))
        {
            return false;
        }
        return $this->populate($result[0]);
    }

    /**
     * Get the Image name of a {@link snowflakeStruct} given the id(identifier),
     * the data is obtained from the database and then the result returned
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param int $id {@link snowflakeStruct} the id(identifier) of the snowflake
     * 
     * @return bool <b>Image name</b> on success or <b>FALSE</b> otherwise
     */
    public static function getImageNameById($conn, $id)
    {

        if (!$conn || !$id || $id == -1)
        {
            return false;
        }

        $sql = "SELECT image_name FROM snowflakes WHERE id=$id;";
        if (!$conn->fetch($sql))
        {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result))
        {
            return false;
        }
        return $result[0]['image_name'];
    }

    /**
     * Add a new {@link snowflakeStruct} data to the database table used to store snowflake
     * provided that all the mandatory members of this class is populated
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function addSnowflake($conn)
    {

        if (!$this->isSfPopulated() || !$conn)
        {
            return false;
        }

        $insertSQL = 'INSERT INTO snowflakes SET uuid=UPPER(UUID()), ' .
                'title="' . sfUtils::escape($this->m_title) .
                '",body_text="' . sfUtils::escape($this->m_body_text) .
                '",publish="' . $this->m_publish .
                '",image_name="' . sfUtils::escape($this->m_image_name) .
                '",gallery="' . $this->m_gallery .
                '",created="' . $this->m_created .
                '",created_by="' . $this->m_created_by .
                '",edited="' . $this->m_edited .
                '",edited_by="' . $this->m_edited_by . '";';
        return $conn->execute($insertSQL);
    }

    /**
     * Update {@link snowflakeStruct} data to the database table used to store snowflake
     * provided that all the mandatory members of this class is populated
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function updateSnowflake($conn)
    {

        if (!$this->isSfPopulated() || !$conn || sfUtils::isEmpty($this->m_id))
        {
            return false;
        }

        $sql = 'UPDATE snowflakes SET title="' . sfUtils::escape($this->m_title) .
                '",body_text="' . sfUtils::escape($this->m_body_text) .
                '",publish="' . $this->m_publish .
                '",image_name="' . sfUtils::escape($this->m_image_name) .
                '",gallery="' . $this->m_gallery .
                '",edited="' . $this->m_edited .
                '",edited_by="' . $this->m_edited_by . '"';
        $sql.= ' WHERE id=' . $this->m_id . ";";

        return $conn->execute($sql);
    }

    /**
     * Delete {@link snowflakeStruct} data store in the database table provided that the 
     * id(identifier) is indicated as a handle for which the data is to be deleted
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param bool $setDelete indicates true or false to actually delete the 
     * data from the database table or set the delete field
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function deleteSnowflake($conn, $setDelete = false)
    {

        if (sfUtils::isEmpty($this->m_id) || !$conn)
        {
            return false;
        }

        $sql = "";
        if ($setDelete == false)
        {
            $sql = "DELETE FROM snowflakes ";
        }
        else
        {
            $sql = "UPDATE snowflakes SET deleted=1 "; // if the user
            if (isset($_SESSION['MM_Username']))
            {
                $sql.=',edited_by="' . $_SESSION['MM_Username'] . '",edited="' . time() . '" ';
            }
        }

        $sql.="WHERE id=" . $this->m_id . ";";

        if (strlen($this->m_image_dir) > 0 && $setDelete == false)
        {
            $imagename = $this->m_image_dir . $this->m_image_name;
            return $conn->execute($sql) && sfUtils::Deletefile($imagename);
        }

        return $conn->execute($sql);
    }

    /**
     * Get a {@link snowflakeStruct} id(identifier) provided the members of this
     * class are populated the id(identifier) is selected from the database
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * 
     * @return bool/int <b>id(identifier)</b> on success or <b>FALSE</b> otherwise
     */
    public function getSnowflakeID($conn)
    {

        /// Sanity Checks
        if (!$this->isSfPopulated() || !$conn)
        {
            return false;
        }

        if (!sfUtils::isEmpty($this->m_id))
        {
            return $this->m_id;
        }

        $sql = 'SELECT id FROM snowflakes WHERE ' .
                'title="' . sfUtils::escape($this->m_title) .
                '" AND body_text="' . sfUtils::escape($this->m_body_text) .
                '" AND publish="' . $this->m_publish .
                '" AND image_name="' . sfUtils::escape($this->m_image_name) .
                '" AND gallery="' . $this->m_gallery . '"';
        $sql.=isset($this->m_created) ? 'AND created="' . $this->m_created . '" ' : " ";
        $sql.=isset($this->m_created_by) ? ' AND created_by="' . $this->m_created_by . '"' : " ";
        $sql.=isset($this->m_edited) ? ' AND edited="' . $this->m_edited . '"' : " ";
        $sql.=isset($this->m_edited_by) ? ' AND edited_by="' . $this->m_edited_by . '"' : " ";
        $sql.=";";

        if (!$conn->fetch($sql))
        {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result))
        {
            return false;
        }
        $this->m_id = $result[0]['id'];
        return $this->m_id;
    }

    /**
     * print all the members of {@link snowflakeStruct}
     * 
     * @return String formatted and labeled member values
     */
    public function printsnowlakes()
    {
        $str = 'uuid="' . $this->m_uuid . '"<br> ';
        $str.= 'title="' . $this->m_title . '"<br> ';
        $str.='body_text="' . $this->m_body_text . '"<br>';
        $str.='publish="' . $this->m_publish . '"<br>';
        $str.='image_name="' . $this->m_image_name . '"<br>';
        $str.='gallery="' . $this->m_gallery . '"<br>';
        $str.='created="' . $this->m_created . '"<br>';
        $str.='created_by="' . $this->m_created_by . '"<br>';
        $str.='edited="' . $this->m_edited . '"<br>';
        $str.='edited_by="' . $this->m_edited_by . '"<br>';
        $str.='deleted="' . $this->m_deleted . '"<br>';
        $str.='flake_it="' . $this->m_flake_it . '"<br>';
        $str.='id=' . $this->m_id . '<br>';
        return $str;
    }

    /**
     * Convert all the members of {@link snowflakeStruct} to a structured html string
     * 
     * @return array The html value of {@link snowflakeStruct}
     */
    public function toHTML()
    {
        $retHtml = '
            <!--Snowflake-->
            <div class="Snowflake">
            <div class="SnowflakeHead"><a href="#SHAREURL#?pageid=' . $this->m_id . '">' . $this->m_title . '</a> </div>

            <!--SnowflakePanel-->
            <div class="SnowflakePanel"> <span> View </span> 
                <a href="#SHAREURL#?pageid=' . $this->m_id . '" title="View this post"> <img src="#SNOWFLAKESURL#resources/images/Icons/View.png" height="22" width="22" alt="Edit" /> </a>
                <span>Share </span> 
                <a href="http://twitter.com/home?status=' . htmlentities(rawurlencode($this->m_title)) . '%20#SHAREURL#?pageid=' . $this->m_id . '" title="Twitter" target="_blank"> <img src="#SNOWFLAKESURL#resources/images/Icons/Twitter.png" height="22" width="22" alt="Twitter" /> </a> 
                <a href="http://www.facebook.com/sharer.php?u=#SHAREURL#?pageid=' . $this->m_id . '" title="Facebook" target="_blank"> <img src="#SNOWFLAKESURL#resources/images/Icons/Facebook.png" height="22" width="22" alt="Facebook" /> </a> 
                <a href="https://plus.google.com/share?url=#SHAREURL#?pageid=' . $this->m_id . '&amp;title=' . htmlentities(rawurlencode($this->m_title)) . '" title="GooglePlus" target="_blank"> <img src="#SNOWFLAKESURL#resources/images/Icons/GooglePlus.png" height="22" width="22" alt="GooglePlus" /> </a> 
                <a href="http://digg.com/submit?phase=2&amp;url=#SHAREURL#?pageid=' . $this->m_id . '&amp;title=' . htmlentities(rawurlencode($this->m_title)) . '" title="Digg" target="_blank"> <img src="#SNOWFLAKESURL#resources/images/Icons/Digg.png" height="22" width="22" alt="Digg" /> </a> 
                <a href="http://stumbleupon.com/submit?url=#SHAREURL#?pageid=' . $this->m_id . '&amp;title=' . htmlentities(rawurlencode($this->m_title)) . '" title="stumbleupon" target="_blank"> <img src="#SNOWFLAKESURL#resources/images/Icons/stumbleupon.png" height="22" width="22" alt="stumbleupon" /> </a> 
                <a href="http://del.icio.us/post?url=#SHAREURL#?pageid=' . $this->m_id . '&amp;title=' . htmlentities(rawurlencode($this->m_title)) . '" title="delicious" target="_blank"> <img src="#SNOWFLAKESURL#resources/images/Icons/delicious.png" height="22" width="22" alt="delicious" /> </a> 
                <a class="flakeit" id="flakeit' . $this->m_id . '" title="flake it" data-type="snowflake"> <span>Flake it</span> <img src="#SNOWFLAKESURL#resources/images/Icons/Snowflakes.png" height="22" width="22" alt="flake it" /> </a> 
            </div><!--End of SnowflakePanel-->

            <div class="PageBreak"></div>
            <div class="clear"></div>
            <!--SnowflakeDescr-->
            <div class="SnowflakeDescr">

                <div class="SnowflakeImage">
                    <a class="colorbox" href="' . $this->m_image_dir . $this->m_image_name . '"  onerror="this.href=\'#MISSINGIMG#\'"  title="' . $this->m_title . '" >
                        <img src="' . $this->m_image_dir . $this->m_image_name . '" onerror="this.src=\'#MISSINGIMG#\'"  alt="' . $this->m_image_name . '" />
                    </a>
                </div>

        ' . html_entity_decode($this->m_body_text) . ' 

            </div><!--SnowflakeDescr Ends-->
            <div class="clear"></div>
            <div class="PageBreak"></div>
            <div class="SnowflakeDate"> Posted |: ' . date(" F j, Y", $this->m_created) . '  | By - ' . $this->m_created_by . ' </div>
            <div class="SnowflakeIt">
                <img src="#SNOWFLAKESURL#resources/images/Icons/Snowflakes.png" height="22" width="22" alt="flake it" /> 
                <span class="flakeitParam" id="flakecount' . $this->m_id . '"> ' . $this->m_flake_it . ' </span>
            </div>
            <div class="SharePost"> </div>
        </div>
        <!-- End of Snowflake -->
        ';
        return $retHtml;
    }

    /**
     * Convert all the members of {@link snowflakeStruct} to an array
     * 
     * @return array The array value of {@link snowflakeStruct}
     */
    public function toArray()
    {
        $retArray = array();
        $retArray['id'] = $this->m_id;
        $retArray['uuid'] = $this->m_uuid;
        $retArray['title'] = $this->m_title;
        $retArray['body_text'] = $this->m_body_text;
        $retArray['publish'] = $this->m_publish;
        $retArray['image_name'] = $this->m_image_dir . $this->m_image_name;
        $retArray['gallery'] = $this->m_gallery;
        $retArray['created'] = $this->m_created;
        $retArray['created_format'] = 'Y-m-d H:i:s O';
        $retArray['created_value'] = date('Y-m-d H:i:s O', $this->m_created);
        $retArray['created_by'] = $this->m_created_by;
        $retArray['edited'] = $this->m_edited;
        $retArray['edited_format'] = 'Y-m-d H:i:s O';
        $retArray['edited_value'] = date('Y-m-d H:i:s O', $this->m_edited);
        $retArray['edited_by'] = $this->m_edited_by;
        $retArray['deleted'] = $this->m_deleted;
        $retArray['flake_it'] = $this->m_flake_it;

        return $retArray;
    }

    /**
     * Convert all the members of {@link snowflakeStruct} to a Json 
     * 
     * @return String The json value of {@link snowflakeStruct}
     */
    public function toJson()
    {
        $retArray = $this->toArray();
        return json_encode($retArray);
    }

    /**
     * Convert all the members of {@link snowflakeStruct} to an xml format
     * 
     * @return string The xml string value of {@link snowflakeStruct}
     */
    public function toXml()
    {
        $retXml = new SimpleXMLElement("<snowflake id='$this->m_id' publish='$this->m_publish'></snowflake>");
        $retXml->addChild('uuid', $this->m_uuid);
        $retXml->addChild('title', $this->m_title);
        $BodyString = sfUtils::escape(html_entity_decode($this->m_body_text));
        $retXml->addChild('body_text', $BodyString);
        $imagename = $retXml->addChild('image_name');
        $imagename->addAttribute('rel', $this->m_image_name);
        $imagename->addAttribute('href', "$this->m_image_dir$this->m_image_name");

        if (!sfUtils::isEmpty($this->m_gallery))
        {
            $Gallery = explode(",", $this->m_gallery);
            $Galleryitem = $retXml->addChild('gallery');
            $Galleryitem->addAttribute('id', $Gallery[0]);
            $Galleryitem->addAttribute('title', $Gallery[1]);
        }

        $created = $retXml->addChild('created');
        $created->addAttribute('format', 'Y-m-d H:i:s O');
        $created->addAttribute('value', date('Y-m-d H:i:s O', $this->m_created));

        $retXml->addChild('created_by', $this->m_created_by);

        $edited = $retXml->addChild('edited');
        $edited->addAttribute('format', 'Y-m-d H:i:s O');
        $edited->addAttribute('value', date('Y-m-d H:i:s O', $this->m_edited));

        $retXml->addChild('edited_by', $this->m_edited_by);
        $retXml->addChild('deleted', $this->m_deleted);
        $retXml->addChild('flake_it', $this->m_flake_it);

        return str_replace('<?xml version="1.0"?>', '', $retXml->asXML());
    }

}

/**
 * Class thats stores information about a User
 * from 
 * <p>The identifier(id) of the User.</p>
 * <p>The username of the User.</p>
 * <p>The password of the User.</p>
 * <p>The email of the User.</p>
 * <p>The access level of User.</p>
 * <p>The access level name.</p>
 * <p>The Reset link for the user to reset passwords.</p>
 * <p>The deleted value if a user who is not the owner of this user has requested a delete.</p>
 * <p>The flake it value of a snowflake usually an integer, people who likes or supports the snowflake flakes it.</p>
 * <p>The Log in status of a user.</p>
 * <p>The last date and time the user logged in.</p>
 * <p>The Image Directory where the snowflake image is stored.</p>
 * 
 *
 * @author Cyril Adelekan
 */
class userStruct
{

    var $m_id;
    var $m_uuid;
    var $m_username;
    var $m_password;
    var $m_email;
    var $m_access_level;
    var $m_access_name;
    var $m_reset_link;
    var $m_image_name;
    var $m_deleted;
    var $m_flake_it;
    var $m_logged_in;
    var $m_last_login;
    var $m_image_dir;

    /**
     * initialise some member variable {@link userStruct} given the input parameters
     *
     * @param String $username {@link userStruct} username
     * @param String $password {@link userStruct} the user's password
     * @param String $email {@link userStruct} the user's email
     * @param int $access_level {@link userStruct} the user's access level
     * @param int $image_name {@link userStruct} the user's image name
     */
    public function init($username, $password, $email, $access_level, $image_name = 'default.png')
    {
        $this->m_username = $username;
        $this->m_password = md5($password);
        $this->m_email = $email;
        $this->m_access_level = $access_level;
        $this->m_access_name = sfUtils::UserLevelName($access_level);
        $this->m_reset_link = hash("sha256", $this->m_password . " " . $email . " " . $username);
        $this->m_image_name = $image_name;
    }

    /**
     * Check that all the required fields in a {@link userStruct} is populated
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function isPopulated()
    {
        return isset($this->m_username) && isset($this->m_password) && isset($this->m_email) && isset($this->m_access_level) && isset($this->m_access_name);
    }

    /**
     * Populate each member of {@link userStruct} given the input parameters
     *
     * @param array $value to be used to populate members of {@link userStruct}
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function populate($value)
    {
        if (empty($value) && !is_array($value))
        {
            return false;
        }
        $this->m_id = array_key_exists('id', $value) ? $value['id'] : "";
        $this->m_uuid = array_key_exists('uuid', $value) ? $value['uuid'] : "";
        $this->m_username = array_key_exists('username', $value) ? $value['username'] : "";
        $this->m_password = array_key_exists('password', $value) ? $value['password'] : "";
        $this->m_reset_link = array_key_exists('reset_link', $value) ? $value['reset_link'] : "";
        $this->m_email = array_key_exists('email', $value) ? $value['email'] : "";
        $this->m_access_level = array_key_exists('access_level', $value) ? $value['access_level'] : "";
        $this->m_access_name = array_key_exists('access_name', $value) ? $value['access_name'] : "";
        $this->m_image_name = array_key_exists('image_name', $value) ? $value['image_name'] : "";
        $this->m_deleted = array_key_exists('deleted', $value) ? $value['deleted'] : "";
        $this->m_flake_it = array_key_exists('flake_it', $value) ? $value['flake_it'] : "";
        $this->m_logged_in = array_key_exists('logged_in', $value) ? $value['logged_in'] : "";
        $this->m_last_login = array_key_exists('last_login', $value) ? $value['last_login'] : "";

        return true;
    }

    /**
     * Get all the values of all members of {@link userStruct} given $username 
     * {@see populate}
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param String $username {@link userStruct} the username
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function getUserByUsername($conn, $username)
    {
        if (!$conn || !$username)
        {
            return false;
        }

        $sql = 'SELECT * FROM snowflakes_users WHERE username ="' . $username . '"';
        if (!$conn->fetch($sql))
        {
            return false;
        }

        $result = $conn->getResultArray();
        if (empty($result))
        {
            return false;
        }
        return $this->populate($result[0]);
    }

    /**
     * Get {@link userStruct} image name given the id(identifier)
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param int $id {@link userStruct} the id(identifier) of the user record
     * 
     * @return bool <b>image name</b> on success or <b>FALSE</b> otherwise
     */
    public static function getImageNameById($conn, $id)
    {

        if (!$conn || !$id || $id == -1)
        {
            return false;
        }

        $sql = "SELECT image_name FROM snowflakes_users WHERE id=$id";
        if (!$conn->fetch($sql))
        {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result))
        {
            return false;
        }
        return $result[0]['image_name'];
    }

    /**
     * Get all the values of all members of {@link userStruct} given the id(identifier),
     * {@see populate}
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param int $id {@link userStruct} the id(identifier) or uuid(universally 
     * unique identifier) of the user record
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function getUserByid($conn, $id)
    {
        if (!$conn || !$id || $id == -1)
        {
            return false;
        }

        $sql = "SELECT * FROM snowflakes_users WHERE ";
        $sql.=strpos($id, '-') ? "uuid='$id';" : "id=$id;";
        if (!$conn->fetch($sql))
        {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result))
        {
            return false;
        }
        return $this->populate($result[0]);
    }

    /**
     * Change all the values of all members of {@link userStruct} given the id(identifier),
     * {@see populate}
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param int $flakeit {@link userStruct} the flake-it value of the user record
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function changeUserFlakeit($conn, $flakeit)
    {
        if (!$this->isPopulated() || !$conn || !$flakeit)
        {
            return false;
        }
        $sql = "UPDATE snowflakes_users SET flake_it=$flakeit"
                . " WHERE username='$this->m_username' AND email='$this->m_email' ";

        return $conn->execute($sql);
    }

    /**
     * Get all the values of all members of {@link userStruct} given the reset link,
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param String $resetLink {@link userStruct} the reset link  value of the user record
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function getUserByResetLink($conn, $resetLink)
    {
        if (!$conn || !$resetLink)
        {
            return false;
        }

        $sql = "SELECT * FROM snowflakes_users WHERE reset_link = \"$resetLink\";";
        if (!$conn->fetch($sql))
        {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result))
        {
            return false;
        }
        return $this->populate($result[0]);
    }

    /**
     * Get all the values of all members of {@link userStruct} given the user name and passoword,
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param String $username {@link userStruct} the username value of the user record
     * @param String $password {@link userStruct} the password value of the user record
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function loginUser($conn, $username, $password)
    {
        if (!$conn || !$username || !$password)
        {
            return false;
        }

        $sql = "SELECT * FROM snowflakes_users WHERE username='" . $username . "' AND password='" . $password . "';";
        $conn->fetch($sql, false);
        $result = $conn->getResultArray();
        if (empty($result))
        {
            return false;
        }
        return $this->populate($result[0]);
    }

    /**
     * Add a new {@link userStruct} data to the database table used to store a user
     * provided that all the mandatory members of {@link userStruct} is populated
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function AddUser($conn)
    {

        if (!$this->isPopulated() || !$conn)
        {
            return false;
        }

        $insertSQL = 'INSERT INTO snowflakes_users SET ' .
                'uuid=UPPER(UUID()),' .
                'username="' . $this->m_username . '",' .
                'password="' . $this->m_password . '",' .
                'reset_link="' . $this->m_reset_link . '",' .
                'email="' . $this->m_email . '",' .
                'image_name="' . $this->m_image_name . '",' .
                'access_level="' . $this->m_access_level . '",' .
                'access_name="' . $this->m_access_name . '";';

        return $conn->execute($insertSQL);
    }

    /**
     * Update {@link userStruct} data to the database table used to store user
     * provided that all the mandatory members of {@link userStruct} is populated
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function UpdateUser($conn)
    {

        if (!$this->isPopulated() || !$conn || sfUtils::isEmpty($this->m_id))
        {
            return false;
        }

        $sql = 'UPDATE snowflakes_users 
      SET username="' . $this->m_username . '", 
          password="' . $this->m_password . '",  
          reset_link="' . $this->m_reset_link . '",
          email="' . $this->m_email . '",
          image_name="' . $this->m_image_name . '",
          access_level="' . $this->m_access_level . '",
          access_name="' . $this->m_access_name . '" WHERE id=' . $this->m_id . ";";

        return $conn->execute($sql);
    }

    /**
     * Delete {@link userStruct} data store in the database table provided that the 
     * id(identifier) is indicated as a handle for which the data is to be deleted
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param bool $setDelete indicates true or false to actually delete the 
     * data from the database table or set the delete field
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function deleteUser($conn, $setDelete = false)
    {

        if (sfUtils::isEmpty($this->m_id) || !$conn)
        {
            return false;
        }

        if ($setDelete == false)
        {
            $sql = "DELETE FROM snowflakes_users ";
        }
        else
        {
            $sql = "UPDATE snowflakes_users SET deleted=1 ";
        }

        $sql.="WHERE id=" . $this->m_id . ";";

        if (strlen($this->m_image_dir) > 0 && $setDelete == false)
        {
            $imagename = $this->m_image_dir . $this->m_image_name;
            return $conn->execute($sql) && sfUtils::Deletefile($imagename);
        }

        return $conn->execute($sql);
    }

    /**
     * Checks id a suser exists given the user name 
     * 
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param String $username {@link userStruct} the username value of the user record
     * 
     * @return bool/int <b>TRUE / >=1</b> on success or <b>FALSE</b> otherwise
     */
    public function userExits($conn, $username)
    {

        if (!$username || !$conn)
        {
            return false;
        }

        $sql = "SELECT username FROM snowflakes_users WHERE username='" . $username . "';";
        if (!$conn->fetch($sql))
        {
            return false;
        }
        return $conn->recordCount();
    }

    /**
     * Get a {@link userStruct} id(identifier) provided the members of {@link userStruct}
     * are populated the id(identifier) is selected from the database
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * 
     * @return bool/int <b>id(identifier)</b> on success or <b>FALSE</b> otherwise
     */
    public function getUserID($conn)
    {

        /// Sanity Checks
        if (!$this->isPopulated() || !$conn)
        {
            return false;
        }

        if (!sfUtils::isEmpty($this->m_id))
        {
            return $this->m_id;
        }

        $sql = 'SELECT id FROM snowflakes_users WHERE ' .
                'username="' . sfUtils::escape($this->m_username) . '" ' .
                'AND password="' . $this->m_password . '" ' .
                'AND email="' . $this->m_email . '" ' .
                'AND image_name="' . sfUtils::escape($this->m_image_name) . '" ' .
                'AND access_level="' . $this->m_access_level . '"';
        $sql.=isset($this->m_access_name) ? ' AND access_name="' . $this->m_access_name . '"' : " ";
        $sql.=isset($this->m_reset_link) ? ' AND reset_link="' . $this->m_reset_link . '"' : " ";
        $sql.=";";

        if (!$conn->fetch($sql))
        {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result))
        {
            return false;
        }
        $this->m_id = $result[0]['id'];
        return $this->m_id;
    }

    /**
     * print all the members of {@link userStruct}
     * 
     * @return String formatted and labeled member values</b>
     */
    public function printuser()
    {
        $str = "id = " . $this->m_id . "<br>";
        $str .= 'uuid="' . $this->m_uuid . '"<br> ';
        $str .= "username = " . $this->m_username . "<br>";
        $str .= "password = " . $this->m_password . "<br>";
        $str .= "email = " . $this->m_email . "<br>";
        $str .= "image name = " . $this->m_image_name . "<br>";
        $str .= "access level = " . $this->m_access_level . "<br>";
        $str .= "access name = " . $this->m_access_name . "<br>";
        $str .= "reset link = " . $this->m_reset_link . "<br>";
        $str .= "deleted = " . $this->m_deleted . "<br>";
        $str .= "flake it = " . $this->m_flake_it . "<br>";
        $str .= "logged in = " . $this->m_logged_in . "<br>";
        $str .= "last login = " . $this->m_last_login . "<br>";

        return $str;
    }

    /**
     * Convert all the members of {@link userStruct} to an array
     * 
     * @return array The array value of {@link userStruct}
     */
    public function toArray()
    {
        $retArray = array();

        $retArray["id"] = $this->m_id;
        $retArray['uuid'] = $this->m_uuid;
        $retArray["username"] = $this->m_username;
        $retArray["password"] = $this->m_password;
        $retArray["email"] = $this->m_email;
        $retArray["image_name "] = $this->m_image_name;
        $retArray["access_level"] = $this->m_access_level;
        $retArray["access_name "] = $this->m_access_name;
        $retArray["reset_link"] = $this->m_reset_link;
        $retArray["deleted"] = $this->m_deleted;
        $retArray["flake_it"] = $this->m_flake_it;
        $retArray["logged_in"] = $this->m_logged_in;
        $retArray["last_login."] = $this->m_last_login;

        return $retArray;
    }

    /**
     * Convert all the members of {@link userStruct} to a Json 
     * 
     * @return String The json value of {@link userStruct}
     */
    public function toJson()
    {
        $retArray = $this->toArray();
        return json_encode($retArray);
    }

    /**
     * Convert all the members of {@link userStruct} to an xml format
     * 
     * @return String The xml string value of {@link userStruct}
     */
    public function toXml()
    {
        $retXml = new SimpleXMLElement("<user id='$this->m_id'></user>");
        $retXml->addChild('uuid', $this->m_uuid);
        $retXml->addChild("username", $this->m_username);
        $retXml->addChild("password", $this->m_password);
        $retXml->addChild("email", $this->m_email);
        $retXml->addChild('access_level', $this->m_access_level);
        $retXml->addChild('access_name', $this->m_access_name);
        $retXml->addChild('reset_link', $this->m_reset_link);
        $imagename = $retXml->addChild('image_name');
        $imagename->addAttribute('rel', $this->m_image_name);
        $imagename->addAttribute('href', "#SFGALLERYURL#$this->m_image_name");
        $retXml->addChild('deleted', $this->m_deleted);
        $retXml->addChild('flake_it', $this->m_flake_it);
        $retXml->addChild('logged_in', $this->m_logged_in);
        $retXml->addChild('last_login', $this->m_last_login);


        return str_replace('<?xml version="1.0"?>', '', $retXml->asXML());
    }

}

/**
 * Class thats stores information about a Gallery
 * from 
 * <p>The identifier(id) of the Gallery.</p>
 * <p>The title of the Gallery.</p>
 * <p>The Thumbnail image name to all the images Gallery, usually comma seperated.</p>
 * <p>The Image name to all the images Gallery, usually comma seperated.</p>
 * <p>The image caption to all the images Gallery, usually comma seperated.</p>
 * <p>The date and time the gallery was created.</p>
 * <p>The User {@link userStruct username} who created the gallery.</p>
 * <p>The date and time the gallery was modified.</p>
 * <p>The User {@link userStruct username} who modified the gallery.</p>
 * <p>The deleted value if a user who is not the owner of this gallery has requested a delete.</p>
 * <p>The flake it value of a gallery usually an integer, people who likes or supports the gallery flakes it.</p>
 * <p>The Image Directory where the gallery image is stored.</p>
 * <p>The Image Directory where the gallery thumb image is stored.</p>
 * 
 *
 * @author Cyril Adelekan
 */
class galleryStruct
{

    var $m_id;
    var $m_uuid;
    var $m_title;
    var $m_thumb_name;
    var $m_image_name;
    var $m_image_caption;
    var $m_publish;
    var $m_created;
    var $m_created_by;
    var $m_edited;
    var $m_edited_by;
    var $m_deleted;
    var $m_flake_it;
    var $m_image_dir;
    var $m_thumb_dir;

    /**
     * Check that all the required fields in a {@link galleryStruct}
     * is populated
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function isSfGPopulated()
    {
        return isset($this->m_title) && isset($this->m_thumb_name) && strlen($this->m_thumb_name) > 0 && isset($this->m_image_name) && strlen($this->m_image_name) > 0 && (isset($this->m_created_by) || isset($this->m_edited_by)) && (isset($this->m_created) || isset($this->m_edited));
    }

    /**
     * Populate each member of {@link galleryStruct} given the input parameters
     *
     * @param array $array to be used to populate members of {@link galleryStruct}
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function populate($array)
    {
        if (empty($array) && !is_array($array))
        {
            return false;
        }

        $this->m_id = array_key_exists('id', $array) ? $array['id'] : "";
        $this->m_uuid = array_key_exists('uuid', $array) ? $array['uuid'] : "";
        $this->m_title = array_key_exists('title', $array) ? $array['title'] : "";
        $this->m_thumb_name = array_key_exists('thumb_name', $array) ? $array['thumb_name'] : "";
        $this->m_image_name = array_key_exists('image_name', $array) ? $array['image_name'] : "";
        $this->m_image_caption = array_key_exists('image_caption', $array) ? $array['image_caption'] : "";
        $this->m_publish = array_key_exists('publish', $array) ? $array['publish'] : "";
        $this->m_created = array_key_exists('created', $array) ? $array['created'] : "";
        $this->m_created_by = array_key_exists('created_by', $array) ? $array['created_by'] : "";
        $this->m_edited = array_key_exists('edited', $array) ? $array['edited'] : "";
        $this->m_edited_by = array_key_exists('edited_by', $array) ? $array['edited_by'] : "";
        $this->m_deleted = array_key_exists('deleted', $array) ? $array['deleted'] : "";
        $this->m_flake_it = array_key_exists('flake_it', $array) ? $array['flake_it'] : "";

        return true;
    }

    /**
     * Add a new {@link galleryStruct} data to the database table used to store a user
     * provided that all the mandatory members of {@link galleryStruct} is populated
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function addSfGallery($conn)
    {

        if (!$this->isSfGPopulated() || !$conn)
        {
            return false;
        }

        $insertSQL = 'INSERT INTO snowflakes_gallery SET uuid=UPPER(UUID()),' .
                'title="' . sfUtils::escape($this->m_title) .
                '",thumb_name="' . $this->m_thumb_name .
                '",image_name="' . $this->m_image_name .
                '",image_caption="' . sfUtils::escape($this->m_image_caption) .
                '",publish="' . $this->m_publish .
                '",created="' . $this->m_created .
                '",created_by="' . $this->m_created_by .
                '",edited="' . $this->m_edited .
                '",edited_by="' . $this->m_edited_by . '";';

        return $conn->execute($insertSQL);
    }

    /**
     * Update {@link galleryStruct} data to the database table used to store user
     * provided that all the mandatory members of {@link galleryStruct} is populated
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function updateGallery($conn)
    {

        if (!$this->isSfGPopulated() || !$conn || sfUtils::isEmpty($this->m_id))
        {
            return false;
        }

        $sql = 'UPDATE snowflakes_gallery SET title="' . sfUtils::escape($this->m_title) .
                '",thumb_name="' . $this->m_thumb_name .
                '",image_name="' . $this->m_image_name .
                '",image_caption="' . sfUtils::escape($this->m_image_caption) .
                '",publish="' . $this->m_publish .
                '",edited="' . $this->m_edited .
                '",edited_by="' . $this->m_edited_by . '"' .
                ' WHERE id=' . $this->m_id . ";";

        return $conn->execute($sql);
    }

    /**
     * Delete {@link galleryStruct} data store in the database table provided that the 
     * id(identifier) is indicated as a handle for which the data is to be deleted
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param bool $setDelete indicates true or false to actually delete the 
     * data from the database table or set the delete field
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function deleteGallery($conn, $setDelete = false)
    {

        if (sfUtils::isEmpty($this->m_id) || !$conn)
        {
            return false;
        }

        if ($setDelete == false)
        {
            $sql = "DELETE FROM snowflakes_gallery ";
        }
        else
        {
            $sql = "UPDATE snowflakes_gallery SET deleted=1 ";
            if (isset($_SESSION['MM_Username']))
            {
                $sql.=',edited_by="' . $_SESSION['MM_Username'] . '",edited="' . time() . '" ';
            }
        }
        $sql.="WHERE id=" . $this->m_id . ";";

        return $conn->execute($sql);
    }

    /**
     * Get all the values of all members of {@link galleryStruct} given the id(identifier),
     * {@see populate}
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param int $id {@link galleryStruct} the id(identifier) or uuid(universally 
     * unique identifier) of the gallery record
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function getGalleryByid($conn, $id)
    {
        if (!$conn || !$id || $id == -1)
        {
            return false;
        }

        $sql = "SELECT * FROM snowflakes_gallery WHERE ";
        $sql.=strpos($id, '-') ? "uuid='$id';" : "id=$id;";
        if (!$conn->fetch($sql))
        {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result))
        {
            return false;
        }
        return $this->populate($result[0]);
    }

    /**
     * Get a {@link galleryStruct} id(identifier) provided the members of {@link galleryStruct}
     * are populated the id(identifier) is selected from the database
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * 
     * @return bool/int <b>id(identifier)</b> on success or <b>FALSE</b> otherwise
     */
    public function getGalleryID($conn)
    {

        if (!$this->isSfGPopulated() || !$conn)
        {
            return false;
        }
        if (!sfUtils::isEmpty($this->m_id))
        {
            return $this->m_id;
        }
        $sql = 'SELECT id FROM snowflakes_gallery WHERE ' .
                'title="' . sfUtils::escape($this->m_title) . '" ' .
                'AND thumb_name="' . $this->m_thumb_name . '" ' .
                'AND image_name="' . sfUtils::escape($this->m_image_name) . '" ' .
                'AND publish=' . $this->m_publish . ' ';
        $sql.=isset($this->m_image_caption) ? 'AND image_caption="' . sfUtils::escape($this->m_image_caption) . '" ' : " ";
        $sql.=isset($this->m_created) ? 'AND created="' . $this->m_created . '" ' : " ";
        $sql.=isset($this->m_created_by) ? 'AND created_by="' . $this->m_created_by . '"' : " ";
        $sql.=isset($this->m_edited) ? 'AND edited="' . $this->m_edited . '" ' : "";
        $sql.=isset($this->m_edited_by) ? 'AND edited_by="' . $this->m_edited_by . '"' : " ";
        $sql.=";";

        if (!$conn->fetch($sql))
        {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result))
        {
            return false;
        }
        $this->m_id = $result[0]['id'];
        return $this->m_id;
    }

    /**
     * print all the members of {@link galleryStruct}
     * 
     * @return String formatted and labeled member values</b>
     */
    public function printGallery()
    {
        $str = 'uuid="' . $this->m_uuid . '"<br> ';
        $str.= 'title="' . $this->m_title . '"<br> ';
        $str.='thumb_name="' . $this->m_thumb_name . '"<br>';
        $str.='image_name="' . $this->m_image_name . '"<br>';
        $str.='image_caption="' . $this->m_image_caption . '"<br>';
        $str.='publish="' . $this->m_publish . '"<br>';
        $str.='created="' . $this->m_created . '"<br>';
        $str.='created_by="' . $this->m_created_by . '"<br>';
        $str.='edited="' . $this->m_edited . '"<br>';
        $str.='edited_by="' . $this->m_edited_by . '"<br>';
        $str.='deleted="' . $this->m_deleted . '"<br>';
        $str.='flake_it="' . $this->m_flake_it . '"<br>';
        $str.='id=' . $this->m_id . '<br>';
        return $str;
    }

    /**
     * Convert all the members of {@link galleryStruct} to a structured html string
     * 
     * @return array The html value of {@link galleryStruct}
     */
    public function toHTML()
    {
        // Get all the image name from database
        $DBImageFiles = explode(",", $this->m_image_name);
        $DBImageThumbFiles = explode(",", $this->m_thumb_name);
        $DBImageCaption = explode(",", $this->m_image_caption);

        // Loop through the array and add directory prefix to each item in array
        foreach ($DBImageFiles as &$value)
        {
            $value = '#SFGALLERYIMGURL#' . $value;
        }

        // Loop through the array and add directory prefix to each item in array	
        foreach ($DBImageThumbFiles as &$value)
        {
            $value = '#SFGALLERYTHUMBURL#' . $value;
        }

        $retHtml = '';
        //DataList
        foreach ($DBImageThumbFiles as $counter => $imageThumbLink)
        {
            $retHtml .= '
                    <li data-pile="' . htmlentities($this->m_title . '<br/><div class="owner"> By -' . $this->m_created_by . '</div>') . '"> 
                        <a class="colorbox" href="' . $DBImageFiles[$counter] . '" onerror="this.href=\'' . $UploadImgUrl . 'missing_default.png\'"  title="' . htmlentities($DBImageCaption[$counter]) . '"> 
                            <span class="tp-info"><span>' . htmlentities($DBImageCaption[$counter]) . '</span></span> 
                            <img src="' . $imageThumbLink . '" onerror="this.src=\'#MISSINGIMG#\'" alt="' . htmlentities($DBImageCaption[$counter]) . '"> 
                        </a>
                    </li>';
        }


        return $retHtml;
    }

    /**
     * Convert all the members of {@link galleryStruct} to an array
     * 
     * @return array The array value of {@link galleryStruct}
     */
    public function toArray()
    {
        $retArray = array();

        $retArray['id'] = $this->m_id;
        $retArray['uuid'] = $this->m_uuid;
        $retArray['title'] . $this->m_title;
        $retArray['thumb_name'] = $this->m_thumb_name;
        $retArray['image_name'] = $this->m_image_name;
        $retArray['imageurl_Prefix'] = '#SFGALLERYTHUMBURL#';
        $retArray['thumburl_Prefix'] = '#SFGALLERYTHUMBURL#';

        $retArray['image_caption'] = $this->m_image_caption;
        $retArray['publish'] = $this->m_publish;
        $retArray['created'] = $this->m_created;
        $retArray['created_format'] = 'Y-m-d H:i:s O';
        $retArray['created_value'] = date('Y-m-d H:i:s O', $this->m_created);
        $retArray['created_by'] = $this->m_created_by;
        $retArray['edited'] = $this->m_edited;
        $retArray['edited_format'] = 'Y-m-d H:i:s O';
        $retArray['edited_value'] = date('Y-m-d H:i:s O', $this->m_edited);
        $retArray['edited_by'] = $this->m_edited_by;
        $retArray['deleted'] = $this->m_deleted;
        $retArray['flake_it'] = $this->m_flake_it;

        return $retArray;
    }

    /**
     * Convert all the members of {@link galleryStruct} to a Json 
     * 
     * @return String The json value of {@link galleryStruct}
     */
    public function toJson()
    {
        $retArray = $this->toArray();
        return json_encode($retArray);
    }

    /**
     * Convert all the members of {@link galleryStruct} to an xml format
     * 
     * @return String The xml string value of {@link galleryStruct}
     */
    public function toXml()
    {

        $retXml = new SimpleXMLElement("<gallery id='$this->m_id' publish='$this->m_publish'></gallery>");
        $retXml->addChild('uuid', $this->m_uuid);
        $retXml->addChild('title', $this->m_title);
        $thumb = $retXml->addChild('thumb_name', $this->m_thumb_name);
        $thumb->addAttribute('imageurlPrefix', '#SFGALLERYIMGURL#');

        $img = $retXml->addChild('image_name', $this->m_image_name);
        $img->addAttribute('thumburlPrefix', '#SFGALLERYTHUMBURL#');

        $retXml->addChild('image_caption', $this->m_image_caption);

        $created = $retXml->addChild('created');
        $created->addAttribute('format', 'Y-m-d H:i:s O');
        $created->addAttribute('value', date('Y-m-d H:i:s O', $this->m_created));

        $retXml->addChild('created_by', $this->m_created_by);

        $edited = $retXml->addChild('edited');
        $edited->addAttribute('format', 'Y-m-d H:i:s O');
        $edited->addAttribute('value', date('Y-m-d H:i:s O', $this->m_edited));

        $retXml->addChild('edited_by', $this->m_edited_by);
        $retXml->addChild('deleted', $this->m_deleted);
        $retXml->addChild('flake_it', $this->m_flake_it);

        return str_replace('<?xml version="1.0"?>', '', $retXml->asXML());
    }

}

/**
 * Class thats stores information about a Snowflake event
 * from 
 * <p>The identifier(id) of the snowflake event.</p>
 * <p>The title of the Snowflake event.</p>
 * <p>The body text of the snowflake event.</p>
 * <p>The Image name of the snowflake event usually stored in the default upload directory.</p>
 * <p>The event date.</p>
 * <p>The event time.</p>
 * <p>The end event date.</p>
 * <p>The end event time.</p>
 * <p>The location of the event/Full address of the event.</p>
 * <p>The latitude and logitue location of the event.</p>
 * <p>The date and time the snowflake event was created.</p>
 * <p>The User {@link userStruct username} who created the Snowflake.</p>
 * <p>The date and time the snowflake was modified.</p>
 * <p>The User {@link userStruct username} who modified the Snowflake</p>
 * <p>The deleted value if a user who is not the owner of this snowflake has requested a delete</p>
 * <p>The flake it value of a snowflake usually an integer, people who likes or supports the snowflake flakes it.</p>
 * <p>The Image Directory where the snowflake image is stored</p>
 * 
 *
 * @author Cyril Adelekan
 */
class eventStruct
{

    var $m_id;
    var $m_uuid;
    var $m_title;
    var $m_body_text;
    var $m_publish;
    var $m_image_name;
    var $m_event_time;
    var $m_event_date;
    var $m_end_time;
    var $m_end_date;
    var $m_location;
    var $m_lat_long;
    var $m_created;
    var $m_created_by;
    var $m_edited;
    var $m_edited_by;
    var $m_deleted;
    var $m_flake_it;
    var $m_image_dir;

    /**
     * Check that all the required fields in a {@link eventStruct} is populated
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function isPopulated()
    {
        return isset($this->m_title) && isset($this->m_body_text) && isset($this->m_event_time) && isset($this->m_event_date) && (isset($this->m_created_by) || isset($this->m_edited_by)) && (isset($this->m_created) || isset($this->m_edited));
    }

    /**
     * Populate each member of {@link eventStruct} given the input parameters
     *
     * @param array $array to be used to populate members of {@link eventStruct}
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function populate($array)
    {
        if (empty($array))
        {
            return false;
        }
        $this->m_id = array_key_exists('id', $array) ? $array['id'] : "";
        $this->m_uuid = array_key_exists('uuid', $array) ? $array['uuid'] : "";
        $this->m_title = array_key_exists('title', $array) ? $array['title'] : "";
        $this->m_body_text = array_key_exists('body_text', $array) ? $array['body_text'] : "";
        $this->m_publish = array_key_exists('publish', $array) ? $array['publish'] : "";
        $this->m_image_name = array_key_exists('image_name', $array) ? $array['image_name'] : "";
        $this->m_event_time = array_key_exists('event_time', $array) ? $array['event_time'] : "";
        $this->m_event_date = array_key_exists('event_date', $array) ? $array['event_date'] : "";
        $this->m_end_time = array_key_exists('end_time', $array) ? $array['end_time'] : "";
        $this->m_end_date = array_key_exists('end_date', $array) ? $array['end_date'] : "";
        $this->m_location = array_key_exists('location', $array) ? $array['location'] : "";
        $this->m_lat_long = array_key_exists('lat_long', $array) ? $array['lat_long'] : "";
        $this->m_created = array_key_exists('created', $array) ? $array['created'] : "";
        $this->m_created_by = array_key_exists('created_by', $array) ? $array['created_by'] : "";
        $this->m_edited = array_key_exists('edited', $array) ? $array['edited'] : "";
        $this->m_edited_by = array_key_exists('edited_by', $array) ? $array['edited_by'] : "";
        $this->m_deleted = array_key_exists('deleted', $array) ? $array['deleted'] : "";
        $this->m_flake_it = array_key_exists('flake_it', $array) ? $array['flake_it'] : "";
        return true;
    }

    /**
     * Get all the values of all members of {@link eventStruct} given the id(identifier),
     * {@see populate}
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param int $id {@link eventStruct} the id(identifier) or uuid(universally 
     * unique identifier) of the event record
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function getEventByid($conn, $id)
    {
        if (!$conn || !$id || $id == -1)
        {
            return false;
        }

        $sql = "SELECT * FROM snowflakes_events WHERE ";
        $sql.=strpos($id, '-') ? "uuid='$id';" : "id=$id;";
        if (!$conn->fetch($sql))
        {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result))
        {
            return false;
        }
        return $this->populate($result[0]);
    }

    /**
     * Get {@link eventStruct} image name   given the id(identifier)
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param int $id {@link eventStruct} the id(identifier) of the event record
     * 
     * @return bool <b>image name</b> on success or <b>FALSE</b> otherwise
     */
    public static function getImageNameById($conn, $id)
    {

        if (!$conn || !$id || $id == -1)
        {
            return false;
        }

        $sql = "SELECT image_name FROM snowflakes_events WHERE id=$id;";
        if (!$conn->fetch($sql))
        {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result))
        {
            return false;
        }
        return $result[0]['image_name'];
    }

    /**
     * Add a new {@link eventStruct} data to the database table used to store a user
     * provided that all the mandatory members of {@link eventStruct} is populated
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function AddEvent($conn)
    {

        if (!$this->isPopulated() || !$conn)
        {
            return false;
        }

        $sql = 'INSERT INTO snowflakes_events SET uuid=UPPER(UUID()),' .
                'title="' . sfUtils::escape($this->m_title) .
                '",body_text="' . sfUtils::escape($this->m_body_text) .
                '",publish="' . $this->m_publish .
                '",image_name="' . sfUtils::escape($this->m_image_name) .
                '",event_time="' . $this->m_event_time .
                '",event_date="' . $this->m_event_date .
                '",created="' . $this->m_created .
                '",created_by="' . $this->m_created_by .
                '",edited="' . $this->m_edited .
                '",edited_by="' . $this->m_edited_by .
                '",end_time="' . $this->m_end_time .
                '",end_date="' . $this->m_end_date .
                '",location="' . sfUtils::escape($this->m_location) .
                '",lat_long="' . $this->m_lat_long . '";';

        return $conn->execute($sql);
    }

    /**
     * Update {@link eventStruct} data to the database table used to store user
     * provided that all the mandatory members of {@link eventStruct} is populated
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function UpdateEvent($conn)
    {

        if (!$this->isPopulated() || !$conn || sfUtils::isEmpty($this->m_id))
        {
            return false;
        }

        $sql = 'UPDATE snowflakes_events SET title="' . sfUtils::escape($this->m_title) .
                '",body_text="' . sfUtils::escape($this->m_body_text) .
                '",publish="' . $this->m_publish .
                '",image_name="' . sfUtils::escape($this->m_image_name) .
                '",event_time="' . $this->m_event_time .
                '",event_date="' . $this->m_event_date .
                '",edited="' . $this->m_edited .
                '",edited_by="' . $this->m_edited_by .
                '",end_time="' . $this->m_end_time .
                '",end_date="' . $this->m_end_date .
                '",location="' . sfUtils::escape($this->m_location) .
                '",lat_long="' . $this->m_lat_long . '"' . ' WHERE id=' . $this->m_id . ";";

        return $conn->execute($sql);
    }

    /**
     * Delete {@link eventStruct} data store in the database table provided that the 
     * id(identifier) is indicated as a handle for which the data is to be deleted
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param bool $setDelete indicates true or false to actually delete the 
     * data from the database table or set the delete field
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function deleteEvent($conn, $setDelete = false)
    {

        if (sfUtils::isEmpty($this->m_id) || !$conn)
        {
            return false;
        }

        if ($setDelete == false)
        {
            $sql = "DELETE FROM snowflakes_events ";
        }
        else
        {
            $sql = "UPDATE snowflakes_events SET deleted=1";
            if (isset($_SESSION['MM_Username']))
            {
                $sql.=',edited_by="' . $_SESSION['MM_Username'] . '",edited="' . time() . '" ';
            }
        }

        $sql.="WHERE id=" . $this->m_id . ";";

        if (strlen($this->m_image_dir) > 0 && $setDelete == false)
        {
            $imagename = $this->m_image_dir . $this->m_image_name;
            return $conn->execute($sql) && sfUtils::Deletefile($imagename);
        }

        return $conn->execute($sql);
    }

    /**
     * Get a {@link eventStruct} id(identifier) provided the members of {@link eventStruct}
     * are populated the id(identifier) is selected from the database
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * 
     * @return bool/int <b>id(identifier)</b> on success or <b>FALSE</b> otherwise
     */
    public function getEventID($conn)
    {
        // Sanity check
        if (!$this->isPopulated() || !$conn)
        {
            return false;
        }
        if (!sfUtils::isEmpty($this->m_id))
        {
            return $this->m_id;
        }

        $sql = 'SELECT id FROM snowflakes_events WHERE ' . 'title="' . sfUtils::escape($this->m_title) . '" ' .
                'AND body_text="' . sfUtils::escape($this->m_body_text) . '" ' . 'AND publish=' . $this->m_publish . ' ';
        $sql.=isset($this->m_event_date) ? 'AND event_date="' . $this->m_event_date . '" ' : " ";
        $sql.=isset($this->m_event_time) ? 'AND event_time="' . $this->m_event_time . '" ' : " ";
        $sql.=isset($this->m_end_date) ? 'AND end_date="' . $this->m_end_date . '" ' : " ";
        $sql.=isset($this->m_end_time) ? 'AND end_time="' . $this->m_end_time . '" ' : " ";
        $sql.=isset($this->m_image_name) ? 'AND image_name="' . sfUtils::escape($this->m_image_name) . '" ' : " ";
        $sql.=isset($this->m_created) ? 'AND created="' . $this->m_created . '" ' : " ";
        $sql.=isset($this->m_created_by) ? 'AND created_by="' . $this->m_created_by . '"' : " ";
        $sql.=isset($this->m_edited) ? 'AND edited="' . $this->m_edited . '" ' : " ";
        $sql.=isset($this->m_edited_by) ? 'AND edited_by="' . $this->m_edited_by . '"' : " ";
        $sql.=";";

        if (!$conn->fetch($sql))
        {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result))
        {
            return false;
        }
        $this->m_id = $result[0]['id'];
        return $this->m_id;
    }

    /**
     * print all the members of {@link eventStruct}
     * 
     * @return String formatted and labeled member values</b>
     */
    public function printEvents()
    {
        $str = 'uuid="' . $this->m_uuid . '"<br> ';
        $str.= 'title="' . $this->m_title . '"<br> ';
        $str.='body_text="' . $this->m_body_text . '"<br>';
        $str.='publish="' . $this->m_publish . '"<br>';
        $str.='image_name="' . $this->m_image_name . '"<br>';
        $str.='event_time="' . $this->m_event_time . '"<br>';
        $str.='event_date="' . $this->m_event_date . '"<br>';
        $str.='end_time="' . $this->m_end_time . '"<br>';
        $str.='end_date="' . $this->m_end_date . '"<br>';
        $str.='location="' . $this->m_location . '"<br>';
        $str.='lat_long="' . $this->m_lat_long . '"<br>';
        $str.='created="' . $this->m_created . '"<br>';
        $str.='created_by="' . $this->m_created_by . '"<br>';
        $str.='edited="' . $this->m_edited . '"<br>';
        $str.='edited_by="' . $this->m_edited_by . '"<br>';
        $str.='deleted="' . $this->m_deleted . '"<br>';
        $str.='flake_it="' . $this->m_flake_it . '"<br>';
        $str.='id=' . $this->m_id . '<br>';
        return $str;
    }

    /**
     * Convert all the members of {@link eventStruct} to an array
     * 
     * @return array The array value of {@link eventStruct}
     */
    public function toArray()
    {
        $retArray = array();

        $retArray["id"] = $this->m_id;
        $retArray['uuid'] = $this->m_uuid;
        $retArray['title'] = $this->m_title;
        $retArray['body_text'] = $this->m_body_text;
        $retArray['publish'] = $this->m_publish;
        $retArray['image_name'] = $this->m_image_name;
        $retArray['event_time'] = $this->m_event_time;
        $retArray['event_time_format'] = 'H:i:s O';
        $retArray['event_time_value'] = date('H:i:s O', $this->m_event_time);
        $retArray['event_date'] = $this->m_event_date;
        $retArray['end_time'] = $this->m_end_time;
        $retArray['end_time_format'] = 'H:i:s O';
        $retArray['end_time_value'] = date('H:i:s O', $this->m_end_time);
        $retArray['end_date'] = $this->m_end_date;
        $retArray['location'] = $this->m_location;
        $retArray['lat_long'] = $this->m_lat_long;
        $retArray['created'] = $this->m_created;
        $retArray['created_format'] = 'Y-m-d H:i:s O';
        $retArray['created_value'] = date('Y-m-d H:i:s O', $this->m_created);
        $retArray['created_by'] = $this->m_created_by;
        $retArray['edited'] = $this->m_edited;
        $retArray['edited_format'] = 'Y-m-d H:i:s O';
        $retArray['edited_value'] = date('Y-m-d H:i:s O', $this->m_edited);
        $retArray['edited_by'] = $this->m_edited_by;
        $retArray['deleted'] = $this->m_deleted;
        $retArray['flake_it'] = $this->m_flake_it;

        return $retArray;
    }

    /**
     * Convert all the members of {@link eventStruct} to a Json 
     * 
     * @return String The json value of {@link eventStruct}
     */
    public function toJson()
    {
        $retArray = $this->toArray();
        return json_encode($retArray);
    }

    /**
     * Convert all the members of {@link eventStruct} to a structured html string
     * 
     * @return array The html value of {@link eventStruct}
     */
    public function toHTML()
    {
        $eventdate = new DateTime($this->m_event_date);
        $enddate = new DateTime($this->m_end_date);

        $retHtml = '
        <!--eventWrapper-->
        <div class="eventWrapper fl"> 
            <!--SnowflakePanel-->
            <div class="SnowflakePanel"> 
                <span>View </span>
                <a href="#SHAREURL#?Eventid=' . $this->m_id . '" title="View this Event"> <img src="#SNOWFLAKESURL#resources/images/Icons/View.png" height="22" width="22" alt="Edit" /> </a>  
                <span>Share </span> 
                <a href="http://twitter.com/home?status=' . htmlentities(rawurlencode($this->m_title)) . '%20#SHAREURL#?Eventid=' . $this->m_id . '" title="Twitter" target="_blank"> <img src="#SNOWFLAKESURL#resources/images/Icons/Twitter.png" height="22" width="22" alt="Twitter" /> </a> 
                <a href="http://www.facebook.com/sharer.php?u=#SHAREURL#?Eventid=' . $this->m_id . '" title="Facebook" target="_blank"> <img src="#SNOWFLAKESURL#resources/images/Icons/Facebook.png" height="22" width="22" alt="Facebook" /> </a> 
                <a href="https://plus.google.com/share?url=#SHAREURL#?Eventid=' . $this->m_id . '&amp;title=' . htmlentities(rawurlencode($this->m_title)) . '" title="GooglePlus" target="_blank"> <img src="#SNOWFLAKESURL#resources/images/Icons/GooglePlus.png" height="22" width="22" alt="GooglePlus" /> </a> 
                <a href="http://digg.com/submit?phase=2&amp;url=#SHAREURL#?Eventid=' . $this->m_id . '&amp;title=' . htmlentities(rawurlencode($this->m_title)) . '" title="Digg" target="_blank"> <img src="#SNOWFLAKESURL#resources/images/Icons/Digg.png" height="22" width="22" alt="Digg" /> </a> 
                <a href="http://stumbleupon.com/submit?url=#SHAREURL#?Eventid=' . $this->m_id . '&amp;title=' . htmlentities(rawurlencode($this->m_title)) . '" title="stumbleupon" target="_blank"> <img src="#SNOWFLAKESURL#resources/images/Icons/stumbleupon.png" height="22" width="22" alt="stumbleupon" /> </a> 
                <a href="http://del.icio.us/post?url=#SHAREURL#?Eventid=' . $this->m_id . '&amp;title=' . htmlentities(rawurlencode($this->m_title)) . '" title="delicious" target="_blank"> <img src="#SNOWFLAKESURL#resources/images/Icons/delicious.png" height="22" width="22" alt="delicious" /> </a>
                <a class="flakeit" id="flakeit' . $this->m_id . '" title="flake it" data-type="event"><span>Flake it</span><img src="#SNOWFLAKESURL#resources/images/Icons/Snowflakes.png" height="22" width="22" alt="flake it" /> </a> 
            </div>
            <!--End of SnowflakePanel-->

            <div class="Break2"></div>
            <!--SFEvent-->
            <div class="SFEvent">
                <div class="SFEvent-date">

                    <ul class="startDate">
                        <li class="month"> ' . $eventdate->format(" M") . '</li>
                        <li class="day">' . $eventdate->format("d") . '</li>
                        <li class="year">' . $eventdate->format(" Y") . '</li>
                        <li class="time">' . sfUtils::toAmPmTime($this->m_event_time) . '</li>
                    </ul>
                    <ul class="eventTitle">
                        <li><a href="#SHAREURL#?Eventid=' . $this->m_id . '" rel="bookmark" title="' . $this->m_title . '">' . $this->m_title . '</a></li>
                        <li><a href="#SHAREURL#?Eventid=' . $this->m_id . '" rel="bookmark" title="location">' . $this->m_location . '</a></li>
                    </ul>
                    <ul class="endDate">
                        <li class="month"> ' . $enddate->format(" M") . '</li>
                        <li class="day">' . $enddate->format("d") . '</li>
                        <li class="year">' . $enddate->format(" Y") . '</li>
                        <li class="time">' . sfUtils::toAmPmTime($this->m_end_time) . '</li>
                    </ul>
                </div>
            </div>
            <!--SFEvent Ends--> 
            <div class="clear"></div>
            <div class="SnowflakeDate"> Posted |: ' . date(" F j, Y", $this->m_created) . '  | By - ' . $this->m_created_by . ' </div>
            <div class="SnowflakeIt"> 
                <img src="#SNOWFLAKESURL#resources/images/Icons/Snowflakes.png" height="22" width="22" alt="flake it" /> 
                <span class="flakeitParam" id="flakecount' . $this->m_id . '"> ' . $this->m_flake_it . ' </span>
            </div>
        </div>
        <!--eventWrapper Ends-->';
        return $retHtml;
    }

    /**
     * Convert all the members of {@link eventStruct} to an xml format
     * 
     * @return String The xml string value of {@link eventStruct}
     */
    public function toXml()
    {

        $retXml = new SimpleXMLElement("<event id='$this->m_id' publish='$this->m_publish'></event>");
        $retXml->addChild('uuid', $this->m_uuid);
        $retXml->addChild('title', $this->m_title);

        $BodyString = sfUtils::escape(html_entity_decode($this->m_body_text));
        $retXml->addChild('body_text', $BodyString);

        $imagename = $retXml->addChild('image_name');
        $imagename->addAttribute('rel', $this->m_image_name);
        $imagename->addAttribute('href', "#SFGALLERYIMGURL#$this->m_image_name");

        $retXml->addChild('event_time', $this->m_event_time);
        $retXml->addChild('event_date', $this->m_event_date);
        $retXml->addChild('end_time', $this->m_end_time);
        $retXml->addChild('end_date', $this->m_end_date);
        $retXml->addChild('location', $this->m_location);
        $retXml->addChild('lat_long', $this->m_lat_long);

        $created = $retXml->addChild('created');
        $created->addAttribute('format', 'Y-m-d H:i:s O');
        $created->addAttribute('value', date('Y-m-d H:i:s O', $this->m_created));

        $retXml->addChild('created_by', $this->m_created_by);

        $edited = $retXml->addChild('edited');
        $edited->addAttribute('format', 'Y-m-d H:i:s O');
        $edited->addAttribute('value', date('Y-m-d H:i:s O', $this->m_edited));

        $retXml->addChild('edited_by', $this->m_edited_by);
        $retXml->addChild('deleted', $this->m_deleted);
        $retXml->addChild('flake_it', $this->m_flake_it);

        return str_replace('<?xml version="1.0"?>', '', $retXml->asXML());
    }

}

class sfLogError
{

    /**
     * Send a bug message to the author of the API so that bugs fixes could be
     * implemented to make the API better.
     * 
     * @param String $bugMessage the message to send to author of the API
     *
     * @return bool <b>TRUE</b> if the mail was successfully accepted for delivery, <b>FALSE</b> otherwise.
     */
    public static function SendBugMessage($bugMessage)
    {

        //sanity Check
        if (!isset($bugMessage))
        {
            return false;
        }

        $body = "Message:\n $bugMessage\n";
        $subject = "Bug found ";
        $server_name = sfUtils::getFilterServer('SERVER_NAME');
        $sender = "noreply@$server_name";
        ## SEND MESSAGE ##
        return mail("bugreport@cyrilinc.co.uk", $subject, $body, "From: $sender");
    }

    /**
     * Log any operation the user makes during the operation of this API.
     * {@link sfLogError} acts as an audit trail to retrace the steps of a user
     * or operations performed by this API itself.
     * 
     * @param String $message the message to log
     * @param String $user {@link userStruct} the user name of the logger
     * @param bool $show used to print the log entry to screen if <b>TRUE</b> or not if <b>FALSE</b>
     * @param String $dataDir the data directory where the log will be stored
     *
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function sfLogEntry($message, $user = "Snowflakes", $show = false, $dataDir = "../data")
    {

        if (!isset($message))
        {
            return false;
        }

        $dataDir = is_dir($dataDir) ? $dataDir : "data";
        $username = isset($_SESSION['MM_Username']) ? $_SESSION['MM_Username'] : $user;
        $datetimelog = date("Y-m-d H:i:s");
        $datelog = date("Y-m-d");
        $logEntryValue = "[ $datetimelog ] ¦=> [$username] ¦=> $message \n";
        if ($show === true)
        {
            echo $logEntryValue;
        }

        $logFilename = "$dataDir/$username-LOG-$datelog.log";
        $logResource = fopen($logFilename, "a+");
        if ($logResource === false)
        {
            return $logResource;
        }

        fwrite($logResource, $logEntryValue);
        return fclose($logResource);
    }

    /**
     * Handle the API errors automatically using this function
     * 
     * @param int $errno the error number.
     * @param String $errstr the error message.
     * @param String $errfile the filename where the error occured.
     * @param String $errline the line in the filename where the error occured.
     *
     */
    public static function sfErrorHandler($errno, $errstr, $errfile, $errline)
    {
        switch ($errno)
        {
            case E_USER_ERROR:
                $errorMessage = "Please report the following error to Cyril Inc, there is either a";
                $errorMessage .= "problem with your snowflakes or you have discovered an error ";
                $errorMessage .= " [File] ¦=> $errfile @ line $errline";
                $errorMessage .= " [Reason] ¦=> $errstr";
                self::sfLogEntry($errorMessage, "Snowflakes");
                self::SendBugMessage($errorMessage);
                break;
            default:
                self::sfLogEntry("[File] ¦=> $errfile @ line $errline [Reason] ¦=> $errstr");
                break;
        }
    }

}

/**
 * Miscellaneous snowflakes utility  methods for carrying out operations to 
 * files and data in snowflakes API .
 */
final class sfUtils
{

    const XML = 'application/xml';
    const JSON = 'application/json';
    const HTML = 'text/html';

    static public $formats = array(
        'xml' => sfUtils::XML,
        'html' => sfUtils::HTML,
        'json' => sfUtils::JSON,
    );

    private function __construct()
    {
        $this->init();
    }

    /**
     * Snowflakes utilities configuration initialisation.
     */
    public function init()
    {
        // error reporting - all errors for development (ensure you have display_errors = On in your php.ini file)
        error_reporting(E_ALL | E_STRICT);
        set_exception_handler(array($this, 'handleException'));
        // session
        if (!isset($_SESSION))
        {
            session_start();
        }
    }

    /**
     * Exception handler.
     * 
     * @param Exception $ex The exception to be handled {@link Exception}
     */
    public function handleException(Exception $ex)
    {
        $extra = array('message' => $ex->getMessage());
        if ($ex instanceof NotFoundException)
        {
            header('HTTP/1.0 404 Not Found');
            $this->runPage('404', $extra);
        }
        else
        {
            // TODO log exception
            header('HTTP/1.1 500 Internal Server Error');
            $this->runPage('500', $extra);
        }
    }

    /**
     * Generate a link with parameters from the url and parameter passed in.
     *
     * @param String $page target page
     * @param array $params page parameters
     * 
     * @return String created from page and parameters.
     */
    public static function createLink($page, array $params = array())
    {
        if (!$page)
        {
            return false;
        }
        return $page . http_build_query($params);
    }

    /**
     * Format date to d/m/Y.
     *
     * @param DateTime $date date to be formatted
     * 
     * @return String formatted date
     */
    public static function formatDate(DateTime $date = null)
    {
        if ($date === null)
        {
            return '';
        }
        return $date->format('d/m/Y');
    }

    /**
     * Format date and time to d/m/Y H(hours):i(seconds) .
     *
     * @param DateTime $date date to be formatted
     * 
     * @return String formatted date and time
     */
    public static function formatDateTime(DateTime $date = null)
    {
        if ($date === null)
        {
            return '';
        }
        return $date->format('d/m/Y H:i');
    }

    /**
     * Redirect to the given page.
     *
     * @param type $page target page
     * @param array $params page parameters
     */
    public static function redirect($page, array $params = array())
    {
        header('Location: ' . self::createLink($page, $params));
        die();
    }

    /**
     * Get value of the URL param.
     * 
     * @return String parameter value
     * @throws NotFoundException if the param is not found in the URL
     */
    public static function getUrlParam($name)
    {
        if (!array_key_exists($name, $_GET))
        {
            throw new NotFoundException('URL parameter "' . $name . '" not found.');
        }
        return $_GET[$name];
    }

    /**
     * Capitalize the first letter of the given string
     *
     * @param String $string string to be capitalized
     * 
     * @return String capitalized string
     */
    public static function capitalize($string)
    {
        return ucfirst(mb_strtolower($string));
    }

    /**
     * Escape the given string
     *
     * @param String $string string to be escaped
     * 
     * @return String escaped string
     */
    public static function escape($string)
    {
        if (defined('ENT_SUBSTITUTE'))
        {
            return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE);
        }

        return htmlspecialchars($string, ENT_QUOTES);
    }

    /**
     * Escape the given string
     *
     * @param String $string string to be escaped
     * 
     * @return String escaped string
     */
    public static function sfescape($string)
    {
        $retValue = str_replace("'", "\'", $string);
        $retValue1 = str_replace("\n", "\\n", $retValue);
        $retValue2 = str_replace("\r", "\\r", $retValue1);
        $retValue3 = str_replace("\t", "\\t", $retValue2);
        $retValue4 = str_replace('"', '\"', $retValue3);
        return $retValue4;
    }

    /**
     * Get a server variable using filter_* functions 
     * Because of the buggy nature of filter_* functions with INPUT_SERVER, this function is necessary
     * 
     * @param String $serverVar the server variable/key to get e.g 'SERVER_PROTOCOL',  'HTTP_HOST'...
     *
     * @return String The value of the server var passed 
     */
    public static function getFilterServer($serverVar)
    {
        if (!$serverVar)
        {
            return "";
        }

        $servervalue = "";
        if (filter_input(INPUT_SERVER, $serverVar))
        {
            $servervalue = filter_input(INPUT_SERVER, $serverVar, FILTER_UNSAFE_RAW, FILTER_NULL_ON_FAILURE);
        }
        else
        {
            if (isset($_SERVER[$serverVar]))
                $servervalue = filter_var($_SERVER[$serverVar], FILTER_UNSAFE_RAW, FILTER_NULL_ON_FAILURE);
        }

        return $servervalue;
    }

    /**
     * Get the current page URL 
     * 
     * @return String current page URL 
     */
    public static function curPageURL()
    {
        $pageURL = "http";
        $https = self::getFilterServer('HTTPS');
        $server_port = self::getFilterServer('SERVER_PORT');
        $server_name = self::getFilterServer('SERVER_NAME');
        $request_uri = self::getFilterServer('REQUEST_URI');
        if ($https == "on")
        {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($server_port != "80")
        {
            $pageURL .= $server_name . ":" . $server_port . $request_uri;
        }
        else
        {
            $pageURL .= $server_name . $request_uri;
        }
        return $pageURL;
    }

    /**
     * Check if the data is empty or not
     *
     * @param String $data the data to be cheked
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function isEmpty($data)
    {
        return (trim($data) === "" || $data === null);
    }

    /**
     * Create a direcory
     *
     * @param String $Dir The directory to create
     * @param String $permissions The permissions given to the directory
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function CreateDirectory($Dir, $permissions)
    {
        if (self::isEmpty($Dir))
        {
            return false;
        }

        //Create  a directory with the right permissions if it doesn't exist
        if (!is_dir($Dir))
        {
            mkdir($Dir);
        }

        if (!self::isEmpty($permissions))
        {
            chmod($Dir, $permissions);
        }

        return true;
    }

    /**
     * Calulate the approprate value in GB,MB,KB ... given the amount of bytes 
     *
     * @param String $bytes the Bytes used to calculate the value
     * 
     * @return String the value of the byte in GB,MB,KB.
     */
    public static function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    /**
     * Calulate the approprate value in bytes given the amount of GB,MB,KB... 
     *
     * @param String $bytevalue the value in GB,MB,KB....
     * 
     * @return int the value of the GB,MB,KB... in bytes.
     */
    public static function toByteSize($bytevalue)
    {
        $aUnits = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4, 'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8);
        $sUnit = strtoupper(trim(substr($bytevalue, -2)));
        if (intval($sUnit) !== 0)
        {
            $sUnit = 'B';
        }
        if (!in_array($sUnit, array_keys($aUnits)))
        {
            return false;
        }
        $iUnits = trim(substr($bytevalue, 0, strlen($bytevalue) - 2));
        if (!intval($iUnits) == $iUnits)
        {
            return false;
        }
        return $iUnits * pow(1024, $aUnits[$sUnit]);
    }

    /**
     * Remove an element from an array
     *
     * @param array $array the array to remove a key from
     * @param String $key the key to remove
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function removeElement(&$array, $key)
    { // pass array by reference
        if (!$array || !$key)
        {
            return false;
        }

        unset($array[$key]);
        return true;
    }

    /**
     * Delete a file
     *
     * @param String $file the file path to delete
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function Deletefile($file)
    {
        if (strlen($file) == 0)
        {
            return false;
        }

        if (!file_exists($file) && is_dir($file))
        {
            return false;
        }

        $exceptionFiles = array("default.png", "Snowflakes.png", "Snowflakes1.png", "Snowflakes2.png", "Snowflakes3.png", "missing_default.png");
        foreach ($exceptionFiles as $value)
        {
            if (strpos($file, $value) !== false)
            {
                return true;
            }
        }
        return unlink($file);
    }

    /**
     * Get a user level name by a given number
     *
     * @param int $number the user level number
     * 
     * @return String <b>User Level name</b> on success or <b>nothing</b> on failure.
     */
    public static function UserLevelName($number)
    {
        $userName = " ";
        switch ($number)
        {
            case 1:
                $userName = "Author/Editor"; /// Create snowflakes Events and gallery but not publish
                break;
            case 2:
                $userName = "Publisher"; /// Can do all the roles of an Author/Editor and publish and unpublish flakes and can only add, edit, veiw or delete own snowflakes
                break;
            case 3:
                $userName = "Manager"; //Can do all the role of the publisher and also add, edit, view or delete all snowflakes
                break;
            case 4:
                $userName = "Administrator"; // Can do everything a manager can do as well as add and remove users
                break;
            case 5:
                $userName = "Super Administrator"; // Can do everything an Administrator can do and also change system settings for snowflake
                break;
            default :
                $userName = "";
        }

        return $userName;
    }

    /**
     * Get todays date time
     * 
     * @return String the current date time.
     */
    public static function todaysDate()
    {
        return time();
    }

    /**
     * Get 7 days date time from current date time
     * 
     * @return String the 7 days date time from current date time.
     */
    public static function TodaysDate7()
    {
        return strtotime("+7 day", time());
    }

    /**
     * Get maximum days in this month
     * 
     * @return String the maximum days in this month.
     */
    public static function maxDays()
    {
        return date("t");
    }

    /**
     * Get a month's date time from current date time
     * 
     * @return String the month's date time from current date time.
     */
    public static function TodaysDateMonth()
    {
        return strtotime("+" . date("t") . " day", time());
    }

    /**
     * Get three month's date time from current date time
     * 
     * @return String three month's date time from current date time.
     */
    public static function TodayDateThreeMonths()
    {
        return strtotime(" + 3 month", time());
    }

    /**
     * Get six month's date time from current date time
     * 
     * @return String six month's date time from current date time.
     */
    public static function TodayDateSixMonths()
    {
        return strtotime(" + 6 month", time());
    }

    /**
     * Restrict Access To a Page: Grant or deny access to this page
     * 
     * @return bool <b>TRUE</b> on valid user or <b>FALSE</b> on invalid user.
     * 
     */
    public static function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup)
    {
        // For security, start by assuming the visitor is NOT authorized. 
        $isValid = False;

        // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
        // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
        if (!empty($UserName))
        {
            // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
            // Parse the strings into arrays. 
            $arrUsers = Explode(",", $strUsers);
            $arrGroups = Explode(",", $strGroups);
            if (in_array($UserName, $arrUsers))
            {
                $isValid = true;
            }

            // Or, you may restrict access to only certain users based on their username. 
            if (in_array($UserGroup, $arrGroups))
            {
                $isValid = true;
            }

            if (($strUsers == "") && true)
            {
                $isValid = true;
            }
        }
        return $isValid;
    }

    /**
     * This function crates a UUID, Which is usually 32 in length with 4 '-'.
     * 
     * @return String UUID String.
     */
    public static function UUID()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                // 32 bits for "time_low"
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                // 16 bits for "time_mid"
                mt_rand(0, 0xffff),
                // 16 bits for "time_hi_and_version",
                // four most significant bits holds version number 4
                mt_rand(0, 0x0fff) | 0x4000,
                // 16 bits, 8 bits for "clk_seq_hi_res",
                // 8 bits for "clk_seq_low",
                // two most significant bits holds zero and one for variant DCE1.1
                mt_rand(0, 0x3fff) | 0x8000,
                // 48 bits for "node"
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Get the sql escaped version of the value passed in depend the data type of the value
     * 
     * @return String database compactible String.
     */
    public static function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")
    {
        if (PHP_VERSION < 6)
        {
            $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
        }

        $theValue = $this->escape($theValue);
        switch ($theType)
        {
            case "text": $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
                break;
            case "long":
            case "int": $theValue = ($theValue != "") ? intval($theValue) : "NULL";
                break;
            case "double": $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
                break;
            case "date": $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
                break;
            case "defined": $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
                break;
        }
        return $theValue;
    }

    /**
     * Get the client IP address
     * 
     * @return String the client IP address.
     */
    public static function getClientIp()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
        {
            $ipaddress = self::getFilterServer("HTTP_CLIENT_IP");
        }
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ipaddress = self::getFilterServer("HTTP_X_FORWARDED_FOR");
        }
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
        {
            $ipaddress = self::getFilterServer("HTTP_X_FORWARDED");
        }
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
        {
            $ipaddress = self::getFilterServer("HTTP_FORWARDED_FOR");
        }
        else if (isset($_SERVER['HTTP_FORWARDED']))
        {
            $ipaddress = self::getFilterServer("HTTP_FORWARDED");
        }
        else if (isset($_SERVER['REMOTE_ADDR']))
        {
            $ipaddress = self::getFilterServer("REMOTE_ADDR");
        }
        else
        {
            $ipaddress = 'UNKNOWN';
        }
        return $ipaddress;
    }

    /**
     * validateFiterInput is used to validate data entered by user 
     *
     * @param int $INPUT <p>
     * One of <b>INPUT_GET</b>, <b>INPUT_POST</b>,
     * <b>INPUT_COOKIE</b>, <b>INPUT_SERVER</b>, or
     * <b>INPUT_ENV</b>.
     * </p>
     * @param String $tag <p>
     * Name of a variable to get.
     * </p>
     * @param int $FILTER_VALIDATE <p>
     * The ID of the filter to apply. The
     * manual page lists the available filters.
     * </p>
     * 
     * @return String <b>TRUE</b> on valid input or <b>FALSE</b> on invalid input.
     */
    public static function validateFiterInput($INPUT, $tag, $FILTER_VALIDATE)
    {

        $validate = filter_input($INPUT, $tag, $FILTER_VALIDATE);
        $tagtype = "";
        if ($FILTER_VALIDATE == FILTER_VALIDATE_BOOLEAN)
        {
            $tagtype = 'boolean';
        }
        else if ($FILTER_VALIDATE == FILTER_VALIDATE_EMAIL)
        {
            $tagtype = 'email address';
        }
        else if ($FILTER_VALIDATE == FILTER_VALIDATE_FLOAT)
        {
            $tagtype = 'float';
        }
        else if ($FILTER_VALIDATE == FILTER_VALIDATE_INT)
        {
            $tagtype = 'int';
        }
        else if ($FILTER_VALIDATE == FILTER_VALIDATE_IP)
        {
            $tagtype = 'Ip address';
        }
        else if ($FILTER_VALIDATE == FILTER_VALIDATE_REGEXP)
        {
            $tagtype = 'regexp';
        }
        else if ($FILTER_VALIDATE == FILTER_VALIDATE_URL)
        {
            $tagtype = 'url';
        }
        $returnsql = '';
        if ($validate)
        {
            $returnsql.= sfUtils::sfPromptMessage('<b>' . filter_input($INPUT, $tag) . '</b> is a valid ' . $tagtype . '.', 'success');
        }
        else
        {
            $returnsql.= sfUtils::sfPromptMessage('<b>' . filter_input($INPUT, $tag) . '</b> is not a valid ' . $tagtype . '.', 'failure');
        }
        return $returnsql;
    }

    /**
     * createSfLink is used to Crate a snowflakes link for an interal snowflake,
     * gallery,event,or user link when generating a notifications and user 
     * activities. e.g 12:12 am Cyrilinc published a gallery: 'Cyrilinc' will be 
     * linked to a user that is called 'Cyrilinc' and 'gallery' will be linked 
     * to a gallery internally so that when they are both clicked, the data will
     * be shown to the user 'Cyrilinc as a user' and the 'gallery' the activity
     * log is referring to.
     *
     * @param String $type <p> The link type to create</p> 
     * @param String $id <p> The identifier of the type</p> 
     * @param String $inifile <p> The configuration file </p> 
     * 
     * 
     * @return String The appropriate link given the type.
     */
    public static function createSfLink($type, $id, $inifile = '../config/config.ini')
    {
        if (!isset($type) || !isset($id))
        {
            return false;
        }
        $siteSettings = new settingsStruct($inifile);
        $link = "";

        switch ($type)
        {
            case "gallery": $link = $siteSettings->m_sfUrl . "Gallery/ViewOne.php?Galleryid=" . $id;
                break;
            case "event": $link = $siteSettings->m_sfUrl . "Events/ViewEvent.php?Eventid=" . $id;
                break;
            case "user": $link = $siteSettings->m_sfUrl . "Users/Account.php?userId=" . $id;
                break;
            case "snowflake": $link = $siteSettings->m_sfUrl . "Viewflake.php?pageid=" . $id;
                break;
            default :
                $link = $siteSettings->m_sfUrl . "#";
        }
        return $link;
    }

    /**
     * Constructs the SSE data format and flushes that data to the client.
     *
     * @param String $id Timestamp/id of this connection.
     * @param String $msg Line of text that should be transmitted.
     * @param int $retry the retry value in millisecconds. 1000ms = 1 second.
     */
    public static function sendSSEMsg($id, $msg, $retry = "")
    {
        if (!$id || !$msg)
        {// sanity check
            return false;
        }

        if ($retry !== "")
        {
            echo "retry: $retry" . PHP_EOL;
        }
        echo "id: $id" . PHP_EOL;
        echo "data: {\n";
        echo "data: \"msg\": " . json_encode($msg) . ", \n";
        echo "data: \"id\": $id\n";
        echo "data: }\n";
        echo PHP_EOL;

        flush();
    }

    /**
     * Gets a formatted/Structured and linked html style of the activitied of a user.
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param String $userName {@link userStruct} the user name of the user who's activity is to be gotten
     * @param String $inifile <p> The configuration file </p> 
     * @param int $limitstart <p> The index of activity to start from</p> 
     * @param int $limitend <p> The index of activity to end to</p>
     * 
     * @return String The formatted/Structured and linked html style of the activitied of a user.
     */
    public static function getActivities($conn, $userName, $inifile = '../config/config.ini', $limitstart = 0, $limitend = 10)
    {

        if (!$userName || !$conn || $limitstart < 0 || $limitend < $limitstart)
        {
            return false;
        }
        $currentUser = $_SESSION['MM_Username'];
        $siteSettings = new settingsStruct($inifile);

        $sql = 'SELECT * FROM snowflakes_change_log WHERE change_on!="user" AND (created_by="' . $userName . '" OR change_by="' . $userName . '")  ORDER BY change_datetime DESC';
        $sql .=$limitend > 0 ? " LIMIT $limitstart, $limitend ;" : ";";
        $conn->fetch($sql);
        $changeActivities = $conn->getResultArray();
        $totalActivities = $conn->recordCount();

        $activitiesString = "No activities yet";
        if ($totalActivities > 0)
        {
            $i = 0;
            $activitiesString = '<ul class="sfActivities">';
            do
            {
                $datetime = new DateTime($changeActivities[$i]['change_datetime']);
                $datedisplay = date('Ymd') == $datetime->format('Ymd') ? $datetime->format(" g:h a") : $datetime->format(" M j");
                $activitiesString.="<li>" . $datedisplay;
                $activitiesString.= " <a href=\"" . $siteSettings->m_sfUrl . "Users/Account.php?userName=" . $changeActivities[$i]['change_by'] . "\">";
                $activitiesString.= $changeActivities[$i]['change_by'] == $userName && $currentUser == $userName ? "You" : $changeActivities[$i]['change_by'];
                $activitiesString.="</a> ";

                $activitiesString.= $changeActivities[$i]['change_action'];
                $changeon = $changeActivities[$i]['change_on'];
                $link = self::createSfLink($changeon, $changeActivities[$i]['action_id'], $inifile);

                $activitiesString.='<a href="' . $link . '"> ';
                $activitiesString.=$changeActivities[$i]['change_by'] == $userName ? $changeActivities[$i]['change_by'] == $changeActivities[$i]['created_by'] ? 'a' : $changeActivities[$i]['created_by'] . "'s" : 'your';
                $activitiesString.=' ' . $changeon . "</a> ";

                $activitiesString.="</li>";
                $i++;
            } while ($i < count($changeActivities));

            $activitiesString .= "</ul>";
        }
        return $activitiesString;
    }

    /**
     * Search the API database tables for user entered string .
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param String $searchString <p> The string to look for </p> 
     * @param String $filter <p> filter by API type i.e snowflakes, gallery, events and users</p> 
     * 
     * @return array The array of the results found in the API database tables.
     */
    public static function searchString($conn, $searchString, $filter = "snowflakes")
    {
        if (!$conn || !$searchString)
        {
            return false;
        }

        $searchResult = array();
        if ($filter == "Whole site" || $filter == "snowflakes")
        {
            $sql = "(SELECT id,title FROM snowflakes WHERE MATCH (title,created_by,edited_by,body_text) AGAINST ('" . sfUtils::escape($searchString) . "'))
                UNION
                (SELECT id,title FROM snowflakes WHERE LOWER(title) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%') 
                  OR LOWER(created_by) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%') 
                  OR LOWER(edited_by) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%') 
                  OR LOWER(body_text) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%'));";

            $conn->fetch($sql);
            $searchResult["snowflakes"] = $conn->getResultArray();
            if ($filter == "snowflakes")
            {
                return $searchResult;
            }
        }

        if ($filter == "Whole site" || $filter == "events")
        {
            $sql = "(SELECT id,title FROM snowflakes_events WHERE MATCH (title,created_by,edited_by,location,body_text) AGAINST ('" . sfUtils::escape($searchString) . "'))
                UNION
                (SELECT id,title FROM snowflakes_events WHERE LOWER(title) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%') 
                  OR LOWER(created_by) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%')  
                  OR LOWER(edited_by) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%') 
                  OR LOWER(location) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%') 
                  OR LOWER(body_text) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%'));";
            $conn->fetch($sql);

            if ($filter == "events")
            {
                $tag = array();
                $tag["events"] = $conn->getResultArray();
                return $tag;
            }

            $searchResult["events"] = $conn->getResultArray();
        }
        if ($filter == "Whole site" || $filter == "gallery")
        {
            $sql = "(SELECT id,title FROM snowflakes_gallery WHERE MATCH (title,created_by,edited_by,image_caption) AGAINST ('" . sfUtils::escape($searchString) . "'))
                UNION
                (SELECT id,title FROM snowflakes_gallery WHERE LOWER(title) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%') 
                  OR LOWER(created_by) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%')  
                  OR LOWER(edited_by) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%')  
                  OR LOWER(image_caption) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%'));";

            $conn->fetch($sql);

            if ($filter == "gallery")
            {
                $tag = array();
                $tag["gallery"] = $conn->getResultArray();
                return $tag;
            }
            $searchResult["gallery"] = $conn->getResultArray();
        }
        if ($filter == "Whole site" || $filter == "users")
        {

            $sql = "(SELECT id,username,email FROM snowflakes_users WHERE MATCH (username,email) AGAINST ('" . sfUtils::escape($searchString) . "'))
                UNION
                (SELECT id,username,email FROM snowflakes_users WHERE LOWER(username) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%') 
                  OR LOWER(email) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%'));";

            $conn->fetch($sql);
            if ($filter == "users")
            {
                $tag = array();
                $tag["users"] = $conn->getResultArray();
                return $tag;
            }

            $searchResult["users"] = $conn->getResultArray();
        }

        return $searchResult;
    }

    /**
     * Checkes if user exists in the API users database table.
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param String $username {@link userStruct} the user name to check
     * 
     * @return bool <b>TRUE</b> if user exists or <b>FALSE</b> otherwise.
     */
    public static function userExits($conn, $username)
    {

        if (!$username || !$conn)
        {
            return false;
        }

        $sql = "SELECT username FROM snowflakes_users WHERE username='" . $username . "'";
        $conn->fetch($sql);
        return $conn->recordCount();
    }

    /**
     * checks user email on request of forgotten password and sends the user a link to reset 
     * the password by email
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param String $email the email of the user requesting a password reset
     * @param String $sender the sender (usally system administrator email)
     * @param String $snowflakesUrl the snowflakes url
     * @param String $errmsg the error message 
     *
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function forgottenPassword($conn, $email, $sender, $snowflakesUrl, $errmsg = "")
    {

        if (!$conn || !$email || !$sender)
        {
            return false;
        }

        $userStruct = new userStruct();
        $sql = 'Select * FROM snowflakes_users WHERE email="' . $email . '"';
        $conn->fetch($sql);
        $result = $conn->getResultArray();
        if (empty($result))
        {
            return false;
        }
        $userStruct->populate($result[0]);

        if ($conn->recordCount() <= 0)
        {
            $errmsg = 'Could not find the account registered to the email ' . $email . ".";
            return false;
        }

        return self::requestResetPassWord($userStruct, $sender, $snowflakesUrl);
    }

    /**
     * process a user's request for a password reset after password is forgotten by user
     *
     * @param userStruct $userStruct {@link userStruct} a struct filled with user information
     * @param String $sender the sender (usally system administrator email)
     * @param String $snowflakesUrl the snowflakes url
     *
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function requestResetPassWord($userStruct, $sender, $snowflakesUrl)
    {

        if (!$userStruct->isPopulated() || !$sender || !$snowflakesUrl)
        {
            return false;
        }
        
        # SUBJECT (Subscribe/Remove)
        $subject = "Reset your snowflakes password";


        $headers = "From: " . strip_tags($sender) . "\r\n";
        $headers .= "Reply-To: " . strip_tags($sender) . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=utf-8\r\n";

        $message = '<!DOCTYPE HTML>
            <html lang="en" >
            <head>
                <link rel="stylesheet" type="text/css" href="'.$snowflakesUrl.'resources/css/style.css" />
            </head>
            <body>';
        $message .= '
                    <!--Snowflake starts-->
                        <div class="Snowflake">
                            <div class="Logo"><img alt="Snowflakes" class="logo" src="'.$snowflakesUrl.'resources/images/Snowflakes.png" width="180" height="60" /></div>
                            <div class="clear"></div>
                            <div class="Break2"></div>
                            <div class="SnowflakeHead">'.$subject.'</div>
                            <div class="clear"></div>
                            <!--SnowflakeDescr-->
                            <div class="SnowflakeDescr">    ';
        
        $resetlink = $snowflakesUrl . "/ResetPassword.php?reset=" . $userStruct->m_reset_link;
        # MAIL BODY
        $message .= "<p>You have been sent this mail because you requested a reset on your password.</p>";
        $message .= "<p>Username:  $userStruct->m_username  </p>";
        $message .= "<p>Email:  $userStruct->m_email  </p>";
        $message .= "<p>If you haven't asked for a password reset then ignore and delete this message.";
        $message .= "If you requested to reset your password then click the rset link below. </p>";
        $message .= "<h4 class=\"SummaryHead\"><a style=\"color:white;\" class=\"NewButton\" href=\"$resetlink\">Reset link</a></h4>";
        $message .='</div><!--SnowflakeDescr Ends-->
                    </div>
                    <!--Snowflake Ends-->';
        $message .= '</body></html>';
        
        ## SEND MESSAGE ##
        return mail($userStruct->m_email, $subject, $message, $header);
    }

    /**
     * Reset the user passowrd given the new password and the reset link
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param String $password {@link userStruct} the new password of the user requesting a password reset
     * @param String $oldResetLink {@link userStruct} the old reset link 
     *
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function resetPassword($conn, $password, $oldResetLink)
    {

        if (!$password || !$conn || !$oldResetLink)
        {
            return false;
        }

        $userStruct = new userStruct();
        // get user by reset link
        $userStruct->getUserByResetLink($conn, $oldResetLink);
        // re-intialize  the user with the new password provided to set the new reset link
        $userStruct->init($userStruct->m_username, $password, $userStruct->m_email, $userStruct->m_access_level, $userStruct->m_image_name);
        return $userStruct->UpdateUser($conn);
    }

    /**
     * convert datetime string to time with am or pm appended
     *
     * @param String $stringDate the date time string
     *
     * @return String  am/pm version of time.
     */
    public static function toAmPmTime($stringDate)
    {
        $datetime = new DateTime($stringDate);
        return $datetime->format("g:i a");
    }

    /**
     * Convert Date from DD/MM/YYYY to YYYY-MM-DD to be valid for sql
     *
     * @param String $stringDate the date time string
     *
     * @return String  sql formatted date 'YYYY-MM-DD'.
     */
    public static function dateToSql($stringDate)
    {
        $datetime = new DateTime(str_replace('/', '-', $stringDate));
        return $datetime->format('Y-m-d');
    }

    /**
     * Convert Date from YYYY-MM-DD to DD/MM/YYYY from sql date string
     *
     * @param String $stringDate the date time string
     *
     * @return String  sql formatted date 'DD/MM/YYYY'.
     */
    public static function dateFromSql($stringDate)
    {
        $datetime = new DateTime($stringDate);
        return $datetime->format("d/m/Y");
    }

    /**
     * Determine table name from type parameter passed in
     *
     * @param String $type the type of data whos table is to be returned
     *
     * @return String  the Table name of a Snowflakes API type.
     */
    public static function tablenameFromType($type)
    {

        $tableName = "snowflakes";
        if ($type == 'snowflake' || $type == 'snowflakes')
        {
            $tableName = "snowflakes";
        }
        else if ($type == 'event')
        {
            $tableName = "snowflakes_events";
        }
        else if ($type == 'gallery')
        {
            $tableName = "snowflakes_gallery";
        }
        else if ($type == 'user')
        {
            $tableName = "snowflakes_users";
        }
        else if ($type == 'changelog')
        {
            $tableName = "snowflakes_change_log";
        }
        else if ($type == 'flakeit')
        {
            $tableName = "snowflakes_flakeit";
        }

        return $tableName;
    }

    /**
     * Process manual 'flake it' triggers if the server's database snowflakes is installed into
     * doesn't support/allows/permits the user to use triggers 
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param int $id the identifier of snowflake data {@link snowflakeStruct}
     * @param String $type the type of snowflake data {@see tablenameFromType}
     * @param String $operation the trigger operatios usually on adding, editing or deleting
     * the snowflake data base on its identifier
     *
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function manualFlakeitTrigger($conn, $id, $type, $operation)
    {
        if (!$conn || !$id || !$type || !$operation)
        {
            return false;
        }

        $tableName = self::tablenameFromType($type);
        if ($tableName == "")
        {
            return false;
        }

        $sql = "SELECT flake_it ";
        $sql .= "FROM $tableName WHERE id=$id";
        $conn->fetch($sql);
        $result = $conn->getResultArray();
        /// delete means the record is deleted so it must pass the 
        //condition to delete the flakeit record  associated with it
        if (empty($result) && $operation != 'DELETE')
        {
            return false;
        }

        $flakeCount = $result[0]['flake_it'];

        if ($operation == 'INSERT')
        {
            $sqlOp = "INSERT INTO snowflakes_flakeit SET flake_on='$type',flake_it=$flakeCount,flake_on_id=$id;";
        }
        else if ($operation == 'DELETE')
        {
            $sqlOp = "DELETE FROM snowflakes_flakeit WHERE flake_on_id=$id AND flake_on='$type';";
        }
        else if ($operation == 'UPDATE')
        {
            // Just to make sure the record exists alread
            $sqlOp = "INSERT IGNORE INTO snowflakes_flakeit SET flake_on='$type',flake_it=$flakeCount,flake_on_id=$id;";
            $sqlOp .= "UPDATE snowflakes_flakeit SET flake_it=$flakeCount WHERE flake_on_id=$id AND flake_on='$type';";
        }
        else
        {
            return false;
        }

        return $conn->execute($sqlOp);
    }

    /**
     * Process manual change log triggers if the server's database snowflakes is installed into
     * doesn't support/allows/permits the user to use triggers 
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param int $id the identifier of snowflake data {@link snowflakeStruct}
     * @param String $type the type of snowflake data {@see tablenameFromType}
     * @param String $operation the trigger operatios usually on adding, editing or deleting
     * the snowflake data base on its identifier
     *
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function manualchangeLogTrigger($conn, $id, $type, $operation)
    {

        if (!$conn || !$id || !$type || !$operation)
        {
            return false;
        }

        $tableName = self::tablenameFromType($type);
        if ($tableName == "")
        {
            return false;
        }

        $log_action = "'added','modified','requested to delete','deleted','logged on','logged off','published','unpublished'";
        if ($operation == 'INSERT')
        {
            $log_action = "added";
        }
        else if ($operation == 'DELETE')
        {
            $log_action = "deleted";
        }
        else if ($operation == 'UPDATE')
        {
            $log_action = "modified";
        }
        else
        {
            return false;
        }

        $sql = "SELECT deleted";
        $sql.= $type != 'user' ? ",publish,created_by,edited_by " : ",username ";
        $sql .= "FROM $tableName WHERE id=$id";
        $conn->fetch($sql);
        $result = $conn->getResultArray();
        // this means that the record has been deleted already if the result is empty and in order for the user 
        // to completely delete a snowflake, event or gallery the user must own it so we add change log using 
        // user session
        if (empty($result))
        {
            if ($operation == 'DELETE')
            {
                $user = isset($_SESSION['MM_Username']) ? $_SESSION['MM_Username'] : "Snowflakes System";
                $insertSql = "INSERT INTO snowflakes_change_log SET change_action='$log_action',change_on='$type',"
                        . "created_by='$user',change_by='$user',action_id=$id;";
                return $conn->execute($insertSql);
            }
            return false;
        }

        $deleted = $result[0]['deleted'];
        $publish = isset($result[0]['publish']) ? $result[0]['publish'] : "";
        $created_by = isset($result[0]['created_by']) ? $result[0]['created_by'] : $result[0]['username'];
        $edited_by = isset($result[0]['edited_by']) ? $result[0]['edited_by'] : $result[0]['username'];
        $log_change_on = $type;

        $insertSql = "INSERT INTO snowflakes_change_log SET change_action='$log_action',change_on='$log_change_on',";
        $insertSql.= "created_by='$created_by',change_by='$edited_by',";
        $insertSql.= "action_id=$id;";
        $inserted = $conn->execute($insertSql);

        if ($deleted === 1)
        {
            $log_action = 'requested to delete';
            $insertSql = "INSERT INTO snowflakes_change_log SET change_action='$log_action',change_on='$log_change_on',created_by='$created_by',change_by='$edited_by',action_id=$id;";
            $deleted = $conn->execute($insertSql);
        }

        if ($operation != 'DELETE' && $type != 'user' && $publish != "")
        {
            if ($publish === 1)
            {
                $log_action = 'published';
            }
            else
            {
                $log_action = 'unpublished';
            }
            $insertSql = "INSERT INTO snowflakes_change_log SET change_action='$log_action',change_on='$log_change_on',created_by='$created_by',change_by='$edited_by',action_id=$id;";
            $conn->execute($insertSql);
        }

        return $inserted;
    }

    /**
     * check triggers on the server's database snowflakes is installed into
     * doesn't support/allows/permits the user to use triggers 
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param int $id the identifier of snowflake data {@link snowflakeStruct}
     * @param String $type the type of snowflake data {@see tablenameFromType}
     * @param String $operation the trigger operatios usually on adding, editing or deleting
     * the snowflake data base on its identifier
     *
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function checkTrigger($conn, $id, $type, $operation)
    {

        if (!$conn || !$id || !$type || !$operation)
        {
            return false;
        }

        $tableName = self::tablenameFromType($type);
        if ($tableName == "")
        {
            return false;
        }

        //// check if trigger exists
        $sql = "SHOW TRIGGERS LIKE '$tableName'";
        $conn->fetch($sql);
        $countTriggers = $conn->recordCount();
        if ($countTriggers >= 1)
        {
            return true;
        }

        if (!self::manualchangeLogTrigger($conn, $id, $type, $operation))
        {
            trigger_error("Could not add or implement change log manual triggers", E_USER_NOTICE);
            return false;
        }

        if (!self::manualFlakeitTrigger($conn, $id, $type, $operation))
        {
            trigger_error("Could not add or implement Flake it manual triggers", E_USER_NOTICE);
            return false;
        }

        return true;
    }

    /**
     * process a  flake it data for any snowflake data type and increment or decrement
     * the flake_it field of  that data type
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param int $id the identifier of snowflake data {@link snowflakeStruct}
     * @param String $type the type of snowflake data {@see tablenameFromType}
     * @param bool $flakeit if true then increment the flake_it field otherwise
     * decrement the field
     *
     * @return mixed <b>the flake it value</b> on success or <b>FALSE</b> on failure.
     */
    public static function flakeIt($conn, $id, $type, $flakeit = "true")
    {

        if (!$id || !$conn || !$type)
        {
            return false;
        }

        $tableName = self::tablenameFromType($type);
        if ($tableName == "")
        {
            return false;
        }

        $sql = "UPDATE $tableName SET flake_it=flake_it";
        $sql.= $flakeit === "true" ? "+1" : "-1";
        $sql.=" WHERE id=$id";

        $updated = $conn->execute($sql);
        if (!$updated)
        {
            $sqlError.= "Could not update the flake it feild.";
            trigger_error($sqlError, E_USER_NOTICE);
        }

        // Check Trigger exist , if not then use manual trigger
        self::checkTrigger($conn, $id, $type, "UPDATE");


        $sql = "SELECT flake_it from $tableName WHERE id=$id";
        $conn->fetch($sql);
        $result = $conn->getResultArray();
        if (empty($result))
        {
            return false;
        }
        return $result[0]['flake_it'];
    }

    /**
     * Add query string to a url
     *
     * @param String $url the url to add keys and values as query to e.g cyrilinc.co.uk
     * @param String $key the query key /identifier e.g ?key=
     * @param String $value the value of the key e.g ?key=value
     *
     * @return String The <b>url</b> with the key and the query e.g cyrilinc.co.uk?key=value.
     */
    public static function addQuerystringVar($url, $key, $value)
    {
        $url = preg_replace('/(.*)(?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
        $url = substr($url, 0, -1);
        if (strpos($url, '?') === false)
        {
            return ($url . '?' . $key . '=' . $value);
        }
        else
        {
            return ($url . '&' . $key . '=' . $value);
        }
    }

    /**
     * remove query string to a url
     *
     * @param String $url the url to add keys and values as query to e.g 
     * cyrilinc.co.uk?key=value&key2=value2
     * @param String $key the query key /identifier e.g ?key2=
     * @param String $value the value of the key e.g ?key2=value2
     *
     * @return String The <b>url</b> with the key and the query e.g cyrilinc.co.uk?key=value.
     */
    public static function removeQuerystringVar($url, $key)
    {
        $url = preg_replace('/(.*)(?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
        $url = substr($url, 0, -1);
        return ($url);
    }

    /**
     * Delete a snowflake gallery and all the images associated with the specific gallery 
     * pointed out by the id(identifier)
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param int $id the identifier of snowflake data {@link snowflakeStruct}
     * @param bool $setDelete indicates true or false to actually delete the 
     * data from the database table or set the delete field
     *
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function deleteGallery($conn, $id, $setDelete = false)
    {

        if (!$id || !$conn)
        {
            return false;
        }

        if ($setDelete == false)
        {
            $sql = "DELETE FROM snowflakes_gallery ";
        }
        else
        {
            $sql = "UPDATE snowflakes_gallery SET deleted=1 ";
            if (isset($_SESSION['MM_Username']))
            {
                $sql.=',edited_by="' . $_SESSION['MM_Username'] . '",edited="' . time() . '" ';
            }
        }

        $sql.="WHERE id=" . $id;

        return $conn->execute($sql);
    }

    /**
     * encyrpt a string and Returns an encrypted & utf8-encoded string
     *
     * @param String $pure_string the original string to encrypt
     * @param int $encryption_key the encryption key
     *
     * @return String an encrypted & utf8-encoded string.
     */
    public static function encrypt($pure_string, $encryption_key)
    {
        if (!$pure_string || !$encryption_key)
        {
            return false;
        }
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $encryption_key, utf8_encode($pure_string), MCRYPT_MODE_ECB, $iv);
        if (strlen($encrypted_string) >= 1)
        {
            return $encrypted_string;
        }
        else
        {
            return $pure_string;
        }
    }

    /**
     * decrypt a string and Returns an decrypted string
     *
     * @param String $encrypted_string the encrypted string
     * @param int $encryption_key the encryption key
     *
     * @return String a decrypted original string.
     */
    public static function decrypt($encrypted_string, $encryption_key)
    {
        if (!$encrypted_string || !$encryption_key)
        {
            return false;
        }
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $encryption_key, $encrypted_string, MCRYPT_MODE_ECB, $iv);
        return $decrypted_string;
    }

    /**
     * Sets the user logged in status, to identify if the user is currently logged in to the
     * API or not
     *
     * @param String $username {@link userStruct} the user name to check
     * @param bool $value logged in if TRUE and logged out if FALSE
     * @param String $inifile <p> The configuration file </p> 
     *
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function setUserLoginOut($userName, $value = false, $inifile = '../config/config.ini')
    {
        if (!$userName)
        {
            return false;
        }
        //host
        $config = new databaseParam($inifile);
        $SFconnects = new sfConnect($config->dbArray());
        $SFconnects->connect(); // Connect to database
        $login = $value == true ? 1 : 0;
        $sql = "UPDATE snowflakes_users SET logged_in=" . $login . ", ip=\"" . self::getClientIp() . "\" WHERE username=\"" . $userName . '"';
        return $SFconnects->execute($sql);
    }

    /**
     * Counts all the flake it for all snowflakes data type(snowflakes) related to the user
     * selected
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param String $username {@link userStruct} the user name to check
     *
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function snowFlakeItCount($conn, $userName = "")
    {

        if (!$conn)
        {
            return false;
        }

        $sql = "SELECT SUM(flake_it)total "
                . "FROM snowflakes";
        if (strlen($userName) > 0)
        {
            $sql.= " WHERE created_by='$userName' GROUP BY created_by";
        }

        $sql.=";";

        $conn->fetch($sql);
        $result = $conn->getResultArray();
        if (empty($result))
        {
            return false;
        }
        return $result[0]['total'];
    }

    /**
     * Counts all the flake it for all snowflakes data type(event) related to the user
     * selected
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param String $username {@link userStruct} the user name to check
     *
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function eventFlakeItCount($conn, $userName = "")
    {

        if (!$conn)
        {
            return false;
        }

        $sql = "SELECT SUM(flake_it)total "
                . "FROM snowflakes_events";
        if (strlen($userName) > 0)
        {
            $sql.= " WHERE created_by='$userName' GROUP BY created_by";
        }

        $sql.=";";

        $conn->fetch($sql);
        $result = $conn->getResultArray();
        if (empty($result))
        {
            return false;
        }
        return $result[0]['total'];
    }

    /**
     * Counts all the flake it for all snowflakes data type(gallery) related to the user
     * selected
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param String $username {@link userStruct} the user name to check
     *
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function galleryFlakeItCount($conn, $userName = "")
    {

        if (!$conn)
        {
            return false;
        }

        $sql = "SELECT SUM(flake_it)total "
                . "FROM snowflakes_gallery";
        if (strlen($userName) > 0)
        {
            $sql.= " WHERE created_by='$userName' GROUP BY created_by";
        }

        $sql.=";";

        $conn->fetch($sql);
        $result = $conn->getResultArray();
        if (empty($result))
        {
            return false;
        }
        return $result[0]['total'];
    }

    /**
     * Counts all the flake it for all snowflakes data type related to the user
     * selected
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param String $username {@link userStruct} the user name to check
     * @param bool $showCompact This is a flag to suggest if it is set the function 
     * {@see sfUtils::comapact99} is used to make a compact count of data in snowflakes.
     *
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function getAllCounts($conn, $username = '', $showCompact = false)
    {

        if (!$conn)
        {
            return false;
        }

        $_SESSION['Snowflakes'] = array();
        $_SESSION['SfGallery'] = array();
        $_SESSION['SfEvents'] = array();
        $_SESSION['SFUsers'] = array();
        $countSnowflakes = array();

        $origSql = "SELECT COUNT(id) count FROM snowflakes WHERE ";

        $sql = $origSql . "publish = 1";
        $conn->fetch($sql);
        $totalRows_rsPublished = $conn->getResultArray();
        $countSnowflakes ['Snowflakes_published'] = $_SESSION['Snowflakes']['published'] = $showCompact ? self::comapact99($totalRows_rsPublished[0]['count']) : $totalRows_rsPublished[0]['count'];

        if (strlen($username))
        {
            $sql = $sql . ' AND created_by="' . self::escape($username) . '"';
            $conn->fetch($sql);
            $userPubSnowflakes = $conn->getResultArray();
            $countSnowflakes ['Snowflakes_user_published'] = $_SESSION['Snowflakes']['user_published'] = $showCompact ? self::comapact99($userPubSnowflakes[0]['count']) : $userPubSnowflakes[0]['count'];
        }

        $sql = $origSql . "publish = 0";
        $conn->fetch($sql);
        $totalRows_rsUnplublished = $conn->getResultArray();
        $countSnowflakes ['Snowflakes_unpublished'] = $_SESSION['Snowflakes']['unpublished'] = $showCompact ? self::comapact99($totalRows_rsUnplublished[0]['count']) : $totalRows_rsUnplublished[0]['count'];

        if (strlen($username))
        {
            $sql = $sql . ' AND created_by="' . self::escape($username) . '"';
            $conn->fetch($sql);
            $userUnPubSnowflakes = $conn->getResultArray();
            $countSnowflakes ['Snowflakes_user_unpublished'] = $_SESSION['Snowflakes']['user_unpublished'] = $showCompact ? self::comapact99($userUnPubSnowflakes[0]['count']) : $userUnPubSnowflakes[0]['count'];
            $countSnowflakes ['Snowflakes_user_total'] = $_SESSION['Snowflakes']['user_total'] = $showCompact ? self::comapact99($userUnPubSnowflakes[0]['count'] + $userPubSnowflakes[0]['count']) : $userUnPubSnowflakes[0]['count'] + $userPubSnowflakes[0]['count'];
        }

        $countSnowflakes ['Snowflakes_total'] = $_SESSION['Snowflakes']['total'] = $showCompact ? self::comapact99($totalRows_rsPublished[0]['count'] + $totalRows_rsUnplublished[0]['count']) : $totalRows_rsPublished[0]['count'] + $totalRows_rsUnplublished[0]['count'];

        $sql = "SELECT COUNT(id) count FROM snowflakes_events WHERE publish = 1";
        $conn->fetch($sql);
        $totalRows_rsPublished = $conn->getResultArray();
        $countSnowflakes ['SfEvents_published'] = $_SESSION['SfEvents']['published'] = $showCompact ? self::comapact99($totalRows_rsPublished[0]['count']) : $totalRows_rsPublished[0]['count'];

        if (strlen($username))
        {
            $sql = $sql . ' AND created_by="' . self::escape($username) . '"';
            $conn->fetch($sql);
            $userPubEvent = $conn->getResultArray();
            $countSnowflakes ['SfEvents_user_published'] = $_SESSION['SfEvents']['user_published'] = $showCompact ? self::comapact99($userPubEvent[0]['count']) : $userPubEvent[0]['count'];
        }

        $sql = "SELECT COUNT(id) count FROM snowflakes_events WHERE publish = 0";
        $conn->fetch($sql);
        $totalRows_rsUnplublished = $conn->getResultArray();
        $countSnowflakes ['SfEvents_unpublished'] = $_SESSION['SfEvents']['unpublished'] = $showCompact ? self::comapact99($totalRows_rsUnplublished[0]['count']) : $totalRows_rsUnplublished[0]['count'];

        if (strlen($username))
        {
            $sql = $sql . ' AND created_by="' . self::escape($username) . '"';
            $conn->fetch($sql);
            $userUnPubEvent = $conn->getResultArray();
            $countSnowflakes ['SfEvents_user_unpublished'] = $_SESSION['SfEvents']['user_unpublished'] = $showCompact ? self::comapact99($userUnPubEvent[0]['count']) : $userUnPubEvent[0]['count'];
            $countSnowflakes ['SfEvents_user_total'] = $_SESSION['SfEvents']['user_total'] = $showCompact ? self::comapact99($userUnPubEvent[0]['count'] + $userPubEvent[0]['count']) : $userUnPubEvent[0]['count'] + $userPubEvent[0]['count'];
        }

        $countSnowflakes ['SfEvents_total'] = $_SESSION['SfEvents']['total'] = $showCompact ? self::comapact99($totalRows_rsPublished[0]['count'] + $totalRows_rsUnplublished[0]['count']) : $totalRows_rsPublished[0]['count'] + $totalRows_rsUnplublished[0]['count'];

        $sql = "SELECT COUNT(id) count FROM snowflakes_gallery WHERE publish = 0";
        $conn->fetch($sql);
        $totalRows_galleryUnpublished = $conn->getResultArray();
        $countSnowflakes ['SfGallery_unpublished'] = $_SESSION['SfGallery']['unpublished'] = $showCompact ? self::comapact99($totalRows_galleryUnpublished[0]['count']) : $totalRows_galleryUnpublished[0]['count'];

        if (strlen($username))
        {
            $sql = $sql . ' AND created_by="' . self::escape($username) . '"';
            $conn->fetch($sql);
            $userPubGallery = $conn->getResultArray();
            $countSnowflakes ['SfGallery_user_unpublished'] = $_SESSION['SfGallery']['user_unpublished'] = $showCompact ? self::comapact99($userPubGallery[0]['count']) : $userPubGallery[0]['count'];
        }

        $sql = "SELECT COUNT(id) count FROM snowflakes_gallery WHERE publish = 1";
        $conn->fetch($sql);
        $totalRows_galleryPublished = $conn->getResultArray();
        $countSnowflakes ['SfGallery_published'] = $_SESSION['SfGallery']['published'] = $showCompact ? self::comapact99($totalRows_galleryPublished[0]['count']) : $totalRows_galleryPublished[0]['count'];

        if (strlen($username))
        {
            $sql = $sql . ' AND created_by="' . self::escape($username) . '"';
            $conn->fetch($sql);
            $userUnPubGallery = $conn->getResultArray();
            $countSnowflakes ['SfGallery_user_published'] = $_SESSION['SfGallery']['user_published'] = $showCompact ? self::comapact99($userUnPubGallery[0]['count']) : $userUnPubGallery[0]['count'];
            $countSnowflakes ['SfGallery_user_total'] = $_SESSION['SfGallery']['user_total'] = $showCompact ? self::comapact99($userUnPubGallery[0]['count'] + $userPubGallery[0]['count']) : $userUnPubGallery[0]['count'] + $userPubGallery[0]['count'];
        }

        $countSnowflakes ['SfGallery_total'] = $_SESSION['SfGallery']['total'] = $showCompact ? self::comapact99($totalRows_galleryPublished[0]['count'] + $totalRows_galleryUnpublished[0]['count']) : $totalRows_galleryPublished[0]['count'] + $totalRows_galleryUnpublished[0]['count'];

        $sql = "SELECT COUNT(id) count FROM snowflakes_users";
        $conn->fetch($sql);
        $totalRows_users = $conn->getResultArray();
        $countSnowflakes ['SFUsers_total'] = $_SESSION['SFUsers']['total'] = $showCompact ? self::comapact99($totalRows_users[0]['count']) : $totalRows_users[0]['count'];

        return $countSnowflakes;
    }

    /**
     * process a title and a message to produce a well formated dailog message
     *
     * @param String $title The title of the dialog message
     * @param String $message The dialog message
     *
     * @return mixed <b>The html dialog format</b> on success or <b>FALSE</b> on failure.
     */
    public static function dialogMessage($title, $message)
    {

        if (!strlen($message))
        {
            return false;
        }

        if (!strlen($title))
        {
            $title = 'Message';
        }
        $str = ' <!-- dialog-message Starts-->'
                . '<div class="dialog-message" title="' . $title . '">
                    ' . $message . '
        </div>
                 <!-- End dialog-message -->';
        return $str;
    }

    /**
     * Display message with error,warning or success icons  
     *
     * @param String $message The prompt message to display
     * @param String $icon The icon message to be displayed usually success,error and warning
     *
     * @return mixed <b>The html prompt format</b> on success or <b>FALSE</b> on failure.
     */
    public static function sfPromptMessage($message, $icon)
    {

        if (!strlen($message) || !strlen($icon))
        {
            return false;
        }

        $str = '<!-- sfPromptmessage Starts-->
        <div class="sfPromptmessage">    
            <div class="propmtIcon"><span class="icon ' . $icon . '"></span></div>
            <div class="promptmessage"><p>' . $message . '</p></div>
            <div style="clear:both;"></div> 
        </div>
        <!-- End sfPromptmessage -->';
        return $str;
    }

    /**
     * get the string equivalent of published status
     *
     * @param bool $publish The publish value which coould be true,false,1 or 0
     *
     * @return String The string equivalent of published status.
     */
    public static function getPublishStatus($publish)
    {

        $retValue = "";
        if ($publish == 0 || $publish === false)
        {
            $retValue = " Unpublished";
        }
        else if ($publish == 1 || $publish === true)
        {
            $retValue = " Published";
        }
        return $retValue;
    }

    /**
     * migrate old snowflakes version database table into the new snowflakes table
     * and copy the associated data (images) to the required folders
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param String $dbname the database name of the old snowflakes (v1.0 and below)
     * @param String $adminUsername the administrator username
     * @param String $output The message concerning the migration poulated
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function migrate($conn, $dbname, $adminUsername, &$output)
    {

        if (!$conn || !$dbname || !$adminUsername)
        {
            return false;
        }

        $sql = 'INSERT IGNORE INTO snowflakes_users (username,password,reset_link,email,access_level,access_name)
                SELECT a.username,a.password,MD5(CONCAT_WS(" ",a.password,a.email,a.username,a.adminid)) reset_link,a.email,a.AcessLevel,IF(a.AcessLevel=5,"Super Administrator",IF(a.AcessLevel=4,"Administrator",IF(a.AcessLevel=3,"Manager",IF(a.AcessLevel=2,"Publisher","Author/Editor"))))
                FROM ' . $dbname . '.AdminUsers a;';

        if (!$conn->execute($sql))
        {
            $output.=self::sfPromptMessage("Could Not migrate users from $dbname.AdminUsers <br /> " . $conn->getMessage() . "<br/>", 'error');
            return false;
        }

        $output.="User Migration from $dbname.AdminUsers was successful <span class=\"icon success\"></span><br />";

        $sql = 'INSERT IGNORE INTO snowflakes (title,body_text,publish,image_name,gallery,created,created_by,edited,edited_by)
            SELECT b.title,b.bodytext,b.publish, b.imagename,b.gallery,b.created,b.createdby,b.created edited,b.createdby editedby
            FROM ' . $dbname . '.SnowFlakeTable b;';


        if (!$conn->execute($sql))
        {
            $output.=self::sfPromptMessage("Could Not migrate Snowflakes from $dbname.SnowFlakeTable<br /> " . $conn->getMessage() . "<br/>", 'error');
            return false;
        }

        $output.="Snowflakes Migration from $dbname.SnowFlakeTable was successful <span class=\"icon success\"></span><br />";

        $sql = 'INSERT IGNORE INTO snowflakes_events (title,body_text,publish,image_name,event_time,event_date,end_time,end_date,location,created,edited,created_by,edited_by)
            SELECT c.title, c.bodytext, c.publish, c.imagename, c.evtime event_time, FROM_UNIXTIME(c.evDate) event_date,@endtime:=ADDTIME(c.evtime, "01:00:00") end_time, 
            IF(@endtime>"24:00:00",DATE_ADD(FROM_UNIXTIME(c.evDate), INTERVAL 1 DAY),FROM_UNIXTIME(c.evDate)) end_date, c.Location,c.evDate created,c.evDate edited,
            "' . self::escape($adminUsername) . '" created_by,"' . self::escape($adminUsername) . '" edited_by
            FROM ' . $dbname . '.SF_EventsTable c;';

        if (!$conn->execute($sql))
        {
            $output.=self::sfPromptMessage("Could Not migrate Snowflakes Events from $dbname.SF_EventsTable<br /> " . $conn->getMessage() . "<br/>", 'error');
            return false;
        }

        $output.="Snowflakes Events Migration from $dbname.SF_EventsTable was successful <span class=\"icon success\"></span><br />";

        $sql = 'INSERT IGNORE INTO snowflakes_gallery (title,thumb_name,image_name,image_caption,created,created_by,edited,edited_by)
            SELECT d.title,d.Thumbname,d.imagename,d.ImageCaption,d.created,d.createdby,d.created edited,d.createdby edited_by 
            FROM ' . $dbname . '.SF_GalleryTable d;';

        if (!$conn->execute($sql))
        {
            $output.=self::sfPromptMessage("Could Not migrate Snowflakes Gallery from $dbname.SF_GalleryTable<br /> " . $conn->getMessage() . "<br/>", 'error');
            return false;
        }

        $output.="Snowflakes Gallery Migration from $dbname.SF_GalleryTable was successful <span class=\"icon success\"></span><br />";

        $sql = 'INSERT IGNORE INTO snowflakes_settings(sf_host_name,sf_db,sf_db_username,sf_db_password,sf_db_type,sf_url,result_url,out_url,events_result_url,events_output_url,gallery_result_url,gallery_out_url,upload_gallery_dir)
            SELECT e.SFHostname, e.SFDatabase,e.SFDBUsername,e.SFDBPassword,"' . $conn->getAttribute('type') . '",e.SnowflakesUrl,e.SnowflakesResultUrl,e.SFOutUrl,e.SFEventsResultUrl,e.SFEventsOutputUrl,e.SFGalleryResultUrl,e.SFGalleryOutUrl,e.UploadGalleryDir
            FROM ' . $dbname . '.SF_SnowflakesSettings e;';

        if (!$conn->execute($sql))
        {
            $output.=self::sfPromptMessage("Could Not migrate Snowflakes settings from $dbname.SF_SnowflakesSettings <br /> " . $conn->getMessage() . "<br/>", 'error');
            return false;
        }
        $sql = 'SELECT e.SnowflakesUrl,e.SnowflakesResultUrl,e.SFOutUrl,e.SFEventsResultUrl,e.SFEventsOutputUrl,e.SFGalleryResultUrl,e.SFGalleryOutUrl,e.UploadGalleryDir
            FROM ' . $dbname . '.SF_SnowflakesSettings e;';

        $conn->fetch($sql);
        $result = $conn->getResultArray();

        $insert = 'UPDATE snowflakes_settings SET sf_url="' . self::escape($result[0]['SnowflakesUrl']) . '" WHERE setting_id=1 AND sf_url="";';
        $conn->execute($insert);

        $insert = 'UPDATE snowflakes_settings SET result_url="' . self::escape($result[0]['SnowflakesResultUrl']) . '" WHERE setting_id=1 AND result_url="";';
        $conn->execute($insert);

        $insert = 'UPDATE snowflakes_settings SET out_url="' . self::escape($result[0]['SFOutUrl']) . '" WHERE setting_id=1 AND out_url="";';
        $conn->execute($insert);

        $insert = 'UPDATE snowflakes_settings SET events_result_url="' . self::escape($result[0]['SFEventsResultUrl']) . '" WHERE setting_id=1 AND events_result_url="";';
        $conn->execute($insert);

        $insert = 'UPDATE snowflakes_settings SET events_output_url="' . self::escape($result[0]['SFEventsOutputUrl']) . '" WHERE setting_id=1 AND events_output_url="";';
        $conn->execute($insert);

        $insert = 'UPDATE snowflakes_settings SET gallery_result_url="' . self::escape($result[0]['SFGalleryResultUrl']) . '" WHERE setting_id=1 AND gallery_result_url="";';
        $conn->execute($insert);

        $insert = 'UPDATE snowflakes_settings SET gallery_out_url="' . self::escape($result[0]['SFGalleryOutUrl']) . '" WHERE setting_id=1 AND gallery_out_url="";';
        $conn->execute($insert);

        $output.="Snowflakes settings Migration from $dbname.SF_SnowflakesSettings was successful <span class=\"icon success\"></span><br />";

        return true;
    }

    /**
     * Copy a directory from a source to a destination recursively thereby creating
     * files in the destination directory and subdirectory just like in the source 
     * directory
     *
     * @param String $source the Source directory
     * @param String $dest the destination directory
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function copyDirectoryList($source, $dest)
    {

        if (!$source || !is_dir($source) || !$dest)
        {
            return false;
        }

        // Make destination directory
        if (!is_dir($dest))
        {
            mkdir($dest);
        }

        $sourcefileList = scandir($source);
        $destfileList = scandir($dest);

        foreach ($sourcefileList as $file)
        {

            if ($file == '.' || $file == '..' || in_array($file, $destfileList))
            {
                continue;
            }

            // Simple copy for a file
            if (is_file("$source/$file"))
            {
                copy("$source/$file", "$dest/$file");
            }

            // Simple copy for a file
            if (is_dir("$source/$file"))
            {
                self::copyDirectoryList("$source/$file", "$dest/$file");
            }
        }

        return true;
    }

    /**
     * migrate data associated with snowflakes previous versions to the new
     * snowflakes upload directory
     *
     * @param String $source the Source directory
     * @param String $inifile <p> The configuration file </p> 
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function migrateUpdir($source, $inifile = '../config/config.ini')
    {
        if (!$source || !is_dir($source))
        {
            return false;
        }
        $datadir = new dataDirParam($inifile);
        // Check for symlinks
        if (is_link($source))
        {
            return symlink(readlink($source), $datadir->m_uploadGalleryDir);
        }

        return self::copyDirectoryList($source, $datadir->m_uploadGalleryDir);
    }

    /**
     * Decode xml data
     *
     * @param String $xmltext <p> The xml data that needs to be decoded </p> 
     * 
     * @return mixed <b>Decoded xml string</b> on success or <b>FALSE</b> on failure.
     */
    public static function xmldecoder($xmltext)
    {
        if (!$xmltext)
        {
            return false;
        }

        /// replace &amp; with "&"
        $xmltext = str_replace("&amp;", "&", $xmltext);
        /// replace &lt; with "<"
        $xmltext = str_replace("&lt;", "<", $xmltext);
        /// replace &gt; with ">"
        $xmltext = str_replace("&gt;", ">", $xmltext);
        /// replace &apos; with "'"
        $xmltext = str_replace("&apos;", "'", $xmltext);
        /// replace &quot; with "\""
        $xmltext = str_replace("&quot;", "\"", $xmltext);

        return $xmltext;
    }

    /**
     * Encode xml data
     *
     * @param String $xmltext <p> The xml data that needs to be encoded </p> 
     * 
     * @return mixed <b>Encoded xml string</b> on success or <b>FALSE</b> on failure.
     */
    public static function xmlencoder($xmltext)
    {
        if (!$xmltext)
        {
            return false;
        }

        /// replace "&" with "&amp;" 
        $xmltext = str_replace("&", "&amp;", $xmltext);
        /// replace "<" with "&lt;"
        $xmltext = str_replace("<", "&lt;", $xmltext);
        /// replace ">" with "&gt;"
        $xmltext = str_replace(">", "&gt;", $xmltext);
        /// replace "'" with "&apos;" 
        $xmltext = str_replace("'", "&apos;", $xmltext);
        /// replace "\"" with "&quot;" with 
        $xmltext = str_replace("\"", "&quot;", $xmltext);

        return $xmltext;
    }

    /**
     * generate snowflake rss data from published snowflakes
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param array $snowflakesList {@link snowflakeStruct} a list of snowflake struct
     * @param String $inifile <p> The configuration file </p> 
     * 
     * @return mixed <b>Rss string</b> on success or <b>FALSE</b> on failure.
     */
    public static function createSnowflakesRss($conn, $snowflakesList, $inifile = '../config/config.ini')
    {
        /// sanity Check
        if (!$conn || empty($snowflakesList))
        {
            return false;
        }

        $siteSettings = new settingsStruct($inifile);
        $itemUrl = isset($siteSettings->m_snowflakesResultUrl) ? $siteSettings->m_snowflakesResultUrl : $siteSettings->m_sfUrl . "OneView.php";
        $headers = apache_request_headers();
        // build parent element
        $rss = new SimpleXMLElement("<rss version='2.0'></rss>");
        $channel = $rss->addChild('channel');
        $channel->addChild('title', 'Snowflakes Rss');
        $channel->addChild('description', 'A ' . $headers['Host'] . ' Snowflakes Rss feed');
        $channel->addChild('link', $siteSettings->m_sfUrl . 'rss.php?ty=snowflakes');

        $image = $channel->addChild('image');
        $image->addChild('url', $siteSettings->m_sfUrl . "resources/images/Snowflakes2.png");
        $image->addChild('title', "Snowflakes Rss image");
        $image->addChild('link', $siteSettings->m_sfUrl . 'rss.php?ty=snowflakes');
        $image->addChild('width', '120');
        $image->addChild('height', '40');

        foreach ($snowflakesList as $key => $value)
        {
            $flakeStruct = $value;
            $BodyString = self::escape(html_entity_decode($flakeStruct->m_body_text));

            $item = $channel->addChild('item');
            $item->addChild('title', self::escape($flakeStruct->m_title));
            $item->addChild('description', substr($BodyString, 0, 280) . '...');
            $item->addChild('link', $itemUrl . '?pageid=' . $flakeStruct->m_id);
            $item->addChild('date', date(" F j, Y", $flakeStruct->m_created));
            $item->addChild('publisher', $flakeStruct->m_created_by);
            $item->addChild('flakes', $flakeStruct->m_flake_it);
        }

        //format for pretty printing
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($rss->asXML());

        return $dom->saveXML();
    }

    /**
     * generate snowflake rss data from published snowflakes events
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param array $eventList {@link eventStruct} a list of snowflake struct
     * @param String $inifile <p> The configuration file </p> 
     * 
     * @return mixed <b>Rss string</b> on success or <b>FALSE</b> on failure.
     */
    public static function createEventRss($conn, $eventList, $inifile = '../config/config.ini')
    {
        /// sanity Check
        if (!$conn || empty($eventList))
        {
            return false;
        }

        $siteSettings = new settingsStruct($inifile);
        $itemUrl = isset($siteSettings->m_eventsResultUrl) ? $siteSettings->m_eventsResultUrl : $siteSettings->m_sfUrl . "Events/OneView.php";
        $headers = apache_request_headers();

        // build parent element
        $rss = new SimpleXMLElement("<rss version='2.0'></rss>");
        $channel = $rss->addChild('channel');
        $channel->addChild('title', 'Snowflakes Event Rss');
        $channel->addChild('description', 'A ' . $headers['Host'] . ' snowflakes event rss feed');
        $channel->addChild('link', $siteSettings->m_sfUrl . 'rss.php?ty=events');

        $image = $channel->addChild('image');
        $image->addChild('url', $siteSettings->m_sfUrl . "resources/images/Snowflakes2.png");
        $image->addChild('title', "Snowflakes Rss image");
        $image->addChild('link', $siteSettings->m_sfUrl . 'rss.php?ty=events');
        $image->addChild('width', '120');
        $image->addChild('height', '40');

        foreach ($eventList as $key => $value)
        {
            $eventStruct = $value;
            $eventtime = new DateTime($eventStruct->m_event_date);
            $endtime = new DateTime($eventStruct->m_end_date);
            $BodyString = self::escape(html_entity_decode($eventStruct->m_body_text));

            $item = $channel->addChild('item');
            $item->addChild('title', self::escape($eventStruct->m_title));
            $item->addChild('description', substr($BodyString, 0, 280) . '...');
            $item->addChild('link', $itemUrl . "?Eventid=" . $eventStruct->m_id);
            $item->addChild('date', date(" F j, Y", $eventStruct->m_created));
            $item->addChild('eventdatetime', $eventtime->format(" F j, Y") . " " . self::toAmPmTime($eventStruct->m_event_time));
            $item->addChild('enddatetime', $endtime->format(" F j, Y") . " " . self::toAmPmTime($eventStruct->m_end_time));
            $item->addChild('location', $eventStruct->m_location);
            $item->addChild('publisher', $eventStruct->m_created_by);
            $item->addChild('flakes', $eventStruct->m_flake_it);
        }
        //format for pretty printing
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($rss->asXML());

        return $dom->saveXML();
    }

    /**
     * generate snowflake rss data from published snowflakes gallery
     *
     * @param sfConnect $conn {@link sfConnect} used for database connections
     * @param array $galleryList {@link galleryStruct} a list of snowflake struct
     * @param String $inifile <p> The configuration file </p> 
     * 
     * @return mixed <b>Rss string</b> on success or <b>FALSE</b> on failure.
     */
    public static function createGalleryRss($conn, $galleryList, $inifile = '../config/config.ini')
    {
        /// sanity Check
        if (!$conn || empty($galleryList))
        {
            return false;
        }

        $siteSettings = new settingsStruct($inifile);
        $itemUrl = isset($siteSettings->m_galleryResultUrl) ? $siteSettings->m_galleryResultUrl : $siteSettings->m_sfUrl . "Gallery/OneView.php";
        $headers = apache_request_headers();

        $rss = new SimpleXMLElement("<rss version='2.0'></rss>");
        $channel = $rss->addChild('channel');
        $channel->addChild('title', 'Snowflakes Gallery Rss');
        $channel->addChild('description', 'A ' . $headers['Host'] . ' snowflakes Gallery rss feed');
        $channel->addChild('link', $siteSettings->m_sfUrl . 'rss.php?ty=gallery');

        $image = $channel->addChild('image');
        $image->addChild('url', $siteSettings->m_sfUrl . "resources/images/Snowflakes2.png");
        $image->addChild('title', "Snowflakes Gallery Rss");
        $image->addChild('link', $siteSettings->m_sfUrl . 'rss.php?ty=gallery');
        $image->addChild('width', '120');
        $image->addChild('height', '40');

        foreach ($galleryList as $key => $value)
        {
            $galleryStruct = $value;
            $coverimage = explode(",", $galleryStruct->m_thumb_name);
            $covercaption = explode(",", $galleryStruct->m_image_caption);

            $item = $channel->addChild('item');
            $item->addChild('title', self::escape($galleryStruct->m_title));
            $item->addChild('link', self::xmlencoder($itemUrl . "?Galleryid=" . $galleryStruct->m_id));
            $item->addChild('date', date(" F j, Y", $galleryStruct->m_created));
            $cvrimage = $item->addChild('image');
            $cvrimage->addChild('title', end($covercaption));
            $cvrimage->addChild('url', self::xmlencoder($siteSettings->m_sfGalleryThumbUrl . end($coverimage)));
            $cvrimage->addChild('link', self::xmlencoder($itemUrl . "?Galleryid=" . $galleryStruct->m_id));
            $cvrimage->addChild('width', $siteSettings->m_thumbWidth);
            $cvrimage->addChild('height', $siteSettings->m_thumbHeight);
            $item->addChild('publisher', $galleryStruct->m_created_by);
            $item->addChild('flakes', $galleryStruct->m_flake_it);
        }
        //format for pretty printing
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($rss->asXML());

        return $dom->saveXML();
    }

    /**
     * Checks if a string is at the begining of a much larger string
     *
     * @param String $haystack The string to search in
     * @param array $needle the string to search for
     * 
     * @return mixed <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function startsWith($haystack, $needle)
    {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    /**
     * Checks if a string is at the end of a much larger string
     *
     * @param String $haystack The string to search in
     * @param array $needle the string to search for
     * 
     * @return mixed <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function endsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    /**
     * read and display a log file
     *
     * @param String $filename the log file path to view
     * @param array $Logtype the log type to view e.g error, warning and success
     * 
     * @return mixed <b>The log array </b> on success or <b>FALSE</b> on failure.
     */
    public static function viewLogFile($filename, $Logtype = "All")
    {
        if (!$filename || !file_exists($filename))
        {
            return false;
        }

        $textFile = file_get_contents($filename);
        $lines = explode("\n[", $textFile);
        $info = array();
        $linedata = array();
        $errorcount = 0;
        $warningCount = 0;
        $successCount = 0;

        foreach ($lines as $line => $data)
        {
            $linedata = explode("¦=>", $data);
            $info[$line]['datetime'] = str_replace("[", "", str_replace("]", "", $linedata[0]));
            $info[$line]['User'] = str_replace("[", "", str_replace("]", "", $linedata[1]));
            if (strpos($linedata[2], "[File]"))
            {
                $info[$line]['File'] = str_replace("[Reason]", "", $linedata[3]);
                $info[$line]['Reason'] = $linedata[4];
                $info[$line]['Execute'] = "None";

                if (strpos($linedata[4], "warning") || strpos($linedata[4], "deprecated") || strpos($linedata[4], "is deprecated;"))
                {
                    $info[$line]['Status'] = '<span class="icon warning"></span>';
                    $warningCount++;
                }
                else
                {
                    $info[$line]['Status'] = '<span class="icon error"></span>';
                    $errorcount++;
                }
            }
            else if (strpos($linedata[2], "[Execute]"))
            {
                $info[$line]['Execute'] = str_replace("[Query Error]", "", $linedata[3]);
                $info[$line]['File'] = "None";
                $info[$line]['Reason'] = strpos($linedata[3], "[Query Error]") ? $linedata[4] : "None";
                $info[$line]['Status'] = strpos($linedata[3], "[Query Error]") ? '<span class="icon error"></span>' : '<span class="icon success"></span>';
                if (strpos($linedata[3], "[Query Error]"))
                {
                    $errorcount++;
                }
                else
                {
                    $successCount++;
                }
            }
        }
        $info['errorcount'] = $errorcount;
        $info['warningCount'] = $warningCount;
        $info['successCount'] = $successCount;
        return $info;
    }

    /**
     * Get the list of files in a directory and returns an html table tag formatted
     * string 
     *
     * @param String $dir data directory
     * 
     * @return mixed <b>The list string </b> on success or <b>FALSE</b> on failure.
     */
    public static function getfileList($dir = "../data/")
    {

        // check that this $dir is populated and it's a directory
        if (!$dir || !is_dir($dir))
        {
            return false;
        }

        $listString = '<div class="tablepage">
                <table class="layout display responsive-table">
                    <thead>
                        <tr><td>Date</td><td>Log Files</td></tr>
                    </thead>
                    <tbody>';
        // Sort in ascending order - this is default
        $files = scandir($dir);

        foreach ($files as $file)
        {
            //sanity Check
            if ($file == '.' || $file == '..')
            {
                continue;
            }
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if ($ext != 'log')
            {
                continue;
            }
            $datestring = substr($file, -14, -4);
            $listString .='  <tr><td>' . $datestring . '</td><td><a href="LogViewer.php?logfile=' . $file . '" data-log-date="' . $datestring . '">' . $file . "</a></td></tr>\n";
        }

        $listString .=' </tbody>
                </table>
            </div><!-- End of tablepage --> ';

        return $listString;
    }

    /**
     * Get the list of files in a directory and returns an array with file names in the
     * directory
     *
     * @param String $dir data directory
     * 
     * @return mixed <b>The file list in an array </b> on success or <b>FALSE</b> on failure.
     */
    public static function getfileList2($dir = "../data/")
    {

        // check that this $dir is populated and it's a directory
        if (!$dir || !is_dir($dir))
        {
            return false;
        }

        $logFileList = array();
        // Sort in ascending order - this is default
        $files = scandir($dir);

        foreach ($files as $key => $file)
        {
            //sanity Check
            if ($file == '.' || $file == '..')
            {
                continue;
            }
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if ($ext != 'log')
            {
                continue;
            }
            $datestring = substr($file, -14, -4);
            $logFileList[$key]['Date'] = $datestring;
            $logFileList[$key]['LogFile'] = $file;
        }

        return $logFileList;
    }

    /**
     * if the parameter passed in is > 100 then return 99+ otherwise return as it is
     *
     * @param String $data the data
     * 
     * @return mixed <b>99+</b> on success or <b>FALSE</b> on failure.
     */
    public static function comapact99($data)
    {
        if (!$data)
        {
            return $data;
        }

        if ($data >= 100)
        {
            return '99+';
        }

        return $data;
    }

    /**
     * Get the date time zone list from {@link DateTimeZone::listIdentifiers} if it exists
     * else return the hardcoded list of DateTimeZone
     * 
     * @return array <b>DateTimeZone/b> list.
     */
    public static function getTimeZoneList()
    {

        if (!method_exists("DateTimeZone", "listIdentifiers"))
        {
            $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
            return $timezones;
        }

        $timezones = array(
            "Africa/Abidjan", "Africa/Accra", "Africa/Addis_Ababa", "Africa/Algiers", "Africa/Asmara", "Africa/Asmera",
            "Africa/Bamako", "Africa/Bangui", "Africa/Banjul", "Africa/Bissau", "Africa/Blantyre", "Africa/Brazzaville",
            "Africa/Bujumbura", "Africa/Cairo", "Africa/Casablanca", "Africa/Ceuta", "Africa/Conakry", "Africa/Dakar",
            "Africa/Dar_es_Salaam", "Africa/Djibouti", "Africa/Douala", "Africa/El_Aaiun", "Africa/Freetown", "Africa/Gaborone",
            "Africa/Harare", "Africa/Johannesburg", "Africa/Juba", "Africa/Kampala", "Africa/Khartoum", "Africa/Kigali",
            "Africa/Kinshasa", "Africa/Lagos", "Africa/Libreville", "Africa/Lome", "Africa/Luanda", "Africa/Lubumbashi",
            "Africa/Lusaka", "Africa/Malabo", "Africa/Maputo", "Africa/Maseru", "Africa/Mbabane", "Africa/Mogadishu",
            "Africa/Monrovia", "Africa/Nairobi", "Africa/Ndjamena", "Africa/Niamey", "Africa/Nouakchott", "Africa/Ouagadougou",
            "Africa/Porto-Novo", "Africa/Sao_Tome", "Africa/Timbuktu", "Africa/Tripoli", "Africa/Tunis", "Africa/Windhoek",
            "America/Adak", "America/Anchorage", "America/Anguilla", "America/Antigua", "America/Araguaina", "America/Argentina/Buenos_Aires",
            "America/Argentina/Catamarca", "America/Argentina/ComodRivadavia", "America/Argentina/Cordoba", "America/Argentina/Jujuy",
            "America/Argentina/La_Rioja", "America/Argentina/Mendoza", "America/Argentina/Rio_Gallegos", "America/Argentina/Salta",
            "America/Argentina/San_Juan", "America/Argentina/San_Luis", "America/Argentina/Tucuman", "America/Argentina/Ushuaia",
            "America/Aruba", "America/Asuncion", "America/Atikokan", "America/Atka", "America/Bahia", "America/Bahia_Banderas",
            "America/Barbados", "America/Belem", "America/Belize", "America/Blanc-Sablon", "America/Boa_Vista", "America/Bogota",
            "America/Boise", "America/Buenos_Aires", "America/Cambridge_Bay", "America/Campo_Grande", "America/Cancun", "America/Caracas",
            "America/Catamarca", "America/Cayenne", "America/Cayman", "America/Chicago", "America/Chihuahua", "America/Coral_Harbour",
            "America/Cordoba", "America/Costa_Rica", "America/Creston", "America/Cuiaba", "America/Curacao", "America/Danmarkshavn",
            "America/Dawson", "America/Dawson_Creek", "America/Denver", "America/Detroit", "America/Dominica", "America/Edmonton",
            "America/Eirunepe", "America/El_Salvador", "America/Ensenada", "America/Fort_Wayne", "America/Fortaleza", "America/Glace_Bay",
            "America/Godthab", "America/Goose_Bay", "America/Grand_Turk", "America/Grenada", "America/Guadeloupe", "America/Guatemala",
            "America/Guayaquil", "America/Guyana", "America/Halifax", "America/Havana", "America/Hermosillo", "America/Indiana/Indianapolis",
            "America/Indiana/Knox", "America/Indiana/Marengo", "America/Indiana/Petersburg", "America/Indiana/Tell_City",
            "America/Indiana/Vevay", "America/Indiana/Vincennes", "America/Indiana/Winamac", "America/Indianapolis", "America/Inuvik",
            "America/Iqaluit", "America/Jamaica", "America/Jujuy", "America/Juneau", "America/Kentucky/Louisville", "America/Kentucky/Monticello",
            "America/Knox_IN", "America/Kralendijk", "America/La_Paz", "America/Lima", "America/Los_Angeles", "America/Louisville",
            "America/Lower_Princes", "America/Maceio", "America/Managua", "America/Manaus", "America/Marigot", "America/Martinique",
            "America/Matamoros", "America/Mazatlan", "America/Mendoza", "America/Menominee", "America/Merida", "America/Metlakatla",
            "America/Mexico_City", "America/Miquelon", "America/Moncton", "America/Monterrey", "America/Montevideo", "America/Montreal",
            "America/Montserrat", "America/Nassau", "America/New_York", "America/Nipigon", "America/Nome", "America/Noronha",
            "America/North_Dakota/Beulah", "America/North_Dakota/Center", "America/North_Dakota/New_Salem", "America/Ojinaga",
            "America/Panama", "America/Pangnirtung", "America/Paramaribo", "America/Phoenix", "America/Port-au-Prince", "America/Port_of_Spain",
            "America/Porto_Acre", "America/Porto_Velho", "America/Puerto_Rico", "America/Rainy_River", "America/Rankin_Inlet",
            "America/Recife", "America/Regina", "America/Resolute", "America/Rio_Branco", "America/Rosario", "America/Santa_Isabel",
            "America/Santarem", "America/Santiago", "America/Santo_Domingo", "America/Sao_Paulo", "America/Scoresbysund", "America/Shiprock",
            "America/Sitka", "America/St_Barthelemy", "America/St_Johns", "America/St_Kitts", "America/St_Lucia", "America/St_Thomas",
            "America/St_Vincent", "America/Swift_Current", "America/Tegucigalpa", "America/Thule", "America/Thunder_Bay", "America/Tijuana",
            "America/Toronto", "America/Tortola", "America/Vancouver", "America/Virgin", "America/Whitehorse", "America/Winnipeg",
            "America/Yakutat", "America/Yellowknife", "Antarctica/Casey", "Antarctica/Davis", "Antarctica/DumontDUrville", "Antarctica/Macquarie",
            "Antarctica/Mawson", "Antarctica/McMurdo", "Antarctica/Palmer", "Antarctica/Rothera", "Antarctica/South_Pole", "Antarctica/Syowa",
            "Antarctica/Troll", "Antarctica/Vostok", "Arctic/Longyearbyen", "Asia/Aden", "Asia/Almaty", "Asia/Amman", "Asia/Anadyr", "Asia/Aqtau",
            "Asia/Aqtobe", "Asia/Ashgabat", "Asia/Ashkhabad", "Asia/Baghdad", "Asia/Bahrain", "Asia/Baku", "Asia/Bangkok", "Asia/Beirut",
            "Asia/Bishkek", "Asia/Brunei", "Asia/Calcutta", "Asia/Choibalsan", "Asia/Chongqing", "Asia/Chungking", "Asia/Colombo",
            "Asia/Dacca", "Asia/Damascus", "Asia/Dhaka", "Asia/Dili", "Asia/Dubai", "Asia/Dushanbe", "Asia/Gaza", "Asia/Harbin", "Asia/Hebron",
            "Asia/Ho_Chi_Minh", "Asia/Hong_Kong", "Asia/Hovd", "Asia/Irkutsk", "Asia/Istanbul", "Asia/Jakarta", "Asia/Jayapura",
            "Asia/Jerusalem", "Asia/Kabul", "Asia/Kamchatka", "Asia/Karachi", "Asia/Kashgar", "Asia/Kathmandu", "Asia/Katmandu",
            "Asia/Khandyga", "Asia/Kolkata", "Asia/Krasnoyarsk", "Asia/Kuala_Lumpur", "Asia/Kuching", "Asia/Kuwait", "Asia/Macao",
            "Asia/Macau", "Asia/Magadan", "Asia/Makassar", "Asia/Manila", "Asia/Muscat", "Asia/Nicosia", "Asia/Novokuznetsk",
            "Asia/Novosibirsk", "Asia/Omsk", "Asia/Oral", "Asia/Phnom_Penh", "Asia/Pontianak", "Asia/Pyongyang", "Asia/Qatar",
            "Asia/Qyzylorda", "Asia/Rangoon", "Asia/Riyadh", "Asia/Saigon", "Asia/Sakhalin", "Asia/Samarkand", "Asia/Seoul",
            "Asia/Shanghai", "Asia/Singapore", "Asia/Taipei", "Asia/Tashkent", "Asia/Tbilisi", "Asia/Tehran", "Asia/Tel_Aviv",
            "Asia/Thimbu", "Asia/Thimphu", "Asia/Tokyo", "Asia/Ujung_Pandang", "Asia/Ulaanbaatar", "Asia/Ulan_Bator", "Asia/Urumqi",
            "Asia/Ust-Nera", "Asia/Vientiane", "Asia/Vladivostok", "Asia/Yakutsk", "Asia/Yekaterinburg", "Asia/Yerevan", "Atlantic/Azores",
            "Atlantic/Bermuda", "Atlantic/Canary", "Atlantic/Cape_Verde", "Atlantic/Faeroe", "Atlantic/Faroe", "Atlantic/Jan_Mayen",
            "Atlantic/Madeira", "Atlantic/Reykjavik", "Atlantic/South_Georgia", "Atlantic/St_Helena", "Atlantic/Stanley", "Australia/ACT",
            "Australia/Adelaide", "Australia/Brisbane", "Australia/Broken_Hill", "Australia/Canberra", "Australia/Currie", "Australia/Darwin",
            "Australia/Eucla", "Australia/Hobart", "Australia/LHI", "Australia/Lindeman", "Australia/Lord_Howe", "Australia/Melbourne",
            "Australia/North", "Australia/NSW", "Australia/Perth", "Australia/Queensland", "Australia/South", "Australia/Sydney",
            "Australia/Tasmania", "Australia/Victoria", "Australia/West", "Australia/Yancowinna", "Brazil/Acre", "Brazil/DeNoronha",
            "Brazil/East", "Brazil/West", "Canada/Atlantic", "Canada/Central", "Canada/East-Saskatchewan", "Canada/Eastern", "Canada/Mountain",
            "Canada/Newfoundland", "Canada/Pacific", "Canada/Saskatchewan", "Canada/Yukon", "CET", "Chile/Continental", "Chile/EasterIsland",
            "CST6CDT", "Cuba", "EET", "Egypt", "Eire", "EST", "EST5EDT", "Etc/GMT", "Etc/GMT+0", "Etc/GMT+1", "Etc/GMT+10", "Etc/GMT+11", "Etc/GMT+12",
            "Etc/GMT+2", "Etc/GMT+3", "Etc/GMT+4", "Etc/GMT+5", "Etc/GMT+6", "Etc/GMT+7", "Etc/GMT+8", "Etc/GMT+9", "Etc/GMT-0", "Etc/GMT-1",
            "Etc/GMT-10", "Etc/GMT-11", "Etc/GMT-12", "Etc/GMT-13", "Etc/GMT-14", "Etc/GMT-2", "Etc/GMT-3", "Etc/GMT-4", "Etc/GMT-5",
            "Etc/GMT-6", "Etc/GMT-7", "Etc/GMT-8", "Etc/GMT-9", "Etc/GMT0", "Etc/Greenwich", "Etc/UCT", "Etc/Universal", "Etc/UTC",
            "Etc/Zulu", "Europe/Amsterdam", "Europe/Andorra", "Europe/Athens", "Europe/Belfast", "Europe/Belgrade", "Europe/Berlin",
            "Europe/Bratislava", "Europe/Brussels", "Europe/Bucharest", "Europe/Budapest", "Europe/Busingen", "Europe/Chisinau",
            "Europe/Copenhagen", "Europe/Dublin", "Europe/Gibraltar", "Europe/Guernsey", "Europe/Helsinki", "Europe/Isle_of_Man",
            "Europe/Istanbul", "Europe/Jersey", "Europe/Kaliningrad", "Europe/Kiev", "Europe/Lisbon", "Europe/Ljubljana", "Europe/London",
            "Europe/Luxembourg", "Europe/Madrid", "Europe/Malta", "Europe/Mariehamn", "Europe/Minsk", "Europe/Monaco", "Europe/Moscow",
            "Europe/Nicosia", "Europe/Oslo", "Europe/Paris", "Europe/Podgorica", "Europe/Prague", "Europe/Riga", "Europe/Rome", "Europe/Samara",
            "Europe/San_Marino", "Europe/Sarajevo", "Europe/Simferopol", "Europe/Skopje", "Europe/Sofia", "Europe/Stockholm", "Europe/Tallinn",
            "Europe/Tirane", "Europe/Tiraspol", "Europe/Uzhgorod", "Europe/Vaduz", "Europe/Vatican", "Europe/Vienna", "Europe/Vilnius",
            "Europe/Volgograd", "Europe/Warsaw", "Europe/Zagreb", "Europe/Zaporozhye", "Europe/Zurich", "Factory", "GB", "GB-Eire", "GMT",
            "GMT+0", "GMT-0", "GMT0", "Greenwich", "Hongkong", "HST", "Iceland", "Indian/Antananarivo", "Indian/Chagos", "Indian/Christmas",
            "Indian/Cocos", "Indian/Comoro", "Indian/Kerguelen", "Indian/Mahe", "Indian/Maldives", "Indian/Mauritius", "Indian/Mayotte",
            "Indian/Reunion", "Iran", "Israel", "Jamaica", "Japan", "Kwajalein", "Libya", "MET", "Mexico/BajaNorte", "Mexico/BajaSur",
            "Mexico/General", "MST", "MST7MDT", "Navajo", "NZ", "NZ-CHAT", "Pacific/Apia", "Pacific/Auckland", "Pacific/Chatham", "Pacific/Chuuk",
            "Pacific/Easter", "Pacific/Efate", "Pacific/Enderbury", "Pacific/Fakaofo", "Pacific/Fiji", "Pacific/Funafuti", "Pacific/Galapagos",
            "Pacific/Gambier", "Pacific/Guadalcanal", "Pacific/Guam", "Pacific/Honolulu", "Pacific/Johnston", "Pacific/Kiritimati",
            "Pacific/Kosrae", "Pacific/Kwajalein", "Pacific/Majuro", "Pacific/Marquesas", "Pacific/Midway", "Pacific/Nauru", "Pacific/Niue",
            "Pacific/Norfolk", "Pacific/Noumea", "Pacific/Pago_Pago", "Pacific/Palau", "Pacific/Pitcairn", "Pacific/Pohnpei", "Pacific/Ponape",
            "Pacific/Port_Moresby", "Pacific/Rarotonga", "Pacific/Saipan", "Pacific/Samoa", "Pacific/Tahiti", "Pacific/Tarawa",
            "Pacific/Tongatapu", "Pacific/Truk", "Pacific/Wake", "Pacific/Wallis", "Pacific/Yap", "Poland", "Portugal", "PRC", "PST8PDT",
            "ROC", "ROK", "Singapore", "Turkey", "UCT", "Universal", "US/Alaska", "US/Aleutian", "US/Arizona", "US/Central", "US/East-Indiana", "US/Eastern", "US/Hawaii", "US/Indiana-Starke", "US/Michigan", "US/Mountain", "US/Pacific", "US/Pacific-New",
            "US/Samoa", "UTC", "W-SU", "WET", "Zulu"
        );
        return $timezones;
    }

    /**
     * Set the time zone of the API 
     * 
     * @param String $timezones the date time zone to be set
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function settimezone($timezones)
    {

        if (!$timezones)
        {
            return false;
        }
        return date_default_timezone_set($timezones);
    }

    /**
     *  gets the data from a URL 
     * 
     * @param String $url The URL to which data is gotten from
     * 
     * @return mixed <b>URL Data</b> on success or <b>FALSE</b> on failure.
     */
    public static function getData($url)
    {
        if (!$url)
        {
            return false;
        }
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * Dynamically increases php memory limit if the limit is almost reached 
     * depending on the threshold set in this function
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function memoryTickHandler()
    {

        $usage = memory_get_usage();
        $Memory = ini_get('memory_limit');
        $Memory = self::toByteSize($Memory);
        $delta = ($usage / $Memory) * 100;
        $threshold = 98; //percent threshold before adding more memory;
        if ($delta < $threshold)
        {
            return false;
        }
        $added = bcmul($Memory, 0.3); //add 30% more than the original memory limit
        $total = $added + $Memory;
        ini_set('memory_limit', (int) $total);
        sleep(1);
        return true;
    }

    /**
     * This is used to get the message satus for snowflake's Read only Restful
     * webservice API
     * 
     * @param int $code The code of the message to return
     * 
     * @return String The corresponding status message
     * 
     */
    public static function getStatusMessage($code = 500)
    {
        $status = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported');

        return $status[$code];
    }

    /**
     * Set the header for a RESTful API depending on the code and 
     * return content type which might be in html,xml or json
     * 
     * @param int $code The code of the message to return
     * @param String $content_type The content type of the header
     * 
     */
    public static function setHeaders($code = 500, $content_type = "html")
    {
        if (strcasecmp($content_type, 'json') == 0 || strcasecmp($content_type, 'jsonhtml') == 0)
        {
            $content_type = "application/json; charset=utf-8";
        }
        elseif (strcasecmp($content_type, 'xml') == 0)
        {
            $content_type = "application/xml; charset=utf-8";
        }
        elseif (strcasecmp($content_type, 'html') == 0)
        {
            $content_type = "text/html; charset=utf-8";
        }
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("HTTP/1.1 " . $code . " " . self::getStatusMessage($code));
        header("Content-Type:" . $content_type);
    }

    /**
     * Deliver HTTP Response,HTTP Response header and HTTP response content type
     * for snowflake's RESTful API and exit
     * 
     * @param mixed $data The desired HTTP response data 
     * @param int $code the status code:200,500..., code and data to 
     * return in the format specified
     * @param string $content_type This contains format of
     * The desired HTTP response content type: [json, html, xml]
     * 
     * @return mixed <b>HTTP Response Header and Data</b> on success or <b>FALSE</b> on failure.
     * 
     * */
    public static function deliverResponseAndExit($data, $code, $content_type = 'html')
    {

        if (!$data)
        {
            return false;
        }
        $m_content_type = $content_type;
        $m_code = ($code) ? $code : 200;
        // Set HTTP Response and HTTP Response Content Type
        self::setHeaders($m_code, $m_content_type);
        // Process different content types
        if (strcasecmp($m_content_type, 'json') == 0)
        {
            // Format data into a JSON response
            $json_response = json_encode($data);
            // Deliver formatted data
            echo $json_response;
        }
        elseif (strcasecmp($m_content_type, 'jsonhtml') == 0)
        {
            $startedAt = time();
            self::sendSSEMsg($startedAt, $data);
        }
        elseif (strcasecmp($m_content_type, 'xml') == 0)
        {
            // Format data into an XML response (This is only good at handling string data, not arrays)
            $packages = new SimpleXMLElement("<SnoflakesData></SnoflakesData>");
            $packages->addChild('code', $code);
            $packages->addChild('data', $data);

            //format for pretty printing
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($packages->asXML());
            $xmlResponse = $dom->saveXML();
            header('Content-Length: ' . strlen($xmlResponse));
            // Deliver formatted data
            echo $xmlResponse;
        }
        elseif (strcasecmp($m_content_type, 'html') == 0)
        {
            // Deliver formatted data
            echo $data;
        }
        die();
    }

    /**
     * Replace Snowflakes Hash tags within the data string provided with 
     * snoflakes configuration data
     *  
     *
     * @param String $data that contains the string containing the hash symbols
     * @param String $inifile <p> The configuration file </p> 
     * @param String $shareURL <p> A share url for social sites. This value if set 
     * replaces the #SHAREURL# tag in the data, because this is dynamic data and
     * might change given if it is snowflakes, snowflake events or snowflake gallery</p>
     * 
     * @return mixed <b>Rss string</b> on success or <b>FALSE</b> on failure.
     */
    public static function replaceSFHashes(&$data, $inifile = '../config/config.ini', $shareURL = '')
    {
        if (!$data)
        {
            return false;
        }
        if (is_array($data))
        {
            foreach ($data as $key => $value)
            {
                self::replaceSFHashes($data[$key], $inifile, $shareURL);
            }
        }

        $siteSettings = new settingsStruct($inifile);
        $Powerlink = $siteSettings->m_sfUrl . "resources/images/Snowflakes2.png";
        $UploadImgUrl = $siteSettings->m_sfGalleryUrl;
        $imageMissing = $UploadImgUrl . "missing_default.png";

        $newData = str_replace('#SNOWFLAKESURL#', $siteSettings->m_sfUrl, $data);
        $newData1 = str_replace('#POWERLINK#', $Powerlink, $newData);
        $newData2 = str_replace('#MISSINGIMG#', $imageMissing, $newData1);
        $newData3 = str_replace('#SFGALLERYIMGURL#', $siteSettings->m_sfGalleryImgUrl, $newData2);
        $newData4 = str_replace('#SFGALLERYTHUMBURL#', $siteSettings->m_sfGalleryThumbUrl, $newData3);
        $data = $newData4;
        if (strlen($shareURL) > 0)
        {
            $data = str_replace('#SHAREURL#', $shareURL, $newData4);
        }
    }

}

class databaseParam
{

    //Db Info           //config Name [db]
    var $m_hostName;    //host
    var $m_dbName;      //dbname
    var $m_dbType;      //type
    var $m_dbUsername;  //username
    var $m_dbPassword;  //password
    var $m_isenc;       //flag to indicate if password is encrypted
    var $m_key;         //key
    var $m_admin_email; //admin_email
    var $m_time_zone;   //time_zone

    /**
     * Initialize the database parameter by loading data from the 
     * configuration file 
     * 
     * @param String $inifile the ini config file for snowflakes API
     * 
     */

    public function __construct($inifile = '../config/config.ini')
    {
        $this->init($inifile);
    }

    /**
     * Initialize the database parameter by loading data from the config file
     *
     * @param String $inifile the ini config file for snowflakes API
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function init($inifile = '../config/config.ini')
    {
        $m_data = Config::getConfig("db", $inifile);
        return $this->populate($m_data);
    }

    /**
     * Populate each member of {@link databaseParam} given the input parameters
     *
     * @param array $array to be used to populate members of {@link databaseParam}
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function populate($array)
    {
        if (empty($array) || !is_array($array))
        {
            return false;
        }
        //Db Info           //config Name [db]
        $this->m_hostName = $array['host'];
        $this->m_dbName = $array['dbname'];
        $this->m_dbType = $array['type'];
        $this->m_dbUsername = $array['username'];
        $this->m_dbPassword = $array['password'];
        $this->m_admin_email = $array['admin_email'];
        $this->m_time_zone = $array['time_zone'];
        $this->m_isenc = $array['isenc'];
        $this->m_key = str_replace('.', '', "$this->m_hostName$this->m_dbName$this->m_dbType$this->m_dbUsername");
    }

    /**
     * This gets all the data ready for a database connection and returns it 
     * 
     * @return array The array of parameters used to connect to database.
     */
    public function dbArray()
    {
        $sqlArray = array('type' => $this->m_dbType,
            'host' => $this->m_hostName,
            'username' => $this->m_dbUsername,
            'password' => $this->m_isenc === "Y" ? sfUtils::decrypt($this->m_dbPassword, $this->m_key) : $this->m_dbPassword,
            'database' => $this->m_dbName);

        return $sqlArray;
    }

}

class dataDirParam
{

    //datadir Info           //config Name [datadir]
    var $m_logdir;  //logdir
    var $m_path;        //path
    var $m_resources; //resources
    var $m_uploadGalleryDir; //uploadGalleryDir
    var $m_galleryImgDir; //galleryImgDir
    var $m_galleryThumbDir; //galleryThumbDir

    /**
     * Initialize the data directory parameter by loading data from the 
     * configuration file 
     * 
     * @param String $inifile the ini config file for snowflakes API
     */

    public function __construct($inifile = '../config/config.ini')
    {
        $this->init($inifile);
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
        $m_data = Config::getConfig("datadir", $inifile);
        return $this->populate($m_data);
    }

    /**
     * Populate each member of {@link dataDirParam} given the input parameters
     *
     * @param array $array to be used to populate members of {@link dataDirParam}
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function populate($array)
    {
        if (empty($array))
        {
            return false;
        }
        //datadir Info           //config Name [datadir]
        $this->m_logdir = $array['logdir'];
        $this->m_path = $array['path'];
        $this->m_resources = $array['resources'];
        $this->m_uploadGalleryDir = $array['uploadGalleryDir'];
        $this->m_galleryImgDir = $array['galleryImgDir'];
        $this->m_galleryThumbDir = $array['galleryThumbDir'];
    }

}

class settingsStruct
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
     * Populate each member of {@link settingsStruct} given the input parameters
     *
     * @param array $array to be used to populate members of {@link settingsStruct}
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

?>
