<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Class: WEBSITE
class website {

	public $SQL_table_web_template = 'web_template';
	public $SQL_table_web_wishlist = 'wishlist';

	function __construct($config=[]) {
		global $db,$zulu,$class_user;
		$this->db = $db;
		$this->zulu = $zulu;
			$this->user = $class_user;
			$this->config = new stdClass();
			$this->config->module = new stdClass();
			$this->vars = new stdClass();
			$this->vars->slider = new stdClass();

		$this->endpoint = 'https://www.razorweb.co.nz/globalApps/';
		$this->authentication_endpoint = $this->endpoint.'checkCode.php';
		$this->user_folder = FE_crm.FE_rel.'template/user/'.$this->user->authorised->token.'/';
		$this->template_folder = FE_crm.FE_rel.'template/profile/';
		$this->template_user_folder = FE_crm.$this->user->authorised->file_web_path;

		$this->frame_fold = $this->user_folder.'frames/';

		$this->image_fold = $this->user_folder.'images/';
		$this->image_rel = MAIN_rel.$this->image_fold;
		$this->image_path = MAIN_path.$this->image_fold;
		$this->image_url = MAIN_url.$this->image_fold;

        $this->logo_filename = 'logo';
        $this->logo_ext = 'png';
        $this->logo_basename = $this->logo_filename.".".$this->logo_ext;
        $this->logo_rel = $this->image_rel.$this->logo_basename;
		$this->logo_path = $this->image_path.$this->logo_basename;
		$this->logo_url = $this->image_url.$this->logo_basename;

        $this->favicon_filename = 'favicon';
        $this->favicon_ext = 'png';
        $this->favicon_basename = $this->favicon_filename.".".$this->favicon_ext;
		$this->favicon_fold = $this->user_folder.'favicon/';
        $this->favicon_fold_rel = MAIN_rel.$this->favicon_fold;
        $this->favicon_fold_path = MAIN_path.$this->favicon_fold;
		$this->favicon_fold_url = MAIN_url.$this->favicon_fold;
		$this->favicon_rel = $this->favicon_fold_rel.$this->favicon_basename;
        $this->favicon_path = $this->favicon_fold_path.$this->favicon_basename;
		$this->favicon_url = $this->favicon_fold_url.$this->favicon_basename;
		$this->favicon_api_key = 'fc9e132fd4ed6f47b0e4332a2b2f872dcb944a1c';

		//Defaults
		$this->config->program = $this->user->authorised->_plan['website_type']; //- ZULUCMS - CMS Only, ZULUSHP - SHOP / CMS
		$this->config->mode_default = 'shop';

		if(MASTER_mode=='main') {
			$this->config->program = 'ZULUSHP';
		}

		$this->config->version = 100;
		$this->config->crm_mode = true;
		$this->config->status_type = [0=>'Under Construction',1=>'Live / Published',2=>'Maintenance Mode'];
		$this->config->shop = ($this->config->program=='ZULUSHP'?true:false);
		$this->config->shop_result_perpage_index = 8;
		$this->config->shop_cart_steps = [1=>['title'=>'Details'],2=>['title'=>'Shipping'],3=>['title'=>'Payment'],4=>['title'=>'Complete']];
		$this->config->shop_search_smart = true;

		//Search Shop
		$this->config->shop_result_perpage = 24;
		$this->config->shop_result_pageindex = 10;
		$this->config->shop_result_row_count = 4;

		//Modules
		$this->config->module->google_amp = false;

		$this->social_options = [
            'facebook'      =>  ['label'=>'Facebook', 'icon'=>'fab fa-facebook-f'],
            'instagram'     =>  ['label'=>'Instagram', 'icon'=>'fab fa-instagram'],
            'linkedin'      =>  ['label'=>'LinkedIn', 'icon'=>'fab fa-linkedin-in'],
            'youtube'       =>  ['label'=>'Youtube', 'icon'=>'fab fa-youtube'],
            'vimeo'         =>  ['label'=>'Vimeo', 'icon'=>'fab fa-vimeo-v'],
            'twitter'       =>  ['label'=>'Twitter', 'icon'=>'fab fa-twitter'],
            'google_business'=>  ['label'=>'Google My Business', 'icon'=>'fab fa-google'],
            'pinterest'     =>  ['label'=>'Pinterest', 'icon'=>'fab fa-pinterest-p'],
            'tumblr'        =>  ['label'=>'Tumblr', 'icon'=>'fab fa-tumblr'],
            'tiktok'        =>  ['label'=>'TikTok', 'icon'=>'fab fa-tiktok'],
        ];

        $this->recaptcha_score_options = [
            '1'     =>  '1.0 (definitely a human)',
            '0.9'   =>  '0.9',
            '0.8'   =>  '0.8',
            '0.7'   =>  '0.7',
            '0.6'   =>  '0.6',
            '0.5'   =>  '0.5 (probably a human)',
            '0.4'   =>  '0.4',
            '0.3'   =>  '0.3',
            '0.2'   =>  '0.2',
            '0.1'   =>  '0.1',
            '0'     =>  '0.0 (definitely a bot)',
        ];
        $this->recaptcha_score_default = '0.5';
	}
	function setup() {
		global $class_setting;

		@mkdir(dirname(__FILE__)."/../../".$this->user_folder);
		@mkdir(dirname(__FILE__)."/../../".$this->frame_fold);
		@mkdir(dirname(__FILE__)."/../../".$this->image_fold);
		@mkdir(dirname(__FILE__)."/../../".$this->favicon_fold);

		if($class_setting->data['ws_template_id']==0) {
			$def_tpl = $this->template_data(['master'=>true,'first'=>true]);
			$this->template_set($def_tpl['token']);
		}

		return true;
	}
	function image_add($field='image',$config=[]) {
		if($_FILES[$field]['tmp_name']!=NULL) {
			$name = $_FILES[$field]['name'];
			if($config['name']!=NULL) {
				$name = $config['name'];
			}
			@mkdir(dirname(__FILE__)."/../../".$this->image_fold);

			$file_destination = $this->image_path.$name;
			$fdr = $this->image_rel.$name;

			if(move_uploaded_file($_FILES[$field]['tmp_name'],$file_destination)) {
				return array("success"=>true,"reason"=>"File uploaded.","url_abs"=>$file_destination,"url"=>$fdr);
			} else {
				return array("success"=>false,"reason"=>"Failed to move file.");
			}
		} else {
			return array("success"=>false,"reason"=>"Empty image upload field.");
		}
	}
	function image_delete($image) {
		$file = $this->image_path.$image;
		@unlink($file);
		return true;
	}
	function get_favicon() {
		global $class_setting,$zulu;

        @mkdir($zulu->path_clean(FE_abs.$this->favicon_fold));
		$data = ["favicon_generation" => [
			'api_key'=>$this->favicon_api_key,
			'master_picture'=>['type'=>'url','url'=>$this->favicon_url,'demo'=>'false'],
			'files_location'=>['type'=>'path','path'=>$this->favicon_fold_url],
			'favicon_design'=>[
				'desktop_browser'=>[],
				'ios'=>['picture_aspect'=>'background_and_margin','margin'=>'4','background_color'=>'#FFF','assets'=>['ios6_and_prior_icons'=>'false','ios7_and_later_icons'=>'true','precomposed_icons'=>'false','declare_only_default_icon'=>'true']],
				'windows'=>['picture_aspect'=>'no_change','background_color'=>'#FFF','assets'=>['windows_80_ie_10_tile'=>'true','windows_10_ie_11_edge_tiles'=>['small'=>'true','medium'=>'true','big'=>true,'rectangle'=>'true']]],
				'firefox_app'=>['picture_aspect'=>'circle','keep_picture_in_circle'=>'true','circle_inner_margin'=>'5','background_color'=>'#FFF','manifest'=>['app_name'=>MAIN_name,'developer_name'=>'RAZOR Web Design','developer_url'=>'http://razorweb.co.nz']],
				'android_chrome'=>['picture_aspect'=>'shadow','manifest'=>['name'=>MAIN_name,'display'=>'standalone'],'assets'=>['legacy_icon'=>'true','low_resolution_icons'=>'false'],'theme_color'=>'#4972ab'],
				'safari_pinned_tab'=>['picture_aspect'=>'black_and_white','threshold'=>'60','theme_color'=>'#FFF'],
				'open_graph'=>['picture_aspect'=>'background_and_margin','background_color'=>'#FFF','margin'=>'12%','ratio'=>'1.91:1']
			],
			'versioning'=>['param_name'=>'ver','param_value'=>$this->zulu->serial(5)]
		]];
		$data_string = json_encode($data);

		$ch = curl_init('https://realfavicongenerator.net/api/favicon');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data_string))
		);

		$result = curl_exec($ch);
		$result = json_decode($result,true);

        $result = $result['favicon_generation_result'];
        if($result['result']['status'] == 'success') {
			$zip_path = $result['favicon']['package_url'];
            $local_zip = $zulu->path_clean(FE_abs.$this->favicon_fold_rel."favicon.zip");
			file_put_contents($local_zip, fopen($zip_path, 'r'));
			$zip = new ZipArchive;
			$res = $zip->open($local_zip);
			if ($res === TRUE) {
			  	$zip->extractTo($zulu->path_clean(FE_abs.$this->favicon_fold_rel));
			  	$zip->close();
				@unlink($local_zip);
			  	$status = ['success'=>true];

				$class_setting->setting_edit('ws_tpl_script_favicon',$result['favicon']['html_code']);
			} else {
			  	$status = ['success'=>false];
			}
		}
		return $status;
	}
	function serial_request($qry) {
		$response = file_get_contents($this->authentication_endpoint."?".http_build_query($qry));
		if(!$response) {
			return false;
		} else {
			return explode(':',$response);
		}
	}
	function serial_validate() {
		global $class_setting;

		$setting = $class_setting->setting_data();
		$qry = [
			'Action'		=>	'Authenticate',
			'Serial'		=>	$setting['ws_license_serial'],
			'RUPID'		=>	$setting['ws_license_rupid'],
			'Host'		=>	$_SERVER['HTTP_HOST'],
			'Product'	=>	$this->config->program,
			'Version'	=>	$this->config->version,
		];

		$data = $this->serial_request($qry);

		if(!$data) { //-- fallback if server down
			$data[0] = 1;
		}
		if($data[0]=='1') {
			$data['_valid'] = true;
			if($data[1]!=NULL) {
				$data['_msg'] = $data[1];
			}
		} else {
			$data['_valid'] = false;
			if($data[2]!=NULL) {
				$data['_msg'] = $data[2];
			}
		}

		return $data;
	}
	function form_build($form_name=NULL,$form_id=0,$config=[]) {
		global $zulu,$class_form_post,$class_cache,$class_object;
		$form_edit = new form;

		if($form_name==NULL&&$form_id==0) {
			if($zulu->vars->form_post->form_tag!=NULL) {
				$form_name = $zulu->vars->form_post->form_tag;
			} else {
				$form_id = $zulu->vars->form_post->form_load['id'];
				$config['ovr_user_id'] = true;
			}
		}
		if($config['object_form'] && $config['object_type_id']){
			$form_arr = $class_object->form_build_array($form_id,$ex_query);
		}else{
			if($form_id > 0) {
				if($config['ovr_user_id']) {
					$ex_query['ovr_user_id'] = true;
				}

				$form_arr = $class_form_post->form_build_array($form_id,$ex_query);
				$FORM = $form_arr['form'];
				$FIELD = $form_arr['field'];
				$form_name = $FORM['slug'];
			} else {
				$FORM = $class_form_post->template[$form_name]['form'];
				$FIELD = $class_form_post->template[$form_name]['field'];
			}
		}
		$hide_labels = isset($FORM['hide_labels']) && $FORM['hide_labels'] ? true : false;

		if($_SESSION['form_post']['hide'][$form_name]) {
			$form_done = true;
			unset($_SESSION['form_post']['hide'][$form_name]);
			$html .= $zulu->notification('',0,['tag'=>'form_'.$form_name]);
		}

		if(!$form_done || true) {
			if($FORM['label_title']!=NULL) {
            	$html .= "<h2>".$FORM['label_title']."</h2>";
			}
			if($FORM['label_description']!=NULL) {
            	$html .= "<p>".$FORM['label_description']."</p>";
			}
    		$html .= $zulu->notification('',0,['tag'=>'form_'.$form_name]);
            $html .= "<div class=\"form-wrapper form-".$form_name."\">
            ".($config['form_wrapper_hide']&&isset($config['form_wrapper_hide'])?NULL:"<form method=\"post\" name=\"form\" id=\"form\" action=\"\" enctype=\"multipart/form-data\" >")."
            	<div class=\"form-block single".($FORM['conf_float']?' float':NULL)."\">";

			//Load form cache
			if($form_id>0&&$class_cache->exists('form_post_autosave_'.$form_id)) {
				$auto_save_cache = unserialize($class_cache->load('form_post_autosave_'.$form_id));
				$_POST['field'] = $auto_save_cache['field'];
			}

			foreach($FIELD as $key=>$data) {
				$bt_extra = [];
				$data['input']['config']['class'][] = "input-".$data['id'];
				if($data['input']['config']['office']>0) {
					if($zulu->config->form_post_token!=NULL&&$_SESSION['zl_user']['id']<=0) { //-- edit
						if($data['input']['config']['office_public']>0) {
							$data['input']['type'] = 'static';
							$office_use = true;
						} else {
							continue;
						}
					} else { //new
						if($_SESSION['zl_user']['id']<=0) { //-- new
							continue;
						}
					}
				}
				if($data['input']['config']['inherit_field']) { //-- inherit field control
					$field_form_data = $class_form_post->form_field_data(['id'=>$data['input']['config']['inherit_field'],'field'=>['slug']]);
					$bt_extra[] = "<a data-field-id=\"field-".$field_form_data['slug']."\" class=\"bt-copy opt opt-grey text-light\" href=\"#\"><i class=\"fas fa-copy\"></i> Copy existing</a>";
					$zulu->template->jquery['fp_inherit'] = "
					$(document).on('click','.bt-copy',function() {
						var ident = $(this).data('field-id');
						$(this).parent().next().val($('#' + ident).val());
						return false;
					});
					";
				}
				if($data['input']['config']['show_field_if_empty']>0) { //-- toggle field control
					$field_form_data = $class_form_post->form_field_data(['id'=>$data['input']['config']['show_field_if_empty'],'field'=>['slug']]);
					$zulu->template->jquery[] = "
					$(document).on('".($field_form_data['input']=='radio'?'change':($field_form_data['input']=='checkbox'?'click':'blur'))."','.input-".$field_form_data['id']."',function() {
						".($data['input']['config']['show_field_if_value']!=NULL?"
						if($(this).attr('type')=='checkbox') {
							if($(this).val()=='".$data['input']['config']['show_field_if_value']."') {
								if($(this).is(':checked')) {
									$('.field-group-".$data['id']."').show();
								} else {
									$('.field-group-".$data['id']."').hide();
								}
							}
						} else {
							if($(this).val()=='".$data['input']['config']['show_field_if_value']."') {
								$('.field-group-".$data['id']."').show();
							} else {
								$('.field-group-".$data['id']."').hide();
							}
						}
						":"
						if($(this).val()!='') {
							$('.field-group-".$data['id']."').show();
						} else {
							$('.field-group-".$data['id']."').hide();
						}")."
					});
					$('.field-group-".$data['id']."').hide();
					";
				}
				if($data['input']['type']=='checkbox'&&$_POST['field'][$form_name][$key]>0) {
					$data['input']['config']['checked'] = true;
				}
				if($data['input']['type']=='checkbox'&&!isset($_POST['field'][$form_name][$key])) {
					$_POST['field'][$form_name][$key] = $data['default'];
				}
				if($data['spacer']) {
					$html .= "<hr />";
					$html .= "<h3>".$data['spacer_title']."</h3>";
				}
				if($data['input']['type']=='break') {
					$html .= "<hr />";
					continue;
				} elseif($data['input']['type']=='text') {
					$html .= "<div class='field w100 field-group-".$data['id']."'>".$data['description']."</div>";
					continue;
				} elseif($data['input']['type']=='date') {
					$data['input']['config']['class'] = ['date-picker'];
					$data['input']['config']['custom']['placeholder'] = 'DD/MM/YYYY';
					$data['input']['type'] = 'input';
				} elseif(($data['input']['type']=='checkbox' || $data['input']['type']=='radio') && count($data['input']['config']['option'])>0) {
					$html .= "<div class='field".($data['width']>0?" w".$data['width']:NULL)." field-group-".$data['id']."'><label>".$data['label'].($data['required']?" <em>*</em>":NULL)." ".implode(" ",$bt_extra)."</label></div>";
					foreach($data['input']['config']['option'] as $input_option) {
						$checked = false;
						if(
							($data['input']['type']=='checkbox' && (in_array($input_option,$_POST['field'][$form_name][$key]) || in_array($input_option,$_GET['field'][$form_name][$key]))) ||
						  	($data['input']['type']=='radio' && ($_POST['field'][$form_name][$key]==$input_option || $_GET[$form_name]['field'][$key]==$input_option))
						) {
							$checked = true;
						}
						$html .= "<div class=\"field".($data['input']['config']['custom']['option_width']>0?" w".$data['input']['config']['custom']['option_width']:NULL)."\">
									<label class=\"inline-input\">".$form_edit->input_html($data['input']['type'],"field[".$form_name."][".$key."]".($data['input']['type']=='checkbox'?"[]":NULL),$input_option,['checked'=>($checked?true:false),'class'=>['input-'.$data['id']]])." ".$input_option."</label>
								</div>";
					}
					continue;
				} elseif($data['input']['type']=='select') {
					$data['input']['config']['option'] = array_merge(['Select...'],$data['input']['config']['option']);
				}

				$html .= "<div class=\"field".($data['width']>0?" w".$data['width']:NULL)." field-group-".$data['id']."\">";
				if($data['iframe']!=NULL) {
					$html .= "<iframe src=\"".$data['iframe']."\" frameborder=\"0\" class=\"iframe\"></iframe>";
				}
				else { $dl = NULL; }
				if($data['download']!=NULL) {
					$dl = "<span class=\"text-small\"><a target=\"_blank\" class=\"button green\" href=\"".$data['download']."\"><i class=\"fas fa-save\"></i> Click to view</a></span>";
				}
				if($office_use) {
					$dl .= "<span class=\"text-small color-grey\">(Office use only)</span>";
				}
				if($data['input']['type']!='checkbox'&&$data['input']['type']!='radio') {
					if(!$hide_labels) {
						$html .= "<label>".$data['label'].($data['required']?" <em>*</em>":NULL)." {$dl} ".implode(" ",$bt_extra)."</label>";
					}
					$html .= $form_edit->input_html($data['input']['type'],"field[".$form_name."][".$key."]",($_POST['field'][$form_name][$key]!=NULL?$_POST['field'][$form_name][$key]:($_GET['field'][$form_name][$key]!=NULL?$_GET['field'][$form_name][$key]:$data['default'])),$data['input']['config']+['id'=>'field-'.$key]);
				} else {
					$html .= "<label class=\"inline-input\">".$form_edit->input_html($data['input']['type'],"field[".$form_name."][".$key."]",($_POST['field'][$form_name][$key]!=NULL?$_POST['field'][$form_name][$key]:($_GET['field'][$form_name][$key]!=NULL?$_GET['field'][$form_name][$key]:$data['default'])),$data['input']['config'])." ".$data['label'].($data['required']?" <em>*</em>":NULL)." {$dl} ".implode(" ",$bt_extra)."</label>";
				}
				$html .= "</div>";
				unset($office_use);
			}

            if(!isset($config['submit_hide']) || !$config['submit_hide']) {
                $html .= "<div class=\"field submit\">
                    ".$form_edit->input_html("submit","submit",($FORM['label_submit']!=NULL?$FORM['label_submit']:"Submit Form"))."
                    ".$form_edit->input_html("hidden","action","form_post_submit")."
                    ".$form_edit->input_html("hidden","form_submit_token",$this->vars->form_data['form']['token'])."
                    ".$form_edit->input_html("hidden","form_name",$form_name)."
                </div>
                ";
            }

            if($FORM['conf_google_captcha'] && $FORM['conf_google_captcha_key']!=NULL && $FORM['conf_google_captcha_secret']!=NULL && !isset($_SESSION['form_post']['captcha_valid'][$form_name])) {
                $html .= $form_edit->input_html("recaptcha", $form_name, null, ['google_captcha_key'=>$FORM['conf_google_captcha_key']]);
			}

            //-- close form block
			$html .= "</div>";

            if(!isset($config['form_wrapper_hide']) || !$config['form_wrapper_hide']) {
                $html .= "</form>";
            }

            //-- close form wrapper
            $html .= "</div>";

			} else {
    			$html .= $zulu->notification('',0,['tag'=>'form_'.$form_name]);
            }

		return $html;
	}
	function shop_active() {
		global $zulu,$class_setting,$class_user;
		$se = $class_setting->setting_data(['user_id'=>$class_user->authorised->id]);
		$ts_from = $zulu->dateEncode($se['ws_shop_chk_dis_from'],true);
		$ts_to = $zulu->dateEncode($se['ws_shop_chk_dis_to'],true);

		if($ts_from>0&&$ts_from<=time()&&$ts_to>0&&$ts_to>=time()) {
			return false;
		} else {
			return true;
		}
	}
	function template_edit($id=0,$data,$extra=[]) {
		global $class_user,$zulu;

		if($id<1) {
			$token = $zulu->serial();
			$data['user_id'] = (isset($data['user_id'])?$data['user_id']:$class_user->authorised->id);
			$this->db->query("INSERT INTO ".$this->SQL_table_web_template." (token,status,name,user_id) VALUES ('".$token."',1,'Untitled Theme','".$data['user_id']."')");

			@mkdir($zulu->path_clean(dirname(__FILE__)."/../../".$this->template_folder.$token."/"));
			@mkdir($zulu->path_clean(dirname(__FILE__)."/../../".$this->template_folder.$token."/images/"));

			$id = $this->db->insert_id;
			unset($data['user_id']);
		}

		foreach($data as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}

		$query = "UPDATE ".$this->SQL_table_web_template." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}'";
		if($this->db->query($query)) {

			//-- Write files
			if($class_user->authorised->role=='admin') {
				$success = true;
				$tpl_data = $this->template_data(['id'=>$id,'ovr_user_id'=>true]);
				if($extra['file_css']!=NULL) {
					$handle_css = fopen($tpl_data['_data']['root_path'].'style.css','wa+');
					if($handle_css) {
						fwrite($handle_css,$extra['file_css']);
						fclose($handle_css);
						chmod($handle_css,0644);
					} else {
						$success = false;
						$reason = "Failed to write CSS file.";
					}
				}
				if($extra['file_header']!=NULL) {
					$handle_css = fopen($tpl_data['_data']['root_path'].'header.tpl','wa+');
					if($handle_css) {
						fwrite($handle_css,$extra['file_header']);
						fclose($handle_css);
						chmod($handle_css,0644);
					} else {
						$success = false;
						$reason = "Failed to write header file.";
					}
				}
				if($extra['file_footer']!=NULL) {
					$handle_css = fopen($tpl_data['_data']['root_path'].'footer.tpl','wa+');
					if($handle_css) {
						fwrite($handle_css,$extra['file_footer']);
						fclose($handle_css);
						chmod($handle_css,0644);
					} else {
						$success = false;
						$reason = "Failed to write footer file.";
					}
				}
			}
			//-- End Write

			return array("success"=>$success,"id"=>$id,"reason"=>($reason!=NULL?$reason:NULL));
		} else {
			return array("success"=>false,"reason"=>($reason!=NULL?$reason:"Database error occurred."),'id'=>$id);
		}
	}
	function template_data($config=array()) {
		global $class_user,$zulu;

		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if($config['client_id']!=NULL) {
			$sql_config['where'][] = "client_id = '".$config['client_id']."'";
		}
		if($config['token']!=NULL) {
			$sql_config['where'][] = "token = '".$config['token']."'";
			$config['first'] = true;
		}
		if($config['status']!=NULL || $config['status']=='0') {
			$sql_config['where'][] = "status = '".$config['status']."'";
		}
		if($config['master']) {
			$config['first'] = true;
			$config['ovr_user_id'] = true;
			$sql_config['where'][] = "master = 1";
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		$sql_config['sort'] = 'name ASC, status DESC';
		if(!$config['ovr_user_id']) {
			if($class_user->authorised->role=='admin') {
				$sql_config['where'][] = "(user_id = 0 OR user_id = '".(isset($config['user_id'])?$config['user_id']:$class_user->authorised->id)."')";
			} else {
				$sql_config['where'][] = "user_id = '".(isset($config['user_id'])?$config['user_id']:$class_user->authorised->id)."'";
			}
		}
		$rows = $zulu->table_data($this->SQL_table_web_template,$id,$sql_config);
		if($rows['id']>0) {
			$rows['_data']['root_path'] = $zulu->path_clean(dirname(__FILE__)."/../../".$this->template_folder.$rows['token']."/");
			$rows['_data']['rel_path'] = $zulu->path_clean($this->template_folder.$rows['token']."/");
		}
		return $rows;
	}
	function template_set($token) {
		global $class_setting,$class_user;
		$tdata = $this->template_data(['token'=>$token,'ovr_user_id'=>true]);
		if((($tdata['user_id']==0||$tdata['user_id']==$class_user->authorised->id)&&$class_user->authorised->role!='admin')||$class_user->authorised->role=='admin') {
			$class_setting->setting_edit("ws_template_id",$tdata['id']);
			$class_setting->setting_edit("ws_template_path",$tdata['token']);
			return ['success'=>true,'reason'=>'Template updated successfully.'];
		} else {
			return ['success'=>false,'reason'=>'You are not allowed to use this template.'];
		}
	}
	function template_delete($token) {
		global $class_setting,$class_user;

		$tdata = $this->template_data(['token'=>$token,'ovr_user_id'=>true]);
		if(($tdata['user_id']==$class_user->authorised->id&&$class_user->authorised->role!='admin')||$class_user->authorised->role=='admin') {
			$this->db->query("DELETE FROM ".$this->SQL_table_web_template." WHERE id = '".$tdata['id']."'");
			return ['success'=>true,'reason'=>'Template deleted successfully.'];
		} else {
			return ['success'=>false,'reason'=>'You are not allowed to delete this template.'];
		}
	}
	function template_css_parse($content) {
		global $class_setting;

		$css_code = $content;
		$result = preg_match_all('/\[[^\]]*\]/',$css_code,$css_match);

		if($result) {
			foreach($css_match[0] as $css_rep) {
				$this_field = str_replace(['[',']'],'',$css_rep);
				if($class_setting->data['ws_theme_'.$this_field]!=NULL) {
					$css_code = str_replace($css_rep,$class_setting->data['ws_theme_'.$this_field],$css_code);
				} elseif($this_field=='custom_css') {
					$css_code = str_replace($css_rep,'.null {}',$css_code);
				}
			}
		}
		return $css_code;
	}
	private function slider_content_build($post) {
		global $zulu;
		$class = [];
		$html = [];
		$post['_meta'] = (isset($post['_meta']) && is_array($post['_meta']) ? $post['_meta'] : []);
		$post['_meta'] += [
			'overlay_colour' => '#000000',
			'overlay_opacity' => 0,
			'head_ele_type' => '',
			'align_x' => NULL,
			'align_y' => NULL,
			'heading_1' => '',
			'heading_2' => '',
			'heading_3' => '',
			'button_convert' => 0,
		];
		$post['content'] = (isset($post['content']) ? $post['content'] : '');

		list($r, $g, $b) = sscanf($post['_meta']['overlay_colour'], "#%02x%02x%02x");

		$showp = ($post['_meta']['head_ele_type']=='p'?true:false);
		if($post['_meta']['align_x']!=NULL) {
			$class[] = "t".$post['_meta']['align_x'];
		}
		if($post['_meta']['align_y']!=NULL) {
			$class[] = "v".$post['_meta']['align_y'];
		}
		if(trim($post['_meta']['heading_1'])!=NULL) {
			$html[] = ($showp?"<p class='h1'>".stripslashes($post['_meta']['heading_1'])."</p>":"<h1>".stripslashes($post['_meta']['heading_1'])."</h1>");
		}
		if(trim($post['_meta']['heading_2'])!=NULL) {
			$html[] = ($showp?"<p class='h2'>".stripslashes($post['_meta']['heading_2'])."</p>":"<h2>".stripslashes($post['_meta']['heading_2'])."</h2>");
		}
		if(trim($post['_meta']['heading_3'])!=NULL) {
			$html[] = ($showp?"<p class='h3'>".stripslashes($post['_meta']['heading_3'])."</p>":"<h3>".stripslashes($post['_meta']['heading_3'])."</h3>");
		}
		if(trim($post['content'])!=NULL) {
			$con = stripslashes($post['content']);
			if($post['_meta']['button_convert']>0) {
				$con = str_replace("<a","<a class='button'",$con);
			}

			$html[] = $con;
		}
		return "
			<div class=\"slide-inner\" style=\"background-color:rgba(".$r.",".$g.",".$b.",".$post['_meta']['overlay_opacity'].");\">
				<div class=\"slide-cell ".implode(" ",$class)."\">
					".implode("\n",$html)."
				</div>
			</div>
				";
	}

    function slider_build($slider_id=0, $config=[]) {
		global $zulu,$class_post,$class_file;

        $use_root = false;
        $slide_content_html = $html = null;
        $slider_html_slide = [];

        if($slider_id > 0) {
            $slider_post_data = $class_post->post_data(['id'=>$slider_id]);
            $token = $slider_post_data['token'];

            if(
                trim($slider_post_data['_meta']['heading_1'])!=NULL ||
                trim($slider_post_data['_meta']['heading_2'])!=NULL ||
                trim($slider_post_data['_meta']['heading_3'])!=NULL ||
                trim(strip_tags($slider_post_data['content']))!=NULL
            ) {
                $use_root = true;
                $slide_content_html = $this->slider_content_build($slider_post_data);
            }
            $this->vars->slider->use_root = $use_root;

            if($slider_post_data['_meta']['display_type']=='content') { //-- content html slider
                $type = 'content';
                $image_data = $class_post->post_image($slider_id);
                foreach($image_data['gallery'] as $slider_row) {
                    $image = $class_post->config->file_rel.$slider_row['image'];
                    $this_slider_post = $class_post->post_data(['id'=>$slider_row['post_id']]);
                    $slide_meta = $this_slider_post['_meta'];
                    if(!$use_root) {
                        $slide_content_html = $this->slider_content_build($this_slider_post);
                    }
                    $slider_html_slide[] = "<div class=\"slide slider-image-wrapper\" style=\"background-image: url('".$image."');\">
                        ".($slide_meta['redirect_link']!=NULL?"<a class=\"overlay-link\" target=\"".$slide_meta['redirect_location']."\" href=\"".$slide_meta['redirect_link']."\">":NULL)."
                        ".(!$use_root&&trim($slide_content_html)!=NULL?"
                        <div class=\"slide-overlay\">
                            ".stripslashes($slide_content_html)."
                        </div>
                        ":NULL)."
                        ".($slide_meta['redirect_link']!=NULL?"</a>":NULL)."
                    </div>";
                }

            } else { //-- image slider

                $type = 'image';
                $image_data = $class_post->post_image($slider_id);
                foreach($image_data['gallery'] as $key=>$slider_row) {
                    $image = $class_post->config->file_rel.$slider_row['image'];
                    $this_slider_post = $class_post->post_data(['id'=>$slider_row['post_id']]);
                    $slide_meta = $this_slider_post['_meta'];
                    $first = $key == 0 ? true : false;

                    $slider_html_slide[] = "<div class='slide'>
                        ".($slide_meta['redirect_link']!=NULL?"<a class=\"overlay-link\" target=\"".$slide_meta['redirect_location']."\" href=\"".$slide_meta['redirect_link']."\">":NULL)."
                        <img ".(!$first?'data-':null)."src='".$image."' alt='".stripslashes($slider_row['title'])."' class='".(!$first?'tns-lazy-img':null)."' />
                        ".($slide_meta['redirect_link']!=NULL?"</a>":NULL)."
                    </div>";
                }

            }

        } elseif(isset($config['slides']) && count($config['slides']) > 0) {
            $slider_html_slide = $config['slides'];
            $token = $zulu->serial(6);
            $type = 'custom';

        }

        if(count($slider_html_slide) > 0) {
            $tablet_config = $mobile_config = [];

			$items = isset($config['items']) && $config['items'] > 0 ? $config['items'] : 1;
            $slideBy = isset($config['slideBy']) && $config['slideBy'] > 0 ? $config['slideBy'] : 1;
            $loop = isset($config['loop']) && !$config['loop'] ? 'false' : 'true';
            $autoplay = isset($config['autoplay']) && !$config['autoplay'] ? 'false' : 'true';
            $autoplayTimeout = isset($config['autoplayTimeout']) && $config['autoplayTimeout'] > 0 ? $config['autoplayTimeout'] : 1;
			$nav = !isset($config['nav']) || !$config['nav'] ? 'false' : 'true';

            if(isset($config['tablet']) && count($config['tablet']) > 0) {
                $tablet_config = [
                    'items'             =>  isset($config['tablet']['items']) && $config['tablet']['items'] > 0 ? $config['tablet']['items'] : $items,
                    'slideBy'           =>  isset($config['tablet']['slideBy']) && $config['tablet']['slideBy'] > 0 ? $config['tablet']['slideBy'] : $slideBy,
                ];
            }
            if(isset($config['mobile']) && count($config['mobile']) > 0) {
                $mobile_config = [
                    'items'             =>  isset($config['mobile']['items']) && $config['mobile']['items'] > 0 ? $config['mobile']['items'] : (isset($tablet_config['items']) ? $tablet_config['items'] : $items),
                    'slideBy'           =>  isset($config['mobile']['slideBy']) && $config['mobile']['slideBy'] > 0 ? $config['mobile']['slideBy'] : (isset($tablet_config['slideBy']) ? $tablet_config['slideBy'] : $slideBy),
                ];
            }

            $zulu->template->jquery_code[] = "let slider_".$token." = tns({
                container: '#slider-".$token."',
                items: ".$items.",
                slideBy: ".$slideBy.",
                loop: ".$loop.",
                controls: true,
                controlsPosition: 'bottom',
                autoplay: ".$autoplay.",
                autoplayTimeout: ".$autoplayTimeout.",
                autoplayHoverPause: true,
                autoplayButtonOutput: false,
                nav: ".$nav.",
								navPosition: 'bottom',
                mouseDrag: true,
                lazyload: true,
                gutter: 10,
                responsive: {
                    ".(count($mobile_config)>0?"0: ".json_encode($mobile_config).",":null)."
                    ".(count($tablet_config)>0?"600: ".json_encode($tablet_config).",":null)."
                    1024: {
                        items: ".$items.",
                        slideBy: ".$slideBy.",
                    }
                },
            });";
            $zulu->include_tiny_slider();

            $html = "
            <div class=\"slider-wrapper theme-default post-wrap\">
                <div id=\"slider-".$token."\" class=\"slider slider-".$type."\" data-token='".$token."'>
                    ".implode("",$slider_html_slide)."
                </div>
                ".($use_root&&trim($slide_content_html)!=NULL?"
                <div class=\"slide-overlay\">
                    ".stripslashes($slide_content_html)."
                </div>":NULL)."
            </div>";
        }

		return ['token'=>$token, 'success'=>(count($slider_html_slide)>0?true:false), 'html'=>$html];
	}

	function shop_mode() {
		global $class_setting;
		return (trim($class_setting->data['ws_shop_mode'])!=NULL?($class_setting->data['ws_shop_mode']=='shop'?true:false):($this->config->mode_default=='shop'?true:false));
	}

	function social_icon_html() {
        global $class_setting;

        $links = [];
		$html = null;

        foreach($this->social_options as $key=>$val) {
            if($class_setting->data['ws_social_link_'.$key] && $class_setting->data['ws_social_link_'.$key.'_enable']) {
                $links[] = "<a class='icon-link social-link' href='".$class_setting->data['ws_social_link_'.$key]."' target='_blank' title='".$val['label']."'>
                    <span class='icon ".$val['icon']."'></span>
                </a>";
            }
        }

		if(count($links) > 0) {
			$html = "<div class='d-flex icons social-icons'>".implode('',$links)."</div>";
		}

        return $html;
    }

	function has_social_icons() {
        global $class_setting;

        foreach($this->social_options as $key=>$val) {
            if($class_setting->data['ws_social_link_'.$key] && $class_setting->data['ws_social_link_'.$key.'_enable']) {
                return true;
            }
        }

        return false;
    }

}
