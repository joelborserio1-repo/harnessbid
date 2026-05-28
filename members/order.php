<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','order');
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
              <h1>Billing Summary</h1>
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

    <h2>You have <?= ($sale_count==0||$sale_count==NULL?'no':$sale_count); ?> invoices in your account.</h2>
    </div>
    <?php echo $zulu->notification(); ?>

    <?php echo $line_table; ?>

    <div align="center"><?php echo $pagination; ?></div>

</div>
</div>

<?php

include FE_abs."template/foot.php";

?>
