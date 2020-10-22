<?php

    namespace Library;

    if ( ! defined('CORRECT_PATH')) exit();

/**********************************************************************************************
 *
 *      Log Handler
 *
 *      add & show all logs
 *
**********************************************************************************************/


/*
 *      Log Class
 */
class Log {

    public static   $logs = array(),                // array of log data
                    $flagVisible = false,           // response | view visible or not
                    $flagWritable = false;          // write log file or not

    public function __construct()
    {
    }

    /**
     *      add log
     *      @param  string|array  $log
     *      @return void
     */
    public static function addLog($log)
    {
        if (!is_null($log)) {
            if (is_array($log) && !empty($log)){
                static::$logs[] = json_encode($log);
            }
            else if ($log != "") {
                static::$logs[] = $log;
            }
        }
    }

    /**
     *      set visible to show log
     *      @return void
     */
    public static function setVisible()
    {
        static::$flagVisible = true;
    }

    /**
     *      set writable to save log file
     *      @return void
     */
    public static function setWritable()
    {
        static::$flagWritable = true;
    }

    /**
     *      get log
     *      @param  boolean $isVisible
     *      @return string|null
     */
    public static function getLog($isVisible=false)
    {
        $isVisible = $isVisible || static::$flagVisible;
        $nowTime = date("H:i:s");
        if ($isVisible) {
            $result = "";
            foreach (static::$logs as $index => $log) {
                $result .= " | [" . $nowTime . "] | LOG_" . $index . ": " . $log . PHP_EOL;
            }
        }
        else {
            $result = null;
        }
        return $result;
    }

    /**
     *      save logs to file
     *      @return void
     */
    public static function saveLogFile()
    {
        if (!static::$flagWritable) return;
        $nowDate = date("Y-m-d");
        $logFile = PATH_LOGS . $nowDate . ".log";
        $logs = static::getLog(true);
        $logHandler = fopen($logFile, "a+");
        if ($logHandler && $logs) {
            fwrite($logHandler, $logs);
            fclose($logHandler);
        }
    }

}