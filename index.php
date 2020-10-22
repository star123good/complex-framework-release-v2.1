<?php

/**********************************************************************************************
 *
 *      index
 *
 *      define CORRECT_PATH
 *      include autoload
 *
**********************************************************************************************/

define('CORRECT_PATH', "COMPLEX_CORRECT_PATH");
define('ROOT_PATH', dirname(__FILE__));
define('CONFIG_FILEPATH', ROOT_PATH . "/backend/configures/Config.php");


// require config php
require_once(CONFIG_FILEPATH);

// requre vendor autoload php
require_once(PATH_VENDOR . 'autoload.php');

// require common help php
require_once(PATH_HELPS . 'common.help.php');

// require autoload php
require_once(PATH_BACKEND . 'Complex.php');
// register app
Complex::register();

// require router php
require_once(PATH_BACKEND . 'router.php');

// run app
Complex::run();