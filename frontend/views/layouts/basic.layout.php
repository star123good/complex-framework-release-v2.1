<?php

    if ( ! defined('CORRECT_PATH')) exit();

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Complex PHP Framework">
        <meta name="author" content="STAR SSS">
        <meta name="keywords" content="Complex,PHP,Framework,Free,Simple,Web,CSS3,HTML,bootstrap4,jquery,popper">

        <link rel="icon" href="/favicon.ico">

        <title><?php echo $view->getData('title') ? $view->getData('title') : $view->getData('SITE_TITLE'); ?></title>

        <!-- Imported Styles -->
        <?php
            foreach($view->getCss() as $css){
                echo '<link rel="stylesheet" href="'.$css.'">';
            }
        ?>
    </head>

    <body class="<?php echo $view->getData('body_class_name'); ?>">
        
        <?php 
            include($view->getPage());
            if ($view->getData('log')) echo $view->getData('log');
        ?>

        <!-- Imported Scripts -->
        <?php
            foreach($view->getJs() as $js){
                echo '<script src="'.$js.'"></script>';
            }
        ?>
    </body>
</html>