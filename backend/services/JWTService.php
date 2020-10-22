<?php

    namespace Services;

    if ( ! defined('CORRECT_PATH')) exit();

/**********************************************************************************************
 *
 *      JWT Service
 *
 *      JSON Web Token
 *      encrypt functions - id=>encrypt_id, api_key=>encrypt_api_key, etc
 *      token generator function
 *
**********************************************************************************************/


use \Config;
use Library\Service as Service;


/*
 *      JWT Service Class
 */
class JWTService extends Service {
    
    /**
     *      static::base64UrlEncode function
     *      This way we can pass the string within URLs without any URL encoding.
     *      @param  string  $text
     *      @return string
     */
    public static function base64UrlEncode($text)
    {
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($text)
        );
    }

    /**
     *      generate JSON Web Token
     *      @param  mixed   $data
     *      @return string
     */
    public static function generateJWT($data)
    {
        // get the local secret key
        $secret = (Config::getConfig('SECRET_KEY')) ? Config::getConfig('SECRET_KEY') : "";

        // Create token header as a JSON string
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        // add expire date
        $data['exp_date'] = time() + TOKEN_EXP_DATE_LIMIT;
        // Create token payload as a JSON string
        $payload = json_encode($data);

        // Encode Header
        $base64UrlHeader = static::base64UrlEncode($header);
        // Encode Payload
        $base64UrlPayload = static::base64UrlEncode($payload);

        // Create Signature Hash
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        // Encode Signature to Base64Url String
        $base64UrlSignature = static::base64UrlEncode($signature);

        // Create JWT
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        return $jwt;
    }

    /**
     *      validate JSON Web Token
     *      @param  string  $jwt
     *      @return mixed
     */
    public static function validateJWT($jwt)
    {
        // get the local secret key
        $secret = (Config::getConfig('SECRET_KEY')) ? Config::getConfig('SECRET_KEY') : "";

        // split the token
        $tokenParts = explode('.', $jwt);
        if (count($tokenParts) != 3) return null;
        $header = base64_decode($tokenParts[0]);
        $payload = base64_decode($tokenParts[1]);
        $signatureProvided = $tokenParts[2];

        // build a signature based on the header and payload using the secret
        $base64UrlHeader = static::base64UrlEncode($header);
        $base64UrlPayload = static::base64UrlEncode($payload);
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = static::base64UrlEncode($signature);

        // verify it matches the signature provided in the token
        if ($base64UrlSignature === $signatureProvided) {
            try {
                $data = json_decode($payload, true);
                // verify expire date
                $data['exp_verify'] = ($data['exp_date'] > time()) ? true : false;
            }
            catch(Exception $e) {
                $data = $payload;
            }
            return $data;
        }
        else return null;
    }

    /**
     *      generate API Token
     *      @param  int     $len
     *      @return string
     */
    public static function generateAPIToken($len=32)
    {
        return bin2hex(random_bytes($len));
    }

}