<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','product');
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
              <h2 class="h1">Listing</h2>
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
<div class="frame frame-product">

    <?php echo $zulu->notification(); ?>

    <?php if(!$is_live) { ?>
    <div class="alert alert-warning text-center">
        <?= $zulu->icon('exclamation-triangle'); ?> This listing has closed.
    </div>
    <?php } ?>

    <?php if($owned_listing && $product->hide) { ?>
    <div class="alert alert-warning text-center">
        <?= $zulu->icon('exclamation-triangle'); ?> This listing is disabled and not publicly viewable. Please contact us for more information.
    </div>
    <?php } ?>

    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-md-8">
                <?php if($owned_listing || ($has_sold && $is_client_buyer)) { ?>
                <div class="box listing-detail-box">
                    <?php if($owned_listing) { ?>
                    <h2>Listing Detail</h2>
                    <?php } else { ?>
                    <h2>You Won the Auction!</h2>
                    <?php } ?>
                    <h3>(only you can see this section)</h3>

                    <?php if($owned_listing) { ?>
                    <div class="row justify-content-between">
                        <div class="col-auto">
                            <p>
                                <?php if($is_auction) { ?>
                                <?= $zulu->icon('pennant', 's'); ?> Bids: <?= $current_bids; ?><br />
                                <?= $zulu->icon('binoculars', 's'); ?> Bidders/Watchers: <?= $current_watchers; ?><br />
                                <?php } else { ?>
                                <?= $zulu->icon('binoculars', 's'); ?> Watchers: <?= $current_watchers; ?><br />
                                <?php } ?>
                                <?= $zulu->icon('eye', 's'); ?> Views: <?= $current_views; ?><br />
                            </p>
                        </div>
                        <div class="col-auto text-right">
                            <p>
                                <?php if($is_live) { ?>
                                <a href="<?= $zulu->front_link($product->feURLEdit()); ?>" class="<?= ($has_bids?'grey':''); ?>">Edit Listing</a><br />
                                <a href="<?= $zulu->front_link(LINK_listing_new); ?>">Create a New Listing</a><br />
                                <a href="#" class="<?= ($has_bids?'grey':'red'); ?> popup-overlay-trigger" data-popup="popup-withdraw">Withdraw Listing</a><br />

                                <?php } elseif($has_sold) { ?>
                                Sold for <?= $currency_code; ?> <b>$<?= number_format($product->price); ?></b>

                                <?php } else { ?>
                                <a href="<?= $zulu->front_link($product->feURLRelist()); ?>">Relist</a><br />
                                <?php } ?>
                            </p>
                        </div>
                    </div>
                    <hr />
                    <?php } ?>

                    <?php if($has_sold) { ?>
                    <p><?= $zulu->icon('id-card'); ?> Contact Details:</p>
                    <dl class="dl-horizontal dt-left dd-right">
                        <dt><?= $zulu->icon('user'); ?> Name of <?= $contact_label; ?></dt>
                        <dd><?= $contact_name; ?></dd>
                        <dt><?= $zulu->icon('envelope'); ?> Email Address of <?= $contact_label; ?></dt>
                        <dd><?= $contact_email; ?></dd>
                    </dl>
                    <?php if(!$owned_listing) { ?>
                    <p><b>NB: The seller will be in touch to finalise the Auction Details.</b></p>
                    <?php } else { ?>
                    <p><b>NB: You must contact the buyer to finalise the Auction Details.</b></p>
                    <?php } ?>
                    <?php } ?>

                    <dl class="dl-horizontal dt-left dd-right">
                        <?php if($is_auction) { ?>

                        <?php if(!$has_sold) { ?>
                        <dt>Start price</dt>
                        <dd>$<?= number_format($listing->price); ?></dd>
                        <dt>Reserve price</dt>
                        <dd>$<?= number_format($listing->price_reserve); ?></dd>
                        <dt>Current Bid</dt>
                        <dd><?= ($listing->price_bid>0?"$".number_format($listing->price_bid):"No bids"); ?></dd>
                        <?php } ?>

                        <?php } else { ?>
                        <dt>Asking price</dt>
                        <?php if($product->is_poa) { ?>
                            <dd>Contact for pricing</dd>
                        <?php } else { ?>
                            <dd>$<?= number_format($listing->price); ?></dd>
                        <?php } ?>
                        <?php } ?>
                    </dl>
                </div>
                <?php } ?>
                <div class="listing-details">
                <h1 class="h2"><?php echo $main_title; ?><span class="fw-light"> - <?= $product->spec_gait; ?></span></h1>
                <div class="row details">
                    <div class="col-auto">
                        <p><?= $zulu->icon('calendar', 'r'); ?> <?= $product->spec_age; ?> year<?= $zulu->s($product->spec_age); ?> of age</p>
                    </div>
                    <div class="col-auto">
                        <p><?= $zulu->icon('venus-mars', 'r'); ?> <?= $product->spec_sex; ?></p>
                    </div>
                    <div class="col-auto">
                        <p><?= $zulu->icon('venus', 'r'); ?> <?= stripslashes($product->spec_dam); ?></p>
                    </div>
                    <div class="col-auto">
                        <p><?= $zulu->icon('mars', 'r'); ?> <?= stripslashes($product->spec_sire); ?></p>
                    </div>
                    <div class="col-auto">
                        <p><?= $zulu->icon('tag', 'r'); ?> <?= $product->spec_colour; ?></p>
                    </div>
                    <div class="col-auto">
                        <p>
                            <?php if($product->locationFlag()) { ?>
                            <img src="<?= $product->locationFlag(); ?>" alt="<?= $product->locationText(); ?>" class="" />
                            <?php } else { ?>
                            <?= $zulu->icon('location-dot', 'r'); ?>
                            <?php } ?>
                            <?= $product->regionText(); ?>
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-7">
                      <div class="seller-details">
                        <p class="blue"><b>Seller Details:</b></p>
                        <p>
                            Name: <?= (trim($client->company)?$client->company:$client->nameFull()); ?>
                            <?php if($client->phone) { ?>
                            <br />Phone: <?= $client->phone; ?>
                            <?php } ?>
                        </p>
                      </div>
                        <div class="product-description">
            				<p><?php echo $description; ?></p>
            			</div>

                        <?php if($pedigree_file && $pedigree_file_img) { ?>
                        <div class="">
                            <img src="<?= $pedigree_file; ?>" alt="Pedigree image" class="responsive pedigree" />
                        </div>
                        <?php } ?>

                        <?php if($listing->add_video && ($prod_meta['video_1'] || $prod_meta['video_2'])) { ?>
                        <div class="row videos">
                            <?php if($prod_meta['video_1']) { ?>
                            <div class="col-md-6">
                                <iframe src="<?= $zulu->video_url($prod_meta['video_1']); ?>" allowfullscreen loading="lazy"></iframe>
                            </div>
                            <?php } ?>
                            <?php if($prod_meta['video_2']) { ?>
                            <div class="col-md-6">
                                <iframe src="<?= $zulu->video_url($prod_meta['video_2']); ?>" allowfullscreen loading="lazy"></iframe>
                            </div>
                            <?php } ?>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="col-md-5">

                        <?= $image_slider; ?>
                        <?php if(count($gallery) > 0) { ?>
                        <p class="center"><a href='#' id="open-images">View All Images (<?= count($gallery)+ 1; ?>)</a></p>
                        <?php } ?>
                        <!--<div class="product-image-container">
                            <a class="main-link" href="<?php echo $zulu->path_clean($image_main_big); ?>" rel="image" title="<?php echo $main_title; ?>">
                                <img src="<?php echo $image_main; ?>" alt="product image for <?php echo $main_title; ?>" class="main" data-default="<?php echo $image_main; ?>" id="product-image" />
                            </a>

                            <?php if(count($gallery)>0 || count($video_html)>0) { ?>
                            <div class="image-container gallery coltable col4">
                                <?php foreach($gallery as $gal_img) { ?>
                                <div class="col">
                                    <div class="image-wrap">
                                        <a href="<?php echo MAIN_rel.$gal_img; ?>" rel="image"><img alt="gallery image of <?php echo $main_title; ?>" src="<?php echo $zulu->thumb($gal_img,'w=496&h=360&far=1&bg=ffffff'); ?>" /></a>
                                     </div>
                                </div>
                                <?php } ?>
                                <?php foreach($video_html as $video) { ?>
                                <div class="col">
                                    <div class="image-wrap">
                                        <?php echo $video; ?>
                                     </div>
                                </div>
                                <?php } ?>
                            </div>
                            <?php } ?>
                        </div>-->
                    </div>

                </div>

                <div class="family-tree">
                    <?= $product->familyTreeHTML(); ?>
                </div>

                <p class="button-wrapper">
                    <?php if($pedigree_file) { ?>
                    <a href="<?= $pedigree_file; ?>" target="_blank" class="button">Pedigree</a>
                    <?php } ?>
                    <a href="<?= $bbURL; ?>" class="button" target="_blank"><span class="far fa-external-link"></span> View Pedigree</a>
                    <?php /*<a href="#" class="button" id="open-images">Images</a>*/?>
                    <p>
                        <small>Pedigrees provided by <a href="https://www.breedersbible.com/" target="_blank" rel="nofollow">BreedersBible.com</a></small>
                    </p>
                    <p>
                      <small>*Please note that Breeders Bible may take some time to load the pedigree information correctly.</small>
                    </p>
                </p>

              </div>
            </div>
            <div class="col-md-4">
                <?= $listing->priceBoxHTML(); ?>
            </div>
        </div>
    </div>

    <?php if($show_related) { ?>
    <hr>
    <h3>Related Products</h3>
    <?php echo $related_product_html; ?>
    <?php } ?>

    <?php if($featured_slider) { ?>
    <hr>
    <div class="row no-gutters justify-content-between align-items-center">
        <div class="col-auto">
            <h3 class="m-0">Featured Listings on HarnessBid...</h3>
        </div>
        <div class="col-auto">
            <a href="<?= $zulu->front_link(LINK_browse); ?>" class="button">View All Listings</a>
        </div>
    </div>
    <?= $featured_slider; ?>
    <?php } ?>

</div>
</div>

<?php if($owned_listing) { ?>
<div class="popup-overlay" id="popup-withdraw">
    <?php if($has_bids) { ?>
    <h3 class="text-center">You can't withdraw a listing that's been bid on.</h3>
    <div class="container-fluid p-0">
        <div class="row justify-content-center">
            <div class="col-auto">
                <a href="#" class="button popup-hide">OK</a>
            </div>
        </div>
    </div>
    <?php } else { ?>
    <h3 class="text-center">Are you sure you want to withdraw this listing?</h3>
    <div class="container-fluid p-0">
        <div class="row justify-content-center">
            <div class="col-auto">
                <a href="#" class="button popup-hide"><?= $zulu->icon('times', 'r'); ?> No</a>
            </div>
            <div class="col-auto offset-md-1">
                <a href="<?= $zulu->front_link($product->feURLWithdraw()); ?>" class="button"><?= $zulu->icon('check', 'r'); ?> Yes</a>
            </div>
        </div>
    </div>
    <?php } ?>
</div>
<?php } ?>

<?php if(!$owned_listing && $is_auction) { ?>
<div class="popup-overlay" id="popup-bid">
    <h3 class="text-center">Are you sure you want to place this bid?</h3>
    <div class="container-fluid p-0">
        <div class="row justify-content-center">
            <div class="col-auto">
                <a href="#" class="button popup-hide"><?= $zulu->icon('times', 'r'); ?> No</a>
            </div>
            <div class="col-auto offset-md-1">
                <a href="#" class="button" id="bid-confirm"><?= $zulu->icon('check', 'r'); ?> Yes</a>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<?php /*<div class="popup-overlay bb-popup" id="popup-bb">
    <a href="#" class="popup-close"><span class="fas fa-times"></span></a>
    <div class="holds-the-iframe" style="background:url(/template/profile/E8sCu5K9b6YdhkrQi2IsMOOg/images/loader.gif) center center no-repeat;">
        <iframe src="<?= $bbURL; ?>" width="100%" height="500px" loading="lazy"></iframe>
    </div>
</div>*/ ?>

<!-- Go to www.addthis.com/dashboard to customize your tools -->
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-64079f824e6a6e41"></script>

<?php

include FE_abs."template/foot.php";

?>
