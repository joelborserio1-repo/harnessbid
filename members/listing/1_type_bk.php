<?php

define('PAGE_file','new_listing_type');
define('AUTHORISE',1);
include dirname(__FILE__)."/../../includes/loader.php";
include FE_abs."template/head.php";
include "header.php";

?>

<div class="form-block single">
    <div class="field">
        <h1 class="h2">1. Listing Type</h1>
        <p>Before we get started, what type of listing would you like to run?</p>
    </div>

    <div class="field listing-radio-selector<?= ($_POST['listing_type']=='auction'?' selected':null); ?>">
        <?= $zulu->icon('gavel'); ?>
        <p class="heading">Live auction</p>
        <p>Choose a start price, reserve and end date. HarnessBid will run a bidding campaign automatically for you.<?= $listing_fee_label; ?></p>
        <?= $form_edit->input_html('radio', 'listing_type', 'auction', ['class'=>['hide'], 'checked'=>($_POST['listing_type']=='auction')]); ?>
    </div>

    <div class="field listing-radio-selector <?= ($_POST['listing_type']=='classified'?' selected':null); ?>">
        <?= $zulu->icon('ad'); ?>
        <p class="heading">Simple listing (Classified)</p>
        <p>This is just like running an ad in the newspaper. There is an asking price, and people send an enquiry to find out more.<?= $listing_fee_label; ?></p>
        <?= $form_edit->input_html('radio', 'listing_type', 'classified', ['class'=>['hide'], 'checked'=>($_POST['listing_type']=='classified')]); ?>
    </div>

    <?php if($has_membership) { ?>
    <div class="field text-center">
        <img src="<?php echo TP_rel; ?>/images/image-12.jpg" alt="image of horses" class="img-horse" />
    </div>
    <?php } else { ?>
      [NO MEMBERSHIP]
    <?php } ?>
</div>

<?php if(isset($_POST['listing_type']) && $_POST['listing_type']) { ?>
    <div class="form-block style">
        <div class="field submit">
            <?= $form_edit->input_html('submit', 'submitb', 'Continue to Listing Details '.$zulu->icon('arrow-right'), ['class'=>['bt-block', 'btn-next']]); ?>
        </div>
    </div>
<?php } ?>

<?php

include "footer.php";
include FE_abs."template/foot.php";

?>
