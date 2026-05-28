
<?php if(PAGE_action==NULL) { ?>
<div class="row">
	<div class="col-lg-12">

		<div class="row row-margin">
			<div class="col-md-12">
				<a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'edit'))); ?>" class="btn btn-primary"><span class="fas fa-plus-circle"></span> New Subscription</a>

				<div class="btn-group">
					<a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="far fa-cogs"></span> Manage <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'template'))); ?>"><span class="far fa-edit"></span> Templates</a></li>
						<li><a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'Process','Template'=>($_GET['Template']!=NULL?$_GET['Template']:NULL)))); ?>"><span class="far fa-sync-alt"></span> Process</a></li>
						<li><a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Do'=>'Export','Template'=>($_GET['Template']!=NULL?$_GET['Template']:NULL),'Sort'=>($_GET['Sort']!=NULL?$_GET['Sort']:NULL),'View'=>($_GET['View']!=NULL?$_GET['View']:NULL),'Branch'=>($_GET['Branch']!=NULL?$_GET['Branch']:NULL),'Search'=>($_GET['Search']!=NULL?$_GET['Search']:NULL)))); ?>"><span class="far fa-file-excel"></span> Export</a></li>

					</ul>
				</div>
			</div>
		</div>

		<div class="panel panel-info panel-filter">
			<div class="panel-heading">
				<a data-toggle="collapse" href="#panel-filter" class="" aria-expanded="true"><i class="far fa-filter"></i> Filter / Search Results</a>
			</div>
			<div class="panel-body panel-collapse collapse in" id="panel-filter" aria-expanded="true" style="">
				<form action="" method="get">
					<?php echo $form_edit->input_html("hidden","Page",$_GET['Page']); ?>
					<div class="row">
						<div class="col-md-2">
							<div class="input-group">
								<span class="input-group-addon"><span class="fas fa-sync"></span> View</span>
								<?php echo $form_edit->input_html("select","View",$_GET['View'],array('option'=>$form_edit->input_array_bind($renew_status_filters,'label'))); ?>
							</div>
						</div>
						<div class="col-md-2">
							<div class="input-group">
								<span class="input-group-addon"><span class="fas fa-edit"></span> Template</span>
								<?php echo $form_edit->input_html("select","Template",$_GET['Template'],array('option'=>$template_array)); ?>
							</div>
						</div>
						<div class="col-md-2">
							<div class="input-group">
								<span class="input-group-addon"><span class="far fa-calendar-minus"></span> From</span>
								<?php echo $form_edit->input_html("input","From",$_GET['date_from'],array('custom'=>['placeholder'=>'DD/MM/YYYY'],'class'=>['input-date'])); ?>
							</div>
						</div>
						<div class="col-md-2">
							<div class="input-group">
								<span class="input-group-addon"><span class="far fa-calendar-plus"></span> To</span>
								<?php echo $form_edit->input_html("input","To",$_GET['date_to'],array('custom'=>['placeholder'=>'DD/MM/YYYY'],'class'=>['input-date'])); ?>
							</div>
						</div>
						<div class="col-md-2">
							<div class="input-group">
								<?php echo $form_edit->input_html("input","Search",$_GET['Search'],['custom'=>['placeholder'=>'Search','id'=>'search-box']]); ?>
								<span class="input-group-btn"><?php echo $form_edit->input_html("submit","",'<span class="fas fa-search"></span>',['custom'=>['id'=>'search-submit']]); ?></span>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>

		<div class="panel panel-default">
			<!-- /.panel-heading -->
			<div class="panel-body">
				<?php /*<?php if(count($template_data) > 0) { ?>
					<ul class="nav nav-tabs">
						<li class="<?php echo ($_GET['Template']==0||$_GET['Template']==NULL?"active":NULL); ?>"><a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Template'=>0,'Sort'=>($_GET['Sort']>0?$_GET['Sort']:NULL)))); ?>" class="" aria-expanded="false">All <span class="bullet stat-0"><?php echo $all_count; ?></span></a></li>
						<?php foreach($template_data as $template_row) { $count = $class_renew->subscription_count($template_row['id'],$_GET['Sort']); ?>
						<li class="<?php echo ($_GET['Template']==$template_row['id']?"active":NULL); ?>"><a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Template'=>$template_row['id'],'Sort'=>($_GET['Sort']>0?$_GET['Sort']:NULL)))); ?>" class="" aria-expanded="false"><?php echo stripslashes($template_row['title'])." <span class=\"bullet stat-0\">".$count."</span>"; ?></a></li>
						<?php } ?>
						<?php if($custom_count > 0) { ?>
						<li class="<?php echo ($_GET['Template']=='0'?"active":NULL); ?>"><a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Template'=>'0','Sort'=>($_GET['Sort']>0?$_GET['Sort']:NULL)))); ?>" class="" aria-expanded="false">Custom <span class="bullet stat-0"><?php echo $custom_count_status; ?></span></a></li>
						<?php } ?>
					</ul>
				<?php } ?>*/ ?>

				<?php /*<div class="row row-filter">
					<form method="get" action="">
					<?php echo $form_edit->input_html("hidden","Page",$_GET['Page']); ?>
					<?php echo $form_edit->input_html("hidden","Template",$_GET['Template']); ?>
					<?php echo $form_edit->input_html("hidden","View",$_GET['View']); ?>
					<div class="col-sm-6 selector">
							<span class="color-grey"><span class="fas fa-search"></span> Toggle</span>
							<a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Template'=>$_GET['Template'],'View'=>'active'))).($_GET['Branch']>0?"&Branch=".$_GET['Branch']:NULL).($_GET['Search']>0?"&Search=".$_GET['Search']:NULL); ?>"><button class="btn btn-success btn-xs" type="button"><span class="fas fa-check"></span> Active</button></a>
							<a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Template'=>$_GET['Template'],'View'=>'pending'))).($_GET['Branch']>0?"&Branch=".$_GET['Branch']:NULL).($_GET['Search']>0?"&Search=".$_GET['Search']:NULL); ?>"><button class="btn btn-default btn-xs" type="button"><span class="fas fa-pause"></span> Pending</button></a>
							<a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Template'=>$_GET['Template'],'View'=>'expire'))).($_GET['Branch']>0?"&Branch=".$_GET['Branch']:NULL).($_GET['Search']>0?"&Search=".$_GET['Search']:NULL); ?>"><button class="btn btn-danger btn-xs" type="button"><span class="far fa-clock"></span> Expired</button></a>
							<a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Template'=>$_GET['Template'],'View'=>'cancel'))).($_GET['Branch']>0?"&Branch=".$_GET['Branch']:NULL).($_GET['Search']>0?"&Search=".$_GET['Search']:NULL); ?>"><button class="btn btn-danger btn-xs" type="button"><span class="fas fa-times"></span> Cancelled</button></a>
							<a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Template'=>$_GET['Template'],'View'=>'renew'))).($_GET['Branch']>0?"&Branch=".$_GET['Branch']:NULL).($_GET['Search']>0?"&Search=".$_GET['Search']:NULL); ?>"><button class="btn btn-warning btn-xs" type="button"><span class="fas fa-sync-alt"></span> Renews Soon</button></a>
					</div>
					<div class="col-md-6 search">
						<div class="input-group">
							<?php echo $form_edit->input_html("input","Search",$_GET['Search'],['custom'=>['placeholder'=>'Search']]); ?>
							<span class="input-group-btn"><button class="btn btn-default" type="submit"><span class="fas fa-search"></span></button></span>
						</div>
					</div>
					</form>
				</div>*/ ?>

				<form method="POST" action="">
					
					<?php echo $zulu->template->body; ?>
					<div align="center"><?php echo $pagination; ?></div>
					<p class="page-count text-center opt opt-grey"><i class="fas fa-bars"></i> <?php echo $zulu->vars->renew_count+$offset; ?> record(s) in total</p>

					<?php if($zulu->vars->renew_count>0) { ?>
						<div class="panel panel-info panel-checkbox-action-box">
							<div class="panel-heading"> <span class="fas fa-fire"></span> Select an action to perform on selected items...</div>
							<div class="panel-body">
								<div class="form-group">
									<label>Please select an action:</label>
								<?php
								$option_sale = array(0=>'None','process'=>'Process (Check renewals and payments)','generate_renewal'=>'Generate Renewal Order','set_renew_date'=>'Set Renewal Date','cancel'=>'Cancel','delete'=>'Delete');
								echo $form_edit->input_html("select","execute",$_POST['execute'],['option'=>$option_sale,'class'=>['toggle-action-input']]); ?>
								</div>
								<div class="form-group toggle-action ta-input-set_renew_date">
									<label>Please set the new renewal date:</label>
									<?php echo $form_edit->input_html("input","input_action[renew_date]",$_POST['input_action']['renew_date'],array('class'=>['input-date'])); ?>
								</div>
								<?php echo $form_edit->input_html("submit","submit_complete",'<span class="fas fa-check"></span> Confirm',array('class'=>array('btn-info'))); ?>
							</div>
						</div>
					<?php } ?>
				</form>

			</div>
			<div class="panel-footer">
				<h4 class="">Active Gross Income</h4>
				<div class="row">
					<div class="col-md-4">
						<span>Gross Per <b>Week</b></span><br>
						<span class="opt opt-success"><?php echo LOCALE_currency_symbol.$zulu->dollar($total_week); ?></span>
					</div>
					<div class="col-md-4">
						<span>Gross Per <b>Month</b></span><br>
						<span class="opt opt-success"><?php echo LOCALE_currency_symbol.$zulu->dollar($total_month); ?></span>
					</div>
					<div class="col-md-4">
						<span>Gross Per <b>Year</b></span><br>
						<span class="opt opt-success"><?php echo LOCALE_currency_symbol.$zulu->dollar($total_year); ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>
