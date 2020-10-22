<?php

namespace Controllers;

if ( ! defined('CORRECT_PATH')) exit();


use \Config;
use Library\Controller;
use Library\RestController;
use Library\Log;
use Models\UserModel;
use Models\PostModel;
use Models\PostCategoryModel;


/**
 *      Home Controller Class
 */
class HomeController extends Controller {

    /**
     *      GET /
     */
    public function index()
    {
        $this->_getView()
            // ->disableTheme()
            ->setData('body_class_name', 'home-body')
            ->setData('flagTopImage', true)
            ->setData('authorized', $this->_getSession()->authorized)
            ->setPage('home/index');
    }

}