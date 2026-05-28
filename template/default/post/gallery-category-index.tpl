<h1><?php echo $post->title; ?></h1>
<h2><?php echo ucfirst(implode(" ",$filter_title)); ?></h2>
<?php echo $post->content; ?>

<div class="row">
    <div class="coltable float post-wrap col3">
        <?php foreach($index_loop as $item) { ?>
           <?php 
           //Get the first gallery that is in this category
			$gallery_meta = $zulu->table_data('post_meta pm', 0, ['join'=>'post ON pm.identifier=post.id','where'=>["field='gallery_category'","value='".$item['_data']['id']."'","status='published'","type='gallery'"],'sort'=>'sort ASC','first'=>true]);

         	$gallery_item = [];
         	$image_data = [];
          	if($gallery_meta['identifier'] > 0){
          		$gallery_item = $class_post->post_data(['id'=>$gallery_meta['identifier']]);
           		$image_data = $class_post->post_image($gallery_item['id'],['base'=>$class_post->config->file_rel]);
          	}

            ?>
            <div class="col">
                <div class="post-item">
					<a href="<?php echo $item['url']; ?>">
                	<div class="post-image">
                         <?php if($image_data['main']!=NULL) {
                            echo "<img src=\"".$zulu->thumb(FE_crm.$image_data['file'],"w=600&h=400&zc=1&bg=FFFFFF")."\" alt=\"image of ".$item['name']."\" class=\"feature-image\" />";
                        } else {
                            echo "<img src=\"".$zulu->thumb(FE_crm.$class_post->config->placeholder,"w=600&h=400&zc=1&bg=FFFFFF")."\" alt=\"placeholder image of ".$item['name']."\" class=\"feature-image\" />";
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