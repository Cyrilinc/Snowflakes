<?php

/**
 * Description of sfConnect
 * sfConnect is used to connect to the database,
 * 
 * @author Cyril Adelekan
 */
class sfConnect {

    var $attributes = array('status' => true);
    var $sqlLite;
    public $m_sfConnect;
    public $m_sfStmt;

    /**
    * The constructor of the  database connection {@link sfConnect}, 
    * its stores all the attributes needed connect to a database and sets the attribute
    * using the array parameter key as the attrbute key and its value as the value of the attribute
    *
    * @param array $attributes that contains all the data needed to connect to the database.
    * 
    */
    function sfConnect($attributes = array()) {
        if (count($attributes) >= 1) {
            foreach ($attributes as $key => $value) {
                $this->setAttribute($key, $value);
            }
        }
        $defaults = array();
        $defaults['fetch'] = 'associative';
        foreach ($defaults as $key => $value) {
            if (!array_key_exists($key, $this->attributes)) {
                $this->setAttribute($key, $value);
            }
        }
    }

    /**
    * Get an attribute's value 
    *
    * @param String $attribute the attribute to get.
    * 
    * @return String the value of the attribute 
    */

    function getAttribute($attribute) {
        if ($this->getStatus() == true) {
            if (array_key_exists($attribute, $this->attributes)) {
                return $this->attributes[$attribute];
            } else {
                $this->setStatus(false);
                $this->setMessage("Attribute: '" . $attribute . "' does not exist");
                return $this->getStatus();
            }
        }

        if (array_key_exists('message', $this->attributes)) {
            return $this->attributes['message'];
        } else {
            "Status is false";
        }
    }

    /**
    * set an attribute and its value 
    *
    * @param String $attribute the attribute to set.
    * @param String $value the value of the attribute.
    * 
    */
    function setAttribute($attribute, $value) {
        if ($this->getStatus() == true) {
            $this->deleteAttribute($attribute);
            $this->attributes[$attribute] = $value;
        }
    }

    /**
    * Delete an attribute and its value 
    *
    * @param String $attribute the attribute to delete.
    * 
    */
    function deleteAttribute($attribute) {
        if (array_key_exists($attribute, $this->attributes) == true) {
            unset($this->attributes[$attribute]);
        }
    }

    /**
    * Get the satus of an sql operation
    * 
    * @return bool the value of the attribute['status'] 
    */

    function getStatus() {
        return $this->attributes['status'];
    }


    /**
    * set the satus of an sql operation
    * 
    * @param String $status the status value to set.
    */

    function setStatus($status) {
        $this->attributes['status'] = $status;
    }

    /**
    * Get an sql operation message
    * 
    * @return String the value of the attribute['message'] 
    */

    function getMessage() {
        return $this->getAttribute('message');
    }

    /**
    * set the message of an sql operation
    * 
    * @param String $message the message value to set.
    * @param bool $log if set to true also log the message.
    */

    function setMessage($message, $log = true) {
        if ($log == true) {
            sfLogError::sfLogEntry($message);
        }
        $this->setAttribute('message', $message);
    }

    /**
    * Get the result of an sql operation
    * 
    * @return array the value of the attribute['result'] 
    */
    function getResultArray() {
        if ($this->getStatus() == true) {
            if (array_key_exists('result', $this->attributes)) {
                return $this->getAttribute('result');
            } else {
                $this->setStatus(false);
                $this->setMessage("Attribute: 'result' does not exist");
                return $this->getStatus();
            }
        }
    }

    /**
    * Connect to the database given that all the attributes to connecting 
    * to the database is populated
    * 
    */
    function connect() {

        $user = $this->getAttribute('username');
        $pass = $this->getAttribute('password');
        $host = $this->getAttribute('host');
        $dbname = $this->getAttribute('database');

        if (!$user || !$pass || !$host) {
            $this->setStatus(false);
            return false;
        }

        $type = $this->getAttribute('type');
        //echo $type . "= Connection Type <br>";

        if ($type <> false) {
            $this->setStatus(true);
            try {
                switch ($type) {
                    case 'MySQL';

                        $this->m_sfConnect = new PDO('mysql:host=' . $host, $user, $pass);
                        if ($dbname) {
                            $this->selectDatabase($dbname);
                        }
                        break;
                    case 'SQLite';
                        $dbdatapath = $this->getAttribute('datapath');
                        if ($dbname) {
                            $this->m_sfConnect = new PDO("sqlite:" . $dbdatapath . $dbname . ".db");
                        } else {
                            $this->m_sfConnect = new PDO("sqlite:" . $dbdatapath . "snowflakes.db");
                        }
                        break;
                    case 'MSSQL';
                        $this->m_sfConnect = new PDO('mssql:host=' . $host, $user, $pass);
                        if ($dbname) {
                            $this->selectDatabase($dbname);
                        }
                        break;
                    case 'Sybase';
                        $this->m_sfConnect = new PDO('sybase:host=' . $host, $user, $pass);
                        if ($dbname) {
                            $this->selectDatabase($dbname);
                        }
                        break;
                    case 'ODBC';
                    case 'Oracle';
                    case 'PostgreSQL';
                        $this->setMessage("Database type:" . $type . " has not yet been provided for in this class");
                        $this->setStatus(false);
                        break;
                    default:
                        $this->setMessage("Database type:" . $type . " is invalid");
                        $this->setStatus(false);
                }
            } catch (PDOException $e) {
                $this->setMessage($e->getMessage());
                $this->setStatus(false);
            }

            $this->setAttribute('link', $this->m_sfConnect);
            $this->fetch("SHOW VARIABLES LIKE 'version'", false);
            $ver = $this->getResultArray();
            if ($ver) {
                $this->setAttribute('version', $ver[0]['Value']);
            }
        }
    }


