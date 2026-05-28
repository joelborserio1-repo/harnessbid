<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','membership_switch');
define('AUTHORISE',1);
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
              <h1>Change Subscription</h1>
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

<div class="frame">

    <?php echo member_breadcrumb(); ?>
	<?php echo $zulu->notification(); ?>

    <div class="row">
		<div class="col-md-8">
			<h2>Your Subscription</h2>
			<div class="current-plan">
				<h3 class="membership-heading">
                    <?php echo $renew_row['title']; ?>
                    <b>$<?php echo $renew_row['price']; ?> <small>/<?php echo ($renew_row['renew_interval']>1?$renew_row['renew_interval']." ":null).strtolower($class_renew->config->renew_scale[$renew_row['renew_scale']]); ?></small></b>
                </h3>

                <?php if($is_cancelled) { ?>
                <h5 class="">Your subscription will is cancelled and will end on <b><?php echo $zulu->dateTimezone($renew_row['renew_next'], 'jS F Y'); ?></b>.</h5>
                <p><a href='<?php echo $zulu->front_link(true, ['query'=>['action'=>'cancel_undo']]); ?>' class="button"><?= $zulu->icon('sync-alt'); ?> Reactivate</a></p>
                <?php } else { ?>
                <h5 class="">Your subscription will automatically renew on <b><?php echo $zulu->dateTimezone($renew_row['renew_next'], 'jS F Y'); ?></b> and you'll be charged <b>$<?php echo $renew_row['price']; ?></b>.</h5>
                <?php } ?>

			</div>

            <?php if(count($template_data) > 1) { ?>
            <hr>
			<h3>Change Subscription</h2>
			<?php foreach($template_data as $tr) { if($tr['id']==$renew_row['template_id']) continue; ?>
			<div class="membership-block">
				<h3 class="membership-heading"><?php echo stripslashes($tr['title']); ?></h3>
				<p><b>$<?php echo $tr['price']; ?> /<?php echo ($tr['renew_interval']>1?tr['renew_interval']." ":null).strtolower($class_renew->config->renew_scale[$tr['renew_scale']]); ?></b></p>
				<p><?php echo stripslashes($tr['description']); ?></p>
				<p><a href='<?php echo $zulu->front_link(true, ['query'=>['action'=>'change','sub'=>$tr['id']]]); ?>' class="button">Change Now</a></p>
			</div>
			<?php } ?>
            <?php } ?>

		</div>

        <?php if(!$is_cancelled) { ?>
        <div class="col-md-4">
            <div class="member-box member-cancel">
                <p><a href='#' class="button popup-overlay-trigger" data-popup='change-sub-popup'><?= $zulu->icon('ban'); ?> Cancel Subscription</a></p>
            </div>
        </div>
        <?php } ?>

	</div>
</div>

<div class="popup-overlay change-sub-popup" id="change-sub-popup">
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
