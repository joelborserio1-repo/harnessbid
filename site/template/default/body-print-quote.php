    <div class="container">
        <div class="row">

        	<div class="document">

                <div class="document-head style-blue box-comment" id="box-comment">
                	<h3 class="no-margin"><span class="fas fa-paper-plane"></span> Send comments </h3>
                    <?php echo ($comment_nfc?"<div class=\"spacer10\"></div>".$zulu->notification():NULL); ?>
                    <hr>
                    <form method="post" action="">
                    	<div class="row">
                        	<div class="col-md-4">
                            	<div class="form-group">
                                	<label>Your Name</label>
                                    <?php echo $form_edit->input_html("input","name",($_POST['name']==NULL?$quote_meta['contact']:$_POST['name'])); ?>
                                </div>
                            </div>
                        	<div class="col-md-4">
                            	<div class="form-group">
                                	<label>Your Email</label>
                                    <?php echo $form_edit->input_html("input","email",($_POST['email']==NULL?$quote_meta['email']:$_POST['email'])); ?>
                                </div>
                            </div>
                        </div>

                    	<div class="row">
                        	<div class="col-md-12">
                            <div class="form-group">
                                <label>Your Comment / Query</label>
                                    <?php echo $form_edit->input_html("textarea","message",$_POST['message']); ?>
                            </div>
                            </div>
                        </div>
                    	<div class="row">
                        	<div class="col-md-12">
                				<?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-paper-plane\"></span> Send Comment",array("class"=>array("btn-success"))); ?>
                				<?php echo $form_edit->input_html("hidden","method",'comment'); ?>
                            </div>
                        </div>
                    </form>
                </div>

            	<?php if($zulu->template->page_def['paid']) { ?>
                <div class="document-head style-green text-center">
                	<h3 class="no-margin"><span class="fas fa-check"></span> Sale has been fully paid ($<?php echo $zulu->dollar($zulu->template->page_def['paid_amount']); ?>)</h3>
                </div>
                <?php } ?>
            	<?php if($zulu->template->page_def['overdue']) { ?>
                <div class="document-head style-red text-center">
                	<h3 class="no-margin"><span class="fas fa-exclamation-triangle"></span> This sale is now overdue!</h3>
                </div>
                <?php } ?>
            	<?php if($class_quote->vars->signed) { ?>
                <div class="document-head style-green text-center">
                	<h3 class="no-margin"><span class="fas fa-check"></span> Quote is signed and approved.</h3>
                </div>
                <?php } ?>

                <div class="document-head">

                	<div class="coltable col3 vmiddle">
                    	<div class="col col-left">
                        	<?php if($setting_data['quote_logo_path']!=NULL) { ?>
                        	<img class="responsive" src="<?php echo $setting_data['quote_logo_path']; ?>" alt="<?php echo $setting_data['company']; ?>" />
                            <?php } else {  ?>
                        	<h2><?php echo $setting_data['company']; ?></h2>
                            <?php } ?>
                        </div>
                    	<div class="col col-center">
                        </div>
                    	<div class="col col-right">
                        	<h2 class="no-margin"><?php echo ($class_quote->vars->sign||!$zulu->template->page_def['draft']?NULL:"Draft "); ?><?php echo $zulu->template->page_def['type']; ?> #<?php echo $zulu->template->page_def['reference']; ?></h2>
                            <p class="no-margin"><?php echo $quote_reference; ?></p>
                        </div>
                    </div>

                </div>

                <div class="document-body">
                <?php echo $zulu->notification(); ?>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">Your Details</div>
                            <div class="panel-body">
                            		<div class="row">
                                    <div class="col-xs-6">
                                			<p><b><?php echo $client_out['name']; ?></b><br><?php echo $client_out['address']; ?></p>
                                    </div>
                                    <div class="col-xs-6">
										<p><?php echo $tpl_out['reference']; ?></p>
                                    	 <p><?php echo $tpl_out['date_full']; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="panel panel-default">
                            <div class="panel-heading"><?php echo $company_out['company']; ?></div>
                            <div class="panel-body">
                                <p><?php echo $zulu->template->page_def['company_html']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if($zulu->template->body->bill_count>0) { ?>
                <hr>
                <h2>Itemisation of <?php echo $zulu->template->page_def['type']; ?></h2>
                <?php echo $zulu->template->body->table_item; ?>
                <div class="row">
            	<div class="col-md-9">
                    <?php if($deposit) { ?>
                    <p class="color-grey"><span class="far fa-money-bill"></span> Deposit of <?php echo LOCALE_currency_symbol.$deposit_amount; ?> <sup><?php echo $setting_data['tax_method_label']; ?> <?php echo $setting_data['tax_label']; ?></sup> required upon signing.</p>
                    <?php } ?>

					<?php if(count($quote_addon['quote'])>0&&!$class_quote->vars->signed) { ?>
                    <div class="panel panel-warning" style="max-width:400px;">
                    	<div class="panel-heading">
							<i class="fas fa-plus-circle"></i> <b>Optional</b> Extras
                    	</div>
                    	<div class="panel-body">
							<ul class="bullet-list">
								<?php echo implode(' ',$quote_addon['quote']); ?>
							</ul>
						</div>
					</div>
					<?php } ?>
                </div>
            	<div class="col-md-3">
                	<div class="row price-box">
                    	<div class="col-xs-4">
                            <p class="lbl-sub">Subtotal</p>
                            <?php echo ($sale_meta['ship_price']!=NULL?"<p class=\"lbl-shp\">Shipping</p>":NULL); ?>
                            <?php echo ($order_discount>0?"<p class=\"lbl-dsc\">Discount</p>":NULL); ?>
                            <?php if($sale_data['tax_disable']<=0) { ?><p class="lbl-gst"><?php echo $setting_data['tax_label']; ?> (<?php echo $setting_data['tax_method_label']; ?>)</p><?php } ?>
                            <p class="lbl-ttl">Total</p>
                        </div>
                    	<div class="col-xs-8 text-right">
                            <p class="lbl-sub b"><?php echo LOCALE_currency_symbol; ?><span class="sale-summary-subtotal" id="sale-summary-subtotal"><?php echo number_format($cost['subtotal'],2); ?></span></p>
                            <?php echo ($sale_meta['ship_price']!=NULL?"<p class=\"lbl-shp b\">".LOCALE_currency_symbol."<span class=\"sale-summary-shp\" id=\"sale-summary-shp\">".number_format($sale_meta['ship_price'],2)."</span></p>":NULL); ?>
                            <?php echo ($order_discount>0?"<p class=\"lbl-dsc b\">".LOCALE_currency_symbol."<span class=\"sale-summary-dsc\" id=\"sale-summary-dsc\">".number_format($order_discount,2)."</span></p>":NULL); ?>
                            <?php if($sale_data['tax_disable']<=0) { ?><p class="lbl-gst b"><?php echo LOCALE_currency_symbol; ?><span class="sale-summary-gst" id="sale-summary-gst"><?php echo number_format($cost['tax'],2); ?></span></p><?php } ?>
                            <p class="lbl-ttl b"><?= $currency_code; ?> <?php echo LOCALE_currency_symbol; ?><span class="sale-summary-total" id="sale-summary-total"><?php echo number_format($cost['total'],2); ?></span></p>
                        </div>
                	</div>
                </div>
            </div>
            	<?php } ?>

				<?php if(count($quote_addon['upsell'])>0&&!$class_quote->vars->signed) { ?>
				<div class="panel panel-warning">
					<div class="panel-heading"><i class="fas fa-plus-circle"></i> Optional Extras</div>
					<div class="panel-body">
						<div class="row">
							<?php foreach($quote_addon['upsell'] as $upsell) {
								echo "<div class=\"col-md-3\">".$upsell."</div>";
							} ?>
						</div>
					</div>
					</div>
                <?php } ?>

            	<?php if(!$zulu->template->is_quote) { ?>
            	<?php echo (trim($setting_data['sale_footer'])?"<hr>".$setting_data['sale_footer']:NULL); ?>
            	<?php } ?>
            	<?php if($zulu->template->is_quote) { ?>
               	<?php if(trim($zulu->template->body_form_data)!=NULL) { ?>
                <hr>
                <h2>Attached Form</h2>
               	<?php echo $zulu->template->body_form_data; ?>
               	<?php } ?>

                <?php if(count($quote_section)>0) { ?>
                <hr>
                <h2>Summary of <?php echo $zulu->template->page_def['type']; ?></h2>
                <?php } ?>

                <?php foreach($quote_section as $item) {
					$panel_id = $zulu->serial(4);
					echo "
					<div class=\"panel panel-default\" ".($item['config']['id']!=NULL?"id=\"".$item['config']['id']."\"":NULL).">
						<div class=\"panel-heading\">
							<a data-toggle=\"collapse\" href=\"#panel-".$panel_id."\" aria-expanded=\"".($item['config']['minimised']?'false':'true')."\" class=\"".($item['config']['minimised']?'collapsed':NULL)."\">".$item['title'].($item['config']['minimised']?" <button type=\"button\" class=\"btn-xs btn-default btn\"><i class=\"fas fa-chevron-down\"></i> Click here to view more</button>":NULL)."</a>
						</div>
						<div id=\"panel-".$panel_id."\" class=\"panel-collapse collapse ".($item['config']['minimised']?NULL:'in')."\" aria-expanded=\"".($item['config']['minimised']?'false':'true')."\" style=\"\">
							<div class=\"panel-body\">".$item['content']."</div>
						</div>
					</div>
					";
				} ?>

                <hr>
                <h2>Declaration</h2>

                <?php if($class_quote->vars->sign) { ?>

                	<?php if($class_quote->config->sign_method==1) { //-- Sign by BUTTON ?>
                		<p>By clicking 'ACCEPT' below, I agree with the terms of use and the information quoted above. <?php echo $terms_button; ?></p>

						<?php if($class_quote->vars->signed) { ?>
						<button type="button" class="btn btn-success btn-disabled btn-lg" disabled><i class="fas fa-check"></i> Accepted</button>
						<p class="caption grey"><?php echo "Signed ".$zulu->date($quote_meta['signature_time'],"d/m/Y g:ia")." from IP address ".$quote_meta['signature_ip']; ?></p>
						<?php } else { ?>
						<form method="post" action="" id="form-signature">
							<button type="submit" name="bt_sign" class="btn btn-success btn-lg"><i class="fas fa-check"></i> Accept Quote</button>
							<?php echo $form_edit->input_html("hidden","method",'accept_quote'); ?>
						</form><!--signature form-->
						<p class="caption grey">Click 'Accept' to instantly sign this quote off online.</p>
						<?php } ?>

                	<?php } else { //-- Sign by SIGNATURE ?>
						<p>By signing the below, I agree with the terms of use and the information quoted above. <?php echo $terms_button; ?></p>

						<div class="signature-box" <?php if(!$class_quote->vars->signed) { ?>id="signature"<?php } ?>>
						<?php echo ($class_quote->vars->signed_file!=""?"<img src=\"".$class_quote->vars->signed_file."\" />":NULL); ?>
						</div>

						<form method="post" action="" id="form-signature">
							<?php echo $form_edit->input_html("hidden","method",'accept_quote'); ?>
							<?php echo $form_edit->input_html("hidden","signature",'',array("id"=>"signature_textform")); ?>
						</form><!--signature form-->

						<p class="caption grey">Signature (Above) <?php if(!$class_quote->vars->signed) { ?><a href="#" class="bt-clear">Clear Signature</a><?php }  else { echo " - Signed ".$zulu->date($quote_meta['signature_time'],"d/m/Y g:ia")." from IP address ".$quote_meta['signature_ip']; }?></p>
              		<?php } ?>
                <?php } else { ?>
				<p>This <?php echo strtolower(PAGE_name); ?> is still a <b>draft</b>, once confirmed you may sign below. <?php echo $terms_button; ?></p>
                 <?php } ?>
                <?php } ?>
                </div>

                <div class="document-foot">
                	<div class="row">
                    	<div class="col-xs-4 col-left">
                        	<p><?php echo $setting_data['company']; ?></p>
                        </div>
                    	<div class="col-xs-4 col-center">
                        	<p><span class="fas fa-envelope"></span> <?php echo $setting_data['contact_email']; ?></p>
                        </div>
                    	<div class="col-xs-4 col-right">
                        	<p><span class="fas fa-phone"></span> <?php echo $setting_data['contact_phone']; ?></p>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <div class="taskbar-spacer"></div>
    <div class="taskbar">
        <div class="container">
            <div class="row">
                <div class="col-xs-3">
                	<h3 class="no-margin" style="padding-top:5px;"><?php echo ($class_quote->vars->sign||!$zulu->template->page_def['draft']?NULL:"Draft "); echo $zulu->template->page_def['type']; ?> #<?php echo $zulu->template->page_def['reference']; ?></h3>
                </div>
                <div class="col-xs-5">
                	<div class="share-wrap"><label><span class="fas fa-link"></span> Share</label><input type="text" contenteditable="false" value="<?php echo $zulu->template->page_def['share']; ?>" /></div>
                </div>
                <div class="col-xs-4 text-right">
                	<?php if($zulu->template->page_def['owner']) { ?>
                    <a href="<?php echo $zulu->link_page(PAGE_file); ?>"><button type="button" class="btn btn-default"><span class="fas fa-list"></span> All <?php echo strtolower($zulu->template->page_def['type']); ?>s</button></a>
                    <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('id'=>$zulu->template->page_def['id'],'Action'=>'edit'))); ?>"><button type="button" class="btn btn-primary"><span class="fas fa-edit"></span> Edit <?php echo strtolower($zulu->template->page_def['type']); ?></button></a>
                    <?php if(!$class_quote->vars->sign&&$zulu->template->page_def['quote']&&$class_user->has_perm('quote_approve')) { ?>
                    <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('id'=>$quote_data['id'],'Action'=>'approve'))); ?>"><button type="button" class="btn btn-success"><span class="fas fa-check"></span> Approve</button></a>
                    <?php } ?>
                    <?php } else { ?>
                    <?php if(!$class_quote->vars->signed&&$class_quote->vars->sign) { ?>
                    <a href="#"><button type="button" class="btn btn-success bt-sign"><span class="far fa-pencil"></span> Sign this quote</button></a>
                    <a href="#"><button type="button" class="btn btn-success bt-confirm"><span class="fas fa-check"></span> Accept &amp; Confirm</button></a>
                    <?php } ?>
                    <a href="#"><button type="button" class="btn btn-primary bt-comment"><span class="far fa-envelope"></span> Send Comment</button></a>
                    <?php } ?>
                    <a href="#" onclick="window.print()"><button type="button" class="btn btn-default"><span class="far fa-print"></span></button></a>
                    <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('id'=>$quote_data['id'],'Action'=>'print','Do'=>'PDF'))); ?>" target="_blank" title="Open PDF version"><button type="button" class="btn btn-default"><span class="far fa-file-pdf"></span></button></a>
                </div>
            </div>
    	</div>
    </div>

    <!-- jQuery -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="<?php echo MAIN_rel; ?>bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

	<?php if(count($zulu->template->js_file)>0) {
	foreach($zulu->template->js_file as $file) { ?>
	<script type="text/javascript" src="<?php echo $file; ?>"></script>
    <?php } } ?>
	<?php if(count($zulu->template->jquery)>0) { ?>
	<script type="text/javascript">
		$(document).ready(function(e) {
		<?php echo implode("\n\n",$zulu->template->jquery); ?>
        });
	</script>
    <?php } ?>
	<?php if(count($zulu->template->js_file)>0) { ?>
    <script type="text/javascript">
	<?php foreach($zulu->template->js_code as $code) { ?>
	<?php echo $code; ?>
    <?php } ?>
	</script>
	<?php } ?>
