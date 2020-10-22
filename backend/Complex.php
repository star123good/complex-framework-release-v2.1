<?php

    if ( ! defined('CORRECT_PATH')) exit();

/**********************************************************************************************
 *
 *      auto load
 *      main core of Complex framework
 *
 *      include required library, help, controller, model, view
 *      if file exist, include_once or require_once
 *      create each object from required class
 *      using Globals class with static variables such as controller instance, view instance,
 *      request instance, response instance, session instance, database instance, log class, etc.
 *
**********************************************************************************************/


use Library\Route as Route;
use Library\Command as Command;
use Library\Request as Request;
use Library\Response as Response;
use Library\Log as Log;


/*
 *      Complex Class 
 */
class Complex {
    
    private $router,                      		// Route Class Instance
    		$controller,                      	// Controller Class Instance
    		$command,                         	// Command Class Instance
    		$request,                         	// Request Class Instance
    		$response,                        	// Response Class Instance
    		$middlewares,                       // Middleware Class Instance List
    		$initMiddlewares;                   // Initialized Middleware Class Name List
	
	private static 	$flagDevelop,				// environment is development
					$instance;					// THE only instance of the class
			

	public function __construct()
	{
	}

    /**
     *      get instance
     *      @return     Complex
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
	 * 		initialize
	 * 		create some instances
	 * 		@return void
	 */
	private function init()
	{
		// create new request
		$this->request = Request::getInstance();

		// get router from global router
		$this->router = Route::getInstance();

		// init middlewares from config
		$this->initMiddlewares = Config::getConfig('MIDDLEWARES');
		if (is_null($this->initMiddlewares)) $this->initMiddlewares = array();
		$this->router->initMiddlewares($this->initMiddlewares);
		// Middleware Classname List
		$this->middlewares = array();
		
		if ($this->request->isCLI()) {
			// create new command
			$this->command = new Command();
		}
		else {
			// create new response
			$this->response = Response::getInstance();
		}

		// set env
		$this->setEnv();
	}
	
	/**
	 * 		get file name from class name
	 * 		@param	string	$className
	 * 		@return string
	 */
	private static function getFileName($className)
	{
		// explode classname to filepaths array
		$filepaths = explode("\\", $className);
		$last = count($filepaths) - 1;

		// get full path from filepaths array
		$fullpath = PATH_BACKEND;
		for($i = 0; $i < $last; $i ++) {
			$fullpath .= strtolower($filepaths[$i]) . "/";
		}
		$fullpath .= $filepaths[$last] . ".php";
		
		return $fullpath;
	}

	/**
	 * 		auto load
	 * 		@param	string	$className
	 * 		@return void
	 */
    public static function autoload($className)
	{
		$filename = Complex::getFileName($className);

        // require once
		if (file_exists($filename)) {
			require_once($filename);
		}
	}

	/**
	 * 		exception handler
	 * 		@param	Exception	$exception
	 * 		@return void
	 */
    public static function exceptionHandler($exception)
	{
		if (static::$flagDevelop) echo " | Exception: " . $exception->getMessage() . PHP_EOL;
	}

	/**
	 * 		error handler
	 * 		@param	int		$errorLevel
	 * 		@param	string	$errorMessage
	 * 		@param	string	$errorFile
	 * 		@param	int		$errorLine
	 * 		@param	array	$errorContext
	 * 		@return void
	 */
    public static function errorHandler($errorLevel, $errorMessage, $errorFile, $errorLine, $errorContext)
	{
		if (static::$flagDevelop) echo " | Error: [$errorLevel] $errorMessage - $errorFile:$errorLine " . PHP_EOL;
	}

	/**
	 * 		set environment
	 * 		@return void
	 */
	private function setEnv()
	{
		switch (Config::getConfig('ENV')) {
			case "production" :
				static::$flagDevelop = false;
				break;
			case "development" :
			default :
				static::$flagDevelop = true;
				break;
		}
	}
	
	/**
	 * 		register
	 * 		calls some of ini_set()
	 * 		@return void
	 */
	public static function register()
	{
		// error report according to environment
		if (static::$flagDevelop) error_reporting(E_ALL);
		else error_reporting(0);

		// autoload register
		spl_autoload_register('Complex::autoload');

		// set exception handler
		set_exception_handler('Complex::exceptionHandler');

		// set error handler
		set_error_handler('Complex::errorHandler');
	}

	/**
	 * 		run app
	 * 		@return void
	 */
	public static function run()
	{
		$app = static::getInstance();
		// init
		$app->init();

		// save logs in file
		if (Config::getConfig('SAVE_LOG_FILE')) Log::setWritable();
		// show logs in the case of development
		if (Config::getConfig('SHOW_LOG_VIEW')) Log::setVisible();

		// check from browser or cli
		if ($app->request->isCLI()) {
			Log::addLog("ClI running...");

			// run command
			$app->command->run();
		}
		else {
			// run response
			$app->runRespond();
		}
			
		// save logs
		Log::saveLogFile();
	}

	/**
	 * 		run browser respond
	 * 		@return void
	 */
	private function runRespond()
	{
		Log::addLog("BROWSER running...");
		// set URI to router from request URI
		$this->router->setRequest($this->request->getPath(), $this->request->getMethod());

		// get controller & method
		$controllerName = $this->router->getController();
		$methodName = $this->router->getMethod();
		$middlewareNames = $this->router->getMiddlewares();

		$flagNotFound = true;
		$flagCheckMiddleware = true;

		// middleware
		if (!empty($middlewareNames)) {
			foreach($middlewareNames as $middlewareName) {
				$middleware = new $middlewareName;
				$this->middlewares[] = $middleware;

				$flagCheckMiddleware = $middleware->handle();
				Log::addLog("middlewares : " . $middlewareName . ", checking result : " . $flagCheckMiddleware);

				if (!$flagCheckMiddleware) break;
			}
			
		}

		// controller
		if ($flagCheckMiddleware && $controllerName && class_exists($controllerName)) {
			$this->controller = new $controllerName;
			// method
			if ($methodName && method_exists($controllerName, $methodName)) {
				// call function
				call_user_func_array(array($this->controller, $methodName), $this->router->getParameters());

				$flagNotFound = false;
				
				Log::addLog("controller : " . $controllerName . ", method : " . $methodName);
			}
		}

		if (!$flagCheckMiddleware) {
			// middleware checked false
			$this->middlewares[count($this->middlewares)-1]->errorHandler();
		}
		else if ($flagNotFound) {
			// not found controller or method
			$this->response->setStatusCode(404);
			Log::addLog("Controller and Method Not Found");
		}

		// after middleware check
		foreach($this->middlewares as $middleware) {
			$middleware->afterHandler();
		}

		// reponse
		$this->response->send();
	}

}