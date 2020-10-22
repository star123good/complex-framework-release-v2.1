<?php

    if ( ! defined('CORRECT_PATH')) exit();

?>
<?php if ($view->getData('flagTopImage')) { ?>
<div class="container-fluid bg-full-size" style="background-image: url(<?php echo $view->getData('WEB_PATH') ?>public/images/top-background.jpg)">
    <h1 class="centered" color="black"><?php echo $view->getData('SITE_TITLE') ?></h1>
</div>
<?php } ?>
<!-- Start of Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
    <div class="container">
        <a class="navbar-brand text-uppercase" href="<?php echo $view->getData('WEB_PATH') ?>"><?php echo $view->getData('SITE_TITLE') ?></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item dropdown active">
                    <a class="nav-link dropdown-toggle" href="#" id="navbar-post-dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Post
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbar-post-dropdown">
                        <a class="dropdown-item" href="<?php echo $view->getData('WEB_PATH') ?>post">List</a>
                        <a class="dropdown-item" href="#">New Post</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Link</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbar-blog-dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Blog
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbar-blog-dropdown">
                        <a class="dropdown-item" href="#">Action</a>
                        <a class="dropdown-item" href="#">Another action</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">Something else here</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbar-media-dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Media
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbar-media-dropdown">
                        <a class="dropdown-item" href="#">Video</a>
                        <a class="dropdown-item" href="#">Audio</a>
                    </div>
                </li>
            </ul>
            <form class="form-inline my-2 my-lg-0" action="<?php echo $view->getData('WEB_PATH') ?>search" method="GET">
                <div class="input-group mr-sm-2">
                    <input class="form-control py-2" type="search" name="key" placeholder="Search" aria-label="Search">
                    <span class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fa fa-search"></i>
                        </button>
                    </span>
                </div>
            </form>
            <ul class="navbar-nav ml-1">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $view->getData('WEB_PATH') ?>notification">
                        <i class="fa fa-bell"></i>
                        <span class="badge">3</span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbar-user-dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="<?php echo $view->getData('WEB_PATH') ?>public/images/avatars/user-avatar-placeholder.png" class="avatar" alt="user avatar">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbar-user-dropdown">
                        <a class="dropdown-item disabled" href="#"><small>test@email.com</small></a>
                        <a class="dropdown-item" href="<?php echo $view->getData('WEB_PATH') ?>profile">My Profile</a>
                        <a class="dropdown-item" href="<?php echo $view->getData('WEB_PATH') ?>admin">Admin</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?php echo $view->getData('WEB_PATH') ?>login">Log In</a>
                        <a class="dropdown-item" href="<?php echo $view->getData('WEB_PATH') ?>signup">Sign Up</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?php echo $view->getData('WEB_PATH') ?>logout">Log out</a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- End of Navbar -->