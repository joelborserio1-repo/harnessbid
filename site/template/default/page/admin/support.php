<!-- /.row -->
	<?php if(PAGE_action==NULL) { ?>
    
    <div class="col-lg-12">
		<p>
        	<?php echo $new_ticket_html; ?>
        </p>
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
                <ul class="nav nav-tabs style-project">
                	<?php foreach($class_support->vars->status_array as $key=>$val) { ?>
                    <?php $config_for_tabs['Sort'] = $key ?>
					<li class="<?php echo ($tab==$key?"active":NULL); ?>"><a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>$config_for_tabs)); ?>" class="" aria-expanded="false"><?php echo $val." ".$count[$key]; ?></a></li>
                    <?php } ?>
                </ul>
            
    			<?php echo $zulu->template->body; ?>
            </div>
    	</div>
    </div>
    <?php } ?>
    
    <?php if(PAGE_action=='view_messages') { ?>
    <input type="hidden" id="current_ticket_id" value="<?php echo $_GET['id']; ?>" />
    <div class="row">
		<div class="col-md-12">
        	<div class="panel panel-primary">
                <div class="panel-heading">
                	Ticket Information                
                </div>
                <div class="panel-body">
                <?php echo $ticket_information_html ?>
            	</div>
            </div>
        </div>
    </div>        

    <div class="row">
        <div class="col-md-6">
        	<div class="panel panel-default">
                <div class="panel-heading">
                    Messages                
                </div>
                <div class="panel-body" style="height: 500px; overflow-y: scroll;" id="message-window">
                <?php echo $message_html; ?>
                </div>
        	</div>   
        </div>
        <div class="col-md-6">
        	<div class="panel panel-default">
                <div class="panel-heading">
                    Reply                
                </div>                    
                <form role="form" action="" method="post">

                    <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                    
                                        <?php echo $message_form_edit->input_html("textarea","reply_message","",['class'=>['form-control'],'rows'=>'5']); ?>
                                    </div>
                                </div>
    
                            </div>
                            <div class="row">
                            	<div class="col-lg-3">
                                	<p><i class="fas fa-envelope"></i> Notify client? <?php echo $message_form_edit->input_html('checkbox','send_email',1) ?></p> 
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <?php echo $message_form_edit->input_html("submit","submit","Send",['class'=>['btn btn-success form-control']]); ?>
                                    </div>
                                </div>
                            </div>
                    </div>

                </form>
            </div>
    	</div>
    </div>
    

    <?php } ?>
   	<?php if(PAGE_action=='edit_ticket'){ ?>
	<div class="row">
		<div class="col-lg-6 col-lg-offset-3">
		<div class="panel panel-primary">
			<div class="panel-heading">
				Create new ticket                
			</div>
			<div class="panel-body">
				<form role="form" action="" method="post">
				<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label>Subject:</label>
								<input name="ticket_subject" value="<?php echo $_POST['ticket_subject']?>" autofocus class="form-control" type="text" />                            
							</div>
						</div>
					<div class="col-md-6">
					  <div class="form-group">
							<label>Link to Client: </label>
                            <?php if(isset($_GET['client_id'])){ ?>
                                <input value="<?php echo $client_name; ?>" class="form-control" type="text" disabled/>
                                <input name="client_id" value="<?php echo $_GET['client_id']; ?>" hidden />                           
                            <?php }else{?>
								<?php echo $ticket_form_edit->input_html("input","name",$_POST['name'],array('autocomplete'=>false,'class'=>array('sf-input','typeahead','sf-input-user_id'),'custom'=>array('data-sf'=>'sf_customer','data-populate'=>'user_id','autocomplete'=>'off'))); ?>
                                <div class="guessbox guessbox-user_id">
                                    <ul></ul>
                                </div>
                                <?php echo $ticket_form_edit->input_html("hidden","client_id",$_POST['client_id'],array('id'=>'sf-user_id','class'=>array('sf-value'))); ?>
                            <?php } ?>
					  </div>	
					</div>

				  </div>
                  <div class="row">
                  		<div class="col-md-12">
							<div class="form-group">
								<label>Status:</label>
                                <label class="radio-inline"><input type="radio" value="Open" name="ticket_status" checked>Open</label>
								<label class="radio-inline"><input type="radio" value="Hold" name="ticket_status">On Hold</label>
								<label class="radio-inline"><input type="radio" value="Closed" name="ticket_status">Closed</label>                           
							</div>
						</div>
                  </div>
				  <div class="row">
						<div class="col-lg-12">
							 <div class="form-group">
  								<label>Message:</label>
  								<textarea class="form-control" rows="5" name="ticket_message_text" value="<?php echo $_POST['ticket_message_text']; ?>"></textarea>
							</div>
						</div>
				  </div>                            
                      <div class="row">
                          <div class="col-lg-3">
                          	<p><i class="fas fa-envelope"></i> Notify client? <?php echo $ticket_form_edit->input_html('checkbox','send_email',1) ?></p> 
                          </div>
                      </div>
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
        						<?php echo $ticket_form_edit->input_html("submit","submit",$ticket_form_edit->CONFIG_button_save,['class'=>['btn btn-success']]); ?>
        						<?php echo $ticket_form_edit->input_html("reset","reset",'Reset'); ?>
							</div>
						</div>
					</div>
				</form>	
			</div>
		</div>
		</div>
	</div>
 	<?php } ?>
 	
    <?php if(PAGE_action=='client_messages') { ?>
    <div class="row">
    	<div class="col-md-12">
       		<?php $zulu->notification(); ?>
        </div>
    </div>
    <div class="row">
		<div class="col-md-4">
        	<div class="panel panel-primary">
                <div class="panel-heading">
                	My Support Tickets                
                </div>
                <div class="panel-body" style="height: 500px; overflow-y: scroll;">
                    <?php echo $tickets_list_html; ?>
            	</div>
            </div>
        </div>
        
        <div class="col-md-4">
        	<div class="panel panel-default">
                <div class="panel-heading">
                    Messages                
                </div>
                <div class="panel-body" style="height: 500px; overflow-y: scroll;" id="message-window">
                <?php echo $message_html; ?>
                </div>
        	</div>     
        </div>
        <div class="col-md-4">
        	<div class="panel panel-default">
                <div class="panel-heading">
                    Reply                
                </div>                    
                <form role="form" action="" method="post">

                    <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                    
                                        <?php echo $message_form_edit->input_html("textarea","reply_message","",['class'=>['form-control'],'rows'=>'5']); ?>
                                    </div>
                                </div>
    
                            </div>
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <?php echo $message_form_edit->input_html("submit","submit","Send",['class'=>['btn btn-success form-control']]); ?>
                                    </div>
                                </div>
                            </div>
                    </div>
                </form>
            </div>
    	</div>
    </div>
    <?php } ?>
 	