<?php

    namespace Services;

    if ( ! defined('CORRECT_PATH')) exit();

/**********************************************************************************************
 *
 *      Validate Service
 *
 *      some validations
 *
**********************************************************************************************/


use Library\Service as Service;


/*
 *      Validate Service Class
 */
class ValidateService extends Service {
    
    /**
     *      validate name
     *      @param  string  $str
     *      @return bool
     */
    public static function validateName($str)
    {
        return (preg_match("/^[a-zA-Z ]*$/", $str));
    }

    /**
     *      validate email
     *      @param  string  $email
     *      @return bool
     */
    public static function validateEmail($email)
    {
        return (filter_var($email, FILTER_VALIDATE_EMAIL));
    }

    /**
     *      validate URL
     *      @param  string  $website
     *      @return bool
     */
    public static function validateUrl($website)
    {
        return (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $website));
    }

    /**
     *      validate password
     *      @param  string  $password
     *      @return bool
     */
    public static function validatePassword($password)
    {
        // Validate password strength
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number    = preg_match('@[0-9]@', $password);
        $specialChars = preg_match('@[^\w]@', $password);
        return ($uppercase && $lowercase && $number && $specialChars && strlen($password) >= 8);
    }

}