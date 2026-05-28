<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','member');
define('AUTHORISE',1);
define('AUTHORISE_no_verify',1);
include dirname(__FILE__)."/../includes/loader.php";
include FE_abs."template/head.php";

?>

<div class="frame">

    <?php echo $zulu->notification(); ?>

    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-lg-5 col-md-5 col-12">

                <h1 class="h2"><?php echo $greet; ?>, <?php echo $_SESSION['user']['name_first']; ?></h1>

                <h3>Your subscription status is:<br /><b><?= (!CLIENT_subscribed?'Free':'Premium'); ?> Membership</b></h3>

				<div class="pb-block pb-block-type-text pb-block-id-138 premier-member">
                    <div class="pb-block-content">
                        <h3><?= (!CLIENT_subscribed?"Become a <strong>Premier Member...</strong>":"&nbsp;"); ?></h3>
                        <p>Get the most out of your online bidding experience with HarnessBid's Premier Membership.</p>
	                    <ul>
                            <li>Sales history database</li>
                            <li>Discounted listing fees</li>
                            <li>View past sales results from U.S auctions</li>
                            <li>Listings featured on our socials and newsletter</li>
                        </ul>

	                    <div class="wrap">
                            <?php if(!CLIENT_subscribed) { ?>
                            <a class="button" href="<?= $zulu->front_link(LINK_account_membership); ?>">Upgrade Now</a>
                            <h4><strong><em>$</em>9.99 per month</strong>or $99.99 per year</h4>
                            <?php } else { ?>
                            <a class="button" href="<?= $zulu->front_link(LINK_account_membership); ?>">View Subscription</a>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <?php if($recently_viewed) { ?>
                <div class="recently-viewed">
                    <h3>Your recently viewed:</h3>
                    <ul class="product-box row4">
                        <?php foreach($product_blocks as $product_block) { ?>
                        <li><?= $product_block; ?></li>
                        <?php } ?>
                    </ul>
                </div>
                <?php } ?>

            </div>

            <div class="col-lg-7 col-md-7 col-12">

                <h3><?= $zulu->icon('binoculars'); ?> Start Browsing</h3>
                <div class="row account-tiles">
                    <div class="col-lg-4 col-12">
                        <div class="account-tile">
                            <a href="<?= $zulu->front_link(LINK_browse, ['query'=>['search_gait'=>'Trotter']]); ?>">
                                <!--<?= $zulu->icon('t'); ?>-->
                                <img src="<?php echo TP_rel; ?>/images/icon-trotter-v2.png" />
                                <p>Trotter</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="account-tile">
                            <a href="<?= $zulu->front_link(LINK_browse, ['query'=>['search_gait'=>'Pacer']]); ?>">
                                <!--<?= $zulu->icon('p'); ?>-->
                                <img src="<?php echo TP_rel; ?>/images/icon-pacer-v2.png" />
                                <p>Pacer</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="account-tile">
                            <a href="<?= $zulu->front_link(LINK_browse, ['query'=>['search_gait'=>'Road Horse']]); ?>">
                                <?= $zulu->icon('horse'); ?>
                                <p>Road Horse</p>
                            </a>
                        </div>
                    </div>
                </div>

                <h3><?= $zulu->icon('user-circle'); ?> My Buying Options</h3>
                <div class="row account-tiles">
                    <div class="col-lg-4 col-12">
                        <div class="account-tile">
                            <a href="<?= $zulu->front_link(LINK_account_watchlist); ?>">
                                <?= $zulu->icon('check-circle', 'r'); ?>
                                <p>Watchlist</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="account-tile">
                            <a href="<?= $zulu->front_link(LINK_account_won); ?>">
                                <?= $zulu->icon('trophy', 'r'); ?>
                                <p>Purchases</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="account-tile">
                            <a href="<?= $zulu->front_link(LINK_account_lost); ?>">
                                <?= $zulu->icon('hourglass-end', 'r'); ?>
                                <p>Missed</p>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-auto mr-auto">
                        <h3><?= $zulu->icon('usd-circle'); ?> My Selling Options</h3>
                    </div>
                    <div class="col-auto">
                        <a href="<?= $zulu->front_link(LINK_listing_new); ?>" class="button"><?= $zulu->icon('plus'); ?> Create new Listing</a>
                    </div>
                </div>

                <div class="row account-tiles">
                    <div class="col-lg-4 col-12">
                        <div class="account-tile">
                            <a href="<?= $zulu->front_link(LINK_account_listings_active); ?>">
                                <?= $zulu->icon('bolt', 'r'); ?>
                                <p>Active Listings</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="account-tile">
                            <a href="<?= $zulu->front_link(LINK_account_listings_unsold); ?>">
                                <?= $zulu->icon('sack', 'r'); ?>
                                <p>Unsold</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="account-tile">
                            <a href="<?= $zulu->front_link(LINK_account_listings_sold); ?>">
                                <?= $zulu->icon('money-check-edit-alt', 'r'); ?>
                                <p>Sold</p>
                            </a>
                        </div>
                    </div>
                </div>

                <h3 class="premium"><?= $zulu->icon('sparkles'); ?> Premier Members</h3>
                <div class="row account-tiles premium">
                    <div class="col-lg-4 col-12">
                        <div class="account-tile<?=(!CLIENT_subscribed?' locked':null); ?>">
                            <a href="<?= $zulu->front_link(LINK_account_sales_record_database); ?>">
                                <?= $zulu->icon('lock', 'r'); ?>
                                <p>Sales Record Database</p>
                            </a>
                        </div>
                        <?php if(!CLIENT_subscribed) { ?>
                        <div class="locked-ribbon"><p>Unlock Now</p></div>
                        <?php } ?>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="account-tile">
                            <a href="<?= $zulu->front_link(LINK_account_membership); ?>">
                                <?php if(!CLIENT_subscribed) { ?>
                                <?= $zulu->icon('thumbs-up', 'r'); ?>
                                <p>Subscribe Now</p>
                                <?php } else { ?>
                                <?= $zulu->icon('calendar-clock', 'r'); ?>
                                <p>Subscription</p>
                                <?php } ?>
                            </a>
                        </div>
                    </div>
					<div class="col-lg-4 col-12">
					</div>
                </div>

                <h3><?= $zulu->icon('cog'); ?> My Settings</h3>
                <div class="row account-tiles">
                    <div class="col-lg-4 col-12">
                        <div class="account-tile">
                            <a href="<?= $zulu->front_link(LINK_account_update); ?>">
                                <?= $zulu->icon('cog', 'r'); ?>
                                <p>Update Account</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="account-tile">
                            <a href="<?= $zulu->front_link(LINK_account_orders); ?>">
                                <?= $zulu->icon('info-circle', 'r'); ?>
                                <p>Billing Statement</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="account-tile">
                            <a href="<?= $zulu->front_link(LINK_account_logout); ?>">
                                <?= $zulu->icon('sign-out-alt', 'r'); ?>
                                <p>Sign Out</p>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

<?php

include FE_abs."template/foot.php";

?>
