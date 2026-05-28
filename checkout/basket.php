<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','basket');
include dirname(__FILE__)."/../includes/loader.php";
include FE_abs."template/head.php";

?>

<div class="frame frame-basket">

    <?php if($cart_count <= 0) { ?>

    <h1>My Basket</h1>
    <?php echo $zulu->notification(); ?>
    <p>Your basket is currently empty.</p>
    <a href="<?php echo $zulu->front_link(LINK_browse); ?>" class="button">
		<span class="fas fa-chevron-right"></span> Start Shopping
	</a>

    <?php } else { ?>

    <div class="container-fluid">
        <div class="row">
            <main class="col-lg-8 main-content pr-lg-5">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1>My Basket</h1>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="<?= $zulu->front_link(LINK_browse); ?>" class="button continue">
							<span class="fas fa-chevron-right"></span> Continue Shopping
						</a>
                    </div>
                </div>

                <?php echo $zulu->notification(); ?>

                <?= $checkout_summary['products']; ?>

                <div class="text-right">
                    <a href="<?= $zulu->front_link(true,['query'=>['Action'=>'Empty']]); ?>" class="button empty">
						<span class="far fa-trash-alt"></span> Empty Basket
					</a>
                </div>
            </main>
            <aside class="col-lg-4 sidebar-content pl-lg-5">
                <div class="cart-summary">
                    <?= $checkout_summary['totals']; ?>

                    <?= $checkout_summary['module']; ?>

                    <h3>Ready to checkout?</h3>
                    <?= $checkout_summary['html_button']; ?>
                </div>
            </aside>
        </div>
    </div>

    <?php if($show_related) { ?>
    <hr>
    <h3>You may also like...</h3>
    <?php echo $related_product_html; ?>
    <?php } ?>

    <?php } ?>

</div>

<?php

include FE_abs."template/foot.php";

?>
