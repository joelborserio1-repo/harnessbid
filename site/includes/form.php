<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Form

class form {

	public $CONFIG_button_save = "<span class=\"fas fa-save\"></span> Save";

	function __construct() {
		global $db;
		$this->db = $db;
	}

	//Validate Reset
	function validate_reset() {

	}

	//Help icon
	function icon_help($text) {
		return "<span class=\"fas fa-info-circle color-grey help\" title=\"{$text}\"></span>";
	}

	//Generate inputs from existing GET/POST data
	function input_hidden($config) {
		if($config['method']=='GET') {
			foreach($_GET as $key=>$val) {
				if(in_array($key,$config['filter'])&&count($config['filter'])>0) {
					continue;
				}
				$html .= $this->input_html('hidden',$key,$val);
			}
		} else {
			foreach($_GET as $key=>$val) {
				if(in_array($key,$config['filter'])&&count($config['filter'])>0) {
					continue;
				}
				$html .= $this->input_html('hidden',$key,$val);
			}
		}
		return $html;
	}

	//Input Generate
	function input_html($type,$name,$value='',$config=array()) {
		global $zulu, $class_setting;

		$attr = array();
		$class = array();
		if($config['placeholder']!=NULL) {
			$attr[] = 'placeholder="'.$config['placeholder'].'"';
		}
		if($config['autofocus']) {
			$attr[] = 'autofocus';
		}
		if($config['id']) {
			$attr[] = "id='".$config['id']."'";
		}
		if($config['title']!=NULL) {
			$attr[] = "title='".$config['title']."'";
		}
		if($config['length']>0) {
			$attr[] = "maxlength='".$config['length']."'";
		}
		if($config['checked']==1) {
			$attr[] = 'checked';
		}
		if($config['autoc_off']||(isset($config['autocomplete'])&&!$config['autocomplete'])) {
			$attr[] = 'autocomplete="off"';
		}
		if($config['disabled']==1) {
			$attr[] = 'disabled';
		}
		if(isset($config['limit']['min'])) {
			$attr[] = "min=\"".$config['limit']['min']."\"";
		}
		if(isset($config['limit']['max'])) {
			$attr[] = "max=\"".$config['limit']['max']."\"";
		}
		if($config['rows']>1) {
			$attr[] = 'rows="'.$config['rows'].'"';
		}
		if(isset($config['class'])&&count($config['class'])>0) {
			foreach($config['class'] as $config_class) {
				$class[] = $config_class;
			}
		}
		if(isset($config['custom'])&&count($config['custom'])>0) {
			foreach($config['custom'] as $custom_name=>$custom_value) {
				$custom[] = $custom_name."=\"".$custom_value."\"";
			}
			$custom = implode(" ",$custom);
		}
		if(isset($config['style'])&&count($config['style'])>0) {
			foreach($config['style'] as $config_style) {
				$style[] = $config_style;
			}
			$style = "style=\"".implode("; ",$style)."\"";
		}
		switch($type) {
			case 'static':
				$html = "<p {$style} {$custom} ".implode(" ",$attr)." class=\"form-control-static  ".implode(" ",$class)."\">".($value==NULL?$_POST[$name]:$value)."</p><input type=\"hidden\" {$style} {$custom} name=\"{$name}\" value=\"".($value==NULL?$_POST[$name]:$value)."\" ".implode(" ",$attr)." class=\"form-control  ".implode(" ",$class)."\" />";
			break;
			case 'select':
				foreach($config['option'] as $o_value=>$o_label) {
					if(is_array($value)) {
						$sel = (in_array($o_value,$value));
						$sel = ($sel?"selected":NULL);
						$html_option .= "<option value=\"{$o_value}\" ".($config['data_label']?" data-id=\"{$o_value}\" data-label=\"".ltrim($o_label,"-")."\"":NULL)." ".$sel.">{$o_label}</option>";
					} else {
						$html_option .= "<option value=\"{$o_value}\" ".($config['data_label']?" data-id=\"{$o_value}\" data-label=\"".ltrim($o_label,"-")."\"":NULL)." ".($value==$o_value?"selected":NULL).">{$o_label}</option>";
					}

				}
				$html = "<select name=\"{$name}\" {$style} {$custom} class=\"form-control  ".implode(" ",$class)."\" ".implode(" ",$attr).">".$html_option."</select>";
			break;
			case 'input':
				$html = "<input type=\"text\" {$style} {$custom} name=\"{$name}\" value=\"".($value==NULL?$_POST[$name]:$value)."\" ".implode(" ",$attr)." class=\"form-control  ".implode(" ",$class)."\" />";
			break;
			case 'file':
				$html = "<input type=\"file\" {$style} {$custom} name=\"{$name}\" value=\"".($value==NULL?$_POST[$name]:$value)."\" ".implode(" ",$attr)." class=\"form-control  ".implode(" ",$class)."\" />";
			break;
			case 'hidden':
				$html = "<input type=\"hidden\" {$style} {$custom} name=\"{$name}\" value=\"".($value==NULL?$_POST[$name]:$value)."\" ".implode(" ",$attr)." class=\"form-control  ".implode(" ",$class)."\" />";
			break;
			case 'password':
				$html = "<input type=\"password\" {$style} {$custom} name=\"{$name}\" value=\"".$value."\" ".implode(" ",$attr)." class=\"form-control  ".implode(" ",$class)."\" />";
			break;
			case 'reset':
				$html = "<button ".implode(" ",$attr)." {$style} {$custom} type=\"reset\" name=\"{$name}\" class=\"btn btn-default  ".implode(" ",$class)."\">".($value==NULL?$_POST[$name]:$value)."</button>";
			break;
			case 'submit':
				$html = "<button ".implode(" ",$attr)." {$style} {$custom} type=\"submit\" name=\"{$name}\" class=\"btn btn-default ".implode(" ",$class)."\">".($value==NULL?$_POST[$name]:$value)."</button>";
			break;
			case 'textarea':
				$html = "<textarea ".implode(" ",$attr)." {$style} {$custom} name=\"{$name}\" class=\"form-control ".implode(" ",$class)."\">".($value==NULL?$_POST[$name]:$value)."</textarea>";
			break;
			case 'code':
				$serial = $zulu->serial(4);
				$zulu->template->js_file['code-mirror'] = TPL_rel."assets/code-mirror/lib/codemirror.js";
				$zulu->template->js_file['code-mirror-css'] = TPL_rel."assets/code-mirror/mode/css/css.js";
				$zulu->template->js_file['code-mirror-html'] = TPL_rel."assets/code-mirror/mode/htmlmixed/htmlmixed.js";
                $zulu->template->js_file['code-mirror-js'] = TPL_rel."assets/code-mirror/mode/javascript/javascript.js";
                $zulu->template->js_file['code-mirror-xml'] = TPL_rel."assets/code-mirror/mode/xml/xml.js";
				$zulu->template->js_file['code-mirror-matchbrackets'] = TPL_rel."assets/code-mirror/addon/edit/matchbrackets.js";
				$zulu->template->css_file['code-mirror'] = TPL_rel."assets/code-mirror/lib/codemirror.css";
				$zulu->template->jquery[] = "var editor".$serial." = CodeMirror.fromTextArea(document.getElementById('ta-".$serial."'), {
					lineNumbers: true,
				    mode: 'text/html'
				});";
				$html = "<textarea id=\"ta-".$serial."\" ".implode(" ",$attr)." {$style} {$custom} name=\"{$name}\" class=\"form-control ".implode(" ",$class)."\">".($value==NULL?$_POST[$name]:$value)."</textarea>";
			break;
			case 'checkbox':
				$html = "<input type=\"checkbox\" name=\"{$name}\" {$style} {$custom} ".($value==$_POST[$name]?'checked':NULL)." value=\"".($value==NULL?$_POST[$name]:$value)."\" ".implode(" ",$attr)." class=\"".implode(" ",$class)."\" />";
			break;
			case 'radio':
				if($config['ovr_check_value']) {
					$html = "<input type=\"radio\" name=\"{$name}\" {$style} {$custom} ".($config['checked']?'checked="checked"':NULL)." value=\"".$value."\" ".implode(" ",$attr)." class=\"".implode(" ",$class)."\" />";
				} else {
					$html = "<input type=\"radio\" name=\"{$name}\" {$style} {$custom} ".($value==$_POST[$name]?'checked="checked"':NULL)." value=\"".($value==NULL?$_POST[$name]:$value)."\" ".implode(" ",$attr)." class=\"".implode(" ",$class)."\" />";
				}
			break;
			case 'color':
				$class_this = 'spectrum-'.$zulu->serial(4);
				$class[] = 'spectrum';
				$class[] = $class_this;
				$html = "<input type=\"text\" {$style} {$custom} name=\"{$name}\" value=\"".($value==NULL?$_POST[$name]:$value)."\" ".implode(" ",$attr)." class=\"form-control  ".implode(" ",$class)."\" />";
				$zulu->template->file_post_css['spec'] = TPL_rel."assets/spectrum/spectrum.css";
				$zulu->template->file_post_css['spec_theme'] = TPL_rel."assets/spectrum/themes/sp-dark.css";
				$zulu->template->js_file['spec'] = TPL_rel."assets/spectrum/spectrum.js";
				$zulu->template->jquery[] = "
					$(\".".$class_this."\").spectrum({
						showInput: true,
						className: \"sp-dark\",
						showInitial: true,
						showPalette: true,
						showPaletteOnly: false,
						hideAfterPaletteSelect:true,
						showSelectionPalette: true,
						maxSelectionSize: 10,
						preferredFormat: \"hex\",
						localStorageKey: \"spectrum.demo\",
						change: function(color) {
							var id = $(this).parent().parent().parent().data('id');
							$(\"#draggable-text-\"+id).css(\"color\", color.toHexString());
						},
						palette: [
							['#1abc9c','#57d68d','#5cace2','#9b59b6','#5c6d7e','#f1c40f','#e67e22','#e74c3c','#ecf0f1','#95a5a6','#f39c12','#d35400','#c0392b','#bdc3c7']
						]
					});
				";
			//	$zulu->template->css_code[] = ".full-spectrum { display:block !important; }";
			break;
			case 'htmlarea':
				global $class_file;

