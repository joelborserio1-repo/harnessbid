<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file', 'reset');
include dirname(__FILE__)."/../includes/loader.php";
include FE_abs."template/head.php";

?>

<div class="frame">
    <div class="container-fluid p-0">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-md-9 col-12">

                <form method="post" action="">

                    <div class="form-block single form_table">
                        <div class="field">
                            <h1 class="h2">Reset your password</h1>
                            <?php echo $zulu->notification(); ?>
                        </div>
                        <div class="field">
                            <label>New Password</label>
                            <?= $form_edit->input_html('password', 'password'); ?>
                        </div>
                        <div class="field">
                            <label>Confirm New Password</label>
                            <?= $form_edit->input_html('password', 'password_confirm'); ?>
                        </div>
                        <div class="field submit">
                            <?= $form_edit->input_html('submit', 'submit', "Reset password", ['class'=>['bt-block button btn-variant-1']]); ?>
                            <?= $form_edit->input_html('hidden', 'action', "reset"); ?>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php

include FE_abs."template/foot.php";

?>
