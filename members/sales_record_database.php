<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','sales_record_database');
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
              <h1>Sales Record Database</h1>
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
      <h1 class="h2">Search database...</h1>
    </div>

</div>
<div class="pb-section section-variant-3">
  <div class="frame">
    <form method="get" action="">
        <div class="listing-filter">
            <div class="listing-filter-nav row align-items-center">
                <div class="filter-option filter-search col-auto">
                    <?= $form_edit->input_html('input', 'search', $_GET['search'], ['placeholder'=>'Horse Name']); ?>
                </div>
                <div class="filter-option filter-search col-auto">
                    <?= $form_edit->input_html('input', 'search_sire', $_GET['search_sire'], ['placeholder'=>'Sire Name']); ?>
                </div>
                <div class="filter-option filter-search col-auto">
                    <?= $form_edit->input_html('input', 'search_dam', $_GET['search_dam'], ['placeholder'=>'Dam Name']); ?>
                </div>
                <div class="filter-option filter-search col-auto">
                    <?= $form_edit->input_html('input', 'search_source', $_GET['search_source'], ['placeholder'=>'Auction House']); ?>
                </div>
                <div class="filter-option filter-dropdown col-auto">
                    <a href="#" data-type="date">Sale Date <?= $zulu->icon('chevron-down'); ?></a>
                </div>
                <div class="filter-option filter-submit col-auto ml-auto">
                    <?= $form_edit->input_html('submit', 'submitb', 'Search', ['placeholder'=>'Dam Name']); ?>
                </div>
            </div>
            <div class="listing-filter-dropdown" data-type="date">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <label>Date From:</label>
                    </div>
                    <div class="col-auto">
                        <?= $form_edit->input_html('input', 'search_date_from', $_GET['search_date_from'], ['placeholder'=>'DD/MM/YYYY', 'class'=>['date-picker']]); ?>
                    </div>
                    <div class="col-auto">
                        <label>Date To:</label>
                    </div>
                    <div class="col-auto">
                        <?= $form_edit->input_html('input', 'search_date_to', $_GET['search_date_to'], ['placeholder'=>'DD/MM/YYYY', 'class'=>['date-picker']]); ?>
                    </div>
                </div>
            </div>

        </div>

        <div class="container-fluid p-0">
            <div class="row no-gutters justify-content-between align-items-center">
                <div class="col-auto">
                    <p>There are <?= $total_count; ?> sale records in our database<?= ($search_term?" for '".$search_term."'":null); ?>.</p>
                </div>
                <div class="col-auto">
                    <div class="filter-select">
                        <label>Filter By:</label>
                        <?= $form_edit->input_html('select', 'search_filter', $_GET['search_filter'], ['option'=>['date_desc'=>'Latest First','date_asc'=>'Oldest First','price_asc'=>'Lowest Price','price_desc'=>'Highest Price','name_asc'=>'Horse Name']]); ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
  </div>
</div>
<div class="frame">
    <?php echo $zulu->notification(); ?>

    <?php echo $line_table; ?>

    <?php if ($pagination) { ?><div align="center"><?php echo $pagination; ?></div><?php } ?>
</div>

</div>
</div>

<?php

include FE_abs."template/foot.php";

?>
