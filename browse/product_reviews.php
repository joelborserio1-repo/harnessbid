<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','product_reviews');
include dirname(__FILE__)."/../includes/loader.php";
include FE_abs."template/head.php";

?>

<div class="frame">

    <div class="product-browse-header">
        <?= $breadcrumb; ?>
        <h1><?php echo $prod_title; ?> Reviews</h1>
        <p><a href="<?php echo $zulu->front_link($main_link); ?>" class="button"><i class="fas fa-chevron-left"></i> Back to product</a></p>
        <?php echo $zulu->notification(); ?>
    </div>

    <div class="product-reviews">
        <?php if($has_rating) { ?>
        <div class="coltable col2 padcol vtop">
            <div class="col w25">
                <?php echo $review_summary; ?>
                <?php if($has_reviews) { ?>
                <p><a href="<?php echo $zulu->front_link($review_link); ?>" class="button"><i class="fas fa-edit"></i> Write a Review</a></p>
                <?php } ?>
            </div>
            <div class="col w75">
                <?php if(!$has_reviews) { ?>
                <p>Nobody has reviewed this product yet. Be the first!</p>
                <p><a href="<?php echo $zulu->front_link($review_link); ?>" class="button"><i class="fas fa-edit"></i> Write a Review</a></p>
                <?php } else { ?>
                <div class="review-rows">
                    <?php foreach($reviews as $review) { echo $review->feHTML(); } ?>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php } else { ?>
        <p>Nobody has reviewed this product yet. Be the first!</p>
        <p><a href="<?php echo $zulu->front_link($review_link); ?>" class="button"><i class="fas fa-edit"></i> Write a Review</a></p>
        <?php } ?>
    </div>

</div>

<?php

include FE_abs."template/foot.php";

?>
