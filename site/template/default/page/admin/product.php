
<div class="row">
	<?php if(PAGE_action==NULL) { ?>
    
    <div class="col-lg-12">
    
        <p>
            <?php if($product_option) { ?>
        	<a href="<?php echo $zulu->link_page('product',array('query'=>array('Action'=>'edit','id'=>$class_product->root_id))); ?>"><button class="btn btn-default" type="button"><span class="fas fa-reply"></span> Back to editing</button></a>
            <?php if(!$class_product->attribute_is($class_product->root_id)) { ?>
        	<a href="<?php echo $zulu->link_page('product',array('query'=>array('Action'=>'edit','Type'=>'product','ProductRoot'=>$class_product->root_id))); ?>"><button class="btn btn-primary" type="button"><span class="fas fa-plus-circle"></span> New Product<?php echo ($product_option?" Option":NULL); ?></button></a>
            <?php } ?>
            <?php } ?>
            
            <?php if(!$product_option) { ?>
        	<a href="<?php echo $zulu->link_page('product',array('query'=>array('Action'=>'edit','Type'=>'product','ProductRoot'=>$class_product->root_id))); ?>" class="btn btn-primary"><span class="fas fa-plus-circle"></span> New Product</a>
        	<a href="<?php echo $zulu->link_page('product',array('query'=>array('Action'=>'edit','Type'=>'category','ProductRoot'=>$class_product->root_id))); ?>" class="btn btn-primary"><span class="fas fa-plus-circle"></span> New Category</a>
            <?php } ?>
            <?php if($has_attribute&&$has_more_attributes) { ?>
            <a href="<?php echo $zulu->link_page('product',array('query'=>array('Action'=>'attribute_option','ProductRoot'=>$class_product->root_id))); ?>"><button class="btn btn-danger" type="button"><span class="fas fa-cubes"></span> Create Options</button></a>
            <?php } ?>
            <?php if($class_product->root_id==0) { ?>
			<a href="<?php echo $zulu->link_page('product',array('query'=>array('ProductRoot'=>-1))); ?>"  class="btn btn-default"><span class="fas fa-cubes"></span> All Products</a>
            <?php } ?>
            
            <?php if($class_product->root_id>0 && $product_option) { ?>
			<a href="<?php echo $zulu->link_page('product',array('query'=>array('Action'=>'attribute','ProductRoot'=>$class_product->root_id))); ?>"  class="btn btn-warning"><span class="fas fa-cube"></span> Attributes</a>

			<a href="<?php echo $zulu->link_page('product',array('query'=>array('Action'=>'attribute_option','ProductRoot'=>$class_product->root_id))); ?>"><button class="btn btn-danger" type="button"><span class="fas fa-cubes"></span> View Options</button></a>
            <?php } ?>
            
            <a href="<?php echo $zulu->link_page('product',array('self'=>true,'query'=>array('Action'=>'stock'))); ?>" class="btn btn-success"><span class="fas fa-truck"></span> Adjust Stock</a>
			
            <?php if($_GET['Do']=='bulk') { ?>
            <a href="<?php echo $zulu->link_page('product',array('self'=>true,'filter'=>array('Do'))); ?>"><button class="btn btn-default" type="button"><span class="fas fa-bars"></span> Exit Bulk Update</button></a>
			<?php } else { ?>
            <a href="<?php echo $zulu->link_page('product',array('self'=>true,'query'=>array('Do'=>'bulk'))); ?>"><button class="btn btn-success" type="button"><span class="fas fa-bars"></span> Bulk Update</button></a>
            <?php } ?>
            
            <?php if($product_option) { ?>
			<span class="opt opt-grey">&nbsp;&nbsp;<i class="fas fa-info-circle"></i> This product has <b><?php echo $count_option; ?></b> options, with <b><?php echo $count_possible; ?></b> possible options.</span>
            <?php } ?>
            
        </p>
        
    	
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
            	
            	<div class="col-lg-12">
					<form action="" method="get">
						<?php echo $form_edit->input_html("hidden","Page",$_GET['Page']); ?>
						<?php if($_GET['ProductRoot'] != NULL) { echo $form_edit->input_html("hidden","ProductRoot",$_GET['ProductRoot']); } ?>
						<?php if($_GET['Do'] != NULL) { echo $form_edit->input_html("hidden","Do",$_GET['Do']); } ?>
                        <?php if(isset($_GET['brand']) && $_GET['brand'] > 0) { echo $form_edit->input_html("hidden","brand",$_GET['brand']); } ?>
						<div class="row">
							<div class="col-lg-2">
								<div class="input-group">
									<?php echo $form_edit->input_html("input","Search",$_GET['Search'],['custom'=>['placeholder'=>'Search','id'=>'search-box']]); ?>
									<span class="input-group-btn"><?php echo $form_edit->input_html("submit","",'<span class="fas fa-search"></span>',['custom'=>['id'=>'search-submit']]); ?></span>
								</div>
							</div> 
						</div>
					</form>
           			<br>
            		<form method="post" action="">
						<?php echo $zulu->template->body; ?>
						<div align="center"><?php echo $pagination; ?></div>
						<p class="page-count text-center opt opt-grey"><i class="fas fa-bars"></i> <?php echo $product_total_count; ?> record(s) in total</p>

						<?php if($bulk_mode) { ?>
						<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['id'=>'postsubmit','class'=>['btn-success','btn']]); ?>
						<?php echo $form_edit->input_html("hidden","action",'bulk'); ?>
						<?php } else { ?>
						<div class="panel panel-info panel-checkbox-action-box">
							<div class="panel-heading"> <span class="fas fa-fire"></span> Select an action to perform on selected items...</div>
							<div class="panel-body">
								<div class="form-group">
									<?php echo $form_edit->input_html("select","execute",$_POST['execute'],array('option'=>array(0=>'None','publish'=>'Publish','hide'=>'Hide','new'=>'New','unnew'=>'Remove New','delete'=>'Delete'))); ?>
							   </div>
								<?php echo $form_edit->input_html("submit","submit_complete",'<span class="fas fa-check"></span> Confirm',array('class'=>array('btn-info'))); ?>
							</div>
						</div>
						<?php } ?>
					</form>
				</div>
    		</div>
			<?php if($class_product->root_id==0&&!$all) { ?>
			<div class="panel-footer">
				<span class="opt opt-grey"><i class="far fa-info-circle"></i> Note: Click 'All Products' to see all products, including those you may have not categorised yet.</span>
			</div>
			<?php } ?>
        </div>	
        
    </div>
    <?php } ?>
    <?php if(PAGE_action=='stock') { ?>
    
    <div class="col-lg-12">
        <form method="post" action="">
        	<p><a href="<?php echo $zulu->link_page(PAGE_file,['self'=>true,'filter'=>['Action']]); ?>" class="btn btn-default"><span class="fas fa-reply"></span> Cancel</a> <button type="submit" class="btn btn-success"><span class="fas fa-check"></span> Adjust Stock</button></p>
            <div class="panel panel-default">
            	<div class="panel-body">
               		<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label>Adjustment Note</label>
								<?php echo $form_edit->input_html("input","reason",$_POST['reason'],array('placeholder'=>'(optional)')); ?>
						   </div>
					   </div>
						<?php if($class_setting->data) { ?>
						<div class="col-md-3">
							<div class="form-group">
								<label>Location</label>
								<?php echo $form_edit->input_html("select","location_id",$_POST['location_id'],array('option'=>$loc_array)); ?>
						   </div>
					   </div>
						<?php } ?>
					</div>
            		<?php echo $zulu->template->body; ?>
            	</div>
            </div>
            <?php echo $form_edit->input_html('hidden','action','stock_adjust'); ?>
        	<p><a href="<?php echo $zulu->link_page(PAGE_file,['self'=>true,'filter'=>['Action']]); ?>" class="btn btn-default"><span class="fas fa-reply"></span> Cancel</a> <button type="submit" class="btn btn-success"><span class="fas fa-check"></span> Adjust Stock</button></p>
        </form>
    </div>
    
    <?php } ?>
    <?php if(PAGE_action=='edit') { ?>
    <div class="col-lg-12">
            <?php if($_POST['template']=='membership'||$_POST['meta']['object']=='membership') { ?>
            <div class="panel panel-red">
                <div class="panel-heading">
                    <span class="fas fa-exclamation-triangle"></span> Warning! Any changes to pricing here will adjust all linked subscriptions billing amounts.
                </div>
            </div>
            <?php } ?>
        	<form role="form" action="" method="post" enctype="multipart/form-data">
			<?php $form_edit->categoryOptionForm(); ?>
            <div class="panel panel-primary">
                <div class="panel-heading">
                	<?php echo ($product_option?"Option Information":"Basic Information"); ?>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label><?php echo ($product_option?"Option ":($type=='Category'?"Category ":"Product ")); ?>Name</label>
                                <?php echo $form_edit->input_html("input","name",$_POST['name'],array('placeholder'=>($product_option?"Leave blank for default title...":NULL))); ?>
                            </div>
                        </div>
                    	<?php if($type!='Category') { ?>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>SKU</label>
                                <?php echo $form_edit->input_html("input","sku",$_POST['sku']); ?>
                            </div>
                        </div>
                        <?php } else { ?>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Parent Category</label>
								<?php echo $form_edit->input_html("select","parent_id",($type=='Category'?($_GET['ProductRoot']>0?$_GET['ProductRoot']:$_POST['parent_id']):NULL),array("option"=>$output,"data_label"=>true)); $parent_select_defined = true; ?> 
                            </div>
                        </div>
                        <?php } ?>
                    </div>	
            	</div>
            </div>
            
            <?php if($type!='Category') { ?>
            <div class="row">
                <div class="col-md-<?php if(!$product_option) { ?>6<?php } else { ?>12<?php } ?>">
					<?php if($has_attribute||$has_options) { ?>
                    <div class="panel panel-info">
                    	<div class="panel-heading">
                        <div class="row">
                            <div class="col-md-4">
                                <span class="far fa-money-bill"></span> This is an attributed product
                            </div>
                            <div class="col-md-8 text-right">
                                <?php if(!$new && ($class_setting->data['price_break_enable'] || !$product_option || ($has_attribute && !$has_options))) { ?>
                            	<span class="color-grey"><span class="fas fa-bars"></span> Options</span>
                                <?php if(!$product_option) { ?>
                                <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('ProductRoot'=>PAGE_id,'Action'=>'attribute'))); ?>" class="btn btn-warning btn-xs"><span class="fas fa-cube"></span> Attributes</a> 
                                <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('ProductRoot'=>PAGE_id))); ?>" class="btn btn-info btn-xs"><span class="fas fa-cube"></span> All Variants</a> 
								<a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('ProductRoot'=>PAGE_id,'Action'=>'edit'))); ?>" class="btn btn-info btn-xs"><span class="fas fa-cube"></span> New Variant</a>
                                <?php if($has_attribute) { ?> 

                                <?php if(!$has_options) { ?>
                                <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('ProductRoot'=>PAGE_id))); ?>" class="btn btn-danger btn-xs"><span class="fas fa-rocket"></span> Generate Options</a> 
                                <?php } } } ?>
                                <?php if($class_setting->data['price_break_enable']) { ?>
                                <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('id'=>PAGE_id,'Action'=>'price_break'))); ?>" class="btn btn-success btn-xs"><span class="fas fa-arrows-h"></span> Price Breaks</a>
                                <?php } ?>
                                <?php } ?>
                            </div>
                        </div>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Display Mode</label>
                                        <?php echo $form_edit->input_html("select","meta[option_display]",$_POST['meta']['option_display'],['option'=>[''=>'Default']+$class_product->config->display_mode]); ?>
                                    </div>
                                </div>
                        	</div>
                        </div>
                    </div>
                    <?php } else { ?>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-md-3">
                                    <span class="fas fa-money-bill"></span> Pricing Options
                                </div>
                                <div class="col-md-9 text-right">
                                    <?php if(!$new && (!$product_option || $class_setting->data['price_break_enable'])) { ?>
                                	<span class="color-grey"><span class="fas fa-bars"></span> Product Options</span>
                                    <?php if(!$product_option) { ?>
                                    <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('ProductRoot'=>PAGE_id,'Action'=>'attribute'))); ?>" class="btn btn-warning btn-xs"><span class="fas fa-cube"></span> Attributes</a> 
                                	<a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('ProductRoot'=>PAGE_id))); ?>" class="btn btn-info btn-xs"><span class="fas fa-cube"></span> Variants</a> 
                                    <?php if($has_attribute) { ?>
                                    <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('ProductRoot'=>PAGE_id))); ?>" class="btn btn-danger btn-xs"><span class="fas fa-cubes"></span> Options</a> 
                                    <?php } ?>
                                    <?php } ?>
                                    <?php if($class_setting->data['price_break_enable']) { ?>
                                    <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('id'=>PAGE_id,'Action'=>'price_break'))); ?>" class="btn btn-success btn-xs"><span class="fas fa-arrows-h"></span> Price Breaks</a> 
                                    <?php } ?>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Price</label>
                                        <div class="form-group input-group">
                                        <span class="input-group-addon"><?php echo LOCALE_currency_symbol; ?></span>
                                        <?php echo $form_edit->input_html("input","price",number_format($_POST['price'],2)); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Special Price (if any)</label>
                                        <div class="form-group input-group">
                                        <span class="input-group-addon"><?php echo LOCALE_currency_symbol; ?></span>
                                        <?php echo $form_edit->input_html("input","price_special",number_format($_POST['price_special'],2)); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
            	</div>
				<?php if(!$product_option) { ?>
                <div class="col-md-6">
                	<div class="panel panel-warning">
                    	<div class="panel-heading">
                        	<i class="fas fa-folder"></i> <?php if($type=='Category') { ?>Parent <?php } ?>Category
                        </div>
                        <div class="panel-body">
                        	<div class="row">
								<?php if(!$product_option) { ?>
                                <div class="col-md-12">
                                    <div class="form-group no-margin">
                                        <label><?php echo ($type=='Category'?"Parent":"Add Product to "); ?> Category</label>
                                        <div class="row">
                                        	<div class="col-md-10">
                                        		<?php echo $form_edit->input_html("select","parent_id",($type=='Category'?($_GET['ProductRoot']>0?$_GET['ProductRoot']:$_POST['parent_id']):NULL),array('class'=>['input-cat'],"option"=>$output,"data_label"=>true)); ?> 
                                        	</div>
                                            <div class="col-md-2">
                                            	<button class="btn btn-block btn-warning bt-new-cat" type="button"><i class="fas fa-plus-circle"></i> Add</button>
                                            </div>
                                        </div>
                                        <p class="js-categories"><?php echo implode("",$cat_preload); ?></p>
                                    </div>
                                </div>
                                <?php } ?>
                        	</div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
            
            <div class="panel panel-default">
            	<div class="panel-heading"><i class="fas fa-edit"></i> Description &amp; Media</div>
               <div class="panel-body">
                   	<div class="row">
                   		<div class="col-md-6">
                   			<div class="form-group">
								<label>Description</label>
								<?php echo $form_edit->input_html("htmlarea","description",stripslashes($_POST['description']),array('custom'=>array('id'=>'description'))); ?>
							</div>
					   	</div>
                 		<?php if($type!='Category' && !$new && !$product_option) { ?>
                  		<div class="col-md-6">
                  			<div class="form-group">
                  				<div class="row">
                  					<div class="col-md-3">
										<label>Features</label>
									</div>
                 					<div class="col-md-9 text-right">
										<p><a class="btn btn-primary btn-xs" href="<?php echo $zulu->link_page('product',array('query'=>array('Action'=>'feature_edit','Product'=>$id))); ?>" type="button"><i class="fas fa-plus-circle"></i> New Feature</a></p>
									</div>
                  				</div>
								<?php echo $zulu->template->feature_table; ?>
							</div>
					   	</div>
                  		<?php } ?>
                   	</div>
                    
                    <?php if($type!='Category' && !$product_option) { ?>
                    <div class="form-group">
                    	<label>Youtube Videos</label>
                        <div class="input_fields_wrap">
                            <?php echo $video_url_html; ?>
						</div>
                    </div>
                    <?php } ?>
                </div>
            </div>
            
            <div class="panel panel-default">
            	<div class="panel-heading">
                	<i class="far fa-image"></i> Image Upload
                </div>
                <div class="panel-body">
                    <div class="form-group">
                            <label>Add images...</label>
                            <?php echo $class_file->uploadifive_input("image"); ?>
                            <hr>
                            <div class="coltable col5 float pad">
                            <?php foreach($image_data['_all'] as $row) { ?>
                            <div class="col">
                            	<div class="image">
                                	<img src="<?php echo zulu::thumb($row,"w=300&h=200&zc=1&bg=ffffff"); ?>" class="responsive" alt="product image" />
                                    <div class="ctrl">
                                    	<a href="<?php echo $zulu->link_page('product',array('query'=>array('Action'=>'edit','id'=>PAGE_id,'Method'=>'DeleteImage','Image'=>str_replace($image_data['_path'],'',$row)))); ?>"><button type="button" class="btn confirm btn-xs btn-danger">Delete</button></a>
                                    	<a href="<?php echo $zulu->link_page('product',array('query'=>array('Action'=>'edit','id'=>PAGE_id,'Method'=>'SetImageMain','Image'=>str_replace($image_data['_path'],'',$row)))); ?>"><button type="button" class="btn btn-xs btn-<?php echo ($row==$image_data['main']?"success":"default"); ?>"><?php echo ($row==$image_data['main']?"Main":"Set Main"); ?></button></a>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            </div>
                        </div>
                	</div>
                </div>
            
            <?php if($type!='Category') { ?>
			<div class="panel panel-info">
				<div class="panel-heading"><a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#panel-logistic" aria-expanded="false"><i class="fas fa-info-circle"></i> Logistics &amp; Supplier</a></div>
              <div id="panel-logistic" class="panel-collapse collapse">
				<div class="panel-body">
					<div class="row">
						<div class="col-md-2">
							<div class="form-group">
								<label>Supplier</label>
								<?php echo $form_edit->input_html("select","meta[supplier_id]",$_POST['meta']['supplier_id'],['option'=>$supplier_array]); ?>
							</div>
						</div>
						<?php if($product_data['type_variant']==1||$product_data['type_variant']==0) { //-- root products cost prices ?>
							<div class="col-md-2">
								<div class="form-group">
									<label>Cost Price / Unit Price</label>
									<div class="form-group input-group">
									<span class="input-group-addon"><?php echo LOCALE_currency_symbol; ?></span>
									<?php echo $form_edit->input_html("input","price_base",$_POST['price_base'],['placeholder'=>'0.00']); ?>
									</div>
								</div>
							</div>
							<?php if($product_data['type_variant']==1) { ?>
							<div class="col-md-2">
								<div class="form-group">
									<label>Cost Price Method</label>
									<?php echo $form_edit->input_html("select","meta[price_base_method]",$_POST['meta']['price_base_method'],['option'=>[''=>'Standard','percent'=>'Percent']]); ?>
								</div>
							</div>
							<?php } ?>
						<?php } ?>
						<?php if($parent_data['id']>0&&$product_data['type_variant']==2) { //-- variant products cost prices ?>
							<?php if($parent_meta['price_base_method']!='percent') { ?>
							<div class="col-md-2">
								<div class="form-group">
									<label>Cost Price / Unit Price</label>
									<div class="form-group input-group">
									<span class="input-group-addon"><?php echo LOCALE_currency_symbol; ?></span>
									<?php echo $form_edit->input_html("input","price_base",$_POST['price_base'],['placeholder'=>'0.00']); ?>
									</div>
								</div>
							</div>
							<?php } else { ?>
							<div class="col-md-2">
								<div class="form-group">
									<label>Weighting on Product Cost Price</label>
									<div class="form-group input-group">
									<?php echo $form_edit->input_html("input","meta[price_weight]",$_POST['meta']['price_weight'],['placeholder'=>'0.00']); ?>
									<span class="input-group-addon">%</span>
									</div>
								</div>
							</div>
							<?php } ?>
  						<?php } ?>
						<div class="col-md-2">
							<div class="form-group">
								<label>Supplier Sell Notification <?php echo $form_edit->icon_help("This will email alert the supplier automatically when a product is sold."); ?></label>
								<?php echo $form_edit->input_html("select","meta[supplier_alert]",$_POST['meta']['supplier_alert'],['option'=>[0=>'No',1=>'Yes']]); ?>
							</div>
						</div>
   						<div class="col-md-1">
							<div class="form-group">
								<label>Weight (kg)</label>
								<?php echo $form_edit->input_html("input","meta[weight]",stripslashes($_POST['meta']['weight'])); ?>
							</div>
						</div>
						<div class="col-md-1">
							<div class="form-group">
								<label>Width (cm)</label>
								<?php echo $form_edit->input_html("input","meta[width]",stripslashes($_POST['meta']['width'])); ?>
							</div>
						</div>
						<div class="col-md-1">
							<div class="form-group">
								<label>Height (cm)</label>
								<?php echo $form_edit->input_html("input","meta[height]",stripslashes($_POST['meta']['height'])); ?>
							</div>
						</div>
						<div class="col-md-1">
							<div class="form-group">
								<label>Depth (cm)</label>
								<?php echo $form_edit->input_html("input","meta[depth]",stripslashes($_POST['meta']['depth'])); ?>
							</div>
						</div>
                    	<div class="col-md-2">
							<div class="form-group">
								<label>Additional Shipping Price</label>
								<div class="form-group input-group">
									<span class="input-group-addon"><?php echo LOCALE_currency_symbol; ?></span>
									<?php echo $form_edit->input_html("input","meta[ship_price]",stripslashes($_POST['meta']['ship_price'])); ?>
								</div>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>Additional Unit Shipping Price</label>
								<div class="form-group input-group">
									<span class="input-group-addon"><?php echo LOCALE_currency_symbol; ?></span>
									<?php echo $form_edit->input_html("input","meta[ship_price_unit]",stripslashes($_POST['meta']['ship_price_unit'])); ?>
								</div>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>Stock ETA <?php echo $form_edit->icon_help('Only shows if stock is 0. If ETA has passed, nothing will show.'); ?></label>
								<?php echo $form_edit->input_html("input","meta[opt_stock_eta]",stripslashes($_POST['meta']['opt_stock_eta']),['placeholder'=>'DD/MM/YYYY','class'=>['field-date','date']]); ?>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>Stock Parent <?php echo $form_edit->icon_help('Select a product for this item to adjust when purchased.'); ?></label>
								<?php
								echo $form_edit->input_html("input","sa_prod_name",$_POST['sa_prod_name'],array('autocomplete'=>false,'class'=>array('sf-input','typeahead','sf-input-product_quick'),'placeholder'=>'Add product...','custom'=>array('data-sf'=>'sf_product','data-populate'=>'sf-product_id','autocomplete'=>'off')))."
                        		<div class=\"guessbox guessbox-product_quick\">
                           			<ul></ul>
                        		</div>
                        		".$form_edit->input_html("hidden","stock_adjust[0][product_id]",$_POST['stock_adjust'][0]['product_id'],array('id'=>'sf-product_quick','class'=>array('sf-value')));
								
								?>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>Stock Parent Adjust <?php echo $form_edit->icon_help('Setting a value will mean stock off the parent product will be adjusted too by the specified amount.'); ?></label>
								<?php echo $form_edit->input_html("input","stock_adjust[0][quantity]",stripslashes($_POST['stock_adjust'][0]['quantity']),['placeholder'=>'0.00']); ?>
							</div>
						</div>
                    </div>
				</div>
			</div>
			</div>

            <div class="panel panel-info">
            	<div class="panel-heading"><a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#panel-options" aria-expanded="false"><i class="fas fa-cogs"></i> Other Options</a></div>
              
              <div id="panel-options" class="panel-collapse collapse">
               <div class="panel-body">
               <h4>General Settings</h4>
               	<div class="row">
               		<div class="col-md-2">
						<div class="form-group">
							<label>Sort Order</label>
							<?php echo $form_edit->input_html("number","sort",$_POST['sort']); ?>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>Web Featured <?php echo $form_edit->icon_help('This will highlight the product on your website (if your website is active)'); ?></label>
							<?php echo $form_edit->input_html("checkbox","feature",1,($_POST['feature']==1?array('custom'=>array('checked'=>'checked')):NULL)); ?>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>Digital / Intangible <?php echo $form_edit->icon_help('If this product is void from shipping.'); ?></label>
							<?php echo $form_edit->input_html("checkbox","meta[digital]",1,($_POST['meta']['digital']==1?array('custom'=>array('checked'=>'checked')):NULL)); ?>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>Disable Quantity Grouping <?php echo $form_edit->icon_help('Automatically seperates individual items instead of combining them as 1.'); ?></label>
							<?php echo $form_edit->input_html("checkbox","meta[quantity_group_stop]",1,($_POST['meta']['quantity_group_stop']==1?array('custom'=>array('checked'=>'checked')):NULL)); ?>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>Hide Product</label>
							<?php echo $form_edit->input_html("checkbox","hide",1,($_POST['hide']==1?array('custom'=>array('checked'=>'checked')):NULL)); ?>
						</div>
					</div>
			   </div>
			   <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Clearance</label>
                            <?php echo $form_edit->input_html("checkbox","meta[clearance]",1,($_POST['meta']['clearance']==1?array('custom'=>array('checked'=>'checked')):NULL)); ?>
                        </div>
                    </div>
					<div class="col-md-2">
						<div class="form-group">
							<label>Ignore Xero Inventory <?php echo $form_edit->icon_help('This will skip the linkage of this item to Xero\'s inventory system.'); ?></label>
							<?php echo $form_edit->input_html("checkbox","meta[xero_ignore]",1,($_POST['meta']['xero_ignore']>0?['custom'=>['checked'=>'checked']]:NULL)); ?>
						</div>
					</div>
				</div>
			   <h4>Custom Configurations</h4>
				<div class="row">
					<div class="col-md-2">
						<div class="form-group">
							<label>Linked Object</label>
							<?php echo $form_edit->input_html("input","meta[object]",stripslashes($_POST['meta']['object'])); ?>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>Linked Object ID</label>
							<?php echo $form_edit->input_html("input","meta[object_id]",stripslashes($_POST['meta']['object_id'])); ?>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>Custom Variables</label>
							<?php echo $form_edit->input_html("input","meta[custom_variable]",stripslashes($_POST['meta']['custom_variable'])); ?>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label>Basket Custom Input Label <?php echo $form_edit->icon_help('You can set input text box that shows on the basket page where the user can enter custom data. For example: Name for engraving'); ?></label>
							<?php echo $form_edit->input_html("input","meta[input_label]",stripslashes($_POST['meta']['input_label'])); ?>
						</div>
					</div>
                </div>
            		<?php if($type!='Category') { ?>
				   <h4>Product Data</h4>
					<div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>UPC / EAN Barcode</label>
                                <?php echo $form_edit->input_html("input","meta[barcode]",stripslashes($_POST['meta']['barcode'])); ?>
                            </div>
                        </div>
						<div class="col-md-2">
                            <div class="form-group">
                                <label>Brand <small><a href="<?= $zulu->link_page(PAGE_file, ['query'=>['Action'=>'brand']]); ?>" target="_blank"><i class="fas fa-external-link-alt"></i></a></small></label>
                                <?php echo $form_edit->input_html("select","brand_id",$_POST['brand_id'],['option'=>[0=>'None']+ProductBrand::optionArray()]); ?>
                            </div>
                        </div>
						<div class="col-md-2">
							<div class="form-group">
								<label>Mark as New</label>
								<?php echo $form_edit->input_html("checkbox","new",1,($_POST['new']==1?array('custom'=>array('checked'=>'checked')):NULL)); ?>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
				</div>
            </div>
            <?php } else { ?>
            <div class="panel panel-info">
                <div class="panel-heading"><a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#panel-options" aria-expanded="false"><i class="fas fa-cogs"></i> Other Options</a></div>
                <div id="panel-options" class="panel-collapse collapse">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-lg-2 col-md-3">
                                <div class="form-group">
                                    <label>Browse View <?php echo $form_edit->icon_help("What to show when browsing this catgegory."); ?></label>
                                    <?php echo $form_edit->input_html("select","meta[browse_view]",$_POST['meta']['browse_view'],['option'=>['default'=>'Use Default Setting','product'=>'Products Only','category'=>'Subcategories Only','both'=>'Both']]); ?> 
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Sort Order</label>
                                    <?php echo $form_edit->input_html("number","sort",$_POST['sort']); ?>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Hide Category</label>
                                    <?php echo $form_edit->input_html("checkbox","hide",1,($_POST['hide']==1?array('custom'=>array('checked'=>'checked')):NULL)); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
                
            <?php if($class_user->authorised->opt_website){ ?>
				<div class="panel panel-info">
					<div class="panel-heading"><a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#panel-meta" aria-expanded="false"><i class="fas fa-tag"></i> Meta Data</a></div>
					<div id="panel-meta" class="panel-collapse collapse">
						<div class="panel-body">
							<p>Meta data is data that is not displayed on the page but is used by search engines to index your products.</p>
							<div class="row">
								<div class="col-md-2">
									<div class="form-group">
										<label>Meta Title <span class="fas fa-info-circle color-grey help" title="This is the title displayed in the window and the header of search results. Normally 50-60 characters."></span></label>
										<?php echo $form_edit->input_html("input","meta[meta_title]",stripslashes($_POST['meta']['meta_title'])); ?>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>Meta Description <span class="fas fa-info-circle color-grey help" title="This is displayed in search results for the description. Normally 150-160 characters."></span></label>
										<?php echo $form_edit->input_html("input","meta[meta_description]",stripslashes($_POST['meta']['meta_description'])); ?>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>Meta Keywords <span class="fas fa-info-circle color-grey help" title="Not required, but can be used for common search terms."></span></label>
										<?php echo $form_edit->input_html("input","meta[meta_keywords]",stripslashes($_POST['meta']['meta_keywords'])); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
            
            <?php } ?>
			<?php echo $form_edit->input_html("submit","submit_return","<i class=\"fas fa-reply\"></i> Save &amp; Return",['class'=>['btn-default','btn']]); ?>
			<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['id'=>'postsubmit','class'=>['btn-success','btn']]); ?>
            <?php echo $form_edit->input_html("hidden","action",'edit'); ?>

            <?php echo $form_edit->input_html("hidden","type",($type!=NULL?strtolower($type):'product')); ?>
			<?php if($_POST['product_temp_folder']!=NULL) { ?>
	            <?php echo $form_edit->input_html("hidden","product_temp_folder",$_POST['product_temp_folder']); ?>
            <?php } ?>
            <?php if($_POST['parent_id']>0&&$_POST['type_variant']!=0&&$_POST['type_variant']!=1) { ?>
	            <?php echo $form_edit->input_html("hidden","parent_id",$_POST['parent_id']); ?>
            <?php } ?>
            
            </form>
            
            <?php if($type!='Category' && !$new) { ?>
            <hr />
            <div class="row">
                <div class="col-md-6">
                    <form method="post" action="">
                    <div class="panel panel-green">
                        <div class="panel-heading"><a class="collapsed" data-toggle="collapse" href="#collapse-stock" aria-expanded="false"><span class="fas fa-truck"></span> Stock Control</a></div>
                        <div id="collapse-stock" class="panel-collapse collapse">
                       <div class="panel-body">
                            <?php if($stock_control) { ?>
                            <?php if($has_options) { ?>
                            <h4><?php echo $class_product->stock_label($current_stock); ?></h4>
                            <p class="caption color-grey margin-0">Based on current stock from all variants.</p>
                            <?php } else { ?>
                            <div class="row">
                                <div class="col-xs-2">
                                    <div class="form-group">
                                        <label>Adjust</label>
                                        <?php echo $form_edit->input_html("number","stock",(!$_POST['stock']?'0':$_POST['stock']),array('custom'=>array('id'=>'stock','min'=>1))); ?>
                                    </div>
                                </div>
								<?php if($stock_location_count>0) { ?>
                                <div class="col-xs-4">
                                    <div class="form-group">
                                        <label>Note</label>
                                        <?php echo $form_edit->input_html("input","note",$_POST['note'],array('placeholder'=>'(optional)','custom'=>array('id'=>'note'))); ?>
                                    </div>
                                </div>
                                <div class="col-xs-3">
                                    <div class="form-group">
                                        <label>Location</label>
                                        <?php echo $form_edit->input_html("select","location_id",$_POST['location_id'],array('option'=>$opt_location)); ?>
                                    </div>
                                </div>
								<?php } else { ?>
                                <div class="col-xs-7">
                                    <div class="form-group">
                                        <label>Note</label>
                                        <?php echo $form_edit->input_html("input","note",$_POST['note'],array('placeholder'=>'(optional)','custom'=>array('id'=>'note'))); ?>
                                    </div>
                                </div>
								<?php } ?>
                                <div class="col-xs-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <p>
                                        <button type="submit" name="submit_sub" class="btn btn-danger"><span class="fas fa-minus"></span></button>&nbsp;<button type="submit" name="submit_add" class="btn btn-success"><span class="fas fa-plus"></span></button></p>
                                            <?php echo $form_edit->input_html("hidden","do",'stock'); ?>
                                    </div>
                                </div>
                            </div>
						   
						   	<?php if($loc_stock_count>0) { ?>
						   <hr>
                          	<h4><span class="fas fa-map-marker"></span> Location inventory</h4>
                             <?php echo $zulu->template->body->panel_stock_location; ?>
						   <?php } ?>
						   
                            <hr>
                            <div class="coltable col2 vmiddle">
                                <div class="col">
                                	<h4><span class="fas fa-bars"></span> Stock Log</h4>
                                </div>
                                <div class="col text-right">
                                	<h4><?php echo $class_product->stock_label($current_stock); ?>
									</h4>
                                </div>
                            </div>
                            <br>
                             <?php echo $zulu->template->body->panel_stock; ?>
                             <?php } ?>
                             <?php }  else { ?>
                             <span class="color-grey">Stock information available once you save this new product.</span>
                             <?php } ?>
                        </div>
                        </div>
                    </div>
                    </form>
                </div>
                
                <div class="col-md-6">
                    <div class="panel panel-yellow">
                        <div class="panel-heading"><a class="collapsed" data-toggle="collapse" href="#collapse-report" aria-expanded="false"><span class="fas fa-chart-line"></span> Product Report</a></div>
						<div id="collapse-report" class="panel-collapse collapse">
							<div class="panel-body">
								<?php echo $zulu->template->body->panel_report; ?>
							</div>
						</div>
               	 	</div>
            	</div>
    		</div>
    		<?php } ?>
    	</div>
        <br>
    <?php } ?>
    <?php if(PAGE_action=='attribute') { ?>
    <div class="col-md-12">
        <p>
        	<a href="<?php echo $zulu->link_page('product',array('query'=>array('Action'=>'edit','id'=>$class_product->root_id))); ?>"><button class="btn btn-default" type="button"><span class="fas fa-reply"></span> Edit <?php echo ($is_product?"Product":"Category"); ?></button></a>

        	<a href="<?php echo $zulu->link_page('product',array('query'=>array('Action'=>'attribute_edit','field[product_id]'=>$class_product->root_id))); ?>"><button class="btn btn-primary" type="button"><span class="fas fa-plus-circle"></span> Create Attribute</button></a>
            
            <?php if($has_attribute) { ?>
            <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('ProductRoot'=>$class_product->root_id))); ?>"><button class="btn btn-info" type="button"><span class="fas fa-cube"></span> All Variants</button></a> 

            <a href="<?php echo $zulu->link_page('product',array('query'=>array('Action'=>'attribute_option','ProductRoot'=>$class_product->root_id))); ?>"><button class="btn btn-danger" type="button"><span class="fas fa-cubes"></span> View Options</button></a>
            
			<span class="opt opt-grey">&nbsp;&nbsp;<i class="fas fa-info-circle"></i> This product has <b><?php echo $count_option; ?></b> options, with <b><?php echo $count_possible; ?></b> possible options.</span>
            <?php } ?>
        </p>
        
    	<form method="post" action="">
        <div class="panel panel-primary">
            <div class="panel-heading">
            	<i class="fas fa-cube"></i> <?php echo ($is_product?"Product":"Category"); ?> Attributes
            </div>
            <div class="panel-body">
            	<div class="col-lg-12">
    				<?php echo $zulu->template->body; ?>
				</div>
    		</div>
        </div>	
        
        <div class="panel panel-info panel-checkbox-action-box">
        	<div class="panel-heading"> <span class="fas fa-fire"></span> Select an action to perform on selected items...</div>
        	<div class="panel-body">
            	<div class="form-group">
                	<?php echo $form_edit->input_html("select","execute",$_POST['execute'],array('option'=>array(0=>'None','delete'=>'Delete'))); ?>
               </div>
				<?php echo $form_edit->input_html("submit","submit_complete",'<span class="fas fa-check"></span> Confirm',array('class'=>array('btn-info'))); ?>
        	</div>
        </div>
        </form>
        
        <?php if(count($inherit_count)>0) { ?>
        <div class="panel panel-default">
            <div class="panel-body">
            	<i class="fas fa-cube"></i> Inherited Attributes
            </div>
            <div class="panel-body">
            	<div class="col-lg-12">
    				<?php echo $zulu->template->body_inherit; ?>
				</div>
    		</div>
        </div>	
        <?php } ?>
        
    </div>
	<?php }	?>
	<?php if(PAGE_action=='attribute_option') { ?>
    <div class="col-md-12">
    	<p>You can select and save product combinations on this page, tick the 'create' box and then save to create options. To generate <b>all</b> options, click the 'Generate Combinations' button below, then all the below options will be instantly saved.</p>
        <p>
        	<a href="<?php echo $zulu->link_page('product',array('query'=>array('Action'=>'edit','id'=>$class_product->root_id))); ?>" class="btn btn-default"><span class="fas fa-reply"></span> Edit <?php echo ($is_product?"Product":"Category"); ?></a>

        	<a href="<?php echo $zulu->link_page('product',array('query'=>array('Action'=>'attribute','ProductRoot'=>$class_product->root_id))); ?>"  class="btn btn-warning"><span class="fas fa-cube"></span> Manage Attributes</a>
            
			<a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('ProductRoot'=>$class_product->root_id))); ?>"><button class="btn btn-info" type="button"><span class="fas fa-cube"></span> All Variants</button></a> 
            
            <?php if($count_option<$count_possible) { ?>
			<a href="<?php echo $zulu->link_page('product',array('query'=>array('Action'=>'attribute_option','Do'=>'GenOptions','ProductRoot'=>$class_product->root_id))); ?>"  class="btn btn-danger"><span class="fas fa-sync-alt"></span> Generate All Options</a>
			<?php } ?>
            
			<span class="opt opt-grey">&nbsp;&nbsp;<i class="fas fa-info-circle"></i> This product has <b><?php echo $count_option; ?></b> options, with <b><?php echo $count_possible; ?></b> possible options.</span>
            
        </p>
        
    	<form method="post" action="">
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
            	<div class="col-lg-12">
    				<?php echo $zulu->template->body; ?>
				</div>
    		</div>
        </div>	
        
        <?php if($count_option<$count_possible) { ?>
		<p><?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['class'=>['btn btn-success']]); ?>
		<?php echo $form_edit->input_html("hidden","action",'save'); ?>
        </p>
        
        <div class="panel panel-info panel-checkbox-action-box">
        	<div class="panel-heading"> <span class="fas fa-fire"></span> Select an action to perform on selected items...</div>
        	<div class="panel-body">
            	<div class="form-group">
                	<?php echo $form_edit->input_html("select","execute",$_POST['execute'],array('option'=>array(0=>'None','delete'=>'Delete'))); ?>
               </div>
				<?php echo $form_edit->input_html("submit","submit_complete",'<span class="fas fa-check"></span> Confirm',array('class'=>array('btn-info'))); ?>
        	</div>
        </div>
        <?php } ?>
        </form>
        
      
    </div>
	<?php }	?>
    <?php if(PAGE_action=='attribute_edit') { ?>
    <div class="col-md-12">
    <form role="form" action="" method="post">

        <div class="panel panel-primary">
            <div class="panel-heading">
				<i class="fas fa-edit"></i> Settings
            </div>
            <div class="panel-body">
                <div class="row">
                   <div class="col-md-4">
                        <div class="form-group">
                            <label>Attribute Name <em>*</em></label>
                            <?php echo $form_edit->input_html("input","name",$_POST['name']); ?> 
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Input Type</label>
                            <?php echo $form_edit->input_html("select","input",$_POST['input'],['option'=>$class_product->config->attr_input]); ?> 
                        </div>
                    </div>
                   <div class="col-md-2">
                        <div class="form-group">
                            <label>Sort Order</label>
                            <?php echo $form_edit->input_html("input","input_sort",$_POST['input_sort'],['placeholder'=>'Sorts lowest number first']); ?> 
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Hide from site?</label>
                            <?php echo $form_edit->input_html("select","input_hide",$_POST['input_hide'],['option'=>[0=>'No',1=>'Yes']]); ?> 
                        </div>
                    </div>
                </div>	
            </div>
        </div>
        
        <div class="panel panel-default">
            <div class="panel-heading">
				<i class="fas fa-cubes"></i> Options
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6" id="attr-table">
                        <?php /*?><div class="form-group">
                            <label>Options (Separate with commas) <em>*</em></label>
                            <?php echo $form_edit->input_html("input","options",$_POST['options']); ?> 
                        </div><?php */?>
                        <?php echo $zulu->template->body_option; ?>
						<p class='text-center'><button type='button' id='row-add' class='btn btn-primary btn-xs' title='Add another option'><span class='fas fa-plus-circle'></span> Add another option</button></p>
                    </div>
                </div>
           </div>
       </div>
        
        <?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['id'=>'postsubmit','class'=>['btn-success','btn']]); ?>
        <?php echo $form_edit->input_html("hidden","action",'edit'); ?>
        <?php echo $form_edit->input_html("hidden","product_id",$_POST['product_id']); ?>
        
    </form>
    </div>
    <?php } ?>
    <?php if(PAGE_action=='feature_edit') { ?>
    <div class="col-md-12">
    <form role="form" action="" method="post" enctype="multipart/form-data">

        <div class="panel panel-primary">
            <div class="panel-heading">
				<i class="fas fa-edit"></i> Settings
            </div>
            <div class="panel-body">
                <div class="row">
					<div class="col-md-6">
						<div class="row">
							<div class="col-md-8">
								<div class="form-group">
									<label>Title <em>*</em></label>
									<?php echo $form_edit->input_html("input","title",stripslashes($_POST['title'])); ?> 
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label>Sort Order</label>
									<?php echo $form_edit->input_html("input","sort",$_POST['sort'],['placeholder'=>'Sorts lowest number first']); ?> 
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label>Description</label>
									<?php echo $form_edit->input_html("textarea","description",stripslashes($_POST['description']),array('custom'=>array('id'=>'description','rows'=>'5'))); ?>
								</div>
							</div>
						</div>
					</div>
               		<div class="col-md-6">
               			<div class="row">
               				<div class="col-md-12">
								<div class="form-group">
									<label>Image</label>
									<?php if($_POST['image']==NULL) { ?>
										<?php echo $form_edit->input_html("file","image"); ?>
									<?php } else { ?>
										<p><span class="opt opt-success"><span class="fas fa-check"></span> Image Uploaded</span> <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'feature_edit','Method'=>'DeleteImage'))); ?>" class="btn btn-xs btn-danger">Remove</a></p>
										<div class="row">
											<div class="col-md-6">
												<div class="image">
													<img class="responsive" src="<?php echo $zulu->thumb($class_product->image_rel.$_POST['product_id']."/feature/".$id."/".$_POST['image'],"w=300&h=200&zc=1&bg=ffffff"); ?>" alt="feature image">
												</div>
											</div>
										</div>
									<?php } ?>
								</div>
							</div>
               			</div>
					</div>
                </div>	
            </div>
        </div>
        
        <?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['id'=>'postsubmit','class'=>['btn-success','btn']]); ?>
        <?php echo $form_edit->input_html("hidden","action",'edit'); ?>
        <?php echo $form_edit->input_html("hidden","product_id",$_POST['product_id']); ?>
        
    </form>
    </div>
    <?php } ?>
    <?php if(PAGE_action=='special') { ?>
    
    <div class="col-lg-12">
		<p>
			<?php echo $new_special_html ; ?>
		</p>
		<div class="panel panel-default">
			<!-- /.panel-heading -->
			<div class="panel-body">
				<ul class="nav nav-tabs style-project">
					<li class="<?php echo (!isset($tab)?"active":NULL); ?>"><a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>['Action'=>'special'])); ?>" class="" aria-expanded="false">All</a></li>
					<li class="<?php echo ($tab=='active'?"active":NULL); ?>"><a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>['Action'=>'special','sort'=>'active'])); ?>" class="" aria-expanded="false">Active</a></li>
					<li class="<?php echo ($tab=='upcoming'?"active":NULL); ?>"><a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>['Action'=>'special','sort'=>'upcoming'])); ?>" class="" aria-expanded="false">Upcoming</a></li>
					<li class="<?php echo ($tab=='complete'?"active":NULL); ?>"><a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>['Action'=>'special','sort'=>'complete'])); ?>" class="" aria-expanded="false">Complete</a></li>
				</ul>

				<?php echo $zulu->template->body; ?>
			</div>
		</div>
	</div>
    <?php } ?>
    <?php if(PAGE_action=='special_edit'){ ?>
	<form role="form" action="" method="post">
		<div class="row">
			<div class="col-lg-6">
				<div class="panel panel-primary">
					<div class="panel-heading"><h4 class="panel-title">Special Settings</h4></div>
					<div class="panel-body">
							<div class="row">
								<div class="col-lg-4">
									<div class="form-group">
										<label>Title</label>
										<?php echo $form_edit->input_html('input','title',$_POST['title']); ?>
									</div>
								</div>
								<div class="col-lg-4">
									<label>Date From</label>
									<div class="input-group">
										<span class="input-group-addon"><span class="far fa-calendar-minus"></span></span>
										<?php echo $form_edit->input_html('input','date_from',$_POST['date_from'],['custom'=>['placeholder'=>'DD-MM-YYYY'],'class'=>['datepicker']]); ?>					
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label>Date To</label>
										<div class="input-group">
											<span class="input-group-addon"><span class="far fa-calendar-plus"></span></span>
											<?php echo $form_edit->input_html('input','date_to',$_POST['date_to'],['custom'=>['placeholder'=>'DD-MM-YYYY'],'class'=>['datepicker']]); ?>						
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-4">
									<div class="form-group">	
										<label>Discount Type</label>
										<?php echo $form_edit->input_html('select','discount_type',$_POST['discount_type'],['option'=>['fixed'=>'Fixed','percent'=>'Percent','actual'=>'Actual']]); ?>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label>Amount</label>
										<?php echo $form_edit->input_html('input','amount',$_POST['amount']); ?>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label>Enabled</label>
										<?php echo $form_edit->input_html('checkbox','status',1,['custom'=>($_POST['status']?['checked'=>'checked']:NULL)]); ?>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<?php echo $form_edit->input_html("hidden","action",'save_special'); ?>
										<?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['class'=>['btn btn-success']]); ?>
										<?php echo $form_edit->input_html("reset","reset",'Reset'); ?>
									</div>
								</div>
							</div>
					</div>
					<div class="panel-footer">
						<p class="text-muted"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> Be aware of applying discounts that are less than the original price.</p>
					</div>
				</div>

			</div>
			<div class="col-lg-6">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">Product Selection</h4>
					</div>
					<div class="panel-body">
						<select id="product_select" name="products[]" multiple="multiple">
							<?php echo $options_html; ?>
						</select>
					</div>
				</div>
			</div>
		</div>
	</form>	
	<?php } ?>
    <?php if(PAGE_action=='price_break') { ?>
    <div class="col-md-12">
        <div class="row">
            <div class="col-lg-12">
                <p>
                    <a class="btn btn-default" href="<?php echo $zulu->link_page('product',array('query'=>array('Action'=>'edit','id'=>PAGE_id))); ?>"><span class="fas fa-reply"></span> Edit Product</a>
                </p>
       	    </div>
        </div>
        <?php if(!$breaks_valid) { ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> There is a conflict with the Price Breaks you've entered, if you don't fix them then it could lead to unexpected pricing being used. <br><br>Please check the following:
            <ul>
                <li>You have no double-ups</li>
                <li>The Price Breaks don't fall within the bounds of another Price Break</li>
            </ul>
        </div>
        <?php } ?>
        <form method="post" action="">
            <div class="row">
                <div class="col-md-8">
                    <div class="panel panel-info">
                        <div class="panel-heading"><i class="fas fa-arrows-h" aria-hidden="true"></i> Price Breaks</div>
                        <div class="panel-body">
                            <?php echo $zulu->template->body; ?>
                            <div class="row">
                                <p class='text-center'><button type='button' id='row-add' class='btn btn-primary btn-xs' title='Add another option'><span class='fas fa-plus-circle'></span> Add another option</button></p>
                            </div>    
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel panel-default">
                        <div class="panel-heading"><i class="fas fa-cog" aria-hidden="true"></i> Settings</div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Disable Price Breaks</label>
                                        <?php echo $form_edit->input_html('select','meta[price_break_disable]',$_POST['meta']['price_break_disable'],['option'=>['0'=>'No','1'=>'Yes']]); ?>
                                    </div>
                                </div>
                                <?php if($product_row['parent_id'] > 0) { ?>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Disable Parent Price Breaks</label>
                                        <?php echo $form_edit->input_html('select','meta[price_break_disable_parent]',$_POST['meta']['price_break_disable_parent'],['option'=>['0'=>'No','1'=>'Yes']]); ?>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>    
                        </div>
                    </div>
                </div>
            </div>
            <?php echo $form_edit->input_html("submit","submit",$form_edit->CONFIG_button_save,['id'=>'postsubmit','class'=>['btn-success','btn']]); ?>
            <?php echo $form_edit->input_html("hidden","action",'edit'); ?>
        </form>
    </div>
	<?php }	?>
    
    <?php if(PAGE_action=='brand') { ?> 
    
    <div class="col-lg-12">
        <p>
            <a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('Action'=>'brand_edit'))); ?>" class="btn btn-primary"><span class="fa fa-plus-circle"></span> New Brand</a>
        </p>
        
        <form action="" method="get">
            <?php echo $form_edit->input_html("hidden","Page",$_GET['Page']); ?>
            <?php echo $form_edit->input_html("hidden","Action",$_GET['Action']); ?>
            <div class="row">
                <div class="col-lg-3 col-md-4">
                    <div class="input-group">
                        <?php echo $form_edit->input_html("input","Search",$_GET['Search'],['custom'=>['placeholder'=>'Search','id'=>'search-box']]); ?>
                        <span class="input-group-btn"><?php echo $form_edit->input_html("submit","",'<span class="fa fa-search"></span>',['custom'=>['id'=>'search-submit']]); ?></span>
                    </div>
                </div> 
            </div>
        </form>
        <br>
        <?php echo $zulu->template->body; ?>
        <div align="center"><?php echo $pagination; ?></div>
        <p class="page-count text-center opt opt-grey"><i class="fa fa-bars"></i> <?php echo $total_count; ?> record(s) in total</p>
    </div>
    <?php } ?>
	<?php if(PAGE_action=='brand_edit') { ?>
    <form role="form" action="" method="post">
    	<div class="col-lg-12">
    		<div class="panel panel-default">
				<div class="panel-heading"><i class="fa fa-edit"></i> Main Info</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label>Name <span class="denote">*</span></label>
								<?php echo $form_edit->input_html("input","title",$_POST['title']); ?>
							</div>
						</div>
                        <?php if($class_user->authorised->opt_website) { ?>
						<div class="col-md-2">
							<div class="form-group">
								<label>Hide</label>
								<?php echo $form_edit->input_html("checkbox","hide",1,array('checked'=>$_POST['hide'])); ?>
							</div>
						</div>
                        <?php } ?>
                    </div>
                    <div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Description</label>
								<?php echo $form_edit->input_html("htmlarea","description",$_POST['description']); ?>
							</div>
						</div>
                        <?php if($class_user->authorised->opt_website) { ?>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Image</label>
                                        <?php if($image != null) { ?>
                                        <div class="image">
                                            <div class="ctrl">
                                                <a href="<?= $zulu->link_page(PAGE_file, ['self'=>true,'query'=>['Do'=>'ClearImage']]); ?>" class="btn btn-danger btn-xs bt-image-delete" data-type="header"><i class="fas fa-times"></i></a>
                                            </div>
                                            <img src="<?php echo $image; ?>?<?php echo $zulu->serial(8); ?>" alt="custom image" />
                                        </div>
                                        <?php } ?>
                                        <div class="upload-wrapper"<?php echo ($image?" hidden":null); ?>>
                                            <?php echo $class_file->uploadifive_input("image_main"); ?>
                                        </div>
                                        <?php if($new) { ?>
                                        <?php echo $form_edit->input_html("hidden","temp_folder",$temp_folder); ?>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <?php if($image != null) { ?>
                                    <div class="form-group">
                                        <label>Default Feature Colour <?php echo $form_edit->icon_help("Extracted from the uploaded image."); ?></label>
                                        <?php if($_POST['colour_default'] != null) { ?>
                                        <div class="colour-preview-box" style="background-color:<?= ($_POST['colour_default']); ?>"></div>
                                        <p><?= $_POST['colour_default']; ?></p>
                                        <?php } else { ?>
                                        <p>Unable to extract a colour. Please try reuploading the image.</p>
                                        <?php } ?>
                                    </div>
                                    <div class="form-group">
                                        <label>Feature Colour Override</label>
                                        <?php echo $form_edit->input_html("color","colour_manual",$_POST['colour_manual']); ?>
                                        <label>No Override <?php echo $form_edit->input_html("checkbox","colour_manual_off",1,['checked'=>$_POST['colour_manual_off']]); ?></label>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
						</div>
                        <?php } ?>
                    </div>
				</div>
			</div>
            
            <?php if($class_user->authorised->opt_website) { ?>
            <div class="panel panel-info">
                <div class="panel-heading"><a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#panel-meta" aria-expanded="false"><i class="fa fa-tag"></i> Meta Data</a></div>
                <div id="panel-meta" class="panel-collapse collapse">
                    <div class="panel-body">
                        <p>Meta data is data that is not displayed on the page but is used by search engines to index your pages.</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Meta Title <span class="fas fa-info-circle color-grey help" title="This is the title displayed in the window and the header of search results. Normally 50-60 characters."></span></label>
                                    <?php echo $form_edit->input_html("input","meta_title",stripslashes($_POST['meta_title']),['countchar'=>true,'countchar_min'=>50,'countchar_max'=>60]); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Meta Keywords <span class="fas fa-info-circle color-grey help" title="Not required, but can be used for common search terms."></span></label>
                                    <?php echo $form_edit->input_html("input","meta_keywords",stripslashes($_POST['meta_keywords']),['countchar'=>true]); ?>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Meta Description <span class="fas fa-info-circle color-grey help" title="This is displayed in search results for the description. Normally 150-160 characters."></span></label>
                                    <?php echo $form_edit->input_html("input","meta_description",stripslashes($_POST['meta_description']),['countchar'=>true,'countchar_min'=>150,'countchar_max'=>300]); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
            
			<?php echo $form_edit->input_html("submit","submit","<span class=\"fa fa-save\"></span> Save",array('class'=>array('btn-success'))); ?>
			<?php echo $form_edit->input_html("hidden","action",'edit'); ?>
    	</div>
    </form>
    <?php } ?>
    
    <?php if(PAGE_action=='review') { ?>
    
    <div class="col-lg-12">
        <form action="" method="get">
            <?php echo $form_edit->input_html("hidden","Page",$_GET['Page']); ?>
            <?php echo $form_edit->input_html("hidden","Action",$_GET['Action']); ?>
            <?php if($_GET['ProductRoot'] != NULL) { echo $form_edit->input_html("hidden","ProductRoot",$_GET['ProductRoot']); } ?>
            <?php if($_GET['Do'] != NULL) { echo $form_edit->input_html("hidden","Do",$_GET['Do']); } ?>
            <div class="row">
                <div class="col-lg-3 col-md-4">
                    <div class="input-group">
                        <?php echo $form_edit->input_html("input","Search",$_GET['Search'],['custom'=>['placeholder'=>'Search review...','id'=>'search-box']]); ?>
                        <span class="input-group-btn"><?php echo $form_edit->input_html("submit","",'<span class="fa fa-search"></span>',['custom'=>['id'=>'search-submit']]); ?></span>
                    </div>
                </div> 
                <div class="col-lg-3 col-md-4">
                    <?php echo $form_edit->input_html("input","",'',['custom'=>['placeholder'=>'Search product...','id'=>'product-box', 'autocomplete'=>'off', 'data-sf'=>'sf_product_main','data-populate'=>'id'], 'class'=>['sf-input','typeahead','sf-input-id','input-id']]); ?>
                    <div class="guessbox guessbox-id">
                        <ul></ul>
                    </div>
                    <?php echo $form_edit->input_html("hidden","Product",$_GET['Product'],['id'=>'product-filter','class'=>['sf-value']]); ?>
                </div>
            </div>
        </form>
        <br>
        <div class="row">
			<div class="col-md-12">
				<ul class="nav nav-tabs">
                    <?php foreach($tabs as $key=>$val) { ?>
                    <li class="<?php echo ($view_tab==$key?"active":NULL); ?>">
                        <a class="layout-load" data-template="<?php echo $key; ?>" href="<?php echo $zulu->link_page(PAGE_file,['self'=>true, 'query'=>["Tab"=>$key],'filter'=>['Pg']]); ?>"><?php echo $val; ?></a>
                    </li>
                    <?php } ?>
				</ul>
			</div>
		</div>
        <form method="post" action="">
            <?php echo $zulu->template->body; ?>
            <div align="center"><?php echo $pagination; ?></div>
            <p class="page-count text-center opt opt-grey"><i class="fa fa-bars"></i> <?php echo $total_count; ?> record(s) in total</p>
        </form>
    </div>
    
    <?= implode('',$modals); ?>
    <?php } ?>
    
</div>
