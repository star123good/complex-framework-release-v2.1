<?php

    namespace Library;

    if ( ! defined('CORRECT_PATH')) exit();

/**********************************************************************************************
 *
 *      View Class
 *
 *      view parameters
 *      layout, page, css, js, etc
 *
**********************************************************************************************/


use \Config;
use Library\Log as Log;


/*
 *      View Class
 */
class View {

    protected   $layout,                            // layout file
                $page,                              // view page file
                $css,                               // css files
                $js,                                // js files
                $data,                              // data array
                $flagTheme = true;                  // data array

    private static $instance;                       // THE only instance of the class


    public function __construct()
    {
        $this->init();
    }

    /**
     *      get instance
     *      @return     View
     *      @example    View::getInstance()
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
     *      init | clear
     *      @param  string  $key
     *      @return View
     */
    public function init($key=null)
    {
        if (is_null($key)) {
            $this->layout = 'main';
            $this->page = null;
            $this->css = array();
            $this->js = array();
            $this->data = array();
        }
        else if ($key == 'layout') $this->layout = 'main';
        else if ($key == 'page') $this->page = null;
        else if ($key == 'css') $this->css = array();
        else if ($key == 'js') $this->js = array();
        else if ($key == 'data') $this->data = array();

        return $this;
    }

    /**
     *      set layout
     *      @param  string  $value
     *      @return View
     */
    public function setLayout($value)
    {
        $this->layout = $value;
        return $this;
    }

    /**
     *      set page
     *      @param  string  $value
     *      @return View
     */
    public function setPage($value)
    {
        $this->page = $value;
        return $this;
    }

    /**
     *      get page
     *      @return string
     */
    public function getPage()
    {
        return PATH_VIEW_PAGES . $this->page . ".view.php";
    }

    /**
     *      get path via key
     *      @param  string  $key
     *      @return string  $path
     */
    public function getPath($key=null)
    {
        if (is_null($key)) $path = Config::getConfig('PUBLIC_PATH');
        else if ($key == "assets") $path = Config::getConfig('ASSETS_PATH');
        else if ($key == "css") $path = Config::getConfig('CSS_PATH');
        else if ($key == "js") $path = Config::getConfig('JS_PATH');
        else if ($key == "fonts") $path = Config::getConfig('FONTS_PATH');
        else if ($key == "images") $path = Config::getConfig('IMAGES_PATH');
        else $path = "";

        return $path;
    } 

    /**
     *      add css
     *      @param  string  $value
     *      @param  string  $key
     *      @return View
     */
    public function addCss($value, $key=null)
    {
        $this->css[] = $this->getPath($key) . $value;

        return $this;
    }

    /**
     *      get css
     *      @return array
     */
    public function getCss()
    {
        return $this->css;
    }

    /**
     *      add js
     *      @param  string  $value
     *      @param  string  $key
     *      @return View
     */
    public function addJs($value, $key=null)
    {
        $this->js[] = $this->getPath($key) . $value;
        
        return $this;
    }

    /**
     *      get js
     *      @return array
     */
    public function getJs()
    {
        return $this->js;
    }

    /**
     *      set data
     *      @param  string  $key
     *      @param  mixed   $value
     *      @return View
     */
    public function setData($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     *      get data
     *      @param  string  $key
     *      @return mixed
     */
    public function getData($key)
    {
        return (isset($this->data[$key])) ? $this->data[$key] : null;
    }

    /**
     *      render
     *      @param  int     $statusCode
     *      @param  string  $statusText
     *      @return void
     */
    public function render($statusCode, $statusText)
    {
        global $view;
        $view = self::$instance;

        // check error from status code
        $flagErrorExist = ($statusCode >= 300);
        $errorContent = $statusCode . " " . $statusText;

        // check template files exist
        $layout_file = PATH_VIEW_LAYOUTS . $this->layout . ".layout.php";
        if (!$flagErrorExist && !(file_exists($layout_file) && file_exists($this->getPage()))){
            $flagErrorExist = true;
            $errorContent = "Template Not Found";
        }

        // check error page
        if ($flagErrorExist) {
            $this->setPage('errors/'.$statusCode);
            if (!file_exists($this->getPage())) $this->setPage('errors/default');

            $this->setData('title', 'Error - Complex')
                ->setData('body_class_name', 'error-body')
                ->setData('content', $errorContent)
                ->setLayout('basic');
            
            $layout_file = PATH_VIEW_LAYOUTS . $this->layout . ".layout.php";
        }

        // log
        Log::addLog("layout is " . $this->layout);
        Log::addLog("page is " . $this->page);
        $log = Log::getLog();
        if ($log) $this->setData('log', $log);

        // show default theme
        $this->setTheme();

        // include layout file
        include_once($layout_file);
    }

    /**
     *      set default theme 
     *      using config.json THEME / CSS, JS
     *      @return View
     */
    private function setTheme()
    {
        if ($this->flagTheme && null !== (Config::getConfig('THEME'))) {
            if (is_array(Config::getConfig('THEME')['CSS'])) {
                // default css files
                foreach (Config::getConfig('THEME')['CSS'] as $css) {
                    $this->addCss($css[0], $css[1]);
                }
            }
            if (is_array(Config::getConfig('THEME')['JS'])) {
                // default js files
                foreach (Config::getConfig('THEME')['JS'] as $js) {
                    $this->addJs($js[0], $js[1]);
                }
            }
            // set some theme variables
            $this->setData('WEB_PATH', Config::getConfig('WEB_PATH'));
            $this->setData('SITE_TITLE', Config::getConfig('SITE_TITLE'));
        }
        return $this;
    }

    /**
     *      disable default theme
     *      @return View
     */
    public function disableTheme()
    {
        $this->flagTheme = false;
        return $this;
    }
    
}