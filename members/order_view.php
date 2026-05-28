<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','order_view');
include dirname(__FILE__)."/../includes/loader.php";
include FE_abs."template/head.php";

?>

<div class="pb-section pb-section-row-1 section-variant-1 section-pad-1">
  <div class="frame frame-master">
    <div class="pb-container container-fluid">
      <div class="pb-row row pb-row-column-2 align-items-center">
        <div class="pb-column col-sm-8">
          <div class="pb-block pb-block-type-text pb-block-id-129">
            <div class="pb-block-content">
              <h1>Invoice #<?php echo $sale_ref; ?></h1>
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

    <?php echo member_breadcrumb(); ?>

	<?php echo $zulu->notification(); ?>

    <?php if($sale_balance > 0 && $sale_data['status'] == '1' && $class_module->order_pay_block(['sale_total'=>$cart_summary['total']])) { ?>
    <div class="box">
        <h3><span class="far fa-credit-card"></span> Payment Options</h3>

        <?php if(!$manual_payment_form) { ?>
            <?php echo $class_module->order_pay_block(['sale_total'=>$cart_summary['total']]); ?>
        <?php } else { ?>
            <form method="post" name="payment" id="payment-form" action="">
                <?php echo $module_payment->form_payment($sale_id, 'order_view'); ?>
                <p><span class="text-mini grey">You agree to pay the due amount.</span></p>
                <a href="<?php echo $zulu->front_link(true,['self'=>true,'filter'=>['Action','Module']]); ?>" class="button btn-variant-5"><span class="fas fa-chevron-left"></span> Cancel Payment</a>
                <?= $form_edit->input_html('submit', 'submitb', '<span class="fas fa-check"></span> Confirm &amp; Make Payment'); ?>
                <?= $form_edit->input_html('hidden', 'action', 'manual_payment_form'); ?>
            </form>
        <?php } ?>
    </div>
    <?php } ?>
    <p class="button-wrapper">
    	<a href="<?php echo $class_sale->sale_url($sale_data['token'],true); ?>" class="button" target="_blank"><span class="fas fa-print"></span> Print Receipt</a>
    </p>

    <div class="row">
        <div class="coltable col3 vtop padcol">
            <div class="col">
                <div class="box order-box">
                    <h3>Order Status</h3>
                    <p>Placed <b><?php echo date("d/m/Y",$sale_data['date']); ?></b></p>
                    <p>Order Total <b><?php echo LOCALE_currency.$zulu->dollar($sale_total); ?> <?= $currency_code; ?></b><br>Order Balance <b><?php echo LOCALE_currency.$zulu->dollar($sale_balance); ?> <?= $currency_code; ?></b></p>
                    <?php echo ($coupon_code!=NULL?"<p>Discount Code <b>".$coupon_code."</b></p>":NULL); ?>
                    <p>Current Status <?php echo $sale_status; ?></p>

                </div>
            </div>
            <!--<div class="col">
                <div class="box">
                    <h3>Billing Information</h3>
                    <p><?php echo $bill; ?></p>
					<p class="grey"><i>Payment method: <?php echo $sale_payment; ?></i></p>
                </div>
            </div>-->
            <?php /*if($is_tangible) { ?>
            <?php if($sale_meta['delivery_method'] == 'ship') { ?>
            <div class="col">
                <div class="box">
                    <h3>Shipping Information</h3>
                    <p><?php echo $ship; ?></p>
                    <?php echo ($sale_shipping!=NULL?"<p class=\"grey\"><i>Ship method: ".$sale_shipping."</i></p>":NULL); ?>
                </div>
            </div>
            <?php } elseif($sale_meta['delivery_method'] == 'pickup') { ?>
            <div class="col">
                <div class="box">
                    <h3>Pickup Information</h3>
                    <p><?php echo $sale_meta['ship_method']; ?></p>
                    <p><?php echo $sale_meta['ship_method_note']; ?></p>
                </div>
            </div>
            <?php } ?>
            <?php }*/ ?>
        </div>
    </div>
    <div class="invoice-table">
    <h3>Your invoice</h3>
    <?php echo $line_table; ?>
    <p>&nbsp;</p>

    <div class="container-fluid p-0">
        <div class="row no-gutters justify-content-end">
            <div class="col-12 col-sm-6 col-lg-3">
                <dl class="dl-horizontal dt-left dd-right">
                    <?php if($sale_discount > 0) { ?>
                    <dt class='discount'>Discount</dt>
                    <dd class='discount'><?= LOCALE_currency.$zulu->dollar($sale_discount); ?></dd>
                    <?php } ?>
                    <?php if($is_tangible && $sale_meta['delivery_method']=='ship') { ?>
                    <dt class='shipping'>Shipping</dt>
                    <dd class='shipping'><?= LOCALE_currency.$zulu->dollar($sale_meta['ship_price']); ?></dd>
                    <?php } ?>
                    <dt class='total'><b>Total</b></dt>
                    <dd class='total'><b><?= $currency_code; ?> <?= LOCALE_currency.$zulu->dollar($cart_summary['total']); ?></b></dd>
                    <?php if(!$sale_data['tax_disable']) { ?>
                    <dt class='tax'><small><?= $taxttlinfo; ?></small></dt>
                    <dd class='tax'><small><?= LOCALE_currency.$zulu->dollar($cart_summary['tax']); ?></small></dd>
                    <?php } ?>
                </dl>
            </div>
        </div>
    </div>
  </div>

    <?php /*<hr />

    <div class="row">
        <div class="coltable col2 vtop padcol float">
            <div class="col">
                <h3>Order Updates</h3>
               	<?php echo $order_updates_html_table; ?>
            </div>
            <?php if($SUPPORT_enabled) { ?>
            <div class="col">
                <div class="coltable col2">
                    <div class="col"><h3>Support Tickets</h3></div>
                    <div class="col text-right"><a href="<?php echo FE_rel; ?>members/support_new.php?object=sale&object_id=<?php echo $sale_id; ?>&sale_ref=<?php echo $sale_ref; ?>"><button type="button"><span class="fas fa-plus-circle"></span> New Ticket</button></a></div>
                </div>
                <?php echo $support_tickets_html_table; ?>
            </div>
            <?php } ?>
        </div>
    </div>*/ ?>
</div>
</div>

<?php

include FE_abs."template/foot.php";

?>
