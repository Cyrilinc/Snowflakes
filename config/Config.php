<?php

/**
 * Application config.
 */
final class Config {

    /** @var array config data */
    private static $m_data = null;

    /**
     * @return bool
     */
    public static function checkConfig($inifile = '../config/config.ini') {
        return file_exists($inifile);
    }

    /**
     * @return array
     *
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

    public static function saveConfig($m_data, $inifile = '../config/config.ini') {

        $config = self::arrayToConfig($m_data);
        return file_put_contents($inifile, $config);
    }

    /**
     * @return array
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
