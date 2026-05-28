<?php

define('PAGE_file','new_listing_details');
define('AUTHORISE',1);
include dirname(__FILE__)."/../../includes/loader.php";
include FE_abs."template/head.php";
include "header.php";

?>

<div class="form-block style">
    <div class="field w100">
        <h1 class="h2"><?= ($new_listing?'2':'1'); ?>. Details</h1>
    </div>

    <div class="field w100 field-heading">
        <h4><?= ($is_marketplace_listing ? 'Listing title' : 'Name of horse'); ?></h4>
    </div>
    <div class="field w100">
        <div class="field-inset-wrapper">
            <label><?= ($is_marketplace_listing ? 'Enter a clear title for the item' : 'Enter the horses name'); ?></label>
            <?= $form_edit->input_html('input', 'name', $_POST['name'], ['placeholder'=>($is_marketplace_listing ? 'Example: Race sulky in good condition' : 'Enter the horses name')]); ?>
        </div>
    </div>

    <?php if($is_marketplace_listing) { ?>
    <div class="field w100 field-heading">
        <h4>Marketplace details</h4>
    </div>
    <div class="field w50">
        <div class="field-inset-wrapper">
            <label>Category</label>
            <?= $form_edit->input_html('select', 'meta[marketplace_category]', $_POST['meta']['marketplace_category'], ['option'=>[''=>'Select...']+Products::$marketplace_category_options]); ?>
        </div>
    </div>
    <div class="field w50">
        <div class="field-inset-wrapper">
            <label>Condition</label>
            <?= $form_edit->input_html('select', 'meta[marketplace_condition]', $_POST['meta']['marketplace_condition'], ['option'=>[''=>'Select...']+Products::$marketplace_condition_options]); ?>
        </div>
    </div>
    <div class="field w33">
        <div class="field-inset-wrapper">
            <label>Make</label>
            <?= $form_edit->input_html('input', 'meta[marketplace_make]', $_POST['meta']['marketplace_make'], ['placeholder'=>'Make']); ?>
        </div>
    </div>
    <div class="field w33">
        <div class="field-inset-wrapper">
            <label>Model</label>
            <?= $form_edit->input_html('input', 'meta[marketplace_model]', $_POST['meta']['marketplace_model'], ['placeholder'=>'Model']); ?>
        </div>
    </div>
    <div class="field w33">
        <div class="field-inset-wrapper">
            <label>Year</label>
            <?= $form_edit->input_html('number', 'meta[marketplace_year]', $_POST['meta']['marketplace_year'], ['placeholder'=>'Year', 'custom'=>['min'=>'1900','step'=>'1']]); ?>
        </div>
    </div>
    <div class="field w50">
        <div class="field-inset-wrapper">
            <label>Online payment</label>
            <?= $form_edit->input_html('select', 'meta[marketplace_payment_mode]', $_POST['meta']['marketplace_payment_mode'], ['option'=>Products::$marketplace_payment_mode_options]); ?>
        </div>
    </div>
    <div class="field w50">
        <div class="field-inset-wrapper">
            <label>Fulfilment</label>
            <?= $form_edit->input_html('select', 'meta[marketplace_fulfilment]', $_POST['meta']['marketplace_fulfilment'], ['option'=>[''=>'Select...']+Products::$marketplace_fulfilment_options]); ?>
        </div>
    </div>
    <div class="field w100">
        <div class="field-inset-wrapper">
            <label>Dimensions / sizing</label>
            <?= $form_edit->input_html('input', 'meta[marketplace_dimensions]', $_POST['meta']['marketplace_dimensions'], ['placeholder'=>'Dimensions, sizing or fit notes']); ?>
        </div>
    </div>
    <?php } else { ?>
    <div class="field w100 field-heading">
        <h4>Details</h4>
    </div>
    <div class="field w100">
        <label>What is the gait of the horse?</label>
        <div class="row radio-row">
            <?php foreach(Products::$gait_options as $gait) { ?>
            <div class="col-auto">
                <label class="inline"><?= $form_edit->input_html('radio', 'spec_gait', $gait, ['checked'=>($_POST['spec_gait']==$gait)]); ?> <?= $gait; ?></label>
            </div>
            <?php } ?>
        </div>
    </div>
    <div class="field w100">
        <div class="field-inset-wrapper">
            <label>Name of the sire?</label>
            <?= $form_edit->input_html('input', 'spec_sire', $_POST['spec_sire'], ['placeholder'=>'Name of the sire?']); ?>
        </div>
    </div>
    <div class="field w100">
        <div class="field-inset-wrapper">
            <label>Name of the dam?</label>
            <?= $form_edit->input_html('input', 'spec_dam', $_POST['spec_dam'], ['placeholder'=>'Name of the dam?']); ?>
        </div>
    </div>
    <div class="field w33">
        <div class="field-inset-wrapper">
            <label>Sex</label>
            <?= $form_edit->input_html('select', 'spec_sex', $_POST['spec_sex'], ['option'=>[''=>'Select...']+Products::$sex_options]); ?>
        </div>
    </div>
    <div class="field w33">
        <div class="field-inset-wrapper">
            <label>Color</label>
            <?= $form_edit->input_html('select', 'spec_colour', $_POST['spec_colour'], ['option'=>[''=>'Select...']+Products::$colour_options]); ?>
        </div>
    </div>
    <div class="field w33">
        <div class="field-inset-wrapper">
            <label>Age</label>
            <?= $form_edit->input_html('number', 'spec_age', $_POST['spec_age'], ['placeholder'=>'Age', 'custom'=>['min'=>'0']]); ?>
        </div>
    </div>
    <?php } ?>
    <div class="field w100">
        <div class="field-inset-wrapper">
            <label>Comments / Description</label>
            <?= $form_edit->input_html('textarea', 'description', $_POST['description'], ['placeholder'=>'Comments / Description...', 'rows'=>'4']); ?>
        </div>
    </div>

    <div class="field w100 field-heading">
        <h4>Location</h4>
    </div>
    <?php if(!$location_manual) { ?>
    <div class="field w100">
        <label>Your listing is located in <b id="location-text"><?= $region_name; ?>, <?= $location_name; ?></b> <a href="#" id="location-manual-trigger">(edit)</a></label>
    </div>
    <?php } ?>
    <div class="<?= (!$location_manual?'hide':null); ?>" id="location-manual">
        <div class="field w50">
            <div class="field-inset-wrapper">
                <label>Country</label>
                <?= $form_edit->input_html('select', 'location_id', $_POST['location_id'], ['option'=>[''=>'Select...']+$location_options, 'id'=>'location-select']); ?>
            </div>
        </div>
        <div class="field w50" id="region-field"<?= ($_POST['location_id']<=0?' hidden':null); ?>>
            <div class="field-inset-wrapper">
                <label>State / Region</label>
                <?= $form_edit->input_html('select', 'region_id', $_POST['region_id'], ['option'=>[''=>'Select...']+$region_options, 'id'=>'region-select']); ?>
            </div>
        </div>
        <div class="field w50" id="location-other-field"<?= ($_POST['location_id']>0&&$_POST['region_id']>0?' hidden':null); ?>>
            <div class="field-inset-wrapper">
                <label>Enter your Location</label>
                <?= $form_edit->input_html('input', 'meta[location_other]', $_POST['meta']['location_other'], ['placeholder'=>'Enter your Location', 'id'=>'location-other']); ?>
            </div>
        </div>
    </div>

    <div class="field w100 field-heading">
        <h4>Pricing</h4>
    </div>
    <div class="field w100" id="currency-label">
        <label>Prices will show as <b id="currency-text"><?= $currency->code; ?></b> <a href="#" id="currency-manual-trigger">(edit)</a></label>
    </div>
    <div class="hide" id="currency-manual">
        <div class="field w50">
            <div class="field-inset-wrapper">
                <label>Currency</label>
                <?= $form_edit->input_html('select', 'currency_id', $_POST['currency_id'], ['option'=>$currency_options, 'id'=>'currency-select']); ?>
            </div>
        </div>
    </div>
    <?php if($listing_type == 'auction') { ?>
    <div class="field w50">
        <div class="field-inset-wrapper">
            <label>Start Price</label>
            <?= $form_edit->input_html('number', 'price', $_POST['price'], ['placeholder'=>'Start Price', 'custom'=>['min'=>'0','step'=>'1'], 'id'=>'price-input']); ?>
        </div>
    </div>
    <div class="field w50">
        <div class="field-inset-wrapper">
            <label>Reserve Price</label>
            <?= $form_edit->input_html('number', 'price_reserve', $_POST['price_reserve'], ['placeholder'=>'Reserve Price', 'custom'=>['min'=>'0','step'=>'1']]); ?>
        </div>
    </div>
    <div class="field w100">
        <p class="grey">Bid increments start at <b>$<span id="bid-increment">100</span></b> per bid. <a href="#" id="bid-increment-manual-trigger">(edit)</a></p>
    </div>
    <div class="<?= (!$bid_increment_manual?'hide':null); ?>" id="bid-increment-manual">
        <div class="field w50">
            <div class="field-inset-wrapper">
                <label>Minimum Bid Increment</label>
                <?= $form_edit->input_html('number', 'meta[min_bid_increment]', $_POST['meta']['min_bid_increment'], ['id'=>'bid-increment-input', 'placeholder'=>'Minimum Bid Increment', 'custom'=>['min'=>'1','step'=>'1']]); ?>
            </div>
        </div>
    </div>
    <?php } else { ?>
    <div class="field w100">
        <label>Enter an Asking Price or choose to have buyers contact you for a price.</label>
    </div>
    <div class="field w50">
        <div class="field-inset-wrapper">
            <label>Price</label>
            <?= $form_edit->input_html('number', 'price', $_POST['price'], ['placeholder'=>'Price', 'custom'=>['min'=>'0','step'=>'1'], 'id'=>'input-price', 'disabled'=>($_POST['is_poa'])]); ?>
        </div>
    </div>
    <div class="field w50">
        <label>Ask buyers to contact you for a price?</label>
        <div class="form-switch">
            <?= $form_edit->input_html('checkbox', 'is_poa', '1', ['id'=>'is-poa', 'checked'=>$_POST['is_poa']]); ?>
            <label for="is-poa" class="label-success"></label>
        </div>
    </div>
    <?php } ?>

    <?php if(!$is_edit) { ?>
    <?php if($listing_type != 'classified') { ?>
    <div class="field w100 field-heading">
        <h4>Listing close</h4>
    </div>
    <div class="field w100">
        <label>End Date</label>
        <div class="row radio-row">
            <div class="col-auto">
                <label class="inline"><?= $form_edit->input_html('radio', 'date_close_fixed', '3 Days', ['checked'=>($_POST['date_close_fixed']=='3 Days')]); ?> 3 Days</label>
            </div>
            <div class="col-auto">
                <label class="inline"><?= $form_edit->input_html('radio', 'date_close_fixed', '5 Days', ['checked'=>($_POST['date_close_fixed']=='5 Days')]); ?> 5 Days</label>
            </div>
            <div class="col-auto">
                <label class="inline"><?= $form_edit->input_html('radio', 'date_close_fixed', '7 Days', ['checked'=>($_POST['date_close_fixed']=='7 Days')]); ?> 7 Days</label>
            </div>
            <div class="col-auto">
                <label class="inline"><?= $form_edit->input_html('radio', 'date_close_fixed', 'Custom', ['checked'=>($_POST['date_close_fixed']=='Custom')]); ?> Custom</label>
            </div>
        </div>
    </div>
    <div class="field w50 field-date" id="date-close-custom"<?= ($_POST['date_close_fixed']!='Custom'?' hidden':null); ?>>
        <div class="field-inset-wrapper">
            <label>End Date</label>
            <?= $form_edit->input_html('input', 'date_close_custom', $_POST['date_close_custom'], ['placeholder'=>'End Date', 'class'=>['datepicker'], 'custom'=>['readonly'=>'readonly']]); ?>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="field w50 field-time">
        <div class="field-inset-wrapper">
            <label>End Time</label>
            <?= $form_edit->input_html('input', 'time_close', $_POST['time_close'], ['placeholder'=>'End Time', 'class'=>['timepicker']]); ?>
        </div>
    </div>
    <div class="field w100">
        <p class="grey">Times are based on your timezone: <b><?= $_SESSION['TIMEZONE']; ?></b>.</p>
    </div>
    <?php } else { ?>
    <?= $form_edit->input_html('hidden', 'date_close_fixed', '45 Days'); ?>
    <?= $form_edit->input_html('hidden', 'time_close', $_POST['time_close'], ['class'=>['timepicker']]); ?>
    <?php } ?>
    <?php } ?>

    <?php if(!$is_edit && $addon_html) { ?>
    <div class="field w100 field-heading">
        <h4>Stand out from the crowd</h4>
    </div>
    <?= $addon_html; ?>
    <?php /*<div class="field w100">
        <p class="grey text-center">All prices are in <b>US Dollars</b>.</p>
    </div>*/ ?>
    <?php } ?>

</div>

<div class="form-block style">
    <div class="field submit">
        <?= $form_edit->input_html('submit', 'bsubmit', 'Continue to Photos &amp; Video '.$zulu->icon('arrow-right'), ['class'=>['bt-block', 'btn-next']]); ?>
        <?= $form_edit->input_html('hidden', 'action', 'submit'); ?>
        <?= $form_edit->input_html('hidden', 'bypass_usta', $_POST['bypass_usta'], ['id'=>'bypass-usta-input']); ?>
    </div>
</div>

<?php if($new_listing) { ?>
<div class="form-block style back">
    <div class="field submit">
        <a href="<?= $zulu->front_link($url_back); ?>" class="button btn-back"><?= $zulu->icon('arrow-left'); ?> Back to Listing Type</a>
    </div>
</div>
<?php } ?>

<?php

include "footer.php";
include FE_abs."template/foot.php";

?>
