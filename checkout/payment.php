<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','checkout_payment');
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
                    <h1 class="hide">Payment</h1>
                    <?php echo $zulu->notification(); ?>

                    <?= $sale->checkoutReviewHTML(); ?>

                    <form method="post" action="" id="payment-form">

                        <h3 class="checkout-heading">Billing Address</h3>
                        <?php if($_POST['delivery_method'] == 'ship') { ?>
                        <ul class='list-group billing-options'>
                            <li class='list-group-item'>
                                <div class='lgi-radio'><?= $form_edit->input_html('radio', 'billing_option', '0', ['checked'=>($_POST['billing_option']=='0'?true:false), 'id'=>'billing-option-0']); ?></div>
                                <div class='lgi-label'><label for="billing-option-0">Same as shipping address</label></div>
                            </li>
                            <li class='list-group-item'>
                                <div class='lgi-radio'><?= $form_edit->input_html('radio', 'billing_option', '1', ['checked'=>($_POST['billing_option']=='1'?true:false), 'id'=>'billing-option-1']); ?></div>
                                <div class='lgi-label'><label for="billing-option-1">Use a different address</label></div>
                            </li>
                            <li class='list-group-item item-detail'>
                        <?php } ?>
                                <div class="form-block style">
                                    <div class="field w50">
                                        <div class="field-inset-wrapper">
                                        <label>First Name <span class="denote">*</span></label>
                                        <?= $form_edit->input_html('input', 'meta[bill_name_first]', $_POST['bill_name_first'], ['placeholder'=>'First Name *']); ?>
                                        </div>
                                    </div>
                                    <div class="field w50">
                                        <div class="field-inset-wrapper">
                                        <label>Last Name <span class="denote">*</span></label>
                                        <?= $form_edit->input_html('input', 'meta[bill_name_last]', $_POST['bill_name_last'], ['placeholder'=>'Last Name *']); ?>
                                        </div>
                                    </div>
                                    <div class="field w100">
                                        <div class="field-inset-wrapper">
                                        <label>Company</label>
                                        <?= $form_edit->input_html('input', 'meta[bill_company]', $_POST['bill_company'], ['placeholder'=>'Company']); ?>
                                        </div>
                                    </div>
                                    <div class="field w100">
                                        <div class="field-inset-wrapper">
                                        <label>Address <span class="denote">*</span></label>
                                        <?= $form_edit->input_html('input', 'meta[bill_address]', $_POST['bill_address'], ['placeholder'=>'Address *']); ?>
                                        </div>
                                    </div>
                                    <div class="field w50">
                                        <div class="field-inset-wrapper">
                                        <label>Suburb</label>
                                        <?= $form_edit->input_html('input', 'meta[bill_suburb]', $_POST['bill_suburb'], ['placeholder'=>'Suburb']); ?>
                                        </div>
                                    </div>
                                    <div class="field w50">
                                        <div class="field-inset-wrapper">
                                        <label>City <span class="denote">*</span></label>
                                        <?= $form_edit->input_html('input', 'meta[bill_city]', $_POST['bill_city'], ['placeholder'=>'City *']); ?>
                                        </div>
                                    </div>
                                    <div class="field w50">
                                        <div class="field-inset-wrapper">
                                        <label>Postcode</label>
                                        <?= $form_edit->input_html('input', 'meta[bill_post]', $_POST['bill_post'], ['placeholder'=>'Postcode']); ?>
                                        </div>
                                    </div>
                                    <div class="field w50">
                                        <div class="field-inset-wrapper">
                                        <label>Country <?php echo ($class_setting->data['ws_shop_country_lock']!=NULL?$form_edit->icon_help("Only ".$class_setting->data['ws_shop_country_lock']." addresses are allowed."):NULL); ?> <span class="denote">*</span></label>
                                        <?php if($class_setting->data['ws_shop_country_lock'] != NULL) { ?>
                                        <p class="text-regular"><?php echo $class_setting->data['ws_shop_country_lock']; ?></p>
                                        <?= $form_edit->input_html('hidden', 'meta[bill_country]', $class_setting->data['ws_shop_country_lock']); ?>
                                        <?php } else { ?>
                                        <?= $form_edit->input_html('select', 'meta[bill_country]', ($_POST['bill_country']?$_POST['bill_country']:$class_setting->data['ws_addr_country']), ['option'=>$form_edit->country_option()]); ?>
                                        <?php } ?>
                                        </div>
                                    </div>
                                </div>
                        <?php if($_POST['delivery_method'] == 'ship') { ?>
                            </li>
                        </ul>
                        <?php } ?>

                        <h3 class="checkout-heading">Payment</h3>
                        <ul class='list-group payment-options'>
                            <?php echo $payment_modules['html']; ?>
                        </ul>

                        <div class="container-fluid p-0 checkout-footer">
                            <div class="row no-gutters align-items-center justify-content-center">
                                <div class="col-md order-md-2 col-12 order-md-2 mb-md-0 mb-4 col-submit">
                                    <?= $form_edit->input_html('submit', 'submitb', '<span class="far fa-check"></span>&nbsp; Complete order'); ?>
                                </div>
                                <div class="col-md order-md-1 col-12 order-md-1 col-back">
                                    <?php if($meta['delivery_method'] == 'ship') { ?>
                                    <a href="<?= $zulu->front_link(LINK_checkout_shipping); ?>" class="back-link">
                                        <i class='fas fa-chevron-left icon'></i> Return to shipping
                                    </a>
                                    <?php } else { ?>
                                    <a href="<?= $zulu->front_link(LINK_checkout); ?>" class="back-link">
                                        <i class='fas fa-chevron-left icon'></i> Return to details
                                    </a>
                                    <?php } ?>
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
