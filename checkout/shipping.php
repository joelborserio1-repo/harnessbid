<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','checkout_shipping');
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
                    <h1 class="hide">Shipping</h1>
                    <?php echo $zulu->notification(); ?>

                    <?= $sale->checkoutReviewHTML(); ?>

                    <form method="post" action="">

                        <h3 class="checkout-heading">Shipping method</h3>
                        <ul class='list-group shipping-options'>
                            <?php echo $shipping_modules['html']; ?>
                        </ul>

                        <div class="container-fluid p-0 checkout-footer">
                            <div class="row no-gutters align-items-center justify-content-center">
                                <div class="col-md order-md-2 col-12 order-md-2 mb-md-0 mb-4 col-submit">
                                    <?= $form_edit->input_html('submit', 'submit', 'Continue <span class="fas fa-chevron-right"></span>'); ?>
                                </div>
                                <div class="col-md order-md-1 col-12 order-md-1 col-back">
                                    <a href="<?= $zulu->front_link(LINK_checkout); ?>" class="back-link">
                                        <i class='fas fa-chevron-left icon'></i> Return to details
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
