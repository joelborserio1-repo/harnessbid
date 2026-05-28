<!-- /.row -->
<?php if(PAGE_action=='get_started') { ?>
<div class="row">
	<div class="col-md-6 col-md-offset-3 get-started-container">
		<div class="center gs-heading">
			<img class="responsive master-trio" src="https://zulusys.nz/images/web-plan/lineup-trio.png" alt="trio of websites" />
			<h1>Website QuickStart</h1>
		</div>
		<form method="post" action="" id="gs_form">
		<div class="panel panel-green">
			<div class="panel-heading">
				<div class="row">
					<div class="col-md-5">
						<?php /*<p class="h3 no-margin st-1-title title">Website Quick Start</p>*/ ?>
						<p class="h3 no-margin st-1-title title">Your business</p>
						<p class="h3 no-margin st-2-title title">Get the look right</p>
						<p class="h3 no-margin st-3-title title">What pages?</p>
						<p class="h3 no-margin st-4-title title">Nice work!</p>
					</div>
					<div class="col-md-7 text-right">
						<ul class="step-list">
							<li class="st-1 <?php echo ($_POST['step']==1||!isset($_POST['step'])?'hvr':NULL); ?>"><span>1</span> Setup</li>
							<li class="st-2 <?php echo ($_POST['step']==2?'hvr':NULL); ?>"><span>2</span> Look</li>
							<li class="st-3 <?php echo ($_POST['step']==3?'hvr':NULL); ?>"><span>3</span> Pages</li>
							<li class="st-4 <?php echo ($_POST['step']==4?'hvr':NULL); ?>"><span>4</span> Done</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="panel-body">
				<div class="st-1-body body-block">
					<div class="form-group">
						<label>Whats your company / website name?</label>
						<?php echo $form_edit->input_html('input','company_name',$_POST['company_name'],['placeholder'=>'ACME Company']); ?>
					</div>
					<div class="form-group">
						<label>Whats your email for contact?</label>
						<?php echo $form_edit->input_html('input','company_email',$_POST['company_email'],['placeholder'=>'joe@acme.com']); ?>
					</div>
					<div class="form-group">
						<label>Whats your phone for contact?</label>
						<?php echo $form_edit->input_html('input','company_phone',$_POST['company_phone'],['placeholder'=>'09 555 1234']); ?>
					</div>
				</div><!--End step 1 body-->

				<div class="st-2-body body-block">
					<div class="form-group">
						<label>Select a theme...</label>
						<?php echo $zulu->template->body->template_slides; ?>
					</div>
					<div class="form-group">
						<label>Upload your logo...</label>
						<?php if(!file_exists($logo_path)) { ?>
                        <?php echo $class_file->uploadifive_input("logo_main"); ?>
						<?php } else { ?>
						<p><span class="opt opt-success"><span class="fas fa-check"></span> Logo Uploaded</span> <a target="_blank" href="<?php echo MAIN_url.$logo_rel; ?>" class="btn btn-xs btn-warning">View</a> <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Method'=>'DeleteLogo','Action'=>PAGE_action,'Tab'=>$_GET['Tab'],'Step'=>2))); ?>" class="btn btn-xs btn-danger">Remove</a></p>
						<?php } ?>
					</div>
					<p class="color-grey disclaimer"><i class="fas fa-info-circle"></i> You can easily change your theme, its colour scheme and logo later.</p>
				</div><!--End step 2 body-->

				<div class="st-3-body body-block">
					<div class="form-group">
						<label>Add your new pages below...</label>

						<?php echo "
						<div id='row-container'>".$zulu->table_render($table_row,0,array('class'=>'','js_table'=>false,'data_table'=>false,'html_id'=>'module-options','tbody'=>['id'=>'sortable-rows']))."</div>
						<div class=\"row\">
							<p class='text-center'><button type='button' id='row-add' class='btn btn-primary btn-xs' title='Add another option'><span class='fas fa-plus-circle'></span> Add another button</button></p>
						</div>";
						?>

					</div>
					<p class="color-grey disclaimer"><i class="fas fa-info-circle"></i> Consider pages such as <i>Home, Services, About Us, Contact Us, Terms of Use &amp; Privacy Policy</i>.</p>
				</div><!--End step 3 body-->

				<div class="st-4-body body-block text-center">

					<p><b>Nice work!</b> Continue by selecting an option below:</p>

					<p>
						<a href="<?php echo $zulu->link_page('website',['query'=>['Action'=>'site']]); ?>" class="btn btn-primary btn-block"><i class="fas fa-edit"></i> Setup Business Details</a>
						<a href="<?php echo $zulu->link_page('post',['query'=>['type'=>'page']]); ?>" class="btn btn-primary btn-block"><i class="fas fa-file-alt"></i> Setup Pages</a>
						<a href="<?php echo FE_rel; ?>" target="_blank" class="btn btn-warning btn-block"><i class="fas fa-laptop"></i> Preview Website</a>
					</p>


				</div><!--End step 4 body-->

				<div class="form-group fg-submit no-margin">
					<button type="submit" class="btn btn-success btn-block btn-lg btn-continue"><i class="fas fa-chevron-right"></i> Continue</button>
					<p class="text-center"><a href="#" class="btn-back color-gray grey"><i class="fas fa-chevron-left"></i> Back Step</a></p>
					<input type="hidden" class="input-action" name="action" value="step" />
					<input type="hidden" class="input-step" name="step" value="<?php echo ($_POST['step']>0?$_POST['step']:($_GET['Step']>0?$_GET['Step']:1)); ?>" />
				</div>
			</div>
		</div>
	</div>
	</form>
</div>
<?php } ?>
<?php if(PAGE_action=='site') { ?>
    <div class="row">
        <div class="col-md-12">
            <ul class="nav nav-tabs" style="margin-bottom:10px;">
            <?php foreach($tab_list as $key=>$name) { ?>
            <li class="<?php echo ($selected_tab==$key?"active":NULL); ?>"><a class="layout-load" data-template="<?php echo $key; ?>" href="<?php echo $zulu->link_page(PAGE_file,['query'=>['Action'=>PAGE_action,"Tab"=>$key]]); ?>"><?php echo $name; ?></a></li>
            <?php } ?>
            </ul>
        </div>
    </div>
	<?php if($selected_tab=='general') { ?>
    <form method="post" action="" enctype="multipart/form-data">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">Site Details</div>
				<div class="panel-body">
					<div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Name <em>*</em></label>
                                <?php echo $form_edit->input_html("input","setting[ws_site_name]",$_POST['ws_site_name'],['required'=>true]); ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Slogan / Tagline</label>
                                <?php echo $form_edit->input_html("input","setting[ws_site_slogan]",$_POST['ws_site_slogan']); ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Homepage <?php echo $form_edit->icon_help("Once you've added pages to your site, you can set which one is the main homepage."); ?></label>
                                <?php echo $form_edit->input_html("select","setting[ws_site_homepage]",$_POST['ws_site_homepage'],['option'=>$option_page_array]); ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Domain Name Host <?php echo $form_edit->icon_help("For example: joebuild.com or www.joebuild.com"); ?></label>
                                <?php echo $form_edit->input_html("input","setting[ws_site_host]",$_POST['ws_site_host']); ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Force HTTPS <?php echo $form_edit->icon_help("Redirects any HTTP requests to HTTPS."); ?></label>
                                <div class="form-switch">
                                    <?php echo $form_edit->input_html("checkbox","setting[ws_site_force_https]",1,['id'=>'force-https','checked'=>$_POST['ws_site_force_https']]); ?>
                                    <label for="force-https" class="label-success"></label>
                                </div>
                            </div>
                        </div>
				    </div>
				</div>
			</div>

            <div class="panel panel-default">
                <div class="panel-heading">Site Logo</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Main Logo</label>
                                <?php if(!file_exists($logo_path)) { ?>
                                <?php echo $class_file->uploadifive_input("logo_main"); ?>
                                <?php } else { ?>
                                <p>
                                    <span class="opt opt-success"><span class="fas fa-check"></span> Logo Uploaded</span>
                                    <a target="_blank" href="<?php echo $logo_rel; ?>" class="btn btn-xs btn-warning">View</a>
                                    <a href="<?php echo $zulu->link_page(PAGE_file,array('self'=>true, 'query'=>array('Method'=>'DeleteLogo'))); ?>" class="btn btn-xs btn-danger">Remove</a>
                                </p>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Favicon Upload</label>
                                <?php if(!file_exists($favicon_path)) { ?>
                                <?php echo $class_file->uploadifive_input("favicon_main"); ?>
                                <?php } else { ?>
                                <p>
                                    <span class="opt opt-success"><span class="fas fa-check"></span> Favicon Uploaded</span>
                                    <a target="_blank" href="<?php echo $favicon_rel; ?>" class="btn btn-xs btn-warning">View</a>
                                    <a href="<?php echo $zulu->link_page(PAGE_file,array('self'=>true, 'query'=>array('Method'=>'DeleteFavicon'))); ?>" class="btn btn-xs btn-danger">Remove</a>
                                </p>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer color-grey">
                    <i class="fas fa-info-circle"></i> Please ensure you wait for the upload box to show <b>Completed</b> before leaving the page.
                </div>
            </div>

            <div class="panel panel-default">
				<div class="panel-heading">META Tag Defaults <?php echo $form_edit->icon_help("META tags help identify and rank your site in search engine results."); ?></div>
				<div class="panel-body">
					<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							<label>Title Suffix <?php echo $form_edit->icon_help("This follows after your business name. For example: Quality Shoe Shop in Hamilton (Use keywords where possible)"); ?></label>
							<?php echo $form_edit->input_html("input","setting[ws_meta_title]",$_POST['ws_meta_title']); ?>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Description <?php echo $form_edit->icon_help("This appears in search engine results, ideally this should be no longer than 155 characters."); ?></label>
							<?php echo $form_edit->input_html("input","setting[ws_meta_description]",$_POST['ws_meta_description']); ?>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Keywords <?php echo $form_edit->icon_help("Not as important nowadays, but here if you'd like to add keywords."); ?></label>
							<?php echo $form_edit->input_html("input","setting[ws_meta_keyword]",$_POST['ws_meta_keyword']); ?>
						</div>
					</div>
				</div>
				</div>
			</div>
		</div>
	</div>
    <div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">Contact Defaults</div>
				<div class="panel-body">
					<div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Contact</label>
                            <?php echo $form_edit->input_html("input","setting[ws_contact_name]",$_POST['ws_contact_name']); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Email <em>*</em> <?php echo $form_edit->icon_help("This is where website enquiry emails will come to."); ?></label>
                            <?php echo $form_edit->input_html("input","setting[ws_contact_email]",$_POST['ws_contact_email'],['required'=>true]); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Phone</label>
                            <?php echo $form_edit->input_html("input","setting[ws_contact_phone]",$_POST['ws_contact_phone']); ?>
                        </div>
                    </div>
					</div>
					<div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Address</label>
                            <?php echo $form_edit->input_html("input","setting[ws_addr_addr]",$_POST['ws_addr_addr']); ?>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Suburb</label>
                            <?php echo $form_edit->input_html("input","setting[ws_addr_suburb]",$_POST['ws_addr_suburb']); ?>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>City</label>
                            <?php echo $form_edit->input_html("input","setting[ws_addr_city]",$_POST['ws_addr_city']); ?>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Postcode</label>
                            <?php echo $form_edit->input_html("input","setting[ws_addr_post]",$_POST['ws_addr_post']); ?>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Country</label>
                            <?php echo $form_edit->input_html("input","setting[ws_addr_country]",$_POST['ws_addr_country']); ?>
                        </div>
                    </div>
					</div>
				</div>
			</div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Save",array('class'=>array('btn-success'))); ?>
                <?php echo $form_edit->input_html("hidden","action",'edit'); ?>
            </div>
        </div>
    </div>
    </form>

	<?php } elseif($selected_tab=='miscellaneous') { ?>
    <form method="post" action="" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Google&reg; API Keys</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <div class="form-group">
                                    <label>Analytics Profile</label>
                                    <?php echo $form_edit->input_html("input","setting[ws_module_google_ga_profile]",$_POST['ws_module_google_ga_profile']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <div class="form-group">
                                    <label>reCAPTCHA v3 Site Key</label>
                                    <?php echo $form_edit->input_html("input","setting[ws_module_google_captcha_api_key]",$_POST['ws_module_google_captcha_api_key']); ?>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="form-group">
                                    <label>reCAPTCHA v3 Secret Key</label>
                                    <?php echo $form_edit->input_html("input","setting[ws_module_google_captcha_api_secret]",$_POST['ws_module_google_captcha_api_secret']); ?>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="form-group">
                                    <label>reCAPTCHA v3 Score Threshold <?= $form_edit->icon_help("A reCAPTCHA score equal to or higher than this value will be considered human and accepted. Anything lower will be treated as a bot and rejected."); ?></label>
                                    <?php echo $form_edit->input_html("select","setting[ws_module_google_captcha_api_score]",$_POST['ws_module_google_captcha_api_score'], ['option'=>$class_website->recaptcha_score_options]); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Adobe&reg; API Keys</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <div class="form-group">
                                    <label>TypeKit ID</label>
                                    <?php echo $form_edit->input_html("input","setting[ws_module_adobe_typekit]",$_POST['ws_module_adobe_typekit']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Save",array('class'=>array('btn-success'))); ?>
                    <?php echo $form_edit->input_html("hidden","action",'edit'); ?>
                </div>
            </div>
        </div>
    </form>

    <?php } elseif($selected_tab=='shop') { ?>
    <form method="post" action="" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Shop Settings</div>
                    <div class="panel-body">
                        <div class="row">
							<div class="col-lg-2 col-md-4 col-sm-6">
								<div class="form-group">
									<label>Root Category</label>
									<?php $form_edit->categoryOptionForm(); ?>
									<?php echo $form_edit->input_html("select","setting[ws_shop_category]",$_POST['ws_shop_category'],array('class'=>['input-cat'],"option"=>$output)); ?>
								</div>
							</div>
							<div class="col-lg-2 col-md-4 col-sm-6">
								<div class="form-group">
									<label>Shop Mode</label>
									<?php echo $form_edit->input_html("select","setting[ws_shop_mode]",$_POST['ws_shop_mode'],array("option"=>['shop'=>'Shop','catalogue'=>'Catalogue Only'])); ?>
								</div>
							</div>
							<div class="col-lg-2 col-md-4 col-sm-6">
								<div class="form-group">
									<label>Browse Page Purchase <?php echo $form_edit->icon_help("Allows customers to buy a product on the catalog page."); ?></label>
									<?php echo $form_edit->input_html("select","setting[ws_shop_catalog_purchase]",$_POST['ws_shop_catalog_purchase'],["option"=>[0=>'No',1=>'Yes']]); ?>
								</div>
							</div>
                     		<div class="col-lg-2 col-md-4 col-sm-6">
								<div class="form-group">
									<label>Country Lock <?php echo $form_edit->icon_help("Only allow addresses for a certain country."); ?></label>
									<?php echo $form_edit->input_html("select","setting[ws_shop_country_lock]",$_POST['ws_shop_country_lock'],["option"=>[''=>'None']+$form_edit->country_option()]); ?>
								</div>
							</div>
                            <div class="col-lg-2 col-md-4 col-sm-6">
								<div class="form-group">
                                    <label>Enable Coupons</label>
									<?php echo $form_edit->input_html("select","setting[ws_shop_coupon_enable]",$_POST['ws_shop_coupon_enable'],['option'=>['0'=>'No','1'=>'Yes']]); ?>
								</div>
							</div>
                            <div class="col-lg-2 col-md-4 col-sm-6">
								<div class="form-group">
                                    <label>Disable Search Sidebox</label>
									<?php echo $form_edit->input_html("select","setting[ws_shop_search_sidebox_disable]",$_POST['ws_shop_search_sidebox_disable'],['option'=>['0'=>'No','1'=>'Yes']]); ?>
								</div>
							</div>
                            <div class="col-lg-2 col-md-4 col-sm-6">
								<div class="form-group">
                                    <label>Enable Brands</label>
									<?php echo $form_edit->input_html("select","setting[ws_shop_brands_enable]",$_POST['ws_shop_brands_enable'],['option'=>['0'=>'No','1'=>'Yes'],'id'=>'brand-enable']); ?>
								</div>
							</div>
                            <div class="col-lg-2 col-md-4 col-sm-6">
								<div class="form-group">
                                    <label>Brand Page View <?php echo $form_edit->icon_help("Displays the brands in a nice tiled view or a basic list view."); ?></label>
									<?php echo $form_edit->input_html("select","setting[ws_shop_brands_view]",$_POST['ws_shop_brands_view'],['option'=>['tile'=>'Tiled','list'=>'List'],'class'=>['brand-option']]); ?>
								</div>
							</div>
                            <div class="col-lg-2 col-md-4 col-sm-6">
								<div class="form-group">
                                    <label>Disable Product Counts <?php echo $form_edit->icon_help("On the sidebox filters when browsing products. When enabled it can slow down the site with large quantities of products."); ?></label>
									<?php echo $form_edit->input_html("select","setting[ws_shop_product_counts_disabled]",$_POST['ws_shop_product_counts_disabled'],['option'=>['0'=>'No','1'=>'Yes']]); ?>
								</div>
							</div>
                            <div class="col-lg-2 col-md-4 col-sm-6">
								<div class="form-group">
                                    <label>Default Browse View <?php echo $form_edit->icon_help("What to show when browsing categories."); ?></label>
									<?php echo $form_edit->input_html("select","setting[ws_shop_browse_default_view]",$_POST['ws_shop_browse_default_view'],['option'=>['product'=>'Products Only','category'=>'Subcategories Only','both'=>'Both']]); ?>
								</div>
							</div>
                            <div class="col-lg-2 col-md-4 col-sm-6">
								<div class="form-group">
                                    <label>Root Browse View <?php echo $form_edit->icon_help("What to show on root browse page."); ?></label>
									<?php echo $form_edit->input_html("select","setting[ws_shop_browse_root_view]",$_POST['ws_shop_browse_root_view'],['option'=>['product'=>'Products Only','category'=>'Subcategories Only','both'=>'Both']]); ?>
								</div>
							</div>
						</div>
                 	</div>
           		</div>
                <div class="panel panel-default">
                    <div class="panel-heading">Stock Control</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Show Stock Level</label>
                                    <?php echo $form_edit->input_html("select","setting[ws_shop_stock_show]",$_POST['ws_shop_stock_show'],array("option"=>[0=>"No",1=>"Yes"])); ?>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Show Stock Count</label>
                                    <?php echo $form_edit->input_html("select","setting[ws_shop_stock_count]",$_POST['ws_shop_stock_count'],array("option"=>[0=>"No",1=>"Yes"])); ?>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Allow Backorders</label>
                                    <?php echo $form_edit->input_html("select","setting[ws_shop_stock_backorder]",$_POST['ws_shop_stock_backorder'],array("option"=>[0=>"No",1=>"Yes"])); ?>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Show 'Low' Warning From</label>
                                    <?php echo $form_edit->input_html("input","setting[ws_shop_stock_low_threshold]",$_POST['ws_shop_stock_low_threshold'],['placeholder'=>'Leave blank for no warning...']); ?>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Hide Level From <?php echo $form_edit->icon_help("Requires 'show stock count' and 'show stock level' to be enabled."); ?></label>
                                    <?php echo $form_edit->input_html("input","setting[ws_shop_stock_threshold]",$_POST['ws_shop_stock_threshold'],['placeholder'=>'Leave blank for no threshold...']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Purchase Settings</div>
                    <div class="panel-body">
                        <div class="row">
							<div class="col-md-4">
								<div class="form-group">
									<label>Checkout Mode</label>
									<?php echo $form_edit->input_html("select","setting[ws_shop_chk_mode]",$_POST['ws_shop_chk_mode'],array('class'=>['input-cat'],"option"=>['both'=>"Member & Public Checkout",'mem'=>"Member Only",'nomem'=>"Public Only"])); ?>
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									<label>Disable Purchasing From <?php echo $form_edit->icon_help("Enter the full date and time if required, this will disable any purchase options."); ?></label>
									<?php echo $form_edit->input_html("input","setting[ws_shop_chk_dis_from]",$_POST['ws_shop_chk_dis_from'],array('placeholder'=>'DD/MM/YYYY HH:MM','class'=>[''])); ?>
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									<label>Disable Purchasing To <?php echo $form_edit->icon_help("Enter the full date and time if required, this will disable any purchase options."); ?></label>
									<?php echo $form_edit->input_html("input","setting[ws_shop_chk_dis_to]",$_POST['ws_shop_chk_dis_to'],array('placeholder'=>'DD/MM/YYYY HH:MM','class'=>[''])); ?>

								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label>Disable Message <?php echo $form_edit->icon_help("Shows in the header of the website."); ?></label>
									<?php echo $form_edit->input_html("input","setting[ws_shop_chk_dis_msg]",$_POST['ws_shop_chk_dis_msg']); ?>
								</div>
							</div>
						 </div>
                		<div class="row">
							<div class="col-md-2">
								<div class="form-group">
									<label>Attribute Display Mode</label>
									<?php echo $form_edit->input_html("select","setting[ws_shop_display_mode]",$_POST['ws_shop_display_mode'],array('class'=>['input-cat'],"option"=>$class_product->config->display_mode)); ?>
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									<label>Skip billing information?</label>
									<?php echo $form_edit->input_html("select","setting[ws_shop_chk_bill_hide]",$_POST['ws_shop_chk_bill_hide'],array('class'=>['input-cat'],"option"=>[0=>"No, require",1=>"Yes, hide"])); ?>
								</div>
							</div>

						 </div>
                 	</div>
             	</div>

                <div class="panel panel-default" id="product-reviews">
                    <div class="panel-heading">Product Reviews</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-lg-2 col-md-4 col-sm-6">
								<div class="form-group">
                                    <label>Enable product reviews</label>
									<?php echo $form_edit->input_html("select","setting[ws_shop_product_review_enable]",$_POST['ws_shop_product_review_enable'],['option'=>['0'=>'No','1'=>'Yes'],'id'=>'product-reviews-enable']); ?>
								</div>
							</div>
                            <div class="col-lg-2 col-md-4 col-sm-6">
								<div class="form-group">
                                    <label>Admin approval required <?php echo $form_edit->icon_help("Whether you need to manually approve a review before it goes live."); ?></label>
									<?php echo $form_edit->input_html("select","setting[ws_shop_product_review_approval]",$_POST['ws_shop_product_review_approval'],['option'=>['0'=>'No','1'=>'Yes']]); ?>
								</div>
							</div>
                            <div class="col-lg-2 col-md-4 col-sm-6">
								<div class="form-group">
                                    <label>Email notification <?php echo $form_edit->icon_help("Receive an email when a review is placed or updated."); ?></label>
									<?php echo $form_edit->input_html("select","setting[ws_shop_product_review_notify]",$_POST['ws_shop_product_review_notify'],['option'=>['0'=>'No','1'=>'Yes']]); ?>
								</div>
							</div>
                            <div class="col-lg-2 col-md-4 col-sm-6">
								<div class="form-group">
                                    <label>Maximum feature reviews <?php echo $form_edit->icon_help("How many reviews to show on the product page."); ?></label>
									<?php echo $form_edit->input_html("number","setting[ws_shop_product_review_feature_max]",($_POST['ws_shop_product_review_feature_max']!=null?$_POST['ws_shop_product_review_feature_max']:ProductReview::$featured_count),['placeholder'=>ProductReview::$featured_count,'custom'=>['min'=>'1']]); ?>
								</div>
							</div>
						 </div>
                 	</div>
             	</div>

                <div class="panel panel-default">
                    <div class="panel-heading">Extra Features</div>
                    <div class="panel-body">
                        <div class="row">
							<div class="col-md-3">
								<div class="form-group">
                                    <label>Show Related Products On &nbsp; <button type='button' class='btn btn-xs btn-success' title="Select All" id="related-select-bt"><i class="fas fa-check"></i></button> <button type='button' class='btn btn-xs btn-danger' title="Deselect All" id="related-deselect-bt"><i class="fas fa-times"></i></button></label>
									<?php echo $form_edit->input_html("select","setting[ws_shop_related_products][]",$_POST['ws_shop_related_products'],array('option'=>['product'=>'Products Page','basket'=>'Basket Page'],'custom'=>['multiple'=>'multiple'],'id'=>'related-products-select')); ?>
								</div>
							</div>
                            <div class="col-md-3">
								<div class="form-group">
                                    <label>Enable Wish List</label>
									<?php echo $form_edit->input_html("select","setting[ws_shop_wishlist_enable]",$_POST['ws_shop_wishlist_enable'],['option'=>['0'=>'No','1'=>'Yes']]); ?>
								</div>
							</div>
                            <div class="col-md-3">
								<div class="form-group">
                                    <label>Enable Support Tickets</label>
									<?php echo $form_edit->input_html("select","setting[ws_shop_support_enable]",$_POST['ws_shop_support_enable'],['option'=>['0'=>'No','1'=>'Yes']]); ?>
								</div>
							</div>
                            <div class="col-md-3">
								<div class="form-group">
                                    <label>Browse Page Images</label>
									<?php echo $form_edit->input_html("select","setting[ws_shop_browse_image_crop]",$_POST['ws_shop_browse_image_crop'],['option'=>['zc'=>'Crop','far'=>'Fit Image in View']]); ?>
								</div>
							</div>
						 </div>
                 	</div>
             	</div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Save",array('class'=>array('btn-success'))); ?>
                    <?php echo $form_edit->input_html("hidden","action",'edit'); ?>
                </div>
            </div>
        </div>
    </form>

    <?php } elseif($selected_tab=='maintenance') { ?>
    <form method="post" action="" enctype="multipart/form-data">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">Site Status</div>
				<div class="panel-body">
					<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							<label>Set Status <em>*</em></label>
							<?php echo $form_edit->input_html("select","setting[ws_status]",$_POST['ws_status'],['option'=>$class_website->config->status_type]); ?>
						</div>
                  </div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Status Message</label>
							<?php echo $form_edit->input_html("input","setting[ws_status_msg]",$_POST['ws_status_msg'],['placeholder'=>'For example: Site being updated...']); ?>
						</div>
					</div>
				</div>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading">Live Editing Settings</div>
				<div class="panel-body">
					<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							<label class="">Disable Live Editing <em>*</em> <?php echo $form_edit->icon_help('This allows you to disable the website while viewing it.'); ?></label>
							<?php echo $form_edit->input_html("select","setting[ws_cb_disable]",$_POST['ws_cb_disable'],['option'=>[0=>'No',1=>'Yes']]); ?>
						</div>
                  </div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="">Force Default Frame Content <em>*</em> <?php echo $form_edit->icon_help('This will automatically set the frames content as the main page information. WARNING: This will override your posts content.'); ?></label>
							<?php echo $form_edit->input_html("select","setting[ws_cb_force]",$_POST['ws_cb_force'],['option'=>[0=>'No',1=>'Yes']]); ?>
						</div>
                  </div>
				</div>
				</div>
			</div>
		</div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Save",array('class'=>array('btn-success'))); ?>
                <?php echo $form_edit->input_html("hidden","action",'edit'); ?>
            </div>
        </div>
    </div>
    </form>

    <?php if($_POST['ws_status']<=0) { ?>
    <form method="post" action="" enctype="multipart/form-data">
	<div class="row">
		<div class="col-md-12">
            <div class="panel panel-success">
				<div class="panel-heading"><i class="fas fa-bolt"></i> Go Live Checklist</div>
				<div class="panel-body checklist">

                    <p>Following steps are required to go live.</p>

                    <h3><?php if(strstr(MAIN_url,$_SERVER['HTTP_HOST'])) { ?>
                    <i class="fas fa-check-circle color-green"></i>
                    <input class="action" type="hidden" name="check[1]" id="status-1" value="1" />
                    <?php } else { ?>
                    <i class="fas fa-times color-red"></i>
                    <?php } ?> 1. Matched HTTP Host <?php echo $form_edit->icon_help("You can set this URL in the /includes/define.php file."); ?></h3><!-- step 1 -->

                    <h3>
                    <i class="fas fa-check-circle color-green"></i>
                    <input class="action" type="hidden" name="check[2]" id="status-2" value="1" />
                    2. Valid license for website <a href="<?php echo $zulu->link_page(PAGE_file,['query'=>['Action'=>'site','Tab'=>'license']]); ?>" class="btn btn-info btn-xs" target="_blank"><i class="fas fa-edit"></i></a></h3><!-- step 2 -->

                    <h3><?php echo "<a href=\"#\" class=\"select-task ".($_POST['check'][3]==1?'selected':NULL)."\" data-id=\"3\"><i class=\"far fa-circle\"></i><input class=\"action\" type=\"hidden\" name=\"check[3]\" id=\"status-3\" ".($_POST['check'][3]==1?'checked value="1"':"value=\"0\"")." /></a>"; ?> 3. Default contact email '<?php echo $class_setting->data['ws_contact_email']; ?>' is valid? <a href="<?php echo $zulu->link_page(PAGE_file,['query'=>['Action'=>'site','Tab'=>'general']]); ?>" target="_blank" class="btn btn-info btn-xs"><i class="fas fa-edit"></i></a></h3>

                    <h3><?php echo "<a href=\"#\" class=\"select-task ".($_POST['check'][4]==1?'selected':NULL)."\" data-id=\"4\"><i class=\"far fa-circle\"></i><input class=\"action\" type=\"hidden\" name=\"check[4]\" id=\"status-4\" ".($_POST['check'][4]==1?'checked value="1"':"value=\"0\"")." /></a>"; ?> 4. Default contact phone '<?php echo $class_setting->data['ws_contact_phone']; ?>' is valid? <a href="<?php echo $zulu->link_page(PAGE_file,['query'=>['Action'=>'site','Tab'=>'general']]); ?>" target="_blank" class="btn btn-info btn-xs"><i class="fas fa-edit"></i></a></h3>

                    <h3><?php echo "<a href=\"#\" class=\"select-task ".($_POST['check'][5]==1?'selected':NULL)."\" data-id=\"5\"><i class=\"far fa-circle\"></i><input class=\"action\" type=\"hidden\" name=\"check[5]\" id=\"status-5\" ".($_POST['check'][5]==1?'checked value="1"':"value=\"0\"")." /></a>"; ?> 5. Custom META Tags <small>'<?php echo $zulu->shorten($class_setting->data['ws_meta_title'],50); ?>'</small> <a href="<?php echo $zulu->link_page(PAGE_file,['query'=>['Action'=>'site','Tab'=>'general']]); ?>" target="_blank" class="btn btn-info btn-xs"><i class="fas fa-edit"></i></a></h3>

                    <h3><?php if($setting['ws_module_google_ga_profile']==NULL&&!strstr($setting['ws_tpl_script_body'],'analytics.js')&&!strstr($setting['ws_tpl_script_foot'],'analytics.js')) { ?>
                    <i class="fas fa-times color-red"></i>
                    <?php } else { ?>
                    <i class="fas fa-check-circle color-green"></i>
                    <input class="action" type="hidden" name="check[6]" id="status-6" value="1" />
                    <?php } ?> 6. Google&reg; Analytics <a href="<?php echo $zulu->link_page(PAGE_file,['query'=>['Action'=>'site','Tab'=>'miscellaneous']]); ?>" target="_blank" class="btn btn-info btn-xs"><i class="fas fa-edit"></i></a></h3>

                    <h3><?php echo "<a href=\"#\" class=\"select-task ".($_POST['check'][7]==1?'selected':NULL)."\" data-id=\"7\"><i class=\"far fa-circle\"></i><input class=\"action\" type=\"hidden\" name=\"check[7]\" id=\"status-7\" ".($_POST['check'][7]==1?'checked value="1"':"value=\"0\"")." /></a>"; ?> 7. Site is mobile friendly</h3>

                    <h3><?php if(file_exists($favicon_path)) { ?>
                    <i class="fas fa-check-circle color-green"></i>
                    <input class="action" type="hidden" name="check[8]" id="status-8" value="1" />
                    <?php } else { ?>
                    <i class="fas fa-times color-red"></i>
                    <?php } ?> 8. Favicon uploaded &amp; correct <?php if(file_exists($favicon_path)) { ?><small><img src="<?php echo $favicon_rel; ?>" class="image" /></small> <?php } ?><a href="<?php echo $zulu->link_page(PAGE_file,['query'=>['Action'=>'template']]); ?>" class="btn btn-info btn-xs" target="_blank"><i class="fas fa-edit"></i></a></h3><!-- step 8 -->

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
							<?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-rocket\"></span> Confirm &amp; Go Live",array('class'=>array('btn-success'))); ?>
                         <?php echo $form_edit->input_html("hidden","action",'golive'); ?>
                        </div>
                    </div>
				</div>
			</div>
		</div>
	</div>

    </form>

	<?php } ?>
    <div class="panel panel-default">
    	<div class="panel-heading"><i class="fas fa-laptop"></i> Site Settings</div>
    	<div class="panel-body">
    		<p class="margin-0 no-margin">
    			<b>Your template path:</b> <?php echo $zulu->path_clean($class_website->user_folder); ?>
    			<br><b>Your account id: </b> <?php echo $class_user->authorised->id; ?>
    			<br><b>Website URL: </b> <?php echo 'https://'.($class_setting->data['ws_site_host']!=NULL?$class_setting->data['ws_site_host'].FE_rel:FE_url); ?>
    		</p>
    	</div>
    </div>

	<?php if($class_setting->data['ws_status']==0) { //-- In UC Mode ?>
	<form method="post" action="">
		<div class="panel panel-danger">
			<div class="panel-heading"><i class="fas fa-star"></i> Quick Page / Menu Generator</div>
			<div class="panel-body">
				<p><b>Notes for using the generator:</b></p>
				<ul>
					<li>Add suffix ':SKIP' at the end to skip the affixing to the menu</li>
					<li>Add suffix ':SUB' at the end to add the item to the last parent menu item</li>
				</ul>
				<hr>
				<div class="form-group">
					<label>Enter a page name on each new line...</label>
					<?php echo $form_edit->input_html("textarea","post_list",$_POST['post_list'],['rows'=>10,'placeholder'=>"For Example:".PHP_EOL.PHP_EOL."Home".PHP_EOL."About Us".PHP_EOL."Contact Us".PHP_EOL."Terms of Use:SKIP".PHP_EOL."Privacy Policy:SKIP"]); ?>
				</div>
			</div>
			<div class="panel-footer">
				<button type="submit" class="btn btn-danger" name="set_rows"><i class="fa fa-chevron-right"></i> Save &amp; Generate</button>
				<input type="hidden" name="action" value="generate_page" />
			</div>
		</div>
	</form>
	<?php } ?>

	<?php } elseif($selected_tab=='social') { ?>
	<form method="post" action="">
		<div class="panel panel-default">
			<div class="panel-heading"><i class="fas fa-link"></i> Links</div>
			<div class="panel-body">
				<p>Enter in your social media locations here and then activate which ones you would like to display on your website.</p>
				<?php foreach($class_website->social_options as $key=>$val) { ?>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label><?= $val['label']; ?> URL</label>
							<?php echo $form_edit->input_html("input","setting[ws_social_link_".$key."]",$_POST['ws_social_link_'.$key], ['class'=>['social-link-input']]); ?>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label>Enable</label>
							<div class="form-switch<?= (!$_POST['ws_social_link_'.$key]?' disabled':null); ?>">
								<?php echo $form_edit->input_html("checkbox","setting[ws_social_link_".$key."_enable]",1,['id'=>'social-link-'.$key.'-enable','checked'=>$_POST['ws_social_link_'.$key.'_enable']]); ?>
								<label for="social-link-<?= $key; ?>-enable" class="label-success"></label>
							</div>
						</div>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Save",array('class'=>array('btn-success'))); ?>
					<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
				</div>
			</div>
		</div>
	</form>

    <?php } elseif($selected_tab=='script') { ?>
    <form method="post" action="">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Template Scripts</div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label><samp>&lt;head&gt;</samp> Script</label>
                            <?php echo $form_edit->input_html("code","setting[ws_tpl_script_head]",$_POST['ws_tpl_script_head'],['rows'=>7]); ?>
                        </div>
                        <div class="form-group">
                            <label>Open <samp>&lt;body&gt;</samp> Script</label>
                            <?php echo $form_edit->input_html("code","setting[ws_tpl_script_body]",$_POST['ws_tpl_script_body'],['rows'=>7]); ?>
                        </div>
                        <div class="form-group">
                            <label>Close <samp>&lt;/body&gt;</samp> Script</label>
                            <?php echo $form_edit->input_html("code","setting[ws_tpl_script_foot]",$_POST['ws_tpl_script_foot'],['rows'=>7]); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Save",array('class'=>array('btn-success'))); ?>
                    <?php echo $form_edit->input_html("hidden","action",'edit'); ?>
                </div>
            </div>
        </div>
    </form>

	<?php } ?>

<?php  } ?><!--site config-->

<?php if(PAGE_action=='theme') { ?>
    <div class="row">
        <div class="col-md-12">
            <ul class="nav nav-tabs" style="margin-bottom:10px;">
            <?php foreach($tab_list as $key=>$name) { ?>
            <li class="<?php echo ($selected_tab==$key?"active":NULL); ?>"><a class="layout-load" data-template="<?php echo $key; ?>" href="<?php echo $zulu->link_page(PAGE_file,['query'=>['Action'=>PAGE_action,"Tab"=>$key]]); ?>"><?php echo $name; ?></a></li>
            <?php } ?>
            </ul>
        </div>
    </div>
    <?php if($selected_tab=='theme') { ?>
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading"><i class="fas fa-archive"></i> Default Themes</div>
				<div class="panel-body">
					<?php echo $zulu->template->body_default; ?>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="row">
						<div class="col-md-6">
							<i class="fas fa-edit"></i> Your Themes
						</div>
						<div class="col-md-6 text-right">
							<a href="<?php echo $zulu->link_page(PAGE_file,['query'=>['Action'=>'template_edit']]); ?>" class="btn btn-default btn-xs"><i class="fas fa-plus-circle"></i> Create New Theme</a>
						</div>
					</div>
				</div>
				<div class="panel-body">
					<?php echo $zulu->template->body_your; ?>
				</div>
			</div>
		</div>
	</div>

	<?php } elseif($selected_tab=='theme_setting') { ?>
    <form method="post" enctype="multipart/form-data">

		<?php echo $zulu->template->body; ?>
		<div class="form-group no-margin submit">
			<?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Save",array('class'=>array('btn-success'))); ?>
			<?php echo $form_edit->input_html("hidden","action",'theme_setting'); ?>
		</div>

    </div>
    </form>

    <?php } ?>

<?php } ?><!--template config-->


<?php if(PAGE_action=='template_edit') { ?>
    <form method="post" action="" enctype="multipart/form-data">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">Template Settings</div>
				<div class="panel-body">
                    <div class="row">
                   		<div class="col-md-8">
							<div class="form-group">
								<label>Template Name</label>
								<?php echo $form_edit->input_html("input","name",$_POST['name']); ?>
							</div>
                   		</div>
                   		<div class="col-md-4">
                    		<div class="form-group">
                   				<label>Status</label>
                        		<?php echo $form_edit->input_html("select","status",(!isset($_POST['status'])?1:$_POST['status']),['option'=>[0=>'Draft',1=>'Published']]); ?>
							</div>
                   		</div>
                   	</div>
                    <div class="form-group">
                        <label>Description</label>
                        <?php echo $form_edit->input_html("textarea","description",$_POST['description'],['rows'=>3]); ?>
                    </div>
                    <?php if($class_user->authorised->role=='admin') { ?>
                    <div class="form-group">
                        <label><b>Header</b> HTML Code</label>
                        <?php echo $form_edit->input_html("textarea","file_header",$_POST['file_header'],['rows'=>10,'id'=>'ta-header']); ?>
                    </div>
                    <div class="form-group">
                        <label><b>Footer</b> HTML Code</label>
                        <?php echo $form_edit->input_html("textarea","file_footer",$_POST['file_footer'],['rows'=>10,'id'=>'ta-footer']); ?>
                    </div>
                    <div class="form-group">
                        <label><b>CSS</b> Code</label>
                        <?php echo $form_edit->input_html("textarea","file_css",$_POST['file_css'],['rows'=>10,'id'=>'ta-css']); ?>
                    </div>
                    <div class="form-group">
						<label>Preview Graphic</label>
						<?php if(!file_exists($logo_path)) { ?>
						<?php echo $form_edit->input_html("file","preview"); ?>
						<?php } else { ?>
						<p><span class="opt opt-success"><span class="fas fa-check"></span> Preview Uploaded</span> <a target="_blank" href="<?php echo $logo_rel; ?>" class="btn btn-xs btn-warning">View</a> <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Do'=>'DeletePreview'),'self'=>true)); ?>" class="btn btn-xs btn-danger">Remove</a></p>
					<?php } ?>
					</div>
                    <div class="form-group">
                        <label>User</label>
                        <?php echo $form_edit->input_html("select","user_id",$_POST['user_id'],['option'=>[0=>'Default (All Users)']+$form_edit->userOptionForm(false,0)]); ?>
                    </div>
					<?php } ?>
				</div>
				<?php if($class_user->authorised->role=='admin') { ?>
				<div class="panel-footer">
					<b>File Path</b> <?php echo FE_url."template/profile/".$_POST['token']."/"; ?>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <?php echo $form_edit->input_html("submit","submit","<span class=\"fas fa-save\"></span> Save",array('class'=>array('btn-success'))); ?>
                <?php echo $form_edit->input_html("hidden","action",'edit'); ?>
            </div>
        </div>
    </div>
    </form>
<?php } ?>
