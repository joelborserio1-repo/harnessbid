<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','forgot');
include dirname(__FILE__)."/../includes/loader.php";
include FE_abs."template/head.php";

?>

<div class="frame">

    <div class="container-fluid p-0">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-md-9 col-12">

                    <?php if(!$sent) { ?>

                    <div class="form-block single form_table">
                        <form method="post" action="">
                            <div class="field">
                                <h1 class="h2">Forgotten password</h1>
                                <?php echo $zulu->notification(); ?>
                                <p>Enter your email address below and we'll send you a link to reset your password.</p>
                            </div>

                            <div class="field">
                                <?= $form_edit->input_html('email', 'email', $_POST['email'], ['custom'=>['autofocus'=>'autofocus'], 'placeholder'=>'Email Address']); ?>
                            </div>
                            <div class="field submit">
                                <?= $form_edit->input_html('submit', 'submit', "Reset password", ['class'=>['bt-block']]); ?>
                                <?= $form_edit->input_html('hidden', 'action', "forgot"); ?>
                            </div>
                        </form>
                    </div>

                    <p>
                        <a href="<?= $zulu->front_link(LINK_account_login); ?>" class="button bt-block">
                            <i class="fas fa-chevron-left"></i> Sign In
                        </a>
                    </p>

                    <?php } else { ?>

                    <div class="form-block single form_table">
                        <div class="field">
                            <h1 class="h2">Password reset email sent</h1>
                            <?php echo $zulu->notification(); ?>
                        </div>

                        <div class="field">
                            <p>We've sent an email to <b><?= $sent_email; ?></b> with instructions on how to reset your password.</p>
                            <p>Remember to check your spam folder. If you cannot find the email after several minutes contact us for assistance.</p>
                        </div>
                    </div>

                    <?php } ?>
                </div>

            </div>
        </div>
    </div>

</div>

<?php

include FE_abs."template/foot.php";

?>
