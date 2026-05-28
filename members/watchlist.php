<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','watchlist');
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
              <h1>My Watchlist</h1>
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
    <div class="member-page-header">
    <?php echo member_breadcrumb(); ?>

    <div class="page-title">
    <h2>You have <b><?= $product_count; ?></b> horse<?= $zulu->s($product_count); ?> in your Watchlist.</h2>
    <?php if($product_count == 0) { ?>
    </div>
    <?php } ?>
    <?php if($product_count > 0) { ?>
    <p>Click on any of your watchlist listings to see more about it.</p>
    </div>
    <?php } else { ?>
    <p class="button-wrapper"><a href="<?= $zulu->front_link(LINK_browse); ?>" class="button">Browse listings</a></p>
    <?php } ?>

    <?php echo $zulu->notification(); ?>

    <?php if($product_count > 0) { ?>
    <div class="listing-results">
        <?php echo implode('', $product_results); ?>
    </div>
    <?php } ?>

  </div>

</div>
</div>

<?php

include FE_abs."template/foot.php";

?>