                if(isset($config['id']) && $config['id'] != null) {
                    $txtref = $config['id'];
                } else {
                    $txtref = "txtarea-".rand(1,9999);
				    $attr[] = "id=\"{$txtref}\"";
                }

				$sContent=stripslashes(($value==NULL?$_POST[$name]:$value)); /*** remove (/) slashes ***/
				$html = "<textarea ".implode(" ",$attr)." name=\"{$name}\" {$custom} class=\"form-control ".implode(" ",$class)."\">".$this->encodeHTML($sContent)."</textarea>";
				$zulu->template->jquery_redactor = true;

                $init_code = '
                $("#'.$txtref.'").redactor({
                    plugins: ["alignment", "counter", "filemanager", "fontcolor", "fontsize", "fullscreen", "imagemanager", "properties", "table", "video"],
                    fileUpload: "'.TPL_rel.'assets/redactor/redactor.file.php?name_random=0&template=file",
                    fileManagerJson: "'.$class_file->user_upload_path().'file/file.json",
                    imageUpload: "'.TPL_rel.'assets/redactor/redactor.file.php?name_random=0&template=image",
                    imageManagerJson: "'.$class_file->user_upload_path().'image/image.json",
                    imageResizable: true,
                    imagePosition: true,
                    buttonsAddBefore: {
                        before: "format",
                        buttons: ["undo", "redo"]
                    },
                    buttonsAdd: ["line"],
                    linkNewTab: true,
                    minHeight: "300px",
                    maxHeight: "600px",
                });
                ';

