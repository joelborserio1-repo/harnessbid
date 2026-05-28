<?php
//(C)2016 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.1

//-- Form Post
class form_post {

	public $SQL_table = 'form_post';
	public $SQL_table_form = 'form';
	public $SQL_table_form_field = 'form_field';
    public $SQL_table_form_category = 'form_category';

	function __construct($load_config=[]) {
		global $db,$class_setting,$zulu;
		$this->db = $db;
		$this->config = $this->vars = new stdClass();
		$this->config->mode_version = true;
		$this->template = [];

        //Form Folder Root
		$this->form_folder = 'file/form/';
		$this->form_folder_rel = MAIN_rel.$this->form_folder;
		$this->form_folder_path = MAIN_path.$this->form_folder;

		$this->config->field_types = ['input'=>'Textbox','select'=>'Dropdown Box','checkbox'=>'Checkbox','radio'=>'Radio Button','textarea'=>'Textarea','number'=>'Number Textbox','date'=>'Datepicker','file'=>'File Upload','break'=>'Form Break','text'=>'Text'];
		$this->config->field_widths = ['100'=>'Full Width','75'=>'3/4 Width','66'=>'2/3 Width','50'=>'Half Width','33'=>'1/3 Width','25'=>'1/4 Width'];

		//-- form: contact us default
		$this->template['form_contact']['form'] = [
			'title'	=>	'Contact Us',
			'description'	=>	'Basic contact form for website.',
			'record_table'	=>	'',
			'object_table'	=>	'',
			'html_class'	=>	[],
			'label_title'	=>	"Contact Form",
			'label_description'	=>	NULL,
			'label_complete'	=>	"Thanks for contacting us, we will be in contact shortly.",
			'label_submit'	=>	"Send Message",
			'email_to_name'	=>	$class_setting->data['ws_contact_name'],
			'email_to'		=>	$class_setting->data['ws_contact_email'],
			'email_reply'	=>	'_email',
			'reference'		=>	"Enquiry from [name] ([email])",
			'conf_max_sess'	=>	0,
			'conf_hide_sub'	=>	true,
			'conf_google_captcha'			=>	true,
			'conf_google_captcha_key'		=>	$class_setting->data['ws_module_google_captcha_api_key'],
			'conf_google_captcha_secret'	=>	$class_setting->data['ws_module_google_captcha_api_secret'],
		];
		$this->template['form_contact']['field'] = [
				'name'	=>	['label'=>'Name','required'=>true,'input'=>['type'=>'input']],
				'email'	=>	['label'=>'Email','required'=>true,'input'=>['type'=>'input']],
				'phone'	=>	['label'=>'Phone','required'=>false,'input'=>['type'=>'input']],
				'message'	=>	['label'=>'Your message / enquiry','required'=>true,'input'=>['type'=>'textarea','config'=>['placeholder'=>'Enter a brief message with your enquiry...']]],
				/*'referal'		=>	['label'=>'How did you find out about us?','input'=>['type'=>'select','config'=>['option'=>[''=>'Select...','Word of Mouth'=>'Word of Mouth','Internet Search'=>'Internet Search','Family'=>'Family','Event / Conference'=>'Event / Conference','Previous Customer'=>'Previous Customer','Other'=>'Other']]]],*/
		];

		//-- form: quote default
		$this->template['form_quote']['form'] = [
			'title'	=>	'Get a Quote',
			'description'	=>	'Basic quote form for website.',
			'record_table'	=>	'',
			'object_table'	=>	'',
			'html_class'	=>	[],
			'label_title'	=>	"Quote Form",
			'label_description'	=>	NULL,
			'label_complete'	=>	"Thanks for requesting a quote, we will be in contact shortly.",
			'label_submit'	=>	"Send Request",
			'email_to_name'	=>	$class_setting->data['ws_contact_name'],
			'email_to'		=>	$class_setting->data['ws_contact_email'],
			'email_reply'	=>	'_email',
			'reference'		=>	"Quote request from [name] ([email])",
			'conf_max_sess'	=>	0,
			'conf_hide_sub'	=>	true,
			'conf_google_captcha'			=>	true,
			'conf_google_captcha_key'		=>	$class_setting->data['ws_module_google_captcha_api_key'],
			'conf_google_captcha_secret'	=>	$class_setting->data['ws_module_google_captcha_api_secret'],
		];
		$this->template['form_quote']['field'] = [
				'name'	=>	['label'=>'Name','required'=>true,'input'=>['type'=>'input']],
				'email'	=>	['label'=>'Email','required'=>true,'input'=>['type'=>'input']],
				'phone'	=>	['label'=>'Phone','required'=>false,'input'=>['type'=>'input']],
				'message'	=>	['label'=>'Your message / enquiry','required'=>true,'input'=>['type'=>'textarea','config'=>['placeholder'=>'Enter a brief message with your requirements...']]],
				'interest'	=>	['label'=>'What service are you interested in?','input'=>['type'=>'select','config'=>['option'=>[''=>'Select...','Other'=>'Other']]]],
				'referal'		=>	['label'=>'How did you find out about us?','input'=>['type'=>'select','config'=>['option'=>[''=>'Select...','Word of Mouth'=>'Word of Mouth','Internet Search'=>'Internet Search','Family'=>'Family','Event / Conference'=>'Event / Conference','Previous Customer'=>'Previous Customer','Other'=>'Other']]]],
		];

		//-- form: start horse listing default
		$this->template['form_horse_listing']['form'] = [
			'title'	=>	'Horse Listing',
			'description'	=>	'Basic listing form for website.',
			'record_table'	=>	'',
			'object_table'	=>	'',
			'html_class'	=>	[],
			'label_title'	=>	"Start listing your horse...",
			'label_description'	=>	NULL,
			'label_complete'	=>	"Thanks for contacting us, we will be in contact shortly.",
			'label_submit'	=>	"List Now",
			'email_to_name'	=>	$class_setting->data['ws_contact_name'],
			'email_to'		=>	$class_setting->data['ws_contact_email'],
			'email_reply'	=>	'_email',
			'reference'		=>	"Enquiry from [name] ([email])",
			'conf_max_sess'	=>	0,
			'conf_hide_sub'	=>	true,
			'conf_google_captcha'			=>	true,
			'conf_google_captcha_key'		=>	$class_setting->data['ws_module_google_captcha_api_key'],
			'conf_google_captcha_secret'	=>	$class_setting->data['ws_module_google_captcha_api_secret'],
		];
		$this->template['form_horse_listing']['field'] = [
				'horse_name'	=>	['label'=>'Horse name','required'=>true,'input'=>['type'=>'input','config'=>['placeholder'=>'Please enter the name of your horse here...']]],
		];

		$this->template['form_listing_contact']['form'] = [
			'title'	=>	'Listing Contact Form',
			'description'	=>	'',
			'record_table'	=>	'',
			'object_table'	=>	'',
			'html_class'	=>	[],
			'label_title'	=>	"",
			'label_description'	=>	NULL,
			'label_complete'	=>	"Your message has been sent to the seller.",
			'label_submit'	=>	"Send",
			'email_to_name'	=>	$class_setting->data['ws_contact_name'],
			//'email_to'		=>	$class_setting->data['ws_contact_email'],
			'email_reply'	=>	'_email',
			'reference'		=>	"Enquiry from [name] ([email])",
			'conf_max_sess'	=>	0,
			'conf_hide_sub'	=>	true,
			'conf_google_captcha'			=>	true,
			'conf_google_captcha_key'		=>	$class_setting->data['ws_module_google_captcha_api_key'],
			'conf_google_captcha_secret'	=>	$class_setting->data['ws_module_google_captcha_api_secret'],
			'hide_labels'	=>	true,
		];
		$this->template['form_listing_contact']['field'] = [
			'listing'	=>	['label'=>'Listing Name','required'=>false,'input'=>['type'=>'hidden']],
			'name'	=>	['label'=>'Your Name','required'=>true,'input'=>['type'=>'input','config'=>['placeholder'=>'Your Name *']]],
			'email'	=>	['label'=>'Your Email Address','required'=>true,'input'=>['type'=>'input','config'=>['placeholder'=>'Your Email Address *']]],
			'phone'	=>	['label'=>'Your Phone Number','required'=>false,'input'=>['type'=>'input','config'=>['placeholder'=>'Your Phone Number']]],
			'message'	=>	['label'=>'Your message to the seller','required'=>true,'input'=>['type'=>'textarea','config'=>['placeholder'=>'Your message to the seller... *']]],
			'email_to'	=>	['label'=>'','required'=>false,'input'=>['type'=>'hidden','config'=>[]]],
		];

		//-- Pre Load Form
		if(count($load_config)>0) {
			$form_data = $this->form_data($load_config);
			$zulu->vars->form_post->form_load = $form_data;
			$zulu->vars->form_post->form_id = $form_data['id'];
			$zulu->vars->form_post->form_tag = NULL;

			if($load_config['tag']!=NULL) {
				$zulu->vars->form_post->form_tag = $load_config['tag'];
				$zulu->vars->form_post->form_load = $this->template[$load_config['tag']]['form'];
			}
		}

		//-- Form Posted?
		if($_POST['action']=='form_post_submit') {
			$this->vars->form_posted = true;
		} else {
			$this->vars->form_posted = false;
		}
	}
	function load_config($config=[]) {

		if($config['form']!=NULL) {
			$form = $config['form'];
		} else {
			$form = $_GET['Form'];
		}

		$FIELD = $this->template[$form]['field'];
		$FORM = $this->template[$form]['form'];

		//Set vars
		$this->form->field = $FIELD;
		$this->form->config = $FORM;

		return true;
	}
	function public_url($tag,$direct=false) {
		global $class_user;
		if(trim($class_user->authorised->token)==NULL) {
			$user_token = $class_user->user_data(['id'=>$class_user->authorised->id,'field'=>['token']]);
			$user_token = $user_token['token'];
		} else {
			$user_token = $class_user->authorised->token;
		}
		if($class_user->authorised->child_id>0) {
			$user_token = $class_user->user_data(['id'=>$class_user->authorised->id,'field'=>['token']]);
			$user_token = $user_token['token'];
		}
		$prefix = FE_url;
		if($direct) {
			$prefix = MAIN_url;
			$user_token = "submit/".$user_token;
		}

		return $prefix."form/".$user_token."/".$tag."/";
	}
	function form_post_new($config=array()) {
		global $class_user;
		$data['token'] = zulu::serial(16);
		$data['user_id'] = $class_user->authorised->id;
		$data['form_time'] = time();

		$query = "INSERT INTO ".$this->SQL_table." ".$this->db->build(2,array('token','form_time','user_id'),$data);
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function form_post_edit($id,$config=array()) {
		global $class_user,$class_cache;
		if($id<1) {
			$data = $this->form_post_new();
			$id = $data['id'];
		}

		$data['form_time_update'] = time();
		$fields[] = 'form_time_update';

		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}

		$query = "UPDATE ".$this->SQL_table." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}' AND user_id='".$class_user->authorised->id."'";

		if($this->db->query($query)) {
			$class_cache->dump('quote_count');
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function insert($form,$field_array,$config=[],$file_array=[]) {
		global $zulu,$class_user,$class_file;

		if(isset($config['form_array'])) {
			$form_config = $config['form_array'];
			$form_config = $form_config['form'];
		} else {
			$form_config = $this->template[$form]['form'];
		}
		$form_name = $form_config['title'];
		$form_name_caption = $form_config['description'];

		$output_array = array();
		foreach($field_array as $key=>$field) {

			if($key == 'email_to') {
				$form_config['email_to'] = $field['value'];
				continue;
			}
			$output_array[] = array($field[0],$_POST[$field[1]]);
			$email_out[] = "<tr><td valign=\"top\"><b>".$field['label']."</b></td><td valign=\"top\">".(is_array($field['value'])?implode(", ",$field['value']):$field['value'])."</td></tr>";

		}

		$query_INSERT = $this->db->query("INSERT INTO form_post (user_id,token,parent_id,form_name,form,form_name_caption,form_time,user_ip,object_id,object,user_agent) VALUES (".sprintf("'%s','%s','%s','%s','%s', '%s', '%s', '%s', '%s', '%s', '%s'",
		($config['user_id']>0?$config['user_id']:$class_user->authorised->id),
		$zulu->serial(16),
        ($form_config['archive_id']>0?$form_config['archive_id']:0),
		$form_name,
		$form,
		$form_name_caption,
		time(),
		$_SERVER['REMOTE_ADDR'],
		$config['object_id'],
		$config['object'],
		addslashes($_SERVER['HTTP_USER_AGENT'])
		).")");
		if($query_INSERT) {

			$insert_id = $this->db->insert_id;
			$this->id = $insert_id;
			foreach($field_array as $key=>$data) {
				if($key == 'email_to') {
					continue;
				}
				if(is_array($data['value'])) {
					$data['value'] = serialize($data['value']);
				}
				$this->db->query("INSERT INTO form_post_meta (identifier,field,field_label,value,version) VALUES ('".$insert_id."','".$key."','".$data['label']."','".$data['value']."',".time().")");
			}

			foreach($file_array as $field=>$file){
				$file_return = $class_file->file_upload_form_post($file['name'],$file['tmp_name'], NULL, '../form_post/'.$insert_id.'/');
				if($file_return['success']){
					$file_url = MAIN_url."file/form_post/".$insert_id."/".$file_return['name'];
					//Add file to email out
					$email_out[] = "<tr><td valign=\"top\"><b>".$file['label']."</b></td><td valign=\"top\"><a href=\"".$file_url."\">".$file_url."</a></td></tr>";
					//Add database record
					$this->db->query("INSERT INTO form_post_meta (identifier,field,field_label,value,version) VALUES ('".$insert_id."','".$field."','".$file['label']."','".$file_url."',".time().")");

				}
			}

			//-- email it?
			if($form_config['email_to']!=NULL) {
				if(is_array($form_config['email_to'])) {
					$em_arr = $form_config['email_to'];
				} else {
					$em_arr[] = $form_config['email_to'];
				}
				if($form_config['email_reply']!=NULL) {
					if($form_config['email_reply'][0]=='_') { // -- defines to use post variable
						$rt_field = substr($form_config['email_reply'],1);
						$rt_email = $field_array[$rt_field]['value'];
						$mailqry['reply'] = $rt_email;
					} else {
						$mailqry['reply'] = $form_config['email_reply'];
					}
				}

				$message = "<p>Hello".($form_config['email_to_name']!=NULL?" ".$form_config['email_to_name']:NULL).",<br><br>A new form submission was received.<br><br><b>Form Name</b> ".$form_config['title']."<br><b>Date Received</b> ".date("d/m/Y h:ia")."<br><br><b>Form Data</b></p><table class=\"nice\" cellpadding=\"2\">".implode('',$email_out)."</table><p><b>IP Address</b> ".$_SERVER['REMOTE_ADDR']."<br><b>Agent</b> ".$_SERVER['HTTP_USER_AGENT']."";

				$mailqry['client'] = true;
				foreach($em_arr as $email) {
					$zulu->mail_send($email,"New Form '".$form_name."' Received",$message,'',false,$mailqry);
				}
			}

			$_SESSION['form_post']['submit_count'][$form] += 1;
			if($form_config['conf_hide_sub']&&isset($form_config['conf_hide_sub'])) {
				$_SESSION['form_post']['hide'][$form] = true;
			}

			return true;
		} else {
			echo $this->db->error;exit;
			return false;
		}
	}
	function update($id,$form_name=NULL,$form_name_caption=NULL,$field_array=[],$config=[],$file_array=[]) {
		global $zulu,$class_file;

		if($form_name!=NULL) {
			$field_data['form_name'] = $form_name;
		}
		if($form_name_caption!=NULL) {
			$field_data['form_name_caption'] = $form_name_caption;
		}
		if($config['reference']!=NULL) {
			$field_data['form_reference'] = $config['reference'];
		}
		if(isset($config['parent_id'])) {
			$field_data['parent_id'] = $config['parent_id'];
		}
		if($config['object']!=NULL) {
			$field_data['object'] = $config['object'];
			$field_data['object_id'] = $config['object_id'];
		}
		$field_data['form_time_update'] = time();
		foreach($field_data as $key=>$val) {
			$fields[] = $key;
		}

		$output_array = array();
		foreach($field_array as $field) {
			$output_array[] = array($field[0],$_POST[$field[1]]);
		}

		if(count($fields)>0) {
			$query_INSERT = $this->db->query("UPDATE form_post SET ".$this->db->build(1,$fields,$field_data)." WHERE id = '".$id."'");
		} else {
			$query_INSERT = true;
		}
		if($query_INSERT) {

			$meta = $zulu->table_data("form_post_meta",0,['where'=>["identifier = '".$id."'"],'sort'=>'id ASC']);
			foreach($meta as $key=>$val) {
				$latest_meta[$val['field']] = $val['value'];
			}

			$this->id = $id;
			foreach($field_array as $key=>$data) {
				$unser = @unserialize($data['value']);
				if($data['value'] === 'b:0;' || $unser !== false) {
					$data['value'] = $unser;
				}
				if(is_array($data['value'])) {
					$data['value'] = serialize($data['value']);
				}
				if($latest_meta[$key]!=$data['value']) {
					$this->meta_update($this->id,$key,$data['value'],$data['label']);
				}
			}

			foreach($file_array as $field=>$file){
				$file_return = $class_file->file_upload_form_post($file['name'],$file['tmp_name'], NULL, '../form_post/'.$insert_id.'/');
				if($file_return['success']){
					$file_url = MAIN_url."file/form_post/".$insert_id."/".$file_return['name'];
					$this->meta_update($this->id,$field,$file_url,$file['label']);
				}
			}

			return true;
		} else {
			return false;
		}
	}
	function version_array() {
		global $zulu;

		$data = $this->data;
		$id = $data['id'];

		$meta = $zulu->table_data("form_post_meta",0,['field'=>['version'],'where'=>["identifier = '".$id."'"],'sort'=>'version DESC','group'=>'version']);
		foreach($meta as $row) {
			$array[] = $row['version'];
		}

		return $array;
	}
	function form_post_data($config=[]) {
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if($config['parent_id']!=NULL) {
			$sql_config['where'][] = "parent_id = '".$config['parent_id']."'";
		}
		if(isset($config['archive'])) {
			$sql_config['where'][] = "archive = '".$config['archive']."'";
		}
		if($config['token']!=NULL) {
			$config['first'] = true;
			$sql_config['where'][] = "token = '".$config['token']."'";
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		if($config['field']!=NULL) {
			$sql_config['field'] = $config['field'];
		}
        if(isset($config['row_start'])) {
            $sql_config['start'] = $config['row_start'];
        }
        if(isset($config['row_limit'])) {
            $sql_config['limit'] = $config['row_limit'];
        }
		if($config['sort']!=NULL) {
			$sql_config['sort'] = $config['sort'];
		} else {
			$sql_config['sort'] = 'form_post.id DESC';
		}
		if(!$config['ovr_user_id']) {
			$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		}
		return zulu::table_data($this->SQL_table,$id,$sql_config);
	}
	function load($config=[]) {
		global $zulu, $class_user, $class_website;

		if(!$config['ovr_user_id']) {
			$sql['where'][] = "user_id = '".$class_user->authorised->id."'";
		}
		if(count($config)<=0) {
			$id = $this->id;
		} else {
			if($config['token']!=NULL) {
				$sql['where'][] = "token = '".$config['token']."'";
				$sql['first'] = true;
			} elseif($config['form_id']>0) {
				$sql['where'][] = "form_id = '".$config['form_id']."'";
			} elseif($config['form']!=NULL) {
				$sql['where'][] = "form = '".$config['form']."'";
			} elseif($config['id']>0) {
				$id = $config['id'];
				$sql['first'] = true;
			}
			if(isset($config['filter'])) {
				if($config['filter']['object_table']!=''&&$config['filter']['object_id']>0) {
					//$object_meta = $zulu->meta_array($zulu->meta_value($config['filter']['object_table'],$config['filter']['object_id'],'form_%'),['multi'=>true]);
					$object_meta = $zulu->meta_array($zulu->table_data($config['filter']['object_table']."_meta",0,['where'=>['field LIKE "form_%"','identifier = '.$config['filter']['object_id']]]));

					foreach($object_meta as $row_id) {
						if(is_array($row_id)) {
							foreach($row_id as $row_sub_id) {
							$in_id[] = $row_sub_id;
							}
						} else {
							$in_id[] = $row_id;
						}
					}
					if(count($in_id)>0) {
						$sql['where'][] = "id IN(".implode(",",$in_id).")";
					}
					unset($config['filter']['object_table'],$config['filter']['object_id']);
				}
				foreach($config['filter'] as $fkey=>$fval) {
                    if($fkey == 'search') {
                        $search = strtolower($fval);
                        $sql['join'][] = "form_post_meta AS m ON form_post.id = m.identifier";
                        $sql['where'][] = "(MATCH(m.value) AGAINST ('".$search."' IN BOOLEAN MODE) OR m.value LIKE '%".$search."%')";
                        $sql['group'] = 'form_post.id';
                        $sql['field'] = ['form_post.*'];
                        //$sql['test'] = true;
                    } else {
                        $sql['where'][] = $fkey." = '".$fval."'";
                    }
				}
			}
		}
        if(isset($config['row_start'])) {
            $sql['start'] = $config['row_start'];
        }
        if(isset($config['row_limit'])) {
            $sql['limit'] = $config['row_limit'];
        }
		$sql['sort'] = ($config['sort']!=NULL?$config['sort']:'form_post.id ASC');
		$this->data = $zulu->table_data("form_post",$id,$sql);
		$id = $this->data['id'];

		$meta_where[] = "identifier = '".$id."'";
		if($config['timestamp']>0) {
			$meta_where[] = "version <= ".$config['timestamp'];
		}
		$meta = $zulu->table_data("form_post_meta",0,['where'=>$meta_where,'sort'=>'id ASC']);
        if($id  > 0) {
            $this->data['meta_raw'] = $meta;
        }
		foreach($meta as $key=>$val) {
			$unser = @unserialize($val['value']);
			if($val['value'] === 'b:0;' || $unser !== false) {
				$val['value'] = $unser;
			}
			$this->data['meta'][$val['field']] = $val['value'];
			if($config['set_post']&&!$this->vars->form_posted) {
				$_POST['field'][$this->data['form']][$val['field']] = $val['value'];
			}
		}
		if($config['set_post']) {
			$class_website->vars->form_data['form'] = $this->data;
			$class_website->vars->form_data['field'] = $_POST['field'][$this->data['form']];
			$this->form_update = $this->data;
		}
		return;
	}
	function delete($id) {
		global $class_user;
		$data = $this->form_post_data(['id'=>$id]);

		if($data['archive']>0) {
			$query = "UPDATE ".$this->SQL_table." SET parent_id = 0 WHERE parent_id = '".$id."'";
			if($this->db->query($query)) {
				//-- update
			}
		}

		$query = "DELETE FROM ".$this->SQL_table." WHERE id = '".$id."' AND user_id='".$class_user->authorised->id."'";
		if($this->db->query($query)) {
			$this->db->query("DELETE FROM ".$this->SQL_table."_meta WHERE identifier = '".$id."'");
			return true;
		} else {
			return false;
		}
	}
	function exists($val,$field='id') {
		$data = table_data('form_post',$val,['primary_field'=>$field,'first'=>true,'field'=>['id']]);
		return ($data['id']<=0?false:true);
	}
	function meta_update($id,$field,$value,$label=NULL) {
		$result = $this->db->mysqli->query("SELECT id FROM form_post_meta WHERE identifier = '$id' AND field = '$field'");
		if($result->num_rows>0 && !$this->config->mode_version) {
			$query = "UPDATE form_post_meta SET value = '".$value."', version = '".time()."' WHERE identifier = '$id' AND field = '$field'";
		} else {
			$query = "INSERT INTO form_post_meta (identifier,field,field_label,value,version) VALUES ('{$id}','{$field}','{$label}','{$value}',".time().")";
		}
		return ($this->db->query($query)?true:false);
	}
	function object_count($config=[]) {
		global $zulu;

		if($config['object_id']>0&&$config['object_table']!=NULL) {
			$meta_data = $zulu->meta_array($zulu->table_data($config['object_table']."_meta",0,['where'=>['field LIKE "form_%"','identifier = '.$config['object_id']]]));
		}
		return count($meta_data);
	}
	function object_count_link($config=[]) {
		global $zulu;

		$count = $this->object_count($config);
		return "<a href=\"".$zulu->link_page('form_post',['query'=>["filter[object_table]"=>$config['object_table'],"filter[object_id]"=>$config['object_id']]])."\" class=\"opt opt-warning\"><i class=\"fas fa-link\"></i> ".$count." Forms</a>";
	}

	function form_data($config=array()) {
		global $class_user,$zulu;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if($config['token_short']!=NULL) {
			$sql_config['where'][] = "token_short = '".$config['token_short']."'";
			$sql_config['first'] = true;
		}
		if($config['token']!=NULL) {
			$sql_config['where'][] = "token = '".$config['token']."'";
			$sql_config['first'] = true;
		}
		if($config['slug']!=NULL) {
			$sql_config['where'][] = "slug = '".$config['slug']."'";
			$sql_config['first'] = true;
		}
        if($config['parent_id']!=NULL) {
			$sql_config['where'][] = "parent_id = '".$config['parent_id']."'";
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		if($config['sort']!=NULL) {
			$sql_config['sort'] = $config['sort'];
		} else {
			$sql_config['sort'] = 'title ASC';
		}
		if(!$config['ovr_user_id']&&!$config['user_id']) {
			$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		}
		if(isset($config['user_token'])) {
			$udata = $class_user->user_data(['token'=>$config['user_token'],'field'=>['id']]);
			$sql_config['where'][] = "user_id = '".$udata['id']."'";
		}
		if(isset($config['user_id'])) {
			$sql_config['where'][] = "user_id = '".$config['user_id']."'";
		}

		return $zulu->table_data($this->SQL_table_form,$id,$sql_config);
	}
	function form_meta($id,$field=NULL) {
		return zulu::meta_value('form',$id,$field);
	}
	function form_delete($id,$identifier='id') {
		global $class_user;
		$query = "DELETE FROM ".$this->SQL_table_form." WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."'";
		if($this->db->query($query)) {
			if($identifier=='id') {
				$this->db->query("DELETE FROM ".$this->SQL_table_form_field." WHERE form_id = '".$id."'");
			}
			return true;
		} else {
			return false;
		}
	}
	function form_new($config=array()) {
		global $class_user,$zulu;
		$data['user_id'] = $class_user->authorised->id;
		$data['stat_add'] = time();
		$data['stat_update'] = time();
		$data['token'] = $zulu->serial();
		$data['token_short'] = strtoupper($zulu->serial(4));

		$query = "INSERT INTO ".$this->SQL_table_form." ".$this->db->build(2,array('token','token_short','stat_add','user_id','stat_update'),$data);

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function form_edit($id,$config=array()) {
		global $class_user,$zulu;

		$config['stat_update'] = time();
		if($id<1) {
			$data = $this->form_new();
			$id = $data['id'];
		}

		if($config['title']!=NULL) {
			$config['slug'] = $zulu->slug($config['title']);
		}

		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}

		$query = "UPDATE ".$this->SQL_table_form." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}'".($class_user->authorised->role!='admin'?"AND user_id='".$class_user->authorised->id."'":NULL);
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}

	function form_field_data($config=array()) {
		global $class_user,$zulu;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if(isset($config['form_id'])) {
			$sql_config['where'][] = "form_id = '".$config['form_id']."'";
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
		$data = $zulu->table_data($this->SQL_table_form_field,$id,$sql_config);
		return $data;
	}
	function form_field_delete($id,$identifier='id') {
		global $class_user;
		$query = "DELETE FROM ".$this->SQL_table_form_field." WHERE `{$identifier}` = '".$id."'";
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	function form_field_new($config=array()) {
		global $class_user,$zulu;
		$data['stat_add'] = time();
		$data['stat_update'] = time();

		$query = "INSERT INTO ".$this->SQL_table_form_field." ".$this->db->build(2,array('stat_add','stat_update'),$data);

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function form_duplicate($id,$new_data=[]) {
		global $zulu;

		$query = "INSERT INTO ".$this->SQL_table_form." (token, user_id, title, slug, description, reference, stat_add) SELECT '".$zulu->serial()."', user_id, CONCAT(title, ' (Copy)'), CONCAT(slug, '-".$zulu->serial(4)."'), description, reference, '".time()."' FROM ".$this->SQL_table_form." WHERE id = '".$id."'";
		if($this->db->query($query)) {
			$form_id = $this->db->insert_id;

			$query_field = "INSERT INTO ".$this->SQL_table_form_field." (form_id,name,slug,input,width,description,required,config,sort,stat_add) SELECT '".$form_id."', name, slug, input, width, description, required, config, sort, '".time()."' FROM ".$this->SQL_table_form_field." WHERE form_id = '".$id."'";
			if(!$this->db->query($query_field)) {
				return false;
			}

			if(count($new_data)>0) {

				unset($data,$fields);
				foreach($new_data as $key=>$val) {
					$data[$key] = $val;
					$fields[] = $key;
				}

				$query = "UPDATE ".$this->SQL_table_form." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}'";
				if(!$this->db->query($query)) {
					return false;
				}
			}
			return true;
		} else {
			return false;
		}
	}
	function form_field_edit($id,$config=array()) {
		global $class_user,$zulu;
		$config['stat_update'] = time();
		if($id<1) {
			$data = $this->form_field_new();
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

		$query = "UPDATE ".$this->SQL_table_form_field." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}'";
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function form_fields($id) {
		$line_data = $this->form_field_data(['form_id'=>$id]);
		return $line_data;
	}

	function form_build_array($id,$config=[]) {
		global $class_setting,$zulu;
		$fdata_query = ['id'=>$id];
		if(isset($config['ovr_user_id'])) {
			$fdata_query['ovr_user_id'] = true;
		}
		$form_row = $this->form_data($fdata_query);
		$form_meta = $zulu->meta_array($this->form_meta($form_row['id']));
		$fields = $this->form_fields($form_row['id']);

		$email_to = ($form_meta['email_admin']>0||!isset($form_meta['email_admin'])?$class_setting->data['contact_email']:NULL).($form_row['email']!=NULL?",".$form_row['email']:NULL);
		if(trim($email_to)==NULL) {
			$email_to = $class_setting->data['contact_email'];
		}

		$form_arr['form'] = [
			'title'				=>	stripslashes($form_row['title']),
			'description'		=>	stripslashes($form_row['description']),
			'slug'				=>	$form_row['slug'],
			'label_title'		=>	stripslashes($form_row['title']),
			'label_description'	=>	stripslashes($form_row['description']),
			'label_complete'	=>	($form_meta['valid_complete']!=NULL?stripslashes($form_meta['valid_complete']):"Thanks for contacting us, we will be in contact shortly."),
			'label_submit'		=>	($form_meta['label_form_submit']!=NULL?stripslashes($form_meta['label_form_submit']):"Send Message"),
			'email_to_name'		=>	$class_setting->data['contact_name'],
			'email_to'			=>	$email_to,
			'email_reply'		=>	'_email',
			'reference'			=>	stripslashes($form_row['reference']),
			'conf_max_sess'		=>	0,
			'conf_hide_sub'		=>	true,
			'conf_float'		=>	true,
			'conf_google_captcha'			=>	false,
			'conf_google_captcha_key'		=>	$class_setting->data['ws_module_google_captcha_api_key'],
			'conf_google_captcha_secret'	=>	$class_setting->data['ws_module_google_captcha_api_secret'],
            'archive_id'        =>  $form_meta['archive_id'],
		];

		foreach($fields as $field) {
			$form_arr['field'][$field['slug']] = ['id'=>$field['id'],'label'=>stripslashes($field['name']),'required'=>$field['required'],'required_all'=>$field['required_all'],'input'=>['type'=>$field['input'],'config'=>unserialize($field['config'])],'width'=>$field['width'],'description'=>stripslashes($field['description'])];
		}
		return $form_arr;
	}
	function form_process($config=[]) {
		global $zulu,$class_user;
		$form_name = $this->db->escape_string($_POST['form_name']);
		if(count($config)==0&&$zulu->vars->form_post->form_tag!=NULL) {
			$form_name = $zulu->vars->form_post->form_tag;
		}
		if(count($config)>0) {
			if($config['name']!=NULL) {
				$form_name = $config['name'];
			}
			if($config['id']) {
				$form_data = $this->form_data(['id'=>$config['id']]);
				$form_name = $form_data['slug'];
				$form_valid_skip = true;
				$form_array = $this->form_build_array($form_data['id'],['ovr_user_id'=>true]);
				$FORM = $form_array['form'];
				$FIELD = $form_array['field'];
				$exins['user_id'] = $form_data['user_id'];
			}
		} elseif(count($config)==0) {
			if($form_name!=NULL) {
				$fd_query = ['slug'=>$form_name,'ovr_user_id'=>true];
				if($config['user_token']!=NULL) {
					$fd_query['user_token'] = $config['user_token'];
				}
				if($this->config->user_token!=NULL) {
					$fd_query['user_token'] = $this->config->user_token;
				}
				$form_data = $this->form_data($fd_query);
			} else {
				$form_data = $zulu->vars->form_post->form_load;
			}
			if($form_data['id']>0) {
				$form_name = $form_data['slug'];
				$form_valid_skip = true;
				$form_array = $this->form_build_array($form_data['id'],['ovr_user_id'=>true]);
				$FORM = $form_array['form'];
				$FIELD = $form_array['field'];
				$exins['user_id'] = $form_data['user_id'];
			}
		} elseif(!isset($this->template[$form_name])) {
			$form_data = $this->form_data(['slug'=>$form_name,'user_id'=>($config['user_id']>0?$config['user_id']:$class_user->authorised->id)]);
			if($form_data['id']>0) {
				$form_name = $form_data['slug'];
				$form_valid_skip = true;
				$form_array = $this->form_build_array($form_data['id'],['ovr_user_id'=>true]);
				$FORM = $form_array['form'];
				$FIELD = $form_array['field'];
				$exins['user_id'] = $form_data['user_id'];
			}
		}
		if(!isset($this->template[$form_name])&&!$form_valid_skip) {
			$zulu->notification_set("This form is invalid '{$form_name}'.",2);
		} else {

			if(!isset($FORM)) {
				$FORM = $this->template[$form_name]['form'];
			}
			if(!isset($FIELD)) {
				$FIELD = $this->template[$form_name]['field'];
			}

			//-- Validate data
			foreach($FIELD as $key=>$data) {
				if(((is_array($_POST['field'][$form_name][$key])&&count($_POST['field'][$form_name][$key])<=0)||(!is_array($_POST['field'][$form_name][$key])&&trim($_POST['field'][$form_name][$key])==NULL))&&$data['required']) {
					$error_string = "The field '".$zulu->shorten($data['label'],40)."' is required.";
					if($data['input']['type']=='checkbox') {
						$error_string = "Please tick the '".$zulu->shorten($data['label'],40)."' checkbox.";
					}
					if($data['input']['type']=='radio') {
						$error_string = "Please select an option for '".$zulu->shorten($data['label'],40)."'.";
					}
					if($data['input']['config']['custom']['valid_msg_required']!=NULL) {
						$error_string = stripslashes($data['input']['config']['custom']['valid_msg_required']);
					}
					$error_log[] = $error_string;
					$die = true;
				}
				if($data['input']['type']=='checkbox'&&$data['required_all']>0&&count($data['input']['config']['option'])!=count($_POST['field'][$form_name][$key])) {
					$error_string = "Please tick ALL checkboxes for '".$zulu->shorten($data['label'],40)."'.";
					if($data['input']['config']['custom']['valid_msg_required']!=NULL) {
						$error_string = stripslashes($data['input']['config']['custom']['valid_msg_required']);
					}
					$error_log[] = $error_string;
					$die = true;
				}
			}

			//-- Captcha?
			if($FORM['conf_google_captcha']&&$FORM['conf_google_captcha_key']!=NULL&&$FORM['conf_google_captcha_secret']!=NULL&&!isset($_SESSION['form_post']['captcha_valid'][$form_name])) {
				if(isset($_SESSION['form_post']['captcha_valid'][$form_name])) {
					//--
				} else {
                    $recap_result = form::validate_recaptcha($FORM['conf_google_captcha_secret']);
                    if($recap_result['success']) {
                        $_SESSION['form_post']['captcha_valid'][$form_name] = $form_name;
                    } else {
                        $error_log[] = "We're unable to process your request due to suspected spam. Please try again or contact us using a different method.";
                        $die = true;
                    }
				}
			}

			//-- Check Max Submission
			if($_SESSION['form_post']['submit_count'][$form_name]>=$FORM['conf_max_sess']&&$FORM['conf_max_sess']>0) {
				$error_log[] = "You cannot submit this form more than ".$FORM['conf_max_sess']." time".($FORM['conf_max_sess']!=1?"s":NULL)." per session.";
				$die = true;
			}

			//-- Handle form save
			if(!$die) {

				//-- Objects
				if($config['object_id']>0) {
					$object = $config['object'];
					$object_id = $config['object_id'];
				}

				//-- Form data
				unset($data);
				foreach($_POST['field'][$form_name] as $key=>$val) {
					$data[$key] = ['label'=>$FIELD[$key]['label'],'value'=>$val];
				}

				$files_data = [];
				foreach($_FILES['field']['name'][$form_name] as $key=>$file_name){
					$files_data[$key] = [
						'label'		=>$this->template[$form_name]['field'][$key]['label'],
						'name'		=>$_FILES['field']['name'][$form_name][$key],
						'type'		=>$_FILES['field']['type'][$form_name][$key],
						'tmp_name'	=>$_FILES['field']['tmp_name'][$form_name][$key],
						'error'		=>$_FILES['field']['error'][$form_name][$key],
						'size'		=>$_FILES['field']['size'][$form_name][$key],
					];
				}

				if($this->form_update['id']>0) {
					$form_row_id = $this->form_update['id'];
					//echo $form_row_id;
					//exit;
				}

				$form_post = new form_post;
				if($form_row_id>0) {
					$result = $form_post->update($form_row_id,$form_name,"",$data,[],$files_data);
				} else {
					$exins['object'] = $object;
					$exins['object_id'] = $object_id;
					if(isset($form_array)) {
						$exins['form_array'] = $form_array;
					}
					$form_post->insert($form_name,$data,$exins,$files_data);
				}
				$form_post->load();

				$form_id = $form_post->data['id'];
				$zulu->meta_update($FORM['record_table'],$_GET['RecordID'],$_GET['Form'],$form_id);

				$form_meta = $zulu->meta_array($this->form_meta($form_data['id']));
				if($form_meta['valid_complete_toggle_link']>0) {
					$validation_bt[] = "<a href=\"".$this->public_url($form_data['slug'],true)."?Submission=".$form_post->data['token']."\" target=\"_blank\"><i class=\"fas fa-edit\"></i> Click / copy the link here to edit this in the future</a>";
				}

				//-- Unset
				unset($_SESSION['form_post']['captcha_valid'][$form_name]);

				$zulu->notification_set("<i class=\"fas fa-thumbs-up\"></i> ".($FORM['label_complete']!=NULL?$FORM['label_complete']:"Thanks for filling in this form.").(count($validation_bt)>0?"<br>".implode("<br>",$validation_bt):NULL),1,['tag'=>'form_'.$form_name]);
				return ['success'=>true,'id'=>$form_id];
			} else {
				$zulu->notification_set("Sorry, please fix the items below:<br><br>".implode("<br>",$error_log),2,['tag'=>'form_'.$form_name]);
				return ['success'=>false,'reason'=>"Validation failed."];
			}
		}
	}

    function category_data($config=array()) {
		global $class_user,$zulu;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if($config['token']!=NULL) {
			$sql_config['where'][] = "token = '".$config['token']."'";
			$sql_config['first'] = true;
		}
		if($config['parent_id']!=NULL) {
			$sql_config['where'][] = "parent_id = '".$config['parent_id']."'";
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		if($config['sort']!=NULL) {
			$sql_config['sort'] = $config['sort'];
		} else {
			$sql_config['sort'] = 'title ASC';
		}
		if(!$config['ovr_user_id']&&!$config['user_id']) {
			$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		}
		if(isset($config['user_id'])) {
			$sql_config['where'][] = "user_id = '".$config['user_id']."'";
		}

		return $zulu->table_data($this->SQL_table_form_category,$id,$sql_config);
	}
	function category_delete($id,$identifier='id') {
		global $class_user;
		$query = "DELETE FROM ".$this->SQL_table_form_category." WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."'";
		if($this->db->query($query)) {
			if($identifier=='id') {
				$this->db->query("UPDATE ".$this->SQL_table_form." SET parent_id='0' WHERE parent_id = '".$id."'");
			}
			return true;
		} else {
			return false;
		}
	}
	function category_new($config=array()) {
		global $class_user,$zulu;
		$data['user_id'] = $class_user->authorised->id;
		$data['stat_add'] = time();
		$data['stat_update'] = time();
		$data['token'] = $zulu->serial();

		$query = "INSERT INTO ".$this->SQL_table_form_category." ".$this->db->build(2,array('token','stat_add','user_id','stat_update'),$data);

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function category_edit($id,$config=array()) {
		global $class_user,$zulu;

		$config['stat_update'] = time();
		if($id<1) {
			$data = $this->category_new();
			$id = $data['id'];
		}

		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}

		$query = "UPDATE ".$this->SQL_table_form_category." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}'".($class_user->authorised->role!='admin'?"AND user_id='".$class_user->authorised->id."'":NULL);
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}

}
