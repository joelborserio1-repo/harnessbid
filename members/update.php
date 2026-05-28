<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','update');
define('AUTHORISE',1);
define('AUTHORISE_no_verify',1);
include dirname(__FILE__)."/../includes/loader.php";
include FE_abs."template/head.php";

?>

<div class="pb-section pb-section-row-1 section-variant-1 section-pad-1 page-title">
  <div class="frame frame-master">
    <div class="pb-container container-fluid">
      <div class="pb-row row pb-row-column-2 align-items-center">
        <div class="pb-column col-sm-8">
          <div class="pb-block pb-block-type-text pb-block-id-129">
            <div class="pb-block-content">
              <h1>Update Account</h1>
            </div>
          </div>
        </div>
        <div class="pb-column col-sm-4">
          <div class="pb-block pb-block-type-text pb-block-id-130 label">
            <div class="pb-block-content">
              <p><span class="fas fa-check"></span> Home of <em>zero </em><strong>Commissions</strong></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="pb-section section-pad-5">
<div class="frame">
    <div class="member-page-header">
    <?php echo member_breadcrumb(); ?>

    <h1 class="h2">Accounts settings</h1>
  </div>
    <?php echo $zulu->notification(); ?>

    <div class="settings-form">
        <div class="sidebar"><span class="fas fa-gears"></span></div>
        <form method="post" name="update" id="update" action="">
            <div class="form-block style">
                <div class="field">
                    <label>First Name <em>*</em></label>
                    <?= $form_edit->input_html('input', 'name_first', $_POST['name_first']); ?>
                </div>
                <div class="field">
                    <label>Last Name <em>*</em></label>
                    <?= $form_edit->input_html('input', 'name_last', $_POST['name_last']); ?>
                </div>
                <div class="field">
                    <label>Email Address <em>*</em></label>
                    <?= $form_edit->input_html('email', 'email', $_POST['email']); ?>
                </div>
                <div class="field">
                    <label>Phone Number <em>*</em></label>
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
                    <!--<?= $form_edit->input_html('input', 'phone', $_POST['phone']); ?>-->
                </div>

                <!--<div class="field w100 location-flag-options">
                    <label>Where are you based? <em>*</em></label>
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
                </div>-->
                <div class="clearfix"></div>
                <div class="field">
                    <label>Select your Location <em>*</em></label>
                    <?= $form_edit->input_html('select', 'location_id', $_POST['location_id'], ['option'=>[''=>'']+$location_options, 'id'=>'location-select']); ?>
                </div>
                <div class="field" id="region-field"<?= ($_POST['location_id']==='0'?' hidden':null); ?>>
                    <label>Select your Region <em>*</em></label>
                    <?= $form_edit->input_html('select', 'region_id', $_POST['region_id'], ['option'=>['0'=>'']+$region_options, 'id'=>'region-select']); ?>
                </div>
                <div class="field" id="location-other-field"<?= ($_POST['location_id']!=='0'?' hidden':null); ?>>
                    <label>Enter your Location <em>*</em> <?= $form_edit->icon_help('Enter your country and optionally your state/city.'); ?></label>
                    <?= $form_edit->input_html('input', 'location_other', $_POST['location_other']); ?>
                </div>
                <div class="clearfix"></div>
                <div class="field">
                    <label>Timezone <em>*</em></label>
                    <?= $form_edit->input_html('select', 'timezone', $_POST['timezone'], ['option'=>[]+$form_edit->timeZoneOptionsForm()]); ?>
                </div>
            </div>

            <hr />
            <h4>Change your password</h4>
            <div class="form-block style">
                <div class="field">
                    <label>New Password</label>
                    <?= $form_edit->input_html('password', 'password', ''); ?>
                </div>
                <div class="field">
                    <label>Confirm New Password</label>
                    <?= $form_edit->input_html('password', 'password', ''); ?>
                </div>
            </div>

            <?php /*<div class="form-block style">
                <div class="field">
                    <label>Address <span class="denote">*</span></label>
                    <?= $form_edit->input_html('input', 'ship_address', $_POST['ship_address']); ?>
                </div>
                <div class="field">
                    <label>Suburb</label>
                    <?= $form_edit->input_html('input', 'ship_suburb', $_POST['ship_suburb']); ?>
                </div>
                <div class="field">
                    <label>City <span class="denote">*</span></label>
                    <?= $form_edit->input_html('input', 'ship_city', $_POST['ship_city']); ?>
                </div>
                <div class="field">
                    <label>Postcode</label>
                    <?= $form_edit->input_html('input', 'ship_post', $_POST['ship_post']); ?>
                </div>
                <div class="field">
                    <label>Country <?php echo ($class_setting->data['ws_shop_country_lock']!=NULL?$form_edit->icon_help("Only ".$class_setting->data['ws_shop_country_lock']." addresses are allowed."):NULL); ?> <span class="denote">*</span></label>
                    <?php if($class_setting->data['ws_shop_country_lock'] != NULL) { ?>
                        <p class="text-regular"><?php echo $class_setting->data['ws_shop_country_lock']; ?></p>
                        <?= $form_edit->input_html('hidden', 'ship_country', $class_setting->data['ws_shop_country_lock']); ?>
                    <?php } else { ?>
                        <?= $form_edit->input_html('select', 'ship_country', $_POST['ship_country'], ['option'=>$form_edit->country_option()]); ?>
                    <?php } ?>
                </div>
            </div>*/ ?>

            <div class="form-block style">
                <div class="field submit">
                    <?= $form_edit->input_html('submit', 'submit', 'Save Details'); ?>
                </div>
            </div>

        </form>
    </div>
</div>
</div>

<?php

include FE_abs."template/foot.php";

?>
