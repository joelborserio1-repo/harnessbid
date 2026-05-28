<!-- /.row -->
<div class="row">

	<?php if($zulu->template->comment) { ?>
	<!--show comment box on all pages-->
	<div class="col-md-12">
	<?php echo $zulu->notification('',1,['tag'=>'sale_comment']); ?>
	<div class="panel panel-primary box-comment" id="box-comment">
		<div class="panel-heading">
			<span class="fas fa-paper-plane"></span> Send copy of sale and/or comment
		</div>
		<div class="panel-body">
		<form method="post" action="">
			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<label>Recipient Name</label>
						<?php echo $form_edit->input_html("input","name",$cbox_sale_data['name']); ?>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label>Recipient Email</label>
						<?php echo $form_edit->input_html("input","email",($cbox_sale_data['email']!=NULL?$cbox_sale_data['email']:$cbox_user_data['email'])); ?>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
				<div class="form-group">
					<label>Custom Message</label>
					<?php echo $form_edit->input_html("textarea","message",$_POST['message']); ?>
					<p class="caption color-grey">Leave blank to send generic receipt message.</p>
				</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-paper-plane\"></span> Send",array("class"=>array("btn-success"))); ?>
					<?php echo $form_edit->input_html("hidden","method",'comment'); ?>
				</div>
			</div>
		</form>
		</div>
	</div><!--end comment box-->
	</div>
	<?php } ?>

	<?php if(PAGE_action==NULL) { ?>

    <div class="col-lg-12">

        <div class="row row-margin">
        	<div class="col-md-6">
           		<?php if($class_user->has_perm('sale_edit')) { ?>
            	<a href="<?php echo $zulu->link_page('sale',array('query'=>array('Action'=>'edit'))); ?>" class="btn btn-primary"><span class="fas fa-plus-circle"></span> Add New Sale</a>
            	<?php } ?>
            </div>
        	<div class="col-md-6 text-right">
            <span class="color-grey"><span class="fas fa-search"></span> Toggle</span>
            	<a href="<?php echo $zulu->link_page('sale',array('query'=>array('View'=>'paid'))); ?>"><button class="btn btn-default btn-xs" type="button"><span class="fas fa-check"></span> Paid</button></a>
            	<a href="<?php echo $zulu->link_page('sale',array('query'=>array('View'=>'pending'))); ?>"><button class="btn btn-default btn-xs" type="button"><span class="fas fa-check"></span> Pending</button></a>
            	<a href="<?php echo $zulu->link_page('sale',array('query'=>array('View'=>'parked'))); ?>"><button class="btn btn-default btn-xs" type="button"><span class="fas fa-pause"></span> Parked</button></a>
            	<a href="<?php echo $zulu->link_page('sale',array('query'=>array('View'=>'void'))); ?>"><button class="btn btn-default btn-xs" type="button"><span class="fas fa-times"></span> Voided</button></a>
            	<?php if($class_xero->vars->enabled) { ?>
            	<a href="<?php echo $zulu->link_page('sale',array('query'=>array('View'=>'xero_no'))); ?>"><button class="btn btn-default btn-xs" type="button"><span class="fas fa-times"></span> No Xero</button></a>
            	<?php } ?>
            	<a href="<?php echo $zulu->link_page('sale',array('query'=>array('View'=>'sent_no'))); ?>"><button class="btn btn-default btn-xs" type="button"><span class="fas fa-times"></span> No Sent</button></a>
            	<a href="<?php echo $zulu->link_page('sale'); ?>"><button class="btn btn-warning btn-xs" type="button"><span class="fas fa-sync-alt"></span> Reset</button></a>
            </div>
        </div>

        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
				<form action="" method="get">
					<?php echo $form_edit->input_html("hidden","Page",$_GET['Page']); ?>
					<?php if($_GET['View'] != NULL) { echo $form_edit->input_html("hidden","View",$_GET['View']); } ?>
					<div class="row">
						<div class="col-lg-3">
							<div class="input-group">
								<span class="input-group-addon"><span class="far fa-calendar-minus"></span> From</span>
								<?php echo $form_edit->input_html("input","From",$_GET['date_from'],array('custom'=>['placeholder'=>'DD/MM/YYYY'],'class'=>['date-picker'])); ?>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="input-group">
								<span class="input-group-addon"><span class="far fa-calendar-plus"></span> To</span>
								<?php echo $form_edit->input_html("input","To",$_GET['date_to'],array('custom'=>['placeholder'=>'DD/MM/YYYY'],'class'=>['date-picker'])); ?>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="input-group">
								<?php echo $form_edit->input_html("input","Search",$_GET['Search'],['custom'=>['placeholder'=>'Search','id'=>'search-box']]); ?>
								<span class="input-group-btn"><?php echo $form_edit->input_html("submit","",'<span class="fas fa-search"></span>',['custom'=>['id'=>'search-submit']]); ?></span>
							</div>
						</div>
					</div>
				</form>
            	<br>
            	<form method="POST" action="">
					<?php echo $zulu->template->body; ?>
					<div align="center"><?php echo $pagination; ?></div>
					<p class="page-count text-center opt opt-grey"><i class="fas fa-bars"></i> <?php echo $zulu->vars->sale_count+$offset; ?> record(s) in total</p>
					<?php if($zulu->vars->sale_count>0) { ?>
						<div class="panel panel-info panel-checkbox-action-box">
							<div class="panel-heading"> <span class="fas fa-fire"></span> Select an action to perform on selected items...</div>
							<div class="panel-body">
								<div class="form-group">
								<?php
								$option_sale = array(0=>'None','delete'=>'Void','mail'=>'Email Sale','mail_mark'=>'Mark Sent');
								if($class_xero->vars->enabled) {
									$option_sale['export_xero'] = 'Export to Xero (Draft)';
									$option_sale['export_xero_publish'] = 'Export to Xero (Approved)';
								}
								echo $form_edit->input_html("select","execute",$_POST['execute'],array('option'=>$option_sale)); ?>							   </div>
								<?php echo $form_edit->input_html("submit","submit_complete",'<span class="fas fa-check"></span> Confirm',array('class'=>array('btn-info'))); ?>
							</div>
						</div>
					<?php } ?>
            	</form>
            </div>
    	</div>
    </div>
    <?php } ?>
	<?php if(PAGE_action=='pay') { ?>
    <div class="col-lg-12">

    <p>You can view &amp; make payments for this sale below as well as view the sales log.</p>
    <p>
        <?php if(!$class_sale->locked) { ?>
        <a href="<?php echo $zulu->link_page('sale',array('query'=>array('Action'=>'edit','id'=>PAGE_id))); ?>"  class="btn btn-primary"><span class="fas fa-edit"></span> Edit Sale</a>
        <?php } ?>
    	<a href="<?php echo $zulu->link_page('sale',array('query'=>array('Action'=>'edit','id'=>PAGE_id,'Method'=>'View'))); ?>" class="btn btn-default"><span class="fas fa-search"></span> View Sale</a>

		<a href="<?php echo $zulu->link_page('sale',array('query'=>array('Action'=>'print','id'=>PAGE_id))); ?>" target="_blank" class="btn btn-default"><span class="fas fa-print"></span> Print</a>

		<?php if($client_data['email']!=NULL) { ?>
		<?php } else { ?>
		<a href="<?php echo $zulu->link_page('client',array('query'=>array('Action'=>'edit','id'=>$client_data['id'],'From'=>PAGE_id))); ?>" target="_blank" class="btn btn-info"><span class="fas fa-envelope"></span> Add Email</a>
		<?php } ?>
		<a href="#" title="Include custom email address and/or message." class="bt-mail-receipt btn btn-warning"><span class="fas fa-envelope"></span> Mail Custom</a>
		<a href="<?php echo $zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'email_sale']]); ?>" title="Send mail instantly to clients email." class="btn btn-warning"><span class="fas fa-envelope"></span> <span class="fas fa-chevron-right"></span> Mail Direct</a>


    </p>

    </div>
    <?php if($class_sale->sale_has_membership(PAGE_id) && $sale_balance>0) { ?>
	<div class="col-lg-12">
		<div class="panel panel-danger">
          <div class="panel-heading "><span class="fas fa-exclamation-triangle"></span> Warning</div>
          <div class="panel-body">
          		<p>This sale has a subscription in it so it needs to be paid via credit card.</p>
          </div>
      </div>
   	</div>
	<?php } ?>

    <div class="col-lg-5">
    	<div class="row">
        	<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading "><span class="fas fa-chart-line"></span> Sale Balances</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-sm-4">
								<div class="form-group">
									<h3>Total</h3>
									<p class="static-form-control"><?php echo LOCALE_currency_symbol.number_format($sale_total,2); ?></p>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<h3>Paid</h3>
									<p class="static-form-control"><?php echo LOCALE_currency_symbol.number_format($sale_paid,2); ?></p>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<h3>Balance</h3>
									<p class="static-form-control"><?php echo LOCALE_currency_symbol.number_format($sale_balance,2); ?></p>
								</div>
							</div>
						</div>
						<?php if($sale_meta['fe_payment']!=NULL) { ?>
						<div class="alert alert-info no-margin"><i class="fas fa-university"></i> Client has selected '<?php echo $class_sale->config->payment[$sale_meta['fe_payment']]; ?>' as their payment method. <b>Check  account for this payment then add payment below.</b></div>
						<?php } ?>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading "><span class="fas fa-bars"></span> Sale Transaction List</div>
					<div class="panel-body">
						<?php echo $pay_table; ?>
					</div>
				</div>
      		</div>
       </div>
	</div>
	<div class="col-lg-7">
		<ul class="nav nav-tabs">
			<li class="active"><a data-toggle="tab" href="#tab_payment"><i class="far fa-money-bill"></i> Payment</a></li>
			<li><a data-toggle="tab" href="#tab_refund"><i class="fas fa-undo"></i> Refund</a></li>
			<li><a data-toggle="tab" href="#tab_restock"><i class="fas fa-truck"></i> Restock</a></li>
			<li><a data-toggle="tab" href="#tab_coupon"><i class="fas fa-ticket-alt"></i> Coupon</a></li>
			<li><a data-toggle="tab" href="#tab_xero"><i class="fas fa-link"></i> Xero&reg;</a></li>
			<li><a data-toggle="tab" href="#tab_updates"><i class="fas fa-bullhorn"></i> Updates</a></li>
		</ul>

		<div class="tab-content">
			<div id="tab_payment" class="tab-pane fade in active">
				<br>
				<?php if(!$paid) { ?>
					<div class="panel panel-success">
						<div class="panel-heading "><i class="far fa-money-bill"></i> Add Payment to Sale</div>
						<div class="panel-body">
						<form role="form" action="" method="post" class="inline_label_input">
							<div class="row">
								<div class="col-lg-3">
									<div class="form-group">
										<label>Amount</label>
										<div class="form-group input-group">
										<span class="input-group-addon"><?php echo LOCALE_currency_symbol; ?></span>
										<?php echo $form_edit->input_html("input","amount",$_POST['amount'],['placeholder'=>'0.00']); ?>
										</div>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label>Date</label>
										<?php echo $form_edit->input_html("input","date",$_POST['date'],array('placeholder'=>'DD/MM/YYYY')); ?>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label>Method</label>
										<?php echo $form_edit->input_html("select","method",$_POST['method'],array('option'=>$payment_options)); ?>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label>Reference</label>
										<?php echo $form_edit->input_html("input","reference",$_POST['reference']); ?>
									</div>
								</div>
							</div>
							<div class="form-group no-margin">
								<?php echo $form_edit->input_html("submit","submit",'<span class="fas fa-chevron-right"></span> Record Payment',array('class'=>array('btn-success'))); ?>
								<?php echo $form_edit->input_html("hidden","action","pay_other"); ?>
							</div>
						</form>
						</div>
					</div><!--manual pay panel-->
            <?php } else { ?>
				<div class="panel panel-success">
					<div class="panel-heading"><span class="fas fa-check"></span> Paid</div>
					<div class="panel-body">
						Full payment for this sale has been received.
					</div>
				</div>
            <?php } ?>
			</div>
			<div id="tab_refund" class="tab-pane fade">
				<br>
				<div class="panel panel-warning">
					<div class="panel-heading"><i class="fas fa-undo"></i> Refund Payment</div>
					<div class="panel-body">
					<form role="form" action="" method="post" class="inline_label_input">
						<div class="row">
							<div class="col-lg-4">
								<div class="form-group">
									<label>Amount</label>
									<div class="input-group">
										<span class="input-group-addon"><?php echo LOCALE_currency_symbol; ?></span>
										<?php echo $form_edit->input_html("number","refund_amount",$_POST['refund_amount'],['placeholder'=>'0.00','custom'=>['step'=>'any']]); ?>
									</div>
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label>Date</label>
									<?php echo $form_edit->input_html("input","refund_date",$_POST['refund_date'],array('placeholder'=>'DD-MM-YYYY', 'id'=>'refund_date')); ?>
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label>Reference</label>
									<?php echo $form_edit->input_html("input","refund_reference",$_POST['refund_reference']); ?>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label>Transaction</label>
									<?php echo $form_edit->input_html("select","refund_pay_id",$_POST['refund_pay_id'],['option'=>$transaction_options ]); ?>
								</div>
							</div>
							<div class="col-lg-6">
								<div class="form-group">
									<label>Line Item</label>
									<?php echo $form_edit->input_html("select","refund_line_id",$_POST['refund_line_id'],['option'=>$sale_line_options ]); ?>
								</div>
							</div>
						</div>

						<div class="form-group">
							<?php echo $form_edit->input_html("submit","submit",'<span class="fas fa-chevron-right"></span> Refund',array('class'=>array('btn-warning btn-block'))); ?>
							<?php echo $form_edit->input_html("hidden","action","refund_payment"); ?>
						</div>
					</form>
					</div>
				</div>
			</div>
			<div id="tab_restock" class="tab-pane fade">
				<br>
				<div class="panel panel-warning">
					<div class="panel-heading"><span class="fas fa-truck"></span> Restock</div>
					<div class="panel-body">
						<form role="form" action="" method="post">
							<?php echo $form_edit->input_html("hidden","action","restock"); ?>
							<?php echo $form_edit->input_html("hidden","sale_reference",$sale_data['reference']); ?>

							<div class="row">
								<div class="col-lg-12">
									<?php echo $restock_table; ?>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-12">
									<?php echo $form_edit->input_html("submit","submit",'<span class="fas fa-chevron-right"></span> Apply',array('class'=>array('btn-success'))); ?>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
			<div id="tab_coupon" class="tab-pane fade">
			<br>
				<div class="panel panel-warning">
					<div class="panel-heading"><span class="fas fa-ticket-alt"></span> Apply a Coupon</div>
					<div class="panel-body">
						<form role="form" action="" method="post" class="inline_label_input">
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<label>Coupon Code</label>
										<?php if($sale_data['coupon_id']==0) { ?>
											<?php echo $form_edit->input_html("input","coupon",$_POST['coupon']); ?>
										<?php } else { ?>
											<p><?php echo $coupon_name; ?> <a href="<?php echo $zulu->link_page('sale',array('query'=>array('id'=>PAGE_id,'Action'=>'remove_coupon'))); ?>" class="confirm-delete"><button type="button" class="btn btn-danger btn-circle"><span class="fas fa-times"></span></button></a></p>
										<?php } ?>
									</div>
								</div>
							</div>
							<?php if($sale_data['coupon_id']==0) { ?>
							<div class="form-group no-margin">
								<?php echo $form_edit->input_html("submit","submit",'<span class="fas fa-chevron-right"></span> Apply',array('class'=>array('btn-warning'))); ?>
								<?php echo $form_edit->input_html("hidden","action","coupon_apply"); ?>
							</div>
							<?php } ?>
						</form>
					</div>
				</div>
			</div>
			<div id="tab_xero" class="tab-pane fade">
			<br>
				<?php if($class_xero->vars->enabled) { ?>
					<div class="panel panel-green">
						<div class="panel-heading"><span class="fas fa-link"></span> Xero&reg;</div>
						<div class="panel-body">

							<?php	if($is_xero) {
								if($xero_link!=NULL) { ?>
								<a href="<?php echo $xero_link; ?>" target="_blank" class="btn btn-default"><span class="fas fa-search"></span> View in Xero</a>
								<a href="<?php echo $zulu->link_page('sale',array('query'=>array('Action'=>'pay','id'=>PAGE_id,'Do'=>'xero_update'))); ?>" class="btn btn-default"><span class="fas fa-sync-alt"></span> Update Xero</a>
								<?php }  ?>
								<?php } else { ?>
								<a href="<?php echo $zulu->link_page('sale',array('query'=>array('Action'=>'pay','id'=>PAGE_id,'Do'=>'xero_export'))); ?>" class="btn btn-default"><span class="far fa-cloud-upload-alt"></span> Export to Xero</a>
								<a href="<?php echo $zulu->link_page('sale',array('query'=>array('Action'=>'pay','id'=>PAGE_id,'Do'=>'xero_export','authorised'=>1))); ?>" class="btn btn-default"><span class="fas fa-cloud-upload-alt"></span> Export to Xero (Authorised)</a>
							<?php }
								if($xero_stat['label']!=NULL) {
									echo "<span class=\"opt opt-".$xero_stat['class']."\"><i class=\"fas fa-".$xero_stat['icon']."\"></i> ".$xero_stat['label']."</span>";
								}
								if($xero_link!=NULL) { ?>
								<div class="row">
									<div class="col-sm-4">
										<div class="form-group">
											<h3>Invoice #</h3>
											<p class="static-form-control"><?php echo $xero_invoice['invoice']['InvoiceNumber']; ?></p>
										</div>
									</div>
									<div class="col-sm-4">
										<div class="form-group">
											<h3>Total</h3>
											<p class="static-form-control"><?php echo LOCALE_currency_symbol.number_format($xero_invoice['invoice']['Total'],2); ?>
										</div>
									</div>
									<div class="col-sm-4">
										<div class="form-group">
											<h3>Paid</h3>
											<p class="static-form-control"><?php echo LOCALE_currency_symbol.number_format($xero_invoice['invoice']['AmountPaid'],2); ?>
										</div>
									</div>
								</div>
							<?php } ?>
						</div>
					</div>
				<?php }else{ ?>
					<p>Xero&reg; is not enabled</p>
				<?php } ?>
			</div>
			<div id="tab_updates" class="tab-pane fade">
			<br>
				<div class="panel panel-info">
					<div class="panel-heading"><span class="fas fa-bullhorn"></span> Sale Updates</div>
					<div class="panel-body">
						<?php echo $zulu->template->body->table_log; ?>
					</div>
					<div class="panel-footer">
						<form method="post" action="">
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label>Select a default subject</label>
										<?php echo $form_edit->input_html("input",'title',$_POST['title'],['placeholder'=>"Example: Order Shipped",'class'=>['input-subject']]); ?>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label>(or) Enter a title</label>
										<?php echo $form_edit->input_html("select",'def_title',$_POST['def_title'],['placeholder'=>"Example: Order Shipped",'class'=>['input-default'],'option'=>[''=>'Select...','Awaiting Stock'=>'Awaiting Stock','Payment Received'=>'Payment Received','Awaiting Shipment'=>'Awaiting Shipment','Shipped'=>'Shipped','Arrived to Customer'=>'Arrived to Customer','Complete'=>'Complete',]]); ?>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<?php echo $form_edit->input_html("textarea",'data',$_POST['data'],['placeholder'=>"Optional extra information...",'rows'=>3]); ?>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-3">
									<div class="form-group no-margin">
										<?php echo $form_edit->input_html("submit","submit",'<span class="fas fa-chevron-right"></span> Save Update',array('class'=>array('btn-info btn-block'))); ?>
										<?php echo $form_edit->input_html("hidden","action","new_log"); ?>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group no-margin">
										<label class="checkbox-inline" style="padding-top: 5px;"><?php echo $form_edit->input_html("checkbox","status_alert",1,array('class'=>array('btn-email','checked'=>($_POST['status_alert']>0?true:false)))); ?> <span class="opt opt-grey"><i class="far fa-envelope"></i> Email customer?</span></label>
									</div>
								</div>
                                <?php if($display_complete){?>
                                <div class="col-md-3">
									<div class="form-group no-margin">
										<label class="checkbox-inline" style="padding-top: 5px;"><?php echo $form_edit->input_html("checkbox","sale_complete",1,array('class'=>array('','checked'=>($_POST['sale_complete']>0?true:false)))); ?> <span class="opt opt-grey"><i class="fas fa-check"></i> Complete sale?</span></label>
									</div>
								</div>
                                <?php }?>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

    <?php } ?>

	<?php if(PAGE_action=='edit') { ?>

    <div class="col-lg-12">
	<form role="form" action="" method="post" class="inline_label_input">

            <?php if($_POST['coupon_id']>0) { ?>
            <div class="alert alert-info">
                <span class="fas fa-ticket-alt"></span> Coupon used: <a class="alert-link" href="<?php echo $zulu->link_page('sale',array('query'=>array('Action'=>'coupon_edit','id'=>$_POST['coupon_id']))); ?>"><?php echo stripslashes($coupon_data['name']); ?></a>
            </div>
			<?php } ?>

        	<?php if($class_sale->view) { ?>
            <?php $zulu->notification(); ?>


            <p>
            <?php if(!$class_sale->complete) { ?>
          	<a href="<?php echo $zulu->link_page('sale',array('query'=>array('Action'=>'edit','id'=>PAGE_id))); ?>"><button type="button" class="btn btn-default"><span class="fas fa-edit"></span> Edit Sale</button></a>
            <a href="<?php echo $zulu->link_page('sale',array('query'=>array('Action'=>'sale_complete','id'=>PAGE_id))); ?>"><button type="button" class="btn btn-success"><span class="fas fa-check"></span> Complete Sale</button></a>
            <?php } ?>
            <?php if($user_data['email']!=NULL) { ?>
            <?php } else { ?>
            <a href="<?php echo $zulu->link_page('client',array('query'=>array('Action'=>'edit','id'=>$user_data['id'],'From'=>PAGE_id))); ?>" target="_blank"><button type="button" class="btn btn-info"><span class="fas fa-envelope"></span> Add Email</button></a>
            <?php } ?>
            <a href="#" class="bt-mail-receipt"><button type="button" class="btn btn-warning"><span class="fas fa-envelope"></span> Mail Receipt</button></a>
            <a href="<?php echo $zulu->link_page('sale',array('query'=>array('Action'=>'pay','id'=>PAGE_id))); ?>"><button type="button" class="btn btn-success"><span class="far fa-money-bill"></span> View / Make Payments</button></a>
            <a href="<?php echo $zulu->link_page('sale',array('query'=>array('Action'=>'print','id'=>PAGE_id))); ?>" target="_blank"><button type="button" class="btn btn-default"><span class="fas fa-print"></span> Print</button></a>
            <?php } ?>

            <?php if($class_user->authorised->opt_support&&$class_sale->complete) { ?>
            <a href="<?php echo $zulu->link_page('support',array('query'=>array('SaleID'=>PAGE_id,'client_id'=>$sale_data['client_id']))); ?>" target="_blank"><button type="button" class="btn btn-default"><span class="far fa-life-ring"></span> View Support Tickets</button></a>

            <a href="<?php echo $zulu->link_page('support',array('query'=>array('Action'=>'edit_ticket','object'=>'sale','object_id'=>PAGE_id,'client_id'=>$sale_data['client_id']))); ?>" target="_blank"><button type="button" class="btn btn-default"><span class="fas fa-plus-circle"></span> New Support Ticket</button></a>
            <?php } ?>

            </p>
            <div class="panel panel-default">
            	<div class="panel-heading ">
                	<div class="row">
                    	<div class="col-md-6">
                			<span class="far fa-money-bill"></span> Sale Details
                		</div>
                        <div class="col-md-6 text-right">
                        	<?php if(!$new) { ?>
                        	<span class="color-grey"><span class="fas fa-bars"></span> Options</span>
            				<?php if($class_sale->view) { ?>
            				<?php if(!$class_sale->locked) { ?>
                            <a href="<?php echo $zulu->link_page('sale',array('query'=>array('Action'=>'edit','id'=>PAGE_id))); ?>"><button class="btn btn-default btn-xs" type="button"><span class="fas fa-edit"></span> Edit Sale</button></a>
                            <?php } } else { ?>
                            <a href="<?php echo $zulu->link_page('sale',array('query'=>array('Action'=>'edit','id'=>PAGE_id,'Method'=>'View'))); ?>"><button class="btn btn-default btn-xs" type="button"><span class="far fa-eye"></span> View Sale</button></a>
                            <?php } ?>
            				<?php if($sale_meta['xero_link']['value']==NULL) { ?>
                            <a href="<?php echo $zulu->link_page('sale',array('query'=>array('Action'=>'xero','id'=>PAGE_id))); ?>"><button class="btn btn-default btn-xs" type="button"><span class="fas fa-cloud-upload-alt"></span> Export Xero</button></a>
                            <?php } }?>
                        </div>
                	</div>
                </div>
                <div class="panel-body">
                	<?php if($class_sale->view) { ?>
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Customer</label>
                                <p class="form-control-static"><?php echo ($_POST['client_id']>0?"<a href=\"".$zulu->link_page('client',array('query'=>array('Action'=>'edit','id'=>$_POST['client_id'])))."\">".$_POST['name']."</a>":$_POST['name']); ?></p>

								<?php if($sale_meta['fe']['value']>0) { ?>
                                <span class="opt opt-grey opt-bord text-mini"><i class="fas fa-laptop"></i> Web Order</span>
                                <?php } ?>&nbsp;
								<?php if($sale_meta['ship_type']['value']!=NULL) { ?>
                                <span class="opt opt-danger opt-bord text-mini"><i class="fas fa-truck"></i> <?php echo $sale_meta['ship_type']['value']; ?></span>
                                <?php } ?>&nbsp;
                                <?php if($sale_meta['fe_payment']['value']!=NULL) { ?>
                                <span class="opt opt-warning opt-bord text-mini"><i class="fas fa-university"></i> <?php echo $class_sale->config->payment[$sale_meta['fe_payment']['value']]; ?></span>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Date</label>
                                <p class="form-control-static"><?php echo $_POST['date']; ?></p>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Date Due</label>
                                <p class="form-control-static"><?php echo $_POST['date_due']; ?></p>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Sale # (Automatic)</label>
                                <?php if($new) { ?>
                                <?php echo $form_edit->input_html("input","reference",($_POST['reference']!=NULL?$_POST['reference']:$class_sale->reference_gen())); ?>
                                <?php } else { ?>
                                <p class="form-control-static"><?php echo $_POST['reference']; ?></p>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
						<?php if($_POST['email']!=null) { ?>
                        <div class="col-lg-3">
                            <label>Email</label>
                            <p class="form-control-static"><?php echo $_POST['email']; ?></p>
                        </div>
                        <?php } ?>
                        <?php if($_POST['phone']!=NULL) { ?>
                        <div class="col-lg-3">
                            <label>Phone</label>
                            <p class="form-control-static"><?php echo $_POST['phone']; ?></p>
                        </div>
                        <?php } ?>

                    	<?php if($coupon_code!=NULL) { ?>
                        <div class="col-lg-3">
                        	<label>Coupon Code</label>
                            <p class="form-control-static"><?php echo $coupon_code; ?></p>
                        </div>
                        <?php } ?>
						<?php if($show_shipping) { ?>
                    	<div class="col-lg-3">
                        	<label>Shipping Address</label>
                            <p class="form-control-static"><?php echo $ship_address; ?></p>
                        </div>
                        <?php } ?>
                        <?php if($sale_meta['bill_address']['value']) { ?>
                        <div class="col-lg-3">
                        	<label>Billing Address</label>
                            <p class="form-control-static"><?php echo $bill_address; ?></p>
                        </div>
                        <?php } ?>
                         <?php if($ship_notes!=NULL) { ?>
                         <div class="col-lg-3">
                        	<label>Shipping Notes</label>
                            <p class="form-control-static"><?php echo $ship_notes; ?></p>
                        </div>
                         <?php } ?>
                    </div>

                    <?php } else { ?>
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Contact</label>
                        		<?php echo $form_edit->input_html("input","name",$_POST['name'],array('autocomplete'=>false,'class'=>array('sf-input','typeahead','sf-input-user_id'),'custom'=>array('data-sf'=>'sf_customer','data-populate'=>'user_id','autocomplete'=>'off'))); ?>
                        		<div class="guessbox guessbox-user_id">
                           			<ul></ul>
                        		</div>
                        		<?php echo $form_edit->input_html("hidden","client_id",$_POST['client_id'],array('id'=>'sf-user_id','class'=>array('sf-value'))); ?>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Date</label>
                                <?php echo $form_edit->input_html("input","date",($_POST['date']!=NULL?$_POST['date']:date('d/m/Y'))); ?>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Date Due</label>
                                <?php echo $form_edit->input_html("input","date_due",($_POST['date_due']!=NULL?$_POST['date_due']:date('d/m/Y'))); ?>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Sale # (Automatic)</label>
                                <?php if($new) { ?>
                                <?php echo $form_edit->input_html("input","reference",($_POST['reference']!=NULL?$_POST['reference']:$class_sale->reference_gen())); ?>
                                <?php } else { ?>
                                <p class="form-control-static"><?php echo $_POST['reference']; ?></p>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
            	</div>
            </div>

            <div class="panel panel-default">
            	<div class="panel-heading ">
				   <div class="row">
						<div class="col-md-6">
							<span class="fas fa-shopping-cart"></span> Line Item(s)
						</div>
						<div class="col-md-6 text-right">
							<?php if(!$class_sale->view) { ?>
							<span class="color-grey"><span class="fas fa-bars"></span> Options</span>
							<a class="btn btn-primary btn-xs" href="javascript:void(0);" id="btn_surcharge"><i class="fas fa-credit-card"></i> Add Surcharge</a>
							<?php }?>
						</div>
					</div>
               </div>
                <div class="panel-body">
            <table class="table table-hover table-bordered table-striped line-items" name="line-items" id="line-items">
    			<tr>
                	<th class="sku">SKU</th>
                    <th class="des">Description</th>
                    <th class="unit">Unit Price</th>
                    <th class="quan">Quantity</th>
                    <th class="disc">Discount</th>
                    <th class="linettl">Line Total</th>
                    <th class="action"></th>
                </tr>
                <?php for($i=1;$i<=($class_sale->view?count($_POST['line']):(count($_POST['line'])>4?count($_POST['line']):4));$i++) {

				$line_subtotal = ($_POST['line'][$i]['price']*$_POST['line'][$i]['qty']);
				$line_disc = $_POST['line'][$i]['disc'];

				//$line_disc = $line_subtotal*($_POST['line'][$i]['disc']/100);
				$line_total = ($line_subtotal-$line_disc);
				$order_total += $line_total;
				$_POST['line'][$i]['disc'] = ((($line_disc/$line_subtotal)*100)>0?(($line_disc/$line_subtotal)*100):0);

				if($class_sale->view) { ?>
				<tr>
                	<td class="sku"><p class="form-control-static"><?php echo $_POST['line'][$i]['sku']; ?></p></td>
                	<td class="des"><p class="form-control-static"><?php echo $_POST['line'][$i]['description']; ?></p></td>
                    <td class="unit"><p class="form-control-static">$<?php echo number_format($_POST['line'][$i]['price'],2); ?></p></td>
                    <td class="quan"><p class="form-control-static"><?php echo $_POST['line'][$i]['qty']; ?></p></td>
                    <td class="disc"><p class="form-control-static"><?php echo number_format($_POST['line'][$i]['disc'],2); ?>%</p></td>
                    <td class="linettl"><p class="form-control-static">$<?php echo number_format($line_total,2); ?></p></td>
                    <td class="action"><a title="View the item linked to this sale item." href="<?php echo $_POST['line'][$i]['link']; ?>" class="opt opt-primary"><span class="fas fa-link"></span></a></td>
				</tr>
                <?php } else { ?>
				<tr>
                	<td class="sku"><?php echo $form_edit->input_html("input","line[{$i}][sku]",$_POST['line'][$i]['sku'],array('autocomplete'=>false,'class'=>array('sf-input','typeahead','sf-input-sku','input-sku'),'custom'=>array('data-sf'=>'sf_product_sku','data-id'=>$i,'data-populate'=>'sku','autocomplete'=>'off'))); ?>
                        		<div class="guessbox guessbox-sku">
                           			<ul></ul>
                        		</div>
                        		<?php echo $form_edit->input_html("hidden","sku",$_POST['sku'],array('id'=>'sf-sku','class'=>array('sf-value'))); ?></td>
                    <td class="des"><?php echo $form_edit->input_html("input","line[{$i}][description]",$_POST['line'][$i]['description'],array('class'=>array('input-description'))); ?></td>
                    <td class="unit">
                    	<div class="input-group">
                        	<span class="input-group-addon">$</span>
							<?php echo $form_edit->input_html("input","line[{$i}][price]",$_POST['line'][$i]['price'],array('class'=>array('line-item-unit','input-price'),'custom'=>array('data-row'=>$i,'data-row-type'=>'unit'),'id'=>'line-'.$i.'-unit')); ?>
                    	</div>
                    </td>
                    <td class="quan"><?php echo $form_edit->input_html("input","line[{$i}][qty]",$_POST['line'][$i]['qty'],array('class'=>array('line-item-quan'),'custom'=>array('data-row'=>$i,'data-row-type'=>'quan'),'id'=>'line-'.$i.'-quan')); ?></td>
					<td class="disc"><div class="input-group"><?php echo $form_edit->input_html("input","line[{$i}][disc]",$_POST['line'][$i]['disc'],array('class'=>array('line-item-disc'),'custom'=>array('data-row'=>$i,'data-row-type'=>'disc'),'id'=>'line-'.$i.'-disc')); ?><span class="input-group-addon">%</span></div></td>
                    <td class="linettl"><p class="form-control-static">$<span class="line-item-total" id="line-<?php echo $i; ?>-total">0.00</span></p></td>
                    <td class="action">
                    	<a href="<?php echo $_POST['line'][$i]['link']; ?>" class="opt opt-primary" title="View the item linked to this sale item."><span class="fas fa-link"></span></a>
                    	<a href="#" data-line="<?php echo $_POST['line'][$i]['line_id']; ?>" class="line-remove opt opt-danger"><span class="fas fa-times"></span></a><?php echo $form_edit->input_html("hidden","line[{$i}][line_id]",$_POST['line'][$i]['line_id']); ?></td>
				</tr>
                <?php } }

				//Totals
				$order_total += $_POST['ship_price'];
				$payment_summary = $class_sale->payment_summary($order_total,['sale_id'=>$sale_data['id']]);

				$order_subtotal = $payment_summary['subtotal'];
				$order_tax = $payment_summary['tax'];
				$order_total = $payment_summary['total'];
				?>
            </table>
            <?php echo $form_edit->input_html("hidden","row_count",$i-1,array('id'=>'row_count')); ?>
            <?php echo $form_edit->input_html("hidden","sale-summary-subtotal-hidden",number_format($order_subtotal,2),array('id'=>'sale-summary-subtotal-hidden')); ?>
            <?php echo $form_edit->input_html("hidden","sale-summary-total-hidden",number_format($order_total,2),array('id'=>'sale-summary-total-hidden')); ?>

            <div class="row">
            	<div class="col-md-10">
            		<?php if(!$class_sale->view) { ?>
                    <p class="text-center">
                        <a href="#" id="bt-add-invoice-row"><button class="btn btn-primary" type="button"><i class="fas fa-plus-circle"></i> Add New Row</button></a>
                    </p>
                    <?php } ?>
                </div>
            	<div class="col-md-2">
                	<div class="row price-box">
                    	<div class="col-xs-6">
                          	<?php if($class_sale->view && $order_discount>0) { ?>
                          	<p class="lbl-disc">Discount</p>
                          	<?php } ?>
                            <p class="lbl-sub">Subtotal</p>
                            <?php echo ($_POST['ship_price']!=NULL?"<p class=\"lbl-ship\">Shipping</p>":NULL); ?>
                            <?php if(($order_tax>0)||(!$class_sale->gst_disable&&$new)) { ?><p class="lbl-gst"><?php echo $tax_label; ?> (<?php echo $tax_label_meth; ?>)</p><?php } ?>
                            <p class="lbl-ttl">Total</p>
                        </div>
                    	<div class="col-xs-6 text-right">
                        	<?php if($class_sale->view) { ?>
                         	<?php if($order_discount > 0) { ?>
                         		<p class="lbl-disc b">$<span class="sale-summary-disc" id="sale-summary-disc"><?php echo number_format($order_discount,2); ?></span></p>
                         	<?php } ?>
                            <p class="lbl-sub b">$<span class="sale-summary-subtotal" id="sale-summary-subtotal"><?php echo number_format($order_subtotal,2); ?></span></p>
                            <?php echo ($_POST['ship_price']!=NULL?"<p class=\"lbl-ship b\">$<span class=\"sale-summary-shipping\" id=\"sale-summary-shipping\">".number_format($_POST['ship_price'],2)."</span></p>":NULL); ?>
                            <?php if($order_tax>0) { ?><p class="lbl-gst b">$<span class="sale-summary-gst" id="sale-summary-gst"><?php echo number_format($order_tax,2); ?></span></p><?php } ?>
                            <p class="lbl-ttl b">$<span class="sale-summary-total" id="sale-summary-total"><?php echo number_format($order_total,2); ?></span></p>
                            <?php } else { ?>
                            <p class="lbl-sub b">$<span class="sale-summary-subtotal" id="sale-summary-subtotal">0.00</span></p>
                            <?php echo ($_POST['ship_price']!=NULL?"<p class=\"lbl-ship b\">$<span class=\"sale-summary-shipping\" id=\"sale-summary-shipping\">".number_format($_POST['ship_price'],2)."</span></p>":NULL); ?>
                            <?php if(($order_tax>0)||(!$class_sale->gst_disable&&$new)) { ?><p class="lbl-gst b">$<span class="sale-summary-gst" id="sale-summary-gst">0.00</span></p><?php } ?>
                            <p class="lbl-ttl b">$<span class="sale-summary-total" id="sale-summary-total">0.00</span></p>
                            <?php } ?>
                        </div>
                	</div>
                </div>
            </div>

            </div>
			<?php if($class_sale->locked) { ?>
            <div class="panel-footer color-grey">
            	<i class="fas fa-lock"></i> This sale is now locked for editing.
            </div>
            <?php } ?>
        </div>
		<?php if($class_sale->view) { ?>
		<?php } else { ?>
		<?php echo $form_edit->input_html("submit","submit_delete",($new?'<span class="fas fa-reply"></span> Cancel':'<span class="fas fa-times"></span> Delete'),array('class'=>array('confirm btn-lg'))); ?>
		<?php echo $form_edit->input_html("submit","submit_park",'<span class="fas fa-pause"></span> Save &amp; Park',array('title'=>'This will save the sale into the drafts area.','class'=>array('btn-warning btn-lg'))); ?>
		<?php echo $form_edit->input_html("submit","submit_complete",'<span class="fas fa-check"></span> Complete',array('title'=>'This will complete the sale and adjust any stock.','class'=>array('btn-success btn-lg'))); ?>

		<?php echo $form_edit->input_html("hidden","action",'edit'); ?>

        <div id='modal-price-break' class='modal fade modal-price-break' role='dialog'>
            <div class='modal-dialog modal-lg'>
                <div class='modal-content'>
                    <div class='modal-header'><button type='button' class='close' data-dismiss='modal'>&times;</button><h4 class='modal-title'>Price Adjustment</h4></div>
                    <div class='modal-body'>
                        <p>You've reached a Price Break for this item, would you like to apply the highlighted Price Break.</p>
                        <div id="table-container"></div>
                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-success pull-left price-break-apply' data-dismiss='modal' data-sku='' data-price='' data-price-break=''><i class='fas fa-check'></i> Apply</button>
                        <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
                    </div>
                </div>
            </div>
        </div>
		<?php } ?>
	</form>
    </div>

    <?php } ?>
    <?php if(PAGE_action=='report') { ?>
	<div class="col-md-12">
		<form method="get" action="">
		<div class="panel panel-default">
			<div class="panel-heading"><i class="fas fa-filter"></i> Filter</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-md-3">
						<div class="field">
							<label>Date From</label>
							<?php echo $form_edit->input_html("input","date_from",($_GET['date_from']!=NULL?$_GET['date_from']:date('d/m/Y',strtotime('first day of last month'))),['class'=>['date']]); ?>
						</div>
					</div>
					<div class="col-md-3">
						<div class="field">
							<label>Date From</label>
							<?php echo $form_edit->input_html("input","date_to",($_GET['date_to']!=NULL?$_GET['date_to']:date('d/m/Y',strtotime('last day of last month'))),['class'=>['date']]); ?>
						</div>
					</div>
					<div class="col-md-3">
						<div class="field">
							<label></label>
							<p class="static-form-control"><button type="submit" class="btn btn-success"><i class="fas fa-filter"></i> Generate Data</button></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" name="Page" value="<?php echo PAGE_file; ?>" />
		<input type="hidden" name="Action" value="<?php echo PAGE_action; ?>" />
		</form>
	</div>
	<?php if($filtered) { ?>
	<div class="col-md-8">
		<div class="panel panel-primary">
			<div class="panel-heading"><i class="far fa-money-bill"></i> Sales in this period</div>
			<div class="panel-body">
				<?php echo $zulu->template->table_sale; ?>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="panel panel-green">
			<div class="panel-heading"><i class="fas fa-university"></i> Gross Sales</div>
			<div class="panel-body">
				<?php echo $zulu->template->body->table_gross; ?>
			</div>
		</div>
		<div class="panel panel-info">
			<div class="panel-heading"><i class="fas fa-university"></i> Supplier Gross Sales</div>
			<div class="panel-body">
				<?php echo $zulu->template->body->table_supplier; ?>

				<form method="post" action="">
					<div class="form-group">
						<label>Commission Percentage</label>
						<div class="row">
							<div class="col-md-6"><?php echo $form_edit->input_html("input","com_per",$_POST['com_per']); ?></div>
							<div class="col-md-6"><button type="submit" class="btn btn-success btn-block"><i class="fas fa-calculator"></i> Calculate</button></div>
						</div>
					</div>

				</form>

			</div>
		</div>
		<div class="panel panel-info">
			<div class="panel-heading"><i class="fas fa-filter"></i> Product Gross Sales</div>
			<div class="panel-body">
				<?php echo $zulu->template->body->table_product; ?>
			</div>
		</div>
	</div>
	<?php } ?>
	<?php } ?>
	<?php if(PAGE_action=='abandon') { ?>
	<div class="col-md-12">
		<form method="get" action="">
		<div class="panel panel-default">
			<div class="panel-heading"><i class="fas fa-filter"></i> Filter</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-md-3">
						<div class="field">
							<label>Date From</label>
							<?php echo $form_edit->input_html("input","date_from",($_GET['date_from']!=NULL?$_GET['date_from']:date('d/m/Y',strtotime('first day of last month'))),['class'=>['date']]); ?>
						</div>
					</div>
					<div class="col-md-3">
						<div class="field">
							<label>Date From</label>
							<?php echo $form_edit->input_html("input","date_to",($_GET['date_to']!=NULL?$_GET['date_to']:date('d/m/Y',strtotime('last day of last month'))),['class'=>['date']]); ?>
						</div>
					</div>
					<div class="col-md-3">
						<div class="field">
							<label></label>
							<p class="static-form-control"><button type="submit" class="btn btn-success"><i class="fas fa-filter"></i> Generate Data</button></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" name="Page" value="<?php echo PAGE_file; ?>" />
		<input type="hidden" name="Action" value="<?php echo PAGE_action; ?>" />
		</form>
	</div>
	<?php if($filtered) { ?>
	<div class="col-md-12">
		<div class="panel panel-primary">
			<div class="panel-heading"><i class="far fa-money-bill"></i> Summary of abandoned sales</div>
			<div class="panel-body">
				<div class="row">
					<?php foreach($class_website->config->shop_cart_steps as $shop_key=>$shop_step) {
					if($shop_key==1) {
						$dropoff = $class_setting->data['sale_abc_initial'];
					} else {
						$dropoff = $stat['abandon_do_step'][$shop_key];
					}
					?>
					<div class="col-md-3 text-center">
						<p class="h2 no-margin">Step. <?php echo $shop_key; ?></p>
						<p class="h3 no-margin"><?php echo $shop_step['title']; ?></p>
						<p><a href="<?php echo $zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'sale_step','Step'=>$shop_key]]); ?>" class="btn btn-xs btn-default"><i class="fa fa-bars"></i> View</a></p>

						<div style="padding: 10px 30px;">
							<div class="panel panel-green no-margin">
								<div class="panel-heading">
									Completed
								</div>
								<div class="panel-body">
									<p class="h2 no-margin"><i class="fa fa-caret-right"></i> <?php echo $stat['abandon_step'][$shop_key]; ?></p>
								</div>
							</div>
							<p>&nbsp;</p>
							<?php if(count($class_website->config->shop_cart_steps)!=$shop_key) { ?>
							<div class="panel panel-red no-margin">
								<div class="panel-heading">
									Drop-off
								</div>
								<div class="panel-body">
									<p class="h2 no-margin"><i class="fa fa-caret-down"></i> <?php echo $dropoff; ?></p>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<div class="panel panel-primary">
			<div class="panel-heading"><i class="far fa-money-bill"></i> Sales data grouped by abandoned step</div>
			<div class="panel-body">

				<ul class="nav nav-tabs">
					<?php $this_i = 0;  foreach($class_website->config->shop_cart_steps as $shop_key=>$shop_step) { ?>
					<li class="<?php echo ($this_i==1?"active":NULL); ?>"><a href="#tab-<?php echo $shop_key; ?>" data-toggle="tab"><?php echo $shop_key; ?>. <?php echo $shop_step['title']; ?></a></li>
					<?php $this_i++; } ?>
				</ul>
				<div class="tab-content">
				<?php $this_i = 0; foreach($class_website->config->shop_cart_steps as $shop_key=>$shop_step) { ?>
					<div class="tab-pane fade in <?php echo ($this_i==1?"active":NULL); ?>" id="tab-<?php echo $shop_key; ?>">
						<h3>Sales Abandoned at Step <?php echo $shop_key; ?>. <?php echo $shop_step['title']; ?></h3>
						<?php if($shop_key==1||count($class_website->config->shop_cart_steps)==$shop_key) {
							echo "<p class='opt opt-grey no-margin margin-0'><i class='fas fa-times'></i> ".($shop_key==1?"You cannot view abandoned sales for this step because no sale data is captured.":"Please refer to the <a href='".$zulu->link_page('sale')."'>sales</a> page to view all completed sales.")."</p>";
						} else {
							echo $zulu->template->body_sale[$shop_key];
						} ?>
					</div>
				<?php $this_i++; } ?>
				</div>

			</div>
		</div>
	</div>
	<?php } ?>
	<?php } ?>
	<?php if(PAGE_action=='coupon') { ?>
    <div class="col-lg-12">
		<p>
            <a href="<?php echo $zulu->link_page('sale',array('query'=>array('Action'=>'coupon_edit'))); ?>" class="btn btn-primary"><span class="fas fa-plus-circle"></span> Add New Coupon</a>
            <a href="<?php echo $zulu->link_page('sale',array('query'=>array('Action'=>'coupon_edit','Bulk'=>'1'))); ?>" class="btn btn-primary"><span class="fa fa-ticket-alt"></span> Bulk Coupon Create</a>
        </p>
        <div class="row">
            <div class="col-md-<?php echo (count($export_glob)<=0?'12':'9'); ?>">
                <div class="panel panel-default">
                    <!-- /.panel-heading -->
                    <div class="panel-body">
                        <form action="" method="get">
                            <?php echo $form_edit->input_html("hidden","Page",$_GET['Page']); ?>
                            <?php echo $form_edit->input_html("hidden","Action",$_GET['Action']); ?>
                            <?php echo $form_edit->input_html("hidden","Tab",$_GET['Tab']); ?>
                            <div class="row">
                                <div class="col-md-<?php echo (count($export_glob)<=0?'2':'3'); ?>">
                                    <div class="input-group">
                                        <?php echo $form_edit->input_html("input","Search",$_GET['Search'],['custom'=>['placeholder'=>'Search','id'=>'search-box']]); ?>
                                        <span class="input-group-btn"><?php echo $form_edit->input_html("submit","",'<span class="fas fa-search"></span>',['custom'=>['id'=>'search-submit']]); ?></span>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <ul class="nav nav-tabs" style="margin-bottom:10px;">
                                <?php foreach($tab_list as $key=>$name) { ?>
                                <li class="<?php echo ($selected_tab==$key?"active":NULL); ?>"><a class="layout-load" data-template="<?php echo $key; ?>" href="<?php echo $zulu->link_page(PAGE_file,['self'=>true,'query'=>["Tab"=>$key],'filter'=>['Pg']]); ?>"><?php echo $name; ?></a></li>
                                <?php } ?>
                                </ul>
                            </div>
                        </div>
                        <?php echo $zulu->template->body; ?>
                        <div align="center"><?php echo $pagination; ?></div>
                        <p class="page-count text-center opt opt-grey"><i class="fas fa-bars"></i> <?php echo $coupon_count_full; ?> record(s) in total</p>
                    </div>
                </div>
            </div>
            <?php if(count($export_glob) > 0) { ?>
            <div class="col-md-3">
                <div class="panel panel-info">
                    <div class="panel-heading">Bulk Creation Exports</div>
                    <div class="panel-body">
                        <?php echo $zulu->template->export_table; ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>
    <?php if(PAGE_action=='coupon_edit') { ?>
    <form role="form" action="" method="post" class="inline_label_input">
    <div class="col-lg-8">
    <?php if(!$new) { ?>
        <div class="panel panel-default">
            <div class="panel-heading">Properties</div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Issued / Created</label>
                            <p class="form-control-static"><?php echo zulu::dateDecode($_POST['stat_add'],'d/m/Y h:ia'); ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Created By</label>
                            <p class="form-control-static"><?php echo $coupon_create; ?></p>
                        </div>
                    </div>
                    <?php if($sale_id>0) { ?>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Sale</label>
                            <p class="form-control-static"><a href="<?php echo $zulu->link_page('sale',array('query'=>array('Action'=>'edit','id'=>$sale_id,"Method"=>'View'))); ?>">#<?php echo $sale_ref; ?></a></p>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php } ?>

        <div class="panel panel-default">
            <div class="panel-heading">Details</div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Name <em>*</em></label>
                            <?php echo $form_edit->input_html("input","name",$_POST['name']); ?>
                        </div>
                    </div>
                    <?php if(!$bulk_create) { ?>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Code</label>
                            <?php echo $form_edit->input_html("input","code",$_POST['code']); ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Type</label>
                            <?php echo $form_edit->input_html("select","type",$_POST['type'],['option'=>$class_sale->coupon_type]); ?>
                        </div>
                    </div>
                    <?php } else { ?>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Type</label>
                            <?php echo $form_edit->input_html("select","type",$_POST['type'],['option'=>$class_sale->coupon_type]); ?>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Code Prefix</label>
                            <?php echo $form_edit->input_html("input","code_prefix",$_POST['code_prefix']); ?>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Quantity</label>
                            <?php echo $form_edit->input_html("number","quantity",$_POST['quantity'],['custom'=>['min'=>'0']]); ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Terms</label>
                            <?php echo $form_edit->input_html("input","terms",$_POST['terms']); ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Description</label>
                            <?php echo $form_edit->input_html("textarea","description",$_POST['description']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">Configuration</div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Start Date <em>*</em></label>
                            <?php echo $form_edit->input_html("input","conf_start",$_POST['conf_start'],array('custom'=>array('placeholder'=>'DD/MM/YYYY'))); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Expiry Date <em>*</em></label>
                            <?php echo $form_edit->input_html("input","conf_expire",$_POST['conf_expire'],array('custom'=>array('placeholder'=>'DD/MM/YYYY'))); ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Multiple Customers Allowed</label>
                            <?php echo $form_edit->input_html("select","conf_member[]",$_POST['conf_member'],array('option'=>$form_edit->clientOptionForm(true,1),'custom'=>array('multiple'=>'multiple'))); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Single Customer Allowed</label>
                            <?php echo $form_edit->input_html("select","conf_member_id",$_POST['conf_member_id'],array('option'=>$form_edit->clientOptionForm(true,1))); ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Max Usages Overall</label>
                            <?php echo $form_edit->input_html("input","conf_max",$_POST['conf_max']); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Max Usages Per Order</label>
                            <?php echo $form_edit->input_html("input","conf_max_sale",$_POST['conf_max_sale']); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Max Usages Per Member</label>
                            <?php echo $form_edit->input_html("input","conf_max_member",$_POST['conf_max_member']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">Discount</div>
            <div class="panel-body">
                <div id="coupon-settings" class="disc-settings">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Type</label>
                                <?php echo $form_edit->input_html("select","discount_type",$_POST['discount_type'],array('option'=>$coupon_options)); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Amount <em>*</em></label>
                                <?php echo $form_edit->input_html("input","discount_amount",$_POST['discount_amount']); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Product Min. Quantity</label>
                                <?php echo $form_edit->input_html("input","discount_object_min",$_POST['discount_object_min']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Object Type</label>
                                <?php echo $form_edit->input_html("select","discount_object",$_POST['discount_object'],array('option'=>['product'=>'Product','event_ticket'=>'Event Ticket'])); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Object</label>
                                <?php echo $form_edit->input_html("select","discount_object_id",$_POST['discount_object_id'],array('option'=>$product_options,'id'=>'object_id_1')); ?>
                            </div>
                        </div>
                        <div class="col-md-4" id="object-second">
                            <div class="form-group">
                                <label>Object</label>
                                <?php echo $form_edit->input_html("select","discount_object_id",$_POST['discount_object_id'],['id'=>'object_id_2']); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="voucher-settings" class="disc-settings">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Amount <em>*</em></label>
                                <div class="input-group">
                                    <span class="input-group-addon"><?php echo LOCALE_currency_symbol; ?></span>
                                    <?php echo $form_edit->input_html("input","voucher_amount",$_POST['voucher_amount']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Amount Remaining</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><?php echo LOCALE_currency_symbol; ?></span>
                                    <?php echo $form_edit->input_html("input","discount_remain",$_POST['discount_remain']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            <div class="form-group">
                <?php echo $form_edit->input_html("submit","submit",'Save'); ?>
                <?php echo $form_edit->input_html("hidden","action","edit"); ?>
                <?php echo $form_edit->validate_reset(); ?>
            </div>


        <div class="panel panel-info">
            <div class="panel-heading"><span class="fas fa-info-circle"></span> Note</div>
            <div class="panel-body">
            <p>'Max' fields with a value of '0' mean no maximum value (i.e. infinite)</p>
            </div>
        </div>
    </div>
    <?php if(PAGE_id > 0) { ?>
    <div class="col-lg-4">
    	<?php if($is_voucher) { ?>
        <div class="panel panel-default">
            <div class="panel-heading">Voucher Details</div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>To</label>
                            <?php echo $form_edit->input_html("input","gift_to",stripslashes($_POST['gift_to'])); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>From </label>
                            <?php echo $form_edit->input_html("input","gift_from",stripslashes($_POST['gift_from'])); ?>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Message </label>
                            <?php echo $form_edit->input_html("input","gift_message",stripslashes($_POST['gift_message'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    	<div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
    			<?php echo $zulu->template->body; ?>
            </div>
    	</div>
    </div>
     </form>
    <?php } ?>
    <?php } ?>
</div>
<br />
