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
            
            	<?php if($zulu->template->page_def['complete']) { ?>
                <div class="document-head style-green text-center">
                	<h3 class="no-margin"><span class="fas fa-check"></span> Project completed <?php echo $zulu->template->page_def['date_done']; ?>.</h3>
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
                        	<h2 class="no-margin">Project Summary <?php echo ($project_data['reference']!=NULL?$class_project->config->reference_prefix.'-'.$project_data['reference']:NULL); ?></h2>
                        </div>
                    </div>
                
                </div>
                
                <div class="document-body">
                <?php echo $zulu->notification(); ?>
                <div class="row">
                    <div class="col-lg-12">
                        <h1><?php echo stripslashes($project_data['title']); ?> <small><?php echo $client_data['company']; ?></small></h1>
                    </div>
                </div>
                
                    <div class="row">
                        <div class="col-md-12">
                            <hr>
                            <h2>Tasks &amp; Billable Items</h2>
                        </div>
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                 <div class="panel-heading"><span class="fas fa-list"></span> Tasks for this project</div>
                                <div class="panel-body">
                                        <?php if($count_task>0){ ?>
                                    <?php echo $zulu->template->body->panel_task; ?>
                                    <?php } else { ?>
                                    No tasks exist for this project.
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                 <div class="panel-heading"><span class="far fa-money-bill"></span> Billables for this project</div>
                                <div class="panel-body">
                                <?php if($count_bill>0){ ?>
                                    <?php echo $zulu->template->body->panel_bill; ?>
                                <?php } else { ?>
                               No billable items exist for this project.
                                <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div><!--end bill table / task-->
            	
					<?php if($project_data['notes'] != NULL) { ?>
                    <div class="row">
                        <div class="col-md-12">
                        <hr>
                        <h2>Project Notes</h2>
                        <?php echo stripslashes($project_data['notes']); ?> 
                        </div>
                    </div><!--end notes row-->
                    <?php } ?>
                        
                    
                    <div class="row">
                        <div class="col-md-12">
                 	   		<hr>
                 	   		<h2>Project Summary</h2>
                    	</div>
                        <div class="col-sm-6">
                            <?php echo $zulu->template->report; ?>
                        </div>
                        <div class="col-sm-6">
                             <div class="flot-chart">
                                <div class="flot-chart-content" id="chart-task"></div>
                            </div>
                        </div>
                    </div>
                       
                </div>
                
                <div class="document-foot">
                	<div class="row">
                    	<div class="col-xs-4 col-left">
                        	<p><?php echo $class_setting->data['company']; ?></p>
                        </div>
                    	<div class="col-xs-4 col-center">
                        	<p><span class="fas fa-envelope"></span> <?php echo $class_setting->data['contact_email']; ?></p>
                        </div>
                    	<div class="col-xs-4 col-right">
                        	<p><span class="fas fa-phone"></span> <?php echo $class_setting->data['contact_phone']; ?></p>
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
                	<h3 class="no-margin" style="padding-top:5px;">Project #<?php echo $project_data['id']; ?></h3>
                </div>
                <div class="col-xs-5">
                	<div class="share-wrap"><label><span class="fas fa-link"></span> Share</label><input type="text" contenteditable="false" value="<?php echo $class_project->project_url($project_data['token']); ?>" /></div>
                </div>
                <div class="col-xs-4 text-right">
                	<?php if($zulu->template->page_def['owner']) { ?>
                    <a href="<?php echo $zulu->link_page(PAGE_file); ?>"><button type="button" class="btn btn-default"><span class="fas fa-list"></span> All projects</button></a> 
                    <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('id'=>$project_data['id'],'Action'=>'edit'))); ?>"><button type="button" class="btn btn-primary"><span class="fas fa-edit"></span> Edit project</button></a>
                    <?php } else { ?>
                    <a href="#"><button type="button" class="btn btn-primary bt-comment"><span class="far fa-envelope"></span> Send Comment</button></a>
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