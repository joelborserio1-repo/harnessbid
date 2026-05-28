<!-- /.row -->
<div class="row">
	<?php if(PAGE_action==NULL) { ?>

	<div class="col-lg-12">

		<p>
            <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'edit'))); ?>" class="btn btn-primary"><span class="fas fa-plus-circle"></span> New Location</a>
        </p>

        <?php echo $zulu->template->body; ?>
        <div align="center"><?php echo $pagination; ?></div>
        <p class="page-count text-center opt opt-grey"><i class="fa fa-bars"></i> <?php echo $total_count; ?> record(s) in total</p>
    </div>
    <?php } ?>

	<?php if(PAGE_action=='edit') { ?>
	<form role="form" action="" method="post" enctype="multipart/form-data">
    	<div class="col-lg-12">

    		<div class="panel panel-default">
				<div class="panel-heading"><i class="far fa-edit"></i> Main Info</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label>Name <span class="denote">*</span></label>
								<?php echo $form_edit->input_html("input","name",$_POST['name']); ?>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Currency <span class="denote">*</span></label>
								<?php echo $form_edit->input_html("select","currency_id",$_POST['currency_id'],['option'=>['0'=>'']+$currency_options]); ?>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Enabled <span class="denote">*</span></label>
								<?php echo $form_edit->input_html("select","status",$_POST['status'],['option'=>['1'=>'Yes','0'=>'No']]); ?>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Sort Order</label>
								<?php echo $form_edit->input_html("number","sort",$_POST['sort']); ?>
							</div>
						</div>
                    </div>
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label>Tax Enabled <span class="denote">*</span></label>
								<?php echo $form_edit->input_html("select","tax_disable",$_POST['tax_disable'],['option'=>['1'=>'No','0'=>'Yes']]); ?>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Tax Type</label>
								<?php echo $form_edit->input_html("select","tax_method",$_POST['tax_method'],['option'=>['0'=>'Exclusive','1'=>'Inclusive']]); ?>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Tax Rate</label>
								<div class="input-group">
									<?php echo $form_edit->input_html("input","tax_rate",$_POST['tax_rate']); ?>
									<span class="input-group-addon">%</span>
								</div>
							</div>
						</div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
							<div class="form-group">
								<label>Image</label>
								<div class="upload-wrapper">
                                    <?php echo $class_file->uploadifive_input("image_main"); ?>
                                </div>
                                <?php if($image != null) { ?>
								<br />
                                <div class="image">
                                    <div class="ctrl">
                                        <a href="<?= $zulu->link_page(PAGE_file, ['self'=>true,'query'=>['Do'=>'ClearImage']]); ?>" class="btn btn-danger btn-xs bt-image-delete" data-type="header"><i class="fas fa-times"></i></a>
                                    </div>
                                    <img src="<?php echo $image; ?>?<?php echo $zulu->serial(8); ?>" alt="custom image" />
                                </div>
                                <?php } ?>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Pedigree Link</label>
								<?php echo $form_edit->input_html("input","pedigree_link",$_POST['pedigree_link']); ?>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Enable USTA Lookup</label>
								<?php echo $form_edit->input_html("select","usta_lookup",$_POST['usta_lookup'],['option'=>['1'=>'Yes','0'=>'No']]); ?>
							</div>
						</div>
                    </div>
				</div>
			</div>

			<?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Save",array('class'=>array('btn-success'), 'id'=>'postsubmit')); ?>

			<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
            <?php if($new) { ?>
            <?php echo $form_edit->input_html("hidden","temp_folder",$temp_folder); ?>
            <?php } ?>
    	</div>
    </form>
    <?php } ?>

	<?php if(PAGE_action=='region') { ?>

    <div class="col-lg-12">
        <p>
            <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'region_edit','location'=>$location->id))); ?>" class="btn btn-default"><span class="fas fa-plus-circle"></span> New Region</a>
        </p>
        <!-- /.panel-heading -->
        <form action="" method="get">
            <?php echo $form_edit->input_html("hidden","Page",$_GET['Page']); ?>
            <?php echo $form_edit->input_html("hidden","Action",$_GET['Action']); ?>
            <?php echo $form_edit->input_html("hidden","id",$_GET['id']); ?>
            <div class="row">
                <div class="col-lg-3">
                    <div class="input-group">
                        <?php echo $form_edit->input_html("input","Search",$_GET['Search'],['custom'=>['placeholder'=>'Search','id'=>'search-box']]); ?>
                        <span class="input-group-btn"><?php echo $form_edit->input_html("submit","",'<span class="fa fa-search"></span>',['custom'=>['id'=>'search-submit']]); ?></span>
                    </div>
                </div>
				<div class="col-lg-3">
                    <?php echo $form_edit->input_html("select","Location",$_GET['Location'],['custom'=>[],'option'=>['0'=>'All Regions']+$location_options]); ?>
                </div>
            </div>
        </form>
        <br>
        <?php echo $zulu->template->body; ?>
        <div align="center"><?php echo $pagination; ?></div>
        <p class="page-count text-center opt opt-grey"><i class="fa fa-bars"></i> <?php echo $total_count; ?> record(s) in total</p>
    </div>
    <?php } ?>

	<?php if(PAGE_action=='region_edit') { ?>

    <form role="form" action="" method="post" enctype="multipart/form-data">
    	<div class="col-lg-12">
    		<div class="panel panel-default">
				<div class="panel-heading"><i class="far fa-edit"></i> Main Info</div>
				<div class="panel-body">
					<div class="row">
                        <div class="col-md-3">
							<div class="form-group">
								<label>Name <span class="denote">*</span></label>
								<?php echo $form_edit->input_html("input","name",$_POST['name']); ?>
							</div>
						</div>
                        <div class="col-md-3">
							<div class="form-group">
								<label>Location <span class="denote">*</span></label>
								<?php echo $form_edit->input_html("select","location_id",$_POST['location_id'],['option'=>['0'=>'']+$location_options]); ?>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Enabled <span class="denote">*</span></label>
								<?php echo $form_edit->input_html("select","status",$_POST['status'],['option'=>['1'=>'Yes','0'=>'No']]); ?>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Sort Order</label>
								<?php echo $form_edit->input_html("number","sort",$_POST['sort']); ?>
							</div>
						</div>
					</div>
				</div>
			</div>

			<?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Save",array('class'=>array('btn-success'),'id'=>'postsubmit')); ?>
			<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
    	</div>
    </form>
    <?php } ?>

</div>
<br />
