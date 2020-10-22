<?php

    namespace Library;

    if ( ! defined('CORRECT_PATH')) exit();

/**********************************************************************************************
 *
 *      Route Class
 *
 *      METHOD - GET, POST, PUT, DELETE
 *      URI - object/method/id, etc
 *      middleware
 *
**********************************************************************************************/


use Library\Log as Log;


/**
 *      Route Class
 */
class Route {

    const   METHODS = array('GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTION'),         // request methods
            SEPERATOR = '@',                                                            // action sperator
            FLAG_TO_LOWER = true;                                                       // if uri is ignored lowercase or uppercase

    private $routes = array(),                  // routes array
            $initMiddlewares = array(),         // initialized middlewares
            $routeIndex = array(),              // current index of routes
            $controller = null,                 // controller name
            $method = null,                     // method name
            $parameters = array(),              // parameters array
            $middlewares = array();             // middleware name list

    private static $instance;                   // THE only instance of the class


    public function __construct()
    {
    }

    /**
     *      get instance
     *      @return     Route
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
     *      set initialized middlewares
     *      @param  array   $initMiddlewares
     */
    public function initMiddlewares($initMiddlewares)
    {
        $this->initMiddlewares = $initMiddlewares;
    }

    /**
     *      get method
     *      @param  string  $uri
     *      @param  string  $action
     *      @param  bool    $isClearIndex
     *      @return Route
     *      @example    $router->get('path_to/active/{params1}/{params2}', 'Controller@methods')
     */
    public static function get($uri, $action, $isClearIndex=true)
    {
        $router = static::getInstance();
        $router->addRoute('GET', $uri, $action, $isClearIndex);
        return $router;
    }

    /**
     *      post method
     *      @param  string  $uri
     *      @param  string  $action
     *      @param  bool    $isClearIndex
     *      @return Route
     */
    public static function post($uri, $action, $isClearIndex=true)
    {
        $router = static::getInstance();
        $router->addRoute('POST', $uri, $action, $isClearIndex);
        return $router;
    }

    /**
     *      put method
     *      @param  string  $uri
     *      @param  string  $action
     *      @param  bool    $isClearIndex
     *      @return Route
     */
    public static function put($uri, $action, $isClearIndex=true)
    {
        $router = static::getInstance();
        $router->addRoute('PUT', $uri, $action, $isClearIndex);
        return $router;
    }

    /**
     *      delete method
     *      @param  string  $uri
     *      @param  string  $action
     *      @param  bool    $isClearIndex
     *      @return Route
     */
    public static function delete($uri, $action, $isClearIndex=true)
    {
        $router = static::getInstance();
        $router->addRoute('DELETE', $uri, $action, $isClearIndex);
        return $router;
    }

    /**
     *      Restful API
     *      @param  string  $uri
     *      @param  string  $controller
     *      @return Route
     */
    public static function rest($uri, $controller)
    {
        $router = static::getInstance();
        $router->get($uri,                                              $controller . self::SEPERATOR . "index",    true);
        $router->get($uri . URI_SPLIT . "new",                          $controller . self::SEPERATOR . "new",      false);
        $router->get($uri . URI_SPLIT . "edit" . URI_SPLIT . "{id}",    $controller . self::SEPERATOR . "edit",     false);
        $router->get($uri . URI_SPLIT . "{id}",                         $controller . self::SEPERATOR . "show",     false);
        $router->post($uri,                                             $controller . self::SEPERATOR . "insert",   false);
        $router->put($uri . URI_SPLIT . "{id}",                         $controller . self::SEPERATOR . "update",   false);
        $router->delete($uri . URI_SPLIT . "{id}",                      $controller . self::SEPERATOR . "remove",   false);
        return $router;
    }

    /**
     *      add middleware
     *      @param  string|array  $middlewares     default : "" | []
     *      @example    $router->middleware(['MiddlewareClass1', 'MiddlewareClass2'])
     *      @return Route
     */
    public function middleware($middlewares)
    {
        if (is_array($middlewares) && !empty($middlewares)) {
            foreach($middlewares as $middleware) $this->addMiddleware($middleware);
        }
        else $this->addMiddleware($middlewares);
        return $this;
    }

