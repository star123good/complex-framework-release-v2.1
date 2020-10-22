<?php

    namespace Library;

    if ( ! defined('CORRECT_PATH')) exit();

/**********************************************************************************************
 *
 *      Language Class
 *
 *      multiple languages
 *
**********************************************************************************************/


use \Config;
use Library\Log as Log;


/*
 *      Language Class
 */
class Language {

    protected   $local,                             // local area
                $datas;                             // language datas

    private static $instance;                       // THE only instance of the class


    public function __construct()
    {
        $this->setLocal((Config::getConfig('DEFAULT_LANG')) ? Config::getConfig('DEFAULT_LANG') : 'en');
    }

    /**
     *      get instance
     *      @return     Language
     *      @example    Language::getInstance()
     */
    public static function getInstance()
    {
        if ( !isset(self::$instance))
        {
            self::$instance = new self;
        }
       
        return self::$instance;
    }

    /**
     *      read language json file
     *      @return  void
     */
    private function readLanguageJSON()
    {
        $string = file_get_contents(PATH_LANGUAGES . $this->local . ".json");
        $json = json_decode($string, true);
        $this->datas = array_merge($this->datas, $json);
    }

    /**
     *      set local
     *      @param  string  $local
     *      @return View
     */
    public function setLocal($local)
    {
        $this->local = $local;
        $this->datas = array();
        $this->readLanguageJSON();

        return $this;
    }

    /**
     *      get data value 
     *      @param  string  $key
     *      @return string
     */
    public function getData($key)
    {
        if (isset($this->datas[$key])) {
            return $this->datas[$key];
        }
        return "";
    }
    
}