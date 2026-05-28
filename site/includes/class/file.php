<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Class: FILES
class file {

	public $SQL_table_file = 'file';
	public $SQL_table_file_meta = 'file_meta';

	function __construct($config=[]) {
		global $db,$zulu;
		$this->db = $db;
        $this->zulu = $zulu;
        $this->vars = new stdClass();
        $this->vars->uploadifive = new stdClass();
        $this->permission = new stdClass();
        $this->permission->types = [];

		//File System
		$this->file_root_rel = MAIN_rel."file/store/";
		$this->file_root_abs = MAIN_url."file/store/";
		$this->file_root_temp_rel = MAIN_rel."file/temp/";
		$this->file_root_user_rel = MAIN_rel."file/user/";
		$this->file_root = $_SERVER['DOCUMENT_ROOT'].$this->file_root_rel;
		$this->file_root_temp = $_SERVER['DOCUMENT_ROOT'].$this->file_root_temp_rel;
        $this->file_root_user = $_SERVER['DOCUMENT_ROOT'].$this->file_root_user_rel;
		$this->file_allowed_ext = array('jpg', 'jpeg', 'gif', 'png', 'pdf', 'docx', 'doc', 'xls', 'xlsx', 'psd', 'ai', 'txt', 'rtf');
		$this->image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'bmp', 'tiff', 'ico', 'webp'];

		//Uploadifive
		$this->vars->uploadifive->path_abs = MAIN_rel."includes/plugin/uploadifive/";

		//Permissions
		$this->permission->types['read'] = array("name"=>"Read","everyone"=>true);
		$this->permission->types['write'] = array("name"=>"Write","everyone"=>true);
		$this->permission->types['delete'] = array("name"=>"Delete","everyone"=>true);

