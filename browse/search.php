<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','post_search');
include dirname(__FILE__)."/../includes/loader.php";
include FE_abs."template/head.php";

?>

<div class="frame">

    <h1>Search Results</h1>
    <div class="coltable col2">
        <div class="col">
            <h2>(<?php echo $return['count_all']; ?>) Results for '<?php echo $search_string; ?>'.</h2>
        </div>
        <div class="col text-right">
            <h3></h3>
        </div>
    </div>

    <?php echo $zulu->notification(); ?>
    <hr>

    <?php foreach($search_results as $result){ ?>
    <div class="search-result">
        <div class="coltable col2 vmiddle">
            <div class="col w30 center">
                <div class="search-result-image">
                    <?php $image_data = $class_post->post_image($result['id']); ?>
                    <?php if($image_data['main']!=NULL) {
                    echo "<img src=\"".$zulu->thumb(FE_crm.$image_data['file'],"w=300&h=200&far=1")."\" alt=\"image of ".$result['title']."\" class=\"feature-image\" />";
                    } else {
                        echo "<img src=\"".$zulu->thumb(FE_crm.$class_post->config->placeholder,"w=300&h=200&far=1")."\" alt=\"placeholder image of ".$item['name']."\" class=\"feature-image\" />";
                    } ?>
                </div>
            </div>
            <div class="col w70">
                <h3><?php echo $result['title'] ?></h3>
                <p>
                    <?php
                    $content = strip_tags($result['content']);
                    if (strlen($content) >= 333) {
                        echo substr($content, 0, 333). " ... ";
                    }
                    else {
                        echo $content;
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>
    <?php } ?>

    <div align="center">
        <?php echo $pagination; ?>
    </div>
</div>

<?php

include FE_abs."template/foot.php";

?>
