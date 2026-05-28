<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','listings');
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
              <h1>My Listings</h1>
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

      <div class="listing-header">
      <?php echo member_breadcrumb(); ?>

      <div class="listing-page-title">
        <h2>Create a new listing, see your active or view your past ones.</h2>
        <p>Click on any of your watchlist listings to see more about it.</p>
      </div>

      <?php echo $zulu->notification(); ?>

      <div class="container-fluid p-0">
          <div class="row no-gutters justify-content-between align-items-center">
              <div class="col-auto">
                  <a href="<?= $zulu->front_link(LINK_listing_new); ?>" class="button"><?= $zulu->icon('plus'); ?> Create New Listing</a>
              </div>
              <div class="col-auto">
                  <div class="filter-select">
                      <label>Filter By:</label>
                      <?= $form_edit->input_html('select', 'filter', $_GET['filter'], ['option'=>['all'=>'Show All','active'=>'Active','sold'=>'Sold','unsold'=>'Unsold'], 'id'=>'input-filter']); ?>
                  </div>
              </div>
          </div>
      </div>
      </div>

      <div class="listing-results">
          <?php echo implode('', $product_results); ?>
      </div>

  </div>
</div>

<?php

include FE_abs."template/foot.php";

?>
