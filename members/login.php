<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','login');
include dirname(__FILE__)."/../includes/loader.php";
include FE_abs."template/head.php";

?>

<div class="frame">

    <div class="container-fluid p-0">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-md-9 col-12">

                <div class="form-block single form_table">
                    <form method="post" name="signin" id="signin" action="">
                        <div class="field">
                            <h1 class="h2">Sign in to your account</h1>
                            <?php echo $zulu->notification(); ?>
                            <p>Enter your email and password below to access your account.</p>
                        </div>
                        <div class="field">
                            <?= $form_edit->input_html('email', 'email', $_POST['email'], ['custom'=>$email_input_custom, 'placeholder'=>'Email Address']); ?>
                        </div>
                        <div class="field">
                            <?= $form_edit->input_html('password', 'password', null, ['custom'=>$password_input_custom, 'placeholder'=>'Password']); ?>
                        </div>
                        <div class="field submit">
                            <?= $form_edit->input_html('submit', 'submit', "Sign In", ['class'=>['button bt-block btn-variant-1']]); ?>
                            <?= $form_edit->input_html('hidden', 'action', "signin"); ?>
                        </div>
                    </form>
                </div>

                <div class="text-center">
                    <h4>More Options</h4>
                    <p>
                        <a href="<?= $zulu->front_link(LINK_account_register); ?>" class="button bt-block">
                            Don't have an account? <u>Register here</u>
                        </a>
                    </p>
                    <p>
                        <a href="<?= $zulu->front_link(LINK_account_forgot); ?>" class="btn-variant-2 button bt-block">
                            Forgot your password? <u>Reset here</u>
                        </a>
                    </p>
                </div>

            </div>
        </div>
    </div>

</div>

<?php

include FE_abs."template/foot.php";

?>
