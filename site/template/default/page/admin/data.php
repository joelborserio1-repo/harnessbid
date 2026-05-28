<!-- /.row -->
<div class="row">
	<?php if(PAGE_action==NULL) { ?>
    <form role="form" action="" method="post" enctype="multipart/form-data">
    	<div class="col-lg-6">
        	<div class="panel panel-default">
            	<div class="panel-heading">
                	<span class="far fa-file-excel"></span> Import Data
                </div>
                <div class="panel-body">
                	<p>You can load a <b>.csv</b> file below and import data into your system.</p>
                    <hr>
                    <div class="form-group">
                    	<label>Select a template...</label>
                    	<?php echo $form_edit->input_html("select","template",$_POST['template'],array('option'=>$class_data->template_array('import'))); ?>
                    </div>
                    <div class="form-group">
                    	<label>Upload your data file below...</label>
                    	<?php echo $form_edit->input_html("file","csv",$_POST['file']); ?>
                    </div>
                    <?php echo $form_edit->input_html("hidden","action","load_csv"); ?>
					<?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Continue",array('class'=>array('btn-success'))); ?>
                </div>
            </div>
        </div>
    </form>
    <form role="form" action="" method="post" enctype="multipart/form-data">
        <div class="col-lg-6">
        	<div class="panel panel-default">
            	<div class="panel-heading">
                	<span class="far fa-file-excel"></span> Export Data
                </div>
                <div class="panel-body">
                	<p>You can export data from your system into a <b>.csv</b> file.</p>
                    <hr>
                    <div class="form-group">
                    	<label>Select a template...</label>
                    	<?php echo $form_edit->input_html("select","template",$_POST['template'],array('option'=>$class_data->template_array('export'))); ?>
                    </div>
                    <?php echo $form_edit->input_html("hidden","action","create_csv"); ?>
					<?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-download\"></span> Export &amp; Download",array('class'=>array('btn-success'))); ?>
                </div>
            </div>
        </div>
    </form>
    <?php } ?>
	<?php if(PAGE_action=='import') { ?>
    <form method="post" action="" target="_parent">
    <div class="panel panel-default">
    	<div class="panel-heading">
        	<span class="fas fa-save"></span> Select the correct columns so our system can import your data correctly...
        </div>
        <div class="panel-body">
        	<div class="col-lg-2">
                <div class="form-group">
                    <label for="skip_first">Skip first row</label>
                    <?php echo $form_edit->input_html("checkbox","skip_first",1,array('custom'=>array('id'=>'skip_first'),'class'=>array('inline'))); ?>
                </div>
            </div>
        	<div class="col-lg-2">
                <div class="form-group">
                    <label for="update_rows">Update Rows</label>
                    <?php echo $form_edit->input_html("checkbox","update_rows",1,array('custom'=>array('id'=>'update_rows'),'class'=>array('inline'))); ?>
                </div>
            </div>
			<?php echo $zulu->template->body; ?>
		</div>
    </div>
	<?php echo $form_edit->input_html("hidden","action","execute"); ?>
	<?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Import Data",array('class'=>array('btn-success'))); ?>
    </form>
    <?php } ?>
</div>