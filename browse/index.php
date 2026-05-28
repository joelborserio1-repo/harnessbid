<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','search');
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
              <h1>Browse</h1>
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

<div class="wrapper-product">
  <?php echo $zulu->notification(); ?>
    <!--<div class="frame">

        <div class="row justify-content-between align-items-center">
            <div class="col-auto">
                <h2>Horses for sale <?= ($location_name?" in ".$location_name:null); ?></h2>
            </div>
            <div class="col-auto">

            </div>
        </div>
      </div>-->

  <div class="pb-section section-variant-3">
      <div class="frame">
        <form method="get" action="">
            <div class="listing-filter">
                <div class="listing-filter-nav row align-items-center">
                    <div class="filter-option filter-search col-auto">
                        <?= $form_edit->input_html('input', 'search', $_GET['search'], ['placeholder'=>'Search here...']); ?>
                        <?= $form_edit->input_html('submit', 'submitb', $zulu->icon('search')); ?>
                    </div>
                    <div class="filter-option filter-dropdown col-auto">
                        <a href="#" data-type="gait">Gait <?= $zulu->icon('chevron-down'); ?></a>
                    </div>
                    <div class="filter-option filter-dropdown col-auto">
                        <a href="#" data-type="breed">Breeding <?= $zulu->icon('chevron-down'); ?></a>
                    </div>
                    <div class="filter-option filter-dropdown col-auto">
                        <a href="#" data-type="sex">Sex <?= $zulu->icon('chevron-down'); ?></a>
                    </div>
                    <div class="filter-option filter-dropdown col-auto">
                        <a href="#" data-type="age">Age <?= $zulu->icon('chevron-down'); ?></a>
                    </div>
                    <div class="filter-option filter-dropdown col-auto">
                        <a href="#" data-type="price">Price <?= $zulu->icon('chevron-down'); ?></a>
                    </div>
                    <?php if(count($region_options) > 0) { ?>
                    <div class="filter-option filter-dropdown col-auto">
                        <a href="#" data-type="region">Region <?= $zulu->icon('chevron-down'); ?></a>
                    </div>
                    <?php } ?>
                    <?php if($hasFilter) { ?>
                    <div class="col-12">
                        <a href="<?= $zulu->front_link(true); ?>" class="button remove-filters mt-2">Clear filters</a>
                    </div>
                    <?php } ?>
                </div>
                <div class="listing-filter-dropdown" data-type="gait">
                    <ul>
                        <li class="<?= (!isset($_GET['search_gait'])||!$_GET['search_gait']?'selected':null); ?>"><a href="#">All</a></li>
                        <?php foreach(Products::$gait_options as $val) { ?>
                        <li class="<?= ($_GET['search_gait']==$val?'selected':null); ?>"><a href="#"><?= $val; ?></a></li>
                        <?php } ?>
                    </ul>
                    <?= $form_edit->input_html('hidden', 'search_gait', $_GET['search_gait']); ?>
                </div>
                <div class="listing-filter-dropdown" data-type="breed">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <label>Sire:</label>
                        </div>
                        <div class="col-auto">
                            <?= $form_edit->input_html('input', 'search_sire', $_GET['search_sire'], ['placeholder'=>'Sire name...']); ?>
                            <?= $form_edit->input_html('submit', 'submitb', $zulu->icon('search')); ?>
                        </div>
                        <div class="col-auto">
                            <label>Dam:</label>
                        </div>
                        <div class="col-auto">
                            <?= $form_edit->input_html('input', 'search_dam', $_GET['search_dam'], ['placeholder'=>'Dam name...']); ?>
                            <?= $form_edit->input_html('submit', 'submitb', $zulu->icon('search')); ?>
                        </div>
                    </div>
                </div>
                <div class="listing-filter-dropdown" data-type="sex">
                    <ul>
                        <li class="<?= (!isset($_GET['search_sex'])||!$_GET['search_sex']?'selected':null); ?>"><a href="#">All</a></li>
                        <?php foreach(Products::$sex_options as $val) { ?>
                        <li class="<?= ($_GET['search_sex']==$val?'selected':null); ?>"><a href="#"><?= $val; ?></a></li>
                        <?php } ?>
                        <li class="<?= ($_GET['search_sex']=='Male'?'selected':null); ?>"><a href="#">Male</a></li>
                        <li class="<?= ($_GET['search_sex']=='Female'?'selected':null); ?>"><a href="#">Female</a></li>
                    </ul>
                    <?= $form_edit->input_html('hidden', 'search_sex', $_GET['search_sex']); ?>
                </div>
                <div class="listing-filter-dropdown" data-type="age">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <label>Age:</label>
                        </div>
                        <div class="col-auto">
                            <?= $form_edit->input_html('select', 'search_age_select', $_GET['search_age_select'], ['option'=>[''=>'Choose an option...', '1'=>'Yearling', '2'=>'Two-year-old', '3'=>'Three-year-old', '+'=>'Aged']]); ?>
                        </div>
                        <div class="col-auto">
                            <?= $form_edit->input_html('number', 'search_age', $_GET['search_age'], ['custom'=>['min'=>'0'], 'placeholder'=>'Enter an age...']); ?>
                        </div>
                        <div class="col-auto">
                            <label>Range:</label>
                        </div>
                        <div class="col-12 col-md-4">
                            <div id="age-slider"></div>
                            <?= $form_edit->input_html('hidden', 'search_age_min', $_GET['search_age_min'], ['id'=>'age-min']); ?>
                            <?= $form_edit->input_html('hidden', 'search_age_max', $_GET['search_age_max'], ['id'=>'age-max']); ?>
                        </div>
                        <div class="col-auto ml-auto">
                            <?= $form_edit->input_html('submit', 'submitb', $zulu->icon('arrow-right')); ?>
                        </div>
                    </div>
                </div>
                <div class="listing-filter-dropdown" data-type="price">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <label>Price Range:</label>
                        </div>
                        <div class="col-auto">
                            <?= $form_edit->input_html('input', 'search_price_min', $_GET['search_price_min'], ['placeholder'=>'$ Min price...']); ?>
                            <?= $form_edit->input_html('submit', 'submitb', $zulu->icon('search')); ?>
                        </div>
                        <div class="col-auto">
                            <label>-</label>
                        </div>
                        <div class="col-auto">
                            <?= $form_edit->input_html('input', 'search_price_max', $_GET['search_price_max'], ['placeholder'=>'$ Max price...']); ?>
                            <?= $form_edit->input_html('submit', 'submitb', $zulu->icon('search')); ?>
                        </div>
                    </div>
                </div>
                <?php if(count($region_options) > 0) { ?>
                <div class="listing-filter-dropdown" data-type="region">
                    <ul>
                        <li class="<?= (!isset($_GET['search_region'])||!$_GET['search_region']?'selected':null); ?>"><a href="#">All</a></li>
                        <?php foreach($region_options as $val) { ?>
                        <li class="<?= ($_GET['search_region']==$val?'selected':null); ?>"><a href="#"><?= $val; ?></a></li>
                        <?php } ?>
                    </ul>
                    <?= $form_edit->input_html('hidden', 'search_region', $_GET['search_region']); ?>
                </div>
                <?php } ?>
            </div>
        </form>
        <div class="row no-gutters location-select">
            <div class="col-auto location-option">
                <a href="<?= $zulu->front_link(true, ['query'=>['location_set'=>'-1']]); ?>" title="All Locations"><?= $zulu->icon('globe-americas'); ?></a>
            </div>
            <?php foreach($locations as $location) { ?>
            <div class="col-auto location-option">
                <a href="<?= $zulu->front_link(true, ['query'=>['location_set'=>$location->id]]); ?>" title="<?= $location->name; ?>"><img src="<?= $location->image(); ?>" alt="<?= $location->name; ?>" /></a>
            </div>
            <?php } ?>
            <div class="col-auto location-option">
                <a href="<?= $zulu->front_link(true, ['query'=>['location_set'=>'0']]); ?>">Other</a>
            </div>
        </div>

      </div>
      <?php /*if($hasFilter) { ?>
      <div class="frame">
          <div class="row no-gutters">
              <div class="col-auto">
                  <a href="<?= $zulu->front_link(true); ?>">Remove filters</a>
              </div>
        </div>
      </div>
      <?php }*/ ?>
    </div>
      <div class="frame">

        <?php if($show_category_results && count($category_result_item) > 0) { ?>

        <ul class="product-box category-box category-image row<?= $class_website->config->shop_result_row_count; ?> ls-master">
            <?= implode("",$category_result_item); ?>
        </ul>

        <?php }	?>

        <?php if($show_product_results) { ?>

        <?php if($nrow_PRODF > 0 && trim($product_HTML) != NULL) { ?>
        <div class="box sort-box marg-bottom-5">
            <div class="coltable col3 vmiddle">
                <div class="col">
                    <?php if($nrow_PRODF > $limit) { ?>
                    <p>Found <b><?php echo $nrow_PRODF; ?></b> results, showing <b><?php echo $start_txt; ?></b> to <b><?php echo $startto_txt; ?></b></p>
                    <?php } else { ?>
                    <p>Found <b><?php echo $nrow_PRODF; ?></b> result<?= $zulu->s($nrow_PRODF); ?></p>
                    <?php } ?>
                </div>
                <div class="col">
                    <form name="filter" id="filter" method="post" action="">
                        <div class="form-block filter">
                            <div class="field">
                                <label>Sort:</label>
                                <select name="sort" id="sort">
                                    <?php foreach($BROWSE_sort_array as $key=>$val) { ?>
                                        <option value="<?php echo $key; ?>" <?php echo ($_SESSION['ProductSort']==$key?"selected":NULL); ?>><?php echo $val['label']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php }	?>

        <?php if($nrow_PRODF > 0) { ?>

        <?php echo $product_HTML; ?>

        <?php } else { ?>

        <?php if($has_search) { ?>
        <p  class="no-filter-items"><b>Sorry.</b>your search for <i>"<?= stripslashes($search); ?>"</i> did not match any items.</p>
        <?php } else { ?>
        <p class="no-filter-items"><b>Sorry.</b>no items are available at this time.</p>
        <?php } ?>

        <?php } ?>

        <div align="center">
            <?php echo $pagination; ?>
        </div>

        <?php } ?>
    </div>
</div>

<?php

include FE_abs."template/foot.php";

?>
