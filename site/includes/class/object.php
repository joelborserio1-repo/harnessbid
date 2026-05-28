<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Class: RULE
class objects {
	
	public $SQL_table = 'object';
	public $SQL_table_type = 'object_type';
	public $SQL_table_type_field = 'object_type_field';
	public $SQL_table_field_value = 'object_field_value';
	
	function __construct($config=[]) {
		global $db;
		$this->db = $db;
	}
	
	function object_data($config=array()) {
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if($config['client_id']!=NULL) {
			$sql_config['where'][] = "client_id = '".$config['client_id']."'";
		}
		if($config['object_type_id']!=NULL) {
			$sql_config['where'][] = "object_type_id = '".$config['object_type_id']."'";
		}
		if($config['token']!=NULL) {
			$sql_config['where'][] = "token = '".$config['token']."'";
			$config['first'] = true;
		}
		if($config['status']!=NULL || $config['status']=='0') {
			$sql_config['where'][] = "status = '".$config['status']."'";
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		//$sql_config['sort'] = 'object ASC, status DESC';
		if(!$config['ovr_user_id']) {
			$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		}
		if($config['ovr_user_id'] && $config['user_id']>0) {
			$sql_config['where'][] = "user_id = '".$config['user_id']."'";
		}
		return zulu::table_data($this->SQL_table,$id,$sql_config);
	}
	function delete($id,$identifier='id',$disable=false) {
		global $class_user;
		if($disable) {
			$query = "UPDATE ".$this->SQL_table." SET status = 0 WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."'";
		} else {
			$query = "DELETE FROM ".$this->SQL_table." WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."'";
		} 
		if($this->db->query($query)) {
			return true;
		} else {
			return false;	
		}
	}
	function object_new($config=array()) {
		global $class_user;
		$data['user_id'] = $class_user->authorised->id;
		$data['stat_add'] = time();
		$data['token'] = zulu::serial();
		
		$query = "INSERT INTO ".$this->SQL_table." ".$this->db->build(2,array('token','stat_add','user_id'),$data);
		
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function object_edit($id,$config=array()) {
		global $class_user;
		if($id<1) {
			$data = $this->object_new();
			$id = $data['id'];
		}
		
		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}
		$fields[] = 'stat_update';
		$data['stat_update'] = time();
		
		$query = "UPDATE ".$this->SQL_table." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}' AND user_id='".$class_user->authorised->id."'";
		
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	
	
	function object_type_data($config=array()) {
		global $class_user;
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
		if($config['first']) {
			$sql_config['first'] = true;
		}
		$sql_config['sort'] = '';
		if(!$config['ovr_user_id']) {
			$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		}
		if($config['ovr_user_id'] && $config['user_id']>0) {
			$sql_config['where'][] = "user_id = '".$config['user_id']."'";
		}
		//$sql_config['true'] = true;
		return zulu::table_data($this->SQL_table_type,$id,$sql_config);
	}
	function object_type_delete($id,$identifier='id',$disable=false) {
		global $class_user;
		if($disable) {
			$query = "UPDATE ".$this->SQL_table_type." SET status = 0 WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."'";
		} else {
			$query = "DELETE FROM ".$this->SQL_table_type." WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."'";
		} 
		if($this->db->query($query)) {
			return true;
		} else {
			return false;	
		}
	}
	function object_type_new($config=array()) {
		global $class_user;
		$data['user_id'] = $class_user->authorised->id;
		$data['stat_add'] = time();
		
		$data['token'] = zulu::serial();
		
		$query = "INSERT INTO ".$this->SQL_table_type." ".$this->db->build(2,array('token','stat_add','user_id'),$data);
		
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function object_type_edit($id,$config=array()) {
		global $class_user;
		if($id<1) {
			$data = $this->object_type_new();
			$id = $data['id'];
		}
		
		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}
		$fields[] = 'stat_update';
		$data['stat_update'] = time();
		
		$query = "UPDATE ".$this->SQL_table_type." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}' AND user_id='".$class_user->authorised->id."'";
		
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function object_type_field_data($config=array()) {
		global $class_user,$zulu;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if(isset($config['object_type_id'])) {
			$sql_config['where'][] = "object_type_id = '".$config['object_type_id']."'";
		}
		if($config['slug']!=NULL) {
			$sql_config['where'][] = "slug = '".$config['slug']."'";
			$sql_config['first'] = true;
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		if($config['sort']!=NULL) {
			$sql_config['sort'] = $config['sort'];
		} else {
			$sql_config['sort'] = 'sort ASC, stat_add ASC';
		}
		$data = $zulu->table_data($this->SQL_table_type_field,$id,$sql_config);
		return $data;
	}
	function object_type_field_delete($id,$identifier='id') {
		global $class_user;
		$query = "DELETE FROM ".$this->SQL_table_type_field." WHERE `{$identifier}` = '".$id."'";
		if($this->db->query($query)) {
			return true;
		} else {
			return false;	
		}
	}
	function object_type_field_new($config=array()) {
		global $class_user,$zulu;
		$data['stat_add'] = time();
		$data['stat_update'] = time();
		
		$query = "INSERT INTO ".$this->SQL_table_type_field." ".$this->db->build(2,array('stat_add','stat_update'),$data);
		
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function object_type_field_edit($id,$config=array()) {
		global $class_user,$zulu;
		$config['stat_update'] = time();
		if($id<1) {
			$data = $this->object_type_field_new();
			$id = $data['id'];
		}
		
		if($config['name']!=NULL) {
			$config['slug'] = $zulu->slug($config['name'])."-".$id;
		}
		if($config['required_all']>0) {
			$config['required'] = 1;
		}
		
		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}
		
		$query = "UPDATE ".$this->SQL_table_type_field." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}'";
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function object_field_value_data($config=array()) {
		global $class_user,$zulu;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if(isset($config['object_id'])) {
			$sql_config['where'][] = "object_id = '".$config['object_id']."'";
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		if($config['sort']!=NULL) {
			$sql_config['sort'] = $config['sort'];
		} else {
			$sql_config['sort'] = 'stat_add ASC';
		}
		$data = $zulu->table_data($this->SQL_table_field_value,$id,$sql_config);
		return $data;
	}
	function has_custom_fields($object_type_id){
		$fields = $this->object_type_field_data(['object_type_id'=>$object_type_id, 'field'=>['id']]);
		if(count($fields)>0){
			return true;
		}else{
			return false;
		}
	}
	
	function form_build_array($object_type_id) {
		$fields = $this->object_type_field_data(['object_type_id'=>$object_type_id]);
		foreach($fields as $field) {
			$form_arr['field'][$field['slug']] = ['id'=>$field['id'],'label'=>stripslashes($field['name']),'required'=>$field['required'],'required_all'=>$field['required_all'],'input'=>['type'=>$field['input'],'config'=>unserialize($field['config'])],'width'=>$field['width'],'description'=>stripslashes($field['description'])];
		}
		return $form_arr;
	}
	
	
	function custom_field_build($object_type_id,$config=[]){
		
		global $zulu,$class_form_post,$class_cache;
		$form_edit = new form;
		
		$form_arr = $this->form_build_array($object_type_id);
		$FORM = $form_arr['form'];
		$FIELD = $form_arr['field'];
		$form_name = 'type_custom';
			
		if($_SESSION['form_post']['hide'][$form_name]) {
			$form_done = true;
			unset($_SESSION['form_post']['hide'][$form_name]);
		}

		if(!$form_done) {
    		$html .= $zulu->notification('',0,['tag'=>'form_'.$form_name]);
            $html .= "<div class=\"form-wrapper form-".$form_name."\">
            ".($config['form_wrapper_hide']&&isset($config['form_wrapper_hide'])?NULL:"<form method=\"post\" name=\"form\" id=\"form\" action=\"\" enctype=\"multipart/form-data\" >")."
            	<div class=\"form-block single".($FORM['conf_float']?' float':NULL)."\">";
			
			foreach($FIELD as $key=>$data) {
				$bt_extra = [];
				$data['input']['config']['class'][] = "input-".$data['id'];
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
					$html .= "<label>".$data['label'].($data['required']?" <em>*</em>":NULL)." {$dl} ".implode(" ",$bt_extra)."</label>";
					$html .= $form_edit->input_html($data['input']['type'],"field[".$form_name."][".$key."]",($_POST['field'][$form_name][$key]!=NULL?$_POST['field'][$form_name][$key]:($_GET['field'][$form_name][$key]!=NULL?$_GET['field'][$form_name][$key]:$data['default'])),$data['input']['config']+['id'=>'field-'.$key]);
				} else {
					$html .= "<label class=\"inline-input\">".$form_edit->input_html($data['input']['type'],"field[".$form_name."][".$key."]",($_POST['field'][$form_name][$key]!=NULL?$_POST['field'][$form_name][$key]:($_GET['field'][$form_name][$key]!=NULL?$_GET['field'][$form_name][$key]:$data['default'])),$data['input']['config'])." ".$data['label'].($data['required']?" <em>*</em>":NULL)." {$dl} ".implode(" ",$bt_extra)."</label>";
				}
				$html .= "</div>";
				unset($office_use);
			}
			
			$html .= "
                    ".($config['submit_hide']&&isset($config['submit_hide'])?'':"<div class=\"field submit\">
                    	".$form_edit->input_html("submit","submit",($FORM['label_submit']!=NULL?$FORM['label_submit']:"Submit Form"))."
                    	".$form_edit->input_html("hidden","action","form_post_submit")."
                    	".$form_edit->input_html("hidden","form_submit_token",$this->vars->form_data['form']['token'])."
                    	".$form_edit->input_html("hidden","form_name",$form_name)."
                    </div>")."
            	</div>
            ".($config['form_wrapper_hide']&&isset($config['form_wrapper_hide'])?NULL:"</form>")."
            </div>";
			} else {
    			$html .= $zulu->notification('',0,['tag'=>'form_'.$form_name]);
            }
		
		return $html;
	}
		
	
}