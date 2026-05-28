<?php echo $breadcrumb; ?>

<h1><?php echo $post->title; ?></h1>
<?php echo $post->content; ?>
<?php echo $class_post->post_content($post_data,['display'=>'gallery']); ?>


<div class="row">
    <div class="coltable float post-wrap col3">
<?php

	$query_gallery_meta = $zulu->table_data('post_meta pm', 0, ['join'=>'post ON pm.identifier=post.id','where'=>["field='gallery_category'","value='".$post->id."'","status='published'","type='gallery'"],'sort'=>'sort ASC']);

	if(count($query_gallery_meta) > 0) {
		foreach($query_gallery_meta as $item) {
			$gallery_item = [];
			$image_data = [];
			if($item['identifier'] > 0){
				$gallery_item = $class_post->post_data(['id'=>$item['identifier']]);
				$image_data = $class_post->post_image($gallery_item['id'],['base'=>$class_post->config->file_rel]);
				?>
				<div class="col">
					<div class="post-item">
						<a href="<?php echo $gallery_item['_data']['url']; ?>">
						<div class="post-image">
							<?php if($image_data['main']!=NULL) {
								echo "<img src=\"".$zulu->thumb(FE_crm.$image_data['file'],"w=600&h=400&zc=1&bg=FFFFFF")."\" alt=\"image of ".$gallery_item['name']."\" class=\"feature-image\" />";
							} else {
								echo "<img src=\"".$zulu->thumb(FE_crm.$class_post->config->placeholder,"w=600&h=400&zc=1&bg=FFFFFF")."\" alt=\"placeholder image of ".$gallery_item['name']."\" class=\"feature-image\" />";
							} ?>
						</div>
						<div class="post-body">
							<h3><?php echo $gallery_item['title']; ?></h3>
						</div>
						</a>
					</div>
            	</div>      
			<?php
			}
		}
	}
?>
</div>
</div>
<div class="row text-center">
	<?php echo $pagination; ?>
</div>