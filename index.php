<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','cms');
include dirname(__FILE__)."/includes/loader.php";
include FE_abs."template/head.php";

?>

<?php if((int)$post->_meta->frame_full_width<=0&&(int)$POST_config['config']['frontend_fullwidth']<=0) { ?>
<div class="frame">
<?php } ?>

    <?php

    //---
    // LOAD POST INTO BODY
    //---
    if($post->_data->frame_url!=NULL) {
        include($post->_data->frame_url);
    } else {
        if($class_post->vars->post_template_root!=NULL) {
            $post_template_root = ZSF_include_rel.$class_post->vars->post_template_root;
        } else {
            $post_template_root = FE_tpl."post/";
        }
        if($class_post->vars->index) {
            include($post_template_root.$class_post->vars->index_template.".tpl");
        } else {
            include($post_template_root.$post->_data->template.".tpl");
        }
    }

    ?>

<?php if((int)$post->_meta->frame_full_width<=0&&(int)$POST_config['config']['frontend_fullwidth']<=0) { ?>
</div>
<?php } ?>

<?php

include FE_abs."template/foot.php";

?>
