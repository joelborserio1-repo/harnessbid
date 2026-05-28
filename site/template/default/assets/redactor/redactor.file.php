<?php

//SESSION 
session_start();

//INCLUDES
include(dirname(__FILE__)."/../../../../includes/loader.php");

//valid
function check_ext_valid($type,$tpl) {
	if($tpl=='image'&&($type == 'image/png'
	|| $type == 'image/jpg'
	|| $type == 'image/gif'
	|| $type == 'image/jpeg'
	|| $type == 'image/pjpeg')) {
		$valid = true;
	}
	if($tpl=='file'&&($type == 'application/msword'
	|| $type == 'text/plain'
	|| $type == 'application/pdf'
	|| $type == 'application/rtf'
	|| $type == 'video/avi'
	|| $type == 'text/html'
	|| $type == 'application/vnd.ms-excel'
	|| $type == 'application/msexcel'
	|| $type == 'application/x-ms-excel'
	|| $type == 'text/csv'
	|| $type == 'image/png'
	|| $type == 'image/jpg'
	|| $type == 'image/gif'
	|| $type == 'image/jpeg'
	|| $type == 'image/pjpeg')) {
		$valid = true;
	}
	return ($valid?true:false);
}
// input tag
if($_GET['template']=='file') {
    $template = 'file';
} else {
    $template = 'image';
}
$input_tag = 'file';

// files storage folder
$file_path = $class_file->user_upload_path();
$dir = DOC_root.'/'.substr($file_path,1);
@mkdir($dir);
$file_path .= $template."/";
$dir .= $template."/";
@mkdir($dir);

if(!is_array($_FILES[$input_tag]['name'])) {
    foreach($_FILES[$input_tag] as $key=>$val) {
        $_FILES[$input_tag][$key] = [$val];
    }
}

$return_array = [];

foreach($_FILES[$input_tag]['name'] as $key=>$file_name) {
    
    $_FILES[$input_tag]['type'][$key] = strtolower(($_FILES[$input_tag]['type'][$key]!=null?$_FILES[$input_tag]['type'][$key]:mime_content_type($file_name)));
    
    if (check_ext_valid($_FILES[$input_tag]['type'][$key],$template))
    {
        //ext
        $ext_name = $_FILES[$input_tag]['name'][$key];
        $ext = pathinfo($ext_name, PATHINFO_EXTENSION);

        // -- Set the File name
        if($_GET['name_random']>0) {
            $filename = md5(date('YmdHis')).'.'.$ext;
            $file = $dir.$filename;
        } else {
            $name_parts = explode('.',$ext_name);
            array_pop($name_parts);
            $filename = implode('.',$name_parts)."-".$zulu->serial(5).".".$ext;
            $file = $dir.$filename;
        }
        if($_GET['save_action']=='post') {
            //-- do custom code if needed, i.e. special folder if POST	
        }

        // copying
        move_uploaded_file($_FILES[$input_tag]['tmp_name'][$key], $file);

        // displaying file
        $return_array['file-'.($key+1)] = array('url' => $file_path.$filename,'id'=>$filename);

        // rebuild JSON
        foreach(glob($dir."*") as $fkey=>$file) {
            if(strstr($file,".json") || !is_file($file) || !check_ext_valid(mime_content_type($file),$template)) {
                continue;
            }
            $path_parts = pathinfo($file);
            $size = filesize($file);
            if($size < '1024') {
                $file_size = $size."B";
            } elseif($size < '1048576') {
                $file_size = number_format(($size/1024),2)."KB";
            } else {
                $file_size = number_format((($size/1024)/1024),2)."MB";
            }
            $json_array[] = ['title'=>$path_parts['basename'],'url'=>stripslashes($file_path.$path_parts['basename']),'id'=>$fkey,'size'=>$file_size];
        }
        if(count($json_array) > 0) {
            $fp = fopen($dir.$template.'.json', 'w');
            fwrite($fp, json_encode($json_array,JSON_UNESCAPED_SLASHES));
            fclose($fp);
        }

    }    
    
}

// send filename back to REDACTOR
echo stripslashes(json_encode($return_array));
exit;

?>