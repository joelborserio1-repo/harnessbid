<!-- /.row -->
<div class="row">
	<?php if(PAGE_action==NULL) { ?>

    <div class="col-md-<?php echo ($zulu->template->post['html_sidebar']!=NULL?"9":"12"); ?>">
    <form role="form" action="" method="post">
		<p>
			<a href="<?php echo $zulu->link_page(PAGE_file,array('query'=>array('type'=>$type,'Action'=>'edit'))); ?>" class="btn btn-primary"><span class="fas fa-plus-circle"></span> New <?php echo $post_template['name']; ?></a>
        </p>

        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">

                <ul class="nav nav-tabs">
					<li class="<?php echo ($_GET['filter']['status']=='published'?"active":NULL); ?>"><a href="<?php echo $zulu->link_page(PAGE_file,array('self'=>true,'query'=>["filter"=>['status'=>'published']])); ?>" class="" aria-expanded="false">Published <?php echo $count[1]; ?></a></li>
					<li class="<?php echo ($_GET['filter']['status']=='draft'?"active":NULL); ?>"><a href="<?php echo $zulu->link_page(PAGE_file,array('self'=>true,'query'=>["filter"=>['status'=>'draft']])); ?>" class="" aria-expanded="false">Drafts <?php echo $count[0]; ?></a></li>
					<li class="<?php echo ($_GET['filter']['status']=='hidden'?"active":NULL); ?>"><a href="<?php echo $zulu->link_page(PAGE_file,array('self'=>true,'query'=>["filter"=>['status'=>'hidden']])); ?>" class="" aria-expanded="false">Hidden / Trash <?php echo $count[2]; ?></a></li>
                </ul>

    			<?php echo $zulu->template->body; ?>
            </div>
    	</div>
        <?php if($zulu->vars->task_count>0) { ?>
        <div class="panel panel-info panel-checkbox-action-box">
        	<div class="panel-heading"> <span class="fas fa-fire"></span> Select an action to perform on selected posts...</div>
        	<div class="panel-body">
            	<div class="form-group">
                	<?php echo $form_edit->input_html("select","execute",$_POST['execute'],array('option'=>$bulk_actions)); ?>
               </div>
				<?php echo $form_edit->input_html("submit","submit_complete",'<span class="fas fa-check"></span> Confirm',array('class'=>array('btn-info'))); ?>
        	</div>
        </div>
        <?php } ?>
        </form>
    </div>
    <?php if($zulu->template->post['html_sidebar']!=NULL) { ?>
    <div class="col-md-3">
    	<?php echo $zulu->template->post['html_sidebar']; ?>
    </div>
    <?php } ?>

    <?php } ?>
	<?php if(PAGE_action=='edit') { ?>
    <form role="form" action="" method="post" id="postform">
    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                <span class="fas fa-info-circle"></span> <?php echo $post_template['name']; ?> Information
            </div>
            <div class="panel-body">
                <div class="row">
                    <?php if(!isset($zulu->template->post['field_title'])||(isset($zulu->template->post['panel_body'])&&$zulu->template->post['field_title'])) { ?>
                    <div class="col-sm-<?php echo (count($FIELD['head'])>0?"6":"12"); ?>">

                        <div class="form-group">
                            <label>Title <em>*</em></label>
							<?php echo $form_edit->input_html("input","title",stripslashes($_POST['title'])); ?>
                        </div>
                    </div>
                    <?php } ?>
                    <?php echo $form_edit->admin_form_build($FIELD['head'],['class'=>['col-xs-3']]); ?>
                </div>
            </div>
        </div>

        <?php if(!isset($zulu->template->post['panel_body'])||(isset($zulu->template->post['panel_body'])&&$zulu->template->post['panel_body'])) { ?>

        <?php if(trim($post_data['_meta']['post_index']) != NULL) { ?>
        <div class="alert alert-danger">
            <p>This page is setup display your <b><?php echo ($class_post->config->template[$post_data['_meta']['post_index']]['name_plural']!=NULL?$class_post->config->template[$post_data['_meta']['post_index']]['name_plural']:$class_post->config->template[$post_data['_meta']['post_index']]['name']); ?></b>. To add/update your <b><?php echo $class_post->config->template[$post_data['_meta']['post_index']]['name_plural']; ?></b>, go <a href='<?php echo $zulu->link_page('post',['query'=>['type'=>$post_data['_meta']['post_index']]]); ?>' class="alert-link">here</a>.</p>
        </div>
        <?php } ?>

        <?php if($post_template['config']['post_builder'] && !$new) { ?>
        <?php echo $form_edit->input_html("hidden","meta[post_builder]",($_POST['meta']['post_builder']?'1':'0')); ?>
        <?php echo $form_edit->input_html("hidden","post_builder_id",$post_builder_row['id']); ?>
        <p>
            <a href="#" id="pb-hide"><button class="btn btn-primary" type="button"><span class="fas fa-edit"></span> Use Default Editor</button></a>
            <a href="#" id="pb-show"><button class="btn btn-primary" type="button"><span class="fas fa-wrench"></span> Use Content Builder</button></a>
        </p>
        <div class="panel panel-default" id="pb-section">
            <div class="panel-heading"><span class="fas fa-pencil"></span> Content Builder</div>
            <div class="panel-body"><?php echo $class_post->post_builder_admin_html($post_builder_row['id']); ?></div>
        </div>
        <?php } ?>

        <div class="panel panel-default" id="default-content-section">
            <div class="panel-heading">
                <span class="fas fa-pencil"></span> Content
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($class_post->vars->frame_url!=NULL) { ?>
                                <iframe src="<?php echo $class_post->vars->frame_url; ?>" style="height:800px"></iframe>
                            <?php } else { ?>
                            <?php echo $form_edit->input_html("htmlarea","content",stripslashes($_POST['content'])); ?>
                            <?php } ?>
                        </div>
                    </div>
                    <?php echo $form_edit->admin_form_build($FIELD['body']); ?>
                </div>
            </div>
            <?php if($post_template['config']['post_builder'] && $new) { ?>
            <div class="panel-footer">
				<span class="opt opt-grey"><i class="far fa-info-circle"></i> If you would like to use the content builder, please <b>save</b> this page first to start editing.</span>
			</div>
			<?php } ?>
        </div>
        <?php } ?>
        <?php echo $zulu->template->post['html_body']; ?>
    </div>
    <div class="col-md-4">

    	<div class="row">
        	<div class="col-md-12">
            	<p><?php echo $form_edit->input_html("submit","submit","<i class=\"fas fa-save\"></i> ".($_POST['status']=='published'?'Save':'Publish')." ".$post_template['name'],['class'=>['btn btn-success btn-block'],'id'=>'postsubmit']); ?></p>
                <?php if($post_template['config']['frontend']) { ?>
            	<p><?php echo $form_edit->input_html("submit","submit_preview","<i class=\"fas fa-laptop\"></i> Preview",['class'=>['btn btn-info btn-block']]); ?></p>
                <?php } ?>
                <?php if($new) { ?>
            	<p><?php echo $form_edit->input_html("submit","submit_draft","<i class=\"fas fa-pause\"></i> Save Draft",['class'=>['btn btn-warning btn-block']]); ?></p>
                <?php } ?>
            </div>
       	</div>

        <?php echo $form_edit->input_html("hidden","action",'edit'); ?>

    	<div class="panel panel-primary">
        	<div class="panel-heading"><i class="fas fa-laptop"></i> <?php echo $post_template['name']; ?> Information</div>
            <div class="panel-body">
                <div class="form-group">
                    <label>Status</label>
                    <p><span class="opt opt-bord opt-<?php echo $status_data['css']; ?>"><i class="fas fa-<?php echo $status_data['icon']; ?>"></i> <?php echo $status_data['label']; ?></span></p>
                </div>

                <?php if($post_template['config']['frontend'] && $post_template['config']['frontend_single']) { ?>
                <div class="form-group">
                    <label>Live URL <?php echo $form_edit->icon_help("The live URL gives direct access to this {$post_template['name']} post."); ?> <?php echo ($new?NULL:"<a href=\"".$class_post->post_url(PAGE_id)."\" title=\"View live post.\" target=\"_blank\" class=\"color-grey\"><i class=\"fas fa-external-link-alt\"></i></a>"); ?></label>
                    <?php if(PAGE_id > 0) { ?>
                    	<div class="input-group">
                            <span class="input-group-addon"><?php echo zulu::shorten(FE_url.($post_template['slug']!=NULL?$post_template['slug']."/":NULL),15); ?></span>
                            <?php echo $form_edit->input_html("input","live_url",$_POST['slug'],['id'=>'field_url']); ?>
                            <span class="input-group-addon">/</span>
                        </div>
                    <?php } else { ?>
                    	<?php echo $form_edit->input_html("input","live_url",$_POST['_data']['url'],['placeholder'=>'Save '.$type.' to access link...','readonly'=>true,'id'=>'field_url','disabled'=>true]); ?>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>

    	<div class="panel panel-info ">
        	<div class="panel-heading"><a data-toggle="collapse" href="#cp-config" class="" aria-expanded="true"><i class="fas fa-wrench"></i> Configuration</a></div>
            <div id="cp-config" class="panel-collapse collapse" aria-expanded="false">
            <div class="panel-body">
                <div class="form-group">
                    <label>Sort Order <?php echo $form_edit->icon_help("The lower the number, the higher up the list the item will show."); ?></label>
                    <?php echo $form_edit->input_html("input","sort",$_POST['sort']); ?>
                </div>
                <?php if($post_template['config']['redirect']) { ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Redirect Link <?php echo $form_edit->icon_help("Choose to send the user to a different page."); ?></label>
                            <?php echo $form_edit->input_html("input","meta[redirect_link]",$_POST['meta']['redirect_link']); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Open link in</label>
                            <?php echo $form_edit->input_html("select","meta[redirect_location]",$_POST['meta']['redirect_location'],['option'=>['_self'=>'Same Tab','_blank'=>'New Tab','_parent'=>'Parent Tab']]); ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
                <?php if($post_template['config']['parent']) { ?>
                <div class="form-group">
                    <label>Parent <?php echo $form_edit->icon_help("You can make this {$post_template['name']} post a child of one of the selectable options."); ?></label>
                    <?php echo $form_edit->input_html("select","parent_id",$_POST['parent_id'],['option'=>$post_parent_array]); ?>
                </div>
                <?php } ?>
                <?php if($post_template['config']['frame']) { ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                        	<label>Frame <?php echo $form_edit->icon_help("You can use a hard-coded frame from the FRAME folder."); ?></label>
                        	<?php echo $form_edit->input_html("select","meta[frame]",$_POST['meta']['frame'],['option'=>$frame_array]); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="form-group">
                            	<label>Full Width <?php echo $form_edit->icon_help("In conjunction with your frame, this overrides the default fixed frame width and allows for easy creation of multiple sections."); ?></label>
                            	<?php echo $form_edit->input_html("select","meta[frame_full_width]",$_POST['meta']['frame_full_width'],['option'=>['1'=>'Yes','0'=>'No']]); ?>
                            </div>
                			</div>
                    </div>
                 </div>
				  <?php } ?>
                <?php if($post_template['config']['post_index']) { ?>
                <div class="form-group">
                    <label>Post Index <?php echo $form_edit->icon_help("Display a grid list of a certain post type."); ?></label>
                    <?php echo $form_edit->input_html("select","meta[post_index]",$_POST['meta']['post_index'],['option'=>$post_type_array]); ?>
                </div>
                <?php } ?>
                <div class="form-group">
                    <label>Custom Variables <?php echo $form_edit->icon_help("For use by web developer only. Format: VAR=VAL,VAR=VAL"); ?></label>
                    <?php echo $form_edit->input_html("input","meta[custom]",$_POST['meta']['custom']); ?>
                </div>
                <?php if($post_template['config']['sitemap']) { ?>
                <div class="form-group">
					<label>Show on sitemap</label>
					<?php echo $form_edit->input_html("checkbox","meta[sitemap_show]",1,($_POST['meta']['sitemap_show'] == '1'?['checked'=>true]:NULL)); ?>
				</div>
                <?php } ?>
				<?php echo $form_edit->admin_form_build($FIELD['sidebar'],['columns'=>false]); ?>
            </div>
            </div>
        </div>

        <?php if($post_template['config']['meta']) { ?>
    	<div class="panel panel-default ">
        	<div class="panel-heading"><a data-toggle="collapse" href="#cp-meta" class="" aria-expanded="true"><i class="fas fa-tag"></i> Custom Meta Tags</a></div>
            <div id="cp-meta" class="panel-collapse collapse" aria-expanded="false">
            <div class="panel-body">
            	<div class="form-group">
                    <label>Meta Title <?php echo $form_edit->icon_help("This is the title displayed in the window and the header of search results. Normally 50-60 characters."); ?></label>
                    <?php echo $form_edit->input_html("input","meta[meta_title]",$_POST['meta']['meta_title']); ?>
                </div>
            	<div class="form-group">
                    <label>Meta Description <?php echo $form_edit->icon_help("This is displayed in search results for the description. Normally 150-160 characters."); ?></label>
                    <?php echo $form_edit->input_html("input","meta[meta_description]",$_POST['meta']['meta_description']); ?>
                </div>
            	<div class="form-group">
                    <label>Meta Keywords <?php echo $form_edit->icon_help("Not required, but can be used for common search terms."); ?></label>
                    <?php echo $form_edit->input_html("input","meta[meta_keyword]",$_POST['meta']['meta_keyword']); ?>
                </div>
            </div>
        	</div>
        </div>
        <?php } ?>

        <?php if($post_template['config']['image']['main']||$post_template['config']['image']['gallery']) { ?>
    	<div class="panel panel-warning">
        	<div class="panel-heading"><i class="far fa-image"></i> <?php echo $post_template['name']; ?> Images</div>
            <div class="panel-body">
                    <?php if($post_template['config']['image']['main']) { ?>
                    <div class="form-group">
                        <label>Feature Image</label>
                        <?php if($post_image['main']!=NULL) { ?>
                        <div class="image">
                        	<div class="ctrl"><a href="#" class="btn btn-danger btn-xs bt-image-delete" data-type="main"><i class="fas fa-times"></i></a></div>
                        	<img src="<?php echo $class_post->config->file_rel.$post_image['main']; ?>?<?php echo $zulu->serial(8); ?>" alt="main image" />
                        </div>
                        <?php } ?>
                        <div class="upload-wrapper type-main" style="<?php echo ($post_image['main']!=NULL?"display:none;":NULL); ?>">
                        <?php echo $class_file->uploadifive_input("image_main"); ?>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if($post_template['config']['image']['gallery']&&!$new) { ?>
					<div class="form-group">
                        <label>Gallery Image</label>

                        <div class="image-container coltable col2 float sort" id="sortable">
                        <?php foreach($post_image['gallery'] as $image) { ?>
                        	<div class="col" data-imgid="<?php echo $image['post_id']; ?>">
                            <div class="image">
                                <div class="ctrl"><a href="<?php echo $zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>$image['post_id']]]); ?>" class="btn btn-success btn-xs"><i class="fas fa-pencil"></i></a> <a href="#" class="btn btn-danger btn-xs bt-image-delete" data-imgid="<?php echo $image['post_id']; ?>"><i class="fas fa-times"></i></a></div>
                                <img src="<?php echo $zulu->thumb("file/".$image['image'],"w=400&h=300&zc=1"); ?>" alt="gallery image" />
                            </div>
                            </div>
                        <?php } ?>
                        </div>

                        <?php echo $class_file->uploadifive_input("image_gallery"); ?>
                    </div>
                    <?php } elseif($post_template['config']['image']['gallery']&&$new) { ?>
                    <p class="caption no-margin color-grey"><i class="fas fa-info-circle"></i> You can upload gallery images once you save the post.</p>
                    <?php } ?>

			</div>
        </div>
        <?php } ?>

        <?php if(!$new&&$version_count>0) { ?>
    	<div class="panel panel-default">
        	<div class="panel-heading"><i class="fas fa-history"></i> History <?php echo $form_edit->icon_help("You can revert back to a previous version of this {$post_template['name']} post. Please note it does save all data, for example images cannot be restored from previous historic versions."); ?></div>
            <div class="panel-body">
            	<?php echo $zulu->template->body_version; ?>
            </div>
        </div>
        <?php } ?>
    </div>
    </form>
    <?php } ?>
</div>
<br />
