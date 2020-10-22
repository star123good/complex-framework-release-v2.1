<?php

namespace Models;

if ( ! defined('CORRECT_PATH')) exit();


use Library\Model as Model;


/**
 *      User Model Class
 */
class UserModel extends Model {

    protected static $table = "user";

    protected   $schema = array(
        array('name' => 'id', 'type' => 'int', 'default' => null),
        array('name' => 'email', 'type' => 'varchar', 'default' => null),
        array('name' => 'name', 'type' => 'varchar', 'default' => null),
        array('name' => 'password', 'type' => 'text', 'default' => null),
        array('name' => 'token', 'type' => 'varchar', 'default' => null),
        array('name' => 'created_at', 'type' => 'datetime', 'default' => 'CURRENT_TIMESTAMP'),
    );

}