<?php if(PAGE_action=='edit') { ?>
<div class="row">
<div class="col-lg-8">
	<form role="form" action="" method="post">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<span class="fas fa-info-circle"></span> Main Details
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-lg-6">
						<div class="form-group">
							<label>Contact</label>
							<?php echo $form_edit->input_html("select","client_id",$_POST['client_id'],array('option'=>$form_edit->clientOptionForm(),'class'=>['input-client'])); ?>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="form-group">
							<label>Status</label>
							<?php echo $form_edit->input_html("select","status",($new&&!isset($_POST['status'])?1:$_POST['status']),array('option'=>$class_renew->config->status)); ?>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="form-group">
							<label>Renewal Generates <?php echo $form_edit->icon_help("If 'billable' is selected, the subscription is renewed instantly. If 'sale' is selected, the sale must be PAID before the subscription is renewed."); ?></label>
							<?php echo $form_edit->input_html("select","opt_bill",$_POST['opt_bill'],array('option'=>$class_renew->config->renew_generate)); ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label>Subscription Template</label>
							<?php echo $form_edit->input_html("select","template_id",$_POST['template_selector'],array('option'=>$template_options)); ?>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>Auto-renew</label>
							<?php echo $form_edit->input_html("checkbox","auto_renew",1,array('custom'=>($_POST['auto_renew']==0&&!$new?NULL:array('checked'=>'checked')))); ?>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>Lifetime</label>
							<?php echo $form_edit->input_html("checkbox","lifetime",1,array('custom'=>($_POST['lifetime']==0?NULL:array('checked'=>'checked')))); ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php if($new) { ?>
		<div class="panel panel-danger">
			<div class="panel-heading">
				<span class="far fa-calendar"></span> <span class="far fa-play"></span> Subscription Startup
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-md-3">
						<div class="form-group">
							<label>Start Date</label>
							<?php echo $form_edit->input_html("input","period_start",$_POST['period_start'],array('placeholder'=>'DD/MM/YYYY','class'=>['input-date'])); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php } else { ?>
		<div class="panel panel-primary">
			<div class="panel-heading">
				<span class="fas fa-calendar"></span> Current Period
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-md-3">
						<div class="form-group">
							<label>Current Period Starts</label>
							<p class="form-control-static"><?php echo $period_start; ?></p>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label>Current Period Ends</label>
							<p class="form-control-static"><?php echo $period_end; ?></p>
						</div>
					</div>
					<?php if($show_next_renewal) { ?>
					<div class="col-md-3">
						<div class="form-group">
							<label>Next Renewal</label>
							<p class="form-control-static"><?php echo $period_next; ?></p>
						</div>
					</div>
					<?php } ?>
					<div class="col-md-3">
						<div class="form-group">
							<label>Trial</label>
							<?php if($class_renew->has_trial()) { ?>
								<?php if($class_renew->is_trial_active()) { ?>
									<p class="form-control-static"><span class="opt opt-success"><i class="far fa-sync fa-spin"></i> Trial active (<?php echo $trial_dates; ?>)</span></p>
								<?php } else { ?>
									<p class="form-control-static"><span class="opt opt-grey"><i class="far fa-times"></i> Trial finished (<?php echo $trial_dates; ?>)</span></p>
								<?php } ?>
							<?php } else { ?>
								<p class="form-control-static"><span class="opt opt-grey"><i class="far fa-times"></i> No trial</span></p>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>

		<div id="custom_section">
		<div class="panel panel-default">
			<div class="panel-heading">
				<span class="far fa-pencil"></span> Item Information
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-lg-6">
						<div class="form-group">
							<label>Link Product (optional)</label>
							<?php echo $form_edit->input_html("select","product_id",$_POST['product_id'],['class'=>['bt-select'],'option'=>$prod_options]); ?>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="form-group">
							<label>Name</label>
							<?php echo $form_edit->input_html("input","title",stripslashes($_POST['title'])); ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="form-group">
							<label>Description</label>
							<?php echo $form_edit->input_html("textarea","description",stripslashes($_POST['description'])); ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-4">
						<div class="form-group">
							<label>Price</label>
							<div class="input-group">
								<span class="input-group-addon"><?php echo LOCALE_currency_symbol; ?></span>
								<?php echo $form_edit->input_html("input","price",$_POST['price'],['placeholder'=>'$0.00','class'=>['price-field']]); ?>
								<span class="input-group-addon price-per-label"></span>
							</div>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="form-group">
							<label>Quantity</label>
							<?php echo $form_edit->input_html("input","quantity",$_POST['quantity'],['placeholder'=>'0','class'=>['price-field']]); ?>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="form-group">
							<label>Total</label>
							<p class="form-control-static">$<span class="label-price"><?php echo ($_POST['total_line']>0?$_POST['total_line']:'0.00'); ?></span></p>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="panel panel-warning">
			<div class="panel-heading">
				<span class="fas fa-sync-alt"></span> Renewal Period
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-lg-4">
						<div class="form-group">
							<label>Renew Cycle</label>
							<?php echo $form_edit->input_html("input","renew_interval",$_POST['renew_interval'],['placeholder'=>'Cycles between interval...']); ?>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="form-group">
							<label>Renew Interval</label>
							<?php echo $form_edit->input_html("select","renew_scale",$_POST['renew_scale'],array('class'=>['input-renew-scale'],'option'=>array('d'=>'Day','w'=>'Week','m'=>'Month','y'=>'Year'))); ?>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="form-group">
							<label>Renew Expired Action</label>
							<?php echo $form_edit->input_html("select","renew_action",$_POST['renew_action'],array('option'=>array('e'=>'Renew from renewal date','t'=>'Renew from current date'))); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="panel-footer">
				<p><span class="fas fa-info-circle"></span> Help</P>
					<ul class="notes">
					<li><b>Renew Cycle</b> is the number of times between intervals (i.e. 3 Months)</li>
					<li><b>Renew Interval</b> is the time period (i.e. days, weeks, months, years)</li>
					<li><b>Renew Next</b> when the first renewal should occur (or the next if already commenced)</li>
					</ul>
			</div>
		</div>
		</div>

		<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['class'=>['btn btn-success']]); ?>
		<?php echo $form_edit->input_html("reset","reset",'Reset'); ?>

		<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
		</form>
</div>
<div class="col-lg-4">
	<?php if(count($action_bt)>0) { ?>
	<div class="panel panel-info">
		<div class="panel-heading"><span class="fas fa-bolt"></span> Actions</div>
		<div class="panel-body">
			<?php echo implode("\n",$action_bt); ?>
		</div>
	</div>
	<?php } ?>
	<?php if($renew_data['renew_type']=='conc') { ?>
	<form method="post" action="">
	<div class="panel panel-warning">
		<div class="panel-heading"><span class="fas fa-ticket-alt"></span> Credit Statement</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-lg-4">
					<div class="form-group">
						<label>Adjust stock</label>
						<?php echo $form_edit->input_html("number","credit",(!$_POST['credit']?'0':$_POST['credit']),array('custom'=>array('id'=>'credit','min'=>1))); ?>
					</div>
				</div>
				<div class="col-lg-5">
					<div class="form-group">
						<label>Note</label>
						<?php echo $form_edit->input_html("input","note",$_POST['note'],array('placeholder'=>'(optional)','custom'=>array('id'=>'note'))); ?>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="form-group">
						<label>Adjust</label>
						<p><button type="submit" name="submit_add" class="btn btn-success btn-circle"><span class="fas fa-plus"></span></button>&nbsp;
						<button type="submit" name="submit_sub" class="btn btn-danger btn-circle"><span class="fas fa-minus"></span></button></p>
							<?php echo $form_edit->input_html("hidden","do",'credit'); ?>
					</div>
				</div>
			</div>
			<hr>
			<div class="coltable col2 vmiddle">
				<div class="col">
						<h4><span class="fas fa-list"></span> Credit Log</h4>
				</div>
				<div class="col text-right">
				<h4><?php echo $class_renew->credit_label($current_credit); ?></h4>
				</div>
			</div>
			<br>
			 <?php echo $zulu->template->renewal_log; ?>
		</div>
	</div>
	</form>
	<?php } else { ?>
	<div class="panel panel-warning">
		<div class="panel-heading"><span class="far fa-clock"></span> Renewal Log</div>
		<div class="panel-body">
			<?php echo $zulu->template->renewal_log; ?>
		</div>
	</div>
	<?php } ?>
</div>
</div>
<?php } ?>
<?php if(PAGE_action=='template') { ?>
<div class="row">
	<div class="col-lg-12">
		<p>
			<a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'template_edit'))); ?>" class="btn btn-primary"><span class="fas fa-plus-circle"></span> New Template</a>
		</p>
		<div class="panel panel-default">
			<!-- /.panel-heading -->
			<div class="panel-body">
				<?php echo $zulu->template->body; ?>
			</div>
		</div>
	</div>
