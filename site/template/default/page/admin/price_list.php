<!-- /.row -->
<div class="row">
	<?php if(PAGE_action==NULL) { ?>

	<div class="col-lg-12">
		<form role="form" action="" method="post">

			<?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Save",array('class'=>array('btn-success'), 'id'=>'postsubmit')); ?>
			<br /><br />

			<?php foreach(PriceList::$price_options as $price_key=>$price_option) { ?>
			<div class="panel panel-default">
				<div class="panel-heading"><h4><?= ($price_option['addon']?"Addon - ":null).$price_option['title']; ?></h4></div>
				<div class="panel-body">
					<!--<div class="row">
						<?php if($price_option['addon']) { ?>
						<div class="col-md-3">
							<div class="form-group">
								<label>Available</label>
								<?php echo $form_edit->input_html("select","status",$_POST['status'],['option'=>['1'=>'Yes','0'=>'No']]); ?>
							</div>
						</div>
						<?php } ?>
					</div>-->
					<div class="row">
						<div class="col-lg-12">
							<?= $price_tables[$price_key]; ?>
						</div>
					</div>
				</div>
			</div>
			<?php } ?>

			<?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Save",array('class'=>array('btn-success'), 'id'=>'postsubmit')); ?>
			<?php echo $form_edit->input_html("hidden","action",'edit'); ?>

		</form>
    </div>
    <?php } ?>

	<?php if(PAGE_action=='edit') { ?>
	<form role="form" action="" method="post">
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
								<label>Code <span class="denote">*</span></label>
								<?php echo $form_edit->input_html("input","code",$_POST['code']); ?>
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

			<?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Save",array('class'=>array('btn-success'), 'id'=>'postsubmit')); ?>

			<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
    	</div>
    </form>
    <?php } ?>
</div>
<br />