    /**
     *      add route
     *      @param  string  $type
     *      @param  string  $uri
     *      @param  string  $action
     *      @param  bool    $isClearIndex
     *      @return void
     */
    private function addRoute($type, $uri, $action, $isClearIndex)
    {
        if (in_array($type, self::METHODS)) {
            // check action has '@'
            if (substr_count($action, self::SEPERATOR) == 1) {
                // action
                list($actionController, $actionMethod) = explode(self::SEPERATOR, $action);

                // uri
                list($uriPattern, $actionParameters) = $this->processURI($uri);

                // add routes list
                $this->routes[] = array(
                    'TYPE' => $type,
                    'URI' => $uriPattern,
                    'CONTROLLER' => $actionController,
                    'METHOD' => $actionMethod,
                    'PARAMETERS' => $actionParameters,
                    'MIDDLEWARES' => array(),
                );

                // index of routes
                if ($isClearIndex) {
                    $this->routeIndex = array(count($this->routes) - 1);
                }
                else {
                    $this->routeIndex[] = count($this->routes) - 1;
                }
            }
        }
    }

    /**
     *      add middleware to app
     *      @param  string  $middleware
     *      @return void
     */
    private function addMiddleware($middleware)
    {
        if (is_string($middleware) && !empty($this->routeIndex)) {
            foreach($this->routeIndex as $index) {
                $this->routes[$index]['MIDDLEWARES'][] = $middleware;
            }
        }
    }

    /**
     *      process from uri
     *      @param  string  $uri
     *      @return array
     */
    private function processURI($uri)
    {
        $resURI = "";
        $resParam = array();

        $temp = explode(URI_SPLIT, $uri);
        foreach ($temp as $tempPattern) {
            $tempPattern = trim($tempPattern);
            if ($tempPattern == "") continue;

            if (strlen($tempPattern) > 2 && $tempPattern == "{".substr($tempPattern, 1, -1)."}") {
                $resParam[] = substr($tempPattern, 1, -1);
            }
            else {
                $resURI .= URI_SPLIT . $tempPattern;
            }
        }

        if ($resURI == "") $resURI = URI_SPLIT;
        else if (self::FLAG_TO_LOWER) $resURI = strtolower($resURI) . URI_SPLIT;

        return array($resURI, $resParam);
    }

    /**
     *      set URI & Type
     *      @param  string  $uri
     *      @return void
     */
    public function setRequest($uri, $type)
    {
        $flagSearch = false;

        if (self::FLAG_TO_LOWER) $uri = strtolower($uri);
        if (substr($uri, -1) != URI_SPLIT) $uri = $uri . URI_SPLIT;

        if (!empty($this->routes)) {
            foreach ($this->routes as $route) {
                // Method Type && URI
                if ($type == $route['TYPE'] && strpos($uri, $route['URI']) === 0) {
                    $temp = array();

                    // Parameters
                    if (empty($route['PARAMETERS'])) {
                        if ($uri == $route['URI']) $flagSearch = true;
                    }
                    else {
                        $temp = array_filter(explode(URI_SPLIT, $uri), function($u){ return $u != ""; });
                        $tempRoute = array_filter(explode(URI_SPLIT, $route['URI']), function($u){ return $u != ""; });
                        if (count($temp) == (count($route['PARAMETERS']) + count($tempRoute))) $flagSearch = true;
                    }

                    // if matching
                    if ($flagSearch) {
                        // controller
                        $this->controller = $route['CONTROLLER'];
                        // method
                        $this->method = $route['METHOD'];

                        // parameters
                        if (empty($temp)) {
                            $this->parameters = array();
                        }
                        else {
                            $temp = array_slice($temp, count($tempRoute));
                            $this->parameters = array_combine($route['PARAMETERS'], $temp);
                        }

                        // middleware
                        $this->middlewares = array_merge($this->initMiddlewares, $route['MIDDLEWARES']);
                        break;
                    }
                }
            }
        }
    }

    /**
     *      get controller name
     *      @return string
     */
    public function getController()
    {
        return "Controllers\\" . $this->controller . "Controller";
    }

    /**
     *      get method name
     *      @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     *      get parameters array
     *      @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     *      get middlewares array
     *      @return array
     */
    public function getMiddlewares()
    {
        return array_map(function($middleware) {
            return "Middlewares\\" . $middleware . "Middleware";
        }, $this->middlewares);
    }

}