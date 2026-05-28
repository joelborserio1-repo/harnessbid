<!-- /.row -->

	<?php if(PAGE_action==NULL) { ?>
    <div class="row">
		<div class="col-lg-12">
			<div class="form-group">
				<a class="btn btn-primary" href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'edit_type'))); ?>"><span class="fas fa-plus-circle"></span> New Object Type</a>
				<a class="btn btn-default" href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'type'))); ?>"><span class="fas fa-cube"></span> Object Types</a>
			</div>
		</div>
		<div class="col-lg-12">
			<ul class="nav nav-tabs">
<!--				<li class="active"><a data-toggle="tab" href="#home">Home</a></li>-->
				<?php $item_tab_counter = 0; ?>
				<?php foreach($object_types as $type){ ?>
					<li class="<?php echo ($item_tab_counter==0?"active":NULL) ?>"><a data-toggle="tab" href="#tab_type_<?php echo $type['id']; ?>"><i class="<?php echo $type['icon']; ?>"></i> <?php echo $type['name'] ?> <span class="bullet stat-0"><?php echo $type_counts[$type['id']]; ?></span></a></li>
					<?php $item_tab_counter++; ?>
				<?php } ?>
			</ul>

			<div class="tab-content">
<!--
				<div id="home" class="tab-pane fade in active">
					<h3>HOME</h3>
					<p>Some content.</p>
				</div>
