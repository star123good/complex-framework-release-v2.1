<?php

    namespace Library;

    if ( ! defined('CORRECT_PATH')) exit();

/**********************************************************************************************
 *
 *      Middleware Handler
 *
 *
**********************************************************************************************/


use Library\Log as Log;
use Library\Request as Request;
use Library\Response as Response;
use Library\Session as Session;


/*
 *      Middleware Class
 */
class Middleware {

    protected   $request = null,                // Request instance
                $response = null,               // Response instance
                $session = null;                // Session instance


    public function __construct()
    {
    }

    /**
     *      get request
     *      @return Request
     */
    protected function _getRequest()
    {
        if (is_null($this->request)) {
            $this->request = Request::getInstance();
        }
        return $this->request;
    }

    /**
     *      get response
     *      @return Response
     */
    protected function _getResponse()
    {
        if (is_null($this->response)) {
            $this->response = Response::getInstance();
        }
        return $this->response;
    }

    /**
     *      get session
     *      @return Session
     */
    protected function _getSession()
    {
        if (is_null($this->session)) {
            $this->session = Session::getInstance();
        }
        return $this->session;
    }

    /**
     *      handler
     *      each middleware has to re-build this function
     *      @return bool
     */
    public function handle()
    {
        return true;
    }

    /**
     *      error handler
     *      @return void
     */
    public function errorHandler()
    {
        $this->_getResponse()->setStatusCode(500);
        Log::addLog("Middleware " . get_class($this) . " Failed");
    }

    /**
     *      after handler
     *      @return void
     */
    public function afterHandler()
    {
        // nothing
    }

}