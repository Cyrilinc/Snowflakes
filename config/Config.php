<?php

/**
 * Application configuration file handler used to add, edit and
 * delete configuration elements for Snowflakes API
 * 
 * @author Cyril Adelekan
 */
final class Config {

    /** @var array config data */
    private static $m_data = null;

    /**
     * Checks if a configuration file exists
     *
     * @param string $inifile <p> The configuration file </p>
     *
     * @return bool <b>TRUE</b> if user exists or <b>FALSE</b> otherwise.
     */
    public static function checkConfig($inifile = '../config/config.ini') {
        return file_exists($inifile);
    }

    /**
     * Get a specific section in the configuration file
     * 
     * @param string $section <p> Th tag/element header name of the configuration element to get </p> 
     * @param string $inifile <p> The configuration file </p>
     * 
     * @return array The specific configuration data in form of an array
     */
    public static function getConfig($section = null, $inifile = '../config/config.ini') {
        if ($section === null) {
            return self::getData($inifile);
        }

        $m_data = self::getData($inifile);
        if (!$m_data) {
            return $m_data;
        }

        if (!array_key_exists($section, $m_data)) {
            trigger_error('Unknown config section: ' . $section);
        }

        return $m_data[$section];
    }

    /**
     * Add a section in the configuration file
     * 
     * @param string $section <p> Th tag/element header name of the configuration element to Add </p> 
     * @param string $inifile <p> The configuration file </p>
     * 
     * @return array The combination of all the configuration data in form of an array
     */
    public static function addSection($section, $inifile = '../config/config.ini') {
        if (!$section) {
            return false;
        }

        $m_data = self::getData($inifile);
        if (array_key_exists($section, $m_data)) {
            return true;
        }

        $m_data[$section]["New"] = "";

        self::saveConfig($m_data, $inifile);

        return $m_data;
    }

    /**
     * Deletes a section in the configuration file
     * 
     * @param string $section <p> Th tag/element header name of the configuration element to delete </p> 
     * @param string $inifile <p> The configuration file </p>
     * 
     * @return array The rest of the configuration data in form of an array
     */
    public static function deleteSection($section, $inifile = '../config/config.ini') {
        if (!$section) {
            return false;
        }

        $m_data = self::getData($inifile);
        if (!array_key_exists($section, $m_data)) {
            return false;
        }

        unset($m_data[$section]);

        self::saveConfig($m_data, $inifile);

        return $m_data;
    }

    /**
     * Adds a new configuration element to the configuration file
     * 
     * @param string $value <p> The value of  configuration element to set</p> 
     * @param string $tag <p> The tag/element name of the configuration element to set </p>
     * @param string $section <p> Th tag/element header name of the configuration element to set </p> 
     * @param string $inifile <p> The configuration file </p>
     * 
     * @return array The configuration data in form of an array
     */
    public static function addConfig($value, $tag, $section, $inifile = '../config/config.ini') {

        if (!$section || !$tag) {
            return false;
        }

        $m_data = self::getData($inifile);
        $m_data[$section][$tag] = $value;

        if (!self::saveConfig($m_data, $inifile)) {
            return false;
        }

        return $m_data;
    }

    /**
     * Sets an the value of an element in a section of a configuration file
     * e.g $config[$section][$tag] = $value;. if the section doesn't exists
     * in the configuration file, it is created.
     * 
     * @param string $value <p> The value of  configuration element to set</p> 
     * @param string $tag <p> The tag/element name of the configuration element to set </p>
     * @param string $section <p> Th tag/element header name of the configuration element to set </p> 
     * @param string $inifile <p> The configuration file </p>
     * 
     * @return array The configuration data in form of an array
     */
    public static function setConfig($value, $tag, $section, $inifile = '../config/config.ini') {

        if (!$section || !$tag) {
            return false;
        }

        $m_data = self::getData($inifile);
        if (!array_key_exists($section, $m_data)) {
            self::addSection($section, $inifile);
        }

        $m_data[$section][$tag] = $value;

        self::saveConfig($m_data, $inifile);

        return $m_data;
    }

    /**
     * Create a string version formated in a configuration mannger given keys of the array
     * 
     * @param array $m_data <p> The Array of configration keys and values to convert</p> 
     * 
     * @return string The configuration data in string form
     */
    public static function arrayToConfig($m_data) {

        if (!$m_data) {
            return false;
        }

        $configString = "\n";

        foreach ($m_data as $section => $value) {
            $configString.="[" . $section . "]\n";

            foreach ($value as $tag => $val) {
                $configString.=$tag . '="' . $val . "\"\n";
            }

            $configString.="\n";
        }
        return $configString;
    }

    /**
     * Save an array into a configuration filel, but first a configuration array
     * has to be formatted in a way that is readable {@see arrayToConfig}
     * 
     * @param array $m_data <p> The Array of configration keys and values to store</p> 
     * @param string $inifile <p> The configuration file </p>
     * 
     * @return array The configuration data in form of an array
     */
    public static function saveConfig($m_data, $inifile = '../config/config.ini') {

        $config = self::arrayToConfig($m_data);
        return file_put_contents($inifile, $config);
    }

    /**
     * Get the data from a ini file in form of an array
     * 
     * @param string $inifile <p> The configuration file </p>
     * 
     * @return array The configuration data in form of an array
     */
    private static function getData($inifile) {

        if (!$inifile || !file_exists($inifile)) {
            return false;
        }

        $m_data = parse_ini_file($inifile, true);
        return $m_data;
    }

}

?>