-->
				<?php $item_content_counter = 0; ?>
				<?php foreach($object_types as $type){ ?>
					<div id="tab_type_<?php echo $type['id']; ?>" class="tab-pane fade <?php echo ($item_content_counter==0?"in active":NULL) ?>">
						<br>
						<div class="row">
							<div class="col-lg-12">
								<div class="panel panel-default">
								<div class="panel-heading">
									<div class="row">
										<div class="col-md-6">
											<i class="<?php echo $type['icon']; ?>"></i> <?php echo $type['name'] ?>
										</div>
										<div class="col-md-6 text-right">
											<a class="btn btn-primary btn-xs" href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'edit', 'type_id'=>$type['id']))); ?>"><span class="fas fa-plus-circle"></span> New <?php echo $type['name']; ?></a>
										</div>
									</div>
								</div>
								<div class="panel-body">
									<?php echo $zulu->template->type_tables[$type['id']]; ?>
								</div>
							</div>
							</div>
						</div>
						
					</div>
					<?php $item_content_counter++; ?>
				<?php } ?>
			</div>
		</div>
    </div>
    <?php } ?>
    <?php if(PAGE_action=='type') { ?>
    <div class="row">
		<div class="col-lg-12">
			<div class="form-group">
				<a class="btn btn-primary" href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'edit_type'))); ?>"><span class="fas fa-plus-circle"></span> New Object Type</a>
			</div>
		</div>
		<div class="col-lg-12">
			<div class="panel panel-default">
				<!-- /.panel-heading -->
				<div class="panel-body">
					<?php echo $zulu->template->body; ?>
				</div>
			</div>
		</div>
    </div>
    <?php } ?>
   	<?php if(PAGE_action=='edit_type') { ?>
   	<form role="form" action="" method="post">
   	<?php echo $form_edit->input_html("hidden","object_type_id",$id); ?>
   	<div class="row">
    	<div class="col-lg-12">
    		<div class="form-group">
    			<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['class'=>['btn btn-success']]); ?>
				<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
    		</div>
    	</div>
    </div>
   	<div class="row">
		<div class="col-md-6">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<i class="fas fa-cog"></i> Object Type settings
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-9">
							<div class="form-group">
								<label>Name <em>*</em></label>
								<?php echo $form_edit->input_html("input","name",$_POST['name']); ?>
							</div>
						</div>
						<div class="col-md-3">
							<label>Icon Selection</label>
							<div class="form-group">
								<div class="btn-group">
									<button id="icon_picker" data-selected="graduation-cap" type="button"
											class="icp icp-dd btn btn-default dropdown-toggle iconpicker-component"
											data-toggle="dropdown">
										Selected: <i class="<?php echo $_POST['icon']; ?>"></i>
										<span class="caret"></span>
									</button>
									<div class="dropdown-menu"></div>
								</div>
							</div>
							<?php echo $form_edit->input_html("hidden","icon",$_POST['icon'],['id'=>'icon_input']); ?>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label>Description <em>*</em></label>
								<?php echo $form_edit->input_html("textarea","description",$_POST['description'], ['custom'=>['rows'=>'5', 'style'=>'resize:none;']]); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
   		<div class="col-lg-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="row">
						<div class="col-md-6">
							<i class="fas fa-list"></i> Object Type custom fields
						</div>
						<div class="col-md-6 text-right">
							<button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#add_field_modal"><i class="fas fa-plus-circle"></i> New Field</button>
						</div>
					</div>
				</div>
				<div class="panel-body">
					<?php if($id>0){ ?>
						<div class="row">
							<div class="col-md-12">
								<div id="field_table_container">
									
								</div>
							</div>
						</div>
					<?php }else{ ?>
						<p>Save Object type to enabled adding custom fields</p>
					<?php } ?>
				</div>
			</div>
		</div>
    </div>
    <div class="row">
    	
    </div>
    <div class="row">
    	<div class="col-lg-12">
    		<div class="form-group">
    			<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['class'=>['btn btn-success']]); ?>
				<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
    		</div>
    	</div>
    </div>
    </form>
	<!-- Add field Modal -->
	<div id="add_field_modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title"><i class="fas fa-list"></i> Object Type custom fields</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-lg-12">
							<p class="field_error"></p>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label>Name</label>
								<?php echo $form_edit->input_html("input","field_name",$_POST['field_name']); ?>
							</div>
						</div>
						<div class="col-lg-6">
							<div class="form-group">
								<label>Type</label>
								<?php echo $form_edit->input_html("select","field_input",'input',['option'=>$class_form_post->config->field_types]); ?>
							</div>
						</div>
					</div>
					<div class="row" id="input-settings">
						<div class="col-md-6">
							<div class="form-group">
								<label>Width</label>
								<?php echo $form_edit->input_html("select","field_width",$_POST['width'],['option'=>$class_form_post->config->field_widths]); ?>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Required</label>
								<?php echo $form_edit->input_html("checkbox","field_required",1,($_POST['required']?['checked'=>['checked']]:NULL)); ?>
							</div>
						</div>
						<div class="col-md-3" id="input-settings-checkbox">
							<div class="form-group">
								<label>All Required</label>
								<?php echo $form_edit->input_html("checkbox","field_required_all",1,($_POST['required_all']?['checked'=>['checked']]:NULL)); ?>
							</div>
						</div>
					</div>
					<div class="row" id="number-settings">
						<div class="col-lg-4">
							<div class="form-group">
								<label>Minimum</label>
								<?php echo $form_edit->input_html("number","config[custom][min]",$_POST['config']['custom']['min'],['custom'=>['step'=>'any']]); ?>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Maximum</label>
								<?php echo $form_edit->input_html("number","config[custom][max]",$_POST['config']['custom']['max'],['custom'=>['step'=>'any']]); ?>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Allow Decimal Numbers</label>
								<?php echo $form_edit->input_html("select","config[custom][step]",$_POST['config']['custom']['step'],['option'=>['any'=>'Yes','1'=>'No']]); ?>
							</div>
						</div>
					</div>
					<div class="row" id="option-settings">
						<div class="col-lg-4">
							<div class="form-group">
								<label>Option Width</label>
								<?php echo $form_edit->input_html("select","config[custom][option_width]",$_POST['config']['custom']['option_width'],['option'=>$class_form_post->config->field_widths]); ?>
							</div>
						</div>
					</div>
					<div class="row" id="select-settings">
						<div class="col-lg-4">
							<div class="form-group">
								<label>Multi-select</label>
								<?php echo $form_edit->input_html("checkbox","config[custom][multiple]",1,($_POST['config']['custom']['multiple']?['custom'=>['checked'=>'checked']]:NULL)); ?>
							</div>
						</div>
					</div>
					<div id="input-option-section">
						<div id='row-container'><?php echo $zulu->template->option_table; ?></div>
						<div class="row">
							<p class='text-center'><button type='button' id='row-add' class='btn btn-primary btn-xs' title='Add another option'><span class='fas fa-plus-circle'></span> Add another option</button></p>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button id="add_field" type="button" class="btn btn-success"><i class="fas fa-plus-circle"></i> Add</button>
					<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fas fa-times"></i> Cancel</button>
				</div>
			</div>
		</div>
	</div>
    <?php } ?>
    
	<?php if(PAGE_action=='edit') { ?>
  	<form role="form" action="" method="post">
  		<div class="row">
    		<div class="col-lg-12">
				<div class="form-group">
					<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['class'=>['btn btn-success']]); ?>
					<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
				</div>
			</div>
    	</div>
   		<div class="row">
			<div class="col-md-6">
				<div class="panel panel-primary">
					<div class="panel-heading">
						<i class="fas fa-info-circle"></i> <?php echo $object_type_data['name'] ?> settings
					</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label>Name <em>*</em></label>
									<?php echo $form_edit->input_html("input","name",$_POST['name']); ?>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label>Reference <em>*</em></label>
									<?php echo $form_edit->input_html("input","reference",$_POST['reference']); ?>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label>Description <em>*</em></label>
									<?php echo $form_edit->input_html("textarea","description",$_POST['description'], ['custom'=>['rows'=>'5', 'style'=>'resize:none;']]); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php if($has_custom_fields){ ?>
			<div class="col-lg-6">
				<div class="panel panel-default">
					<div class="panel-heading">
						<i class="fas fa-info-circle"></i> <?php echo $object_type_data['name'] ?> custom fields
					</div>
					<div class="panel-body">
						<?php echo $custom_form; ?>
					</div>
				</div>
			</div>
   			<?php } ?>
    	</div>
    	<div class="row">
    		<div class="col-lg-12">
				<div class="form-group">
					<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['class'=>['btn btn-success']]); ?>
					<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
				</div>
			</div>
    	</div>
    </form>
    <?php } ?>

<br />