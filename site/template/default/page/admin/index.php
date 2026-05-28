<!-- /.row -->

<?php if(MASTER_mode=='web') { ?>
<div class="row">
	<div class="col-md-9">
    	<div class="image block">
    		<img src="<?php echo $class_website->endpoint; ?>zulu_banner.php?v=<?php echo $class_website->config->version; ?>&p=<?php echo $class_website->config->program; ?>" alt="Feature Banner" class="responsive" />
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-9">
    <hr />
    	<p><span class="color-grey"><i class="fas fa-rocket"></i> Quick Link Shortcuts</span></p>
        <p><a href="<?php echo FE_rel; ?>" class="btn btn-success" target="_blank"><i class="fas fa-laptop"></i> View Website</a> <?php if($class_setting->data['ws_status']!=1) { ?><a href="<?php echo $zulu->link_page('website',['query'=>['Action'=>'site','Tab'=>'maintenance']]); ?>" class="btn btn-danger" target="_blank"><i class="fas fa-power-off"></i> Activate Site</a> <?php } ?> <?php if($class_setting->data['ws_license_serial']==NULL||$class_setting->data['ws_license_rupid']==NULL) { ?><a href="<?php echo $zulu->link_page('website',['query'=>['Action'=>'site','Tab'=>'license']]); ?>" class="btn btn-danger" target="_blank"><i class="fas fa-barcode"></i> License Setup</a> <?php } ?><?php if($class_setting->data['ws_module_google_ga_profile']!=NULL) { ?><a href="http://analytics.google.com" class="btn btn-info" target="_blank"><i class="fab fa-google"></i> Google Analytics</a> <?php } ?></p>
    <hr />
    
    <?php if(WEBSITE_cb_force_default||WEBSITE_dev) { ?>
    <div class="alert alert-danger">
    	<p><i class="fas fa-exclamation-triangle"></i> <?php if(WEBSITE_dev) { ?>Site is in development mode (see define.php).<?php } ?> <?php if(WEBSITE_cb_force_default) { ?>Content blocks are being forced to default.<?php } ?></p>
    </div>
    <?php } ?>
    
    </div>
</div>
<div class="row">
	<div class="col-md-3">
    	<div class="panel panel-default">
        	<div class="panel-heading">
            	<div class="coltable col2 vmiddle">
                	<div class="col">
                    	<i class="fas fa-edit"></i> Pages
                    </div>
                    <div class="col text-right">
						<a href="<?php echo $zulu->link_page('post',['query'=>['Action'=>'edit','type'=>'page']]); ?>" class="btn btn-xs btn-default"><i class="fas fa-plus-circle"></i> New Page</a> <a href="<?php echo $zulu->link_page('post',['query'=>['type'=>'page']]); ?>" class="btn btn-xs btn-default"><i class="fas fa-bars"></i> All Pages</a>
                    </div>
               </div>
            </div>
            <div class="panel-body">
            	<?php echo $zulu->template->table_page; ?>
            </div>
        </div>
    </div>
	<div class="col-md-3">
    	<div class="panel panel-default">
			<div class="panel-heading">
            	<div class="coltable col2 vmiddle">
                	<div class="col">
                    	<i class="fas fa-bars"></i> Menus
                    </div>
                    <div class="col text-right">
						<a href="<?php echo $zulu->link_page('post',['query'=>['Action'=>'edit','type'=>'menu']]); ?>" class="btn btn-xs btn-default"><i class="fas fa-plus-circle"></i> New Menu</a> <a href="<?php echo $zulu->link_page('post',['query'=>['type'=>'menu']]); ?>" class="btn btn-xs btn-default"><i class="fas fa-bars"></i> All Menus</a>
                    </div>
               </div>
            </div>
            <div class="panel-body">
            	<?php echo $zulu->template->table_menu; ?>
            </div>
        </div>
    </div>
	<div class="col-md-3">
    	<div class="panel panel-default">
			<div class="panel-heading">
            	<div class="coltable col2 vmiddle">
                	<div class="col">
                    	<i class="far fa-newspaper"></i> News
                    </div>
                    <div class="col text-right">
						<a href="<?php echo $zulu->link_page('post',['query'=>['Action'=>'edit','type'=>'news']]); ?>" class="btn btn-xs btn-default"><i class="fas fa-plus-circle"></i> New Article</a> <a href="<?php echo $zulu->link_page('post',['query'=>['type'=>'news']]); ?>" class="btn btn-xs btn-default"><i class="fas fa-bars"></i> All Articles</a>
                    </div>
               </div>
            </div>
            <div class="panel-body">
            	<?php echo $zulu->template->table_news; ?>
            </div>
        </div>
    </div>
</div>
<?php } ?>
<?php if(MASTER_mode=='main') { ?>
<div class="row">
	<div class="col-md-12">
    	<p>
            <a href="<?php echo $zulu->link_page("client",array('query'=>array("Action"=>"lead_edit"))); ?>"><button type="button" class="btn btn-success btn-sm"><span class="fas fa-star"></span> New Lead</button></a>
            <a href="<?php echo $zulu->link_page("client",array('query'=>array("Action"=>"edit"))); ?>"><button type="button" class="btn btn-success btn-sm"><span class="fas fa-user"></span> New Client</button></a>
        	 <a href="<?php echo $zulu->link_page("task",array('query'=>array("Action"=>"edit"))); ?>"><button type="button" class="btn btn-success btn-sm"><span class="fas fa-list"></span> New Task</button></a>
            <a href="<?php echo $zulu->link_page("project",array('query'=>array("Action"=>"edit"))); ?>"><button type="button" class="btn btn-success btn-sm"><span class="fas fa-suitcase"></span> New Projects</button></a>
            <a href="<?php echo $zulu->link_page("bill",array('query'=>array("Action"=>"edit"))); ?>"><button type="button" class="btn btn-success btn-sm"><span class="fas fa-credit-card"></span> New Billable</button></a>
       </p>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
       <hr>
		<h3 class="text-primary"><span class="fas fa-tasks"></span> Tasks</h3>
    </div>
 </div>

<div class="row">
    <div class="col-lg-4">
    	<div class="panel panel-default panel-warning">
        	<div class="panel-heading"><span class="fas fa-exclamation-triangle"></span> Outstanding Tasks</div>
            <div class="panel-body">
            	<?php echo $zulu->template->body->view_outstanding_tasks; ?>
            </div>	
        </div>
    </div>
    <div class="col-lg-4">
    	<div class="panel panel-default">
        	<div class="panel-heading"><span class="fas fa-chart-pie"></span> Task Division</div>
            <div class="panel-body">
                <div class="flot-chart">
                    <div class="flot-chart-content" id="chart-task"></div>
                </div>
            </div>	
        </div>
    </div>
    <div class="col-lg-4">
    	<div class="panel panel-default">
        	<div class="panel-heading"><span class="fas fa-chart-line"></span> Task Demand</div>
            <div class="panel-body">
                <div class="flot-chart">
                    <div class="flot-chart-content" id="chart-task-bar"></div>
                </div>
            </div>	
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
       <hr>
		<h3 class="text-primary"><span class="fas fa-star"></span> Leads</h3>
    </div>
 </div>
 
<div class="row">
    <div class="col-lg-4">
    	<div class="panel panel-default panel-warning">
        	<div class="panel-heading"><span class="fas fa-user-plus"></span> New Leads</div>
            <div class="panel-body">
            	<?php echo $zulu->template->body->view_new_client; ?>
            </div>	
        </div>
    </div>
    <div class="col-lg-8">
    	<div class="panel panel-default">
        	<div class="panel-heading"><span class="fas fa-chart-line"></span> Lead Generation &amp; Conversions</div>
            <div class="panel-body">
            	<p>Conversion Rate <?php echo number_format(($conversion_rate*100),0); ?>% <span class="color-grey">(Past 4 weeks)</span></p>
                <div class="flot-chart">
                    <div class="flot-chart-content" id="chart-lead-bar"></div>
                </div>
            </div>	
        </div>
    </div>
</div>
<?php } ?>