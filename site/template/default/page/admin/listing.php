
<div class="row">
	<?php if(PAGE_action==NULL) { ?>

    <div class="col-lg-12">

        <div class="row">
            <!-- /.panel-heading -->
            <div class="">

            	<div class="col-lg-12">
					<form action="" method="get">
						<?php echo $form_edit->input_html("hidden","Page",$_GET['Page']); ?>
						<?php if($_GET['ProductRoot'] != NULL) { echo $form_edit->input_html("hidden","ProductRoot",$_GET['ProductRoot']); } ?>
						<?php if($_GET['Do'] != NULL) { echo $form_edit->input_html("hidden","Do",$_GET['Do']); } ?>
                        <?php if(isset($_GET['brand']) && $_GET['brand'] > 0) { echo $form_edit->input_html("hidden","brand",$_GET['brand']); } ?>
						<div class="row">
							<div class="col-lg-2">
								<div class="input-group">
									<?php echo $form_edit->input_html("input","Search",$_GET['Search'],['custom'=>['placeholder'=>'Search','id'=>'search-box']]); ?>
									<span class="input-group-btn"><?php echo $form_edit->input_html("submit","",'<span class="fas fa-search"></span>',['custom'=>['id'=>'search-submit']]); ?></span>
								</div>
							</div>
						</div>
					</form>
           			<br>

					<ul class="nav nav-tabs">
						<?php foreach($tabs as $key=>$val) { ?>
						<li class="<?php echo ($_GET['tab']==$key?"active":NULL); ?>">
							<a href="<?php echo $zulu->link_page(PAGE_file,array('self'=>true,'query'=>['tab'=>$key])); ?>" class="" aria-expanded="false"><?php echo $val; ?></span></a>
						</li>
						<?php } ?>
                    </ul>

            		<form method="post" action="">
						<?php echo $zulu->template->body; ?>
						<div align="center"><?php echo $pagination; ?></div>
						<p class="page-count text-center opt opt-grey"><i class="fas fa-bars"></i> <?php echo $product_total_count; ?> record(s) in total</p>

						<?php if($bulk_mode) { ?>
						<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['id'=>'postsubmit','class'=>['btn-success','btn']]); ?>
						<?php echo $form_edit->input_html("hidden","action",'bulk'); ?>
						<?php } else { ?>
						<div class="panel panel-info panel-checkbox-action-box">
							<div class="panel-heading"> <span class="fas fa-fire"></span> Select an action to perform on selected items...</div>
							<div class="panel-body">
								<div class="form-group">
									<?php echo $form_edit->input_html("select","execute",$_POST['execute'],array('option'=>array(0=>'None','publish'=>'Publish','hide'=>'Hide'))); ?>
							   </div>
								<?php echo $form_edit->input_html("submit","submit_complete",'<span class="fas fa-check"></span> Confirm',array('class'=>array('btn-info'))); ?>
							</div>
						</div>
						<?php } ?>
					</form>
				</div>
    		</div>
        </div>

    </div>
    <?php } ?>

    <?php if(PAGE_action=='edit') { ?>

	<div class="col-md-6">
		<p>
			<a href="<?= $zulu->front_link($product->feURL()); ?>" target="_blank" class="btn btn-default"><?= $zulu->icon('external-link-alt', 'r'); ?> View</a>
			<a href="<?= $zulu->link_page('client', ['query'=>['id'=>$product->client_id,'Action'=>'edit']]); ?>" target="_blank" class="btn btn-info"><?= $zulu->icon('user'); ?> Customer</a>
			<a href="<?= $zulu->link_page('sale', ['query'=>['id'=>$listing->sale_id,'Action'=>'edit']]); ?>" target="_blank" class="btn btn-success"><?= $zulu->icon('money-bill', 'r'); ?> Sale</a>
		</p>
	</div>
	<div class="col-md-6 text-right">
		<p>
			<?php if($product->live) { ?>
			<?php if(!$product->hide) { ?>
			<a href="<?= $zulu->link_page(PAGE_file, ['self'=>true, 'query'=>['do'=>'disable']]); ?>" class="btn btn-warning"><?= $zulu->icon('exclamation-triangle'); ?> Disable</a>
			<?php } else { ?>
			<a href="<?= $zulu->link_page(PAGE_file, ['self'=>true, 'query'=>['do'=>'enable']]); ?>" class="btn btn-success"><?= $zulu->icon('exclamation-triangle'); ?> Enable</a>
			<?php } ?>
			<?php } ?>
			<a href="<?= $zulu->link_page(PAGE_file, ['self'=>true, 'query'=>['do'=>'delete']]); ?>" class="btn btn-danger confirm"><?= $zulu->icon('times'); ?> Delete</a>
		</p>
	</div>

    <div class="col-md-8">

		<div class="panel panel-primary">
        	<div class="panel-heading">
            	<i class="far fa-megaphone"></i> Statistics
            </div>
            <div class="panel-body">
				<dl class="dl-horizontal">
					<?php if($product->listing_type == 'auction') { ?>
					<dt>Bids</dt>
					<dd><?= $current_bids; ?></dd>
					<dt>Bidders/Watchers</dt>
					<dd><?= $current_watchers; ?></dd>
					<?php } ?>
					<dt>Views</dt>
					<dd><?= $current_views; ?></dd>
				</dl>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading"><i class="far fa-edit"></i> Listing Information</div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-lg-12">
                        <dl class="dl-horizontal">
							<dt>Name</dt>
							<dd><?= stripslashes($product->name); ?></dd>
							<dt>Sire</dt>
				            <dd><?= stripslashes($product->spec_sire); ?></dd>
				            <dt>Dam</dt>
				            <dd><?= stripslashes($product->spec_dam); ?></dd>
				            <dt>Sex</dt>
				            <dd><?= stripslashes($product->spec_sex); ?></dd>
				            <dt>Colour</dt>
				            <dd><?= stripslashes($product->spec_colour); ?></dd>
				            <dt>Age</dt>
				            <dd><?= stripslashes($product->spec_age); ?></dd>
				            <dt>Location</dt>
				            <dd><?= $location_name; ?></dd>

							<?php if($product->listing_type == 'auction') { ?>
				            <dt>Start Price</dt>
				            <dd>$<?= number_format($listing->price); ?> <?= $currency_code; ?></dd>
				            <dt>Reserve Price</dt>
				            <dd>$<?= number_format($listing->price_reserve); ?> <?= $currency_code; ?></dd>
				            <?php } else { ?>
				            <dt>Asking Price</dt>
				            <dd>$<?= number_format($listing->price); ?> <?= $currency_code; ?></dd>
				            <?php } ?>

				            <dt>Listing End Date</dt>
				            <dd><?= $zulu->date($listing->time_close, 'd/m/Y'); ?></dd>
				            <dt>Listing End Time</dt>
				            <dd><?= $zulu->date($listing->time_close, 'h:ia'); ?></dd>

							<?php if(trim($product->description)) { ?>
							<dt>Description</dt>
				            <dd><?= stripslashes(str_replace(['\r\n',chr(10),chr(13)], '<br />', $product->description)); ?></dd>
							<?php } ?>

							<?php if($meta['video_1'] || $meta['video_2']) { ?>
						   	<dt>Videos</dt>
					        <dd><?= $zulu->compile('<br />', [$meta['video_1'], $meta['video_2']]); ?></dd>
						    <?php } ?>

							<?php if($meta['pedigree_link']) { ?>
							<dt>Pedigree Information</dt>
							<dd><?= $meta['pedigree_link']; ?></dd>
					        <?php } elseif($meta['pedigree_file']) { ?>
							<dt>Pedigree File</dt>
							<dd><a href="<?= MAIN_url."file/product/".$id."/pedigree/".$meta['pedigree_file']; ?>" target="_blank"><?= MAIN_url."file/product/".$id."/pedigree/".$meta['pedigree_file']; ?></a></dd>
					        <?php } ?>

						</dl>
                    </div>
                </div>
        	</div>
        </div>

        <div class="panel panel-default">
        	<div class="panel-heading">
            	<i class="far fa-image"></i> Images
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <div class="coltable col5 float pad">
                        <?php foreach($image_data['_all'] as $row) { ?>
                        <div class="col">
                        	<div class="image">
                            	<img src="<?php echo zulu::thumb($row,"w=300&h=200&zc=1&bg=ffffff"); ?>" class="responsive" alt="product image" />
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

	<div class="col-md-4">
		<?php if($product->listing_type == 'auction') { ?>
		<div class="panel panel-warning">
        	<div class="panel-heading">
            	<i class="far fa-gavel"></i> Bid History
            </div>
            <div class="panel-body">
				<?= $bid_table; ?>
            </div>
        </div>
		<?php } ?>

		<div class="panel panel-info">
        	<div class="panel-heading">
            	<i class="far fa-plus"></i> Add-ons
            </div>
            <div class="panel-body">
				<?= $addon_table; ?>
            </div>
        </div>
	</div>

    <br>
    <?php } ?>

</div>
