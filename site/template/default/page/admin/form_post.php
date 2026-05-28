<!-- /.row -->
<div class="row">
	<?php if(PAGE_action==NULL) { ?>
    
    <div class="col-lg-12">
        <p>
        	<a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'form'))); ?>" class="btn btn-primary"><span class="fas fa-bars"></span> Manage Forms</a> 
        	<a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'archive_edit','Archive'=>$_GET['Archive']))); ?>" class="btn btn-default"><span class="fas fa-plus-circle"></span> Create Archive</a> 
            <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Do'=>'Export'),'self'=>true)); ?>" class="btn btn-default"><span class="far fa-file-excel"></span> Export</a> 
        </p>
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
            	<?php if(count($template_data) > 0) { ?>
					<ul class="nav nav-tabs">
                    	<li class="<?php echo ($_GET['Template']==0||$_GET['Template']==NULL?"active":NULL); ?>"><a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Template'=>0,'Sort'=>($_GET['Sort']>0?$_GET['Sort']:NULL)))); ?>" class="" aria-expanded="false">All <span class="bullet stat-0"><?php echo $all_count; ?></span></a></li>
                    	<?php foreach($template_data as $template_row) { $count = $class_renew->subscription_count($template_row['id'],$_GET['Sort']); ?>
                        <li class="<?php echo ($_GET['Template']==$template_row['id']?"active":NULL); ?>"><a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Template'=>$template_row['id'],'Sort'=>($_GET['Sort']>0?$_GET['Sort']:NULL)))); ?>" class="" aria-expanded="false"><?php echo stripslashes($template_row['title'])." <span class=\"bullet stat-0\">".$count."</span>"; ?></a></li>
                        <?php } ?>
                        <?php if($custom_count > 0) { ?>
                        <li class="<?php echo ($_GET['Template']=='0'?"active":NULL); ?>"><a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Template'=>'0','Sort'=>($_GET['Sort']>0?$_GET['Sort']:NULL)))); ?>" class="" aria-expanded="false">Custom <span class="bullet stat-0"><?php echo $custom_count_status; ?></span></a></li>
                        <?php } ?>
                    </ul>
				<?php } ?>
                
                <form action="" method="get">
                    <?php echo $form_edit->input_html("hidden","Page",$_GET['Page']); ?>
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
                <form method="post" action="">
    			    <?php echo $zulu->template->body; ?>
                    <div align="center"><?php echo $pagination; ?></div>
				    <p class="page-count text-center opt opt-grey"><i class="fas fa-bars"></i> <?php echo $data_row_total; ?> record(s) in total</p>
                    <div class="panel panel-info panel-checkbox-action-box">
                        <div class="panel-heading"> <span class="fas fa-fire"></span> Select an action to perform on selected items...</div>
                        <div class="panel-body">
                            <div class="form-group">
                                <?php echo $form_edit->input_html("select","execute",$_POST['execute'],array('option'=>array('delete'=>'Delete','0'=>'Archive (Root Folder)')+$archive_options)); ?>
                           </div>
                            <?php echo $form_edit->input_html("submit","submit_complete",'<span class="fas fa-check"></span> Confirm',array('class'=>array('btn-info'))); ?>
                        </div>
                    </div>
                </form>
            </div>
    	</div>
    </div>
    <?php } ?>
	<?php if(PAGE_action=='view') { ?>
    <div class="col-md-12">
        <p>Below is the form data submitted.</p>
    </div>
    <div class="col-md-9">
    	<div class="panel panel-default">
        	<div class="panel-heading"><i class="far fa-file"></i> Data Fields</div>
            <div class="panel-body">
            	<?php echo $zulu->template->body_data; ?>
            </div>
        </div>
    </div>
    <div class="col-md-3">
    	<div class="panel panel-info">
        	<div class="panel-heading"><i class="far fa-file"></i> Submission Info</div>
            <div class="panel-body">
            	<?php echo $zulu->template->body_info; ?>
            </div>
        </div>
        
        <?php if(count($versions)>0&&$class_form_post->config->mode_version) { ?>
    	<div class="panel panel-warning">
        	<div class="panel-heading"><i class="fas fa-history"></i> Update History</div>
            <div class="panel-body">
            	<?php echo $zulu->template->body_update; ?>
            </div>
        </div>
        <?php } ?>
        
    	<div class="panel panel-default">
        	<div class="panel-heading"><i class="fas fa-cogs"></i> Submission Settings</div>
            <div class="panel-body">
            	<form method="post" action="">
					 <div class="form-group">
						<label>Link to archive</label>
						<?php echo $form_edit->input_html("select","parent_id",($_POST['parent_id']>0?$_POST['parent_id']:$data['parent_id']),['option'=>['0'=>'Unarchived']+(array)$archive_array]); ?> 
					</div>
					 <div class="form-group">
						<label>Link to object</label>
						<?php echo $form_edit->input_html("select","object",$_POST['object'],['class'=>['input-linkobj'],'option'=>[''=>'None','sale'=>'Sale','client'=>'Client']]); ?> 
					</div>
					<div class="form-group form-group-object <?php if(trim($_POST['object_name'])==NULL) { echo 'hide'; } ?>">
						<label>Which object?</label>
						<?php echo $form_edit->input_html("input","object_name",$_POST['object_name'],array('autocomplete'=>false,'class'=>array('sf-input','typeahead','sf-input-object_id'),'custom'=>array('data-sf'=>'sf_customer','data-populate'=>'object_id','autocomplete'=>'off'))); ?>
						<div class="guessbox guessbox-object_id">
							<ul></ul>
						</div>
						<?php echo $form_edit->input_html("hidden","object_id",$_POST['object_id'],array('id'=>'sf-object_id','class'=>array('sf-value'))); ?>
					</div>
					<div class="form-group no-margin">
						<?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Save",array('class'=>array('btn-success btn-block'))); ?>

						<?php echo $form_edit->input_html("hidden","action",'save_object'); ?>
					</div>
           		</form>
            </div>
		</div>
       
    	<div class="panel panel-default">
        	<div class="panel-heading"><i class="fas fa-bars"></i> Additional Actions</div>
            <div class="panel-body">
            	<?php if($form_data_tpl['id']>0) { ?>
            	<a href="<?php echo $direct_url; ?>?Submission=<?php echo $data['token']; ?>" target="_blank" class="btn btn-default btn-block"><i class="fas fa-edit"></i> Update Form Submission</a>
            	<a href="<?php echo $zulu->link_page(PAGE_file,['query'=>['Action'=>'form_edit','id'=>$form_data_tpl['id']]]); ?>" class="btn btn-default btn-block"><i class="far fa-folder"></i> Manage Form</a>
            	<a href="<?php echo $zulu->link_page('quote',['query'=>['Action'=>'edit','form_post_id'=>PAGE_id]]); ?>" class="btn btn-default btn-block"><i class="far fa-file"></i> Generate Quote</a>
            	<?php } ?>
			</div>
		</div>
        
    </div>
    <?php } ?>
    
    <?php if(PAGE_action=='form') { ?>
    <div class="col-lg-12">
		<p>
        	<a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'form_edit','Root'=>$_GET['Root']))); ?>"><button class="btn btn-primary" type="button"><span class="fas fa-plus-circle"></span> New Form</button></a> 
            <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'category_edit','Root'=>$_GET['Root']))); ?>"><button class="btn btn-primary" type="button"><span class="fas fa-plus-circle"></span> New Category</button></a> 
            <a href="<?php echo $zulu->link_page(PAGE_file); ?>"><button class="btn btn-info" type="button"><span class="far fa-comment"></span> Form Posts</button></a>
        </p>
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
    			<?php echo $zulu->template->body; ?>
            </div>
    	</div>
    </div>
    <?php } ?>
   
   	<?php if(PAGE_action=='form_edit') { ?>
    <form role="form" action="" method="post" enctype="multipart/form-data">
    	<div class="col-lg-6">
    		<div class="panel panel-default">
				<div class="panel-heading"><i class="fas fa-info-circle"></i> Details</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Title</label>
								<?php echo $form_edit->input_html("input","title",$_POST['title']); ?>
							</div>
						</div>
                        <div class="col-md-6">
							<div class="form-group">
								<label>Category</label>
								<?php echo $form_edit->input_html("select","parent_id",$_POST['parent_id'],['option'=>$cat_options]); ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>Reference Format</label>
								<?php echo $form_edit->input_html("input","reference",$_POST['reference']); ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>Auto-save <?php echo $form_edit->icon_help("This auto-saves details on the form so the user doesn't risk loosing all the data have input."); ?></label>
								<?php echo $form_edit->input_html("select","meta[auto_save]",$_POST['meta']['auto_save'],['option'=>[0=>'No',1=>'Yes']]); ?>
							</div>
						</div>
					</div>
                    <div class="row">
                        <div class="col-md-8">
							<div class="form-group">
                                <label>Extra Recipient Email Addresses <?php echo $form_edit->icon_help("Enter email addresses comma seperated."); ?> <i>(default: <?php echo $class_setting->data['contact_email']; ?>)</i></label>
								<?php echo $form_edit->input_html("input","email",$_POST['email'],['placeholder'=>'e.g. info@example.com,email@example.com']); ?>
							</div>
						</div>
                        <div class="col-md-4">
							<div class="form-group">
                                <label>Send to Admin <?php echo $form_edit->icon_help("Only works if extra receipients are defined. Tick this to prevent forms going to the main account email."); ?></label>
								<?php echo $form_edit->input_html("select","meta[email_admin]",(!isset($_POST['meta']['email_admin'])?1:$_POST['meta']['email_admin']),['option'=>[0=>'No',1=>'Yes']]); ?>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<div class="form-group">
								<label>Description</label>
								<?php echo $form_edit->input_html("textarea","description",$_POST['description']); ?>
							</div>
						</div>
					</div>
                    <div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label>Logo Override</label>
                                <?php if($_POST['meta']['logo']==NULL) { ?>
                                <?php echo $form_edit->input_html("file","image"); ?>
                                <?php } else { ?>
                                <p><span class="opt opt-success"><span class="fas fa-check"></span> Logo Uploaded</span> <a target="_blank" href="<?php echo $class_form_post->form_folder_rel.$class_user->authorised->id."/".$_POST['meta']['logo']; ?>" class="btn btn-xs btn-warning">View</a> <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'form_edit','id'=>PAGE_id,'Method'=>'DeleteLogo'))); ?>" class="btn btn-xs btn-danger">Remove</a></p>
                                <?php } ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>Password Protect</label>
								<?php echo $form_edit->input_html("password","meta[password]",$_POST['meta']['password']); ?>
							</div>
						</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
							<div class="form-group">
								<label>Auto-Archive</label>
								<?php echo $form_edit->input_html("select","meta[archive_id]",$_POST['meta']['archive_id'],['option'=>$archive_options]); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading"><i class="fas fa-tag"></i> Messages &amp; Labels</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>'Submit' Form Button</label>
								<?php echo $form_edit->input_html("input","meta[label_form_submit]",$_POST['meta']['label_form_submit']); ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>'Complete' Validation</label>
								<?php echo $form_edit->input_html("input","meta[valid_complete]",$_POST['meta']['valid_complete']); ?>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Show Edit Link on Complete</label>
								<?php echo $form_edit->input_html("select","meta[valid_complete_toggle_link]",$_POST['meta']['valid_complete_toggle_link'],['option'=>[0=>'No',1=>'Yes']]); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
   			<?php if($class_user->authorised->role=='admin') { ?>
    		<div class="panel panel-default">
				<div class="panel-heading"><i class="fas fa-user"></i> Change Form User</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label>User</label>
								<?php echo $form_edit->input_html("select","user_id",$_POST['user_id'],['option'=>$form_edit->userOptionForm(false,0)]); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php } ?>
			
			<?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Save",array('class'=>array('btn-success'))); ?>
			<?php echo $form_edit->input_html("reset","reset",'Reset'); ?>
            
			<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
    	</div>
        <?php if(PAGE_id > 0) { ?>
        <div class="col-lg-6">
    		<div class="panel panel-default">
				<div class="panel-heading">
					<div class="row">
						<div class="col-md-6">
							<i class="fas fa-link"></i> Reference Fields
						</div>
						<div class="col-md-6 text-right">
							<a href="<?php echo $zulu->link_page('form_post',['query'=>['Action'=>'form_field','Form'=>PAGE_id]]); ?>" class="btn btn-warning btn-xs"><i class="fas fa-bars"></i> Manage Fields</a>
						</div>
					</div>
				</div>
				<div class="panel-body">
                    <p>You can use the below reference codes in the <a href='#' id="reference-help">Reference Format</a> field.</p>
					<?php echo $zulu->template->body; ?>
				</div>
			</div>
        </div>
        <?php } ?>
    </form>
    <?php } ?>
    
   	<?php if(PAGE_action=='archive_edit') { ?>
    <form role="form" action="" method="post">
    	<div class="col-lg-6">
    		<div class="panel panel-default">
				<div class="panel-heading"><i class="fas fa-info-circle"></i> Archive Details</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label>Archive Name</label>
								<?php echo $form_edit->input_html("input","form_name",$_POST['form_name']); ?>
							</div>
						</div>
                        <div class="col-lg-6">
							<div class="form-group">
								<label>Parent Archive</label>
								<?php echo $form_edit->input_html("select","parent_id",$_POST['parent_id'],['option'=>$cat_options]); ?>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<div class="form-group">
								<label>Description</label>
								<?php echo $form_edit->input_html("textarea","form_description",$_POST['form_description']); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			
   			<?php if($class_user->authorised->role=='admin') { ?>
    		<div class="panel panel-default">
				<div class="panel-heading"><i class="fas fa-user"></i> Change Form User</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label>User</label>
								<?php echo $form_edit->input_html("select","user_id",(isset($_POST['user_id'])?$_POST['user_id']:$class_user->authorised->id),['option'=>$form_edit->userOptionForm(false,0)]); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php } ?>
			
			<?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Save",array('class'=>array('btn-success'))); ?>
			<?php echo $form_edit->input_html("reset","reset",'Reset'); ?>
            
			<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
    	</div>
    </form>
    <?php } ?>
    
    <?php if(PAGE_action=='form_field') { ?>
    <div class="col-lg-12">
		<p>
        	<a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'form_field_edit','Form'=>$_GET['Form']))); ?>"><button class="btn btn-primary" type="button"><span class="fas fa-plus-circle"></span> New Field</button></a> 
        	<a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'form'))); ?>"><button class="btn btn-default" type="button"><span class="fas fa-bars"></span> Forms</button></a> 
        </p>
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
    			<?php echo $zulu->template->body; ?>
            </div>
    	</div>
    </div>
    <?php } ?>
    
    <?php if(PAGE_action=='form_field_edit') { ?>
    <form role="form" action="" method="post">
    	<div class="col-lg-6">
    		<div class="panel panel-default">
				<div class="panel-heading"><i class="fas fa-wrench"></i> Details</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label>Name</label>
								<?php echo $form_edit->input_html("input","name",$_POST['name']); ?>
							</div>
						</div>
						<div class="col-lg-6">
							<div class="form-group">
								<label>Type</label>
								<?php echo $form_edit->input_html("select","input",$_POST['input'],['option'=>$class_form_post->config->field_types]); ?>
							</div>
						</div>
					</div>
					<div class="row" id="input-settings">
						<div class="col-md-6">
							<div class="form-group">
								<label>Width</label>
								<?php echo $form_edit->input_html("select","width",$_POST['width'],['option'=>$class_form_post->config->field_widths]); ?>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Required</label>
								<?php echo $form_edit->input_html("checkbox","required",1,($_POST['required']?['checked'=>['checked']]:NULL)); ?>
							</div>
						</div>
						<div class="col-md-3" id="input-settings-checkbox">
							<div class="form-group">
								<label>All Required</label>
								<?php echo $form_edit->input_html("checkbox","required_all",1,($_POST['required_all']?['checked'=>['checked']]:NULL)); ?>
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
				</div>
			</div>
   		
    		<div class="panel panel-default">
				<div class="panel-heading"><i class="fas fa-exclamation-triangle"></i> Validation</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Notification for 'required'...</label>
								<?php echo $form_edit->input_html("input","config[custom][valid_msg_required]",$_POST['config']['custom']['valid_msg_required']); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
          
           <div class="panel panel-info">
				<div class="panel-heading"><a data-toggle="collapse" href="#panel-other"><i class="fas fa-cog"></i> Field Settings</a></div>
                <div class="panel-collapse collapse" id="panel-other">
					<div class="panel-body">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label>Show if <u>this</u> field has a value: <?php echo $form_edit->icon_help('You can select a field that THIS field can copy from - useful for double-up addresses.'); ?></label>
									<?php echo $form_edit->input_html("select","config[show_field_if_empty]",$_POST['config']['show_field_if_empty'],['option'=>[''=>'None']+(array)$arr_field]); ?>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label>...Only if value equals: <?php echo $form_edit->icon_help('You can select a field that THIS field can copy from - useful for double-up addresses.'); ?></label>
									<?php echo $form_edit->input_html("input","config[show_field_if_value]",$_POST['config']['show_field_if_value'],['placeholder'=>'I.e. Yes / Other / No']); ?>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label>Can copy from <u>this</u> field: <?php echo $form_edit->icon_help('You can select a field that THIS field can copy from - useful for double-up addresses.'); ?></label>
									<?php echo $form_edit->input_html("select","config[inherit_field]",$_POST['config']['inherit_field'],['option'=>[''=>'None']+(array)$arr_field]); ?>
								</div>
							</div>
						</div>
					</div>
			   </div>
			</div><!-- other settings panel -->
          
           <div class="panel panel-info">
				<div class="panel-heading"><a data-toggle="collapse" href="#panel-office"><i class="far fa-briefcase"></i> Office Only</a></div>
                <div class="panel-collapse collapse" id="panel-office">
					<div class="panel-body">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label>Office Only Use</label>
									<?php echo $form_edit->input_html("checkbox","config[office]",1,['checked'=>($_POST['config']['office']>0?true:false)]); ?>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label>Publicity</label>
									<?php echo $form_edit->input_html("select","config[office_public]",$_POST['config']['office_public'],['option'=>[0=>'Private',1=>'Show']]); ?>
								</div>
							</div>
						</div>
					</div>
			   </div>
			</div><!-- office only panel -->
            
			<?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Save",array('class'=>array('btn-success'))); ?>
			<?php echo $form_edit->input_html("reset","reset",'Reset'); ?>
            
			<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
   			<?php echo $form_edit->input_html("hidden","form_id",$_POST['form_id']); ?>
    	</div>
    	<div class="col-lg-6">
    		<div class="panel panel-default" id="input-option-section">
				<div class="panel-heading"><i class="fas fa-bars"></i> Options</div>
				<div class="panel-body">
					<div id='row-container'><?php echo $zulu->template->option_table; ?></div>
					<div class="row">
						<p class='text-center'><button type='button' id='row-add' class='btn btn-primary btn-xs' title='Add another option'><span class='fas fa-plus-circle'></span> Add another option</button></p>
					</div>
				</div>
			</div>
			
			<div class="panel panel-default" id="text-option-section">
				<div class="panel-heading">Text</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-lg-12">
							<div class="form-group">
								<?php echo $form_edit->input_html("htmlarea","description",$_POST['description']); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
    	
    </form>
    <?php } ?>
    
    <?php if(PAGE_action=='category_edit') { ?>
    
    <form role="form" action="" method="post">
    	<div class="col-lg-6">
    		<div class="panel panel-default">
				<div class="panel-heading"><i class="fas fa-info-circle"></i> Settings</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label>Name</label>
								<?php echo $form_edit->input_html("input","title",$_POST['title']); ?>
							</div>
						</div>
						<div class="col-lg-6">
							<div class="form-group">
								<label>Category</label>
								<?php echo $form_edit->input_html("select","parent_id",$_POST['parent_id'],['option'=>$cat_options]); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Save",array('class'=>array('btn-success'))); ?>
			<?php echo $form_edit->input_html("reset","reset",'Reset'); ?>
            
			<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
    	</div>
    </form>
    <?php } ?>
    
</div>