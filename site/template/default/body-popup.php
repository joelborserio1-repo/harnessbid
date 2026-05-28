    <div class="container">
        <div class="row">
        
        	<div class="document">
            
                <div class="document-head">
                
                	<div class="coltable col3 vmiddle">
                    	<div class="col col-left">
                        	<h2><?php echo $zulu->nav->title; ?></h2>
                        </div>
                    	<div class="col col-right">
                            <p class="no-margin"><?php echo $zulu->template->page_def['popup_title']; ?></p>
                            <?php if($zulu->template->new_window) { ?>
                            	<p><a href="#" onclick="window.open(document.URL, '_blank', 'location=no,height=600,width=600,scrollbars=yes,status=yes');"><span class="fas fa-sign-out"></span> Open in new window</a></p>
                            <?php } ?>
                        </div>
                    </div>
                
                </div>
                
                <div class="document-body">
                
                <?php echo $zulu->notification(); ?>
                
                <?php echo $zulu->template->body; ?>
           		<?php include(TPL_root."page/".SECTION_path.PAGE_frame.".php"); ?>
                
                </div>
                
                <div class="document-foot">
                	<div class="row">
                    	<div class="col-md-12 col-center">
                       		<?php if($zulu->template->page_def['footer']) {
								echo $zulu->template->page_def['footer_html'];
							} else { ?>
                        	<h3><span class="fas fa-life-ring"></span> Still stuck?</h3>
                        	<p><span class="fas fa-envelope"></span> <?php echo SUPPORT_email; ?> <span class="fas fa-phone"></span> <?php echo SUPPORT_phone; ?></p>
                        	<?php } ?>
                        </div>
                    </div>
                </div>
                
            </div>	
        
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="<?php echo MAIN_rel; ?>bower_components/jquery/dist/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="<?php echo MAIN_rel; ?>bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="<?php echo MAIN_rel; ?>bower_components/metisMenu/dist/metisMenu.min.js"></script>
    
    <!-- Custom Theme JavaScript -->
    <script src="<?php echo MAIN_rel; ?>dist/js/sb-admin-2.js"></script>
        
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