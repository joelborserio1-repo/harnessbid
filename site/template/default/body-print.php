    <div class="container">
        <div class="row">
        
        	<div class="document">
            
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
                        	<h2 class="no-margin"><?php echo $zulu->template->page_def['title']; ?></h2>
                        	<?php echo $zulu->template->page_def['title_sub_html']; ?>
                        </div>
                    </div>
                
                </div>
                
                <div class="document-body">
					<?php echo $zulu->notification(); ?>
					<?php echo $zulu->template->body; ?>
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
    
    <?php if($zulu->template->page_def['taskbar']) { ?>
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
                    <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('id'=>$project_data['id'],'Action'=>'edit'))); ?>"><button type="button" class="btn btn-primary"><span class="far fa-pencil"></span> Edit project</button></a>
                    <?php } else { ?>
                    <a href="#"><button type="button" class="btn btn-primary bt-comment"><span class="far fa-envelope"></span> Send Comment</button></a>
                    <?php } ?>
                    <a href="#" onclick="window.print()"><button type="button" class="btn btn-default"><span class="fas fa-print"></span></button></a>
                </div>
            </div>
    	</div>
    </div>
    <?php } ?>
    
    <div class="zulu-badge">
    	<a href="https://www.zulusys.nz/contact-us/" target="_blank" title="Click here to find out more about Zulu Forms.">
    	<p class="cap">Simple Health &amp; Safety Forms</p>
    	<p class="heading">Free setup by local team - <a href="https://www.zulusys.nz/contact-us/" target="_blank" class="link"><i class="fas fa-envelope"></i> Enquire Now</a></span>
		</a>
    </div>
    <!--end zulu badge->
    
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