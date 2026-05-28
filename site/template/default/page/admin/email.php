<!-- /.row -->
<div class="row">
	<?php if(PAGE_action==NULL) { ?>
    <form role="form" action="" method="post">
    	<div class="col-lg-6">
        	<h4>Bulk Recipients</h4>
            <div class="row">
    			<div class="col-lg-4">
                    <div class="form-group">
                    	<label><?php echo $form_edit->input_html("checkbox","bulk_recipient[lead]",1,array('custom'=>($_POST['bulk_recipient']['lead']==1?array('id'=>'bulk_lead','checked'=>'checked'):array('id'=>'bulk_lead')))); ?> Leads</label>
                		<?php echo $form_edit->input_html("select","select_recipient[lead][]",$_POST['select_recipient']['visitor'],array('custom'=>array('multiple'=>'multiple'),'option'=>$form_edit->clientOptionForm(false,'2'))); ?>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                    	<label><?php echo $form_edit->input_html("checkbox","bulk_recipient[client]",1,array('custom'=>($_POST['bulk_recipient']['client']==1?array('id'=>'bulk_client','checked'=>'checked'):array('id'=>'bulk_client')))); ?> Clients</label>
                		<?php echo $form_edit->input_html("select","select_recipient[client][]",$_POST['select_recipient']['client'],array('custom'=>array('multiple'=>'multiple'),'option'=>$form_edit->clientOptionForm(false,'1'))); ?>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                    	<label><?php echo $form_edit->input_html("checkbox","bulk_recipient[cancel]",1,array('custom'=>($_POST['bulk_recipient']['cancel']==1?array('id'=>'bulk_cancel','checked'=>'checked'):array('id'=>'bulk_cancel')))); ?> Cancelled</label>
                		<?php echo $form_edit->input_html("select","select_recipient[cancel][]",$_POST['select_recipient']['cancel'],array('custom'=>array('multiple'=>'multiple'),'option'=>$form_edit->clientOptionForm(false,'0'))); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            
        </div>
        <div class="col-lg-12">
        	<h4>Email Content</h4>
        	<div class="row">
            	<div class="col-lg-6">
                    <div class="form-group">
                        <label>Subject <em>*</em></label>
                        <?php echo $form_edit->input_html("input","subject",$_POST['subject']); ?>
                    </div>
                </div>
            </div>
            <div class="row">
            	<div class="col-lg-6">
                    <div class="form-group">
                        <label>Message <em>*</em></label>
                        <?php echo $form_edit->input_html("htmlarea","message",$_POST['message'],array('custom'=>array('rows'=>10,'id'=>'message'))); ?>
                    </div>
                    <div class="form-group">
					<?php echo $form_edit->input_html("submit","submit",'Send'); ?>
                    
                    <?php echo $form_edit->input_html("hidden","action",'edit'); ?>
                    <?php echo $form_edit->validate_reset(); ?>
                    </div>
                </div>
            </div>
        </div>
		<div class="row">
            <div class="col-lg-6">
            <h4>Selected Recipients</h4>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Visitors</label>
                            <p><?php echo $list_visitor; ?></p>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Members</label>
                            <p><?php echo $list_member; ?></p>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Resigned</label>
                            <p><?php echo $list_resign; ?></p>
                        </div>
                    </div>
                </div>
        	</div>
        </div>
    </form>
    <?php } ?>
</div>