                if((!isset($config['ajax']) || !$config['ajax']) && (!isset($config['jquery']) || !$config['jquery'])) {
                    $zulu->template->jquery[] = $init_code;
                } else {
                    if(!isset($config['init']) || $config['init']) {
                        $html .= "<script type=\"text/javascript\">".$init_code."</script>";
                    } else {
                        $html .= "<div class='js-code-container'>".$init_code."</div>";
                    }
                }

			break;
			case 'number':
				$html = "<input type=\"number\" {$style} {$custom} name=\"{$name}\" value=\"".($value==NULL?$_POST[$name]:$value)."\" ".implode(" ",$attr)." class=\"form-control  ".implode(" ",$class)."\" />";
			break;
            case 'email':
				$html = "<input type=\"email\" {$style} {$custom} name=\"{$name}\" value=\"".($value==NULL?$_POST[$name]:$value)."\" ".implode(" ",$attr)." class=\"form-control  ".implode(" ",$class)."\" />";
			break;
            case 'tel':
				$html = "<input type=\"tel\" {$style} {$custom} name=\"{$name}\" value=\"".($value==NULL?$_POST[$name]:$value)."\" ".implode(" ",$attr)." class=\"form-control  ".implode(" ",$class)."\" />";
			break;
            case 'recaptcha':
                $captcha_key = $class_setting->data['ws_module_google_captcha_api_key'];
                if(isset($config['google_captcha_key']) && $config['google_captcha_key'] != null) {
                    $captcha_key = $config['google_captcha_key'];
                }

