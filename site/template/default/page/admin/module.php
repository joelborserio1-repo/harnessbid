<!-- /.row -->
<div class="row">
	<?php if(PAGE_action==NULL) { ?> 
    
    <div class="col-lg-12">
   		<div class="row">
			<div class="col-md-12">
				<ul class="nav nav-tabs" style="margin-bottom:10px;">
				<?php foreach($class_module->module_types as $key=>$array) { ?>
					<li class="<?php echo ($_GET['Tab']==$key?"active":NULL); ?>"><a class="layout-load" data-template="<?php echo $key; ?>" href="<?php echo $zulu->link_page(PAGE_file,['query'=>["Tab"=>$key]]); ?>"><?php echo $array['label']; ?></a></li>
				<?php } ?>
				</ul>
			</div>
		</div>
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
    			<?php echo $zulu->template->body; ?>
            </div>
    	</div>
        <?php echo $form_edit->input_html("hidden","Page",PAGE_file); ?>
    </div>
    <?php } ?>
	<?php if(PAGE_action=='edit') { ?>
    <form role="form" action="" method="post">
    	<div class="col-lg-6">
    		<div class="panel panel-default">
				<div class="panel-heading">Details</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-lg-4">
							<div class="form-group">
								<label>Name</label>
								<?php echo $form_edit->input_html("input","name",$_POST['name']); ?>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Front-end Name</label>
								<?php echo $form_edit->input_html("input","name_client",$_POST['name_client']); ?>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Status</label>
								<?php echo $form_edit->input_html("select","status",$_POST['status'],array('option'=>array('0'=>'Inactive','1'=>'Active'))); ?>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<div class="form-group">
								<label>Front-end Description</label>
								<?php echo $form_edit->input_html("textarea","info_client",$_POST['info_client']); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
   			
   			<?php if(method_exists($module,'admin_form_edit')) { ?>
   			<div class="panel panel-default">
   				<div class="panel-heading">Settings</div>
				<div class="panel-body">
					<?php echo $module->admin_form_edit(); ?>
				</div>
			</div>
   			<?php } ?>
            
			<?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Save",array('class'=>array('btn-success'))); ?>
			<?php echo $form_edit->input_html("reset","reset",'Reset'); ?>
            
			<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
    	</div>
    </form>
    <?php } ?>
</div>
<br />