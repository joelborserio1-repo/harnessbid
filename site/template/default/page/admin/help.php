<!-- /.row -->
<div class="row">
	<?php if(PAGE_action==NULL) { ?>
    
    <div class="col-md-12">
        <form method="get" action="" id="filter">
            <div class="row">
            	<div class="col-md-12">
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <span class="fas fa-filter"></span> Filter
                        </div>
                        <div class="panel-body">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Category: </label>
                                    <?php echo $form_edit->input_html("select","Category",$_GET['Category'],array('option'=>$output,'autofocus'=>true)); ?>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="form-group">
                                    <label>Search: </label>
                                    <?php echo $form_edit->input_html("input","Search",$_GET['Search']); ?>
                                </div>
                            </div>
                            <div class="col-lg-1">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <?php echo $form_edit->input_html("submit","",'Filter',array('class'=>array('btn-success display-block'))); ?>
                                    <?php echo $form_edit->input_html("hidden","Page",'help'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if($_GET['Search'] != NULL) { ?>
                    <div class="panel panel-warning">
                    	<div class="panel-heading">
                            <span class="fas fa-search"></span> Search
                        </div>
                        <div class="panel-body">
                        	<?php echo $zulu->template->search_results; ?>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="panel panel-info">
                    	<div class="panel-heading">
                            <span class="fas fa-list-ol"></span> Procedures
                        </div>
                        <div class="panel-body">
                        	<?php echo $zulu->template->procedure_list; ?>
                        </div>
                    </div>
            	</div>
            </div>
        </form>
    </div>
    
    <?php } ?>
</div>