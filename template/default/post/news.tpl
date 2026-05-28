<?php if($post_image['main']!=NULL) { ?>
<div class="news-header" itemprop="image" itemscope itemtype="https://schema.org/ImageObject" style="background-image:url(<?php echo $class_post->config->file_rel.$post_image['main']; ?>); ?>">
	<?php echo $breadcrumb; ?>
	<div class="news-intro capsule-container plain" itemscope itemtype="http://schema.org/NewsArticle">
		<div class="frame">
 		<link itemprop="image" href="<?php echo $class_post->config->file_rel.$post_image['main']; ?>">
  		<meta itemprop="url" content="<?php echo $class_post->post_url($post_data['id']); ?>">
        <meta itemprop="datePublished" content="<?php echo $zulu->time_history($post->_meta->date); ?>">
        <meta itemprop="dateModified" content="<?php echo $zulu->time_history($post->stat_update); ?>">
        <link itemprop="mainEntityOfPage" href="<?php echo $class_post->post_url($post_data['id']); ?>" />
            
		<h1 itemprop="headline"><?php echo $post->title; ?></h1>
    	<div class="wrap" itemprop="author" itemscope itemtype="https://schema.org/Person">
        	<?php if(trim($post->_data->author_name)!=NULL) { ?>
            <p class="capsule plain" itemprop="name"><i class="ti ti-user" aria-hidden="true"></i> By
            	<?php echo $post->_data->author_name; ?>
            </p>        	
        	<?Php } ?>
        	<?php if($post->_meta->date>0) { ?>
            <p class="capsule plain"><i class="ti ti-clock" aria-hidden="true"></i> <?php echo $zulu->time_history($post->_meta->date); ?></p>
        	<?Php } ?>
        </div>
          <span itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
             <meta itemprop="name" content="<?php echo SITE_title; ?>">
             <span itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
             	<link itemprop="url" href="<?php echo $zulu->template->logo_url; ?>" />
             </span>
          </span>
    	</div>
    </div>
</div>
<?php } else { ?>
<div class="news-intro capsule-container">
   	<div class="frame">
		<h1><?php echo $post->title; ?></h1>
		<div class="wrap">
			<?php if(trim($post->_data->author_name)!=NULL) { ?>
				<p class="capsule plain"><i class="ti ti-user" aria-hidden="true"></i> By <?php echo $post->_data->author_name; ?></p>
				<p class="capsule plain"><i class="ti ti-clock" aria-hidden="true"></i> <?php echo $zulu->time_history($post->_meta->date); ?></p>
			<?Php } ?>
		</div>
    </div>
</div>
<?php } ?>

<div class="frame">
	<div class="post-content">
		<?php echo $post->content; ?>
	</div>

	<?php echo $class_post->post_content($post_data,['display'=>'gallery']); ?>

</div>
<div class="section section-variant-1">
	<div class="frame">
		<div class="coltable col2 vmiddle">
			<div class="col">
				<a href="<?php echo $_SERVER['HTTP_REFERER']; ?>" class="button"><i class="ti ti-arrow-back" aria-hidden="true"></i> Return</a>
			</div>
			<div class="col text-right">
				<div class="display-inline-block">
					<!-- AddToAny BEGIN -->
					<div class="a2a_kit a2a_kit_size_32 a2a_default_style">
					<a class="a2a_dd" href="https://www.addtoany.com/share"></a>
					<a class="a2a_button_facebook"></a>
					<a class="a2a_button_twitter"></a>
					<a class="a2a_button_linkedin"></a>
					<a class="a2a_button_pinterest"></a>
					<a class="a2a_button_email"></a>
					</div>
					<script async src="https://static.addtoany.com/menu/page.js"></script>
					<!-- AddToAny END -->
				</div>
			</div>
		</div>
	</div>
</div>