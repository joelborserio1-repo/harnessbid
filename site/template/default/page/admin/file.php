<!-- /.row -->
<div class="row">
	<?php if(PAGE_action==NULL) { ?>
    
    <div class="col-lg-12">
    
        <p>
        <?php /*?><a href="#" rel="toggle-multi"><button class="btn btn-primary" type="button"><span class="fas fa-plus-circle"></span> Upload Files</button></a><?php */?>
        <a href="<?php echo $zulu->link_page('file',array('query'=>array('Action'=>'edit','Type'=>'file'))); ?>"><button class="btn btn-primary" type="button"><span class="fas fa-plus-circle"></span> New File</button></a>
        <a href="<?php echo $zulu->link_page('file',array('query'=>array('Action'=>'edit','Type'=>'folder','FileRoot'=>$class_file->root_id))); ?>"><button class="btn btn-primary" type="button"><span class="fas fa-plus-circle"></span> New Directory</button></a>
        </p>
        
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
            	<div class="col-lg-12">
    				<?php echo $zulu->template->body; ?>
				</div>
    		</div>
        </div>
		<div class="panel panel-default panel-info">
            	<div class="panel-heading"><span class="fas fa-upload"></span> <i>Quick</i> File Uploader</div>
                <div class="panel-body">
                	<p>Select files below and they will instantly loaded to this directory.</p>
              <form action="<?php echo $zulu->link_page('file',array('query'=>array('Action'=>'edit','FileRoot'=>$class_file->root_id,'New'=>1))); ?>"
              class="dropzone"
              method="post"
              id="myDropzone"
              enctype="multipart/form-data">
                    
                    <div class="form-group fallback">
                        <label>Upload File</label>
                        <input type="file" name="file" />
                        <input type="submit" value="Upload" />
                    </div>
                   
                    </form>
    			</div>
        	</div>
        
    </div>
    <?php } ?>
	<?php if(PAGE_action=='quick') { ?>
    
    <div class="col-lg-12">
    	<div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
            	<div class="col-lg-12">
    				<?php echo $zulu->template->body; ?>
				</div>
    		</div>
        </div>
    </div>
    <?php } ?>
    
   		<?php if(PAGE_action=='edit') { ?>
        	<form role="form" action="" method="post">
			<?php $form_edit->directoryOptionForm('',['object'=>$dir_object,'object_id'=>$dir_object_id]); ?>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Name</label>
                        <?php echo $form_edit->input_html("input","name",$_POST['name'],array('autofocus'=>true)); ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label><?php if(!$class_file->file_is) { ?>Parent <?php } ?>Folder</label>
                        <?php echo $form_edit->input_html("select","parent_id",($_GET['FileRoot']>0?$_GET['FileRoot']:$_POST['parent_id']),array("option"=>$output)); ?>
                    </div>
                </div>
            </div>	
                
			<?php echo $form_edit->input_html("submit","save","<span class=\"fas fa-save\"></span> Save",array("class"=>array("btn-success"))); ?>
            <?php echo $form_edit->input_html("hidden","action",'edit'); ?>
            <?php echo $form_edit->input_html("hidden","type",(isset($_GET['Type'])?$_GET['Type']:'file')); ?>
            
            </form>
            
            <?php if($class_file->file_is) { ?>
            <hr>
            
            <?php if(!$class_file->this_file->_exists) { ?>
            <div class="panel panel-default panel-info">
            	<div class="panel-heading"><span class="fas fa-upload"></span> Upload File</div>
                <div class="panel-body">
                <?php if($new) { ?>
                <p class="no-margin"><span class="fas fa-info-circle"></span> Setup your filename above, press 'Save' then you can upload a file here.</p>
                <?php } else { ?>
                    <form action="<?php echo $zulu->link_page('file',array('query'=>array('Action'=>'edit','id'=>$id))); ?>"
              class="dropzone"
              method="post"
              id="myDropzone"
              enctype="multipart/form-data">
                    
                    <div class="form-group fallback">
                        <label>Upload File</label>
                        <input type="file" name="file" />
                        <input type="submit" value="Upload" />
                    </div>
                   
                    </form>
              		<?php } ?>
    			</div>
        	</div>
            <?php } else { ?>
            <div class="panel panel-green">
            	<div class="panel-heading">Uploaded File</div>
                <div class="panel-body">
                	<a href="<?php echo $class_file->file_button($id); ?>"><button class="btn btn-primary" type="button"><span class="fas fa-download"></span> Download File</button></a>
                	<a class="confirm-delete" href="<?php echo $zulu->link_page('file',array('query'=>array('id'=>$id,'Action'=>'delete'))); ?>"><button class="btn btn-danger" type="button"><span class="fas fa-times"></span> Delete File</button></a>
    			</div>
        	</div>
            <?php } ?>
		<?php } } ?>
        <?php if(PAGE_action=='quick_edit') { ?>
        <div class="col-lg-12">
        	<form role="form" action="" method="post">
				<div class="row">
					<div class="col-lg-6">
						<p class="lead">Set the conditions of viewing the file/directory.</p>
					</div>
					<div class="col-lg-6 text-right">
						<?php echo $form_edit->input_html("submit","submit",'<i class="fas fa-save"></i> Save',['class'=>['btn-success']]); ?>
						<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
						<?php echo $form_edit->input_html("hidden","id",$id,['custom'=>['id'=>'quick_access_file_id']]); ?>
						<?php echo $form_edit->input_html("hidden","type",(isset($_GET['Type'])?$_GET['Type']:'file')); ?>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="panel panel-primary">
							<div class="panel-heading"><span class="fas fa-lock"></span> Settings</div>
							<div class="panel-body">
								<div class="row">
									<div class="col-lg-6">
										<div class="form-group">
											<label>Short Description</label>
											<?php echo $form_edit->input_html("input","tag",stripslashes($_POST['tag']),array('autofocus'=>true)); ?>
										</div>
									</div>
								</div>	
							</div>
							
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-6">
						<div class="panel panel-warning">
							<div class="panel-heading"><span class="fas fa-lock"></span> Viewing Conditions</div>
							<div class="panel-body">
								<div class="row">
									<div class="col-lg-4">
										<div class="form-group">
											<label>Password</label>
											<?php echo $form_edit->input_html("input","password",$_POST['password'],['custom'=>['placeholder'=>'Only enter if changing']]); ?>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<label>Expiry Date</label>
											<?php echo $form_edit->input_html("input","expire",($_POST['expire']!=NULL?$zulu->dateDecode($_POST['expire']):NULL),array('placeholder'=>'DD/MM/YYYY','class'=>['date input-date'])); ?>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<label>Max Downloads</label>
											<?php echo $form_edit->input_html("number","max_count",$_POST['max_count']); ?>
										</div>
									</div>
								</div>	
							</div>
							<div class="panel-footer">
								<span class="color-grey"><i class="fas fa-info-circle"></i> All conditions must be met if defined.</span>
							</div>
						</div>				
					</div>
					<div class="col-lg-6">
						<div class="panel panel-info">
							<div class="panel-heading"><i class="fas fa-globe" aria-hidden="true"></i> IP Address Whitelist</div>
							<div class="panel-body">
								<div class="row">
									<div class="col-lg-8">
										<div class="form-group">
											<label>IP Address</label>
											<?php echo $form_edit->input_html("input","whitelist_ips[]","",array('id'=>'first_ip','class'=>[''])); ?>
										</div>
									</div>
									<div class="col-lg-4">
										<label>&nbsp;</label>
										<button class="add_field_button btn btn-primary btn-block"><i class="fas fa-plus" aria-hidden="true"></i> Add</button>
									</div>
								</div>
								<div class="row">
									<div id="input_fields_wrap" class="col-lg-12">
										
									</div>
								</div>
							</div>
							<div class="panel-footer">
								<span class="color-grey"><i class="fas fa-info-circle"></i> Only IP addresses provided will have access. If Whitelist is left empty all addresses will have access.</span>
							</div>
						</div>
						
					</div>
				</div>
				
            </form>
		</div>
        <?php } ?>
	<?php if(PAGE_action=='download') { ?>
	<div class="col-md-12">
		<?php if($require_password) { ?>
		<form role="form" action="" method="post">
		<div class="panel panel-warning">
			<div class="panel-heading"><span class="fas fa-lock"></span> This file is password protected...</div>
			<div class="panel-body">
				<div class="form-group">
					<label>Please enter the password required to access this file:</label>
					<?php echo $form_edit->input_html("password","password",$_POST['password']); ?>
				</div>
			</div>
		</div>  
		<?php echo $form_edit->input_html("submit","submit",'Confirm &amp; Download File',['class'=>['btn','btn-success']]); ?>
		<?php echo $form_edit->input_html("hidden","action",'download'); ?>

		</form>
		<?php } ?>
	</div>
	<?php } ?>
