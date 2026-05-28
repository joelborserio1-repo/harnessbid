<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Menu

class menu {
	function __construct() {
	}
	function compile_menu($menu_data) {
		$menu = [];
		foreach($menu_data as $section=>$section_data) {
			foreach($section_data as $data) {
				$append = array(
					"icon"	=>	(isset($data['icon'])?$data['icon']:NULL),
					"label"	=>	(isset($data['label'])?$data['label']:NULL),
					"link"	=>	(isset($data['link'])?$data['link']:NULL),
					"target"	=>	(isset($data['target'])?$data['target']:NULL),
					"title"	=>	(isset($data['title'])?$data['title']:NULL),
				);
				if(count($data['option'])>0) {
					foreach($data['option'] as $data) {
						if($data==NULL) {
							continue;
						}
						$append['option'][] = array(
							"icon"	=>	(isset($data['icon'])?$data['icon']:NULL),
							"label"	=>	(isset($data['label'])?$data['label']:NULL),
							"link"	=>	(isset($data['link'])?$data['link']:NULL),
							"target"	=>	(isset($data['target'])?$data['target']:NULL),
							"title"	=>	(isset($data['title'])?$data['title']:NULL),
						);
					}
				}
				$menu[$section][] = $append;
			}
		}
		return $menu;
	}
}
$class_menu = new menu;
