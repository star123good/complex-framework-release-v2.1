<?php

    namespace Library;

    if ( ! defined('CORRECT_PATH')) exit();

/**********************************************************************************************
 *
 *      Controller Class
 *
 *      Request, Response, View, Session, Language instances
 *
**********************************************************************************************/


use Library\Log as Log;
use Library\Request as Request;
use Library\Response as Response;
use Library\View as View;
use Library\Session as Session;
use Library\Language as Language;


/*
 *      Controller Class
 */
class Controller {

    protected   $request = null,                // Request instance
                $response = null,               // Response instance
                $view = null,                   // View instance
                $session = null,                // Session instance
                $language = null,               // Language instance
                $flagJSON = false,              // flag JSON or HTML
                $errorCodes = array();          // enabled error codes


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
     *      get view
     *      @return View
     */
    protected function _getView()
    {
        if (is_null($this->view)) {
            $this->view = View::getInstance();
            // set errors
            $this->_setErrorsToView();
        }
        return $this->view;
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
     *      get language
     *      @return Language
     */
    protected function _getLanguage()
    {
        if (is_null($this->language)) {
            $this->language = Language::getInstance();
        }
        return $this->language;
    }

    /**
     *      set errors to view
     *      @return void
     */
    private function _setErrorsToView()
    {
        $errCode = $this->_getRequest()->getParameter('error', 'GET');
        $errMsg = "";
        if ($errCode) {
            if (empty($this->errorCodes) || in_array($errCode, $this->errorCodes)) {
                $errMsg = $this->_getLanguage()->getData($errCode);
            }
        }
        $this->view->setData('error_msg', $errMsg);
    }

    /**
     *      set error codes enabled
     *      @param  mixed   $value
     *      @return void
     */
    protected function _setErrorCodesEnabled($value)
    {
        if (is_array($value)) $this->errorCodes = $value;
        else if (is_string($value)) $this->errorCodes[] = $value;
    }

    /**
     *      set JSON
     *      @param  bool    $value
     *      @return void
     */
    protected function _setJSON($value=true)
    {
        $this->flagJSON = $value;
        $this->_getResponse()->setAPI($this->flagJSON);
    }
    
}