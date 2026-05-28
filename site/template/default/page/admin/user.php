<!-- /.row -->
<div class="row">
	<?php if(PAGE_action==NULL) { ?>

    <div class="col-lg-12">
		<p><a href="<?php echo $zulu->link_page('user',array('query'=>array('Action'=>'edit'))); ?>"><button class="btn btn-primary" type="button"><span class="fas fa-plus-circle"></span> Add New User</button></a></p>
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
    			<?php echo $zulu->template->body; ?>
            </div>
    	</div>
    </div>
    <?php } elseif(PAGE_action=='edit') { ?>
    <div class="col-md-<?php echo (USER_admin?'6':'12'); ?>">
        <form role="form" action="" method="post">
        	<div class="panel panel-default">
            	<div class="panel-heading"><i class="fas fa-user"></i> User Information</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Username</label>
                                <?php echo $form_edit->input_html("input","username",$_POST['username']); ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Email</label>
                                <?php echo $form_edit->input_html("input","email",$_POST['email']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <?php echo $form_edit->input_html("password","password",$_POST['password']); ?>
                        <?php if(!$new) { ?><p class="help-block">Only required if you want to change the password.</p><?php } ?>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <?php echo $form_edit->input_html("password","password_c",$_POST['password_c']); ?>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>First Name</label>
                                <?php echo $form_edit->input_html("input","name_first",$_POST['name_first']); ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Last Name</label>
                                <?php echo $form_edit->input_html("input","name_last",$_POST['name_last']); ?>
                            </div>
                        </div>
                    </div>
            	</div>
            </div>

        	<div class="panel panel-warning">
            	<div class="panel-heading"><i class="fas fa-users"></i> Roles &amp; Group</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                               <label>Group</label>
                                <?php echo $form_edit->input_html("select","group",$_POST['group'],array('option'=>$USER_group)); ?>
                            </div>
                        </div>
                        <?php if(USER_admin) { ?>
                        <div class="col-sm-6">
                            <div class="form-group">
                               <label>Role</label>
                                <?php echo $form_edit->input_html("select","role",$_POST['role'],array('option'=>$USER_option)); ?>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
            	</div>
            </div>

        	<div class="panel panel-info">
            	<div class="panel-heading"><i class="far fa-user"></i> Personal Settings</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                               <label>Email Signature</label>
                                <?php echo $form_edit->input_html("htmlarea","meta[email_signature]",$_POST['meta']['email_signature']); ?>
                            </div>
                        </div>
					</div>
				</div>
			</div>

			<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['class'=>['btn btn-success']]); ?>
			<?php echo $form_edit->input_html("reset","reset",'Reset'); ?>
			<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
          <p>&nbsp;</p>
           </div>
			<?php if(USER_admin) { ?>
           <div class="col-md-6">
           		<div class="panel panel-warning">
                	<div class="panel-heading">
            		<span class="fas fa-wrench"></span> Features <a href="#" rel="toggle-input"><button class="btn btn-default btn-xs" type="button"><span class="fas fa-check"></span> / <span class="fas fa-times"></span></button></a></div>
                    <div class="panel-body">
                        <div class="row">
                        	<?php foreach($class_subscribe->OPTION as $opt) { ?>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label><?php echo ucfirst(str_replace("opt_","",$opt)); ?></label>
                                    <?php echo $form_edit->input_html("checkbox",$opt,1,array('custom'=>($_POST[$opt]?array('checked'=>'checked'):NULL))); ?>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                              <div class="form-group">
                                    <label>Master Mode</label>
                                    <?php if($_POST['plan_id']>0) { ?>
                                    <p class="opt opt-grey">Automatic (<?php echo $plan_data['master_mode']; ?>)</p>
                                    <?php } else { ?>
                                    <?php echo $form_edit->input_html("input","plan_master_mode",$_POST['plan_master_mode']); ?>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>API Key</label>
                                    <?php echo $form_edit->input_html("input","api_key",$_POST['api_key']); ?>
                                </div>
                            </div>
                         </div>
            		</div>
                </div>
           		<div class="panel panel-info">
                	<div class="panel-heading">
            		<span class="fas fa-user"></span> Account</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Expiry</label>
                                    <?php echo $form_edit->input_html("input","plan_expiry",($_POST['plan_expiry']>0?zulu::dateDecode($_POST['plan_expiry']):NULL),array('class'=>array('date'),'placeholder'=>'DD/MM/YYYY')); ?>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Plan</label>
                                    <?php echo $form_edit->input_html("select","plan_id",$_POST['plan_id'],array('option'=>array(''=>"No Plan, Feature Based")+$class_subscribe->plan_array())); ?>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label>Trial Used?</label>
                                    <?php echo $form_edit->input_html("checkbox","subscribe_trial",1,array('custom'=>($_POST['subscribe_trial']?array('checked'=>'checked'):NULL))); ?>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label>Free</label>
                                    <?php echo $form_edit->input_html("checkbox","plan_free",1,array('custom'=>($_POST['plan_free']?array('checked'=>'checked'):NULL))); ?>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label>Cancelled</label>
                                    <?php echo $form_edit->input_html("checkbox","plan_cancelled",1,array('custom'=>($_POST['plan_cancelled']?array('checked'=>'checked'):NULL))); ?>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h3>Braintree Configuration</h3>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Braintree ID</label>
                                    <?php echo $form_edit->input_html("input","subscribe_braintree_id",$_POST['subscribe_braintree_id']); ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>GST Exempt? (only prior to subscribing)</label>
                                    <?php echo $form_edit->input_html("checkbox","subscribe_gst_exempt",1,['checked'=>($_POST['subscribe_gst_exempt']>0?true:false)]); ?>
                                </div>
                            </div>
                       </div>
                    </div>
                    <div class="panel-footer">
                    	<p><span class="fas fa-exclamation-triangle"></span> Warning</P>
                        <ul class="notes">
                        <li>Selecting a plan will override the features selected.</li>
                        <li>'Free' will override any expiry and give user infinite access.</li>
                        <li>Any Braintree ID specified will override the accounts expiry date on renew.</li>
                        </ul>
                    </div>
                </div>
            </div>
			<?Php } ?>
        </form>

    <?php } elseif(PAGE_action=='user_role' && $class_user->authorised->role == 'admin') { ?>
    <div class="col-lg-12">
		<p><a href="<?php echo $zulu->link_page('user',array('query'=>array('Action'=>'user_role_edit'))); ?>"><button class="btn btn-primary" type="button"><span class="fas fa-plus-circle"></span> Add User Role</button></a></p>
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
    			<?php echo $zulu->template->body; ?>
            </div>
    	</div>
    </div>
    <?php } elseif(PAGE_action=='user_group' && $class_user->authorised->role != 'admin') { ?>
    <div class="col-lg-12">
		<p><a href="<?php echo $zulu->link_page('user',array('query'=>array('Action'=>'user_group_edit'))); ?>" class="btn btn-primary"><span class="fas fa-plus-circle"></span> Add User Group</a></p>
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
    			<?php echo $zulu->template->body; ?>
            </div>
    	</div>
    </div>
    <?php } elseif(PAGE_action=='user_role_edit' && $class_user->authorised->role == 'admin') { ?>
    <div class="col-lg-12">
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

			<?php echo $form_edit->input_html("submit","submit",'Save'); ?>
			<?php echo $form_edit->input_html("reset","reset",'Reset'); ?>

			<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
        </form>
    </div>
    <?php }  elseif(PAGE_action=='user_group_edit' && $class_user->authorised->role == 'client') { ?>
    <div class="col-lg-12">
        <form role="form" action="" method="post">
        	<div class="panel panel-default">
            	<div class="panel-heading">
                	<i class="fas fa-user"></i> Information
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Group Name</label>
                                <?php echo $form_edit->input_html("input","name",$_POST['name']); ?>
                            </div>
                        </div>
                    </div>
            	</div>
            </div>
        	<div class="panel panel-danger">
            	<div class="panel-heading">
                	<i class="fas fa-exclamation-triangle"></i> Permissions
                </div>
                <div class="panel-body">
                    <div class="row">
                    	<?php foreach($class_user->config->permission_rules as $perm_tag=>$perm_rule) { ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="checkbox-inline"><?php echo $form_edit->input_html("checkbox","data[".$perm_tag."]",1,['checked'=>($_POST['data'][$perm_tag]>0||!isset($_POST['data'][$perm_tag])?true:false)]); ?> <?php echo $perm_rule['label']; ?></label>

                            </div>
                        </div>
                        <?php } ?>
                    </div>
            	</div>
                <div class="panel-footer">
                	Tick to <b>give</b> permission.
                </div>
            </div>

			<?php echo $form_edit->input_html("submit","submit",'Save'); ?>
			<?php echo $form_edit->input_html("reset","reset",'Reset'); ?>

			<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
        </form>
    </div>
    <?php } else { ?>
    <div class="col-lg-12">
    	<p>Sorry, this page was not found. Please try again. Return <a href="<?php echo $zulu->link_page(PAGE_file); ?>">here</a>.</p>
    </div>
    <?php } ?>
</div>
