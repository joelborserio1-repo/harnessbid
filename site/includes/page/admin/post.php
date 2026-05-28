<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'post');
define(PAGE_name,'Posts');

//-- AUTHORISED?
$class_user->user_authorised_check();

//-- ADMIN MASTER SECTION
if(MASTER_section=='admin') { //admin section

	$zulu->template->head = "";
	$zulu->template->body = "";

	$zulu->template->js_code[] = "
      	$(document).ready(function() {
			$(\"a[rel='toggle-input']\").click(function() {
				$(\"input[type='checkbox'].action\").each(function() {
					if(!$(this).is(\":disabled\")) {
						$(this).prop(\"checked\", !$(this).prop(\"checked\"));
					}
				});
			});
		});
	";

	if(PAGE_action==NULL) {	//grid page

		//-- Post template to show
		if($_GET['type']) {
			$type = $_GET['type'];
			$post_template = $class_post->config->template[$type];
		}
		if($_GET['filter']['status']==NULL) {
			$_GET['filter']['status'] = 'published';
		}
		$zulu->config->title = $post_template['name'];
		$zulu->nav->breadcrumb[$post_template['name_plural']] = [];

		//Post include
		$include = $class_post->post_type_include($type);
		if($include!=NULL) {
			include($include);
		}

		//-- Page Action
		if($_POST && isset($_POST['execute'])) {
            switch ($_POST['execute']) {
                case 'delete':
                    foreach($_POST['action'] as $id=>$val) {
                        if(!$checkret = $class_post->post_delete($id,true)) {
                            $error_log[] = "Failed to delete post ID #".$id;
                        } else {

                        }
                    }
                    $zulu->notification_set("Selected posts were removed successfully.",1);
                    break;

                case 'mark_draft':
                    foreach($_POST['action'] as $id=>$val) {
                        if(!$checkret = $class_post->post_status_change($id,'draft')) {
                            $error_log[] = "Failed to move post ID #".$id;
                        } else {

                        }
                    }
                    $zulu->notification_set("Selected posts were made drafts.".(count($error_log)>0?"<br><br><b>Errors:</b><br>".implode("<br>",$error_log):NULL),1);
                    break;

                case 'mark_pub':
                case 'mark_publish':
                    foreach($_POST['action'] as $id=>$val) {
                        if(!$checkret = $class_post->post_status_change($id,'published')) {
                            $error_log[] = "Failed to move post ID #".$id;
                        } else {

                        }
                    }
                    $zulu->notification_set("Selected posts were published.".(count($error_log)>0?"<br><br><b>Errors:</b><br>".implode("<br>",$error_log):NULL),1);
                    break;

                case 'mark_hide':
                    foreach($_POST['action'] as $id=>$val) {
                        if(!$checkret = $class_post->set_status($id,false)) {
                            $error_log[] = "Failed to move post ID #".$id;
                        } else {

                        }
                    }
                    $zulu->notification_set("Selected posts were hidden.".(count($error_log)>0?"<br><br><b>Errors:</b><br>".implode("<br>",$error_log):NULL),1);
                    break;

                case 'mark_delete':
                    foreach($_POST['action'] as $id=>$val) {
                        if(!$checkret = $class_post->set_status($id,2)) {
                            $error_log[] = "Failed to delete post ID #".$id;
                        } else {

                        }
                    }
                    $zulu->notification_set("Selected posts were deleted.".(count($error_log)>0?"<br><br><b>Errors:</b><br>".implode("<br>",$error_log):NULL),1);
                    break;

                case 'sitemap_add':
                    foreach($_POST['action'] as $id=>$val) {
                        $zulu->meta_update('post', $id, 'sitemap_show', '1');
                    }
                    $zulu->notification_set("Selected posts will show on the sitemap.", 1);
                    break;

                case 'sitemap_remove':
                    foreach($_POST['action'] as $id=>$val) {
                        $zulu->meta_update('post', $id, 'sitemap_show', '0');
                    }
                    $zulu->notification_set("Selected posts will not show on the sitemap.", 1);
                    break;
            }

            header("Location: ".$_SERVER['HTTP_REFERER']);
            exit;
		}

		//-- Table
		function edit_bt($id,$delete=false) {
			global $zulu,$class_post,$post_template;
			$post_url = $class_post->post_url($id);

			$bt = "<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'edit')))."\" class=\"btn btn-primary btn-xs\"><i class=\"fas fa-edit\"></i> Edit</a>";
			if($post_template['config']['frontend'] && $post_template['config']['frontend_single']) {
				$bt .= " <a href=\"".$post_url."\" target=\"_blank\" class=\"btn btn-info btn-xs\"><i class=\"fas fa-laptop\"></i> Preview</a>
                <a href=\"#\" onClick=\"alert('".$post_url."');\" class=\"btn btn-default btn-xs\"><i class=\"fas fa-link\"></i> Link</a>";
			}
            if($post_template['config']['duplicate']) {
				$bt .= " <a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'duplicate')))."\" class=\"btn btn-warning btn-xs\"><i class=\"far fa-clone\"></i> Duplicate</a>";
			}
			if(!$delete) {
				$bt .= " <a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'delete')))."\" class=\"btn btn-default btn-xs\"><i class=\"fas fa-trash-alt\"></i></a>";
			} else {
				$bt .= " <a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'delete','Delete'=>1)))."\" class=\"btn btn-danger btn-xs confirm-delete\"><i class=\"fas fa-trash-alt\"></i></a>";
			}
            return $bt;
		}

		$zulu->config->select_all = true;
		$form_edit = new form;

		$table_column = $table_row = [];
		$table_column[] = array($form_edit->input_html("checkbox","selectall",1,array("class"=>['toggle-input'])),array('class'=>array('')));
		if($post_template['config']['image']['main']) {
            $table_column[] = array("Image",array('class'=>array('')));
        }
		$table_column[] = array("Title",array('class'=>array('')));
		$table_column[] = array("Status",array('class'=>array('')));
		$table_column[] = array("Updated",array('class'=>array('')));
		$table_column[] = array("Added",array('class'=>array('')));
		$table_column[] = array("Actions",array('class'=>array('right')));

		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		if($_GET['filter']) {
			foreach($_GET['filter'] as $fvar=>$fval) {
				$wSQL[$fvar] = $db->escape_string($fval);
				//$filter_array["filter[".$fvar."]"] = $fval;
			}
		}
		$wSQL['type'] = $_GET['type'];
		$data_row = $class_post->post_data($wSQL);
		$zulu->vars->task_count = count($data_row);
		foreach($data_row as $row) {
			$post_image = $class_post->post_image($row['id']);
			$post_status = $class_post->post_status($row['status']);
			$post_meta = $class_post->post_meta($row['id']);
			$sitemap_html ='';
			if($post_meta['sitemap_show']){
				$sitemap_html =' <span class="opt opt-success"><i class="fas fa-check"></i> Sitemap</span>';
			}
			if($post_image['main']!=NULL) {
				$post_image['main'] = $zulu->thumb("file/".$post_image['main'],"w=300&h=200&zc=1");
			}

			$table_row_content = [];
            $table_row_content[] = array($form_edit->input_html("checkbox","action[".$row['id']."]",1,array('checked'=>($_POST['action'][$row['id']]>0?true:false),'class'=>array('action'))),array('class'=>array('action-field')));
            if($post_template['config']['image']['main']) {
                $table_row_content[] = array("<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$row['id'],'Action'=>'edit')))."\">".($post_image['main']!=NULL?"<img class=\"product-image\" alt=\"post image\" src=\"".$post_image['main']."\" />":"<i class=\"far fa-file\"></i>")."</a>",['class'=>['text-center','col-image']]);
            }
            $table_row_content[] = array("<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$row['id'],'Action'=>'edit')))."\">".stripslashes($row['title'])."</a>");
            $table_row_content[] = array("<span class=\"opt opt-".$post_status['css']."\"><i class=\"fas fa-".$post_status['icon']."\"></i> ".$post_status['label']."</span>".$sitemap_html);
            $table_row_content[] = array($zulu->time_history($row['stat_update']));
            $table_row_content[] = array($zulu->time_history($row['stat_add']));
			$table_row_content[] = array(edit_bt($row['id'],($row['status']=='hidden'?true:false)),array('class'=>array('right','w250')));

            $table_row[] = array("content"=>$table_row_content);

		}

		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'basket'));
		$zulu->nav->title = PAGE_name;

        $bulk_actions = [0=>'None','delete'=>'Delete','mark_hide'=>'Mark as Trash','mark_draft'=>'Mark as Draft','mark_publish'=>'Mark as Published'];
        if($post_template['config']['sitemap']) {
            $bulk_actions['sitemap_add'] = "Show on Sitemap";
            $bulk_actions['sitemap_remove'] = "Hide from Sitemap";
        }

	}
	if(PAGE_action=='delete') { //delete
		if($_GET['Delete']>0) {
			$del = true;
		} else {
			$del = false;
		}
		if($class_post->post_delete(PAGE_id,$del)) {
			$zulu->notification_set("Post was ".($del?"deleted":"moved to trash").".",1);
			header("Location: ".$_SERVER['HTTP_REFERER']);
			exit;
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
	}
	if(PAGE_action=='edit') { //edit page

		$form_edit = new form;

		$zulu->template->css_file[] = "//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css";
		$zulu->template->js_file[] = "//code.jquery.com/ui/1.11.4/jquery-ui.js";
		$zulu->template->css_file[] = TPL_rel."css/post_builder.css";
		$zulu->template->js_file[] = TPL_rel."assets/post_builder.js";
		$zulu->template->js_code[] = "
      	$(document).ready(function(){
			$(\".input-date\").datepicker({ dateFormat: \"dd/mm/yy\" });
	  	});
		";

		if(PAGE_id<1) {
			$id = 0;
			$new = true;

			$type = $_GET['type'];
			$post_template = $class_post->config->template[$type];
			$zulu->nav->breadcrumb[$post_template['name']] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['type'=>$type]]));

			$zulu->nav->breadcrumb['New'] = array();
			$zulu->nav->title = "New ".$post_template['name'];
			$status_data = $class_post->post_status('draft');

			if($_SESSION['post']['temp_folder']==NULL) {
				$temp_folder = "post_".date("Ymd")."_".$zulu->serial(8);
				$_SESSION['post']['temp_folder'] = $temp_folder;
			} else {
				$temp_folder = $_SESSION['post']['temp_folder'];
				$post_temp = true;
			}

			if($type==NULL) {
				$zulu->notification_set("No post type was specified.",2);
				header("Location: ".$zulu->link_page('index'));
				exit;
			}

            if(!$_POST) {
                if($post_template['config']['sitemap']) {
                    $_POST['meta']['sitemap_show'] = '1';
                }
            }

		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));

			//Load root post data
			$post_data = $class_post->post_data(array('id'=>$id,'image'=>true));

			//Check version draft override
			$version_data = $class_post->version_data(0,['post_id'=>$id,'draft'=>true,'latest'=>true]);
			if($version_data['id']>0) {
				$v_post = unserialize($version_data['data_post']);
				$v_meta = unserialize($version_data['data_post_meta']);
				foreach($v_post as $key=>$val) {
					$post_data[$key] = $val;
				}
				foreach($v_meta as $key=>$val) {
					$post_data['_meta'][$key] = $val;
				}
			}

			$type = $post_data['type'];
			$post_template = $class_post->config->template[$type];
			$post_image = $class_post->post_image();

			if(!$_POST) {
				foreach($post_data as $key=>$val) {
					$_POST[$key] = $val;
				}
				foreach($post_data['_meta'] as $key=>$val) {
					$_POST['meta'][$key] = $val;
				}
			}
			//print_r($_POST);exit;
			$_POST['description'] = stripslashes($_POST['description']);
			$_POST['slug'] = rtrim($_POST['slug'],"/");
			$status_data = $class_post->post_status($_POST['status']);

			//Adjust breadcrumbs if parent exists
			if($_POST['parent_id']>0) {
				$parent_data = $class_post->post_data(['id'=>$_POST['parent_id']]);
				$zulu->nav->breadcrumb[$parent_data['title']] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>$parent_data['id']]]));
				$zulu->nav->breadcrumb['Edit'] = array();
				$zulu->nav->breadcrumb[stripslashes($post_data['title'])] = array();

			} else {
				$zulu->nav->breadcrumb[($post_template['name_plural']?$post_template['name_plural']:$post_template['name']."'s")] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['type'=>$type]]));
				$zulu->nav->breadcrumb['Edit'] = array();
				$zulu->nav->breadcrumb[stripslashes($post_data['title'])] = array();
			}
			$zulu->nav->title = "Edit ".$post_template['name'];

			// create post builder if doesn't have one and can have one
			if($post_template['config']['post_builder']) {
				$post_builder_row = $class_post->post_data(['type'=>'post_builder','parent_id'=>($id>0?$id:'0'),'status'=>'published','first'=>true]);
				if($post_builder_row['id'] <= 0) {
					$pb_result = $class_post->post_edit(0,['type'=>'post_builder','parent_id'=>($id>0?$id:'0'),'status'=>'published']);
					$post_builder_row['id'] = $pb_result['id'];
				}
			}
		}

		//Post include
		$include = $class_post->post_type_include($type);
		if($include!=NULL) {
			include($include);
		}

		//Post Config Custom Load Fn
		if(function_exists('postc_load')) {
			postc_load();
		}

		//Post Types
		$post_type_array[''] = "None";
		foreach($class_post->config->template as $tt_tpl=>$tt_row) {
			$post_type_array[$tt_tpl] = $tt_row['name'];
		}
		ksort($post_type_array);

		//Post Frames
		$fa = $class_post->frame_array();
		$frame_array[''] = "None";
		if(count($fa)>0) {
			$frame_array += $fa;
		}

		//Parent
		if($class_post->config->template[$type]['config']['parent']) {
			$post_list = $class_post->post_data(['type'=>$type,'status'=>'published']);
			$post_parent_array[0] = "No parent...";
			foreach($post_list as $prow) {
				if($prow['id']!=PAGE_id) {
					$post_parent_array[$prow['id']] = $prow['title'];
				}
			}
		}

		//Post Versions
		if(!$new && $class_post->config->template[$type]['config']['version']) {
			$version_list = $class_post->version_list(PAGE_id);

			$table_column[] = array("Date",array('class'=>array('')));
			$table_column[] = array("Actions",array('class'=>array('right')));

			$table_row[] = array(
					"header"	=>	 true,
					"class"		=>	"",
					"content"	=>	$table_column);
			foreach($version_list as $row) {
				if($post_template['config']['frontend']) {
					$bt[] = "<a href=\"".$class_post->post_url(PAGE_id,['version'=>$row['token']])."\" class=\"btn btn-info btn-xs\" target=\"_blank\"><i class=\"fas fa-laptop\"></i> Preview</a>";
				}
				$bt[] = "<a href=\"".$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>PAGE_id,'Do'=>'DeleteVersion','Version'=>$row['token']]])."\" class=\"btn btn-danger btn-xs confirm\" ><i class=\"fas fa-times\"></i></a>";

				$table_row[] = array("content" => array(
					array($zulu->time_history($row['stat_add']).($row['draft']>0?" <span class=\"opt opt-grey\"><i class=\"fas fa-pause\"></i> Draft</span>":NULL)),
					array(($row['draft']<=0?"<a href=\"".$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>PAGE_id,'Do'=>'Restore','Version'=>$row['token']]])."\" class=\"btn btn-warning btn-xs confirm\"><i class=\"fas fa-sync-alt\"></i> Restore</a> ".implode(" ",$bt):"<i class=\"color-grey\">Currently editing</i>"),array('class'=>array('right','w200')))
				));
				unset($bt);
			}

			$zulu->template->body_version = $zulu->table_render($table_row,0,array('class'=>'basket','js_table'=>false,'data_table'=>false));
			$version_count = count($version_list);
		}

		//Frame Include
		$include_path = $zulu->path_clean(DOC_root.'/'.$class_user->authorised->file_web_path);
		$include = $include_path."frames/".$post_data['_meta']['frame'];
		if(file_exists($include)&&$post_data['_meta']['frame']!=NULL) {
			$class_post->vars->frame_url = $zulu->link_page(PAGE_file,['query'=>['Action'=>'edit_frame','id'=>PAGE_id]]);
		}

		//Load Image JS & Assets
		if($class_post->config->template[$type]['config']['image']) {
			if($new) {
				$main_path = 'temp/'.$temp_folder.'/';
			} else {
				$main_path = 'post/'.$post_data['token'].'/';
			}
			@mkdir($class_file->file_root."/../".$main_path);
			if($class_post->config->template[$type]['config']['image']['main']) {
				$class_file->uploadifive_new("image_main",['preview'=>true,'post'=>['action'=>'post_main_image','post_id'=>PAGE_id,'file_name'=>'main','path'=>$main_path],'setting'=>['multi'=>false,'queueSizeLimit'=>1],'event'=>['complete'=>'']]);
			}
			if($class_post->config->template[$type]['config']['image']['gallery']) {
				$class_file->uploadifive_new("image_gallery",['preview'=>true,'post'=>['action'=>'post_gallery_image','post_id'=>PAGE_id,'path'=>$main_path],'setting'=>['multi'=>true,'queueSizeLimit'=>8]]);
			}
		}

		//-- Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;

			if($form_edit->valid) {

				//Database fields
				$data = [];
				$data['type'] = $type;
				$data['title'] = $db->escape_string($_POST['title']);
				$data['content'] = $db->escape_string($_POST['content']);
				$data['author_id'] = $db->escape_string($_POST['author_id']);
				if($class_post->config->template[$type]['config']['parent']) {
					$data['parent_id'] = $_POST['parent_id'];
				}
				$data['sort'] = $_POST['sort'];
				$data['slug'] = $class_post->slug_check($_POST['live_url'],['type'=>$post_template['slug'],'prefix'=>$post_template['config']['slug_prefix'],'id'=>PAGE_id]);

				//Other Overrides
				if($post_template['config']['slug_lock']&&!$new) {
					$data['slug_ovr'] = true;
					unset($data['slug']);
				}

				//Meta fields (for base post features only; if other meta use post config template)
				if(filter_var($_POST['meta']['redirect_link'],FILTER_VALIDATE_EMAIL)) {
					$_POST['meta']['redirect_link'] = "mailto:".$_POST['meta']['redirect_link'];
				}
				if($_POST['meta']['redirect_link'] != NULL &&
                   substr($_POST['meta']['redirect_link'], 0, 1) != '/' &&
                   substr($_POST['meta']['redirect_link'], 0, 1) != '#' &&
                   substr($_POST['meta']['redirect_link'], 0, 4) != 'http' &&
                   substr($_POST['meta']['redirect_link'], 0, 7) != 'mailto:' &&
                   substr($_POST['meta']['redirect_link'], 0, 4) != 'tel:') {
					$_POST['meta']['redirect_link'] = "http://".ltrim($_POST['meta']['redirect_link'],'/');
				}
				$_POST['meta']['sitemap_show'] = ($_POST['meta']['sitemap_show'] == '1'?'1':'0');
				$meta = $_POST['meta'];

				//Extra fields
				if(count($_POST['extra'])>0) {
					foreach($_POST['extra'] as $field=>$val) {
						$data[$field] = $val;
					}
				}

				//Post Config Custom Save Fn
				if(function_exists('postc_save')) {
					if(!postc_save()) {
						$form_edit->valid = false;
						$CONFIG_error = true;
					}
				}

				if($form_edit->valid) {
					if(isset($_POST['submit_preview'])) {
						$v_post['_root'] = $class_post->post_data(['id'=>PAGE_id]);
						foreach($v_post['_root'] as $key=>$val) {
							if(is_numeric($key)) {
								unset($v_post['_root'][$key]);
							}
							if(is_array($val)) {
								unset($v_post['_root'][$key]);
							}
						}
						foreach($data as $key=>$val) {
							$v_post['_root'][$key] = $val;
						}
						$v_post['id'] = PAGE_id;
						$v_post['_meta'] = $meta;
						$v_post['_root']['content'] = str_replace(['\r','\n',chr(10),chr(13)],'',$v_post['_root']['content']);

						$class_post->version_new($v_post,['draft'=>true]);
						$data['success'] = true;
						$data['id'] = PAGE_id;
						$preview_url = $class_post->vars->token;
					} else {
						$data = $class_post->post_edit($id,$data,$meta);
					}
				}

				if($data['success']) {
					//-- Adjust post status
					if(isset($_POST['submit'])&&$post_data['status']!='published') {
						$class_post->post_status_change($data['id'],'published');
					}
					if($post_temp) {
						$file_content = glob($class_file->file_root."../".$main_path."*");
						$main_path = 'post/'.$class_post->vars->post_new_token.'/';
						@mkdir($class_file->file_root."../".$main_path);
						foreach($file_content as $file) {
							$basename = basename($file);
							@copy($file,$class_file->file_root."../".$main_path.$basename);
							@unlink($file);
							if(strstr($basename,"main")&&count($file_content)==1) {
								//main image
								$zulu->meta_update('post',$data['id'],'image_main',$basename);
							}
						}
					}

                    $version_parent_id = $class_post->vars->version_id;
                    foreach($_POST['pb_post'] as $pb_id=>$pb_data) {
                        if($pb_data['remove']) {
                            $class_post->post_edit($pb_id,['status'=>'hidden','version_parent_id'=>$version_parent_id]);
                        } else {
                            $pb_meta = $pb_data['meta'];
                            unset($pb_data['meta'],$pb_data['remove']);
                            $pb_data['version_parent_id'] = $version_parent_id;
                            $pb_data['status'] = 'published';
                            $class_post->post_edit($pb_id,$pb_data,$pb_meta);
                        }
                    }

					//-- Parent
					$post_data = $class_post->post_data(['id'=>$data['id']]);
					$parent = $post_data['parent_id'];
					$parent_data = $class_post->post_data(['id'=>$parent]);
					$parent_type = $class_post->config->template[$parent_data['type']];

					//-- Return
					$zulu->notification_set("Post ".(!$new?"updated":"created")." successfully.".($preview_url!=NULL?"<br><br><b>Preview the post here:</b> <a href=\"".$class_post->post_url(PAGE_id)."?version=".$preview_url."\" target=\"_blank\">".$class_post->post_url(PAGE_id)."?version=".$preview_url."</a><br><br>":NULL)." <a href=\"".$zulu->link_page(PAGE_file,['query'=>['type'=>$type]])."\">Return to all ".strtolower(($post_template['name_plural']?$post_template['name_plural']:$post_template['name']."'s"))." here</a>".($parent>0?" <a href=\"".$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>$parent_data['id']]])."\">Return to the parent ".strtolower($parent_type['name'])." '".$parent_data['title']."' here</a>":NULL),1);

					if($post_template['config']['action_save']=='parent'&&$parent>0) {
						header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>$parent]]));
					} elseif($post_template['config']['action_save']=='index') {
						header("Location: ".$zulu->link_page(PAGE_file,['query'=>['type'=>$type]]));
					} else {
						header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>$data['id']]]));
					}
					exit;
				} else {
					if(!$CONFIG_error) {
						$zulu->notification_set("A database error occurred.",2);
					}
				}
			}
		}

		//-- JS
		$zulu->template->jquery[] = "

		    $(\"#sortable\").sortable({
			update: function(event, ui) {
				var srt = [];
				$(\"#sortable\").children(\".col\").each(function( index ) {
					srt.push($(this).data('imgid'));
				});
				$.get(\"".$zulu->link_page(PAGE_file,['query'=>['Action'=>PAGE_action,'Do'=>'ImageSort','id'=>PAGE_id]])."&Array=\" + srt,function(data) {
					console.log(data);
				});
			}
			});
    		$(\"#sortable\").disableSelection();

			$(\".bt-image-delete\").click(function() {
				var type = $(this).data(\"type\");
				var imgid = $(this).data(\"imgid\");
				$.get(\"".$zulu->link_page(PAGE_file,['query'=>['Action'=>PAGE_action,'Do'=>'ClearImage','id'=>PAGE_id]])."&Type=\" + type + \"&ImgID=\" + imgid,function(data) {
					console.log(data);
				});

				$(\".type-\" + type).show(500);
				$(this).parents(\".image\").remove();
				return false;
			});

			".($post_template['config']['post_builder']&!$new?"
			var post_builder_id = ".$post_builder_row['id'].";
			".($_POST['meta']['post_builder']?"
			$('#pb-show').hide();
			$('#default-content-section').hide();
			":"
			$('#pb-hide').hide();
			$('#pb-section').hide();
			"):NULL)."
		";

		//-- Other Commands
		if($_GET['Do']=='Restore') { //-- Restore
			if($class_post->version_restore($_GET['Version'])) {
				$zulu->notification_set("Historic version restored.",1);
			} else {
				$zulu->notification_set("Error restoring version.",2);
			}
			header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>PAGE_id]]));
			exit;
		}

		if($_GET['Do']=='DeleteVersion') { //-- Delete
			if($class_post->version_delete(['token'=>$_GET['Version'],'post'=>PAGE_id])) {
				$zulu->notification_set("Historic copy removed permanently.",1);
			} else {
				$zulu->notification_set("Error removing copy.",2);
			}
			header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>PAGE_id]]));
			exit;
		}

		if($_GET['Do']=='ClearImage') {
			if($_GET['ImgID']<=0) {
				$class_post->post_image_delete(PAGE_id);
				echo '-d main';
			} else {
				$class_post->post_delete($_GET['ImgID']);
				echo '-d gallery';
			}
		}

		if($_GET['Do']=='ImageSort'||$_GET['Do']=='PostSort') {
			$array = explode(",",$_GET['Array']);
			$i = 0;
			foreach($array as $item_id) {
				$class_post->post_edit($item_id,['sort'=>$i]);
				$i++;
			}
		}

		if($_GET['Do']=='ImageSort') {
			$array = explode(",",$_GET['Array']);
			$i = 0;
			foreach($array as $item_id) {
				$class_post->post_edit($item_id,['sort'=>$i]);
				$i++;
			}
		}
	}
    if(PAGE_action=='duplicate') {
        $result = $class_post->post_duplicate(PAGE_id);
        if($result['success']) {
            $zulu->notification_set('Duplication successful.',1);
            header("Location: ".$zulu->link_page(PAGE_file,['query'=>['id'=>$result['id'],'Action'=>'edit']]));
            exit;
        } else {
            $zulu->notification_set($result['msg'],2);
            header("Location: ".$_SERVER['HTTP_REFERER']);
            exit;
        }
    }
	if(PAGE_action=='edit_frame') {
		$post_data = $class_post->post_data(['id'=>PAGE_id]);
		$include_path = $zulu->path_clean(DOC_root.'/'.$class_user->authorised->file_web_path);
		$include = $include_path."frames/".$post_data['_meta']['frame'];
		if(!file_exists($include)) {
			$zulu->notification_set("This post does not use a frame.");
			header("Location: ".$zulu->link_page(PAGE_file,['query'=>['id'=>PAGE_id,'Action'=>'edit']]));
			exit;
		}

		ob_start();
		require($include);
		$html = ob_get_clean();

		$setting = $class_setting->setting_data();
		$wb_tpl = ($setting['ws_template']!=NULL?$setting['ws_template']:"default");

		$zulu->template->file_css[] = FE_rel."template/".$wb_tpl."/style.css";
		$zulu->template->file_css[] = FE_rel."template/".$wb_tpl."/style.connect.css";
		$zulu->template->file_css[] = MAIN_rel."bower_components/font-awesome/css/font-awesome.min.css";

		$tpl_profile_path = $class_user->authorised->file_web_path;
		$tpl_profile_path_abs = DOC_root.'/'.$tpl_profile_path;

		if(file_exists($tpl_profile_path_abs."style.css")) {
			$zulu->template->file_css[] = $zulu->path_clean('/'.$tpl_profile_path."style.css");
		}
		foreach($zulu->template->file_css as $file) {
			$css_file .= "<link href=\"{$file}\" rel=\"stylesheet\" media=\"screen\" type=\"text/css\" />";
		}

		echo "
			<!DOCTYPE html>
			<html>
				<head>
					{$css_file}
					<style type=\"text/css\">
						body,
						html {
							background:none !important;
						}
						.frame-container {
							max-width:1200px;
							margin:0 auto;
							padding:10px;
							box-sizing:border-box;
						}
					</style>
				</head>
				<body>
					<div class=\"frame-container\">
					".$html."
					</div>
				</body>
			</html>

		";
		exit;
	}
}