    /**
     * Close the sfConnect connection to the database.
     * 
     */

    function close() {
        $type = $this->getAttribute('type');
        if ($type <> false) {
            $this->setAttribute('link', NULL);
            $this->m_sfConnect = NULL;
        }
    }


    /**
    * Select database to which the sql operation will be running against
    * @param String $database This is the database name
    * @param bool $userAccess This is to detemine if one wants to set user Grant for the database
    * 
    * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
    */

    function selectDatabase($database, $userAccess = false) {
        if (!$database)
            return false;
        $user = $this->getAttribute('username');
        $pass = $this->getAttribute('password');
        $host = $this->getAttribute('host');

        if (!$user || !$pass || !$host) {
            return false;
        }

        $this->setAttribute('database', $database);
        $this->m_sfConnect->exec("CREATE DATABASE IF NOT EXISTS " . $database);
        if ($userAccess) {
            $this->m_sfConnect->exec("CREATE USER '" . $user . "'@'" . $host . "' IDENTIFIED BY '" . $pass . "'");
            $this->m_sfConnect->exec("GRANT ALL ON " . $database . ".* TO '" . $user . "'@'" . $host . "'");
            $this->m_sfConnect->exec("FLUSH PRIVILEGES;");
        }
        $this->m_sfConnect->exec("USE " . $database . ";");
    }


     /**
     * Fetch the result of an sql query
     *
     * @param String $sql This is the sql query
     * @param bool $log This is to detemine if one wants to log the outcome or not
     * 
     * @return mixed <b>Array  of results</b> on success or <b>FALSE</b> on failure.
     */

    function fetch($sql, $log = true) {
        $type = $this->getAttribute('type');
        //echo $type." Connection Type <br>";
        //echo $sql .'<br>';
        if ($type <> false) {
            $this->setAttribute('result', array());
            $this->setStatus(true);

            $this->execute($sql, $log);

            if ($this->getStatus() == false) {
                return false;
            } else {
                $numrows = $this->m_sfStmt->rowCount();
                $numfields = $this->m_sfStmt->columnCount();
                $this->setAttribute('recordcount', $numrows);
                $this->setAttribute('fieldcount', $numfields);

                $fields = array();
                for ($iField = 0; $iField < $numfields; $iField++) {
                    $meta = $this->m_sfStmt->getColumnMeta($iField);
                    $fields[] = $meta;
                }
                $this->setAttribute('fields', $fields);

                $fetch = 0;
                $fetch_type = $this->getAttribute('fetch');
                switch ($fetch_type) {
                    case 'associative';
                        $fetch = PDO::FETCH_ASSOC;
                        break;
                    case 'numeric';
                        $fetch = PDO::FETCH_NUM;
                        break;
                    case 'both';
                        $fetch = PDO::FETCH_BOTH;
                        break;
                    default:
                        $this->setMessage("attribute['fetch'] must be either 'associative', 'numeric' or 'both' e.g. object->setAttribute['fetch'] = 'numeric';");
                        $this->setStatus(false);
                }
                if ($this->getStatus() == true) {
                    $this->deleteAttribute('result');
                    $this->setAttribute('result', array());
                    $this->setAttribute('result', $this->m_sfStmt->fetchAll($fetch));
                } else {
                    $this->setStatus(false);
                }
            }
        }
        return $this->getStatus();
    }

    /**
     * execute the sql query and optionally log both the query and the outcome of the sql
     * operation
     * 
     * @param String $sql This is the sql query
     * @param bool $log This is to detemine if one wants to log the outcome or not
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    function execute($sql, $log = true) {

        $sqlError = "[Execute] ¦=>  " . $sql;

        $type = $this->getAttribute('type');

        if ($type == false) {
            $this->setStatus(false);
            $sqlError.= "[Query Error] ¦=> database type is not set.";
            $this->setMessage($sqlError, $log);
            return false;
        }

        $this->setAttribute('sql', $sql);

        $this->m_sfStmt = $this->m_sfConnect->prepare($sql);

        if (!$this->m_sfStmt) {
            $this->setStatus(false);
            $sqlError.= "[Query Error] ¦=> Could not prepare the sql statement .";
            $this->setMessage($sqlError, $log);
            return false;
        }

        $status = $this->m_sfStmt->execute();

        if ($status == false) {
            $errArray = $this->m_sfStmt->errorInfo();
            $sqlError.= "[Query Error] ¦=>  " . $errArray[1] . " ¦ " . $errArray[2];
            $this->setMessage($sqlError, $log);
            $this->setStatus($status);
            return $status;
        }

        if ($log == true) {
            sfLogError::sfLogEntry($sqlError);
        }
        $this->setStatus($status);
        return $status;
    }

    /**
     * Get the count of records fetched or executed
     *
     * @return int the record count of an sql query.
     */

    function recordCount() {
        return $this->getAttribute('recordcount');
    }

    /**
     * get the last insert Identifier of a table (usually of an autoi incremented primary key)
     * 
     * @return int the Identifier generated by the sql operation afte an INSERT statement.
     */

    function lastInsertId() {
        return $this->m_sfConnect->lastInsertId();
    }

}

?>
