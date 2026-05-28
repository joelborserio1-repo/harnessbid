<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','checkout_index');
include dirname(__FILE__)."/../includes/loader.php";
include FE_abs."template/head.php";

?>

<div class="checkout-wrap">

    <div class="cart-header d-lg-none noselect">
        <div class="frame frame-checkout">
            <div class="container-fluid">
                <div class="row no-gutters align-items-center justify-content-between">
                    <div class="col-auto heading">
                        <span class="toggle-text toggle-show">Show summary <?= $zulu->icon('chevron-down', 'r'); ?></span>
                        <span class="toggle-text toggle-hide d-none">Hide summary <?= $zulu->icon('chevron-up', 'r'); ?></span>
                    </div>
                    <div class="col-auto total">
                        <?= LOCALE_currency.$checkout_summary['total']; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="frame frame-checkout">
        <div class="container-fluid p-0">
            <div class="row no-gutters">
                <aside class="col-lg-4 sidebar-content pl-xl-5 pr-xl-0 px-3 order-lg-2">
                    <div class="cart-summary d-none d-lg-block">
                        <?= $checkout_summary['html']; ?>
                    </div>
                </aside>

                <main class="col-lg-8 main-content pr-xl-5 pl-xl-0 px-3 order-lg-1">

                    <?= $sale->checkoutBreadcrumbHTML(); ?>
                    <h1 class="hide">Details</h1>
                    <?php echo $zulu->notification(); ?>

                    <form method="post" action="">

                        <h3 class="checkout-heading">Contact Information</h3>
                        <div class="form-block w50 style">
                            <div class="field">
                                <div class="field-inset-wrapper">
                                    <label>First Name <span class="denote">*</span></label>
                                    <?= $form_edit->input_html('input', 'name_first', $_POST['name_first'], ['placeholder'=>'First Name *']); ?>
                                </div>
                            </div>
                            <div class="field">
                                <div class="field-inset-wrapper">
                                    <label>Last Name <span class="denote">*</span></label>
                                    <?= $form_edit->input_html('input', 'name_last', $_POST['name_last'], ['placeholder'=>'Last Name *']); ?>
                                </div>
                            </div>
                            <div class="field">
                                <div class="field-inset-wrapper">
                                    <label>Email Address <span class="denote">*</span></label>
                                    <?= $form_edit->input_html('email', 'email', $_POST['email'], ['placeholder'=>'Email Address *']); ?>
                                </div>
                            </div>
                            <div class="field">
                                <div class="field-inset-wrapper">
                                    <label>Phone Number</label>
                                    <?= $form_edit->input_html('tel', 'meta[phone]', $_POST['phone'], ['placeholder'=>'Phone Number']); ?>
                                </div>
                            </div>
                        </div>

                        <?php if($is_tangible) { ?>

                        <?php if($delivery_method_selectable) { ?>
                        <h3 class="checkout-heading">Delivery Method</h3>
                        <ul class='list-group delivery-options'>
                            <li class='list-group-item'>
                                <div class='lgi-radio'><?= $form_edit->input_html('radio', 'delivery_method', 'ship', ['checked'=>($_POST['delivery_method']=='ship'?true:false), 'id'=>'delivery-method-ship']); ?></div>
                                <div class='lgi-label'><label for="delivery-method-ship"><i class='far fa-truck'></i> Ship</label></div>
                            </li>
                            <li class='list-group-item'>
                                <div class='lgi-radio'><?= $form_edit->input_html('radio', 'delivery_method', 'pickup', ['checked'=>($_POST['delivery_method']=='pickup'?true:false), 'id'=>'delivery-method-pickup']); ?></div>
                                <div class='lgi-label'><label for="delivery-method-pickup"><i class='far fa-store'></i> Pick up</label></div>
                            </li>
                        </ul>
                        <?php } ?>

                        <?php if($shipping_options['ship']) { ?>
                        <div class="delivery-option-block" data-type='ship'<?=($_POST['delivery_method']!='ship'?' hidden':null); ?>>
                            <h3 class="checkout-heading">Shipping Address</h3>
                            <div class="form-block w50 style">
                                <div class="field">
                                    <div class="field-inset-wrapper">
                                    <label>First Name <span class="denote">*</span></label>
                                    <?= $form_edit->input_html('input', 'meta[ship_name_first]', $_POST['ship_name_first'], ['placeholder'=>'First Name *']); ?>
                                    </div>
                                </div>
                                <div class="field">
                                    <div class="field-inset-wrapper">
                                    <label>Last Name <span class="denote">*</span></label>
                                    <?= $form_edit->input_html('input', 'meta[ship_name_last]', $_POST['ship_name_last'], ['placeholder'=>'Last Name *']); ?>
                                    </div>
                                </div>
                                <div class="field w100">
                                    <div class="field-inset-wrapper">
                                    <label>Company</label>
                                    <?= $form_edit->input_html('input', 'meta[ship_company]', $_POST['ship_company'], ['placeholder'=>'Company']); ?>
                                    </div>
                                </div>
                                <div class="field w100">
                                    <div class="field-inset-wrapper">
                                    <label>Address <span class="denote">*</span></label>
                                    <?= $form_edit->input_html('input', 'meta[ship_address]', $_POST['ship_address'], ['placeholder'=>'Address *']); ?>
                                    </div>
                                </div>
                                <div class="field">
                                    <div class="field-inset-wrapper">
                                    <label>Suburb</label>
                                    <?= $form_edit->input_html('input', 'meta[ship_suburb]', $_POST['ship_suburb'], ['placeholder'=>'Suburb']); ?>
                                    </div>
                                </div>
                                <div class="field">
                                    <div class="field-inset-wrapper">
                                    <label>City <span class="denote">*</span></label>
                                    <?= $form_edit->input_html('input', 'meta[ship_city]', $_POST['ship_city'], ['placeholder'=>'City *']); ?>
                                    </div>
                                </div>
                                <div class="field">
                                    <div class="field-inset-wrapper">
                                    <label>Postcode</label>
                                    <?= $form_edit->input_html('input', 'meta[ship_post]', $_POST['ship_post'], ['placeholder'=>'Postcode']); ?>
                                    </div>
                                </div>
                                <div class="field">
                                    <div class="field-inset-wrapper">
                                    <label>Country <?php echo ($class_setting->data['ws_shop_country_lock']!=NULL?$form_edit->icon_help("Only ".$class_setting->data['ws_shop_country_lock']." addresses are allowed."):NULL); ?> <span class="denote">*</span></label>
                                    <?php if($class_setting->data['ws_shop_country_lock'] != NULL) { ?>
                                    <p class="text-regular"><?php echo $class_setting->data['ws_shop_country_lock']; ?></p>
                                    <?= $form_edit->input_html('hidden', 'meta[ship_country]', $class_setting->data['ws_shop_country_lock']); ?>
                                    <?php } else { ?>
                                    <?= $form_edit->input_html('select', 'meta[ship_country]', ($_POST['ship_country']?$_POST['ship_country']:$class_setting->data['ws_addr_country']), ['option'=>$form_edit->country_option()]); ?>
                                    <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                        <?php if($shipping_options['pickup']) { ?>
                        <div class="delivery-option-block" data-type='pickup'<?=($_POST['delivery_method']!='pickup'?' hidden':null); ?>>
                            <h3 class="checkout-heading">Pickup Locations</h3>
                            <ul class='list-group pickup-options'>
                                <?= $pickup_option_html; ?>
                            </ul>
                        </div>
                        <?php } ?>
                        <?php } ?>

                        <h3 class="checkout-heading">Additional Information</h3>
                        <div class="form-block single style">
                            <div class="field">
                                <div class="field-inset-wrapper">
                                    <label>Order Notes</label>
                                    <?= $form_edit->input_html('textarea', 'meta[ship_notes]', str_replace("<br>",chr(13),$_POST['ship_notes']), ['custom'=>['rows'=>'5'], 'placeholder'=>'Order Notes']); ?>
                                </div>
                            </div>
                            <?php if($guest) { ?>
                            <div class="field">
                                <div class="field-inset-wrapper">
                                    <label>How did you hear about us?</label>
                                    <?= $form_edit->input_html('select', 'meta[referal]', $_POST['referal'], ['option'=>[''=>'Select...']+$form_edit->referrerOptionForm()]); ?>
                                </div>
                            </div>
                            <?php } ?>
                        </div>

                        <?php if(count($form_id_array)>0) { ?>
                        <hr/>
                        <?php foreach($form_id_array as $fid) {
                            echo $class_website->form_build('',$fid,['submit_hide'=>true,'form_wrapper_hide'=>true]);
                        } ?>
                        <?php } ?>

                        <div class="container-fluid p-0 checkout-footer">
                            <div class="row no-gutters align-items-center justify-content-center">
                                <div class="col-md order-md-2 col-12 order-md-2 mb-md-0 mb-4 col-submit">
                                    <?= $form_edit->input_html('submit', 'submit', 'Continue <span class="fas fa-chevron-right"></span>'); ?>
                                </div>
                                <div class="col-md order-md-1 col-12 order-md-1 col-back">
                                    <a href="<?= $zulu->front_link(LINK_basket); ?>" class="back-link">
                                        <i class='fas fa-chevron-left icon'></i> Return to basket
                                    </a>
                                </div>
                            </div>
                        </div>

                        <?= $form_edit->input_html('hidden', 'action', 'submit'); ?>
                    </form>
                </main>
                
            </div>
        </div>
    </div>

</div>

<?php

include FE_abs."template/foot.php";

?>
