    <div id="wrapper">

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
        
        	<div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.html"><?php echo MAIN_name; ?></a>
            </div>
            
            <div class="navbar-default sidebar" <?php echo ($zulu->template->sidebar_hide||$_SESSION['zl_setting']['sidebar_hide']?"style=\"display:none\"":NULL); ?> role="navigation">
                <div class="sidebar-nav navbar-collapse">
                	
                    <div class="text-center">
        				<div class="logo" style="<?php echo (trim(MAIN_logo)!=NULL&&file_exists("../file/user/".$class_user->authorised->id."/".MAIN_logo)?"background-image:url(../file/user/".$class_user->authorised->id."/".MAIN_logo.")":NULL); ?>"><?php echo MAIN_name; ?></div>
                	</div>
                    
                    <div class="user-info">
                    	<p class="username"><span class="fas fa-user"></span> Hello, <span class="text-bold"><?php echo ($class_user->authorised->name_first); ?></span></p>
                    	<p class="time"><span class="fal fa-clock"></span> <?php echo date("l, M jS g:ia"); ?></p>
                	</div>
                   
                    <ul class="nav" id="side-menu"> <?php /*
                        <li class="sidebar-search">
                            <div class="input-group custom-search-form">
                                <input type="text" class="form-control" placeholder="Search...">
                                <span class="input-group-btn">
                                <button class="btn btn-default" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </span>
                            </div>
                            <!-- /input-group -->
                        </li> */ ?>
                        
                        <?php foreach($zulu->nav->compiled as $menu_key=>$menu_array) { ?>
                        <?php foreach($zulu->nav->compiled[$menu_key] as $menu_array) {
							if(count($menu_array['option'])>0) {
								$dropdown = array();
								$label_extra = "<span class=\"fas arrow\"></span>";
								foreach($menu_array['option'] as $menu_drop_array) {
									$dropdown[] = "
							<li>
								<a href=\"".$menu_drop_array['link']."\" ".($menu_drop_array['target']!=NULL?"target=\"".$menu_drop_array['target']:NULL)." title=\"".$menu_drop_array['title']."\"><i class=\"".$menu_drop_array['icon']." fa-fw\"></i> ".$menu_drop_array['label']."</a>
							</li>
							";
								}
								$dropdown = "<ul class=\"nav nav-second-level\">".implode("\n",$dropdown)."</ul>";
							}
							
							echo "
							<li>
								<a href=\"".$menu_array['link']."\" ".($menu_array['target']!=NULL?"target=\"".$menu_array['target']:NULL)." title=\"".$menu_array['title']."\"><i class=\"".$menu_array['icon']." fa-fw\"></i> ".$menu_array['label']."{$label_extra}</a>\n{$dropdown}
							</li>
							";
							unset($dropdown,$label_extra);
						
						} ?>
                        <?php } ?>
                    </ul>
                    
					<?php if(MASTER_mode=='main') { ?>
                    <div class="contact">
                    	<p class="title">Need help?</p>
                    	<p><span class="fas fa-phone"></span> <a href="tel:<?php echo SUPPORT_phone; ?>"><?php echo SUPPORT_phone; ?></a><br>
                    	<span class="fas fa-envelope"></span> <a href="mailto:<?php echo SUPPORT_email; ?>"><?php echo SUPPORT_email; ?></a></p>
                    </div>
                    <?php } ?>
                    
                    <div class="copyright">
                    	<p>&copy;<?php echo date("Y"); ?> <?php echo MAIN_copyright; ?></p>
                        <?php /*
                        <p><a href="http://razorweb.co.nz?click=RAZOR+CRM" target="_blank" title="Custom CRM system solution by RAZOR Web Design." class="rzr">Solution by RAZOR Web Design</a></p>*/ ?>
                    </div>
                    
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->
        </nav>

        <div id="page-wrapper" class="<?php echo ($zulu->template->sidebar_hide||$_SESSION['zl_setting']['sidebar_hide']?"nosidebar":NULL); ?>">
        	
            <?php if(!$zulu->template->statbar_hide) { ?>
            <div class="navbar-top-links-wrap">
                <ul class="nav navbar-top-links navbar-right">
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" title="Data manager" href="#">
                            <i class="fal fa-database"></i> Data <i class="fas fa-caret-down"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-user">
                            <li><a href="<?php echo $zulu->link_page('data'); ?>"><i class="fal fa-save"></i> Data Import / Export</a>
                            </li>
                        </ul>
                        <!-- /.dropdown-user -->
                    </li>
                    <li class="bt-sidebar-hide-wrap">
                        <a href="#" class="bt-sidebar-hide">
                            <?php if($_SESSION['zl_setting']['sidebar_hide']) { ?><i class="fal fa-eye"></i> Show Sidebar<?php } else { ?><i class="fal fa-eye-slash"></i> Hide Sidebar<?php } ?>
                        </a>
                    </li>
                    <!-- /.dropdown -->
                </ul>
                <div class="clearfix"></div>
        	</div>
            <?php } ?>
            
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">
                    	<?php $i=0;foreach($zulu->nav->breadcrumb as $item=>$config) {
							if(trim($item)!=NULL) {
							echo ($i>0?" <span class=\"small fas fa-chevron-right\"></span> ":NULL).($config['link']!=NULL?"<a href=\"{$config['link']}\">":NULL).$item.($config['link']!=NULL?"</a>":NULL);	
						$i++;}
						}?>
                    </h1>
                    
                    <?php echo $zulu->notification(); ?>
                    
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <?php include(TPL_root."page/".SECTION_path.PAGE_frame.".php"); ?>
            
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

    <!-- jQuery -->
    <script src="<?php echo MAIN_rel; ?>bower_components/jquery/dist/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="<?php echo MAIN_rel; ?>bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="<?php echo MAIN_rel; ?>bower_components/metisMenu/dist/metisMenu.min.js"></script>


    <!-- Morris Charts JavaScript -->
    <script src="<?php echo MAIN_rel; ?>bower_components/raphael/raphael-min.js"></script>
    <script src="<?php echo MAIN_rel; ?>bower_components/morrisjs/morris.min.js"></script>
    <?php /*<script src="<?php echo MAIN_rel; ?>js/morris-data.js"></script>*/ ?>

    <!-- Custom Theme JavaScript -->
    <script src="<?php echo MAIN_rel; ?>dist/js/sb-admin-2.js"></script>
    
    <?php if($zulu->template->jquery_redactor) { ?>
    <!--HTML Area-->
	<script src="<?php echo TPL_rel; ?>assets/redactor/redactor.min.js"></script>
	<script src="<?php echo TPL_rel; ?>assets/redactor/_plugins/alignment/alignment.min.js"></script>
    <script src="<?php echo TPL_rel; ?>assets/redactor/_plugins/counter/counter.min.js"></script>
    <script src="<?php echo TPL_rel; ?>assets/redactor/_plugins/filemanager/filemanager.min.js"></script>
    <script src="<?php echo TPL_rel; ?>assets/redactor/_plugins/fontcolor/fontcolor.min.js"></script>
    <script src="<?php echo TPL_rel; ?>assets/redactor/_plugins/fontsize/fontsize.min.js"></script>
    <script src="<?php echo TPL_rel; ?>assets/redactor/_plugins/fullscreen/fullscreen.min.js"></script>
    <script src="<?php echo TPL_rel; ?>assets/redactor/_plugins/imagemanager/imagemanager.min.js"></script>
    <script src="<?php echo TPL_rel; ?>assets/redactor/_plugins/properties/properties.min.js"></script>
	<script src="<?php echo TPL_rel; ?>assets/redactor/_plugins/table/table.min.js"></script>
    <script src="<?php echo TPL_rel; ?>assets/redactor/_plugins/video/video.min.js"></script>
    <?php } ?>
    
    <!-- DataTables JavaScript -->
    <script src="<?php echo MAIN_rel; ?>bower_components/datatables/media/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo MAIN_rel; ?>bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js"></script>

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

	<?php if(count($zulu->template->js_code)>0) { ?>
    <script type="text/javascript">
	<?php foreach($zulu->template->js_code as $code) { ?>
	<?php echo $code; ?>
    <?php } ?>
	</script>
	<?php } ?>

    <?php if(count($zulu->template->file_post_css)>0) { ?>
	<?php foreach($zulu->template->file_post_css as $file) { ?>
	<link href="<?php echo $file; ?>" type="text/css" rel="stylesheet" />
	<?php } ?>
	<!--end custom css-->
	<?php } ?>