<?php

    namespace Library;

    if ( ! defined('CORRECT_PATH')) exit();

/**********************************************************************************************
 *
 *      Request Handler
 *
 *      URI
 *      get oject, get method, get parameters
 *      if URI is /api/~~~, it must return json.
 *      parameters - $_GET or $_POST or file(PUT) or Cookie
 *      parameters - id, page, order, api_key, search, filters, fields, next_page, etc
 *
**********************************************************************************************/


use Library\Log as Log;


/*
 *      Request Class
 */
class Request {

    private $method,                        // method of request
            $uri,                           // request uri value
            $path,                          // path uri
            $query,                         // query string
            $getParamters = array(),        // GET parameters array
            $postParamters = array(),       // POST parameters array
            $requestParamters = array(),    // REQUEST parameters array
            $putParamters = null,           // PUT parameters array
            $cookieParamters = array(),     // Cookie parameters array
            $headerParamters = array(),     // Header parameters array
            $flagCLI;                       // cli checking flag

    public  $ipAddress,                     // remote ip address
            $userAgent,                     // http user agent
            $argv;                          // argv array

    private static $instance;               // THE only instance of the class


    /**
     *      construct
     */
    public function __construct()
    {
        // get flag CLI
        $this->flagCLI = null;
        $this->flagCLI = $this->isCLI();
        
        // check from browser or cli
        if ($this->flagCLI) {
            // set some args from server
            $this->argv = $_SERVER['argv'];
        }
        else {
            // set some infos from server
            $this->method = $_SERVER['REQUEST_METHOD'];

            $this->uri = $_SERVER['REQUEST_URI'];

            if (isset($_SERVER['PATH_INFO'])) {
                $this->path = $_SERVER['PATH_INFO'];
            }
            else {
                $this->path = URI_SPLIT;
            }

            $this->query = $_SERVER['QUERY_STRING'];

            // client parameters
            $this->ipAddress = $_SERVER['REMOTE_ADDR'];
            $this->userAgent = $_SERVER['HTTP_USER_AGENT'];
        }

        // parameters
        if (isset($_GET)) $this->getParamters = $_GET;
        if (isset($_POST)) $this->postParamters = $_POST;
        if (isset($_REQUEST)) $this->requestParamters = $_REQUEST;
        $this->requestParamters = array_merge($this->requestParamters, $this->getParamters, $this->postParamters);
        if (isset($_COOKIE)) $this->cookieParamters = $_COOKIE;
        if (function_exists('getallheaders')) $this->headerParamters = getallheaders();
        // parse_str(file_get_contents('php://input'), $this->putParamters);
        $this->putParamters = json_decode(file_get_contents('php://input'), true);
    }

    /**
     *      get instance
     *      @return     Request
     *      @example    Request::getInstance()
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
	 * 		check called from cli
	 * 		@return bool
	 */
	public function isCLI()
	{
        if (is_null($this->flagCLI)) {
            if (defined('STDIN')) {
                return true;
            }
            
            if (empty($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['HTTP_USER_AGENT']) && count($_SERVER['argv']) > 0) {
                return true;
            } 
            
            return false;
        }
        else {
            return $this->flagCLI;
        }
	}

    /**
     *      get URI
     *      @return string
     */
    public function getURI()
    {
        return $this->uri;
    }

    /**
     *      get path
     *      @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     *      get method
     *      @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     *      get parameter
     *      @param  string  $key
     *      @param  string  $type       GET | POST | PUT | DELETE | null
     *      @return mixed
     */
    public function getParameter($key, $type=null)
    {
        if (($type == 'GET' || is_null($type)) && isset($this->getParamters[$key])) 
            return $this->getParamters[$key];
        else if (($type == 'POST' || is_null($type)) && isset($this->postParamters[$key])) 
            return $this->postParamters[$key];
        else if (($type == 'PUT' || is_null($type)) && is_array($this->putParamters) && isset($this->putParamters[$key])) 
            return $this->putParamters[$key];
        else if (($type == 'DELETE' || is_null($type)) && is_array($this->putParamters) && isset($this->putParamters[$key]))
            return $this->putParamters[$key];
        else if (is_null($type) && isset($this->requestParamters[$key])) 
            return $this->requestParamters[$key];
        return null;
    }

    /**
     *      add parameter to request
     *      @param  string  $key
     *      @param  mixed   $value
     *      @return void
     */
    public function addParameter($key, $value)
    {
        $this->requestParamters[$key] = $value;
    }

    /**
     *      get parameters
     *      @param  string  $type       GET | POST | PUT | DELETE | null
     *      @return array
     */
    public function getParameters($type=null)
    {
        if ($type == 'GET')
            return $this->getParamters;
        else if ($type == 'POST') 
            return $this->postParamters;
        else if ($type == 'PUT') 
            return $this->putParamters;
        else if ($type == 'DELETE')
            return $this->putParamters;
        else if (is_null($type)) {
            return $this->requestParamters;
        }
        return array();
    }

    /**
     *      get cookie
     *      @param  string  $key
     *      @return mixed
     */
    public function getCookie($key)
    {
        if ($type == 'GET' && isset($this->cookieParamters[$key]))
            return $this->cookieParamters[$key];
        return null;
    }

    /**
     *      get header
     *      @param  string  $key
     *      @return mixed
     */
    public function getHeader($key)
    {
        if (isset($this->headerParamters[$key]))
            return $this->headerParamters[$key];
        return null;
    }

}