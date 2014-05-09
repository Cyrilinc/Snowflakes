<?php

/**
 * Description of sf
 *
 * @author cyrildavids
 */
//Disable error reporting
error_reporting(0);
set_error_handler("sfLogError::sfErrorHandler");
date_default_timezone_set('Europe/London');

class snowflakeStruct {

    var $m_id;
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

    public function isSfPopulated() {
        return isset($this->m_title) && isset($this->m_body_text) && (isset($this->m_created_by) || isset($this->m_edited_by)) && (isset($this->m_created) || isset($this->m_edited));
    }

    public function populate($array) {
        if (empty($array)) {
            return false;
        }

        $this->m_id = isset($array['id']) ? $array['id'] : "";
        $this->m_title = strlen($array['title']) > 0 ? $array['title'] : "";
        $this->m_body_text = $array['body_text'];
        $this->m_publish = isset($array['publish']) ? $array['publish'] : "";
        $this->m_image_name = isset($array['image_name']) ? $array['image_name'] : "";
        $this->m_gallery = isset($array['gallery']) ? $array['gallery'] : "";
        $this->m_created = isset($array['created']) ? $array['created'] : "";
        $this->m_created_by = isset($array['created_by']) ? $array['created_by'] : "";
        $this->m_edited = isset($array['edited']) ? $array['edited'] : "";
        $this->m_edited_by = isset($array['edited_by']) ? $array['edited_by'] : "";
        $this->m_deleted = isset($array['deleted']) ? $array['deleted'] : "";
        $this->m_flake_it = isset($array['flake_it']) ? $array['flake_it'] : "";

        return true;
    }

    public function getSnowflakesByid($conn, $id) {
        if (!$conn || !$id) {
            return false;
        }

        $sql = "SELECT * FROM snowflakes WHERE id=$id;";
        if (!$conn->fetch($sql)) {
            return false;
        }

        $result = $conn->getResultArray();
        if (empty($result)) {
            return false;
        }
        return $this->populate($result[0]);
    }

