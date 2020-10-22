<?php

    namespace Library;

    if ( ! defined('CORRECT_PATH')) exit();

/**********************************************************************************************
 *
 *      Session Handler
 *
 *      session start
 *      get & set session
 *
**********************************************************************************************/


use Library\Log as Log;


/*
 *      Session Class
 */
class Session {
    
    private $sessionState = false;                  // The state of the session
    
    private static $instance;                       // THE only instance of the class
   
   
    public function __construct() 
    {
    }

    /**
     *      get instance
     *      @return     Session
     *      @example    Session::getInstance()
     */
    public static function getInstance()
    {
        if ( !isset(self::$instance))
        {
            self::$instance = new self;
        }
       
        self::$instance->startSession();
       
        return self::$instance;
    }

    /**
     *      start session
     *      @return bool
     */
    public function startSession()
    {
        if ( !$this->sessionState )
        {
            $this->sessionState = session_start();
            Log::addLog("session started.");
        }
       
        return $this->sessionState;
    }
   
    /**
     *      set data
     *      @param  string  $key
     *      @param  mixed   $value
     *      @return void
     *      @example    $instance->key = 'value'
     */
    public function __set( $key , $value )
    {
        $_SESSION[$key] = $value;
    }

    /**
     *      get data
     *      @param  string  $key
     *      @return mixed
     *      @example    $instance->key
     */
    public function __get( $key )
    {
        if ( isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        return null;
    }
    
    /**
     *      isset
     *      @param  string  $key
     *      @return bool
     *      @example    isset($instance->key)
     */
    public function __isset( $key )
    {
        return isset($_SESSION[$key]);
    }

    /**
     *      unset
     *      @param  string  $key
     *      @return void
     */
    public function __unset( $key )
    {
        unset( $_SESSION[$key] );
    }

    /**
     *      destroy
     *      @return bool
     */
    public function destroy()
    {
        if ( $this->sessionState )
        {
            $this->sessionState = !session_destroy();
            unset( $_SESSION );
            Log::addLog("session destroyed.");
           
            return !$this->sessionState;
        }
       
        return false;
    }
}