                $html = "<input type=\"hidden\" name=\"g-recaptcha-response\" value=\"\" class=\"form-control ".implode(" ",$class)."\" id='GRecap-".$name."' />
                <p class='form-caption'>This site is protected by reCAPTCHA and the Google <a href='https://policies.google.com/privacy' target='_blank'>Privacy Policy</a> and <a href='https://policies.google.com/terms' target='_blank'>Terms of Service</a> apply.</p>";

                $zulu->template->js_file['google-recaptcha'] = 'https://www.google.com/recaptcha/api.js?render='.$captcha_key;
                $zulu->template->jquery_code[] = "grecaptcha.ready(function() {
                    grecaptcha.execute('".$captcha_key."', { action: '".$name."' }).then(function(token) {
                        document.getElementById('GRecap-".$name."').value = token;
                    });
                    setInterval(function() {
                        grecaptcha.execute('".$captcha_key."', { action: '".$name."' }).then(function(token) {
                            document.getElementById('GRecap-".$name."').value = token;
                        });
                    }, 60000);
                });";

                break;
		}
		$this->fields[$name];
		if(isset($config['required'])&&$config['required']) {
			$this->required[$name];
		}

		return $html;
	}

	function encodeHTML($sHTML) {
		$sHTML=str_replace("&","&amp;",$sHTML);
		$sHTML=str_replace("<","&lt;",$sHTML);
		$sHTML=str_replace(">","&gt;",$sHTML);
		return $sHTML;
    }

	//Validate
	function validate($fields,$config=[]) {
		$return = false;
		$empty = 0;
		$field_total = count($fields);
		foreach($fields as $field) {
			if(!$config['meta']) {
				if(trim($_POST[$field])=="") {
					$return = true;
					$empty += 1;
				}
			} else {
				if(trim($_POST['meta'][$field])=="") {
					$return = true;
					$empty += 1;
				}
			}
		}
		if($config['any']&&$empty<$field_total) {
			$return = false;
		}

		return $return;
	}

	function validate_email($email) {
		if(!filter_var($email,FILTER_VALIDATE_EMAIL)) {
			return false;
		}
		return true;
	}

	//Bind Key and Value Child into Input Select Array
	function input_array_bind($array,$value,$value_key=NULL) {
		foreach($array as $key=>$val) {
			if($value_key==NULL) {
				$newarray[$key] = $val[$value];
			} else {
				$newarray[$val[$value_key]] = $val[$value];
			}
		}
		return $newarray;
	}

	//DIRECTORY OPTION FORM
	function directoryOptionForm($pos='',$config=[]) { // $pos is the current position inside the hierarchy (curr item's ID) 3 = NO LIMITS
		global $DIRECTORY_tab;
		global $DIRECTORY_level;
		global $DIRECTORY_vl;
		global $output;
		global $class_user;

		$object = ($config['object']!=NULL?$config['object']:'');
		$object_id = ($config['object_id']!=NULL?$config['object_id']:'0');

		$pos = ($pos?$pos:NULL);
		$query = "SELECT * FROM file WHERE type='folder' AND parent_id=".($pos==NULL?"0":$pos)." AND user_id='".$class_user->authorised->id."' AND object='".$object."' AND object_id='".$object_id."' ORDER BY sort ASC, name ASC";
		$res = $this->db->mysqli->query($query);

		if($config['object'] == NULL) {
			$output[0] = 'Home Directory';
		}

		while($row = $res->fetch_array()) {
			$key = $row['id'];
			$val = '';
			$res2 = $this->db->mysqli->query("SELECT * FROM file WHERE type='folder' AND parent_id='$key' AND user_id='".$class_user->authorised->id."' AND object='".$object."' AND object_id='".$object_id."' ORDER BY sort ASC, name ASC");
			$has_kids = $res2->fetch_array() != NULL;

			if($pos!='') {
				for ($i=0; $i<$DIRECTORY_vl; $i++)
					$val .= $DIRECTORY_tab;
			}

			$val .= $row['name'];
			$output[$key] = $val;

			//If the directory has sub-directories
			if ($has_kids) {
				$DIRECTORY_vl++;
				$this->directoryOptionForm($key,$config); // recursive call
				$DIRECTORY_vl--;
			}
		}
	}

	function array_to_options($array,$value=0) {
		foreach($array as $o_value=>$o_label) {
			if(is_array($value)) {
				$sel = (in_array($o_value,$value));
				$sel = ($sel?"selected":NULL);
				$html_option .= "<option value=\"{$o_value}\" ".$sel.">{$o_label}</option>";
			} else {
				$html_option .= "<option value=\"{$o_value}\" ".($value==$o_value?"selected":NULL).">{$o_label}</option>";
			}

		}

		return $html_option;
	}

	//USER OPTION FORM
	function userOptionForm($all=false,$parent_id=-1) {
		global $class_user;

		$parent_id = ($parent_id==-1?$class_user->authorised->id:$parent_id);
		$user_data = $class_user->user_data(['parent'=>$parent_id,'field'=>['u.id','name_first','name_last', 'email'],'sort'=>'name_first ASC, name_last ASC']);

		$options = array();
		if($all) {
			$options[0] = 'All';
		}
		foreach($user_data as $row) {
			$sel = (in_array($row['id'],$users));
			$sel = ($sel?"selected":NULL);
			$options[$row['id']] = $row['name_first']. " " . $row['name_last']." (". $row['email'] .") (#".$row['id'].")";
		}
		return $options;
	}

	//CATEGORY OPTION FORM
	function categoryOptionForm($pos='',$tbl_override=NULL,$config=[]) { // $pos is the current position inside the hierarchy (curr item's ID) 3 = NO LIMITS
		global $DIRECTORY_tab;
		global $DIRECTORY_level;
		global $DIRECTORY_vl;
		global $output;
		global $class_user;

		if(count($config['sql_where'])>0) {
			$sql_addition = $config['sql_where'];
		}
		$tbl = ($tbl_override!=NULL?$tbl_override:'product');

		if($tbl=='product') {
			$where[] = "type='category'";
		}
		$ex_sql = (count($where)>0?implode(" AND ",$where)." AND ":NULL);

		$output[0] = '- No Category / Root';
		$pos = ($pos?$pos:NULL);
		$query = "SELECT * FROM {$tbl} WHERE ".$ex_sql." parent_id=".($pos==NULL?"0":$pos)." AND user_id='".$class_user->authorised->id."'".(count($sql_addition)>0?' AND '.implode("  AND ",$sql_addition):NULL).(!$config['sort_ovr']?" ORDER BY sort ASC, name ASC":NULL);
		$res = $this->db->mysqli->query($query);

		while($row = $res->fetch_array()) {
			$key = $row['id'];
			$val = '';
			$res2 = $this->db->mysqli->query("SELECT * FROM {$tbl} WHERE ".$ex_sql." parent_id='$key' AND user_id='".$class_user->authorised->id."' ".(count($sql_addition)>0?' AND '.implode(" AND ",$sql_addition):NULL).(!$config['sort_ovr']?" ORDER BY sort ASC, name ASC":NULL));
			$has_kids = $res2->fetch_array() != NULL;

			if($pos!='') {
				for ($i=0; $i<$DIRECTORY_vl; $i++)
					$val .= $DIRECTORY_tab;
			}

			if($config['label_field']!=NULL) {
				$val .= $row[$config['label_field']];
			} else {
				$val .= stripslashes($row['name']);
			}
			$output[$key] = $val;

			//If the category has sub-categories
			if ($has_kids) {
				$DIRECTORY_vl++;
				$this->categoryOptionForm($key,$tbl_override,$config); // recursive call
				$DIRECTORY_vl--;
			}
		}
	}

	//CLIENT OPTION FORM
	function clientOptionForm($blank=true,$type='1') {
		global $zulu;
		global $class_user;

		$ex = array("user_id='".$class_user->authorised->id."'");
		if(isset($config['status'])) {
			$ex[] = "status = ".$config['status'];
		} else {
			$ex[] = 'status = 1';
		}
		if($type!='all') {
			$ex[] = "type='{$type}'";
		}

		$client_data = $zulu->table_data('client',0,array("field"=>['name_first','name_last','company','id'],"sort"=>"company ASC, name_first ASC, name_last ASC","where"=>$ex));
		$options = array();
		if($blank) {
			$options[''] = '';
		}
		foreach($client_data as $row) {
			$options[$row['id']] = stripslashes((trim($row['company'])!=NULL?$row['company']:$row['name_first']." ".$row['name_last']));
		}
		return $options;
	}

	//PROJECT OPTION FORM
	function projectOptionForm($blank=true) {
		global $zulu,$class_user,$class_project;
		$title_data = $zulu->table_data('job',0,array("sort"=>"title ASC",'field'=>['id','title','reference'],"where"=>array("status = '0'","template = 0","user_id='".$class_user->authorised->id."'")));
		$options = array();
		if($blank) {
			$options[''] = '';
		}
		foreach($title_data as $row) {
			$options[$row['id']] = ($row['reference']!=NULL?$class_project->config->reference_prefix.'-'.$row['reference'].' ':NULL).stripslashes($row['title']);
		}
		return $options;
	}

	//PROCEDURE OPTION FORM
	function procedureOptionForm($blank=false) {
		global $zulu;
		global $class_user;
		$title_data = $zulu->table_data('procedure',0,array("sort"=>"title ASC","where"=>array("user_id='".$class_user->authorised->id."'")));
		$options = array();
		if($blank) {
			$options[''] = '';
		}
		foreach($title_data as $row) {
			$options[$row['id']] = stripslashes($row['title']);
		}
		return $options;
	}

	//SECTION OPTION FORM
	function sectionOptionForm($procedure_id=0) {
		global $zulu;
		global $class_user;
		$data = $zulu->table_data('procedure_section',0,array("sort"=>"title ASC","where"=>array("procedure_id='".$procedure_id."'","user_id='".$class_user->authorised->id."'")));
		$options = array();
		foreach($data as $row) {
			$options[$row['id']] = stripslashes($row['title']);
		}
		return $options;
	}

	//REFEREE OPTION FORM
	function refereeOptionForm() {
		global $zulu;
		global $REFEREE_array;
		$options = array();
		foreach($REFEREE_array as $key=>$val) {
			$options[$key] = $val;
		}
		return $options;
	}

	function referrerOptionForm() {
		return array(
			''=>'',
            'Client'=>'Client',
			'Online Paid'=>'Online Paid',
			'Online Free'=>'Online Free',
			'Online Referral'=>'Online Referral',
			'Website'=>'Website',
			'In-store'=>'In-store / off the street',
			'Family / Friend'=>'Family / Friend',
			'Event / Conference'=>'Event / Conference',
			'Previous Customer'=>'Previous Customer',
			'Word of Mouth'=>'Word of Mouth',
			'Other'=>'Other'
		);
	}

	function ruleOptionForm($all=false) {
		$templates = scandir("../includes/mail_template");
		foreach($templates as $temp) {
			$t = explode('.',$temp);
			if($t[0] != "") {
				//$template_html .= "<option ".($row_LISTING['template']==$temp?'selected':NULL).">".$temp."</option>";
				$template_html[$temp] = $temp;
			}
		}
		return $template_html;
	}

	//Template OPTION FORM
	function templateOptionForm($blank=false) {
		global $zulu;
		global $class_user;
		$data = $zulu->table_data('rule_template',0,array("sort"=>"category ASC",'field'=>array("DISTINCT category"),'where'=>array("user_id='".$class_user->authorised->id."'")));
		$options = array();
		if($blank) {
			$options[''] = '';
		}
		foreach($data as $row) {
			$options[$row['category']] = $row['category'];
		}
		return $options;
	}

	//COUPON TYPE OPTION FORM
	function couponOptionForm($val="") {
		global $zulu,$class_sale;

		$options = array();
		foreach($class_sale->coupon_types as $key=>$val) {
			$sel = ($val==$row['id']?"selected":NULL);
			$options[$key] = $val;
		}
		return $options;
	}

	//PRODUCT OPTION FORM
	function productOptionForm($val="") {
		global $zulu, $class_product;
		$options = array();
		$query = $class_product->product_data(['type'=>'product']);
		$query = $class_product->product_data(['type'=>'product','sys'=>'0','sort'=>'name ASC','type_variant'=>[0,1]]);
		$options[0] = "";
		foreach($query as $row) {
			$options[$row['id']] = stripslashes($class_product->name($row['id']));

			$child_data = $class_product->product_data(['type'=>'product','parent_id'=>$row['id'],'sort'=>'name ASC, sort ASC']);
			foreach($child_data as $child_row) {
                $options[$child_row['id']] = "-- ".stripslashes($class_product->name($child_row['id']));
            }
		}
		asort($options);
		return $options;
	}

	//EVENT OPTION FORM
	function eventOptionForm($all=true,$config=[]) {
		global $class_book,$class_user;
		$wSQL = [];
		if($config['upcoming']) {
			$wSQL = ['view'=>'1'];
		}
		$data = $class_book->event_data(array("user_id"=>$class_user->authorised->id,'view'=>($config['upcoming']?1:NULL)));

		$options = array();
		if($all) {
			$options[0] = "All";
		}
		foreach($data as $row) {
			$options[$row['id']] = stripslashes($row['name']);
		}
		return $options;
	}

	function eventTicketOptionForm($all=true,$config=[]) {
		global $class_book,$class_user;
		$wSQL = [];
		if($config['event_id'] > 0) {
			$wSQL = ['event_id'=>$config['event_id']];
		}
		$data = $class_book->event_ticket_type_data(array('field'=>array("id",'name'),"user_id='".$class_user->authorised->id."'")+$wSQL);
		$options = array();
		if($all) {
			$options[0] = "All";
		}
		foreach($data as $row) {
			$options[$row['id']] = stripslashes($row['name']);
		}
		return $options;
	}

	//FORM BUILD
	function admin_form_build($field,$config=[]) {
		global $class_user,$zulu;

		if(count($field)>0) {
			foreach($field as $name=>$data) {
				$field_key = $data['field']['name'];
				$field_split = explode("[",$field_key);
				if(count($field_split)>1) {
					$field_split[1] = str_replace("]","",$field_split[1]);
					$val_to_check = $_POST[$field_split[0]][$field_split[1]];
				} else {
					$val_to_check = $_POST[$field_key];
				}

				$value = ($val_to_check!=NULL?$val_to_check:$data['field']['value']);
				$html[] = (!isset($config['columns'])||(isset($config['columns'])&&!$config['columns'])?"<div class=\"".(count($data['class'])>0?implode(" ",$data['class']):implode(" ",$config['class']))."\">":NULL)."
					<div class=\"form-group\">
						<label>".$data['label'].($data['required']?' <em>*</em>':NULL).($data['help']!=NULL?" ".$this->icon_help($data['help']):NULL)."</label>
						".(count($data['field'])>0?$this->input_html($data['field']['type'],$data['field']['name'],$value,$data['field']['config']):NULL).$data['html']."
					</div>
				".(!isset($config['columns'])||(isset($config['columns'])&&!$config['columns'])?"</div>":NULL);
			}
        }

		return (count($html)>0?implode("\n",$html):NULL);
	}

	//MAKE ARRAY
	function make_array($data,$config) {
		foreach($data as $id=>$row) {
			$ret[$id] = $row[$config['label']];
		}
		return $ret;
	}

	function country_option() {
		$country_list = array(
			"New Zealand",
            "Australia",
            "--",
			"Afghanistan",
			"Albania",
			"Algeria",
			"Andorra",
			"Angola",
			"Antigua and Barbuda",
			"Argentina",
			"Armenia",
			"Australia",
			"Austria",
			"Azerbaijan",
			"Bahamas",
			"Bahrain",
			"Bangladesh",
			"Barbados",
			"Belarus",
			"Belgium",
			"Belize",
			"Benin",
			"Bhutan",
			"Bolivia",
			"Bosnia and Herzegovina",
			"Botswana",
			"Brazil",
			"Brunei",
			"Bulgaria",
			"Burkina Faso",
			"Burundi",
			"Cambodia",
			"Cameroon",
			"Canada",
			"Cape Verde",
			"Central African Republic",
			"Chad",
			"Chile",
			"China",
			"Colombi",
			"Comoros",
			"Congo (Brazzaville)",
			"Congo",
			"Costa Rica",
			"Cote d'Ivoire",
			"Croatia",
			"Cuba",
			"Cyprus",
			"Czech Republic",
			"Denmark",
			"Djibouti",
			"Dominica",
			"Dominican Republic",
			"East Timor (Timor Timur)",
			"Ecuador",
			"Egypt",
			"El Salvador",
			"Equatorial Guinea",
			"Eritrea",
			"Estonia",
			"Ethiopia",
			"Fiji",
			"Finland",
			"France",
			"Gabon",
			"Gambia, The",
			"Georgia",
			"Germany",
			"Ghana",
			"Greece",
			"Grenada",
			"Guatemala",
			"Guinea",
			"Guinea-Bissau",
			"Guyana",
			"Haiti",
			"Honduras",
			"Hungary",
			"Iceland",
			"India",
			"Indonesia",
			"Iran",
			"Iraq",
			"Ireland",
			"Israel",
			"Italy",
			"Jamaica",
			"Japan",
			"Jordan",
			"Kazakhstan",
			"Kenya",
			"Kiribati",
			"Korea, North",
			"Korea, South",
			"Kuwait",
			"Kyrgyzstan",
			"Laos",
			"Latvia",
			"Lebanon",
			"Lesotho",
			"Liberia",
			"Libya",
			"Liechtenstein",
			"Lithuania",
			"Luxembourg",
			"Macedonia",
			"Madagascar",
			"Malawi",
			"Malaysia",
			"Maldives",
			"Mali",
			"Malta",
			"Marshall Islands",
			"Mauritania",
			"Mauritius",
			"Mexico",
			"Micronesia",
			"Moldova",
			"Monaco",
			"Mongolia",
			"Morocco",
			"Mozambique",
			"Myanmar",
			"Namibia",
			"Nauru",
			"Nepa",
			"Netherlands",
			"New Zealand",
			"Nicaragua",
			"Niger",
			"Nigeria",
			"Norway",
			"Oman",
			"Pakistan",
			"Palau",
			"Panama",
			"Papua New Guinea",
			"Paraguay",
			"Peru",
			"Philippines",
			"Poland",
			"Portugal",
			"Qatar",
			"Romania",
			"Russia",
			"Rwanda",
			"Saint Kitts and Nevis",
			"Saint Lucia",
			"Saint Vincent",
			"Samoa",
			"San Marino",
			"Sao Tome and Principe",
			"Saudi Arabia",
			"Senegal",
			"Serbia and Montenegro",
			"Seychelles",
			"Sierra Leone",
			"Singapore",
			"Slovakia",
			"Slovenia",
			"Solomon Islands",
			"Somalia",
			"South Africa",
			"Spain",
			"Sri Lanka",
			"Sudan",
			"Suriname",
			"Swaziland",
			"Sweden",
			"Switzerland",
			"Syria",
			"Taiwan",
			"Tajikistan",
			"Tanzania",
			"Thailand",
			"Togo",
			"Tonga",
			"Trinidad and Tobago",
			"Tunisia",
			"Turkey",
			"Turkmenistan",
			"Tuvalu",
			"Uganda",
			"Ukraine",
			"United Arab Emirates",
			"United Kingdom",
			"United States",
			"Uruguay",
			"Uzbekistan",
			"Vanuatu",
			"Vatican City",
			"Venezuela",
			"Vietnam",
			"Yemen",
			"Zambia",
			"Zimbabwe"
		);

		//For each country
		$return = [];
		foreach($country_list as $val) {
			$return[$val] = $val;
		}
		return $return;
	}

    public static function validate_recaptcha($secret) {
        global $class_setting, $class_website;

        $result = ['success'=>false];

        if(isset($_POST['g-recaptcha-response']) && $_POST['g-recaptcha-response'] != null) {

            $score = (isset($class_setting->data['ws_module_google_captcha_api_score'])?$class_setting->data['ws_module_google_captcha_api_score']:$class_website->recaptcha_score_default);

            $recaptcha = new \ReCaptcha\ReCaptcha($secret);
            $resp = $recaptcha->setScoreThreshold($score)
                ->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
            if($resp->isSuccess()) {
                $result['success'] = true;
            }

        }

        return $result;
    }

	function timeZoneOptionsForm() {
		static $timezones = null;

		if ($timezones === null) {
			$timezones = [];
			$offsets = [];
			$now = new DateTime('now', new DateTimeZone('UTC'));

			foreach (DateTimeZone::listIdentifiers() as $timezone) {
				$now->setTimezone(new DateTimeZone($timezone));
				$offsets[] = $offset = $now->getOffset();
				$timezones[$timezone] = '(' . $this->format_GMT_offset($offset) . ') ' . $this->format_timezone_name($timezone);
			}

			array_multisort($offsets, $timezones);
		}

		return $timezones;
	}

	function format_GMT_offset($offset) {
		$hours = intval($offset / 3600);
		$minutes = abs(intval($offset % 3600 / 60));
		return 'GMT' . ($offset ? sprintf('%+03d:%02d', $hours, $minutes) : '');
	}

	function format_timezone_name($name) {
		$name = str_replace('/', ', ', $name);
		$name = str_replace('_', ' ', $name);
		$name = str_replace('St ', 'St. ', $name);
		return $name;
	}

}
