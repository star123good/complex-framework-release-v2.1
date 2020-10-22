<?php

    if ( ! defined('CORRECT_PATH')) exit();

?>
<div class="container-fluid h-100">
    <div class="row justify-content-center align-items-center h-100">
        <div class="col col-sm-6 col-md-6 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Sign Up</h3>
                </div>

                <div class="card-body">

                    <?php 
                        if ($view->getData('error_msg') != "") { 
                            echo '<div class="alert alert-danger alert-dismissible fade show error-msg">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>'
                                . $view->getData('error_msg') . '</div>';
                        }
                    ?>

                    <form action="<?php echo $view->getData('WEB_PATH') ?>signup" method="POST" class="form">
                        <div class="form-group col-12">
                            <div class="row">
                                <label class="col-4 col-form-label" for="email">Email:</label>
                                <div class="input-group col-8">
                                    <input type="text" class="form-control" placeholder="Enter email" name="email" id="email" required>
                                    <div class="invalid-feedback">Please fill out this field.</div>
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-12">
                            <div class="row">
                                <label class="col-4 col-form-label" for="password">Password:</label>
                                <div class="input-group col-8">
                                    <input type="password" class="form-control" id="password" placeholder="Enter password" name="password" required>
                                    <div class="invalid-feedback">Please fill out this field.</div>
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-12">
                            <div class="row">
                                <label class="col-4 col-form-label" for="re-password">Re-Password:</label>
                                <div class="input-group col-8">
                                    <input type="password" class="form-control" id="re-password" placeholder="Confirm password" name="re-password" required>
                                    <div class="invalid-feedback">Please fill out this field.</div>
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-unlock"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-12 text-right">
                            <button type="submit" class="btn btn-primary">Register</button>
                        </div>
                    </form>

                    <div class="col-12 text-center">
                        Do you have already an account? <a href="<?php echo $view->getData('WEB_PATH') ?>login">Log In</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>