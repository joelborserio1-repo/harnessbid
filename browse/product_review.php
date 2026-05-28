<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','product_review');
define('AUTHORISE',1);
include dirname(__FILE__)."/../includes/loader.php";
include FE_abs."template/head.php";

?>

<div class="frame">

    <div class="product-browse-header">
        <?= $breadcrumb; ?>
        <h1><?php echo $prod_title; ?></h1>
        <p><a href="<?php echo $zulu->front_link($main_link); ?>" class="button"><i class="fas fa-chevron-left"></i> Back to product</a></p>
        <?php echo $zulu->notification(); ?>
    </div>

    <form method="post" action="">
        <div class="column w6 col-center review-write">

            <p>How many stars would you give this product?</p>
            <div class="review-stars">
                <?php for($i=1; $i<=ProductReview::$stars; $i++) { ?>
                <span class="review-star<?= ($_POST['rating']==$i?' selected':null); ?>" data-rating="<?php echo $i; ?>"><i class="fa<?php echo ($_POST['rating']<$i?'r':'s'); ?> fa-star"></i></span>
                <?php } ?>
                <?php echo $form_edit->input_html('hidden','rating',$_POST['rating']); ?>
            </div>

            <h3>Write a Review</h3>
            <div class="form-block style single">
                <div class="field">
                    <label>Review Title</label>
                    <?php echo $form_edit->input_html('input','title',$_POST['title']); ?>
                </div>
                <div class="field">
                    <label>Review</label>
                    <?php echo $form_edit->input_html('textarea','content',$_POST['content'],['custom'=>['rows'=>'6']]); ?>
                </div>
                <div class="field">
                    <?php echo $form_edit->input_html('submit','submit','Submit Review'); ?>
                    <?php echo $form_edit->input_html('hidden','action','submit'); ?>
                </div>
            </div>
        </div>
    </form>

</div>

<?php

include FE_abs."template/foot.php";

?>
