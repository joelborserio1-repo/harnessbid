<?php

define('PAGE_file','new_listing_summary');
define('AUTHORISE',1);
include dirname(__FILE__)."/../../includes/loader.php";
include FE_abs."template/head.php";
include "header.php";

?>

<div class="form-block single">
    <div class="field">
        <h1 class="h2"><?= ($new_listing?'4':'3'); ?>. Finalise &amp; Summary</h1>
    </div>

    <div class="field">
        <h4>Summary of your listing</h4>
    </div>

    <div class="field">
        <hr />
    </div>
    <div class="field">
        <div class="row">
            <div class="col-auto">
                <h5>Listing Type</h5>
            </div>
            <?php if($new_listing) { ?>
            <div class="col-auto">
                <a href="<?= $zulu->front_link(LINK_listing_new); ?>">(edit)</a>
            </div>
            <?php } ?>
        </div>
    </div>
    <div class="field">
        <h3><?= $listing_type_title; ?></h3>
    </div>

    <div class="field">
        <hr />
    </div>
    <div class="field">
        <div class="row">
            <div class="col-auto">
                <h5>Details</h5>
            </div>
            <div class="col-auto">
                <a href="<?= $zulu->front_link(LINK_listing_new_detail); ?>">(edit)</a>
            </div>
        </div>
    </div>
    <div class="field">
        <dl class="dl-horizontal dt-left">
            <dt>Name of Horse</dt>
            <dd><?= $_POST['name']; ?></dd>
            <dt>Sire</dt>
            <dd><?= $_POST['spec_sire']; ?></dd>
            <dt>Dam</dt>
            <dd><?= $_POST['spec_dam']; ?></dd>
            <dt>Sex</dt>
            <dd><?= $_POST['spec_sex']; ?></dd>
            <dt>Colour</dt>
            <dd><?= $_POST['spec_colour']; ?></dd>
            <dt>Age</dt>
            <dd><?= $_POST['spec_age']; ?></dd>
            <dt>Location</dt>
            <dd><?= $location_name; ?></dd>

            <?php if($listing_type == 'auction') { ?>
            <dt>Start Price</dt>
            <dd>$<?= number_format($_POST['price']); ?> <?= $currency_code; ?></dd>
            <dt>Reserve Price</dt>
            <dd>$<?= number_format($_POST['price_reserve']); ?> <?= $currency_code; ?></dd>
            <?php } else { ?>
            <dt>Price</dt>
            <?php if($_POST['is_poa']) { ?>
                <dd>Contact for pricing</dd>
            <?php } else { ?>
                <dd>$<?= number_format($_POST['price']); ?> <?= $currency_code; ?></dd>
            <?php } ?>
            <?php } ?>

            <dt>Listing End Date</dt>
            <dd><?= $zulu->dateTimezone($_POST['date_close'], 'd F Y'); ?></dd>
            <dt>Listing End Time</dt>
            <dd><?= $_POST['time_close']; ?></dd>
        </dl>
    </div>

    <div class="field">
        <hr />
    </div>
    <div class="field">
        <div class="row">
            <div class="col-auto">
                <h5>Photos &amp; Video</h5>
            </div>
            <div class="col-auto">
                <a href="<?= $zulu->front_link(LINK_listing_new_media); ?>">(edit)</a>
            </div>
        </div>
    </div>
    <div class="field">
        <?php if(count($_SESSION['LISTING']['images']) > 0) { ?>
        <div class="row">
            <?php foreach($_SESSION['LISTING']['images'] as $key=>$image) { ?>
            <div class="col-lg-2-4 col-sm-4 col-6">
                <div class="box photo-box">
                    <img src="<?= MAIN_rel.$upload_dir_images.$image; ?>" alt="photo <?= $key+1; ?>" />
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } else { ?>
        <p>You haven't provided any photos.</p>
        <?php } ?>
    </div>
    <?php if(isset($addons['add_video'])) { ?>
    <div class="field">
        <?php if($_POST['meta']['video_1'] || $_POST['meta']['video_2']) { ?>
        <p><?= $zulu->compile('<br />', [$_POST['meta']['video_1'], $_POST['meta']['video_2']]); ?></p>
        <?php } else { ?>
        <p>You haven't provided any videos.</p>
        <?php } ?>
    </div>
    <?php } ?>
    <div class="field">
        <?php if($_POST['meta']['pedigree_link']) { ?>
        <p>Pedigree Information: <?= $_POST['meta']['pedigree_link']; ?></p>
        <?php } elseif($_POST['meta']['pedigree_file']) { ?>
        <p>Pedigree File: <?= $_POST['meta']['pedigree_file']; ?></p>
        <?php } else { ?>
        <p>You haven't provided any pedigree information. We will use <span class="pedigree-provider-logo"><?= PEDIGREE_provider; ?></span>.</p>
        <?php } ?>
    </div>

    <?php if(!$is_edit) { ?>
    <div class="field">
        <hr />
    </div>
    <div class="field">
        <h4>Let’s get your listing live</h4>
    </div>
    <div class="field">
        <dl class="dl-horizontal dt-left dd-right">
            <dt>Listing fee</dt>
            <dd>
                <?php if(CLIENT_subscribed) { ?>
                    <s>$<?= number_format($listing_price_default, 2); ?></s>
                <?php } ?>
                $<?= number_format($listing_price, 2); ?>
            </dd>
            <?php foreach($addons as $key=>$addon) { ?>
            <dt>Add-on - <?= $addon['title']; ?></dt>
            <dd><?= $addon['price']; ?></dd>
            <?php } ?>
        </dl>
    </div>
    <div class="field text-right">
        <h4><?= $listing_currency_code; ?> $<?= number_format($total_price, 2); ?></h4>
    </div>

    <div class="field">
        <p>Please enter your credit card details below...<p>
        <?= $module_payment->form_payment(0, 'listing'); ?>
    </div>
    <?php } ?>

</div>

<div class="form-block style">
    <div class="field submit">
        <?= $form_edit->input_html('submit', 'submitb', $zulu->icon('check').' '.($is_edit?'Update':'Publish').' Listing', ['class'=>['bt-block', 'btn-next']]); ?>
    </div>
</div>

<div class="form-block style back">
    <div class="field submit">
        <a href="<?= $zulu->front_link($url_back); ?>" class="button btn-back"><?= $zulu->icon('arrow-left'); ?> Back to Photos &amp; Video</a>
    </div>
</div>

<?php

include "footer.php";
include FE_abs."template/foot.php";

?>
