<!-- /.row --> 
<?php if($_GET['Tab']=='ip_ban') { ?>
	<div class="row">
        <div class="col-md-12">
            <ul class="nav nav-tabs" style="margin-bottom:10px;">
            <?php foreach($tab_list as $key=>$name) { ?>
            <li class="<?php echo ($_GET['Tab']==$key?"active":NULL); ?>"><a class="layout-load" data-template="<?php echo $key; ?>" href="<?php echo $zulu->link_page(PAGE_file,['query'=>["Tab"=>$key]]); ?>"><?php echo $name; ?></a></li>
            <?php } ?>
            </ul>
        </div>
    </div>
	<?php if(PAGE_action==NULL) { ?>
	<div class="col-lg-12">
		<p>
        	<a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Tab'=>$_GET['Tab'],'Action'=>'edit'))); ?>"><button class="btn btn-primary" type="button"><span class="fas fa-plus-circle"></span> New IP Ban</button></a>
        </p>
        <form action="" method="post">
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
    			<?php echo $zulu->template->body; ?>
            </div>
    	</div>
        
        <?php if($zulu->vars->client_count>0) { ?>
        <div class="panel panel-info panel-checkbox-action-box">
        	<div class="panel-heading"> <span class="fas fa-fire"></span> Select an action to perform on selected items...</div>
        	<div class="panel-body">
            	<div class="form-group">
                	<?php echo $form_edit->input_html("select","execute",$_POST['execute'],array('option'=>array(0=>'None','delete'=>'Delete'))); ?>
               </div>
				<?php echo $form_edit->input_html("submit","submit_complete",'<span class="fas fa-check"></span> Confirm',array('class'=>array('btn-info'))); ?>
        	</div>
        </div>
        <?php } ?>
        </form>
    </div>
	<?php } ?>
    <?php if(PAGE_action=='edit') { ?>
     <form role="form" action="" method="post">
     	<div class="col-lg-6">
    		<div class="row">
    			<div class="col-lg-6">
                    <div class="form-group">
                        <label>IP Address</label>
                		<?php echo $form_edit->input_html("input","ip",$_POST['ip']); ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Type</label>
                		<?php echo $form_edit->input_html("select","type",$_POST['type'],['option'=>$class_ip->types]); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Note</label>
                		<?php echo $form_edit->input_html("textarea","note",stripslashes($_POST['note']),array()); ?>
                    </div>
                </div>
            </div>
            
            <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Tab'=>$_GET['Tab']))); ?>"><button class="btn btn-primary" type="button"><span class="fas fa-chevron-left"></span> Back</button></a>
			<?php echo $form_edit->input_html("submit","submit",'Save'); ?>
			<?php echo $form_edit->input_html("reset","reset",'Reset'); ?>
            
			<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
    	</div>
     </form>
    <?php } ?>
<?php } elseif(PAGE_action==NULL) { ?>
<form role="form" action="" method="post" enctype="multipart/form-data">
<div class="row">
	<div class="col-md-12">
    	<ul class="nav nav-tabs" style="margin-bottom:10px;">
		<?php foreach($tab_list as $key=>$name) { ?>
        <li class="<?php echo ($selected_tab==$key?"active":NULL); ?>"><a class="layout-load" data-template="<?php echo $key; ?>" href="<?php echo $zulu->link_page(PAGE_file,['query'=>["Tab"=>$key]]); ?>"><?php echo $name; ?></a></li>
        <?php } ?>
        </ul>
    </div>
</div>

<?php if($selected_tab=='custom_fields') { ?>
<div class="row">
	<div class="col-md-6">
    	<!--payroll container-->
        <div class="panel panel-default">
			<div class="panel-heading"><i class="far fa-users"></i> Custom Fields For Contacts</div>
            <div class="panel-body">
            	<?php
				echo $custom_field_contact;
				?>
            </div>
        </div>   
    </div>
</div>
<?php } ?>
<?php if($selected_tab=='design') { ?>
<div class="row">
	<div class="col-md-12">
    	<div class="panel panel-default">
        	<div class="panel-heading">Adjust CRM Branding</div>
                <div class="panel-body">
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label>Logo Upload</label>
                            
                            <?php if($_POST['image']==NULL) { ?>
                            <?php echo $form_edit->input_html("file","image"); ?>
                            <?php } else { ?>
                            <p><span class="opt opt-success"><span class="fas fa-check"></span> Logo Uploaded</span> <a target="_blank" href="<?php echo $class_file->file_root_user_rel.$class_user->authorised->id."/".$_POST['image']; ?>" class="btn btn-xs btn-warning">View</a> <a href="<?php echo $zulu->link_page('setting',array('query'=>array('Method'=>'DeleteImage','Image'=>$_POST['image']))); ?>" class="btn btn-xs btn-danger">Remove</a></p>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label>Design Theme</label>
                            <?php echo $form_edit->input_html("select","setting[theme]",$_POST['theme'],array('option'=>array('razor'=>'Zulu Default','light'=>'Light','dark'=>'Dark','gloss'=>'Gloss','nature'=>'Nature'))); ?>
                        </div>
                    </div>
				</div>
            </div>
        </div>
        <div class="panel panel-default">
        	<div class="panel-heading">Print Document Branding</div>
                <div class="panel-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-xs-6">
                                 <div class="form-group">
                            <label>Logo Upload</label>
                            <?php if($_POST['quote_logo']==NULL) { ?>
                            <?php echo $form_edit->input_html("file","file_logo_quote"); ?>
                            <?php } else { ?>
                            <p><span class="opt opt-success"><span class="fas fa-check"></span> Logo Uploaded</span> <a target="_blank" href="<?php echo $class_file->file_root_rel; ?>../user/<?php echo $class_user->authorised->id; ?>/<?php echo $_POST['quote_logo']; ?>"><button type="button" class="btn btn-xs btn-warning">View</button></a> <a href="<?php echo $zulu->link_page('setting',array('query'=>array('Method'=>'DeleteQuoteLogo'))); ?>"><button type="button" class="btn btn-xs btn-danger">Remove</button></a></p>
                            <?php } ?>
                        </div>
                            </div>
                       </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>
<?php if($selected_tab=='xero') { ?>
<div class="row">
	<div class="col-md-12">
       
       	<?php if(XRO_APP_TYPE=='Public') { ?>
        <div class="panel panel-warning">
            <div class="panel-heading"><i class="fas fa-link"></i> Xero&reg; Easy Connect</div>
            <div class="panel-body">
            	
                <?php echo $class_xero->login_html(); ?>
            	
            </div>
        </div>
        <?php } ?>
		<div class="panel panel-warning">
			<div class="panel-heading"><i class="fas fa-cube"></i> Xero&reg; Private Connection</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-xs-6">
						<div class="form-group">
							<label>Consumer Key</label>
							<?php echo $form_edit->input_html("input","setting[xero_shared_key]",$_POST['xero_shared_key']); ?>
						</div>
					</div>
                    <div class="col-xs-6">
						<div class="form-group">
							<label>Consumer Secret</label>
							<?php echo $form_edit->input_html("input","setting[xero_consumer_key]",$_POST['xero_consumer_key']); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="panel panel-default">
			<div class="panel-heading"><i class="fas fa-cog"></i> Taxation</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-xs-12">
						<div class="form-group">
							<label>Default Account <?php echo $form_edit->icon_help("Enter the default account code for sales - i.e. 200"); ?></label>
							<?php echo $form_edit->input_html("input","setting[xero_default_acc_code]",$_POST['xero_default_acc_code']); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="panel panel-default">
			<div class="panel-heading"><i class="fas fa-cog"></i> Sales Setup</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-xs-6">
						<div class="form-group">
							<label>New Sale Exports Automatically <?php echo $form_edit->icon_help("This will automatically post the sale to Xero."); ?></label>
							<?php echo $form_edit->input_html("select","setting[xero_sale_post]",$_POST['xero_sale_post'],['option'=>[0=>'No',1=>'Yes']]); ?>
						</div>
					</div>
					<div class="col-xs-6">
						<div class="form-group">
							<label>New Sale Export Status <?php echo $form_edit->icon_help("What status should the sale export as?"); ?></label>
							<?php echo $form_edit->input_html("select","setting[xero_sale_post_approved]",$_POST['xero_sale_post_approved'],['option'=>['0'=>'Draft','1'=>'Approved']]); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="panel-footer">
				<p class="no-margin"><b>Please note:</b><br>Automatically exporting to Xero can sometimes slow the page response down by a few seconds, please don't be alarmed.</p>
			</div>
		</div>
		
		<div class="panel panel-green">
			<div class="panel-heading"><i class="fas fa-bolt"></i> Actions</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-xs-3">
						<a href="<?php echo $zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'xero_stock_pull']]); ?>" class="btn-block btn btn-default confirm"><i class="fas fa-arrows"></i> Adjust Stock from Xero&reg;</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>

<?php if($selected_tab=='misc') { ?>
<div class="row">
	<div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading"><i class="far fa-envelope"></i> Outgoing Mail Settings</div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-lg-9 border-right-lg">
                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <div class="form-group">
                                    <label>Email Address <?= $form_edit->icon_help("Email address to send from."); ?></label>
                                    <?php echo $form_edit->input_html("email","setting[smtp_email]",$_POST['smtp_email'],['placeholder'=>$class_setting->data['contact_email']]); ?>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="form-group">
                                    <label>Method <?= $form_edit->icon_help("The method of sending emails to use. SMTP is recommended."); ?></label>
                                    <?php echo $form_edit->input_html("select","setting[smtp_method]",$_POST['smtp_method'],['option'=>['php_mail'=>'Default mail function','smtp'=>'Send Email via SMTP'],'id'=>'smtp-method']); ?>
                                </div>
                            </div>
                        </div>
                        <div id="smtp-block"<?= ($_POST['smtp_method']!='smtp'?' hidden':null); ?>>
                            <div class="row">
                                <div class="col-lg-12">
                                    <p>Your email provider will be able to supply the correct setting to use.</p>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group">
                                        <label>SMTP Host <?= $form_edit->icon_help("Server that will send the emails."); ?></label>
                                        <?php echo $form_edit->input_html("input","setting[smtp_host]",$_POST['smtp_host']); ?>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-3 col-xs-6">
                                    <div class="form-group">
                                        <label>Encryption <?= $form_edit->icon_help("The type of encryption to use."); ?></label>
                                        <?php echo $form_edit->input_html("select","setting[smtp_encryption]",$_POST['smtp_encryption'],['option'=>[''=>'None','tls'=>'TLS','ssl'=>'SSL'],'id'=>'smtp-encryption']); ?>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-3 col-xs-6">
                                    <div class="form-group">
                                        <label>Port Number <?= $form_edit->icon_help("Port to connect to the email server."); ?></label>
                                        <?php echo $form_edit->input_html("number","setting[smtp_port]",$_POST['smtp_port'],['id'=>'smtp-port']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group">
                                        <label>Username <?= $form_edit->icon_help("This is typically the full email address."); ?></label>
                                        <?php echo $form_edit->input_html("input","setting[smtp_username]",$_POST['smtp_username']); ?>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group">
                                        <label>Password <?= $form_edit->icon_help("Password is typically the same as the password to retrieve the email."); ?></label>
                                        <?php echo $form_edit->input_html("password","setting[smtp_password]",$_POST['smtp_password']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <hr class="hidden-lg">
                        <div class="form-group">
                            <label>Send Test Email</label>
                            <?php echo $form_edit->input_html("email","email_test",$_POST['email_test'],['placeholder'=>$class_setting->data['contact_email']]); ?>
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-info" id="smtp-test"><span class="fas fa-paper-plane"></span> Send Test</button>
                        </div>
                    </div>
                </div>
            </div>
		</div>
    	<div class="panel panel-default">
            <div class="panel-heading"><i class="fab fa-mailchimp"></i> MailChimp&reg; Integration</div>
            <div class="panel-body">
            	<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label>API Key</label>
							<?php echo $form_edit->input_html("input","setting[mc_api_key]",$_POST['mc_api_key'],['placeholder'=>'MailChimp API key...']); ?>
						</div>
					</div>
				</div>
            	<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							<label>List ID for all contacts<?php if($_POST['mc_list_master']!=NULL) { ?> <a href="<?php echo $zulu->link_page(PAGE_file,['self'=>true,'query'=>['Method'=>'mc_sync']]); ?>" title="Click here to sync this list with MailChimp" class="btn btn-default btn-xs"><i class="fas fa-arrows-h"></i> Sync Now</a><?php } else { ?> <a href="#" title="Enter a list unique id first..." class="btn btn-default btn-xs disabled"><i class="fas fa-exclamation-triangle"></i> No list id</a><?php } ?></label>
							<?php echo $form_edit->input_html("input","setting[mc_list_master]",$_POST['mc_list_master'],['placeholder'=>'Enter unique id from MailChimp']); ?>
						</div>
					</div>
					<?php foreach($class_client->config->type as $slug=>$type) { ?>
					<div class="col-md-4">
						<div class="form-group">
							<label>List ID for '<?php echo $type; ?>'<?php if($_POST['mc_list_'.$slug]!=NULL) { ?> <a href="<?php echo $zulu->link_page(PAGE_file,['self'=>true,'query'=>['Method'=>'mc_sync','list'=>$slug]]); ?>" title="Click here to sync this list with MailChimp" class="btn btn-default btn-xs"><i class="fas fa-arrows-h"></i> Sync Now</a><?php } else { ?> <a href="#" title="Enter a list unique id first..." class="btn btn-default btn-xs disabled"><i class="fas fa-exclamation-triangle"></i> No list id</a><?php } ?></label>
							<?php echo $form_edit->input_html("input","setting[mc_list_".$slug."]",$_POST['mc_list_'.$slug],['placeholder'=>'Enter unique id from MailChimp']); ?>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
            <div class="panel-footer">
            	<p></p><b><i class="fas fa-tag"></i> Merge tags you can setup in MailChimp for data synchronisation...</b><br>
				RATE - Hourly Rate, COMPANY - Company, TOKEN - Zulu Identifier, TYPE - Type of contact</p>
			</div>
		</div>
        <div class="panel panel-default">
            <div class="panel-heading"><i class="fas fa-info-circle"></i> Extra Settings</div>
            <div class="panel-body">
            	<div class="row">
					<div class="col-md-3">
						<div class="form-group">
							<label>Enable Price Breaks</label>
							<?php echo $form_edit->input_html("select","setting[price_break_enable]",$_POST['price_break_enable'],['option'=>['0'=>'No','1'=>'Yes']]); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
    </div>
</div>
<?php } ?>

<?php if($selected_tab=='general') { ?>
<div class="row">
    <div class="col-lg-6">
        <div class="panel panel-default">
        	<div class="panel-heading">Default Settings</div>
            <div class="panel-body">
    		<div class="row">
    			<div class="col-lg-6">
                    <div class="form-group">
                        <label>Site Name</label>
                		<?php echo $form_edit->input_html("input","setting[name]",stripslashes($_POST['name'])); ?>
                    </div>
                </div>
    			<div class="col-lg-6">
                    <div class="form-group">
                        <label>Company</label>
                		<?php echo $form_edit->input_html("input","setting[company]",stripslashes($_POST['company'])); ?>
                    </div>
                </div>
            </div>
    		<div class="row">
            	<div class="col-lg-4">
                    <div class="form-group">
                        <label>Contact Name</label>
                		<?php echo $form_edit->input_html("input","setting[contact_name]",$_POST['contact_name']); ?>
                    </div>
                </div>
    			<div class="col-lg-4">
                    <div class="form-group">
                        <label>Contact Email</label>
                		<?php echo $form_edit->input_html("input","setting[contact_email]",$_POST['contact_email']); ?>
                    </div>
                </div>
    			<div class="col-lg-4">
                    <div class="form-group">
                        <label>Contact Phone</label>
                		<?php echo $form_edit->input_html("input","setting[contact_phone]",$_POST['contact_phone']); ?>
                    </div>
                </div>
            </div>
            </div>
        </div>
        
        <!--locality container-->
        <div class="panel panel-default">
        	<div class="panel-heading">Locality Settings</div>
            <div class="panel-body">
            	<div class="row">
					<div class="col-xs-3">
						<div class="form-group">
							<label>Address</label>
							<?php echo $form_edit->input_html("input","setting[ship_address]",$_POST['ship_address']); ?>
						</div>
					</div>
					<div class="col-xs-3">
						<div class="form-group">
							<label>Suburb</label>
							<?php echo $form_edit->input_html("input","setting[ship_suburb]",$_POST['ship_suburb']); ?>
						</div>
					</div>
					<div class="col-xs-3">
						<div class="form-group">
							<label>City</label>
							<?php echo $form_edit->input_html("input","setting[ship_city]",$_POST['ship_city']); ?>
						</div>
					</div>
					<div class="col-xs-3">
						<div class="form-group">
							<label>Postcode</label>
							<?php echo $form_edit->input_html("input","setting[ship_post]",$_POST['ship_post']); ?>
						</div>
					</div>
				</div>
            	<div class="row">
					<div class="col-xs-6">
						<div class="form-group">
							<label>Country</label>
							<?php echo $form_edit->input_html("select","setting[country]",$_POST['country'],['option'=>$class_setting->defaults->country]); ?>
						</div>
					</div>
					<div class="col-xs-6">
						<div class="form-group">
							<?php /*?><label>Currency Format</label>
							<?php echo $form_edit->input_html("select","setting[currency_symbol]",$_POST['currency_symbol'],['option'=>$class_setting->defaults->currency_symbol]); ?><?php */?>
							<label>Currency Code</label>
							<?php echo $form_edit->input_html("select","setting[currency_code]",$_POST['currency_code'],['option'=>$currency_options]); ?>
						</div>
					</div>
				</div>
            </div>
        </div>
        
        <!--taxation container-->
        <div class="panel panel-default">
        	<div class="panel-heading">Tax Settings</div>
            <div class="panel-body">
            	<div class="row">
    			<div class="col-md-4">
                    <div class="form-group">
                        <label>Disable Taxation</label>
						<label class="checkbox-inline display-block"><?php echo $form_edit->input_html("checkbox","setting[tax_disable]",1,['checked'=>($_POST['tax_disable']>0?true:false)]); ?> Yes, disable tax</label>
                    </div>
                </div>
    			<div class="col-md-4">
                    <div class="form-group">
                        <label>Tax Label</label>
                		<?php echo $form_edit->input_html("input","setting[tax_label]",$_POST['tax_label']); ?>
                    </div>
                </div>
    			<div class="col-md-4">
                    <div class="form-group">
                        <label>Tax Rate (%)</label>
                		<?php echo $form_edit->input_html("input","setting[tax_rate]",$_POST['tax_rate']); ?>
                    </div>
                </div>
    			<div class="col-md-4">
                    <div class="form-group">
                        <label>Tax Method</label>
                		<?php echo $form_edit->input_html("select","setting[tax_method]",$_POST['tax_method'],array('option'=>array(0=>"Excl",1=>"Incl"))); ?>
                    </div>
                </div>
    			<div class="col-md-4">
                    <div class="form-group">
                        <label>Tax Number</label>
                		<?php echo $form_edit->input_html("input","setting[tax_number]",$_POST['tax_number']); ?>
                    </div>
                </div>
            </div>
            </div>
        </div>
         
    </div>
	<div class="col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading">Legal / Invoice Settings</div>
            <div class="panel-body">
                <div class="form-group">
                    <label>Terms of Trade Upload</label>
                    <?php if($_POST['quote_terms_file']==NULL) { ?>
                    <?php echo $form_edit->input_html("file","file_terms"); ?>
                    <?php } else { ?>
                    <p><span class="opt opt-success"><span class="fas fa-check"></span> File Uploaded</span> <a target="_blank" href="<?php echo $class_file->file_root_rel; ?>../user/<?php echo $class_user->authorised->id; ?>/<?php echo $_POST['quote_terms_file']; ?>"><button type="button" class="btn btn-xs btn-warning">View</button></a> <a href="<?php echo $zulu->link_page('setting',array('query'=>array('Method'=>'DeleteTerms'))); ?>"><button type="button" class="btn btn-xs btn-danger">Remove</button></a></p>
                    <?php } ?>
                </div>
                <div class="form-group">
                    <label>Payment Advice / Terms</label>
                    <?php echo $form_edit->input_html("htmlarea","setting[sale_footer]",$_POST['sale_footer']); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<div class="row">
    <div class="col-md-12">
    	<div class="form-group">
			<?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Save Settings",array('class'=>array('btn-success'))); ?>
			<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
        </div>
    </div>
</div>
</form>
<?php } ?>
	