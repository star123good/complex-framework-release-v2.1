<?php

    namespace Middlewares;

    if ( ! defined('CORRECT_PATH')) exit();

/**********************************************************************************************
 *
 *      CORS Middleware
 *
 *      Cross-Origin Resource Sharing
 *
**********************************************************************************************/


use Library\Middleware as Middleware;


/*
 *      CORS Middleware Class
 */
class CORSMiddleware extends Middleware {

    /**
     *      customize allowed domain list
     */
    private $allowed_domains = array(
            '*',
        );

    /**
     *      after handler
     *      add cors to response header
     *      @return void
     */
    public function afterHandler()
    {
        // modify orgin using $allowed_domains
        $this->_getResponse()->setHeader('Access-Control-Allow-Origin', '*');
        $this->_getResponse()->setHeader('Access-Control-Allow-Methods', '*');
    }

}