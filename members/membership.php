<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','membership');
define('AUTHORISE',1);
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
              <h1>Premier Members Club</h1>
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

    <?php if($renew_row['id'] <= 0) { ?>
	<div class="container-fluid p-0 subscribe-container">
        <div class="row">
			<div class="col-md-6">
				<h2 class="h3">Get discounted listing fees, and special members-only features!</h2>
				<p>Get discounted listing fees, and special members-only features!</p>
				<ul class="fa-ul">
					<li><span class="fa-li"><?= $zulu->icon('check-circle', 'r'); ?></span> Sales history database</li>
					<li><span class="fa-li"><?= $zulu->icon('check-circle', 'r'); ?></span> Discounted listing fees (Save up to $75 USD per listing)</li>
					<li><span class="fa-li"><?= $zulu->icon('check-circle', 'r'); ?></span> U.S. Auction Sales History Database (coming soon)</li>
					<li><span class="fa-li"><?= $zulu->icon('check-circle', 'r'); ?></span> View Upcoming & Past Sales Results</li>
          <li><span class="fa-li"><?= $zulu->icon('check-circle', 'r'); ?></span> Listings featured on our socials and newsletter</li>
					<li><span class="fa-li"><?= $zulu->icon('check-circle', 'r'); ?></span> Free video uploads (up to 2)</li>
					<li><span class="fa-li"><?= $zulu->icon('check-circle', 'r'); ?></span> Additional photo allowance</li>
				</ul>
				<p>Pay and cancel anytime on our monthly plan, or save and pay a years subscription in advance.</p>

                <div class="row">
                    <?php foreach($template_data as $template_row) { ?>
                    <div class="col-auto">
                        <p class="subscription-price">$<?= $template_row['price']; ?> Per <?= ($template_row['renew_interval']>1?$template_row['renew_interval']." ":null).$class_renew->config->renew_scale[$template_row['renew_scale']].$zulu->s($template_row['renew_interval']); ?></p>
                    </div>
                    <?php } ?>
                </div>
			</div>
			<div class="col-md-6">
				<div class="box subscription-payment-box">
                    <form method="post" action="" id="payment-form">
                        <h3>Subscribe with us now...</h3>
                        <?php echo $zulu->notification(); ?>
                        <div class="row no-gutters justify-content-between align-items-center">
                            <div class="col-auto">
                                <p>Membership option</p>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <?php foreach($template_data as $template_row) { ?>
                                    <div class="col-auto">
                                        <label><?= $form_edit->input_html('radio', 'template_id', $template_row['id'], ['checked'=>false, 'class'=>['template-radio'], 'custom'=>['data-price'=>$template_row['price'], 'data-date'=>$template_row['next_renewal']]]); ?> <?= ($template_row['renew_interval']>1?$template_row['renew_interval']." ":null).$class_renew->config->renew_scale[$template_row['renew_scale']]."ly"; ?></label>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="row no-gutters justify-content-between align-items-center">
                            <div class="col-auto">
                                <p>Total payable per month</p>
                            </div>
                            <div class="col-auto">
                                <p><b>USD $<span id="sub-price"><?= $sub_price; ?></span></b></p>
                            </div>
                        </div>
                        <div class="row no-gutters justify-content-between align-items-center">
                            <div class="col-auto">
                                <p>Next renewal date</p>
                            </div>
                            <div class="col-auto">
                                <p><span id="sub-date"><?= $sub_date; ?></span></p>
                            </div>
                        </div>
                        <div class="row no-gutters justify-content-between align-items-center">
                            <div class="col-auto">
                                <p>Please enter your credit card details below...</p>
                            </div>
                            <div class="col-auto">
                                <p><?= $zulu->icon('lock'); ?></p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <?= $module_payment->form_payment(0, 'renew'); ?>
                        </div>

                        <?= $form_edit->input_html('submit', 'submitb', 'Purchase Subscription', ['class'=>['bt-block','btn-variant-1','button']]); ?>
                        <?= $form_edit->input_html('hidden', 'action', 'subscribe'); ?>
                    </form>
				</div>
			</div>
		</div>
	</div>

    <?php } else { ?>
    <?php echo $zulu->notification(); ?>

    <div class="row">
        <div class="col">
            <h3>Current Subscription</h3>

            <div class="member-box current-plan">
                <h3 class="membership-heading"><?php echo stripslashes($renew_row['title']); ?> <b>$<?php echo $renew_row['price']; ?> <small>/<?php echo ($renew_row['renew_interval']>1?$renew_row['renew_interval']." ":null).strtolower($class_renew->config->renew_scale[$renew_row['renew_scale']]); ?></small></b></h3>

                <?php if($is_cancelled) { ?>
                <h5 class="">Your subscription is cancelled and will end on <b><?php echo $zulu->dateTimezone($renew_row['renew_next'], 'jS F Y'); ?></b>.</h5>
                <p><a href='<?php echo $zulu->front_link(true, ['query'=>['action'=>'cancel_undo']]); ?>' class="button btn-variant-4"><?= $zulu->icon('sync-alt'); ?> Reactivate</a></p>
                <?php } else { ?>
                <h5>Your subscription will automatically renew on <b><?php echo $zulu->dateTimezone($renew_row['renew_next'], 'jS F Y'); ?></b> and you will be charged <b>$<?php echo $renew_row['price']; ?></b>.</h5>
                <?php } ?>
            </div>

            <?php if(!$is_cancelled) { ?>
            <div class="payment-method">
                <h4>Payment Method</h4>
                <div class="row no-gutters justify-content-between align-items-center current-method">
                    <div class="col-auto">
                        <?php if($payment_method_datail['success']) { ?>
                        <?= $payment_method_datail['detail_html']; ?>
                        <?php } ?>
                    </div>
                    <div class="col-auto">
                        <a href='#' class="button" id="payment-update-trigger">Update</a>
                    </div>
                </div>

                <div class="change-method hide" id="payment-update-box">
                    <hr />
                    <form method="post" action="" id="payment-form">
                        <p>Please enter your credit card details below...<p>
                        <div class="credit-card-form mb-4">
                            <?= $module_payment->form_payment(0, 'renew'); ?>
                        </div>
                        <?= $form_edit->input_html('submit', 'submitb', 'Submit', ['class'=>['']]); ?>
                        <?= $form_edit->input_html('hidden', 'action', 'payment_update'); ?>
                    </form>
                </div>
            </div>
            <?php } ?>

        </div>

        <div class="col">
            <h3>Subscription Options</h3>
            <?php if(count($template_data) > 1) { ?>
            <?php foreach($template_data as $tr) { if($tr['id']==$renew_row['template_id']) continue; ?>
            <div class="membership-block">
                <h3 class="membership-heading"><?php echo stripslashes($tr['title']); ?></h3>
                <div class="row no-gutters justify-content-between align-items-center">
                    <div class="col-auto">
                        <p><b>$<?php echo $tr['price']; ?> /<?php echo ($tr['renew_interval']>1?tr['renew_interval']." ":null).strtolower($class_renew->config->renew_scale[$tr['renew_scale']]); ?></b></p>
                    </div>
                    <div class="col-auto">
                        <a href='<?php echo $zulu->front_link(true, ['query'=>['action'=>'change','sub'=>$tr['id']]]); ?>' class="button btn-variant-1 popup-overlay-trigger" data-popup='change-sub-popup'>Change</a>
                    </div>
                </div>
                <?php if ($tr['description']) { ?><p><?php echo stripslashes($tr['description']); ?></p><?php } ?>
            </div>
            <?php } ?>

            <?php if(!$is_cancelled) { ?>
            <div class="text-right">
                <a href='#' class="button popup-overlay-trigger" data-popup='cancel-sub-popup'><?= $zulu->icon('ban'); ?> Cancel Subscription</a>
            </div>
            <?php } ?>
            <?php } ?>
        </div>

    </div>

    <?php } ?>

</div>
</div>

<div class="popup-overlay change-sub-popup" id="change-sub-popup">
	<a href='#' class="popup-close"><?= $zulu->icon('times'); ?></a>
	<div class="text-center">
        <h2>Change Subscription</h2>
        <p>Are you sure you want to change your subscription? You will be charged the new amount on your next renewal.</p>
        <p><a href="#" class="button change-sub-btn">Confirm</a></p>
    </div>
</div>
<div class="popup-overlay cancel-sub-popup" id="cancel-sub-popup">
	<a href='#' class="popup-close"><?= $zulu->icon('times'); ?></a>
	<div class="text-center">
        <h2>Cancel <?php echo stripslashes($renew_row['title']); ?></h2>
        <p>Are you sure you want to cancel your subscription? You will still have access to member features until your membership expires.</p>
        <p><a href="<?= $zulu->front_link(LINK_account_membership_cancel); ?>" class="button">Confirm</a></p>
    </div>
</div>

<?php

include FE_abs."template/foot.php";

?>
