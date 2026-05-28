<!-- /.row -->
<div class="row">
	<?php if(PAGE_action==NULL) { ?>
    
    <div class="col-lg-12">
		<p>
        	<a class="btn btn-primary" href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'edit'))); ?>"><span class="fas fa-plus-circle"></span> New Rule</a>
        	<a class="btn btn-default" href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'auto_rule'))); ?>"><span class="fas fa-cog"></span> Automated Rules</a>
        </p>
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
    			<?php echo $zulu->template->body; ?>
            </div>
    	</div>
    </div>
    <?php } ?>
	<?php if(PAGE_action=='edit') { ?>
    <form role="form" action="" method="post">
    <div class="col-md-9">
   		<div class="panel panel-default">
   			<div class="panel-heading">
   				<i class="fas fa-info-circle"></i> Rule settings
   			</div>
   			<div class="panel-body">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label>Client <em>*</em></label>
							<?php echo $form_edit->input_html("select","client_id",$_POST['client_id'],array('option'=>$form_edit->clientOptionForm(false,'all'))); ?>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label>Template <em>*</em> <?php echo $form_edit->icon_help("You can select a template below from the templates list you can create under 'Templates'."); ?></label>
							<?php echo $form_edit->input_html("select","template",$_POST['template'],array('class'=>['input-template'],'option'=>[''=>'None']+$form_edit->templateOptionForm()+$sequence_list)); ?>
						</div>
					</div>
				</div>
                
                <div class="row-rule-info">   
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Object <?php echo $form_edit->icon_help("Your template may have a 'object' in it, i.e. 'website', 'quote', 'booking'... This may not be required but can be used to customise the email further."); ?></label>
                                <?php echo $form_edit->input_html("input","object",$_POST['object']); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Frequency (Days) <em>*</em> <?php echo $form_edit->icon_help("What is the frequency this should go out on? For example: 7 would mean 1 week, 30 would mean every 30 days."); ?></label>
                                <?php echo $form_edit->input_html("input","frequency",$_POST['frequency']); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Max.<span class="lbl-loop"></span> Loop <?php echo $form_edit->icon_help("Enter 0 for infinite (i.e. no end). This will "); ?></label>
                                <?php echo $form_edit->input_html("input","loop_max",$_POST['loop_max'],['placeholder'=>'Leave blank for indefinate']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Start Override <?php echo $form_edit->icon_help("You can enter a custom date for the next mailer to send on."); ?></label>
                                <?php echo $form_edit->input_html("input","frequency_ovr",$_POST['frequency_ovr'],array('placeholder'=>'DD-MM-YYYY (Optional)')); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Sequential <em>*</em> <?php echo $form_edit->icon_help("This will go through each template variant in sequence rather than at random."); ?></label>
                                <?php echo $form_edit->input_html("select","sequential",$_POST['sequential'],array('option'=>array('0'=>'No, random','1'=>'Yes, in order'),'class'=>['input-sequential'])); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status <em>*</em> <?php echo $form_edit->icon_help("Inactive will prevent the email from being sent."); ?></label>
                                <?php echo $form_edit->input_html("select","status",(isset($_POST['status'])?$_POST['status']:1),array('option'=>array('0'=>'Inactive','1'=>'Active'))); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Custom Text <?php echo $form_edit->icon_help("Custom text usually appends to the bottom of the email, this may be an initial email conversation you had with the client / lead."); ?></label>
                                <?php echo $form_edit->input_html("textarea","custom_text",stripslashes($_POST['custom_text'])); ?>
                            </div>
                        </div>
                    </div>
				</div>
            </div>
		</div>
		
  		<?php if(!$zulu->template->body->exclude_table_hide) { ?>
   		<div class="panel panel-danger" id="panel-exclude">
   			<div class="panel-heading">
   				<i class="fas fa-minus-circle"></i> Exclude variations from template 
   			</div>
   			<div class="panel-body" id="panel-exclude-table">
				<?php echo $zulu->template->body->exclude_table; ?>
			</div>
		</div>
		<?php } ?>
			
        <p>	
    	<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['class'=>['btn btn-success']]); ?>
		<?php echo $form_edit->input_html("reset","reset",'Reset'); ?>
		<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
        </p>
        
    </div>
    
    </form>
    <?php } ?>
    <?php if(PAGE_action=='auto_rule') { ?>
    <div class="col-lg-12">
    	<?php foreach($class_rule->config->trigger_options as $key=>$trigger_option){ ?>
    		<div class="row">
    			<div class="col-lg-12">
    				<div class="panel panel-default">
						<div class="panel-heading">
							<div class="row">
								<div class="col-lg-6">
									<h3 class="panel-title"><i class="fas fa-caret-right"></i> Rules for trigger <?php echo $trigger_option['name_be']; ?></h3>
								</div>
								<div class="col-lg-6 text-right">
									<a class="btn btn-primary btn-xs" href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'auto_rule_edit', 'Trigger'=>$key))); ?>"><i class="fas fa-plus"></i> New</a> 
								</div>
							</div>
						</div>
						<div class="panel-body">
							<?php echo $zulu->template->body->trigger_rule_tables[$key]; ?>
						</div>
					</div>
    			</div>
    		</div>
    	<?php } ?>
    </div>
    <?php } ?>
    <?php if(PAGE_action=='auto_rule_edit') { ?>
    <form role="form" action="" method="post">
		<div class="col-lg-12">
			<p class="">When triggered a rule with the following settings will be generated.</p>
		</div>
    	<div class="col-lg-12">
			<div class="form-group">
				<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['class'=>['btn btn-success']]); ?>
				<?php echo $form_edit->input_html("reset","reset",'Reset'); ?>
				<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
			</div>
    	</div>
		<div class="col-md-9">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<i class="fas fa-info-circle"></i> Rule setting template
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label>Template <em>*</em> <?php echo $form_edit->icon_help("You can select a template below from the templates list you can create under 'Templates'."); ?></label>
								<?php echo $form_edit->input_html("select","template",$_POST['template'],array('class'=>['input-template'],'option'=>['_default'=>'Default']+$form_edit->templateOptionForm()+$sequence_list)); ?>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Status <em>*</em> <?php echo $form_edit->icon_help("Inactive will prevent the email from being sent."); ?></label>
								<?php echo $form_edit->input_html("select","status",(isset($_POST['status'])?$_POST['status']:1),array('option'=>array('0'=>'Inactive','1'=>'Active'))); ?>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Sending Delay</label>
								<?php echo $form_edit->input_html("number","send_delay_amount",$_POST['send_delay_amount'],array('placeholder'=>'0')); ?>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>&nbsp;</label>
								<?php echo $form_edit->input_html("select","send_delay_type",$_POST['send_delay_type'],array('class'=>[''],'option'=>['minutes'=>'Minutes', 'hours'=>'Hours', 'days'=>'Days'])); ?>
							</div>
						</div>
					</div>

					<div class="row-rule-info">
						<div class="row">
							<div class="col-lg-12">
								<div class="form-group">
									<label>Custom Text <?php echo $form_edit->icon_help("Custom text usually appends to the bottom of the email, this may be an initial email conversation you had with the client / lead."); ?></label>
									<?php echo $form_edit->input_html("textarea","custom_text",stripslashes($_POST['custom_text'])); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<?php if(!$zulu->template->body->exclude_table_hide) { ?>
			<div class="panel panel-danger" id="panel-exclude">
				<div class="panel-heading">
					<i class="fas fa-minus-circle"></i> Exclude variations from template 
				</div>
				<div class="panel-body" id="panel-exclude-table">
					<?php echo $zulu->template->body->exclude_table; ?>
				</div>
			</div>
			<?php } ?>

		</div>
   		<div class="col-lg-12">
			<div class="form-group">
				<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['class'=>['btn btn-success']]); ?>
				<?php echo $form_edit->input_html("reset","reset",'Reset'); ?>
				<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
			</div>
    	</div>
    </form>
    <?php } ?>
    <?php if(PAGE_action=='auto_rule_edit_old') { ?>
    <div class="col-lg-12">
    	<p class="lead">Automated Rules are rules that get generated based on the triggers you select.</p>
    </div>
    <form role="form" action="" method="post">
    <div class="col-lg-12">
    	<div class="form-group">
    		<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['class'=>['btn btn-success']]); ?>
			<?php echo $form_edit->input_html("reset","reset",'Reset'); ?>
			<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
    	</div>
    </div>
    <div class="col-md-8">
   		<div class="panel panel-primary">
   			<div class="panel-heading">
   				<i class="fas fa-wrench"></i> Rule Template
   			</div>
   			<div class="panel-body">
                <div class="row-rule-info">   
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Object <?php echo $form_edit->icon_help("Your template may have a 'object' in it, i.e. 'website', 'quote', 'booking'... This may not be required but can be used to customise the email further."); ?></label>
                                <?php echo $form_edit->input_html("input","object",$_POST['object']); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Frequency (Days) <em>*</em> <?php echo $form_edit->icon_help("What is the frequency this should go out on? For example: 7 would mean 1 week, 30 would mean every 30 days."); ?></label>
                                <?php echo $form_edit->input_html("input","frequency",$_POST['frequency']); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Max.<span class="lbl-loop"></span> Loop <?php echo $form_edit->icon_help("Enter 0 for infinite (i.e. no end). This will "); ?></label>
                                <?php echo $form_edit->input_html("input","loop_max",$_POST['loop_max'],['placeholder'=>'Leave blank for indefinate']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Start Override <?php echo $form_edit->icon_help("You can enter a custom date for the next mailer to send on."); ?></label>
                                <?php echo $form_edit->input_html("input","frequency_ovr",$_POST['frequency_ovr'],array('placeholder'=>'DD-MM-YYYY (Optional)')); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Sequential <em>*</em> <?php echo $form_edit->icon_help("This will go through each template variant in sequence rather than at random."); ?></label>
                                <?php echo $form_edit->input_html("select","sequential",$_POST['sequential'],array('option'=>array('0'=>'No, random','1'=>'Yes, in order'),'class'=>['input-sequential'])); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status <em>*</em> <?php echo $form_edit->icon_help("Inactive will prevent the email from being sent."); ?></label>
                                <?php echo $form_edit->input_html("select","status",(isset($_POST['status'])?$_POST['status']:1),array('option'=>array('0'=>'Inactive','1'=>'Active'))); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Custom Text <?php echo $form_edit->icon_help("Custom text usually appends to the bottom of the email, this may be an initial email conversation you had with the client / lead."); ?></label>
                                <?php echo $form_edit->input_html("textarea","custom_text",stripslashes($_POST['custom_text'])); ?>
                            </div>
                        </div>
                    </div>
				</div>
            </div>
            <div class="panel-footer">
   				<p><i class="fas fa-info-circle"></i> These are the settings that generated rules will have</p>
   				<p class="no-margin"></p>
			</div>
		</div>
        
    </div>
    <div class="col-lg-4">
    	<div class="panel panel-info">
   			<div class="panel-heading">
   				<i class="fas fa-check"></i> Select Triggers
   			</div>
   			<div class="panel-body">
   				<?php foreach($class_rule->config->trigger_options as $key=>$trigger_option){ ?>
					<div class="checkbox">
						<label><input type="checkbox" value="<?php echo $key; ?>" /><?php echo $trigger_option; ?></label>
					</div>
   				<?php } ?>
				<?php //echo $zulu->template->body->exclude_table; ?>
			</div>
		</div>
    </div>
    <?php foreach($class_rule->config->trigger_options as $key=>$trigger_option){ ?>
		<div id="<?php echo $key; ?>_option_container" class="col-lg-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<i class="fas fa-cog"></i> <?php echo $trigger_option; ?> Trigger options
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Template <em>*</em> <?php echo $form_edit->icon_help("You can select a template below from the templates list you can create under 'Templates'."); ?></label>
								<?php echo $form_edit->input_html("select","template[".$trigger_option."][]",$_POST['template'],array('class'=>['input-template'],'option'=>[''=>'None']+$form_edit->templateOptionForm()+$sequence_list)); ?>
							</div>
							<?php if(!$zulu->template->body->exclude_table_hide) { ?>
								<label>Template Variation Exclusions</label>
								<div id="panel-exclude-table">
									<?php echo $zulu->template->body->exclude_table; ?>
								</div>
							<?php } ?>
						</div>
					</div>

					<?php //echo $zulu->template->body->exclude_table; ?>
				</div>
			</div>
		</div>
    <?php } ?>
    <div class="col-lg-12">
    	<div class="form-group">
    		<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['class'=>['btn btn-success']]); ?>
			<?php echo $form_edit->input_html("reset","reset",'Reset'); ?>
			<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
    	</div>
    </div>
    </form>
    <?php } ?>
    <?php if(PAGE_action=='template_variation') { ?>
    
    <div class="col-lg-12">
		<p>
        	<a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'template_edit','category'=>$category))); ?>" class="btn btn-primary"><span class="fas fa-plus-circle"></span> New Template</a> 
        </p>
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
    			<?php echo $zulu->template->body; ?>
            </div>
            
			<p class="page-count text-center opt opt-grey"><i class="fas fa-info-circle"></i> You can re-order the sequence of each variation.</p>
    	</div>
    </div>
    <?php } ?>
    <?php if(PAGE_action=='template') { ?>
    
    <div class="col-lg-12">
		<p>
        	<a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'template_edit'))); ?>"><button class="btn btn-primary" type="button"><span class="fas fa-plus-circle"></span> New Template</button></a> 
        </p>
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
    			<?php echo $zulu->template->body; ?>
            </div>
    	</div>
    </div>
    <?php } ?> <?php if(PAGE_action=='sequence_template') { ?>
    
    <div class="col-lg-12">
		<p>
        	<a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'sequence_template_edit'))); ?>" class="btn btn-primary"><span class="fas fa-plus-circle"></span> New Sequence Template</a> 
        </p>
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
    			<?php echo $zulu->template->body; ?>
            </div>
    	</div>
    </div>
    <?php } ?>
     <?php if(PAGE_action=='sequence_template_edit') { ?>
    <form role="form" action="" method="post">
    <div class="col-md-9">
        
        <?php if($type==1) { ?>
        
   		<div class="panel panel-info">
   			<div class="panel-heading">
   				<i class="fas fa-info-circle"></i> Rule settings
   			</div>
   			<div class="panel-body">
				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
							<label>Sequence Name <em>*</em></label>
							<?php echo $form_edit->input_html("input","title",stripslashes($_POST['title']),['placeholder'=>"For example: New client marketing sequence"]); ?>
						</div>
					</div>
				</div>
			</div>
		</div>	
   		<div class="panel panel-default">
   			<div class="panel-heading">
   				<i class="fas fa-bars"></i> Rules
   			</div>
   			<div class="panel-body">
            	<?php if(!$new) { ?>
                <p><a href="<?php echo $zulu->link_page(PAGE_file,['query'=>['Action'=>'sequence_template_edit','parent_id'=>PAGE_id]]); ?>" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Add New Rule</a></p>
                <?php } ?>
				<?php if($child_rule_count<=0) { ?>
                    <p class="color-grey no-margin"><i class="fas fa-times"></i> No rules exist yet, <?php if(!$new) { echo "<a href=\"".$zulu->link_page(PAGE_file,['query'=>['Action'=>'sequence_template_edit','parent_id'=>PAGE_id]])."\">create one</a>"; } else { echo "press save to then create child rules"; } ?>.</p>
                <?php } else {
                    echo $zulu->template->body;
                } ?>
            </div>
            <?php if($total['messages']>0) { ?>
            <div class="panel-footer">
            	<p class="no-margin"><i class="fas fa-bars"></i> A total of <b><?php echo $total['messages']; ?></b> will be sent over a period of <b><?php echo $total['duration']; ?></b> days.</p>
            </div>
            <?php } ?>
        </div>
        <?php } else { ?>
  		<div class="panel panel-default">
   			<div class="panel-heading">
   				<i class="fas fa-info-circle"></i> Rule settings
   			</div>
   			<div class="panel-body">
				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
							<label>Template <em>*</em> <?php echo $form_edit->icon_help("You can select a template below from the templates list you can create under 'Templates'."); ?></label>
							<?php echo $form_edit->input_html("select","template",$_POST['template'],array('option'=>$form_edit->templateOptionForm())); ?>
						</div>
					</div>
				</div>
                
                <div class="row-rule-info">   
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Object <?php echo $form_edit->icon_help("Your template (above) may have a 'object' in it, i.e. 'website', 'quote', 'booking'... This may not be required but can be used to customise the email further."); ?></label>
                                <?php echo $form_edit->input_html("input","object",$_POST['object'],['placeholder'=>'Leave blank to inherit rules object...']); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Frequency (Days) <em>*</em> <?php echo $form_edit->icon_help("What is the frequency this should go out on? For example: 7 would mean 1 week, 30 would mean every 30 days."); ?></label>
                                <?php echo $form_edit->input_html("input","frequency",$_POST['frequency']); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Max.<span class="lbl-loop"></span> Loop <em>*</em> <?php echo $form_edit->icon_help("Enter 0 for infinite (i.e. no end). This will "); ?></label>
                                <?php echo $form_edit->input_html("input","loop_max",$_POST['loop_max']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Sequential <em>*</em> <?php echo $form_edit->icon_help("This will go through each template variant in sequence rather than at random."); ?></label>
                                <?php echo $form_edit->input_html("select","sequential",$_POST['sequential'],array('option'=>array('0'=>'No, random','1'=>'Yes, in order'),'class'=>['input-sequential'])); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status <em>*</em> <?php echo $form_edit->icon_help("Inactive will prevent the email from being sent."); ?></label>
                                <?php echo $form_edit->input_html("select","status",(isset($_POST['status'])?$_POST['status']:1),array('option'=>array('0'=>'Inactive','1'=>'Active'))); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Sort <?php echo $form_edit->icon_help("Set the order of the sequences rules. The lower the number the higher priority."); ?></label>
                                <?php echo $form_edit->input_html("number","sort",$_POST['sort']); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Custom Text <?php echo $form_edit->icon_help("Custom text usually appends to the bottom of the email, this may be an initial email conversation you had with the client / lead."); ?></label>
                                <?php echo $form_edit->input_html("textarea","custom_text",$_POST['custom_text'],['placeholder'=>'Leave blank to inherit rules object...']); ?>
                            </div>
                        </div>
                    </div>
				</div>
            </div>
		</div>
        <?php } ?>
		
		<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['class'=>['btn btn-success']]); ?>
		<?php echo $form_edit->input_html("reset","reset",'Reset'); ?>
		<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
    </div>
    </form>
    <?php } ?>
    <?php if(PAGE_action=='queue') { ?>
    
    
    
    <div class="col-lg-12">

		<ul class="nav nav-tabs">
			<li class="<?php echo ($tab=='email'?"active":NULL); ?>"><a href="<?php echo $zulu->link_page(PAGE_file,array('self'=>true,'query'=>array('View'=>'email'))); ?>" class="" aria-expanded="false"><i class="far fa-envelope"></i> Email Queue</a></li>
			<li class="<?php echo ($tab=='text'?"active":NULL); ?>"><a href="<?php echo $zulu->link_page(PAGE_file,array('self'=>true,'query'=>array('View'=>'text'))); ?>" class="" aria-expanded="false"><i class="fas fa-phone"></i> Text Queue</a></li>
		</ul>
       
		<p>&nbsp;</p>
       	<?php if($tab=='text'&&!$hide_cc) { ?>
        <div class="panel panel-warning">
			<div class="panel-heading">
				<i class="far fa-money-bill"></i> Top-up Your Texts
			</div>
            <div class="panel-body">
            	<div class="modules">
					<div class="module module-cc">
						<form id="checkout" method="post" action="">
					  		<div class="row">
					  			<div class="col-md-12">
					  				<label>How many texts you'd like to buy:</label>
					  				<?php echo $form_edit->input_html('select','text_pack',$_POST['text_pack'],['option'=>[100=>'100 Texts - $10NZD',200=>'200 Texts - $20NZD',500=>'500 Texts - $50NZD',1000=>'1000 Texts - $100NZD']]); ?>
					  			</div>
					  		</div>
					  		<br>
					  		<label>Confirm your payment:</label>
						  <div id="payment-form"></div>
							<button class="btn btn-success" type="submit"><span class="fas fa-chevron-right"></span> Top Up Now</button>
						  <input type="hidden" name="action" value="bt_card" />
						</form>
						 <p><br><a href="https://www.braintreegateway.com/merchants/<?php echo BRAINTREE_merchant; ?>/verified" target="_blank"><img src="https://s3.amazonaws.com/braintree-badges/braintree-badge-wide-light.png" width="240px" border="0"/></a></p>
					</div>
				</div>
			</div>
		</div>
      <?php } ?>
       
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
            
            	<?php if($tab=='text') { ?>
				<p>Current balance is <b><?php echo ($balance>0?"<span class='opt opt-success'>".$balance.", you can currently send</span>":"<span class='opt opt-danger'>".$balance.", please top-up</span>"); ?></b>.</p>
            	<?php } ?>
            	
    			<?php echo $zulu->template->body; ?>
            </div>
    	</div>
    </div>
    <?php } ?>
    <?php if(PAGE_action=='template_edit') { ?>
    <form role="form" action="" method="post">
    <div class="col-md-9">
   		<div class="panel panel-info">
   			<div class="panel-heading">
   				<i class="fas fa-info-circle"></i> Rule settings
   			</div>
   			<div class="panel-body">
				<div class="row">
					<div class="col-md-8">
						<div class="form-group">
							<label>Select existing template... <em>*</em></label>
							<?php echo $form_edit->input_html("select","category",$_POST['category'],array('option'=>$form_edit->templateOptionForm(true))); ?>
						</div>
					</div>
					<div class="col-md-4 type-standard-only">
						<div class="form-group">
							<label>Create new template</label>
							<?php echo $form_edit->input_html("input","category_new",$_POST['category_new']); ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
							<label>Template / Variation Name <em>*</em></label>
							<?php echo $form_edit->input_html("input","title",stripslashes($_POST['title']),['placeholder'=>"For example: New client marketing email #1"]); ?>
						</div>
					</div>
				</div>
			</div>
		</div>	
        
  		<div class="panel panel-default type-standard-only">
   			<div class="panel-heading">
   				<i class="far fa-envelope"></i> Email design
   			</div>
   			<div class="panel-body">
				<div class="form-group">
					<label>Email subject <em>*</em></label>
					<?php echo $form_edit->input_html("input","subject",stripslashes($_POST['subject']),['placeholder'=>'For example - RE: Just following up']); ?>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="form-group">
							<label>Email body <em>*</em></label>
							<?php echo $form_edit->input_html("htmlarea","content",stripslashes($_POST['content']),array('custom'=>array('rows'=>10,'id'=>'content'))); ?>
						</div>
					</div>
				</div>
			</div>
   			<div class="panel-footer">
   				<p><b><i class="fas fa-chevron-down"></i> Using merge tags in the email message</b></p>
   				<p class="no-margin">Write your message and include the below tags where you would like the clients details to appear:<br><i>[name] [name_first] [name_last] [company] [object]</i></p>
			</div>
		</div>
		
		<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['class'=>['btn btn-success']]); ?>
		<?php echo $form_edit->input_html("reset","reset",'Reset'); ?>
		<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
    </div>
    </form>
    <?php } ?>
    <?php if(PAGE_action=='signature_edit') { ?>
    <form role="form" action="" method="post">
    <div class="col-md-9">
   		<div class="panel panel-default">
   			<div class="panel-heading">
   				<i class="far fa-envelope"></i> Email signature layout
   			</div>
   			<div class="panel-body">
				<div class="form-group">
					<?php echo $form_edit->input_html("htmlarea","content",$_POST['content'],array('custom'=>array('rows'=>10,'id'=>'content'))); ?>
				</div>
			</div>
		</div>
    	<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['class'=>['btn btn-success']]); ?>
		<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
    </div>
    </form>
    <?php } ?>
</div>
<br />