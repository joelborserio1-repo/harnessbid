<?php if($post->_meta->post_builder == 0 && !$zulu->template->body->hide_title) { ?>
<h1><?php echo $post->title; ?></h1>
<?php } ?>
<h2><?php echo ucfirst(implode(" ",$filter_title)); ?></h2>
<?php echo $post->content; ?>

<div class="row">
    <div class="coltable float post-wrap col3">
        <?php foreach($index_loop as $item) { ?>
            <div class="col">
                <div class="post-item">
					<a href="<?php echo $item['url']; ?>">
                	<div class="post-image">
                         <?php if($item['image']['main']!=NULL) {
                            echo "<img src=\"".$zulu->thumb(FE_crm.$item['image']['file'],"w=600&h=400&zc=1")."\" alt=\"image of ".$item['name']."\" class=\"feature-image\" />";
                        } else {
                            echo "<img src=\"".$zulu->thumb(FE_crm.$class_post->config->placeholder,"w=600&h=400&zc=1")."\" alt=\"placeholder image of ".$item['name']."\" class=\"feature-image\" />";
                        } ?>
                    </div>
                    <div class="post-body">
                    	<h3><?php echo $item['name']; ?></h3>
                    </div>
                    </a>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<div class="row text-center">
	<?php echo $pagination; ?>
</div>