<!-- /.row -->
<div class="row">
	<?php if(PAGE_action==NULL || PAGE_action=='supplier' || PAGE_action=='prospect' || PAGE_action=='lead' || PAGE_action=='cancel' || PAGE_action=='xero') { ?>

    <div class="col-lg-12">
		<div class="row-margin">
       		<?php if(!$cancel) { ?>
        	<a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'edit','type'=>$new_label))); ?>" class="btn btn-primary inline-block"><span class="fas fa-plus-circle"></span> New <?php echo PAGE_label; ?></a>
        	<?php } ?>

			<?php if($class_xero->vars->enabled) { ?>
				<?php echo (!$lead&&!$cancel?"<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'xero')))."\" class=\"btn btn-default inline-block\"><span class=\"fas fa-sync-alt\"></span> Xero Sync</a>":NULL); ?>
			<?php } ?>

			<div class="button-inline-controller w300">
			   <form action="" method="get">
			   <div class="input-group">
					<?php echo $form_edit->input_html("input","Search",$_GET['Search'],['custom'=>['placeholder'=>'Search','id'=>'search-box']]); ?>
					<span class="input-group-btn"><?php echo $form_edit->input_html("submit","",'<span class="fas fa-search"></span>',['custom'=>['id'=>'search-submit']]); ?></span>
				</div>
				<?php echo $form_edit->input_hidden(['method'=>'GET','filter'=>['Search']]); ?>
				</form>
			</div>
		</div>

        <form action="" method="post">
        <?php if(PAGE_action=='xero') { ?>
        	<p><?php echo $form_edit->input_html("submit","save","Save"); ?></p>
        <?php } ?>
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
            	<?php if(count($CLIENT_option) > 0) { ?>
					<ul class="nav nav-tabs">
                        <li class="<?php echo (!isset($_GET['role'])?"active":NULL); ?>"><a href="<?php echo $zulu->link_page(PAGE_file,array('self'=>true,'filter'=>['role'])); ?>" class="" aria-expanded="false">All <?php echo PAGE_name; ?><?php /*<span class="bullet stat-0"><?php echo $nrow_no_role;*/ ?></span></a></li>
                    	<?php $i=0; foreach($CLIENT_option as $key=>$val) { $count = $class_client->client_count($client_type,$key); ?>
                        <li class="<?php echo ($_GET['role']==$key?"active":NULL); ?>"><a href="<?php echo $zulu->link_page(PAGE_file,array('self'=>true,'query'=>['role'=>$key])); ?>" class="" aria-expanded="false"><?php echo $val;//." <span class=\"bullet stat-0\">".$count."</span>"; ?></a></li>
                        <?php $i=1; } ?>
                        <?php //if($nrow_no_role > 0) { ?>
                        <li class="<?php echo ($_GET['role']=='none'?"active":NULL); ?>"><a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>($lead?'lead':($cancel?'cancel':NULL)),'role'=>'none'))); ?>" class="" aria-expanded="false">Ungrouped<?php /* <span class="bullet stat-0"><?php echo $nrow_no_role; ?></span>*/ ?></a></li>
                        <?php //} ?>
                    </ul>
				<?php } ?>

    			<?php echo $zulu->template->body; ?>

				<div align="center"><?php echo $pagination; ?></div>
				<p class="page-count text-center opt opt-grey"><i class="fas fa-bars"></i> <?php echo $zulu->vars->client_count; ?> record(s) in total</p>
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

        <?php if(PAGE_action=='xero') { ?>
        	<p><?php echo $form_edit->input_html("submit","save","Save"); ?><?php echo $form_edit->input_html("hidden","action","xero"); ?></p>
        <?php } ?>
        </form>
    </div>
    <?php } ?>
	<?php if(PAGE_action=='edit') { ?>
    <form role="form" action="" method="post">
	<div class="col-md-12">
    	<p>
    	<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['class'=>['btn btn-success']]); ?>
        <?php echo $form_edit->input_html("reset","reset",'Reset'); ?>
        </p>
    </div>

    <div class="col-lg-6">
      	<div class="panel panel-default">
        	<div class="panel-heading">Contact Details</div>
           <div class="panel-body">
               	<?php if($supplier) { ?>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label>Supplier Company </label>
                            <?php echo $form_edit->input_html("input","company",$_POST['company'],array('autoc_off'=>true)); ?>
                        </div>
                    </div>
                </div>
               	<?php } else { ?>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Type</label>
                            <?php echo $form_edit->input_html("select","type",($_POST?$_POST['type']:($lead?'2':($prospect?'3':'1'))),array('option'=>$class_client->config->type)); ?>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Company</label>
                            <?php echo $form_edit->input_html("input","company",$_POST['company'],array('autoc_off'=>true)); ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>First Name</label>
                            <?php echo $form_edit->input_html("input","name_first",$_POST['name_first'],array('autoc_off'=>true)); ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Last Name</label>
                            <?php echo $form_edit->input_html("input","name_last",$_POST['name_last'],array('autoc_off'=>true)); ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Phone</label>
                            <?php echo $form_edit->input_html("input","phone",$_POST['phone'],array('autoc_off'=>true)); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Mobile</label>
                            <?php echo $form_edit->input_html("input","mobile",$_POST['mobile'],array('autoc_off'=>true)); ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email</label>
                            <?php echo $form_edit->input_html("input","email",$_POST['email'],array('autoc_off'=>true)); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Website</label>
                            <?php echo $form_edit->input_html("input","website",$_POST['website'],array('autoc_off'=>true)); ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Referrer</label>
                            <?php echo $form_edit->input_html("select","refer",$_POST['refer'],array('option'=>$form_edit->referrerOptionForm())); ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Referring Client</label>
                            <?php echo $form_edit->input_html("select","client_id",$_POST['client_id'],array('option'=>$form_edit->clientOptionForm())); ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                	<div class="col-lg-6">
                        <div class="form-group">
                            <label>Hourly Rate</label>
                            <?php echo $form_edit->input_html("input","hourly_rate",$_POST['hourly_rate']); ?>
                        </div>
                    </div>
                	<div class="col-lg-6">
                        <div class="form-group">
                            <label><i class="far fa-id-card"></i> Client Reference</label>
                            <?php echo $form_edit->input_html("input","reference",$_POST['reference']); ?>
                        </div>
                    </div>
                  </div>
                   <div class="row">
                  <?php
					$custom_fields = $class_setting->custom_field['contact'];
					foreach($custom_fields as $field_id=>$field_value) {
						echo "<div class=\"col-lg-6\">
                        <div class=\"form-group\">
                            <label>".$field_value."</label>
                           	".$form_edit->input_html("input","meta[custom_field_".$field_id."]",$_POST['custom_field_'.$field_id])."
                        </div>
                    </div>";
					}
				   ?>
			   		</div>
            	</div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading"><i class="far fa-map-marker"></i> Contacts Addresses</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-4">
                           <div class="row">
                           		<div class="col-lg-6">
                           			<h4>Billing</h4>
                           		</div>
                           		<div class="col-lg-6 text-right">
                           			<button type="button" class="btn btn-default btn-xs copy_address" data-to="bill" data-from="ship"><i class="far fa-copy" aria-hidden="true"></i> Copy Ship</button>
                           		</div>
                           </div>
                           <div class="row">
                           		<div class="col-lg-12">
                           			<div class="form-group">
                            		<label>Address</label>
										<?php echo $form_edit->input_html("input","bill_address",$_POST['bill_address'],array('autoc_off'=>true,'id'=>'bill_address')); ?>
									</div>
									<div class="form-group">
										<label>Suburb</label>
										<?php echo $form_edit->input_html("input","bill_suburb",$_POST['bill_suburb'],array('autoc_off'=>true,'id'=>'bill_suburb')); ?>
									</div>
									<div class="form-group">
										<label>City</label>
										<?php echo $form_edit->input_html("input","bill_city",$_POST['bill_city'],array('autoc_off'=>true,'id'=>'bill_city')); ?>
									</div>
									<div class="form-group">
										<label>Postcode</label>
										<?php echo $form_edit->input_html("input","bill_post",$_POST['bill_post'],array('autoc_off'=>true,'id'=>'bill_post')); ?>
									</div>
                           		</div>
                           </div>

                        </div>
                        <div class="col-lg-4">
                           <div class="row">
                           		<div class="col-lg-6">
                            		<h4>Shipping</h4>
                           		</div>
                           		<div class="col-lg-6 text-right">
                           			<button type="button" class="btn btn-default btn-xs copy_address" data-to="ship" data-from="bill"><i class="far fa-copy" aria-hidden="true"></i> Copy Bill</button>
                           		</div>
                           </div>
                           <div class="row">
                           		<div class="col-lg-12">
                           			<div class="form-group">
										<label>Address</label>
										<?php echo $form_edit->input_html("input","ship_address",$_POST['ship_address'],array('autoc_off'=>true,'id'=>'ship_address')); ?>
									</div>
									<div class="form-group">
										<label>Suburb</label>
										<?php echo $form_edit->input_html("input","ship_suburb",$_POST['ship_suburb'],array('autoc_off'=>true,'id'=>'ship_suburb')); ?>
									</div>
									<div class="form-group">
										<label>City</label>
										<?php echo $form_edit->input_html("input","ship_city",$_POST['ship_city'],array('autoc_off'=>true,'id'=>'ship_city')); ?>
									</div>
									<div class="form-group">
										<label>Postcode</label>
										<?php echo $form_edit->input_html("input","ship_post",$_POST['ship_post'],array('autoc_off'=>true,'id'=>'ship_post')); ?>
									</div>
                           		</div>
                           </div>

                        </div>
                        <div class="col-lg-4">
                           <div class="row">
                           		<div class="col-lg-6">
                            		<h4>Physical</h4>
                           		</div>
                           		<div class="col-lg-6 text-right">
                           			<button type="button" class="btn btn-default btn-xs copy_address" data-to="phy" data-from="bill"><i class="far fa-copy" aria-hidden="true"></i> Copy Bill</button>
                           		</div>
                           </div>
                           <div class="row">
                           		<div class="col-lg-12">
                           			<div class="form-group">
										<label>Address</label>
										<?php echo $form_edit->input_html("input","phy_address",$_POST['phy_address'],array('autoc_off'=>true,'id'=>'phy_address')); ?>
									</div>
									<div class="form-group">
										<label>Suburb</label>
										<?php echo $form_edit->input_html("input","phy_suburb",$_POST['phy_suburb'],array('autoc_off'=>true,'id'=>'phy_suburb')); ?>
									</div>
									<div class="form-group">
										<label>City</label>
										<?php echo $form_edit->input_html("input","phy_city",$_POST['phy_city'],array('autoc_off'=>true,'id'=>'phy_city')); ?>
									</div>
									<div class="form-group">
										<label>Postcode</label>
										<?php echo $form_edit->input_html("input","phy_post",$_POST['phy_post'],array('autoc_off'=>true,'id'=>'phy_post')); ?>
									</div>
                           		</div>
                           </div>

                        </div>
                    </div>
                </div>
            </div>
            <?php if((WEBSITE&&(MASTER_mode=='web'||$class_user->authorised->opt_website))||$_POST['supplier']==1) { ?>
            <div class="panel panel-default">
                <div class="panel-heading"><i class="fas fa-laptop"></i> Website<?php echo ($_POST['supplier']>0?" / Supplier":NULL); ?> Access</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-4">
                            <div class="form-group">
                                <label>Password</label>
                                <?php echo $form_edit->input_html("password","password",'',array('placeholder'=>'Only enter if resetting...','autoc_off'=>true)); ?>
                            </div>
                        </div>
                        <div class="col-xs-2">
                            <div class="form-group">
                                <label>Email Verified</label>
                                <?php echo $form_edit->input_html("checkbox","web_verify",1,([$_POST['web_verify']==1?['custom'=>['checked'=>'checked']]:NULL])); ?>
                            </div>
                        </div>
						<div class="col-xs-2">
                            <div class="form-group">
                                <label>Phone Verified</label>
                                <?php echo $form_edit->input_html("checkbox","web_verify_phone",1,([$_POST['web_verify_phone']>0?['custom'=>['checked'=>'checked']]:NULL])); ?>
                            </div>
                        </div>
                        <div class="col-xs-2">
                            <div class="form-group">
                                <label>Web Access</label>
                                <?php echo $form_edit->input_html("checkbox","web_access",1,([$_POST['web_access']==1?['custom'=>['checked'=>'checked']]:NULL])); ?>
                            </div>
                        </div>
                        <div class="col-xs-2">
                            <div class="form-group">
                                <label>Trade Account</label>
                                <?php echo $form_edit->input_html("checkbox","trade_account",1,([$_POST['trade_account']==1?['custom'=>['checked'=>'checked']]:NULL])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
            <div class="panel panel-default">
                <div class="panel-heading"><span class="far fa-pencil"></span> Notes</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Notes</label>
                                <?php echo $form_edit->input_html("textarea","notes",stripslashes($_POST['notes']),array('custom'=>array('rows'=>4))); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

			<p><?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['class'=>['btn btn-success']]); ?> <?php echo $form_edit->input_html("reset","reset",'Reset'); ?></p>

			<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
            </form>
    </div>

    <div class="col-lg-6">
    	<?php if(PAGE_id > 0) { ?>
		<?php if(!$supplier) { ?>
      	<div class="panel panel-green">
			<div class="panel-heading"><i class="fas fa-chart-line"></i> Contact Stats</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-md-4 text-center">
						<div class="panel panel-default no-margin">
							<div class="panel-heading">
								Total Sales <a href="<?php echo $zulu->link_page('sale',['query'=>['filter[client_id]'=>PAGE_id]]); ?>" class="button btn-default btn-xs"><i class="fas fa-external-link-alt"></i></a>
							</div>
							<div class="panel-body">
								<p class="h2 no-margin"><?php echo $client_stat_html['sale']; ?></p>
							</div>
						</div>
					</div>
					<?php if(MASTER_mode=='main') { ?>
					<div class="col-md-4 text-center">
						<div class="panel panel-default no-margin">
							<div class="panel-heading">
								Total Tasks
							</div>
							<div class="panel-body">
								<p class="h2 no-margin"><?php echo $client_stat_html['task']; ?></p>
							</div>
						</div>
					</div>
					<div class="col-md-4 text-center">
						<div class="panel panel-default no-margin">
							<div class="panel-heading">
								Total Projects
							</div>
							<div class="panel-body">
								<p class="h2 no-margin"><?php echo $client_stat_html['project']; ?></p>
							</div>
						</div>
					</div>
					<?php } else { ?>
					<div class="col-md-4 text-center">
						<div class="panel panel-default no-margin">
							<div class="panel-heading">
								Total Visits
							</div>
							<div class="panel-body">
								<p class="h2 no-margin"><?php echo $client_stat_html['visit']; ?></p>
							</div>
						</div>
					</div>
					<div class="col-md-4 text-center">
						<div class="panel panel-default no-margin">
							<div class="panel-heading">
								Last Sign In
							</div>
							<div class="panel-body">
								<p class="h2 no-margin"><?php echo $client_stat_html['last']; ?></p>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php } ?>
      	<div class="panel panel-default">
			<div class="panel-heading"><i class="fas fa-bolt"></i> Contact Options</div>
           <div class="panel-body">
				<a href="<?php echo $zulu->link_page('sale',['query'=>["filter[client_id]"=>PAGE_id]]); ?>" class="btn btn-default"><i class="far fa-money-bill"></i> View Sales</a>
			</div>
		</div>
        <?php if(count($CLIENT_option) > 0) { ?>
            <div class="panel panel-default">
				<div class="panel-heading"><a class="collapsed" data-toggle="collapse" data-parent="#panel-group" href="#panel-group" aria-expanded="false"><span class="fas fa-users"></span> Groups</a></div>
				<div id="panel-group" class="panel-collapse collapse">
					<div class="panel-body">
						<form method="post" action="">
							<?php echo $role_html; ?>
							<div class="col-lg-6">
								<?php echo $form_edit->input_html("select","role",'',array('option'=>$CLIENT_option)); ?>
							</div>
							<div class="col-lg-2">
								<?php echo $form_edit->input_html("submit","submit",'<span class="fas fa-plus"></span>'); ?>
								<?php echo $form_edit->input_html("hidden","action",'add_role'); ?>
							</div>
						</form>
					</div>
				</div>
           </div>
        <?php } ?>
        <div class="panel panel-default">
			<div class="panel-heading"><a class="collapsed" data-toggle="collapse" data-parent="#panel-files" href="#panel-files" aria-expanded="false"><span class="fas fa-cloud-upload-alt"></span> Files</a></div>
			<div id="panel-files" class="panel-collapse collapse">
				<div class="panel-body">
					<p><a href="<?php echo $zulu->link_page('file',array('query'=>array('Action'=>'edit','Type'=>'file','Client'=>PAGE_id))); ?>"><button class="btn btn-primary" type="button"><span class="fas fa-plus-circle"></span> New File</button></a>
			<a href="<?php echo $zulu->link_page('file',array('query'=>array('Action'=>'edit','Type'=>'folder','FileRoot'=>$class_file->root_id,'Client'=>PAGE_id))); ?>"><button class="btn btn-primary" type="button"><span class="fas fa-plus-circle"></span> New Directory</button></a></p>
					<?php echo $zulu->template->file_table; ?>
					<?php //<a href="<?php echo $zulu->link_page('file',array('query'=>array('Action'=>'edit','Type'=>'file','Client'=>PAGE_id)));"><button class="btn btn-warning" type="button"><span class="fas fa-bolt"></span> Quick Upload</button></a>  ?>
				</div>
			</div>
       </div>
        <div class="panel panel-default">
			<div class="panel-heading"><a class="collapsed" data-toggle="collapse" data-parent="#panel-log" href="#panel-log" aria-expanded="false"><span class="fas fa-list"></span> Logs</a></div>
			<div id="panel-log" class="panel-collapse collapse">
				<div class="panel-body">
					<p><a href="<?php echo $zulu->link_page('client',array('query'=>array('id'=>$id,'Action'=>(PAGE_action=='lead_edit'?'lead_log':(PAGE_action=='cancel_edit'?'cancel_log':'log_edit')),'Type'=>'New'))); ?>"><button class="btn btn-primary" type="button"><span class="fas fa-plus-circle"></span> New Log</button></a></p>
			<?php echo $log_table; ?>
				</div>
			</div>
       </div>

        <?php if(WEBSITE && (MASTER_mode == 'web' || $class_user->authorised->opt_website)) { ?>
        <div class="panel panel-default">
			<div class="panel-heading"><a class="collapsed" data-toggle="collapse" data-parent="#panel-pass-reset" href="#panel-pass-reset" aria-expanded="false"><span class="fas fa-key"></span> Password Resets</a></div>
			<div id="panel-pass-reset" class="panel-collapse collapse">
				<div class="panel-body">
                    <p>
                        <a href="<?= $zulu->link_page(PAGE_file, ['self'=>true, 'query'=>['Do'=>'new-reset-link']]); ?>" class="btn btn-info btn-xs confirm"><i class="fas fa-plus-circle"></i> Generate Reset Link</a>
                    </p>
			        <?php echo $zulu->template->pr_table; ?>
				</div>
			</div>
        </div>
        <?php } ?>
        <?php } ?>
    <?php } ?>
    <?php if(PAGE_action=='log_edit' || PAGE_action=='lead_log' || PAGE_action=='cancel_log') { ?>
    <div class="col-lg-6">
        <form role="form" action="" method="post">
    		<div class="row">
    			<div class="col-lg-6">
                    <div class="form-group">
                        <label>Log</label>
                		<?php echo $form_edit->input_html("textarea","notes",$_POST['notes']); ?>
                    </div>
                </div>
            </div>

			<?php echo $form_edit->input_html("submit","submit",'Save'); ?>
			<?php echo $form_edit->input_html("reset","reset",'Reset'); ?>

			<?php echo $form_edit->input_html("hidden","action",'log_edit'); ?>
        </form>
    </div>
    <?php } ?>
    <?php if(PAGE_action=='client_role') { ?>
    <div class="col-lg-12">
		<p><a href="<?php echo $zulu->link_page('client',array('query'=>array('Action'=>'client_role_edit'))); ?>"><button class="btn btn-primary" type="button"><span class="fas fa-plus-circle"></span> Add Client Group</button></a></p>
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
    			<?php echo $zulu->template->body; ?>
            </div>
    	</div>
    </div>
    <?php } ?>
	<?php if(PAGE_action=='client_role_edit') { ?>
    <div class="col-lg-6">
        <form role="form" action="" method="post">
    		<div class="row">
    			<div class="col-lg-6">
                    <div class="form-group">
                        <label>Name</label>
                		<?php echo $form_edit->input_html("input","name",$_POST['name']); ?>
                    </div>
                </div>
    			<div class="col-lg-6">
                    <div class="form-group">
                        <label>Tag</label>
                		<?php echo $form_edit->input_html("input","tag",$_POST['tag']); ?>
                    </div>
                </div>
            </div>

            <a href="<?php echo $zulu->link_page(PAGE_file,['query'=>['Action'=>'client_role']]); ?>"><button class="btn btn-default bt-cancel" type="button"><span class="fas fa-times"></span> Cancel</button></a>
			<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['class'=>['btn btn-success']]); ?>
           			<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
        </form>
    </div>
    <?php } ?>
</div>
<br />
