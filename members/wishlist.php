<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','wishlist');
define('AUTHORISE',1);
include dirname(__FILE__)."/../includes/loader.php";
include FE_abs."template/head.php";

?>

<div class="frame">

    <?php echo member_breadcrumb(); ?>

    <h1>Your Wish List</h1>
    <?php echo $zulu->notification(); ?>
    <p>Products you have added to your wish list will be listed below.</p>

    <hr>
    <h2>Number of products (<?php echo $wishlist_count; ?>)</h2>
    <form method="POST">
        <?= $form_edit->input_html('hidden', 'action', 'remove_item'); ?>
        <div class="wishlist-list">
            <?php echo $line_table; ?>
        </div>
    </form>
</div>

<?php

include FE_abs."template/foot.php";

?>