		if(isset($_GET['FileRoot']) && $_GET['FileRoot']>0) {
			$this->root_id = $_GET['FileRoot'];
		} else {
			$this->root_id = 0;
		}

	}
	function permission_auth($file,$type,$user_id=0) {
		global $class_user;

		$return = true;
		if($user_id<=0){
			$user_id = $class_user->authorised->id;
		}

		//Check permission table

		//Check quick access

		//Check default
		if(!$this->permission->types[$type]['everyone']) {
			$return = false;
		}
		return $return;
	}
	function group_user_count($id) {
		$data = $this->group_relation_data(0,$id);
		return count($data);
	}
	function group_relation_move($user_id,$group_id) {
		$group_data = $this->group_relation_data($user_id);
		$group_data = $group_data[0];
		$query = "UPDATE file_user_group_relation SET group_id = '{$group_id}' WHERE id = '{$group_data['id']}'";
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	function group_relation_data($user_id=0,$group_id=0,$id=0) {
		$sql_config = array();
		if($user_id>0) {
			$sql_config['where'][] = "user_id = '".$user_id."'";
		}
		if($group_id>0) {
			$sql_config['where'][] = "group_id = '".$group_id."'";
		}
		if($id>0) {
			$sql_config['first'] = true;
		}
		$data_return = zulu::table_data("file_user_group_relation",0,$sql_config);
		return $data_return;
	}
	function group_relation_exists($user_id,$group_id) {
		$sql_config['where'][] = "user_id = '{$user_id}'";
		if($group_id>0) {
			$sql_config['where'][] = "group_id = '{$group_id}'";
		}
		$data_return = zulu::table_data("file_user_group_relation",0,$sql_config);

		if($data_return[0]['id']>0) {
			return true;
		} else {
			return false;
		}
	}
	function group_relation_add($user_id,$group_id) {
		if(!$this->group_relation_exists($user_id,$group_id)&&!$this->group_relation_exists($user_id,0)) {
			$query = "INSERT INTO file_user_group_relation (user_id,group_id) VALUES ('{$user_id}','{$group_id}')";
			if($this->db->query($query)) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	function group_relation_delete($id,$identifier='id') {
		$query = "DELETE FROM file_user_group_relation WHERE `{$identifier}` = '".$id."'";
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	function group_data($config=array()) {
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);

		if(count($config['field'])>0) {
			$sql_config['field'] = $config['field'];
		}
		$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		return zulu::table_data("file_user_group",$id,$sql_config);
	}
	function folder_url($config=array()) {
		if(!$config['Action']) {
			return $this->zulu->link_page('file',array('query'=>array('FileRoot'=>$config['root_id'])));
		} else {
			return $this->zulu->link_page('file',array('query'=>array('Action'=>$config['Action'],'FileRoot'=>$config['root_id'])));
		}
	}
	function file_user_authorised($config=array()) {
		if($config['file_data']!=NULL) {
			$file_data = $config['file_data'];
		} else {
			$file_data = $this->file_data(array('id'=>$config['id']));
		}
		if($this->permission_auth($file_data['id'],'read')) {
			return true;
		} else {
			return false;
		}
	}
	function file_download_url($config=array()) {
		if($config['file_data']!=NULL) {
			$file_data = $config['file_data'];
		} else {
			$file_data = $this->file_data(array('id'=>$config['id']));
		}
		//$filename = zulu::serial(24).".".$file_data['path_ext'];
		$filename = $file_data['path_file'];

		$path_orig = $this->file_root.$file_data['path'];
		$path_temp = $this->file_root_temp.$filename;

		$this->file_path_temp = $path_temp;
		copy($path_orig,$path_temp);
		return $this->file_root_temp_rel.$filename;
	}
	function file_download($token,$config=[]) {
		global $zulu;

		$custom = [
			'usr_ip'	=>	$_SERVER['REMOTE_ADDR'],
			'usr_agent'	=>	$_SERVER['HTTP_USER_AGENT'],
			'time'		=>	time(),
		];
		if($config['quick']) {
			$quick_data = $this->file_quick_data(['token'=>$token]+$config);

			//-- check conditions
			$result = $this->file_quick_allowed($quick_data['id'],$config);
			$token = $quick_data['file_token'];

			if(!$result['success']) {
				return ['success'=>false,'reason'=>$result['reason'],'vars_required'=>$result['vars_required']];
			}
		}

		$fdata = array('token'=>$token);
		if($config['public']) {
			$fdata['ovr_user_id'] = true;
		}
		$fdata['ovr_parent'] = true;
		$file_data = $this->file_data($fdata);
		if($file_data['id']<=0) {
			return ['success'=>false,'reason'=>"File data could not be loaded."];
		}
		if($this->file_user_authorised(array('file_data'=>$file_data))) {

			//-- Generate the temp file URL
			$url = $this->file_download_url(array('file_data'=>$file_data));

			//-- Log the actions
			$zulu->log_edit(0,['object'=>'file','object_id'=>$file_data['id'],'user_id'=>$file_data['user_id'],'custom'=>serialize($custom),'title'=>"User Downloaded File '".stripslashes($file_data['name'])."'",'data'=>"User IP: ".$_SERVER['REMOTE_ADDR']]);
			if(isset($quick_data)) {
				$zulu->log_edit(0,['object'=>'file_quick','object_id'=>$quick_data['id'],'user_id'=>$file_data['user_id'],'custom'=>serialize($custom),'title'=>"User Quick-Accessed File '".stripslashes($file_data['name'])."'",'data'=>"User IP: ".$_SERVER['REMOTE_ADDR']]);
			}

			//-- Redirect the client to url
			header("Location: ".$url);
			unlink($url);
			return array("success"=>true,"reason"=>NULL,"url"=>$url);
		} else {
			return array("success"=>false,"reason"=>"You do not have permission to download this file.");
		}
	}
	function file_data($config=array()) {
		global $class_user,$zulu;

		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);

		if(count($config['field'])>0) {
			$sql_config['field'][] = $config['field'];
		}
		if($config['token']!=NULL) {
			$sql_config['first'] = true;
			$sql_config['where'][] = "token = '".$config['token']."'";
		}
		if($config['name']!=NULL) {
			$sql_config['where'][] = "name = '".$config['name']."'";
		}
		if($config['object']!=NULL || isset($config['object'])) {
			$sql_config['where'][] = "object = '".$config['object']."'";
		}
		if($config['object_id']!=NULL) {
			$sql_config['where'][] = "object_id = '".$config['object_id']."'";
		}
		if($config['type']!=NULL) {
			$sql_config['where'][] = "type = '".$config['type']."'";
		}
		if($id<=0&&!$config['ovr_parent']) {
			if($config['root_id']>0) {
				$sql_config['where'][] = "parent_id = '".$config['root_id']."'";
			} else {
				$sql_config['where'][] = "parent_id = '0'";
			}
		}
		if(!$config['ovr_user_id']&&!$config['public']) {
			$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		}
		return $zulu->table_data('file',$id,$sql_config);
	}
	private function file_quick_allowed($id,$config=[]) {
		global $zulu,$class_user;
		$quick_data = $this->file_quick_data(['id'=>$id]+$config);

		$vars = [];
		$valid = true;
		if(trim($quick_data['password'])!=NULL) {
			if(trim($config['password'])==NULL) {
				$reasons[] = "";
				$vars[] = 'password';
			} else {
				if($config['password']!=$quick_data['password']) {
					$reasons[] = "The access password supplied is incorrect";
					$vars[] = 'password';
				}
			}
		}
		if(trim($quick_data['expire'])!=NULL) {
			if($quick_data['expire']<time()) {
				$reasons[] = "This download has expired";
			}
		}
		if(trim($quick_data['max_count'])!=NULL) {
			$log_data = $zulu->log_data(['object'=>'file_quick','object_id'=>$quick_data['id']]);
			if(count($log_data)>=$quick_data['max_count']) {
				$reasons[] = "Maximum number of downloads reached";
			}
		}
		$ips = unserialize(trim($quick_data['whitelist_ips']));
		if(count($ips) > 0 && !empty($ips)){
			$allowed_ip = false;
			foreach($ips as $ip){
				if(trim($ip) == trim($_SERVER['REMOTE_ADDR'])){
					$allowed_ip = true;
				}
			}
			if(!$allowed_ip){
				$reasons[] = "IP address not allowed.";
			}
		}

		if(count($reasons)>0) {
			$valid = false;
		}
		if($valid)
			return ['success'=>true,'reason'=>NULL];
		else
			return ['success'=>false,'reason'=>implode(", ",$reasons),'vars_required'=>$vars];
	}
	function file_quick_data($config=[]) {
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);

		if(count($config['field'])>0) {
			$sql_config['field'][] = $config['field'];
		}
		if($config['token']!=NULL) {
			$sql_config['first'] = true;
			$sql_config['where'][] = "token = '".$config['token']."'";
		}
		if($config['file_token']!=NULL) {
			$sql_config['first'] = true;
			$sql_config['where'][] = "file_token = '".$config['file_token']."'";
		}
		if($config['user_id']>0) {
			$sql_config['where'][] = "user_id = '".$config['user_id']."'";
		} elseif($config['public']) {
			//--skip
		} else {
			$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		}
		return zulu::table_data("file_quick",$id,$sql_config);
	}
	function file_delete($id,$identifier='id') {
		global $class_user;
		$file_data = $this->file_data(array('id'=>$id));
		$query = "DELETE FROM file WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."'";
		if($this->db->query($query)) {
			@unlink($this->file_root.$file_data['path']);
			return true;
		} else {
			return false;
		}
	}
	function file_delete_raw($filename,$path=NULL) {
		if(unlink($this->file_root.$path.$filename)) {
			return true;
		} else {
			return false;
		}
	}
	function file_quick_delete($id,$identifier='id') {
		global $class_user;
		$query = "DELETE FROM file_quick WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."'";
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	function file_new($config=array()) {
		global $class_user;
		$data['user_id'] = $class_user->authorised->id;
		$data['token'] = zulu::serial();
		$data['serial'] = zulu::serial(32);
		$data['stat_add'] = time();
		$data['stat_update'] = time();
		$data['name'] = stripslashes($config['name']);
		$data['parent_id'] = $config['parent_id'];
		$data['type'] = 'file';

		$query = "INSERT INTO file ".$this->db->build(2,array('name','parent_id','serial','token','stat_add','stat_update','type','user_id'),$data);

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function file_quick_new($config=array()) {
		global $class_user;
		$data['user_id'] = $class_user->authorised->id;
		$data['token'] = zulu::serial();;
		$data['file_token'] = $config['file_token'];
		$data['stat_add'] = time();
		$data['stat_update'] = time();

		$query = "INSERT INTO file_quick ".$this->db->build(2,array('token','file_token','stat_add','stat_update','user_id'),$data);

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function file_edit($id,$config=array()) {
		global $class_user;
		$data['stat_update'] = time();
		$fields[] = 'stat_update';


		if($id<1) {
			$data = $this->file_new();
			$id = $data['id'];
		}

		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}

		$query = "UPDATE file SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}' AND user_id='".$class_user->authorised->id."'";

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function file_button($id,$config=[]) {
		$config['id'] = $id;
		if($config['public']) {
			$config['ovr_user_id'] = true;
		}
		$file_data = $this->file_data($config);
		if($config['public']) {
			return MAIN_url."file/download/".$file_data['token']."/";
		} else {
			return $this->zulu->link_page('file',array('query'=>array('Token'=>$file_data['token'],'Action'=>'download')));
		}
	}
	function file_quick_url($id,$config=[]) {
		global $zulu;
		$config['id'] = $id;
		if($config['public']) {
			$config['ovr_user_id'] = true;
		}
		$file_data = $this->file_quick_data($config);
		if($config['public']) {
			return MAIN_url."file/download/q/".$file_data['token']."/";
		} else {
			return $this->zulu->link_page('file',array('query'=>array('Token'=>$file_data['token'],'Action'=>'download')));
		}
	}
	function file_meta($id) {
		return zulu::meta_value("file",$id);
	}
	function delete($id,$identifier='id') {
		return $this->file_delete($id,$identifier);
	}
	function file_size($file) {
		$size = filesize($this->file_root.$file);
		$base = log($size) / log(1024);
		$suffix = array("", "KB", "MB", "GB", "TB");
		$suffix = $suffix[floor($base)];
		return number_format(pow(1024, $base - floor($base)),1) . $suffix;
	}
	function file_icon($type,$extension='') {
		switch($type) {
			case 'file':
			$icon = "fa-file";
			break;
			case 'folder':
			$icon = "fa-folder";
			break;
		}
		return "{$icon}";
	}
	function file_label($type,$extension) {
		switch($type) {
			case 'file':
			$icon = "File";
			break;
			case 'folder':
			$icon = "Folder";
			break;
		}
		return "{$icon}";
	}
	function folder_tree($id,$data=array()) {
		$file_data = $this->file_data(array('id'=>$id));
		$data[] = $file_data['id'];
		$this->folder_tree->tree[] = $file_data['id'];
		if($file_data['parent_id']>0) {
			$this->folder_tree($file_data['parent_id'],$data);
		} else {
			$this->folder_tree->tree = array_reverse($this->folder_tree->tree);
			return true;
		}
	}
	function folder_breadcrumb($id,$action=NULL) {
		$this->folder_tree($id);
		foreach($this->folder_tree->tree as $row) {
			$file_data = $this->file_data(array('id'=>$row));
			if(!$action) {
				$this->zulu->nav->breadcrumb[$file_data['name']] = array("link"=>$this->zulu->link_page('file',array('query'=>array('FileRoot'=>$row))));
			} else {
				$this->zulu->nav->breadcrumb[$file_data['name']] = array("link"=>$this->zulu->link_page('file',array('query'=>array('Action'=>$action,'FileRoot'=>$row))));
			}
		}
		unset($this->folder_tree->tree);
		return true;
	}
	function file_upload_setpath($id,$path) {
		global $class_user;
		$new_path = basename($path);
		if($this->db->query("UPDATE file SET path = '{$new_path}', path_ext = '".$this->extension($new_path)."' WHERE id = '{$id}' AND user_id='".$class_user->authorised->id."'")) {
			return true;
		} else {
			return false;
		}
	}
	function file_uploaded($id) {
		$file_data = $this->file_data(array('id'=>$id));
		$uploaddir = $this->file_root;
		$path = $file_data['path'];
		$uploadfile = $uploaddir . $path;
		if (file_exists($uploadfile)&&trim($path)!=NULL) {
			return true;
		} else {
			return false;
		}
	}
	function file_upload($id,$config=array()) {
		if($id>0) {
			$file_data = $this->file_data(array('id'=>$id));
		} else {
			$file_new = $this->file_new(array('parent_id'=>$config['parent_id']));
			$file_data = $this->file_data(array('id'=>$file_new['id']));
			$id = $file_data['id'];
			$new = true;
		}
		$uploaddir = $this->file_root;
		$file_name = basename($_FILES['file']['name']);
		$path = $file_data['serial'].".".$this->extension($file_name);
		$uploadfile = $uploaddir . $path;

		if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
			$this->file_upload_setpath($id,$path);
			if($new) {
				$this->file_edit($id,array('name'=>$file_name,'path_file'=>$file_name));
			} else {
				$this->file_edit($id,array('path_file'=>$file_name));
			}
			return ['id'=>$id];
		} else {
			return false;
		}
	}
	function file_upload_raw($field,$filename=NULL,$expath=NULL) {
		$filename = ($filename==NULL?zulu::serial():$filename);
		$uploaddir = $this->file_root;
		$file_name = basename($_FILES[$field]['name']);
		$extension = $this->extension($file_name);
		$path = $filename.".".$extension;
		$uploadfile = $uploaddir.$expath . $path;

		@mkdir($uploaddir.$expath);
		if (move_uploaded_file($_FILES[$field]['tmp_name'], $uploadfile)) {
			$this->file_upload_setpath($id,$path);
			if($extension != 'jpg') {
				move_uploaded_file($_FILES[$field]['tmp_name'], $uploaddir.$expath.$filename.".jpg");
			}
			return array('success'=>true,'path'=>$uploadfile,'name'=>$path);
		} else {
			return false;
		}
	}

	function file_upload_form_post($FILE_name,$FILE_tmp_name,$filename=NULL,$expath=NULL) {
		//$FILE_name is the name from the $_FILES array, $FILE_tmp_name is the tmp_name from the $_FILES
		$filename = ($filename==NULL?zulu::serial():$filename);
		$uploaddir = $this->file_root;
		$file_name = basename($FILE_name);
		$extension = $this->extension($file_name);
		$path = $filename.".".$extension;
		$uploadfile = $uploaddir.$expath . $path;

		@mkdir($uploaddir.$expath);
		if (move_uploaded_file($FILE_tmp_name, $uploadfile)) {
			$this->file_upload_setpath($id,$path);
			if($extension != 'jpg') {
				move_uploaded_file($FILE_tmp_name, $uploaddir.$expath.$filename.".jpg");
			}
			return array('success'=>true,'path'=>$uploadfile,'name'=>$path);
		} else {
			return false;
		}
	}

	function file_upload_raw_text($text,$filename,$expath=NULL) {
		$uploaddir = $this->file_root;
		$file_name = basename($filename);
		$path = $filename;
		$uploadfile = $uploaddir.$expath . $path;
		$handle = fopen($uploadfile,"w+");

		if(fwrite($handle,$text)) {
			@chmod($uploadfile,0775);
			$this->file_upload_setpath($id,$path);
			return array('success'=>true,'path'=>$uploadfile,'name'=>$path);
		} else {
			return false;
		}
	}
	function extension($file) {
		//$splitted = explode('\.',$file);
		//$splitcount = count($splitted)-1;
		//$ext = $splitted[$splitcount];
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		return strtolower($ext);
	}

	function embed_table($config=array()) {
		global $zulu;

		$public = ($config['public']?true:false);

		$table_column[] = array("Type",array('class'=>array('')));
		$table_column[] = array("Name",array('class'=>array('center')));
		$table_column[] = array("Kind",array('class'=>array('center')));
		$table_column[] = array("Size",array('class'=>array('')));
		if(!$public) {
			$table_column[] = array("Direct Link <i class=\"fas fa-arrow-right color-grey\" title=\"This is the direct link, this can be used for websites.\"></i>",array('class'=>array('')));
			$table_column[] = array("Share Link <i class=\"fas fa-link color-grey\" title=\"This integrates with the file system &amp; records downloads.\"></i>",array('class'=>array('')));
		}
		$table_column[] = array("Actions",array('class'=>array('right text-right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		$root_id = ($config['root_id']>0?$config['root_id']:'0');
		$object_id = ($config['object_id']>0?$config['object_id']:'0');
		$object = ($config['object_id']!=NULL?$config['object']:'');
		$file_row = $this->file_data(array('root_id'=>$root_id,'object_id'=>$object_id,'object'=>$object));
		foreach($file_row as $row) {
			$file_meta = $this->file_meta($row['id']);
			$link = ($row['type']=='folder'?$this->folder_url(array('root_id'=>$row['id'],'public'=>$config['public'])):$this->file_button($row['id'],['public'=>$config['public']]));

			if($public) {
				$actions = ($row['type']=='folder'?NULL:"
				<a href=\"".$this->file_button($row['id'],['public'=>$config['public']])."\" title=\"Download\"><button class=\"btn btn-info btn-circle\" type=\"button\"><i class=\"fas fa-download\"></i></button></a> ");
			} else {
				$actions = ($row['type']=='folder'?NULL:"
					<a href=\"".$this->file_button($row['id'],['public'=>$config['public']])."\" title=\"Download\"><button class=\"btn btn-info btn-circle\" type=\"button\"><i class=\"fas fa-download\"></i></button></a> ")."
					<a href=\"".$zulu->link_page('file',array('query'=>array('id'=>$row['id'],'Action'=>'edit')))."\" title=\"Edit\"><button class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a>
					<a href=\"".$zulu->link_page('file',array('query'=>array('Action'=>'quick','Token'=>$row['token'])))."\" title=\"New Quick Access\"><button class=\"btn btn-success btn-circle\" type=\"button\"><i class=\"fas fa-bolt\"></i></button></a>
					<a class=\"confirm-delete\" href=\"".$zulu->link_page('file',array('query'=>array('id'=>$row['id'],'Action'=>'delete','Return'=>urlencode($zulu->link_page('')))))."\" title=\"Delete\"><button class=\"btn btn-danger btn-circle\" type=\"button\"><i class=\"fas fa-times\"></i></button></a>
				";
			}

			$trcols[] = array("<span class=\"far ".$this->file_icon($row['type'])."\"></span>");
			$trcols[] = array("<a href=\"".$link."\">{$row['name']}</a>");
			$trcols[] = array(strtoupper($row['path_ext']));
			$trcols[] = array($this->file_size($row['path']));
			if(!$public) {
				$trcols[] = array(($row['type']=='file'?$zulu->js_prompt_copy($this->file_link($row['id']),['shorten'=>30]):NULL));
				$trcols[] = array(($row['type']=='file'?$zulu->js_prompt_copy($this->file_button($row['id'],['public'=>true]),['shorten'=>30]):NULL));
			}
			$trcols[] = array($actions,array('class'=>array('right text-right')));

			$table_row[] = array("content" => $trcols);
			unset($trcols);
		}
		$class[] = 'file grid-table';
		if(count($config['class'])>0) {
			$class = array_merge($class,$config['class']);
		}
		$this->vars->total = count($file_row);
		return $zulu->table_render($table_row,0,array('class'=>implode(' ',$class)));
	}

	function file_link($id=0) {
		$data = $this->file_data(['id'=>$id]);
		return $this->file_root_abs.$data['path'];
	}

	function user_upload_path() {
		global $class_user;

        @mkdir($this->file_root_user);
        @mkdir($this->file_root_user.$class_user->authorised->id."/");
        @mkdir($this->file_root_user.$class_user->authorised->id."/uploads/");
        $dir = $this->file_root_user_rel.$class_user->authorised->id."/uploads/";

		return $dir;
	}

	function uploadifive_new($element,$config=[]) {
		global $zulu;

		//-- include uploadifive
		if(!$this->vars->uploadifive_included) {
			$this->vars->uploadifive_included = true;


			$zulu->template->js_file[] = $this->vars->uploadifive->path_abs."jquery.uploadifive.min.js";
			$zulu->template->css_file[] = $this->vars->uploadifive->path_abs."uploadifive.css";
		}

		//-- form security
		$timestamp = $zulu->serial(16);
		$config['post']['chk_time'] = $timestamp;
		$config['post']['chk_serial'] = md5('ZuLu2000' . $timestamp);

		//-- html
		if(count($config['post'])>0) {
			foreach($config['post'] as $p_key=>$p_val) {
				$form_data[] = "'".$p_key."' : ".(is_bool($p_val)?($p_val?'true':'false'):(is_numeric($p_val)?$p_val:"'".$p_val."'")).",";
			}
		}
		if(count($config['setting'])>0) {
			foreach($config['setting'] as $s_key=>$s_val) {
				$attribute[] = "'".$s_key."' : ".(is_bool($s_val)?($s_val?'true':'false'):$s_val).",";
			}
		}

		$config['queue_id'] = $zulu->serial(6);
		$zulu->template->jquery[] = "
		$('#upl_".$element."').uploadifive({
			'auto'             : true,
			".implode("\n",$attribute)."
			'formData'         : {
									".implode("\n",$form_data)."
								 },
			'queueID'          : 'queue_".$config['queue_id']."',
			'uploadScript'     : '".$this->vars->uploadifive->path_abs."upload.php',
			'onQueueComplete' : function(file) { ".$config['event']['complete']." ".($config['preview']?"file_preview('".$element."');":NULL)." },
            'onUploadComplete' : function(file, data) { console.log(file.name+': '+data); }
		});
		";
		$this->form_data[$element] = $config;
	}
	function uploadifive_input($element) {
		$form = new form;
		$setting = $this->form_data[$element];
		return "<div id=\"queue_".$setting['queue_id']."\"></div>".($setting['preview']?"<div class=\"preview\" id=\"preview_".$element."\"></div>":NULL).$form->input_html("file",$element,NULL,['id'=>'upl_'.$element,'multiple'=>$setting['setting']['multi']]);
	}

    function image_resize($file, $width=1024) {

        $fileParts = pathinfo($file);
        if(in_array(strtolower($fileParts['extension']),['jpg','jpeg','gif','png'])) {
            $set_width = $width;
            $image_size = getimagesize($file);
            if($image_size[0] > $set_width) {
                include_once(MAIN_path."includes/plugin/uploadifive/code/resize.php");
                $image = new SimpleImage();
                $image->load($file);
                $image->resizeToWidth($set_width);
                $image->save($file);
                return true;
            }
        }

        return false;
    }

    function colour_extract($file) {
        include_once(MAIN_path."includes/plugin/colorextract/vendor/autoload.php");
        $palette = League\ColorExtractor\Palette::fromFilename($file);
        $hex_data = [];
        foreach($palette->getMostUsedColors(5) as $color => $count) {
            $hex_val = League\ColorExtractor\Color::fromIntToHex($color);
            if(!in_array($hex_val,['#FFFFFF','#000000'])) {
                $hex_data[] = $hex_val;
            }
        }
        return $hex_data;
    }

    function convertImageToWebP($source, $destination=null, $quality=80) {
        if(function_exists('imagewebp')) {
            $pathinfo = pathinfo($source);
            if($destination == null) {
                $destination = $pathinfo['dirname']."/".$pathinfo['filename'].".webp";
            }
            $extension = strtolower($pathinfo['extension']);
            if($extension == 'jpeg' || $extension == 'jpg') {
                $image = imagecreatefromjpeg($source);
            } elseif($extension == 'gif') {
                $image = imagecreatefromgif($source);
            } elseif ($extension == 'png') {
                $image = imagecreatefrompng($source);
            }
            if($image != null) {
                return imagewebp($image, $destination, $quality);
            }
        }
    	return false;
    }

}