    public static function getImageNameById($conn, $id) {

        if (!$conn || !$id || $id == -1) {
            return false;
        }

        $sql = "SELECT image_name FROM snowflakes WHERE id=$id;";
        if (!$conn->fetch($sql)) {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result)) {
            return false;
        }
        return $result[0]['image_name'];
    }

    public function addSnowflake($conn) {

        if (!$this->isSfPopulated() || !$conn) {
            return false;
        }

        $insertSQL = 'INSERT INTO snowflakes SET title="' . sfUtils::escape($this->m_title) .
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

    public function updateSnowflake($conn) {

        if (!$this->isSfPopulated() || !$conn || sfUtils::isEmpty($this->m_id)) {
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

    public function deleteSnowflake($conn, $setDelete = false) {

        if (sfUtils::isEmpty($this->m_id) || !$conn) {
            return false;
        }

        $sql = "";
        if ($setDelete == false) {
            $sql = "DELETE FROM snowflakes ";
        } else {
            $sql = "UPDATE snowflakes SET deleted=1 "; // if the user
            if (isset($_SESSION['MM_Username'])) {
                $sql.=',edited_by="' . $_SESSION['MM_Username'] . '",edited="' . time() . '" ';
            }
        }

        $sql.="WHERE id=" . $this->m_id . ";";

        if (strlen($this->m_image_dir) > 0 && $setDelete == false) {
            $imagename = $this->m_image_dir . $this->m_image_name;
            return $conn->execute($sql) && sfUtils::Deletefile($imagename);
        }

        return $conn->execute($sql);
    }

    public function getSnowflakeID($conn) {

        /// Sanity Checks
        if (!$this->isSfPopulated() || !$conn) {
            return false;
        }

        if (!sfUtils::isEmpty($this->m_id)) {
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

        if (!$conn->fetch($sql)) {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result)) {
            return false;
        }
        $this->m_id = $result[0]['id'];
        return $this->m_id;
    }

    public function printsnowlakes() {
        $str = 'title="' . $this->m_title . '"<br> ';
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

}

class userStruct {

    var $m_id;
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

    public function init($username, $password, $email, $access_level, $image_name = 'default.png') {
        $this->m_username = $username;
        $this->m_password = md5($password);
        $this->m_email = $email;
        $this->m_access_level = $access_level;
        $this->m_access_name = sfUtils::UserLevelName($access_level);
        $this->m_reset_link = hash("sha256", $this->m_password . " " . $email . " " . $username);
        $this->m_image_name = $image_name;
    }

    public function isPopulated() {
        return isset($this->m_username) && isset($this->m_password) && isset($this->m_email) && isset($this->m_access_level) && isset($this->m_access_name);
    }

    public function populate($value = array()) {
        if (empty($value)) {
            return false;
        }
        $this->m_id = isset($value['id']) ? $value['id'] : "";
        $this->m_username = isset($value['username']) ? $value['username'] : "";
        $this->m_password = isset($value['password']) ? $value['password'] : "";
        $this->m_reset_link = isset($value['reset_link']) ? $value['reset_link'] : "";
        $this->m_email = isset($value['email']) ? $value['email'] : "";
        $this->m_access_level = isset($value['access_level']) ? $value['access_level'] : "";
        $this->m_access_name = isset($value['access_name']) ? $value['access_name'] : "";
        $this->m_image_name = isset($value['image_name']) ? $value['image_name'] : "";
        $this->m_deleted = isset($value['deleted']) ? $value['deleted'] : "";
        $this->m_flake_it = isset($value['flake_it']) ? $value['flake_it'] : "";
        $this->m_logged_in = isset($value['logged_in']) ? $value['logged_in'] : "";
        $this->m_last_login = isset($value['last_login']) ? $value['last_login'] : "";

        return true;
    }

    public function getUserByUsername($conn, $username) {
        if (!$conn || !$username) {
            return false;
        }

        $sql = 'SELECT * FROM snowflakes_users WHERE username ="' . $username . '"';
        if (!$conn->fetch($sql)) {
            return false;
        }

        $result = $conn->getResultArray();
        if (empty($result)) {
            return false;
        }
        return $this->populate($result[0]);
    }

    public static function getImageNameById($conn, $id) {

        if (!$conn || !$id || $id == -1) {
            return false;
        }

        $sql = "SELECT image_name FROM snowflakes_users WHERE id=$id";
        if (!$conn->fetch($sql)) {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result)) {
            return false;
        }
        return $result[0]['image_name'];
    }

    public function getUserByid($conn, $id) {
        if (!$conn || !$id || $id == -1) {
            return false;
        }

        $sql = "SELECT * FROM snowflakes_users WHERE id=$id;";
        if (!$conn->fetch($sql)) {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result)) {
            return false;
        }
        return $this->populate($result[0]);
    }

    public function getUserByResetLink($conn, $resetLink) {
        if (!$conn || !$resetLink) {
            return false;
        }

        $sql = "SELECT * FROM snowflakes_users WHERE reset_link=\"$resetLink\";";
        if (!$conn->fetch($sql)) {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result)) {
            return false;
        }
        return $this->populate($result[0]);
    }

    public function loginUser($conn, $username, $password) {
        if (!$conn || !$username || !$password) {
            return false;
        }

        $sql = "SELECT * FROM snowflakes_users WHERE username='" . $username . "' AND password='" . $password . "';";
        $conn->fetch($sql, false);
        $result = $conn->getResultArray();
        if (empty($result)) {
            return false;
        }
        return $this->populate($result[0]);
    }

    public function AddUser($conn) {

        if (!$this->isPopulated() || !$conn) {
            return false;
        }

        $insertSQL = 'INSERT INTO snowflakes_users 
      SET username="' . $this->m_username . '", 
          password="' . $this->m_password . '",  
          reset_link="' . $this->m_reset_link . '",
          email="' . $this->m_email . '",
          image_name="' . $this->m_image_name . '",
          access_level="' . $this->m_access_level . '",
          access_name="' . $this->m_access_name . '";';

        return $conn->execute($insertSQL);
    }

    public function UpdateUser($conn) {

        if (!$this->isPopulated() || !$conn || sfUtils::isEmpty($this->m_id)) {
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

    public function deleteUser($conn, $setDelete = false) {

        if (sfUtils::isEmpty($this->m_id) || !$conn) {
            return false;
        }

        if ($setDelete == false) {
            $sql = "DELETE FROM snowflakes_users ";
        } else {
            $sql = "UPDATE snowflakes_users SET deleted=1 ";
        }

        $sql.="WHERE id=" . $this->m_id . ";";

        if (strlen($this->m_image_dir) > 0 && $setDelete == false) {
            $imagename = $this->m_image_dir . $this->m_image_name;
            return $conn->execute($sql) && sfUtils::Deletefile($imagename);
        }

        return $conn->execute($sql);
    }

    public function userExits($conn, $username) {

        if (!$username || !$conn) {
            return false;
        }

        $sql = "SELECT username FROM snowflakes_users WHERE username='" . $username . "';";
        if (!$conn->fetch($sql)) {
            return false;
        }
        return $conn->recordCount();
    }

    public function getUserID($conn) {

/// Sanity Checks
        if (!$this->isPopulated() || !$conn) {
            return false;
        }

        if (!sfUtils::isEmpty($this->m_id)) {
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

        if (!$conn->fetch($sql)) {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result)) {
            return false;
        }
        $this->m_id = $result[0]['id'];
        return $this->m_id;
    }

    public function printuser() {
        $str = "ID = " . $this->m_id . "<br>";
        $str .= "username = " . $this->m_username . "<br>";
        $str .= "password = " . $this->m_password . "<br>";
        $str .= "email = " . $this->m_email . "<br>";
        $str .= "image name = " . $this->m_image_name . "<br>";
        $str .= "access level = " . $this->m_access_level . "<br>";
        $str .= "access name = " . $this->m_access_name . "<br>";
        $str .= "reset link = " . $this->m_reset_link . "<br>";

        return $str;
    }

}

class galleryStruct {

    var $m_id;
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

    public function isSfGPopulated() {
        return isset($this->m_title) && isset($this->m_thumb_name) && strlen($this->m_thumb_name) > 0 && isset($this->m_image_name) && strlen($this->m_image_name) > 0 && (isset($this->m_created_by) || isset($this->m_edited_by)) && (isset($this->m_created) || isset($this->m_edited));
    }

    public function populate($array) {
        if (empty($array)) {
            return false;
        }

        $this->m_id = isset($array['id']) ? $array['id'] : "";
        $this->m_title = isset($array['title']) ? $array['title'] : "";
        $this->m_thumb_name = isset($array['thumb_name']) ? $array['thumb_name'] : "";
        $this->m_image_name = isset($array['image_name']) ? $array['image_name'] : "";
        $this->m_image_caption = isset($array['image_caption']) ? $array['image_caption'] : "";
        $this->m_publish = isset($array['publish']) ? $array['publish'] : "";
        $this->m_created = isset($array['created']) ? $array['created'] : "";
        $this->m_created_by = isset($array['created_by']) ? $array['created_by'] : "";
        $this->m_edited = isset($array['edited']) ? $array['edited'] : "";
        $this->m_edited_by = isset($array['edited_by']) ? $array['edited_by'] : "";
        $this->m_deleted = isset($array['deleted']) ? $array['deleted'] : "";
        $this->m_flake_it = isset($array['flake_it']) ? $array['flake_it'] : "";

        return true;
    }

    public function addSfGallery($conn) {

        if (!$this->isSfGPopulated() || !$conn) {
            return false;
        }

        $insertSQL = 'INSERT INTO snowflakes_gallery SET title="' . sfUtils::escape($this->m_title) .
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

    public function updateGallery($conn) {

        if (!$this->isSfGPopulated() || !$conn || sfUtils::isEmpty($this->m_id)) {
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

    public function deleteGallery($conn, $setDelete = false) {

        if (sfUtils::isEmpty($this->m_id) || !$conn) {
            return false;
        }

        if ($setDelete == false) {
            $sql = "DELETE FROM snowflakes_gallery ";
        } else {
            $sql = "UPDATE snowflakes_gallery SET deleted=1 ";
            if (isset($_SESSION['MM_Username'])) {
                $sql.=',edited_by="' . $_SESSION['MM_Username'] . '",edited="' . time() . '" ';
            }
        }
        $sql.="WHERE id=" . $this->m_id . ";";

        return $conn->execute($sql);
    }

    public function getGalleryByid($conn, $id) {
        if (!$conn || !$id || $id == -1) {
            return false;
        }

        $sql = "SELECT * FROM snowflakes_gallery WHERE id=$id;";
        if (!$conn->fetch($sql)) {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result)) {
            return false;
        }
        return $this->populate($result[0]);
    }

    public function getGalleryID($conn) {

        if (!$this->isSfGPopulated() || !$conn) {
            return false;
        }
        if (!sfUtils::isEmpty($this->m_id)) {
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

        if (!$conn->fetch($sql)) {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result)) {
            return false;
        }
        $this->m_id = $result[0]['id'];
        return $this->m_id;
    }

    public function printGallery() {
        $str = 'title="' . $this->m_title . '"<br> ';
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

}

class eventStruct {

    var $m_id;
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

    public function isPopulated() {
        return isset($this->m_title) && isset($this->m_body_text) && isset($this->m_event_time) && isset($this->m_event_date) && (isset($this->m_created_by) || isset($this->m_edited_by)) && (isset($this->m_created) || isset($this->m_edited));
    }

    public function populate($array) {
        if (empty($array)) {
            return false;
        }
        $this->m_id = isset($array['id']) ? $array['id'] : "";
        $this->m_title = isset($array['title']) ? $array['title'] : "";
        $this->m_body_text = isset($array['body_text']) ? $array['body_text'] : "";
        $this->m_publish = isset($array['publish']) ? $array['publish'] : "";
        $this->m_image_name = isset($array['image_name']) ? $array['image_name'] : "";
        $this->m_event_time = isset($array['event_time']) ? $array['event_time'] : "";
        $this->m_event_date = isset($array['event_date']) ? $array['event_date'] : "";
        $this->m_end_time = isset($array['end_time']) ? $array['end_time'] : "";
        $this->m_end_date = isset($array['end_date']) ? $array['end_date'] : "";
        $this->m_location = isset($array['location']) ? $array['location'] : "";
        $this->m_lat_long = isset($array['lat_long']) ? $array['lat_long'] : "";
        $this->m_created = isset($array['created']) ? $array['created'] : "";
        $this->m_created_by = isset($array['created_by']) ? $array['created_by'] : "";
        $this->m_edited = isset($array['edited']) ? $array['edited'] : "";
        $this->m_edited_by = isset($array['edited_by']) ? $array['edited_by'] : "";
        $this->m_deleted = isset($array['deleted']) ? $array['deleted'] : "";
        $this->m_flake_it = isset($array['flake_it']) ? $array['flake_it'] : "";
        return true;
    }

    public function getEventByid($conn, $id) {
        if (!$conn || !$id || $id == -1) {
            return false;
        }

        $sql = "SELECT * FROM snowflakes_events WHERE id=$id;";
        if (!$conn->fetch($sql)) {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result)) {
            return false;
        }
        return $this->populate($result[0]);
    }

    public static function getImageNameById($conn, $id) {

        if (!$conn || !$id || $id == -1) {
            return false;
        }

        $sql = "SELECT image_name FROM snowflakes_events WHERE id=$id;";
        if (!$conn->fetch($sql)) {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result)) {
            return false;
        }
        return $result[0]['image_name'];
    }

    public function AddEvent($conn) {

        if (!$this->isPopulated() || !$conn) {
            return false;
        }

        $sql = 'INSERT INTO snowflakes_events SET title="' . sfUtils::escape($this->m_title) .
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

    public function UpdateEvent($conn) {

        if (!$this->isPopulated() || !$conn || sfUtils::isEmpty($this->m_id)) {
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

    public function deleteEvent($conn, $setDelete = false) {

        if (sfUtils::isEmpty($this->m_id) || !$conn) {
            return false;
        }

        if ($setDelete == false) {
            $sql = "DELETE FROM snowflakes_events ";
        } else {
            $sql = "UPDATE snowflakes_events SET deleted=1";
            if (isset($_SESSION['MM_Username'])) {
                $sql.=',edited_by="' . $_SESSION['MM_Username'] . '",edited="' . time() . '" ';
            }
        }

        $sql.="WHERE id=" . $this->m_id . ";";

        if (strlen($this->m_image_dir) > 0 && $setDelete == false) {
            $imagename = $this->m_image_dir . $this->m_image_name;
            return $conn->execute($sql) && sfUtils::Deletefile($imagename);
        }

        return $conn->execute($sql);
    }

    public function getEventID($conn) {

        if (!$this->isPopulated() || !$conn) {
            return false;
        }
        if (!sfUtils::isEmpty($this->m_id)) {
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

        if (!$conn->fetch($sql)) {
            return false;
        }
        $result = $conn->getResultArray();
        if (empty($result)) {
            return false;
        }
        $this->m_id = $result[0]['id'];
        return $this->m_id;
    }

    public function printEvents() {
        $str = 'title="' . $this->m_title . '"<br> ';
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

}

class sfLogError {

    public static function SendBugMessage($bugMessage) {
        $body = "Message:\n $bugMessage\n";
        $subject = "Bug found ";
        $server_name = filter_input(INPUT_SERVER, 'SERVER_NAME');
        $sender = "noreply@$server_name";
## SEND MESSAGE ##
        return mail("bugreport@cyrilinc.co.uk", $subject, $body, "From: $sender");
    }

    public static function sfLogEntry($value, $user = "Snowflakes", $show = false, $dataDir = "../data") {

        if (!isset($value)) {
            return false;
        }

        $dataDir = is_dir($dataDir) ? $dataDir : "data";
        $username = isset($_SESSION['MM_Username']) ? $_SESSION['MM_Username'] : $user;
        $datetimelog = date("Y-m-d H:i:s");
        $datelog = date("Y-m-d");
        $logEntryValue = "[ $datetimelog ] ¦=> [$username] ¦=> $value \n";
        if ($show === true) {
            echo $logEntryValue;
        }

        $logFilename = "$dataDir/$username-LOG-$datelog.log";
        $logResource = fopen($logFilename, "a+");
        if ($logResource === false) {
            return;
        }

        fwrite($logResource, $logEntryValue);
        fclose($logResource);
    }

    public static function sfErrorHandler($errno, $errstr, $errfile, $errline) {
        switch ($errno) {
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

/*
  $divisor = 0;
  if ($divisor == 0) {
  sfLogError::sfLogEntry("Cannot divide by zero", "Default User");
  trigger_error("Cannot divide by zero", E_USER_WARNING);
  }
  //E_USER_WARNING
  //E_USER_ERROR
  //E_USER_NOTICE */

/**
 * Miscellaneous utility methods.
 */
final class sfUtils {

    private function __construct() {
        $this->init();
    }

    /**
     * System config.
     */
    public function init() {
// error reporting - all errors for development (ensure you have display_errors = On in your php.ini file)
        error_reporting(E_ALL | E_STRICT);
        set_exception_handler(array($this, 'handleException'));
// session
        if (!isset($_SESSION)) {
            session_start();
        }
    }

    /**
     * Exception handler.
     */
    public function handleException(Exception $ex) {
        $extra = array('message' => $ex->getMessage());
        if ($ex instanceof NotFoundException) {
            header('HTTP/1.0 404 Not Found');
            $this->runPage('404', $extra);
        } else {
// TODO log exception
            header('HTTP/1.1 500 Internal Server Error');
            $this->runPage('500', $extra);
        }
    }

    /**
     * Generate link.
     * @param string $page target page
     * @param array $params page parameters
     */
    public static function createLink($page, array $params = array()) {
        if (!$page) {
            return false;
        }
        return $page . http_build_query($params);
    }

    /**
     * Format date.
     * @param DateTime $date date to be formatted
     * @return string formatted date
     */
    public static function formatDate(DateTime $date = null) {
        if ($date === null) {
            return '';
        }
        return $date->format('d/m/Y');
    }

    /**
     * Format date and time.
     * @param DateTime $date date to be formatted
     * @return string formatted date and time
     */
    public static function formatDateTime(DateTime $date = null) {
        if ($date === null) {
            return '';
        }
        return $date->format('d/m/Y H:i');
    }

    /**
     * Redirect to the given page.
     * @param type $page target page
     * @param array $params page parameters
     */
    public static function redirect($page, array $params = array()) {
        header('Location: ' . self::createLink($page, $params));
        die();
    }

    /**
     * Get value of the URL param.
     * @return string parameter value
     * @throws NotFoundException if the param is not found in the URL
     */
    public static function getUrlParam($name) {
        if (!array_key_exists($name, $_GET)) {
            throw new NotFoundException('URL parameter "' . $name . '" not found.');
        }
        return $_GET[$name];
    }

    /**
     * Capitalize the first letter of the given string
     * @param string $string string to be capitalized
     * @return string capitalized string
     */
    public static function capitalize($string) {
        return ucfirst(mb_strtolower($string));
    }

    /**
     * Escape the given string
     * @param string $string string to be escaped
     * @return string escaped string
     */
    public static function escape($string) {
        if (defined('ENT_SUBSTITUTE')) {
            return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE);
        }

        return htmlspecialchars($string, ENT_QUOTES);
    }

    /**
     * Escape the given string
     * @param string $string string to be escaped
     * @return string escaped string
     */
    public static function sfescape($string) {
        $retValue = str_replace("'", "\'", $string);
        $retValue1 = str_replace("\n", "\\n", $retValue);
        $retValue2 = str_replace("\r", "\\r", $retValue1);
        $retValue3 = str_replace("\t", "\\t", $retValue2);
        $retValue4 = str_replace('"', '\"', $retValue3);
        return $retValue4;
    }

    public static function curPageURL() {
        $pageURL = "http";
        $https = filter_input(INPUT_SERVER, 'HTTPS');
        $server_port = filter_input(INPUT_SERVER, 'SERVER_PORT');
        $server_name = filter_input(INPUT_SERVER, 'SERVER_NAME');
        $request_uri = filter_input(INPUT_SERVER, 'REQUEST_URI');
        if ($https == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($server_port != "80") {
            $pageURL .= $server_name . ":" . $server_port . $request_uri;
        } else {
            $pageURL .= $server_name . $request_uri;
        }
        return $pageURL;
    }

///// Global Functions
    public static function isEmpty($data) {
        return (trim($data) === "" || $data === null);
    }

    public static function CreateDirectory($Dir, $permissions) {
        if (self::isEmpty($Dir)) {
            return false;
        }

//Create  a directory with the right permissions if it doesn't exist
        if (!is_dir($Dir)) {
            mkdir($Dir);
        }

        if (!self::isEmpty($permissions)) {
            chmod($Dir, $permissions);
        }

        return true;
    }

    public static function formatSizeUnits($bytes) {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    public static function toByteSize($p_sFormatted) {
        $aUnits = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4, 'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8);
        $sUnit = strtoupper(trim(substr($p_sFormatted, -2)));
        if (intval($sUnit) !== 0) {
            $sUnit = 'B';
        }
        if (!in_array($sUnit, array_keys($aUnits))) {
            return false;
        }
        $iUnits = trim(substr($p_sFormatted, 0, strlen($p_sFormatted) - 2));
        if (!intval($iUnits) == $iUnits) {
            return false;
        }
        return $iUnits * pow(1024, $aUnits[$sUnit]);
    }

    public static function removeElement(&$array, $key) { // pass array by reference
        if (!$array) {
            return false;
        }

        unset($array[$key]);
        return true;
    }

    public static function Deletefile($file) {
        if (strlen($file) == 0) {
            return false;
        }

        if (!file_exists($file) && is_dir($file)) {
            return false;
        }

        $exceptionFiles = array("default.png", "Snowflakes.png", "Snowflakes1.png", "Snowflakes2.png", "Snowflakes3.png", "missing_default.png");
        foreach ($exceptionFiles as $value) {
            if (strpos($file, $value) !== false) {
                return true;
            }
        }
        unlink($file);
        return true;
    }

    public static function UserLevelName($number) {
        $userName = " ";
        switch ($number) {
            case 1:
                $userName = "Author/Editor"; /// Create snowflakes Events and gallery but but not publish
                break;
            case 2:
                $userName = "Publisher"; /// Can do all the roles of an Author/Editor and publish and unpublish flakes and can only add, edit, veiw or delete own snowflakes
                break;
            case 3:
                $userName = "Manager"; //Can do all the role of the publisher and also add, edit, veiw or delete all snowflakes
                break;
            case 4:
                $userName = "Administrator"; // Can do everything a manager can do as well as add and remove users
                break;
            case 5:
                $userName = "Super Administrator"; // Can do everything an adminstrator can do and also change system settings for snowflake
                break;
            default :
                $userName = "";
        }

        return $userName;
    }

///todays date date and 7 days from now
    public static function todaysDate() {
        return time();
    }

    public static function TodaysDate7() {
        return strtotime("+7 day", time());
    }

    public static function maxDays() {
        return date("t");
    }

    public static function TodaysDateMonth() {
        return strtotime("+" . date("t") . " day", time());
    }

    public static function TodayDateThreeMonths() {
        return strtotime(" + 3 month", time());
    }

    public static function TodayDateSixMonths() {
        return strtotime(" + 6 month", time());
    }

    /**
     * Restrict Access To a Page: Grant or deny access to this page
     * */
    public static function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) {
// For security, start by assuming the visitor is NOT authorized. 
        $isValid = False;

// When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
// Therefore, we know that a user is NOT logged in if that Session variable is blank. 
        if (!empty($UserName)) {
// Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
// Parse the strings into arrays. 
            $arrUsers = Explode(",", $strUsers);
            $arrGroups = Explode(",", $strGroups);
            if (in_array($UserName, $arrUsers)) {
                $isValid = true;
            }

// Or, you may restrict access to only certain users based on their username. 
            if (in_array($UserGroup, $arrGroups)) {
                $isValid = true;
            }

            if (($strUsers == "") && true) {
                $isValid = true;
            }
        }
        return $isValid;
    }

    public static function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") {
        if (PHP_VERSION < 6) {
            $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
        }

        $theValue = $this->escape($theValue);
        switch ($theType) {
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

// Function to get the client IP address
    public static function getClientIp() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = filter_input(INPUT_SERVER, "HTTP_CLIENT_IP");
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = filter_input(INPUT_SERVER, "HTTP_X_FORWARDED_FOR");
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = filter_input(INPUT_SERVER, "HTTP_X_FORWARDED");
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = filter_input(INPUT_SERVER, "HTTP_FORWARDED_FOR");
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = filter_input(INPUT_SERVER, "HTTP_FORWARDED");
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = filter_input(INPUT_SERVER, "REMOTE_ADDR");
        } else {
            $ipaddress = 'UNKNOWN';
        }
        return $ipaddress;
    }

    public static function validateFiterInput($INPUT, $tag, $FILTER_VALIDATE) {

        $validate = filter_input($INPUT, $tag, $FILTER_VALIDATE);
        $tagtype = "";
        if ($FILTER_VALIDATE == FILTER_VALIDATE_BOOLEAN) {
            $tagtype = 'boolean';
        } else if ($FILTER_VALIDATE == FILTER_VALIDATE_EMAIL) {
            $tagtype = 'email address';
        } else if ($FILTER_VALIDATE == FILTER_VALIDATE_FLOAT) {
            $tagtype = 'float';
        } else if ($FILTER_VALIDATE == FILTER_VALIDATE_INT) {
            $tagtype = 'int';
        } else if ($FILTER_VALIDATE == FILTER_VALIDATE_IP) {
            $tagtype = 'Ip address';
        } else if ($FILTER_VALIDATE == FILTER_VALIDATE_REGEXP) {
            $tagtype = 'regexp';
        } else if ($FILTER_VALIDATE == FILTER_VALIDATE_URL) {
            $tagtype = 'url';
        }

        $returnsql = '';
        if ($validate) {
            $returnsql.= '<div style="background-color:green;padding:10px;color:#fff;font-size:16px;">
            <b>' . filter_input($INPUT, $tag) . '</b> is a valid ' . $tagtype . ' </div>';
        } else {
            $returnsql.= '<div style="background-color:red;padding:10px;color:#fff;font-size:16px;">
            <b>' . filter_input($INPUT, $tag) . '</b> is not a valid ' . $tagtype . ' </div>';
        }

        return $returnsql;
    }

    public static function createSfLink($type, $id, $inifile = '../config/config.ini') {
        if (!isset($type) || !isset($id)) {
            return false;
        }
        $settingsConfig = Config::getConfig("settings", $inifile);
        $link = "";

        switch ($type) {
            case "gallery": $link = $settingsConfig['m_sfUrl'] . "Gallery/ViewOne.php?Galleryid=" . $id;
                break;
            case "event": $link = $settingsConfig['m_sfUrl'] . "Events/ViewEvent.php?Eventid=" . $id;
                break;
            case "user": $link = $settingsConfig['m_sfUrl'] . "Users/Account.php?userId=" . $id;
                break;
            case "snowflake": $link = $settingsConfig['m_sfUrl'] . "Viewflake.php?pageid=" . $id;
                break;
            default :
                $link = $settingsConfig['m_sfUrl'] . "#";
        }
        return $link;
    }

    public static function getActivities($conn, $userName, $inifile = '../config/config.ini', $limitstart = 0, $limitend = 10) {

        if (!$userName || !$conn || $limitstart < 0 || $limitend < $limitstart) {
            return false;
        }
        $currentUser = $_SESSION['MM_Username'];
        $settingsConfig = Config::getConfig("settings", $inifile);

        $sql = 'SELECT * FROM snowflakes_change_log WHERE change_on!="user" AND (created_by="' . $userName . '" OR change_by="' . $userName . '")  ORDER BY change_datetime DESC';
        $sql .=$limitend > 0 ? " LIMIT $limitstart, $limitend ;" : ";";
        $conn->fetch($sql);
        $changeActivities = $conn->getResultArray();
        $totalActivities = $conn->recordCount();

        $activitiesString = "No activities yet";
        if ($totalActivities > 0) {
            $i = 0;
            $activitiesString = '<ul class="sfActivities">';
            do {
                $datetime = new DateTime($changeActivities[$i]['change_datetime']);
                $datedisplay = date('Ymd') == $datetime->format('Ymd') ? $datetime->format(" g:h a") : $datetime->format(" M j");
                $activitiesString.="<li>" . $datedisplay;
                $activitiesString.= " <a href=\"" . $settingsConfig['m_sfUrl'] . "Users/Account.php?userName=" . $changeActivities[$i]['change_by'] . "\">";
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

    public static function searchString($conn, $searchString, $filter = "snowflakes") {
        if (!$conn || !$searchString) {
            return false;
        }

        $searchResult = array();
        if ($filter == "Whole site" || $filter == "snowflakes") {
            $sql = "(SELECT id,title FROM snowflakes WHERE MATCH (title,created_by,edited_by,body_text) AGAINST ('" . sfUtils::escape($searchString) . "'))
                UNION
                (SELECT id,title FROM snowflakes WHERE LOWER(title) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%') 
                  OR LOWER(created_by) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%') 
                  OR LOWER(edited_by) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%') 
                  OR LOWER(body_text) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%'));";

            $conn->fetch($sql);
            $searchResult["snowflakes"] = $conn->getResultArray();
            if ($filter == "snowflakes") {
                return $searchResult;
            }
        }

        if ($filter == "Whole site" || $filter == "events") {
            $sql = "(SELECT id,title FROM snowflakes_events WHERE MATCH (title,created_by,edited_by,location,body_text) AGAINST ('" . sfUtils::escape($searchString) . "'))
                UNION
                (SELECT id,title FROM snowflakes_events WHERE LOWER(title) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%') 
                  OR LOWER(created_by) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%')  
                  OR LOWER(edited_by) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%') 
                  OR LOWER(location) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%') 
                  OR LOWER(body_text) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%'));";
            $conn->fetch($sql);

            if ($filter == "events") {
                $tag = array();
                $tag["events"] = $conn->getResultArray();
                return $tag;
            }

            $searchResult["events"] = $conn->getResultArray();
        }
        if ($filter == "Whole site" || $filter == "gallery") {
            $sql = "(SELECT id,title FROM snowflakes_gallery WHERE MATCH (title,created_by,edited_by,image_caption) AGAINST ('" . sfUtils::escape($searchString) . "'))
                UNION
                (SELECT id,title FROM snowflakes_gallery WHERE LOWER(title) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%') 
                  OR LOWER(created_by) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%')  
                  OR LOWER(edited_by) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%')  
                  OR LOWER(image_caption) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%'));";

            $conn->fetch($sql);

            if ($filter == "gallery") {
                $tag = array();
                $tag["gallery"] = $conn->getResultArray();
                return $tag;
            }
            $searchResult["gallery"] = $conn->getResultArray();
        }
        if ($filter == "Whole site" || $filter == "users") {

            $sql = "(SELECT id,username,email FROM snowflakes_users WHERE MATCH (username,email) AGAINST ('" . sfUtils::escape($searchString) . "'))
                UNION
                (SELECT id,username,email FROM snowflakes_users WHERE LOWER(username) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%') 
                  OR LOWER(email) LIKE CONCAT('%',LOWER('" . sfUtils::escape($searchString) . "'),'%'));";

            $conn->fetch($sql);
            if ($filter == "users") {
                $tag = array();
                $tag["users"] = $conn->getResultArray();
                return $tag;
            }

            $searchResult["users"] = $conn->getResultArray();
        }

        return $searchResult;
    }

    public static function userExits($conn, $username) {

        if (!$username || !$conn) {
            return false;
        }

        $sql = "SELECT username FROM snowflakes_users WHERE username='" . $username . "'";
        $conn->fetch($sql);
        return $conn->recordCount();
    }

    public static function forgottenPassword($conn, $email, $sender, $snowflakesUrl, $errmsg = "") {

        if (!$conn || !$email || !$sender) {
            return false;
        }

        $userStruct = new userStruct();
        $sql = 'Select * FROM snowflakes_users WHERE email="' . $email . '"';
        $conn->fetch($sql);
        $result = $conn->getResultArray();
        if (empty($result)) {
            return false;
        }
        $userStruct->populate($result[0]);

        if ($conn->recordCount() <= 0) {
            $errmsg = 'Could not find the account registered to the email ' . $email . ".";
            return false;
        }

        return self::requestResetPassWord($userStruct, $sender, $snowflakesUrl);
    }

    public static function requestResetPassWord($userStruct, $sender, $snowflakesUrl) {

        if (!$userStruct->isPopulated() || !$sender || !$snowflakesUrl) {
            return false;
        }

# SUBJECT (Subscribe/Remove)
        $subject = "Reset your snowflakes password";

        echo $subject . "<br>";

# MAIL BODY
        $body = "You have been sent this mail because you requested a reset on your password.\n";
        $body .= "\tUsername: " . $userStruct->m_username . " \n";
        $body .= "\tEmail: " . $userStruct->m_email . " \n";
        $body .= "If haven't asked for a password reset then ignore this message.\n";
        $body .= "If you requested a password then click the link below.\n ";
        $body .= $snowflakesUrl . "/ResetPassword.php?reset=" . $userStruct->m_reset_link;

## SEND MESSAGE ##
        return mail($userStruct->m_email, $subject, $body, "From: $sender");
    }

    public static function resetPassword($conn, $password, $oldResetLink) {

        if (!$password || !$conn || !$oldResetLink) {
            return false;
        }

        $userStruct = new userStruct();
// get user by reset link
        $userStruct->getUserByResetLink($conn, $oldResetLink);
// re-intialize  the user with the new password provided to set the new reset link
        $userStruct->init($userStruct->m_username, $password, $userStruct->m_email, $userStruct->m_access_level, $userStruct->m_image_name);
        return $userStruct->UpdateUser($conn);
    }

    public static function toAmPmTime($stringDate) {
// Make it into a Unix TimeStamp 
// Convert it to the format you desire 
        $datetime = new DateTime($stringDate);
        return $datetime->format("g:i a");
    }

    public static function dateToSql($stringDate) {
// Convert Date from DD/MM/YYYY to YYYY-MM-DD
        $datetime = new DateTime(str_replace('/', '-', $stringDate));
        return $datetime->format('Y-m-d');
    }

    public static function dateFromSql($stringDate) {
// Convert Date from YYYY-MM-DD to DD/MM/YYYY
        $datetime = new DateTime($stringDate);
        return $datetime->format("d/m/Y");
    }

    public static function tablenameFromType($type) {

        $tableName = "";
        if ($type == 'snowflake') {
            $tableName = "snowflakes";
        } else if ($type == 'event') {
            $tableName = "snowflakes_events";
        } else if ($type == 'gallery') {
            $tableName = "snowflakes_gallery";
        } else if ($type == 'user') {
            $tableName = "snowflakes_users";
        }

        return $tableName;
    }

    public static function manualFlakeitTrigger($conn, $id, $type, $operation) {
        if (!$conn || !$id || !$type || !$operation) {
            return false;
        }

        $tableName = self::tablenameFromType($type);
        if ($tableName == "") {
            return false;
        }

        $sql = "SELECT flake_it ";
        $sql .= "FROM $tableName WHERE id=$id";
        $conn->fetch($sql);
        $result = $conn->getResultArray();
        /// delete means the record is deleted so it must pass the 
        //condition to delete the flakeit record  associated with it
        if (empty($result) && $operation != 'DELETE') {
            return false;
        }

        $flakeCount = $result[0]['flake_it'];

        if ($operation == 'INSERT') {
            $sqlOp = "INSERT INTO snowflakes_flakeit SET flake_on='$type',flake_it=$flakeCount,flake_on_id=$id;";
        } else if ($operation == 'DELETE') {
            $sqlOp = "DELETE FROM snowflakes_flakeit WHERE flake_on_id=$id AND flake_on='$type';";
        } else if ($operation == 'UPDATE') {
            // Just to make sure the record exists alread
            $sqlOp = "INSERT IGNORE INTO snowflakes_flakeit SET flake_on='$type',flake_it=$flakeCount,flake_on_id=$id;";
            $sqlOp .= "UPDATE snowflakes_flakeit SET flake_it=$flakeCount WHERE flake_on_id=$id AND flake_on='$type';";
        } else {
            return false;
        }

        return $conn->execute($sqlOp);
    }

    public static function manualchangeLogTrigger($conn, $id, $type, $operation) {

        if (!$conn || !$id || !$type || !$operation) {
            return false;
        }

        $tableName = self::tablenameFromType($type);
        if ($tableName == "") {
            return false;
        }

        $log_action = "'added','modified','requested to delete','deleted','logged on','logged off','published','unpublished'";
        if ($operation == 'INSERT') {
            $log_action = "added";
        } else if ($operation == 'DELETE') {
            $log_action = "deleted";
        } else if ($operation == 'UPDATE') {
            $log_action = "modified";
        } else {
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
        if (empty($result)) {
            if ($operation == 'DELETE') {
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

        if ($deleted === 1) {
            $log_action = 'requested to delete';
            $insertSql = "INSERT INTO snowflakes_change_log SET change_action='$log_action',change_on='$log_change_on',created_by='$created_by',change_by='$edited_by',action_id=$id;";
            $deleted = $conn->execute($insertSql);
        }

        if ($operation != 'DELETE' && $type != 'user' && $publish != "") {
            if ($publish === 1) {
                $log_action = 'published';
            } else {
                $log_action = 'unpublished';
            }
            $insertSql = "INSERT INTO snowflakes_change_log SET change_action='$log_action',change_on='$log_change_on',created_by='$created_by',change_by='$edited_by',action_id=$id;";
            $conn->execute($insertSql);
        }

        return $inserted;
    }

    public static function checkTrigger($conn, $id, $type, $operation) {

        if (!$conn || !$id || !$type || !$operation) {
            return false;
        }

        $tableName = self::tablenameFromType($type);
        if ($tableName == "") {
            return false;
        }

        //// check if trigger exists
        $sql = "SHOW TRIGGERS LIKE '$tableName'";
        $conn->fetch($sql);
        $countTriggers = $conn->recordCount();
        if ($countTriggers >= 1) {
            return true;
        }

        if (!self::manualchangeLogTrigger($conn, $id, $type, $operation)) {
            trigger_error("Could not add or implement change log manual triggers", E_USER_NOTICE);
            return false;
        }

        if (!self::manualFlakeitTrigger($conn, $id, $type, $operation)) {
            trigger_error("Could not add or implement Flake it manual triggers", E_USER_NOTICE);
            return false;
        }

        return true;
    }

    public static function flakeIt($conn, $id, $type, $flakeit = "true") {

        if (!$id || !$conn || !$type) {
            return false;
        }

        $tableName = self::tablenameFromType($type);
        if ($tableName == "") {
            return false;
        }

        $sql = "UPDATE $tableName SET flake_it=flake_it";
        $sql.= $flakeit === "true" ? "+1" : "-1";
        $sql.=" WHERE id=$id";

        $updated = $conn->execute($sql);
        if (!$updated) {
            $sqlError.= "Could not update the flake it feild.";
            trigger_error($sqlError, E_USER_NOTICE);
        }

        // Check Trigger exist , if not then use manual trigger
        self::checkTrigger($conn, $id, $type, "UPDATE");


        $sql = "SELECT flake_it from $tableName WHERE id=$id";
        $conn->fetch($sql);
        $result = $conn->getResultArray();
        if (empty($result)) {
            return false;
        }
        return $result[0]['flake_it'];
    }

    public static function addQuerystringVar($url, $key, $value) {
        $url = preg_replace('/(.*)(?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
        $url = substr($url, 0, -1);
        if (strpos($url, '?') === false) {
            return ($url . '?' . $key . '=' . $value);
        } else {
            return ($url . '&' . $key . '=' . $value);
        }
    }

    public static function removeQuerystringVar($url, $key) {
        $url = preg_replace('/(.*)(?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
        $url = substr($url, 0, -1);
        return ($url);
    }

    public static function deleteGallery($conn, $id, $setDelete = false) {

        if (!$id || !$conn) {
            return false;
        }

        if ($setDelete == false) {
            $sql = "DELETE FROM snowflakes_gallery ";
        } else {
            $sql = "UPDATE snowflakes_gallery SET deleted=1 ";
            if (isset($_SESSION['MM_Username'])) {
                $sql.=',edited_by="' . $_SESSION['MM_Username'] . '",edited="' . time() . '" ';
            }
        }

        $sql.="WHERE id=" . $id;

        return $conn->execute($sql);
    }

    /**
     * Returns an encrypted & utf8-encoded
     */
    public static function encrypt($pure_string, $encryption_key) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $encryption_key, utf8_encode($pure_string), MCRYPT_MODE_ECB, $iv);
        return $encrypted_string;
    }

    /**
     * Returns decrypted original string
     */
    public static function decrypt($encrypted_string, $encryption_key) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $encryption_key, $encrypted_string, MCRYPT_MODE_ECB, $iv);
        return $decrypted_string;
    }

    public static function setUserLoginOut($userName, $value = false, $inifile = '../config/config.ini') {
        if (!$userName) {
            return false;
        }
//host
        $config = Config::getConfig("db", $inifile);
        $sqlArray = array('type' => $config['type'], 'host' => $config['host'], 'username' => $config['username'], 'password' => sfUtils::decrypt($config['password'], $config['key']), 'database' => $config['dbname']);
        $SFconnects = new sfConnect($sqlArray);
        $SFconnects->connect(); // Connect to database
        $login = $value == true ? 1 : 0;
        $sql = "UPDATE snowflakes_users SET logged_in=" . $login . ", ip=\"" . self::getClientIp() . "\" WHERE username=\"" . $userName . '"';
        return $SFconnects->execute($sql);
    }

    public static function snowFlakeItCount($conn, $userName = "") {

        if (!$conn) {
            return false;
        }

        $sql = "SELECT SUM(flake_it)total "
                . "FROM snowflakes";
        if (strlen($userName) > 0) {
            $sql.= " WHERE created_by='$userName' GROUP BY created_by";
        }

        $sql.=";";

        $conn->fetch($sql);
        $result = $conn->getResultArray();
        if (empty($result)) {
            return false;
        }
        return $result[0]['total'];
    }

    public static function eventFlakeItCount($conn, $userName = "") {

        if (!$conn) {
            return false;
        }

        $sql = "SELECT SUM(flake_it)total "
                . "FROM snowflakes_events";
        if (strlen($userName) > 0) {
            $sql.= " WHERE created_by='$userName' GROUP BY created_by";
        }

        $sql.=";";

        $conn->fetch($sql);
        $result = $conn->getResultArray();
        if (empty($result)) {
            return false;
        }
        return $result[0]['total'];
    }

    public static function galleryFlakeItCount($conn, $userName = "") {

        if (!$conn) {
            return false;
        }

        $sql = "SELECT SUM(flake_it)total "
                . "FROM snowflakes_gallery";
        if (strlen($userName) > 0) {
            $sql.= " WHERE created_by='$userName' GROUP BY created_by";
        }

        $sql.=";";

        $conn->fetch($sql);
        $result = $conn->getResultArray();
        if (empty($result)) {
            return false;
        }
        return $result[0]['total'];
    }

    public static function getAllCounts($conn, $username = '') {

        if (!$conn) {
            return false;
        }

        $_SESSION['Snowflakes'] = array();
        $_SESSION['SfGallery'] = array();
        $_SESSION['SfEvents'] = array();
        $_SESSION['SFUsers'] = array();

        $origSql = "SELECT COUNT(id) count FROM snowflakes WHERE ";

        $sql = $origSql . "publish = 1";
        $conn->fetch($sql);
        $totalRows_rsPublished = $conn->getResultArray();
        $_SESSION['Snowflakes']['published'] = $totalRows_rsPublished[0]['count'];

        if (strlen($username)) {
            $sql = $sql . ' AND created_by="' . self::escape($username) . '"';
            $conn->fetch($sql);
            $userPubSnowflakes = $conn->getResultArray();
            $_SESSION['Snowflakes']['user_published'] = $userPubSnowflakes[0]['count'];
        }

        $sql = $origSql . "publish = 0";
        $conn->fetch($sql);
        $totalRows_rsUnplublished = $conn->getResultArray();
        $_SESSION['Snowflakes']['unpublished'] = $totalRows_rsUnplublished[0]['count'];

        if (strlen($username)) {
            $sql = $sql . ' AND created_by="' . self::escape($username) . '"';
            $conn->fetch($sql);
            $userUnPubSnowflakes = $conn->getResultArray();
            $_SESSION['Snowflakes']['user_unpublished'] = $userUnPubSnowflakes[0]['count'];
            $_SESSION['Snowflakes']['user_total'] = $userUnPubSnowflakes[0]['count'] + $userPubSnowflakes[0]['count'];
        }

        $_SESSION['Snowflakes']['total'] = $totalRows_rsPublished[0]['count'] + $totalRows_rsUnplublished[0]['count'];

        $sql = "SELECT COUNT(id) count FROM snowflakes_events WHERE publish = 1";
        $conn->fetch($sql);
        $totalRows_rsPublished = $conn->getResultArray();
        $_SESSION['SfEvents']['published'] = $totalRows_rsPublished[0]['count'];

        if (strlen($username)) {
            $sql = $sql . ' AND created_by="' . self::escape($username) . '"';
            $conn->fetch($sql);
            $userPubEvent = $conn->getResultArray();
            $_SESSION['SfEvents']['user_published'] = $userPubEvent[0]['count'];
        }

        $sql = "SELECT COUNT(id) count FROM snowflakes_events WHERE publish = 0";
        $conn->fetch($sql);
        $totalRows_rsUnplublished = $conn->getResultArray();
        $_SESSION['SfEvents']['unpublished'] = $totalRows_rsUnplublished[0]['count'];

        if (strlen($username)) {
            $sql = $sql . ' AND created_by="' . self::escape($username) . '"';
            $conn->fetch($sql);
            $userUnPubEvent = $conn->getResultArray();
            $_SESSION['SfEvents']['user_unpublished'] = $userUnPubEvent[0]['count'];
            $_SESSION['SfEvents']['user_total'] = $userUnPubEvent[0]['count'] + $userPubEvent[0]['count'];
        }

        $_SESSION['SfEvents']['total'] = $totalRows_rsPublished[0]['count'] + $totalRows_rsUnplublished[0]['count'];

        $sql = "SELECT COUNT(id) count FROM snowflakes_gallery WHERE publish = 0";
        $conn->fetch($sql);
        $totalRows_galleryUnpublished = $conn->getResultArray();
        $_SESSION['SfGallery']['unpublished'] = $totalRows_galleryUnpublished[0]['count'];

        if (strlen($username)) {
            $sql = $sql . ' AND created_by="' . self::escape($username) . '"';
            $conn->fetch($sql);
            $userPubGallery = $conn->getResultArray();
            $_SESSION['SfGallery']['user_unpublished'] = $userPubGallery[0]['count'];
        }

        $sql = "SELECT COUNT(id) count FROM snowflakes_gallery WHERE publish = 1";
        $conn->fetch($sql);
        $totalRows_galleryPublished = $conn->getResultArray();
        $_SESSION['SfGallery']['published'] = $totalRows_galleryPublished[0]['count'];

        if (strlen($username)) {
            $sql = $sql . ' AND created_by="' . self::escape($username) . '"';
            $conn->fetch($sql);
            $userUnPubGallery = $conn->getResultArray();
            $_SESSION['SfGallery']['user_published'] = $userUnPubGallery[0]['count'];
            $_SESSION['SfGallery']['user_total'] = $userUnPubGallery[0]['count'] + $userPubGallery[0]['count'];
        }

        $_SESSION['SfGallery']['total'] = $totalRows_galleryPublished[0]['count'] + $totalRows_galleryUnpublished[0]['count'];

        $sql = "SELECT COUNT(id) count FROM snowflakes_users";
        $conn->fetch($sql);
        $totalRows_users = $conn->getResultArray();
        $_SESSION['SFUsers']['total'] = $totalRows_users[0]['count'];
    }

    public static function dialogMessage($title, $message) {

        if (!strlen($message)) {
            return false;
        }

        if (!strlen($title)) {
            $title = 'Message';
        }
        $str = ' <!-- dialog-message Starts-->'
                . '<div class="dialog-message" title="' . $title . '">
                    ' . $message . '
		</div>
                 <!-- End dialog-message -->';
        return $str;
    }

    public static function getPublishStatus($publish) {

        $retValue = "";
        if ($publish == 0 || $publish === false) {
            $retValue = " Unpublished";
        } else if ($publish == 1 || $publish === true) {
            $retValue = " Published";
        }
        return $retValue;
    }

    public static function migrate($conn, $dbname, $adminUsername, &$output) {

        if (!$conn || !$dbname || !$adminUsername) {
            return false;
        }

        $sql = 'INSERT IGNORE INTO snowflakes_users (username,password,reset_link,email,access_level,access_name)
                SELECT a.username,a.password,MD5(CONCAT_WS(" ",a.password,a.email,a.username,a.adminid)) reset_link,a.email,a.AcessLevel,IF(a.AcessLevel=5,"Super Administrator",IF(a.AcessLevel=4,"Administrator",IF(a.AcessLevel=3,"Manager",IF(a.AcessLevel=2,"Publisher","Author/Editor"))))
                FROM ' . $dbname . '.AdminUsers a;';

        if (!$conn->execute($sql)) {
            $output.="Could Not migrate users from $dbname.AdminUsers STATUS::FAILED " . $conn->getMessage() . "<br/>";
            return false;
        }

        $output.="User Migration from $dbname.AdminUsers was successful STATUS::SUCCESS";

        $sql = 'INSERT IGNORE INTO snowflakes (title,body_text,publish,image_name,gallery,created,created_by,edited,edited_by)
            SELECT b.title,b.bodytext,b.publish, b.imagename,b.gallery,b.created,b.createdby,b.created edited,b.createdby editedby
            FROM ' . $dbname . '.SnowFlakeTable b;';


        if (!$conn->execute($sql)) {
            $output.="Could Not migrate Snowflakes from $dbname.SnowFlakeTable STATUS::FAILED " . $conn->getMessage() . "<br/>";
            return false;
        }

        $output.="Snowflakes Migration from $dbname.SnowFlakeTable was successful STATUS::SUCCESS";

        $sql = 'INSERT IGNORE INTO snowflakes_events (title,body_text,publish,image_name,event_time,event_date,end_time,end_date,location,created,edited,created_by,edited_by)
            SELECT c.title, c.bodytext, c.publish, c.imagename, c.evtime event_time, FROM_UNIXTIME(c.evDate) event_date,@endtime:=ADDTIME(c.evtime, "01:00:00") end_time, 
            IF(@endtime>"24:00:00",DATE_ADD(FROM_UNIXTIME(c.evDate), INTERVAL 1 DAY),FROM_UNIXTIME(c.evDate)) end_date, c.Location,c.evDate created,c.evDate edited,
            "' . self::escape($adminUsername) . '" created_by,"' . self::escape($adminUsername) . '" edited_by
            FROM ' . $dbname . '.SF_EventsTable c;';

        if (!$conn->execute($sql)) {
            $output.="Could Not migrate Snowflakes Events from $dbname.SF_EventsTable STATUS::FAILED " . $conn->getMessage() . "<br/>";
            return false;
        }

        $output.="Snowflakes Events Migration from $dbname.SF_EventsTable was successful STATUS::SUCCESS";

        $sql = 'INSERT IGNORE INTO snowflakes_gallery (title,thumb_name,image_name,image_caption,created,created_by,edited,edited_by)
            SELECT d.title,d.Thumbname,d.imagename,d.ImageCaption,d.created,d.createdby,d.created edited,d.createdby edited_by 
            FROM ' . $dbname . '.SF_GalleryTable d;';

        if (!$conn->execute($sql)) {
            $output.="Could Not migrate Snowflakes Gallery from $dbname.SF_GalleryTable STATUS::FAILED " . $conn->getMessage() . "<br/>";
            return false;
        }

        $output.="Snowflakes Gallery Migration from $dbname.SF_GalleryTable was successful STATUS::SUCCESS";

        $sql = 'INSERT IGNORE INTO snowflakes_settings(sf_host_name,sf_db,sf_db_username,sf_db_password,sf_db_type,sf_url,result_url,out_url,events_result_url,events_output_url,gallery_result_url,gallery_out_url,upload_gallery_dir)
            SELECT e.SFHostname, e.SFDatabase,e.SFDBUsername,e.SFDBPassword,"' . $conn->getAttribute('type') . '",e.SnowflakesUrl,e.SnowflakesResultUrl,e.SFOutUrl,e.SFEventsResultUrl,e.SFEventsOutputUrl,e.SFGalleryResultUrl,e.SFGalleryOutUrl,e.UploadGalleryDir
            FROM ' . $dbname . '.SF_SnowflakesSettings e;';

        if (!$conn->execute($sql)) {
            $output.="Could Not migrate Snowflakes settings from $dbname.SF_SnowflakesSettings STATUS::FAILED " . $conn->getMessage() . "<br/>";
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

        $output.="Snowflakes settings Migration from $dbname.SF_SnowflakesSettings was successful STATUS::SUCCESS";

        return true;
    }

    public static function copyDirectoryList($source, $dest) {

        if (!$source || !is_dir($source) || !$dest) {
            return false;
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest);
        }

        $sourcefileList = scandir($source);
        $destfileList = scandir($dest);

        foreach ($sourcefileList as $file) {

            if ($file == '.' || $file == '..' || in_array($file, $destfileList)) {
                continue;
            }

            // Simple copy for a file
            if (is_file("$source/$file")) {
                copy("$source/$file", "$dest/$file");
            }

            // Simple copy for a file
            if (is_dir("$source/$file")) {
                self::copyDirectoryList("$source/$file", "$dest/$file");
            }
        }
        
        return true;
    }

    public static function migrateUpdir($source, $inifile = '../config/config.ini') {
        if (!$source || !is_dir($source)) {
            return false;
        }
        $settingsConfig = Config::getConfig("settings", $inifile);
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $settingsConfig['uploadGalleryDir']);
        }

        return self::copyDirectoryList($source,$settingsConfig['uploadGalleryDir']);
        
    }

    public static function createSnowflakesRss($conn, $snowflakesList, $inifile = '../config/config.ini') {
        /// sanity Check
        if (!$conn || empty($snowflakesList)) {
            return false;
        }

        $settingsConfig = Config::getConfig("settings", $inifile);
        $itemUrl = isset($settingsConfig['snowflakesResultUrl']) ? $settingsConfig['snowflakesResultUrl'] : $settingsConfig['m_sfUrl'] . "OneView.php";

        $rssString = '
            <rss version="2.0">
                <channel>
                    <title>Snowflakes Rss</title>
                    <description>A description of Snowflakes Rss feed</description>
                    <link>' . htmlentities($settingsConfig['m_sfUrl'] . 'rss.php?ty=snowflakes') . '</link>
                    <image>
                        <url>' . $settingsConfig['m_sfUrl'] . "resources/images/Snowflakes2.png" . '</url>
                        <title>Snowflakes Rss</title>
                        <link>' . htmlentities($settingsConfig['m_sfUrl'] . 'rss.php?ty=snowflakes') . '</link>
                        <width>120</width>
                        <height>40</height>
                    </image>';
        foreach ($snowflakesList as $key => $value) {
            $flakeStruct = $value;
            $BodyString = htmlspecialchars($flakeStruct->m_body_text, ENT_QUOTES);
            $rssString .= '
                    <item>
                        <title>' . $flakeStruct->m_title . '</title>
                        <description>' . substr($BodyString, 0, 280) . '...</description>
                        <link>' . $itemUrl . "?pageid=" . $flakeStruct->m_id . '</link>
                        <date>' . date(" F j, Y", $flakeStruct->m_created) . '</date>
                        <publisher>' . $flakeStruct->m_created_by . '</publisher>
                        <flakes>' . $flakeStruct->m_flake_it . '</flakes>
                    </item>';
        }
        $rssString .='
                </channel>
            </rss>';

        return $rssString;
    }

    public static function createEventRss($conn, $eventList, $inifile = '../config/config.ini') {
        /// sanity Check
        if (!$conn || empty($eventList)) {
            return false;
        }

        $settingsConfig = Config::getConfig("settings", $inifile);
        $itemUrl = isset($settingsConfig['eventsResultUrl']) ? $settingsConfig['eventsResultUrl'] : $settingsConfig['m_sfUrl'] . "Events/OneView.php";
        $rssString = '
            <rss version="2.0">
                <channel>
                    <title>Snowflakes Event Rss</title>
                    <description>A description of snowflakes event rss feed</description>
                    <link>' . htmlentities($settingsConfig['m_sfUrl'] . 'rss.php?ty=events') . '</link>
                    <image>
                        <url>' . $settingsConfig['m_sfUrl'] . "resources/images/Snowflakes2.png" . '</url>
                        <title>Snowflakes Event Rss</title>
                        <link>' . htmlentities($settingsConfig['m_sfUrl'] . 'rss.php?ty=events') . '</link>
                        <width>120</width>
                        <height>40</height>
                    </image>';

        foreach ($eventList as $key => $value) {
            $eventStruct = $value;
            $BodyString = htmlspecialchars($eventStruct->m_body_text, ENT_QUOTES);
            $eventtime = new DateTime($eventStruct->m_event_date);
            $endtime = new DateTime($eventStruct->m_end_date);
            $rssString .= '
                    <item>
                        <title>' . $eventStruct->m_title . '</title>
                        <description>' . substr($BodyString, 0, 280) . '</description>
                        <link>' . $itemUrl . "?Eventid=" . $eventStruct->m_id . '</link>
                        <date>' . date(" F j, Y", $eventStruct->m_created) . '</date>
                        <eventdatetime>' . $eventtime->format(" F j, Y") . " " . self::toAmPmTime($eventStruct->m_event_time) . '</eventdatetime>
                        <enddatetime>' . $endtime->format(" F j, Y") . " " . self::toAmPmTime($eventStruct->m_end_time) . '</enddatetime>
                        <location>' . $eventStruct->m_location . '</location>    
                        <publisher>' . $eventStruct->m_created_by . '</publisher>
                        <flakes>' . $eventStruct->m_flake_it . '</flakes>
                    </item>';
        }
        $rssString .='
                </channel>
            </rss>';
        return $rssString;
    }

    public static function createGalleryRss($conn, $galleryList, $inifile = '../config/config.ini') {
        /// sanity Check
        if (!$conn || empty($galleryList)) {
            return false;
        }

        $settingsConfig = Config::getConfig("settings", $inifile);
        $itemUrl = isset($settingsConfig['galleryResultUrl']) ? $settingsConfig['galleryResultUrl'] : $settingsConfig['m_sfUrl'] . "Gallery/OneView.php";
        $rssString = '
            <rss version="2.0">
                <channel>
                    <title>Snowflakes Gallery Rss</title>
                    <description>A description of Snowflakes Gallery Rss feed</description>
                    <link>' . htmlentities($settingsConfig['m_sfUrl'] . 'rss.php?ty=gallery') . '</link>
                    <image>
                        <url>' . $settingsConfig['m_sfUrl'] . "resources/images/Snowflakes2.png" . '</url>
                        <title>Snowflakes Gallery Rss</title>
                        <link>' . htmlentities($settingsConfig['m_sfUrl'] . 'rss.php?ty=gallery') . '</link>
                        <width>120</width>
                        <height>40</height>
                    </image>';

        foreach ($galleryList as $key => $value) {
            $galleryStruct = $value;
            $coverimage = explode(",", $galleryStruct->m_thumb_name);
            $covercaption = explode(",", $galleryStruct->m_image_caption);
            $rssString .= '
                    <item>
                        <title>' . $galleryStruct->m_title . '</title>
                        <link>' . $itemUrl . "?Galleryid=" . $galleryStruct->m_id . '</link>
                        <date>' . date(" F j, Y", $galleryStruct->m_created) . '</date>
                        <image>
                            <title>' . end($covercaption) . '</title>
                            <url>' . $settingsConfig['m_sfGalleryThumbUrl'] . end($coverimage) . '</url>
                            <link>' . $itemUrl . "?Galleryid=" . $galleryStruct->m_id . '</link>
                            <width>' . $settingsConfig['thumbWidth'] . '</width>
                            <height>' . $settingsConfig['thumbHeight'] . '</height>
                        </image>
                        <publisher>' . $galleryStruct->m_created_by . '</publisher>
                        <flakes>' . $galleryStruct->m_flake_it . '</flakes>
                    </item>';
        }
        $rssString .='
                </channel>
            </rss>';

        return $rssString;
    }

    public static function startsWith($haystack, $needle) {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    public static function endsWith($haystack, $needle) {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    public static function viewLogFile($filename, $Logtype = "All") {
        if (!$filename || !file_exists($filename)) {
            return false;
        }

        $textFile = file_get_contents($filename);
        $lines = explode("\n[", $textFile);
        $info = array();
        $linedata = array();
        $errorcount = 0;
        $warningCount = 0;
        $successCount = 0;

        foreach ($lines as $line => $data) {
            $linedata = explode("¦=>", $data);
            $info[$line]['datetime'] = str_replace("[", "", str_replace("]", "", $linedata[0]));
            $info[$line]['User'] = str_replace("[", "", str_replace("]", "", $linedata[1]));
            if (strpos($linedata[2], "[File]")) {
                $info[$line]['File'] = str_replace("[Reason]", "", $linedata[3]);
                $info[$line]['Reason'] = $linedata[4];
                $info[$line]['Execute'] = "None";

                if (strpos($linedata[4], "warning") || strpos($linedata[4], "deprecated") || strpos($linedata[4], "is deprecated;")) {
                    $info[$line]['Status'] = '<span class="icon warning"></span>';
                    $warningCount++;
                } else {
                    $info[$line]['Status'] = '<span class="icon error"></span>';
                    $errorcount++;
                }
            } else if (strpos($linedata[2], "[Execute]")) {
                $info[$line]['Execute'] = str_replace("[Query Error]", "", $linedata[3]);
                $info[$line]['File'] = "None";
                $info[$line]['Reason'] = strpos($linedata[3], "[Query Error]") ? $linedata[4] : "None";
                $info[$line]['Status'] = strpos($linedata[3], "[Query Error]") ? '<span class="icon error"></span>' : '<span class="icon success"></span>';
                if (strpos($linedata[3], "[Query Error]")) {
                    $errorcount++;
                } else {
                    $successCount++;
                }
            }
        }
        $info['errorcount'] = $errorcount;
        $info['warningCount'] = $warningCount;
        $info['successCount'] = $successCount;
        return $info;
    }

    public static function getfileList($dir = "../data/") {

        // check that this $dir is populated and it's a directory
        if (!$dir || !is_dir($dir)) {
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

        foreach ($files as $file) {
            //sanity Check
            if ($file == '.' || $file == '..') {
                continue;
            }
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if ($ext != 'log') {
                continue;
            }
            $datestring = substr($file, -14, -4);
            $listString .='	 <tr><td>' . $datestring . '</td><td><a href="LogViewer.php?logfile=' . $file . '" data-log-date="' . $datestring . '">' . $file . "</a></td></tr>\n";
        }

        $listString .='	</tbody>
                </table>
            </div><!-- End of tablepage --> ';

        return $listString;
    }

    public static function getfileList2($dir = "../data/") {

        // check that this $dir is populated and it's a directory
        if (!$dir || !is_dir($dir)) {
            return false;
        }

        $logFileList = array();
        // Sort in ascending order - this is default
        $files = scandir($dir);

        foreach ($files as $key => $file) {
            //sanity Check
            if ($file == '.' || $file == '..') {
                continue;
            }
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if ($ext != 'log') {
                continue;
            }
            $datestring = substr($file, -14, -4);
            $logFileList[$key]['Date'] = $datestring;
            $logFileList[$key]['LogFile'] = $file;
        }

        return $logFileList;
    }

    public static function comapact99($data) {
        if (!$data) {
            return $data;
        }

        if ($data >= 100) {
            return '99+';
        }

        return $data;
    }

    public static function getTimeZoneList() {

        if (!method_exists("DateTimeZone", "listIdentifiers")) {
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

    public static function settimezone($timezones) {

        if (!$timezones) {
            return false;
        }
        return date_default_timezone_set($timezones);
    }

}

class settingsStruct {

    //Db Info           //config Name [db]
    var $m_hostName;    //host
    var $m_dbName;      //dbname
    var $m_dbType;      //type
    var $m_dbUsername;  //username
    var $m_dbPassword;  //password
    var $m_admin_email; //admin_email
    //Settings Info     //[settings]
    var $m_url;         //url
    var $m_path;        //path
    var $m_sfUrl;       //m_sfUrl
    var $m_sfGalleryUrl; //m_sfGalleryUrl
    var $m_sfGalleryImgUrl; //m_sfGalleryImgUrl
    var $m_sfGalleryThumbUrl; //m_sfGalleryThumbUrl 
    var $m_sfProfileUrl; //m_sfProfileUrl
    var $m_uploadGalleryDir; //uploadGalleryDir
    var $m_galleryImgDir; //galleryImgDir
    var $m_galleryThumbDir; //galleryThumbDir
    var $m_thumbWidth; //thumbWidth
    var $m_thumbHeight; //thumbHeight
    var $m_maxImageWidth; //maxImageWidth
    var $m_resources; //resources
    var $m_imageExtList; //imageExtList
    var $m_imageTypesList; //imageTypesList
    var $m_snowflakesResultUrl; //snowflakesResultUrl// One snowflakes result
    var $m_snowflakesOutUrl; // snowflakesOutUrl     // All snowflakes output
    var $m_eventsResultUrl; //eventsResultUrl        // One event result
    var $m_eventsOutputUrl; //eventsOutputUrl        //All event output
    var $m_galleryResultUrl; //galleryResultUrl      // One gallery result
    var $m_galleryOutUrl; //galleryOutUrl           //All gallery output
    var $m_maxImageSize; //maxImageSize

    //Db Info           //config Name [db]

    public static function SethostName($value, $inifile = '../config/config.ini') {
        //host
        return Config::setConfig($value, "host", "db", $inifile);
    }

    public static function SetdbName($value, $inifile = '../config/config.ini') {
        //dbname
        return Config::setConfig($value, "dbname", "db", $inifile);
    }

    public static function SetdbType($value, $inifile = '../config/config.ini') {
        //type
        return Config::setConfig($value, "type", "db", $inifile);
    }

    public static function SetdbUsername($value, $inifile = '../config/config.ini') {
        //username
        return Config::setConfig($value, "username", "db", $inifile);
    }

    public static function SetdbPassword($value, $inifile = '../config/config.ini') {
        //password
        return Config::setConfig($value, "password", "db", $inifile);
    }

    public static function Setadmin_email($value, $inifile = '../config/config.ini') {
        //admin_email
        return Config::setConfig($value, "admin_email", "db", $inifile);
    }

    public static function SettimeZone($value, $inifile = '../config/config.ini') {
        //time_zone
        return Config::setConfig($value, "time_zone", "db", $inifile);
    }

    //Settings Info     //[settings]
    public static function Seturl($value, $inifile = '../config/config.ini') {
        //url
        return Config::setConfig($value, "url", "settings", $inifile);
    }

    public static function Setpath($value, $inifile = '../config/config.ini') {
        //path
        return Config::setConfig($value, "path", "settings", $inifile);
    }

    public static function SetsfUrl($value, $inifile = '../config/config.ini') {
        //m_sfUrl
        return Config::setConfig($value, "m_sfUrl", "settings", $inifile);
    }

    public static function SetsfGalleryUrl($value, $inifile = '../config/config.ini') {
        //m_sfGalleryUrl
        return Config::setConfig($value, "m_sfGalleryUrl", "settings", $inifile);
    }

    public static function SetsfGalleryImgUrl($value, $inifile = '../config/config.ini') {
        //m_sfGalleryImgUrl
        return Config::setConfig($value, "m_sfGalleryImgUrl", "settings", $inifile);
    }

    public static function SetsfGalleryThumbUrl($value, $inifile = '../config/config.ini') {
        //m_sfGalleryThumbUrl 
        return Config::setConfig($value, "m_sfGalleryThumbUrl", "settings", $inifile);
    }

    public static function SetsfProfileUrl($value, $inifile = '../config/config.ini') {
        //m_sfProfileUrl
        return Config::setConfig($value, "m_sfProfileUrl", "settings", $inifile);
    }

    public static function SetuploadGalleryDir($value, $inifile = '../config/config.ini') {
        //uploadGalleryDir
        return Config::setConfig($value, "uploadGalleryDir", "settings", $inifile);
    }

    public static function SetgalleryImgDir($value, $inifile = '../config/config.ini') {
        //galleryImgDir
        return Config::setConfig($value, "galleryImgDir", "settings", $inifile);
    }

    public static function SetgalleryThumbDir($value, $inifile = '../config/config.ini') {
        //galleryThumbDir
        return Config::setConfig($value, "galleryThumbDir", "settings", $inifile);
    }

    public static function SetthumbWidth($value, $inifile = '../config/config.ini') {
        //thumbWidth
        return Config::setConfig($value, "thumbWidth", "settings", $inifile);
    }

    public static function SetthumbHeight($value, $inifile = '../config/config.ini') {
        //thumbHeight
        return Config::setConfig($value, "thumbHeight", "settings", $inifile);
    }

    public static function SetmaxImageWidth($value, $inifile = '../config/config.ini') {
        //maxImageWidth
        return Config::setConfig($value, "maxImageWidth", "settings", $inifile);
    }

    public static function Setresources($value, $inifile = '../config/config.ini') {
//resources
        return Config::setConfig($value, "resources", "settings", $inifile);
    }

    public static function SetimageExtList($value, $inifile = '../config/config.ini') {
        //imageExtList
        return Config::setConfig($value, "imageExtList", "settings", $inifile);
    }

    public function SetimageTypesList($value, $inifile = '../config/config.ini') {
        //imageTypesList
        return Config::setConfig($value, "imageTypesList", "settings", $inifile);
    }

    public static function SetsnowflakesResultUrl($value, $inifile = '../config/config.ini') {
        //snowflakesResultUrl   // One snowflakes result
        return Config::setConfig($value, "snowflakesResultUrl", "settings", $inifile);
    }

    public static function SetsnowflakesOutUrl($value, $inifile = '../config/config.ini') {
        // snowflakesOutUrl     // All snowflakes output
        return Config::setConfig($value, "snowflakesOutUrl", "settings", $inifile);
    }

    public static function SeteventsResultUrl($value, $inifile = '../config/config.ini') {
        //eventsResultUrl        // One event result
        return Config::setConfig($value, "eventsResultUrl", "settings", $inifile);
    }

    public static function SeteventsOutputUrl($value, $inifile = '../config/config.ini') {
        //eventsOutputUrl        //All event output
        return Config::setConfig($value, "eventsOutputUrl", "settings", $inifile);
    }

    public static function SetgalleryResultUrl($value, $inifile = '../config/config.ini') {
        //galleryResultUrl      // One gallery result
        return Config::setConfig($value, "galleryResultUrl", "settings", $inifile);
    }

    public static function SetgalleryOutUrl($value, $inifile = '../config/config.ini') {
        //galleryOutUrl           //All gallery output
        return Config::setConfig($value, "galleryOutUrl", "settings", $inifile);
    }

    public static function SetmaxImageSize($value, $inifile = '../config/config.ini') {
        //maxImageSize
        return Config::setConfig(sfUtils::toByteSize($value), "maxImageSize", "settings", $inifile);
    }

}

?>
