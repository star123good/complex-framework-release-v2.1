<?php

    namespace Middlewares;

    if ( ! defined('CORRECT_PATH')) exit();

/**********************************************************************************************
 *
 *      Auth Middleware
 *
 *      authentication middleware
 *
**********************************************************************************************/


use Library\Middleware as Middleware;
use Services\JWTService;
use Models\UserModel;


/*
 *      Auth Middleware Class
 */
class AuthMiddleware extends Middleware {

    /**
     *      handler
     *      @return bool
     */
    public function handle()
    {
        return $this->isLogin();
    }

    /**
     *      error handler
     *      @return void
     */
    public function errorHandler()
    {
        $this->_getResponse()->setStatusCode(401);
    }

    /**
     *      middleware to check login
     *      @return bool
     */
    public function isLogin()
    {
        return ($this->getUserFromSessionOrHeader() ? true : false);
    }

    /**
     *      get user from session
     *      @return array
     */
    public function getUserFromSessionOrHeader()
    {
        if ($this->_getSession()->authorized) {
            // check session
            $jwt = $this->_getSession()->authorized;
        }
        else if ($this->_getRequest()->getHeader('Authorization')) {
            // check header
            $jwt = $this->_getRequest()->getHeader('Authorization');
            // bearer token
            if (preg_match('/Bearer\s(\S+)/', $jwt, $matches)) {
                $jwt = $matches[1];
            }
        }

        if (isset($jwt) && $jwt) {
            $json = JWTService::validateJWT($jwt);

            if ($json) {

                if (array_key_exists('id', $json) 
                    && array_key_exists('email', $json) 
                    && array_key_exists('token', $json)
                    && (array_key_exists('exp_verify', $json) && $json['exp_verify'])) {
                        $user = UserModel::find($json['id']);
                        
                        // check user email & token
                        if ($user->isExist()) {
                            if ($user->getAttribute('email') == $json['email']
                                && $user->getAttribute('token') == $json['token']) {
                                    // success
                                    $this->_getRequest()->addParameter('user', $user);

                                    return $user;
                            }
                        }
                }
            }
        }

        return null;
    }

}