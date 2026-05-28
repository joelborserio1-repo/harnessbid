<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Data Compiler
class compiler {
	function __construct() {
		global $zulu;
		global $class_menu;

		//Footer Compile

		//Menu Compile
		$zulu->nav->compiled = $class_menu->compile_menu($zulu->nav->menu);
		define('PAGE_title', (is_array($zulu->nav->title)?implode(" &raquo; ",$zulu->nav->title):$zulu->nav->title)." &raquo; ");
	}
}
$class_compiler = new compiler();

?>
