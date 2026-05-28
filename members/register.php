<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','register');
include dirname(__FILE__)."/../includes/loader.php";

define('HB_HANDOFF_SECRET', 'c4d7c9cf6d45f23813ee730a3022f53a');
$prefill_email = '';
if (!empty($_GET['ref']) && $_GET['ref'] === 'harnesslink' && !empty($_GET['e']) && !empty($_GET['sig'])) {
  $payload = $_GET['e'];
  $sig = $_GET['sig'];
  $expected = hash_hmac('sha256', $payload, HB_HANDOFF_SECRET);
  if (hash_equals($expected, $sig)) {
    $parts = explode('.', $payload);
    $expires = (int)($parts[1] ?? 0);
    if ($expires > time()) {
      $prefill_email = base64_decode($parts[0]);
      if(empty($_POST['email'])) {
        $_POST['email'] = $prefill_email;
      }
    }
  }
}

include FE_abs."template/head.php";

?>

<div class="frame">

    <div class="container-fluid p-0">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-md-9 col-12">

                <form method="post" name="register" id="register" action="">
                    <div class="form-block single form_table">
                        <div class="field">
                            <h1 class="h2">Create a free account</h1>
                            <?php echo $zulu->notification(); ?>
                            <p>Enter your details to create a <?= $class_setting->data['ws_name']; ?> account below, we will require a simple email verification to activate your account.</p>
                        </div>

                        <div class="field location-flag-options">
                            <label>Where are you based?</label>
                            <div class="row">
                                <?php foreach($locations as $location) { ?>
                                <div class="col-auto">
                                    <label>
                                        <?= $form_edit->input_html('radio', 'location_id', $location->id, ['checked'=>($_POST['location_id']==$location->id)]); ?>
                                        <img src="<?= $location->image(); ?>" alt="<?= $location->name; ?> Flag" title="<?= $location->name; ?>" />
                                    </label>
                                </div>
                                <?php } ?>
                                <div class="col-auto">
                                    <label>
                                        <?= $form_edit->input_html('radio', 'location_id', 'Other', ['checked'=>($_POST['location_id']=='0')]); ?> Other
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            <div class="">
                                <label>Enter your first name</label>
                                <?= $form_edit->input_html('input', 'name_first', $_POST['name_first'], ['placeholder'=>'']); ?>
                            </div>
                        </div>
                        <div class="field">
                            <div class="">
                                <label>Enter your last name</label>
                                <?= $form_edit->input_html('input', 'name_last', $_POST['name_last'], ['placeholder'=>'']); ?>
                            </div>
                        </div>
                        <div class="field">
                            <div class="">
                                <label>What is your email address?</label>
                                <?= $form_edit->input_html('email', 'email', $_POST['email'], ['placeholder'=>'']); ?>
                            </div>
                        </div>
                        <div class="field">
                            <label>What is your phone number?</label>
                            <div class="row">
                                <div class="col-lg-4 col-sm-12 field-inset-wrapper">
                                    <label>Area Code</label>
                                    <?= $form_edit->input_html('select', 'phone_ext', $_POST['phone_ext'], ['option'=>Clients::$phoneExtensions]); ?>
                                </div>
                                <div class="col field-inset-wrapper">
                                    <label>Phone Number</label>
                                    <?= $form_edit->input_html('input', 'phone_number', $_POST['phone_number'], ['placeholder'=>'Phone Number']); ?>
                                </div>
                            </div>
                            <!--<div class="">
                                <label>What is your phone number?</label>
                                <?= $form_edit->input_html('tel', 'phone', $_POST['phone'], ['placeholder'=>'']); ?>
                            </div>-->
                        </div>
                        <div class="field">
                            <div class="">
                                <label>Enter a password</label>
                                <?= $form_edit->input_html('password', 'password', $_POST['password'], ['placeholder'=>'']); ?>
                            </div>
                        </div>
                        <div class="field">
                            <div class="">
                                <label>Enter the password again</label>
                                <?= $form_edit->input_html('password', 'password_c', $_POST['password_c'], ['placeholder'=>'']); ?>
                            </div>
                        </div>
                        <div class="field">
                            <label><?= $form_edit->input_html('checkbox', 'terms', '1', ['checked'=>($_POST['terms']=='1')]); ?> I agree with the <a href="#" target="_blank">Terms of Use</a>.</label>
                        </div>

                        <div class="field submit">
                            <?= $form_edit->input_html('submit', 'submit', 'Complete Sign Up', ['class'=>['bt-block button btn-variant-1']]); ?>
                        </div>
                        <?php if($do_recaptcha) { ?>
                        <?= $form_edit->input_html('recaptcha', 'register'); ?>
                        <?php } ?>

                    </div>
                </form>

                <p>
                    <a href="<?= $zulu->front_link(LINK_account_login); ?>" class="button bt-block">
                        Already have an account? <u>Sign in</u>
                    </a>
                </p>

            </div>
        </div>
    </div>

</div>

<?php

include FE_abs."template/foot.php";

?>
