<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','search_brand');
include dirname(__FILE__)."/../includes/loader.php";
include FE_abs."template/head.php";

?>

<div class="frame">

    <h1>Our Brands</h1>
    <?php echo $zulu->notification(); ?>

    <?php if($brand_view == 'list') { ?>

    <div class="brand-list-quick-links">
        <ul>
            <?php foreach($char_list as $char) { ?>
            <li>
                <a href='#' class="brand-list-quick-link" data-char="<?= $char; ?>"><?= $char; ?></a>
            </li>
            <?php } ?>
        </ul>
    </div>

    <div class="brand-list">
        <?php foreach($char_list as $char) { ?>
        <div class="brand-list-block" data-char="<?= $char; ?>">
            <hr>
            <h3><?= $char; ?></h3>
            <?= ProductBrand::charList($char); ?>
        </div>
        <?php } ?>
    </div>

    <?php } else { ?>

    <div class="coltable col5 padcol float brand-list">
        <?php foreach($brands as $brand) { ?>
        <div class="col">
            <?= $brand->htmlBlock(); ?>
        </div>
        <?php } ?>
    </div>

    <?php } ?>

</div>

<?php

include FE_abs."template/foot.php";

?>
