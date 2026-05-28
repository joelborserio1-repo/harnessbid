<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','verify');
define('AUTHORISE',1);
define('AUTHORISE_no_verify',1);
include dirname(__FILE__)."/../includes/loader.php";
include FE_abs."template/head.php";

?>

<div class="frame">

    <div class="container-fluid p-0">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-md-9 col-12">

                <form method="post" name="register" id="register" action="">
                    <div class="form-block single form_table">
                        <div class="field">
                            <h1 class="h2">Verify your account</h1>
                            <?php echo $zulu->notification(); ?>
                            <p>Enter the 6 digit code sent to your phone. <a href="<?= $zulu->front_link(true, ['query'=>['action'=>'resend']]); ?>">Resend code</a></p>
                        </div>

                        <div class="field">
                            <?= $form_edit->input_html('input', 'verify_code', $_POST['verify_code'], ['placeholder'=>'000000', 'custom'=>['autocomplete'=>'off']]); ?>
                        </div>

                        <div class="field submit">
                            <?= $form_edit->input_html('submit', 'submit', 'Verify', ['class'=>['bt-block button btn-variant-1']]); ?>
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
