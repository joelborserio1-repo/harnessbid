    <div class="container">
        <div class="row">
        
        	<div class="document">
            
            	<?php if(!$zulu->template->page_def['comment_hide']) { ?>
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
                <?php } ?>
            
            	<?php if($zulu->template->page_def['paid']) { ?>
                <div class="document-head style-green text-center">
                	<h3 class="no-margin"><span class="fas fa-check"></span> Sale has been fully paid ($<?php echo $zulu->dollar($zulu->template->page_def['paid_amount']); ?>)</h3>
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
                        	<?php if($class_setting->data['quote_logo_path']!=NULL) { ?>
                        	<img class="responsive" src="<?php echo $class_setting->data['quote_logo_path']; ?>" alt="<?php echo $class_setting->data['company']; ?>" />
                            <?php } else {  ?>
                        	<h2><?php echo $class_setting->data['company']; ?></h2>
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
                                			<p><b><?php echo $client_out['name']; ?></b><?php echo ($client_out['address']!=NULL?"<br>".$client_out['address']:NULL); ?><?php echo ($client_out['contact_phone']!=NULL?"<br>".$client_out['contact_phone']:NULL); ?><?php echo ($client_out['contact_email']!=NULL?"<br>".$client_out['contact_email']:NULL); ?></p>
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
                        
                    </div>
                </div>
                <hr>
                <h2>Your Tickets</h2>
                <?php echo $zulu->template->body->table_item; ?>
                </div>
           </div>
        </div>
    </div>
    
    <div class="taskbar-spacer"></div>
    <div class="taskbar">
        <div class="container">
            <div class="row">
                <div class="col-xs-3">
                	<h3 class="no-margin" style="padding-top:5px;"><?php echo ($class_quote->vars->sign||!$zulu->template->page_def['draft']?NULL:"Draft "); echo $zulu->template->page_def['type']; ?> <?php if($zulu->template->page_def['reference']!=NULL) { ?>#<?php echo $zulu->template->page_def['reference']; } ?></h3>
                </div>
                <div class="col-xs-5">
                	<div class="share-wrap"><label><span class="fas fa-link"></span> Share</label><input type="text" contenteditable="false" value="<?php echo $zulu->template->page_def['share']; ?>" /></div>
                </div>
                <div class="col-xs-4 text-right">
                	<?php if($zulu->template->page_def['owner']) { ?>
                    <a href="<?php echo $zulu->link_page(PAGE_file); ?>"><button type="button" class="btn btn-default"><span class="fas fa-list"></span> All <?php echo strtolower($zulu->template->page_def['type']); ?>s</button></a> 
                    <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('id'=>$zulu->template->page_def['id'],'Action'=>'edit'))); ?>"><button type="button" class="btn btn-primary"><span class="fas fa-edit"></span> Edit <?php echo strtolower($zulu->template->page_def['type']); ?></button></a>
                    <?php if(!$class_quote->vars->sign&&$zulu->template->page_def['quote']) { ?>
                    <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('id'=>$quote_data['id'],'Action'=>'approve'))); ?>"><button type="button" class="btn btn-success"><span class="fas fa-check"></span> Approve</button></a>
                    <?php } ?>
                    <?php } else { ?>
                    <?php if(!$class_quote->vars->signed&&$class_quote->vars->sign) { ?>
                    <a href="#"><button type="button" class="btn btn-success bt-sign"><span class="fas fa-pencil"></span> Sign this quote</button></a> 
                    <a href="#"><button type="button" class="btn btn-success bt-confirm"><span class="fas fa-check"></span> Accept &amp; Confirm</button></a> 
                    <?php } ?>
                    <?php if(!$zulu->template->page_def['comment_hide']) { ?>
                    <a href="#"><button type="button" class="btn btn-primary bt-comment"><span class="far fa-envelope"></span> Send Comment</button></a>
                    <?php } ?>
                    <?php } ?>
                    <a href="#" onclick="window.print()"><button type="button" class="btn btn-default"><span class="fas fa-print"></span></button></a>
                </div>
            </div>
    	</div>
    </div>
    
    <!-- jQuery -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>

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