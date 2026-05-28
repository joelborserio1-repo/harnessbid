<h1><?php echo $post->title; ?></h1>
<h2><?php echo ucfirst(implode(" ",$filter_title)); ?></h2>
<?php echo $post->content; ?>

<div class="post-wrap post-type-testimonial box-container">
    <?php foreach($index_loop as $item) { ?>
    <?php $star_rate = ''; if($item['_data']['_meta']['rating']>0) {
    	$star_rate = "<br><span class=\"rating\">";
    	for($i=1;$i<=5;$i++) {
			$star_rate .= "<i class=\"ti ".($i<=$item['_data']['_meta']['rating']?"ti-star-filled":"ti-star")."\" aria-hidden=\"true\"></i>";
        }
        $star_rate .= "</span>";
    } ?>
    <div class="box testimonial" itemscope itemtype="http://schema.org/Review">
        <blockquote>
            <?php $quot = '<i class="ti ti-quote" aria-hidden="true"></i> '; if($item['name']!=NULL) { ?>
            <h3><?php echo $quot; ?><?php echo $item['name']; ?></h3>
            <?php unset($quot); } ?>
            <div itemprop="description">
            	<p><?php echo $item['content']; ?></p>
            </div>
            <footer>
                <div class="divider"></div>
                <p class="auth"><?php if($item['image']['main']!=NULL) { echo "<img src=\"".$zulu->thumb(FE_crm.$item['image']['file'],"w=200&h=150&far=1&bg=ffffff")."\" alt=\"image of ".$item['name']."\" class=\"feature-image\" />"; } ?><span><span itemprop="author" itemscope itemtype="http://schema.org/Person"><span itemprop="name"><?php echo $item['_data']['_meta']['from']; ?></span></span><?php echo $star_rate; ?></span></p>
                
                <!--additional meta data-->
                <span itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
                    <meta itemprop="ratingValue" content="<?php echo $item['_data']['_meta']['rating']; ?>"/>
                    <meta itemprop="bestRating" content="5"/>
                </span>
                <span itemprop="itemReviewed" itemscope itemtype="http://schema.org/Thing">
                    <meta itemprop="name" content="Products / services from <?php echo $setting['ws_site_name']; ?>"/>
				</span>
                <span itemprop="publisher" itemscope itemtype="http://schema.org/Organization">
                    <meta itemprop="name" content="<?php echo $setting['ws_site_name']; ?>"/>
                </span>
            </footer>
        </blockquote>
    </div>
    <?php } ?>
</div>

<div class="text-center">
	<?php echo $pagination; ?>
</div>