<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'website');
define(PAGE_name,'Website Settings');
$zulu->nav->breadcrumb[PAGE_name] = array("link"=>$zulu->link_page(PAGE_file));

//-- AUTHORISED?
$class_user->user_authorised_check();

//-- ADMIN MASTER SECTION
if(MASTER_section=='admin') { //admin section

	$zulu->template->head = "";
	$zulu->template->body = "";

	$class_website->setup();

	if(PAGE_action==NULL) {
		header("Location: ".$zulu->link_page('website',['query'=>['Action'=>'site']]));
		exit;
	}
	if(PAGE_action=='get_started') {

		$zulu->template->sidebar_hide = true;
		$form_edit = new form;
		$zulu->nav->breadcrumb = [];

		$logo_path = MAIN_path.$class_website->user_folder.'images/logo.png';
		$logo_rel = MAIN_rel.$class_website->user_folder.'images/logo.png';

		$class_file->uploadifive_new("logo_main",['preview'=>true,'post'=>['path_custom'=>$class_website->image_fold,'file_name'=>'logo','file_ext'=>'png','action'=>'ws_logo'],'setting'=>['multi'=>false,'queueSizeLimit'=>1],'event'=>['complete'=>'']]);

		//-- Do DELETE LOGO
		if($_GET['Method']=='DeleteLogo') {
			@unlink($logo_path);

			header("Location: ".$zulu->link_page(PAGE_file,['self'=>true,'filter'=>['Method']]));
			exit;
		}

		$template_all_data = $class_website->template_data(['status'=>1,'user_id'=>0]);
		$template_data = $class_website->template_data();

		if(!$_POST) {
			$_POST['company_name'] = $class_setting->data['ws_site_name'];
			$_POST['company_email'] = $class_setting->data['ws_contact_email'];
			$_POST['company_phone'] = $class_setting->data['ws_contact_phone'];
		}

		$table_column = [
			array("Page Name",array('class'=>array(''))),
			array("Type of Page"),
			array("Show on Menu?",array('class'=>array(''))),
			array("Actions",array('class'=>array(''))),
			//array("Link Type",array('class'=>array(''))),
			//array("Link Item",array('class'=>array(''))),
			//array("Link Target",array('class'=>array(''))),
			//array("Actions",array('class'=>array('right')))
		];
		$table_row[] = ["header" => true, "class" => "", "content" => $table_column];

		$button_data = unserialize($class_setting->data['ws_page_config']);
		$count = count($button_data);
		if($count<=0) {
			$count = 1;
		}

		$option_array = [''=>'Information Page (HTML)','post_faq'=>'FAQ','post_testimonials'=>'Testimonials','post_gallery'=>'Gallery','post_news'=>'News'];
		if($class_website->config->program=='ZULUSHP') {
			$option_array['shop'] = 'Product Catalogue';
		}
		for($i=1; $i<=$count; $i++) {
			$id_i = $i-1;
			$table_row[] = array("content" => [
				array($form_edit->input_html('input','menu_option['.$id_i.'][name]',stripslashes($button_data[$id_i]['name']),['class'=>['input-title']])),
				array($form_edit->input_html('select','menu_option['.$id_i.'][type]',stripslashes($button_data[$id_i]['type']),['class'=>['input-theme'],'option'=>$option_array])),
				array($form_edit->input_html('checkbox','menu_option['.$id_i.'][menu]',1,['checked'=>($button_data[$id_i]['menu']>0||!isset($button_data[$id_i]['menu'])?true:false),'class'=>['input-type']])),
				array("<a href=\"#\" class=\"btn btn-danger btn-xs remove-row\" title='Remove row'><i class=\"fas fa-times\"></i></a>",array('class'=>array('right','w80')))
			]);
		}

		foreach($template_data as $tpl) {
			$tpl_slide[] = [
				'title'	=>	$tpl['name'],
				'image'	=>	$zulu->path_clean((trim($tpl['image'])!=NULL?MAIN_rel.$class_file->file_root_rel.'../web/template/'.$tpl['image']:MAIN_rel."template/default/images/placeholder.png")),
				'master'	=>	($tpl['user_id']>0?false:true),
				'select'	=>	($tpl['id']==$class_setting->data['ws_template_id']?true:false),
			];
		}
		foreach($template_all_data as $tpl) {
			$tpl_slide[] = [
				'token'	=>	$tpl['token'],
				'title'	=>	stripslashes($tpl['name']),
				'image'	=>	$zulu->path_clean((trim($tpl['image'])!=NULL?$class_file->file_root_rel.'../web/template/'.$tpl['image']:"template/default/images/placeholder.png")),
				'master'	=>	($tpl['user_id']>0?false:true),
				'select'	=>	($tpl['id']==$class_setting->data['ws_template_id']?true:false),
			];
		}
		$i = 1;
		foreach($tpl_slide as $ts) {
			$unid = $zulu->serial(7);
			$tpl_slide_html[] = "
				<li>
					<img src=\"".$zulu->thumb($ts['image'],'w=500&h=400&far=1&bg=ffffff&q=100')."\" class=\"responsive\" alt=\"image of theme ".$ts['title']."\" />
					<p class=\"h4\">".$ts['title']."</p>
					<p class=\"form\"><label for='tpl-select-".$unid."'>".$form_edit->input_html('radio','ws_template_id',$ts['token'],['ovr_check_value'=>true,'class'=>[($ts['select']?'checked':'null')],'id'=>'tpl-select-'.$unid])." Select</p>
				</li>
			";
			if($ts['select']) {
				$current_slide_id = $i-1;
			}
			$i++;
		}

		if(count($tpl_slide)>0) {
			$zulu->template->body->template_slides = "<ul class='slide-template gs-slider' id='slider'>".implode('',$tpl_slide_html)."</ul>";
			$zulu->template->js_code[] = "
			function init_slider() {
				var this_slide =$(\"#slider\").lightSlider({
					item: 2,
					autoWidth: false,
					slideMove: 1, // slidemove will be 1 if loop is true
					slideMargin: 10,

					addClass: '',
					mode: \"slide\",
					useCSS: true,
					cssEasing: 'ease', //'cubic-bezier(0.25, 0, 0.25, 1)',//
					easing: 'linear', //'for jquery animation',////

					speed: 500, //ms'
					auto: false,
					loop: false,
					slideEndAnimation: true,
					pause: 4000,

					enableTouch:false,
					enableDrag:false,

					keyPress: true,
					controls: true,

					responsive : [
					{
					breakpoint:800,
					settings: {
					item:1,
					slideMove:1,
					slideMargin:6,
					}
					},
					{
					breakpoint:480,
					settings: {
					item:1,
					slideMove:1
					}
					}
					],

					onSliderLoad: function() {
						".($current_slide_id>0?"this_slide.goToSlide(".$current_slide_id.")":NULL)."
					},

				});
			}
			";
			$zulu->template->css_file[] = TPL_rel."assets/light-slider/src/css/lightslider.css";
			$zulu->template->js_file[] = TPL_rel."assets/light-slider/src/js/lightslider.js";
		} else {
			$zulu->template->body->template_slides = "<p class='opt opt-grey'><i class='fas fa-times'></i> You can change your template later.</p>";
		}

		//Form Submit
		if($_POST['action']=='step') {
			$form_edit->valid = true;
			$step_no = $db->escape_string($_POST['step']);

			//-- Step 1 ACTIONS
			if($step_no==1) {
				if($form_edit->valid) {
					$class_setting->setting_edit('ws_site_name',$db->escape_string($_POST['company_name']));
					$class_setting->setting_edit('ws_contact_phone',$db->escape_string($_POST['company_phone']));
					$class_setting->setting_edit('ws_contact_email',$db->escape_string($_POST['company_email']));
				}
			}
			//-- Step 2 ACTIONS
			if($step_no==2) {
				if($form_edit->valid) {
					$class_website->template_set($db->escape_string($_POST['ws_template_id']));
				}
			}
			//-- Step 3 PAGES
			if($step_no==3) {

				//---- Check pages
				foreach($_POST['menu_option'] as $mo_index=>$mo) {
					$post_check = $class_post->post_data(['title'=>addslashes($mo['name']),'field'=>['id']]);
					if($post_check['id']>0) {
						$_POST['menu_option'][$mo_index]['post_id'] = $post_check['id'];
						continue;
					}
					if(strstr($mo['type'],'post_')) {
						$type_index = str_replace("post_","",$mo['type']);
					} elseif($mo['type']=='shop') {
						continue;
					}
					$result_page = $class_post->post_edit(0,[
						'title'		=>	addslashes($mo['name']),
						'content'	=>	"Put your page content here - or use the 'content builder' for easy page creation!",
						'status'	=>	'published',
						'type'		=>	'page',
					],[
						'post_index'	=>	$type_index,
						'post_builder'	=>	1,
					]);
					$dump = $result_page['id'];
					$_POST['menu_option'][$mo_index]['post_id'] = $result_page['id'];
                    $class_post->post_edit(0,['type'=>'post_builder','parent_id'=>$result_page['id'],'status'=>'published']);
				}
				$class_setting->setting_edit('ws_page_config',serialize($_POST['menu_option']));

				//---- Check navigation
				$default_menu_id = $class_setting->data['ws_menu_default'];
				if($default_menu_id<=0||strlen(trim($default_menu_id))==0) {
					$result_menu = $class_post->post_edit(0,[
						'title'		=>	"Main Menu",
						'status'	=>	'published',
						'type'		=>	'menu',
					]);
					$default_menu_id = $result_menu['id'];
					$class_setting->setting_edit('ws_menu_default',$default_menu_id);
				}
				foreach($_POST['menu_option'] as $mo_index=>$mo) {
					if($mo['menu']>0) {
						$obj = ($mo['type']=='shop'?'default':'page');
						$obj_id = ($mo['type']=='shop'?0:$mo['post_id']);
						$result_menu_item = $class_post->post_edit(0,[
							'title'		=>	'',
							'parent_id'	=>	$default_menu_id,
							'status'	=>	'published',
							'type'		=>	'menu_item',
						],[
							'object'	=>	$obj,
							'object_id'	=>	$obj_id,
						]);
					}
				}
				//$zulu->meta_update("user",$class_user->authorised->id,"welcome_dismiss",1);
			}

			$ret_array = [
				'success'	=>	true,
				'step'	=> $step_no,
				'dump'	=>	$dump,
				//'post_data'	=>	serialize($_POST),
			];
			echo json_encode($ret_array);
			exit;
		}

		$zulu->template->jquery[] = "
		var step_num = $('.input-step').val();
		var step_max = 4;
		var slider_init = false;
		var first = true;

		$('.checked').attr('checked','checked');
		$('.checked').attr('checked',true);
		$('.checked').prop('checked',true);

		$(document).on('click','.btn-continue',function() {
			//--post that form
			step_num = (step_num*1)+1;
			if(save_step()) {
				set_step();
			}
			return false;
		});
		$(document).on('click','.btn-back',function() {
			//--post that form
			if(step_num>1&&step_num<step_max) {
				step_num = (step_num*1)-1;
				set_step();
			}
			return false;
		});

		set_step();
		function set_step() {
			if(first) {
				var trans = 0;
				first = false;
			} else {
				var trans = 500;
			}
			$('.panel-heading .title').hide();
			$('.body-block').hide(trans);
			$('.step-list > li').removeClass('hvr');
			$('.st-' + step_num).addClass('hvr');
			$('.st-' + step_num + '-title').show();
			$('.st-' + step_num + '-body').slideDown(trans,function() {
				if(step_num==2&&!slider_init) {
					slider_init = true;
					setTimeout(init_slider(),1000);
				}
			});
			if(step_num==4) {
				$('.fg-submit').hide(400);
			}
			$('.input-step').val(step_num);
			return false;
		}

		function save_step() {
			$.ajax({
			   type: 'POST',
			   url: '".$zulu->link_page(PAGE_file,['self'=>true,'filter'=>['Method']])."',
			   data: $('#gs_form').serialize(),
			   beforeSend: function() {
			   		$('.btn-continue').prop('disabled',true);
			   },
			   error: function (jqXHR, exception) {
					return false;
				},
			   success: function(data) {
					data = JSON.parse(data);
					console.log(data);
			   		$('.btn-continue').prop('disabled',false);
			   },
			 });
			return true;
		}

		$(\"#sortable-rows\").sortable();
		$(\"body\").on('click','#row-add',function() {
			var new_row = $('#module-options tbody tr:last').clone();
			var this_html = new_row.html();
			var current_row_count = $('#module-options tbody').children().length-1;
			var new_num = current_row_count+1;
			new_row.html(this_html.replace(new RegExp(current_row_count, 'g'),new_num));
			new_row.appendTo('#module-options').find('.input-type').trigger('change');
			selected_item = true;
			return false;
		});
		$('body').on('click','.clear-row',function() {
			var trow = $(this).parent().parent();
			$(trow).find('input').val('');
			return false;
		});
		$('body').on('click','.remove-row',function() {
			var current_row_count = $('#module-options tbody').children().length;
			if(current_row_count>1) {
				var trow = $(this).parent().parent();
				$(trow).remove();
			}
			return false;
		});
		";
	}
	if(PAGE_action=='site') { //site edit page

        if(isset($_GET['Method'])) {
            if($_GET['Method'] == 'DeleteLogo') {
                @unlink($class_website->logo_path);

                $class_setting->setting_delete('ws_logo_color');
                $class_setting->setting_delete('ws_theme_master_colour');
                $class_setting->setting_delete('ws_theme_master_shade');
                $class_setting->setting_delete('ws_theme_secondary_colour');
                $class_setting->setting_delete('ws_theme_secondary_shade');
                $class_setting->setting_delete('ws_theme_third_colour');
                $class_setting->setting_delete('ws_theme_fourth_colour');

                $zulu->notification_set("Logo removed successfully. Note: The site will revert to using the default logo.",1);

            } elseif($_GET['Method'] == 'DeleteFavicon') {
                $file_list = glob($class_website->favicon_fold_path."*");
                foreach($file_list as $file) {
                    @unlink($file);
                    $class_setting->setting_edit('ws_tpl_script_favicon','');
                }

                $zulu->notification_set("Favicon removed successfully.",1);

            }

            header("Location: ".$zulu->link_page(PAGE_file, ['self'=>true, 'filter'=>['Method']]));
            exit;
        }

        $form_edit = new form;
		$data_row = $class_setting->setting_data();
		$STEP_count = 8;

		$zulu->nav->title = PAGE_name;

		$user_data = $class_user->user_data(array('id'=>$class_user->authorised->id));
		$user_meta = $class_user->user_meta($class_user->authorised->id);
		$setting = $class_setting->setting_data();
		$selected_tab = ($_GET['Tab']?$_GET['Tab']:($class_cache->load('website_site_tab')!=NULL?$class_cache->load('website_site_tab'):"general"));

		if(!isset($_GET['Tab'])) {
			$_GET['Tab'] = $selected_tab;
		}
		$class_cache->save('website_site_tab',$selected_tab);
		$tab_list = [
			"general"        =>	"General",
			"shop"           =>	"Shopping",
			"social"         => "Social Media",
            "script"         => "Scripts",
			"miscellaneous"  =>	"Miscellaneous",
			"maintenance"    =>	"Maintenance",
		];
		if($class_website->config->program!='ZULUSHP'&&MASTER_mode=='web') {
			unset($tab_list['shop']);
		}

		if(!$_POST) {
			foreach($data_row as $key=>$val) {
				$_POST[$key] = $val;
			}

			//-- Load default settings
			if($selected_tab == 'general') {
				if(trim($_POST['ws_site_name'])==NULL) {
					$_POST['ws_site_name'] = $setting['company'];
				}

			} elseif($selected_tab == 'shop') {
                if($_POST['ws_shop_related_products'] != NULL) {
                    $_POST['ws_shop_related_products'] = explode(',',$_POST['ws_shop_related_products']);
                }
                if(!isset($_POST['ws_shop_product_review_enable'])) {
                    $_POST['ws_shop_product_review_enable'] = 0;
                    $_POST['ws_shop_product_review_approval'] = 1;
                    $_POST['ws_shop_product_review_notify'] = 1;
                }

			} elseif($selected_tab == 'miscellaneous') {
                if(!isset($_POST['ws_module_google_captcha_api_score'])) {
                    $_POST['ws_module_google_captcha_api_score'] = $class_website->recaptcha_score_default;
                }

            }
		}

		//Tab: maintenance
		if($selected_tab=='maintenance') {
			$favicon_path = $class_website->favicon_path;
			$favicon_rel = $class_website->favicon_rel;

			//-- Flush Site Contents
			if($_GET['Do']=='clear_system') {
				$class_setting->setting_edit('ws_status',0);

				$db->query("DELETE FROM config WHERE field LIKE 'ws_addr_%' AND user_id = '".$class_user->authorised->id."'");
				$db->query("DELETE FROM config WHERE field LIKE 'ws_meta_%' AND user_id = '".$class_user->authorised->id."'");
				$db->query("DELETE FROM config WHERE field LIKE 'ws_contact_%' AND user_id = '".$class_user->authorised->id."'");
				$db->query("DELETE FROM config WHERE field LIKE 'ws_module_%' AND user_id = '".$class_user->authorised->id."'");
				$db->query("DELETE FROM config WHERE field LIKE 'ws_tpl_script_%' AND user_id = '".$class_user->authorised->id."'");
				$db->query("DELETE FROM config WHERE field LIKE 'ws_tpl_script_%' AND user_id = '".$class_user->authorised->id."'");

				$zulu->notification_set("Clear system done.",1);
				header("Location: ".$zulu->link_page(PAGE_file,['self'=>true]));
				exit;
				//--done
			}

			//-- Post: Menu generator
			if($_POST['action']=='generate_page') {

				$menu = [];
				$i = $last_parent_id = 0;

				if(trim($_POST['post_list'])==NULL) {
					$zulu->notification_set("No pages were created, no pages were listed in the request box.",2);
					header("Location: ".$zulu->link_page(PAGE_file,['self'=>true]));
					exit;
				}

				$content_box = explode(PHP_EOL,$_POST['post_list']);
				foreach($content_box as $this_page) {

					$page_name = $page_conf = [];
					$sp = explode(':',$this_page);
					$page_name = trim($sp[0]);
					if(isset($sp[1])) $page_conf = trim($sp[1]);

					//-- Check Duplicate
					$duplicate = $class_post->post_data(['title'=>$page_name,'type'=>'page','status'=>'published','field'=>['id']]);

					if($duplicate['id']>0) {
						//--skip
						$page_post_id = $duplicate['id'];
					} else {
						$result = $class_post->post_edit(0,[
							'status'	=>	'published',
							'title'		=>	$page_name,
							'type'		=>	'page',
							'author_id'	=>	$class_user->authorised->child_id,
							'sort'		=>	$i,
						]);
						$page_post_id = $result['id'];
					}

					if($page_conf!='SKIP') {
						if($page_conf=='SUB') {
							$menu[$last_parent_id]['child'][] = ['title'=>$page_name,'id'=>$page_post_id];
						} else {
							$last_parent_id = $page_post_id;
							$menu[$page_post_id] = ['title'=>$page_name,'id'=>$page_post_id];
						}
					}
					$i++;
				}
				//-- Finish loop initial

				//-- Generate Menu
				if(count($menu)) {
					$result = $class_post->post_edit(0,[
						'status'	=>	'published',
						'title'		=>	'Main Menu (Generated)',
						'type'		=>	'menu',
						'author_id'	=>	$class_user->authorised->child_id,
					]);
					$menu_post_id = $result['id'];

					$i = 0;
					foreach($menu as $menu_item) {
						$result = $class_post->post_edit(0,[
							'parent_id'	=>	$menu_post_id,
							'status'	=>	'published',
							'type'		=>	'menu_item',
							'author_id'	=>	$class_user->authorised->child_id,
							'sort'		=>	$i,
							'title'		=>	'',
						],[
							'object'	=>	'page',
							'object_id'	=>	$menu_item['id'],
						]);
						if(count($menu_item['child'])) {
							foreach($menu_item['child'] as $menu_item) {
								$result_sub = $class_post->post_edit(0,[
									'parent_id'	=>	$result['id'],
									'status'	=>	'published',
									'type'		=>	'menu_item',
									'author_id'	=>	$class_user->authorised->child_id,
									'sort'		=>	$i,
									'title'		=>	'',
								],[
									'object'	=>	'page',
									'object_id'	=>	$menu_item['id'],
								]);
							}
						}
						$i++;
					}
				}

				$link_pg = $zulu->link_page('post',['query'=>['type'=>'page']]);
				$link_mn = $zulu->link_page('post',['query'=>['Action'=>'edit','id'=>$menu_post_id]]);
				if(!isset($page_post_id)) {
					$zulu->notification_set("No pages were created, no pages were listed in the request box.",2);
				} elseif(count($menu)) {
					$zulu->notification_set("Page and menu post(s) were created. <a target=\"_blank\" href=\"".$link_pg."\">View Pages</a> <a  target=\"_blank\" href=\"".$link_mn."\">View Menu</a>",1);
				} else {
					$zulu->notification_set("Page post(s) were created. <a href=\"".$link_pg."\">View Pages</a>",1);
				}
				$zulu->submit_page_redir($zulu->link_page(PAGE_file,['self'=>true]),'setting_generate_page');
				exit;
			}
		}

		//Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;

			if($form_edit->valid) {

                if($selected_tab=='shop') {
                    if($_POST['ws_shop_chk_dis_to']>0) {
                        $_POST['ws_shop_chk_dis_to'] = $zulu->dateEncode($_POST['ws_shop_chk_dis_to']);
                    }
                    if($_POST['ws_shop_chk_dis_from']>0) {
                        $_POST['ws_shop_chk_dis_from'] = $zulu->dateEncode($_POST['ws_shop_chk_dis_from']);
                    }
                    $_POST['setting']['ws_shop_related_products'] = implode(',',$_POST['setting']['ws_shop_related_products']);

                } elseif($selected_tab=='general') {
                    $_POST['setting']['ws_site_force_https'] = ($_POST['setting']['ws_site_force_https']?'1':'0');

                } elseif($selected_tab == 'social') {
                    foreach($class_website->social_options as $key=>$val) {
                        if(!isset($_POST['setting']['ws_social_link_'.$key.'_enable'])) {
                            $_POST['setting']['ws_social_link_'.$key.'_enable'] = 0;
                        }
                    }

                } elseif($selected_tab == 'script') {
                    $_POST['setting']['ws_tpl_script_head'] = htmlentities($_POST['setting']['ws_tpl_script_head']);
                    $_POST['setting']['ws_tpl_script_body'] = htmlentities($_POST['setting']['ws_tpl_script_body']);
                    $_POST['setting']['ws_tpl_script_foot'] = htmlentities($_POST['setting']['ws_tpl_script_foot']);

                }

				foreach($_POST['setting'] as $key=>$val) {
					$class_setting->setting_edit($key,$val);
				}

				if($_GET['Tab'] != NULL) {
					$link_query = ['query'=>['Tab'=>$_GET['Tab'],'Action'=>PAGE_action]];
				}
				$zulu->notification_set("Settings updated successfully.",1);
				header("Location: ".$zulu->link_page(PAGE_file,$link_query));
				exit;
			}
		}

		//Check golive checklist
		if($_POST['action'] == 'golive') {
			for($i=1;$i<=$STEP_count;$i++) {
				if($_POST['check'][$i]!=1) {
					$missing[] = $i;
				}
			}
			if(count($missing)>0) {
				$zulu->notification_set("Sorry step(s) ".implode(", ",$missing)." were not checked off.",2);
			} else {
				$class_setting->setting_edit('ws_status',1);
				$class_setting->setting_edit('ws_status_checklist',time());
				$zulu->notification_set("Checklist was valid, site is now <b>LIVE</b>.",1);
				header("Location: ".$zulu->link_page(PAGE_file,['self'=>true]));
				exit;
			}
		}

        $zulu->template->css_file[] = "//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css";
		$zulu->template->js_file[] = "//code.jquery.com/ui/1.11.4/jquery-ui.js";
		$zulu->template->js_code[] = "
		$(document).ready(function(){
			$(\".date\").datepicker({ dateFormat: \"dd/mm/yy\" });
		});
		";

        if($selected_tab == 'general') {
            $option_page_array = ["Select..."];
            $page_posts = $class_post->post_data(['sort'=>'title ASC','type'=>'page','status'=>'published','field'=>['id','title'],'meta'=>false]);
            foreach($page_posts as $key=>$val) {
                $option_page_array[$val['id']] = $val['title'];
            }

            $class_file->uploadifive_new("logo_main",['preview'=>true,'post'=>['path_custom'=>$zulu->path_clean($class_website->image_rel),'file_name'=>$class_website->logo_filename,'file_ext'=>$class_website->logo_ext,'action'=>'ws_logo'],'setting'=>['multi'=>false,'queueSizeLimit'=>1],'event'=>['complete'=>'']]);
            $logo_path = $class_website->logo_path;
            $logo_rel = $class_website->logo_rel;

            $class_file->uploadifive_new("favicon_main",['preview'=>true,'post'=>['path_custom'=>$zulu->path_clean($class_website->favicon_fold_rel),'file_name'=>$class_website->favicon_filename,'file_ext'=>$class_website->favicon_ext,'action'=>'ws_favicon'],'setting'=>['multi'=>false,'queueSizeLimit'=>1],'event'=>['complete'=>'']]);
            $favicon_path = $class_website->favicon_path;
            $favicon_rel = $class_website->favicon_rel;

        } elseif($selected_tab == 'shop') {

            $zulu->template->jquery[] = "
            $('#related-select-bt').click(function() {
                $('select[name=\"setting[ws_shop_related_products][]\"] option').prop('selected', true);
                $('select[name=\"setting[ws_shop_related_products][]\"]').focus();
            });
            $('#related-deselect-bt').click(function() {
                $('select[name=\"setting[ws_shop_related_products][]\"] option').prop('selected', false);
                $('select[name=\"setting[ws_shop_related_products][]\"]').focus();
            });

            $('#product-reviews-enable').change(function() {
                var val = $(this).val();
                if(val == 1) {
                    $('#product-reviews select:not(#product-reviews-enable)').prop('disabled', false);
                    $('#product-reviews input').prop('disabled', false);
                } else {
                    $('#product-reviews select:not(#product-reviews-enable)').prop('disabled', true);
                    $('#product-reviews input').prop('disabled', true);
                }
            });
            $('#product-reviews-enable').trigger('change');
            $('#brand-enable').change(function() {
                var val = $(this).val();
                if(val == 1) {
                    $('.brand-option').prop('disabled', false);
                } else {
                    $('.brand-option').prop('disabled', true);
                }
            });
            $('#brand-enable').trigger('change');
            ";

        } elseif($selected_tab == 'social') {
            $zulu->template->jquery[] = "
            $('.social-link-input').on('change keyup', function() {
                var val = $(this).val();
                var form_switch = $(this).closest('.row').find('.form-switch');
                if(val != '') {
                    form_switch.removeClass('disabled');
                    if(!form_switch.find('input[type=\"checkbox\"]').is(':checked')) {
                        form_switch.find('label').trigger('click');
                    }
                } else {
                    if(form_switch.find('input[type=\"checkbox\"]').is(':checked')) {
                        form_switch.find('label').trigger('click');
                    }
                    form_switch.addClass('disabled');
                }
            });
            ";

        } elseif($selected_tab == 'maintenance') {
            $zulu->template->jquery[] = "
            var page_fresh = 1;
            $('.select-task').click(function() {
                var this_id = $(this).data('id');
                if($(this).hasClass('selected')) {
                    $('#status-' + this_id).val('0');
                    $(this).removeClass('selected');
                } else {
                    $('#status-' + this_id).val('1');
                    $(this).addClass('selected')
                }
                page_fresh = 0;
                return false;
            });";

        } elseif($selected_tab == 'script') {
            $zulu->template->css_file['code-mirror'] = TPL_rel."assets/code-mirror/lib/codemirror.css";

        }

	}

	if(PAGE_action=='theme') { //template edit page

		$form_edit = new form;
		$data_row = $class_setting->setting_data();

		$zulu->nav->title = 'Theme Settings';
        $zulu->nav->breadcrumb = [$zulu->nav->title => array("link"=>$zulu->link_page(PAGE_file, ['query'=>['Action'=>PAGE_action]]))];

		$user_data = $class_user->user_data(array('id'=>$class_user->authorised->id));
		$user_meta = $class_user->user_meta($class_user->authorised->id);
		$selected_tab = ($_GET['Tab']?$_GET['Tab']:($class_cache->load('website_theme_tab')!=NULL?$class_cache->load('website_theme_tab'):"theme"));

		if(!isset($_GET['Tab'])) {
			$_GET['Tab'] = $selected_tab;
		}
		$class_cache->save('website_theme_tab',$selected_tab);
		$tab_list = [
			"theme"				=>	"Theme",
			"theme_setting"		=>	"Theme Settings",
		];

		if(!$_POST) {
			foreach($data_row as $key=>$val) {
				$_POST[$key] = $val;
			}
			$post_blank = true;
		}

		//Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;

			if($form_edit->valid) {

				foreach($_POST['setting'] as $key=>$val) {
					$class_setting->setting_edit($key,$val);
				}

				if($_GET['Tab'] != NULL) {
					$link_query = ['query'=>['Tab'=>$_GET['Tab']]];
				}
				$link_query['query']['Action'] = PAGE_action;
				if($haserr) {
					$zulu->notification_set("Settings updated successfully.",1);
				}
				header("Location: ".$zulu->link_page(PAGE_file,$link_query));
				exit;
			}
		}
		//Tab: Theme Settings
		if($selected_tab=='theme_setting') {
			$tpl_id = $class_setting->data['ws_template_id'];
			if($tpl_id>0) {

				$config_path = $class_website->template_user_folder."_config.txt";
				if(file_exists(MAIN_path.$config_path)) {
					$form_edit = new form;
					$has_setting = true;
					$form_layout_data = unserialize(file_get_contents(MAIN_path.$config_path));

					$setting_get = $class_setting->setting_data(['key_start'=>'ws_theme_']);
					if($post_blank) {
						foreach($_POST as $settkey=>$sett) {
							if(strstr($settkey,"ws_theme")) {
								$_POST['ws_theme'][str_replace("ws_theme_",NULL,$settkey)] = stripslashes($sett);
							}
						}
					}

					foreach($form_layout_data['config_field'] as $field_key=>$form_field) {
						$group_name = ($form_field['group']!=NULL?$form_field['group']:'default');
						$form_item[$group_name][] = "<div class=\"form-group\">
							<label>".$form_field['label']." ".($form_field['required']?"<em>*</em>":NULL)."</label>
							".$form_edit->input_html($form_field['input']['type'],'ws_theme['.$field_key.']',htmlentities($_POST['ws_theme'][$field_key]))."
						</div>";
					}
					foreach($form_item as $group_key=>$field_data) {
						$form_panel[] = "
							<div class=\"panel panel-default\">
								<div class=\"panel-heading\">
									<i class=\"fas fa-cogs\"></i> ".ucwords(strtolower(str_replace("_"," ",$group_key)))."
								</div>
								<div class=\"panel-body\">
									".implode("",$field_data)."
								</div>
							</div>
						";
					}

					//--Custom CSS input
					$form_panel[] = "
						<div class=\"panel panel-default\">
							<div class=\"panel-heading\">
								<i class=\"fas fa-code\"></i> Custom CSS Code
							</div>
							<div class=\"panel-body\">
								".$form_edit->input_html('code','ws_theme[custom_css]',$_POST['ws_theme']['custom_css'])."
							</div>
						</div>
					";


					$zulu->template->body = implode("",$form_panel);
				}

				if(!$has_setting) {
					$zulu->notification_set("This theme has no settings.",2);
				}
			} else {
				$zulu->notification_set("You have no theme selected, please select one via the 'theme' tab first!",2);
			}

			//-- Post
			if($_POST['action']=='theme_setting') {
				foreach($_POST['ws_theme'] as $post_key=>$post_val) {
					$setting_label = "ws_theme_".$post_key;
					$class_setting->setting_edit($setting_label,str_replace('\r\n',chr(13),$db->escape_string($post_val)));
				}

				$zulu->notification_set("Theme settings updated.",1);
				header("Location: ".$zulu->link_page(PAGE_file,['self'=>true]));
				exit;
			}
		}

		//Tab: Theme
		if($selected_tab=='theme') {

			//-- Set Template
			if($_GET['Do']=='template_set'&&$_GET['token']!=NULL) {
				$return = $class_website->template_set($_GET['token']);
				if($return['success']) {
					$zulu->notification_set($return['reason'],1);
				} else {
					$zulu->notification_set($return['reason'],2);
				}

				header("Location: ".$zulu->link_page(PAGE_file,['self'=>true,'filter'=>['Do']]));
				exit;
			}
			//-- Delete Template
			if($_GET['Do']=='template_delete'&&$_GET['token']!=NULL) {
				$return = $class_website->template_delete($_GET['token']);
				if($return['success']) {
					$zulu->notification_set($return['reason'],1);
				} else {
					$zulu->notification_set($return['reason'],2);
				}

				header("Location: ".$zulu->link_page(PAGE_file,['self'=>true,'filter'=>['Do']]));
				exit;
			}

			//-- Data
			$template_all_data = $class_website->template_data(['status'=>1,'user_id'=>0]);
			$template_data = $class_website->template_data();

			//-- Default
			$table_column[] = array("",array('class'=>array('')));
			$table_column[] = array("Name",array('class'=>array('')));
			$table_column[] = array("Information",array('class'=>array('')));
			$table_column[] = array("Status",array('class'=>array('')));
			$table_column[] = array("Actions",array('class'=>array('right')));
			$table_row[] = array(
					"header"	=>	 true,
					"class"		=>	"",
					"content"	=>	$table_column);

			foreach($template_data as $row) {
				if($class_setting->data['ws_template_id']!=$row['id']&&$row['status']>0) {
					$bt_array[] = ['label'=>'Activate','class'=>'success','icon'=>'check','link'=>$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'template_set','token'=>$row['token']]])];
				} elseif($row['status']==0) {
				} else {
					$this_tpl = true;
				}
				$bt_array[] = ['label'=>'Edit','class'=>'primary','icon'=>'edit','link'=>$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Action'=>'template_edit','id'=>$row['id']]])];
				$bt_array[] = ['label'=>'Duplicate','class'=>'default','icon'=>'copy','link'=>$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Action'=>'template_edit','template_duplicate'=>$row['id']]])];
				$bt_array[] = ['label'=>'Delete','class'=>'danger confirm','icon'=>'remove','link'=>$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'template_delete','token'=>$row['token']]])];

				$table_row[] = array("content" => array(
					array("<i class=\"far fa-image\"></i>"),
					array(stripslashes($row['name'])),
					array("<small>".$zulu->shorten(stripslashes($row['description']),200)."</small>"),
					array(($this_tpl?"<span class=\"opt opt-success opt-bord\"><i class=\"fas fa-check\"></i> Current Theme</span> ":NULL).($row['status']==0?"<span class=\"opt opt-grey\"><i class=\"fas fa-pause\"></i> Draft</span>":"<span class=\"opt opt-success\"><i class=\"fas fa-flag\"></i> Published</span>")),
					array($zulu->button_render($bt_array),array('class'=>array('right')))
				),'class'=>($row['status']==2?"green":($overdue?"yellow":NULL)));
				unset($this_tpl,$bt_array);
			}
			$zulu->template->body_your = $zulu->table_render($table_row,0,['data_table'=>false]);
			unset($table_row,$table_column,$bt_array);

			//-- Default
			$table_column[] = array("",array('class'=>array('')));
			$table_column[] = array("Name",array('class'=>array('')));
			$table_column[] = array("Information",array('class'=>array('')));
			$table_column[] = array("Status",array('class'=>array('')));
			$table_column[] = array("Actions",array('class'=>array('right')));
			$table_row[] = array(
					"header"	=>	 true,
					"class"		=>	"",
					"content"	=>	$table_column);

			foreach($template_all_data as $row) {
				if($class_setting->data['ws_template_id']!=$row['id']&&$row['status']>0) {
					$bt_array[] = ['label'=>'Activate','class'=>'success','icon'=>'check','link'=>$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'template_set','token'=>$row['token']]])];
				} else {
					$this_tpl = true;
				}
				if($class_user->authorised->role=='admin') {
					$bt_array[] = ['label'=>'Edit','class'=>'primary','icon'=>'edit','link'=>$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Action'=>'template_edit','id'=>$row['id']]])];
					$bt_array[] = ['label'=>'Duplicate','class'=>'default','icon'=>'copy','link'=>$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Action'=>'template_edit','template_duplicate'=>$row['id']]])];
					$bt_array[] = ['label'=>'Delete','class'=>'danger confirm','icon'=>'remove','link'=>$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'template_delete','token'=>$row['token']]])];
				}
				$table_row[] = array("content" => array(
					array("<i class=\"far fa-image\"></i>"),
					array(stripslashes($row['name'])),
					array("<small>".$zulu->shorten(stripslashes($row['description']),200)."</small>"),
					array(($this_tpl?"<span class=\"opt opt-success opt-bord\"><i class=\"fas fa-check\"></i> Current Theme</span> ":NULL).($row['status']==0?"<span class=\"opt opt-grey\"><i class=\"fas fa-pause\"></i> Draft</span>":"<span class=\"opt opt-success\"><i class=\"fas fa-flag\"></i> Published</span>")),
					array($zulu->button_render($bt_array),array('class'=>array('right')))
				),'class'=>($row['status']==2?"green":($overdue?"yellow":NULL)));

				unset($this_tpl,$bt_array);
			}
			$zulu->template->body_default = $zulu->table_render($table_row,0,['data_table'=>false]);
		}

	}
	if(PAGE_action=='template_edit') { //edit page
		$form_edit = new form;
		$zulu->template->js_file[] = TPL_rel."assets/code-mirror/lib/codemirror.js";
		$zulu->template->js_file[] = TPL_rel."assets/code-mirror/mode/css/css.js";
		$zulu->template->js_file[] = TPL_rel."assets/code-mirror/mode/htmlmixed/htmlmixed.js";
		$zulu->template->js_file[] = TPL_rel."assets/code-mirror/addon/edit/matchbrackets.js";
		$zulu->template->css_file[] = TPL_rel."assets/code-mirror/lib/codemirror.css";
		$zulu->template->js_code[] = "
          var editor1 = CodeMirror.fromTextArea(document.getElementById('ta-header'), {
            lineNumbers: true,
            mode: 'text/html',
          });
          var editor2 = CodeMirror.fromTextArea(document.getElementById('ta-footer'), {
            lineNumbers: true,
            mode: 'text/html',
          });
          var editor3 = CodeMirror.fromTextArea(document.getElementById('ta-css'), {
            lineNumbers: true,
            mode: 'css',
          });
          ";

		$type = 'Template';
		if(PAGE_id<1) {
			$id = 0;
			$new = true;

			$zulu->nav->breadcrumb['New '.$type] = array();
			$zulu->nav->title = "New ".$type;
		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
			$group_data = $class_website->template_data(array('id'=>$id,'ovr_user_id'=>true));

			if($group_data['image']!=NULL) {
				$logo_path = $zulu->path_clean(MAIN_path.$class_file->file_root_rel.'../web/template/'.$group_data['image']);
				$logo_rel = $zulu->path_clean(MAIN_rel.$class_file->file_root_rel.'../web/template/'.$group_data['image']);
			}

			if($class_user->authorised->role=='admin') {
				//--nothing
			} else {
				if($group_data['user_id']!=$class_user->authorised->id) {
					$zulu->notification_set("You are not allowed to edit this template.",2);
					header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Tab'=>'theme','Action'=>'template']]));
				}
			}
			if($group_data['id']<=0) {
				$zulu->notification_set("Template doesn't exist.",2);
				header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Tab'=>'theme','Action'=>'template']]));
				exit;
			}

			if(!$_POST) {
				foreach($group_data as $key=>$val) {
					$_POST[$key] = $val;
				}

				//-- Read CSS
				$handle_css = fopen($group_data['_data']['root_path'].'style.css','r');
				if($handle_css) {
					$_POST['file_css'] = fread($handle_css,filesize($group_data['_data']['root_path'].'style.css'));
					fclose($handle_css);
				}
				//-- Read Header
				$handle_css = fopen($group_data['_data']['root_path'].'header.tpl','r');
				if($handle_css) {
					$_POST['file_header'] = fread($handle_css,filesize($group_data['_data']['root_path'].'header.tpl'));
					fclose($handle_css);
				}
				//-- Read Footer
				$handle_css = fopen($group_data['_data']['root_path'].'footer.tpl','r');
				if($handle_css) {
					$_POST['file_footer'] = fread($handle_css,filesize($group_data['_data']['root_path'].'footer.tpl'));
					fclose($handle_css);
				}
			}

			$zulu->nav->breadcrumb['Edit '.$type] = array();
			$zulu->nav->breadcrumb[$group_data['name']] = array();
			$zulu->nav->title = "Edit ".$type;
		}

		//Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;
			if($form_edit->validate(array('name'))) {
				$zulu->notification_set("Please enter a name for the template.",2);
				$form_edit->valid = false;
			}

			$data = array('name'=>$_POST['name'],'description'=>$_POST['description'],'status'=>(!isset($_POST['status'])?1:$_POST['status']));
			$extra = array('file_css'=>$_POST['file_css'],'file_header'=>$_POST['file_header'],'file_footer'=>$_POST['file_footer']);
			if(isset($_POST['user_id'])&&$class_user->authorised->role=='admin') {
				$data['user_id'] = $_POST['user_id'];
			}
			if(isset($_POST['master'])&&$class_user->authorised->role=='admin') {
				$data['master'] = $_POST['master'];
			}
			if($form_edit->valid) {
				if($class_website->template_edit($id,$data,$extra)) {
					$id = ($id>0?$id:mysql_insert_id());
					$tpl_data = $class_website->template_data(['id'=>$id]);

					//--Photos
					if($_FILES['preview']['tmp_name']!=NULL) {
						$terms_upload = $class_file->file_upload_raw('preview',$tpl_data['token'],'../web/template/');
						if($terms_upload['success']) {
							$img_note = "Preview photo uploaded.";
							$class_website->template_edit($id,['image'=>basename($terms_upload['path'])]);
						} else {
							$img_note = "Preview photo failed to upload.";
						}
					}
					$zulu->notification_set("Template ".($id>0?"updated":"created")." successfully. ".$img_note,1);

					header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'template','Tab'=>'theme']]));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}

		//-- Delete Preview
		if($_GET['Do']=='DeletePreview') {
			if(unlink($logo_path)) {
				$class_website->template_edit($id,['image'=>'']);
				$zulu->notification_set("Preview deleted successfully.",1);
			} else {
				$zulu->notification_set("Preview failed to delete.",2);
			}
			header("Location: ".$zulu->link_page(PAGE_file,['self'=>true,'filter'=>['Do']]));
			exit;
		}
	}
}
