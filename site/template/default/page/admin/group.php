<!-- /.row -->
<div class="row">
	<?php if(PAGE_action==NULL) { ?>
    
    <div class="col-lg-12">
    
        <p>
        	<a href="<?php echo $zulu->link_page('group',array('query'=>array('Action'=>'edit'))); ?>"><button class="btn btn-primary" type="button"><span class="fas fa-plus-circle"></span> New Group</button></a>
        </p>
        
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
        <?php echo $zulu->template->body; ?>
            </div>
    	</div>
    </div>
    <?php } ?>
    <?php if(PAGE_action=='users') { ?>
    
    <div class="col-lg-12">
    
        <p>
        	<a href="<?php echo $zulu->link_page('group'); ?>"><button class="btn btn-primary" type="button"><span class="fas fa-reply"></span> All Groups</button></a>
        </p>
        
        <div class="panel panel-default">
        	<div class="panel-body">
            	<form role="form" action="" method="post">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Add User To Group</label>
                            <?php echo $form_edit->input_html("select","user",$_POST['user'],array('option'=>$form_edit->userOptionForm())); ?>
                        </div>
                    </div>
                </div>	
                    
                    <?php echo $form_edit->input_html("submit","submit",'Add'); ?>
                    <?php echo $form_edit->input_html("hidden","action",'adduser'); ?>
                </form>
            </div>
        </div>
        
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
        <?php echo $zulu->template->body; ?>
            </div>
    	</div>
    </div>
    <?php } ?>
	<?php if(PAGE_action=='edit') { ?>
    <div class="col-lg-6">
        	<form role="form" action="" method="post">
            <div class="row">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Group Name</label>
                        <?php echo $form_edit->input_html("input","name",$_POST['name']); ?>
                    </div>
                </div>
            </div>	
                
                <?php echo $form_edit->input_html("submit","submit",'Save'); ?>
                <?php echo $form_edit->input_html("hidden","action",'edit'); ?>
            </form>
    </div>
	<?php } ?>
</div>