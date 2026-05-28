<?php
/*
UploadiFive
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
*/

define('MASTER_section','admin');

//-- Data Loader
include(dirname(__FILE__)."/../../loader.php");

// Set the uplaod directory
$uploadDir = MAIN_path.'file/'.str_replace("../","",$_POST['path']);

$result = ['output'=>[]];

// Custom actions
if($_POST['path_custom']!=NULL) {
	$uploadDir = $_SERVER['DOCUMENT_ROOT'].$_POST['path_custom'];
    $result['custom_upload_dir'] = $uploadDir;
}

// Set the allowed file extensions
$fileTypes = $class_file->file_allowed_ext; // Allowed file extensions

$verifyToken = md5('ZuLu2000' . $_POST['chk_time']);

if (!empty($_FILES) && $_POST['chk_serial'] == $verifyToken) {
	$tempFile   = $_FILES['Filedata']['tmp_name'];
	$fileParts = pathinfo($_FILES['Filedata']['name']);
    $extension = strtolower($fileParts['extension']);

	$name_arr = explode('.',$_FILES['Filedata']['name']);
	array_pop($name_arr);
	$_FILES['Filedata']['name'] = $zulu->slug(implode('',$name_arr)).".".$extension;

	$file_name = $_FILES['Filedata']['name'];
	$targetFile = $uploadDir . $file_name;

	// Validate the filetype
	if (in_array($extension, $fileTypes)) {

		// New ext?
		if($_POST['file_ext']!=NULL) {
			$fileParts['extension'] = $_POST['file_ext'];
		}

		// New name?
		if($_POST['file_name']!=NULL) {
			$file_name = $zulu->slug($_POST['file_name']) . "." . $extension;
			$targetFile = $uploadDir . $file_name;
            $result['output'][] = '-nn';
		}

        $result['file_name'] = $file_name;
        $result['upload_dir'] = $uploadDir;
        $result['extension'] = $extension;

		// Save the file
		if(!move_uploaded_file($tempFile, $targetFile)) {
            $result['output'][] = '-f';
		}

		//Meta save?
		if($_POST['action']!=NULL) {
			if($_POST['post_id']>0) {
				$post_data = $class_post->post_data(['id'=>$_POST['post_id']]);
				$post_tpl = $class_post->config->template[$post_data['type']];
			}

			if($_POST['action']=='post_main_image') { //-- main post image
				$zulu->meta_update('post',$_POST['post_id'],'image_main',$file_name);
			}
			if($_POST['action']=='post_gallery_image') { //-- main post image
				$newpost = $class_post->post_edit(0,['parent_id'=>$_POST['post_id'],'sort'=>'-1','type'=>'image','title'=>$_FILES['Filedata']['name']]);
				$post_data = $class_post->post_data(['id'=>$newpost['id']]);
				$newPath = $uploadDir."../".$post_data['token']."/".basename($targetFile);
				copy($targetFile,$newPath);
				@unlink($targetFile);
				$targetFile = $newPath;
				$zulu->meta_update('post',$newpost['id'],'image_main',$_FILES['Filedata']['name']);
				$class_post->post_status_change($newpost['id'],'published');
			}
			if($_POST['action']=='ws_favicon') {
				if($class_website->get_favicon()) {
                    $result['output'][] = '-favi ok';
				} else {
                    $result['output'][] = '-favi fail';
				}
			}
			if($_POST['action']=='ws_logo') {
                $hex_data = $class_file->colour_extract($targetFile);
				$class_setting->setting_edit('ws_logo_color',serialize($hex_data));
				$class_setting->setting_edit('ws_theme_master_colour',$hex_data[0]);
				$class_setting->setting_edit('ws_theme_master_shade',$zulu->colour_shade_adjust($hex_data[0],20));
				$class_setting->setting_edit('ws_theme_secondary_colour',$hex_data[1]);
				$class_setting->setting_edit('ws_theme_secondary_shade',$zulu->colour_shade_adjust($hex_data[1],20));
				$class_setting->setting_edit('ws_theme_third_colour',$hex_data[2]);
				$class_setting->setting_edit('ws_theme_fourth_colour',$hex_data[3]);

			} elseif($_POST['action'] == 'product_brand_image') {
                if($_POST['id'] > 0) {
                    $brand = ProductBrand::find($_POST['id']);
                    $brand->image = $file_name;
                    $colours = $class_file->colour_extract($targetFile);
                    if(count($colours) > 0 && $colours[0] != null) {
                        $brand->colour_default = $colours[0];
                        $result['output'][] = '-colourextract '.implode(',',$colours);
                    }
                    $brand->save();
                }

            } elseif($_POST['action'] == 'location_image') {
                if($_POST['location_id'] > 0) {
                    $location = Location::find($_POST['location_id']);
                    $location->image = $file_name;
                    $location->save();
                }
            }
		}

		//PHP Resizer
		if(in_array($extension,['jpg','jpeg','gif'])) {

			/* Orientation fix (if required) */
			$exif = exif_read_data($targetFile);
			if (!empty($exif['Orientation'])) {
                $result['output'][] = '-orient load';
				$imageResource = imagecreatefromjpeg($targetFile); // provided that the image is jpeg. Use relevant function otherwise
				switch ($exif['Orientation']) {
					case 3:
					$image = imagerotate($imageResource, 180, 0);
					break;
					case 6:
					$image = imagerotate($imageResource, -90, 0);
					break;
					case 8:
					$image = imagerotate($imageResource, 90, 0);
					break;
					default:
					$image = $imageResource;
				}
                $result['output'][] = '-orient is '.$exif['Orientation'];
				imagejpeg($image, $targetFile, 90);
			} else {
                $result['output'][] = '-orient skip';
			}

			/* Width Adjustment */
			$set_width = 1920;
			if($post_tpl['config']['image_max_width']>0) {
				$set_width = $post_tpl['config']['image_max_width'];
			}
            if($class_file->image_resize($targetFile, $set_width)) {
                $result['output'][] = '-resize w:'.$set_width;
            }

		}

        /*if($_POST['action'] == 'post_builder_image') { //-- main post image
            if($class_file->convertImageToWebP($targetFile)) {
                $result['output'][] = '-webp-success';
            } else {
                $result['output'][] = '-webp-fail';
            }
        }*/

        $result['output'][] = '-up';

	} else {

		// The file type wasn't allowed
        $result['output'][] = 'Invalid file type.';

	}
}

echo json_encode($result);
exit;

?>