</div>
<?php } ?>
<?php if(PAGE_action=='report') { ?>
<div class="row">
	<div class="col-sm-offset-0 col-sm-12 col-md-offset-2 col-md-8 col-center-wrapper">
	<div class="row">
		<div class="col-md-12">
			<form method="get" action="">
			<div class="panel panel-default">
				<div class="panel-heading"><i class="fas fa-filter"></i> Create Report By Date</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-3">
							<div class="field">
								<label>Start Date</label>
								<?php echo $form_edit->input_html("input","date_from",($_GET['date_from']!=NULL?$_GET['date_from']:date('d/m/Y',strtotime('first day of last month'))),['class'=>['input-date']]); ?>
							</div>
						</div>
						<div class="col-md-3">
							<div class="field">
								<label>End Date</label>
								<?php echo $form_edit->input_html("input","date_to",($_GET['date_to']!=NULL?$_GET['date_to']:(!$exact?date('d/m/Y',strtotime('last day of last month')):'')),['class'=>['input-date']]); ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="field">
								<label>&nbsp;</label>
								<p class="static-form-control">
									<button type="submit" class="btn btn-success"><i class="fas fa-filter"></i> View Report</button>
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<input type="hidden" name="Page" value="<?php echo PAGE_file; ?>" />
			<input type="hidden" name="Action" value="<?php echo PAGE_action; ?>" />
			</form>
			<?php if($filtered && false) { ?>
			<div class="panel panel-default">
				<div class="panel-heading"><i class="fal fa-chart-line"></i> Report</div>
				<div class="panel-body">
					<div id="morris-area-chart" style="max-height: 200px;"></div>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
	<?php if($filtered) { ?>
	<div class="row">
		<div class="col-xs-12 col-md-4">
			<div class="panel panel-primary">
				<div class="panel-heading"><span class="h4 no-margin"><i class="far fa-users"></i> Total Renewals</span></div>
				<div class="panel-body">
					<div class="text-center">
						<p class="h2 no-margin"><?= $summary['renew']; ?></p>
						<p class="h4 no-margin">(<?= LOCALE_currency_symbol.number_format($summary['renew_revenue'],2); ?>)</p>
					</div>
				</div>
			</div>
		</div><!-- col: end renewals -->
		<div class="col-xs-12 col-md-4">
			<div class="panel panel-green">
				<div class="panel-heading"><span class="h4 no-margin"><i class="far fa-users"></i> Total Signups</span></div>
				<div class="panel-body">
					<div class="text-center">
						<p class="h2 no-margin"><?= $summary['signup']; ?></p>
					</div>
				</div>
			</div>
		</div><!-- col: end renewals -->
		<div class="col-xs-12 col-md-4">
			<div class="panel panel-danger">
				<div class="panel-heading"><span class="h4 no-margin"><i class="far fa-users"></i> Total Cancellations</span></div>
				<div class="panel-body">
					<div class="text-center">
						<p class="h2 no-margin"><?= $summary['cancel']; ?></p>
					</div>
				</div>
			</div>
		</div><!-- col: end renewals -->
	</div>
	<?php } else { ?>
	<div class="row">
		<div class="col-sm-12 col-md-9">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<div class="row">
						<div class="col-md-6">
							<i class="far fa-money-bill"></i> Active Subscriptions
						</div>
						<div class="col-md-6 text-right">
							<a href="<?= $zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'download']]); ?>" class="btn btn-primary btn-xs"><i class="far fa-file-csv"></i> Download (.CSV)</a>
						</div>
					</div>
				</div>
				<div class="panel-body">
					<?php echo $zulu->template->table_current_subs; ?>
				</div>
			</div>
		</div>
		<div class="col-sm-12 col-md-3">
			<div class="panel panel-green">
				<div class="panel-heading"><i class="far fa-chart-line"></i> Statistics</div>
				<div class="panel-body">

					<div class="text-center">
						<p class="h4"><i class="far fa-users"></i> Active Subscriptions</p>
						<p class="h2 no-margin"><?= $summary['total']; ?></p>
						<hr>
						<p class="h4"><i class="far fa-money-bill"></i> Avg. Revenue Per Month</p>
						<p class="h2 no-margin"><?= LOCALE_currency_symbol.number_format($summary['revenue_month'],2); ?></p>
						<hr>
						<p class="h4"><i class="far fa-money-bill"></i> Avg. Revenue Per Year</p>
						<p class="h2 no-margin"><?= LOCALE_currency_symbol.number_format($summary['revenue_year'],2); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
</div>
</div>
<?php } ?>
<?php if(PAGE_action=='template_edit') { ?>
<form role="form" action="" method="post">
<div class="row">
	<div class="col-lg-8">
		<div class="panel panel-default">
			<div class="panel-heading">
				<span class="far fa-pencil"></span> Item Information
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-lg-6">
						<div class="form-group">
							<label>Link Product (optional)</label>
							<?php echo $form_edit->input_html("select","product_id",$_POST['product_id'],['class'=>['bt-select'],'option'=>$prod_options]); ?>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="form-group">
							<label>Name of Subscription <em>*</em></label>
							<?php echo $form_edit->input_html("input","title",stripslashes($_POST['title'])); ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="form-group">
							<label>Description</label>
							<?php echo $form_edit->input_html("textarea","description",stripslashes($_POST['description'])); ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-4">
						<div class="form-group">
							<label>Price <em>*</em></label>
							<div class="input-group">
								<span class="input-group-addon"><?php echo LOCALE_currency_symbol; ?></span>
								<?php echo $form_edit->input_html("input","price",$_POST['price'],['placeholder'=>'$0.00','class'=>['price-field']]); ?>
								<span class="input-group-addon price-per-label"></span>
							</div>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="form-group">
							<label>Quantity <em>*</em></label>
							<?php echo $form_edit->input_html("input","quantity",$_POST['quantity'],['placeholder'=>'0','class'=>['price-field']]); ?>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="form-group">
							<label>Total</label>
							<p class="form-control-static">$<span class="label-price"><?php echo ($_POST['total_line']>0?$_POST['total_line']:'0.00'); ?></span></p>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="panel panel-info">
			<div class="panel-heading">
				<span class="fas fa-sync-alt"></span> Configuration
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-lg-3">
						<div class="form-group">
							<label>Can switch from</label>
							<?php echo $form_edit->input_html("select","change_from[]",explode(",",$_POST['change_from']),['option'=>$plan_list,'multiple'=>true]); ?>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="form-group">
							<label>Can switch to</label>
							<?php echo $form_edit->input_html("select","change_to[]",explode(",",$_POST['change_to']),['option'=>$plan_list,'multiple'=>true]); ?>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="form-group">
							<label>Subsciption model</label>
							<?php echo $form_edit->input_html("select","renew_type",$_POST['renew_type'],['class'=>['input-renew-type'],'option'=>$class_renew->config->renew_model]); ?>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="form-group">
							<label>Other configurations</label>
							<div class="form-group">
								<p><label class="checkbox-inline"><?php echo $form_edit->input_html("checkbox","opt_switch",1,['checked'=>($_POST['opt_switch']>0?true:false)]); ?> Switch only?</label></p>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-3">
						<div class="form-group">
							<label>Cancellation Policy</label>
							<?php echo $form_edit->input_html("select","meta[cancel_policy]",$_POST['meta']['cancel_policy'],['option'=>['next'=>'Cancel from next renewal date','instant'=>'Cancel instantly']]); ?>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="form-group">
							<label>Renewal Policy</label>
							<?php echo $form_edit->input_html("select","meta[renew_policy]",$_POST['meta']['renew_policy'],['option'=>['instant'=>'Renew without payment','pay'=>'Require payment']]); ?>
						</div>
					</div>
					<div class="col-sm-12 col-md-3">
						<div class="form-group">
							<label>Require Shipping Details</label>
							<?php echo $form_edit->input_html("select","meta[renew_purch_shipping]",$_POST['meta']['renew_purch_shipping'],array('option'=>[0=>'No',1=>'Yes'])); ?>
						</div>
					</div>
					<div class="col-sm-12 col-md-3">
						<div class="form-group">
							<label>Require Billing Details</label>
							<?php echo $form_edit->input_html("select","meta[renew_purch_billing]",$_POST['meta']['renew_purch_billing'],array('option'=>[0=>'No',1=>'Yes'])); ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="panel panel-info">
			<div class="panel-heading">
				<span class="far fa-envelope"></span> Email Alert
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-lg-3">
						<div class="form-group">
							<label>Sign Up Template</label>
							<?php echo $form_edit->input_html("select","meta[mail_signup]",$_POST['meta']['mail_signup'],['option'=>[''=>"None"]+$form_edit->templateOptionForm()]); ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="panel panel-info">
			<div class="panel-heading">
				<span class="far fa-laptop"></span> Website
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-lg-3">
						<div class="form-group">
							<label>Sign Up Redirect URL</label>
							<?php echo $form_edit->input_html("input","meta[web_redirect]",$_POST['meta']['web_redirect'],['placeholder'=>'https://www.example.com']); ?>
						</div>
					</div>
					<div class="col-lg-12">
						<div class="form-group">
							<label>Sign-up Information</label>
							<?php echo $form_edit->input_html("textarea","meta[web_signup_info]",$_POST['meta']['web_signup_info'],['placeholder'=>'']); ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="panel panel-warning" id="panel-date">
			<div class="panel-heading">
				<span class="far fa-calendar"></span> Date Based Settings
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-lg-4">
						<div class="form-group">
							<label>Renew Cycle <em>*</em></label>
							<?php echo $form_edit->input_html("input","renew_interval",$_POST['renew_interval'],['placeholder'=>'Cycles between interval...']); ?>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="form-group">
							<label>Renew Interval <em>*</em></label>
							<?php echo $form_edit->input_html("select","renew_scale",$_POST['renew_scale'],array('class'=>['input-renew-scale'],'option'=>array('d'=>'Day','w'=>'Week','m'=>'Month','y'=>'Year'))); ?>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="form-group">
							<label>Renew Expired Action <em>*</em></label>
							<?php echo $form_edit->input_html("select","renew_action",$_POST['renew_action'],array('option'=>array('e'=>'Renew from renewal date','t'=>'Renew from current date'))); ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-4">
						<div class="form-group">
							<label>Trial Length (Days)</label>
							<?php echo $form_edit->input_html("input","trial_length",$_POST['trial_length'],['placeholder'=>'None']); ?>
							<?php echo $form_edit->input_html("hidden","trial_scale",'d'); ?>
						</div>
					</div>
					<?php /*<div class="col-lg-4">
						<div class="form-group">
							<label>Trial Date <em>*</em></label>
							<?php echo $form_edit->input_html("select","renew_scale",$_POST['renew_scale'],array('class'=>['input-renew-scale'],'option'=>array('d'=>'Day','w'=>'Week','m'=>'Month','y'=>'Year'))); ?>
						</div>
					</div>*/ ?>
				</div>
			</div>
			<div class="panel-footer">
				<p><span class="fas fa-info-circle"></span> Help</P>
					<ul class="notes">
					<li><b>Renew Cycle</b> is the number of times between intervals (i.e. 3 Months)</li>
					<li><b>Renew Interval</b> is the time period (i.e. days, weeks, months, years)</li>
					<!--<li><b>Renew Next</b> when the first renewal should occur (or the next if already commenced)</li>-->
					</ul>
			</div>
		</div>

		<div class="panel panel-warning" id="panel-conc">
			<div class="panel-heading">
				<span class="far fa-credit-card"></span> Concession Settings
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-lg-4">
						<div class="form-group">
							<label>Recharge Breakpoints</label>
							<?php echo $form_edit->input_html("input","meta[conc_credit]",$_POST['meta']['conc_credit'],['placeholder'=>'Leave empty for variable recharge...']); ?>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="form-group">
							<label>Renewal Product (if any)</label>
							<?php echo $form_edit->input_html("select","meta[conc_product_id]",$_POST['meta']['conc_product_id'],['option'=>$prod_options]); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="panel-footer">
				<p><span class="fas fa-info-circle"></span> Help</P>
					<ul class="notes">
					<li><b>Recharge Breakpoints</b> allow set recharge points to be created, i.e. 5 credit, 10 credit, 30 credit recharge only</li>
					<li><b>Renewal Product</b> will give the staff or customer a link to that product to buy more credits</li>
					<!--<li><b>Renew Next</b> when the first renewal should occur (or the next if already commenced)</li>-->
					</ul>
			</div>
		</div>

		<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['class'=>['btn btn-success']]); ?>
		<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
	</div>
</div>
</form>
<?php } ?>
<?php if(PAGE_action=='process' || PAGE_action=='Process') { ?>
<form role="form" action="" method="post">
<div class="row">
	<div class="col-md-6">
		<div class="panel panel-danger">
			<div class="panel-heading">
				<span class="far fa-check"></span> Select the Subscriptions To Renew
			</div>
			<div class="panel-body">
				<?php echo $zulu->template->body; ?>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<span class="far fa-pencil"></span> Process Renewables To Date
			</div>
			<div class="panel-body">
				<div class="form-group">
					<label>Date</label>
					<?php echo $form_edit->input_html("input","date",stripslashes($_POST['date']),['class'=>['date-input date input-date']]); ?>
				</div>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">
				<span class="far fa-money-bill"></span> Renewable Sale Information
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-lg-6">
						<div class="form-group">
							<label>Date</label>
							<?php echo $form_edit->input_html("input","sale_date",stripslashes($_POST['sale_date']),['class'=>['date-input date input-date']]); ?>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="form-group">
							<label>Due Date</label>
							<?php echo $form_edit->input_html("input","sale_date_due",stripslashes($_POST['sale_date_due']),['class'=>['date-input date input-date']]); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
<div class="panel panel-default">
			<div class="panel-heading">
				<span class="far fa-pencil"></span> Process Renew cycles
			</div>
			<div class="panel-body">
				<div class="form-group">
					<label>Cycles</label>
                    <?php echo $form_edit->input_html("select","renew_cycles",$_GET['renew_cycles'],array('option'=>[1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12])); ?>
				</div>
			</div>
		</div>
		<a href="<?php echo $zulu->link_page('renew'); ?>" class="btn btn-default btn-lg"><i class="far fa-reply"></i> Return</a>
		<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save . " &amp; Process",['class'=>['btn btn-danger btn-lg']]); ?>
		<?php echo $form_edit->input_html("hidden","do",'submit'); ?>
	</div>
</div>
</form>
<?php } ?>
