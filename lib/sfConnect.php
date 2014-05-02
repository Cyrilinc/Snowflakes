<?php

/**
 * Description of sfConnect
 *
 * @author cyrildavids
 */
class sfConnect {

    var $attributes = array('status' => true);
    var $sqlLite;
    public $m_sfConnect;
    public $m_sfStmt;

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

    function setAttribute($attribute, $value) {
        if ($this->getStatus() == true) {
            $this->deleteAttribute($attribute);
            $this->attributes[$attribute] = $value;
        }
    }

    function deleteAttribute($attribute) {
        if (array_key_exists($attribute, $this->attributes) == true) {
            unset($this->attributes[$attribute]);
        }
    }

    function getStatus() {
        return $this->attributes['status'];
    }

    function setStatus($status) {
        $this->attributes['status'] = $status;
    }

    function getMessage() {
        return $this->getAttribute('message');
    }

    function setMessage($message, $log = true) {
        if ($log == true) {
            sfLogError::sfLogEntry($message);
        }
        $this->setAttribute('message', $message);
    }

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

                        //TODO something with the username and password, possibly encryption
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

    function close() {
        $type = $this->getAttribute('type');
        if ($type <> false) {
            $this->setAttribute('link', NULL);
            $this->m_sfConnect = NULL;
        }
    }

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

    function recordCount() {
        return $this->getAttribute('recordcount');
    }

    function lastInsertId() {
        return $this->m_sfConnect->lastInsertId();
    }

}

?>
