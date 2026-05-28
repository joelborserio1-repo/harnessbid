<?php
//--CACHE
class cache {

	function __construct() {
		if(isset($_GET['CacheDump']) && $_GET['CacheDump']!=NULL) {
			$this->dump($_GET['CacheDump']);
		}
	}
	function dump($tag) {
		if($tag=='all') {
			unset($_SESSION['cache']);
		} else {
			unset($_SESSION['cache'][$tag]);
		}
		return;
	}
	function save($tag,$content,$expire=0) {
		$_SESSION['cache'][$tag] = $content;
		if($expire>0) {
			$_SESSION['cache_expire'][$tag] = $expire;
		}
		return;
	}
	function load($tag) {
		return $_SESSION['cache'][$tag];
	}
	function exists($tag) {
		if(isset($_SESSION['cache_expire'][$tag])&&$_SESSION['cache_expire'][$tag]<time()) {
			unset($_SESSION['cache'][$tag]);
		}
		return (is_array($_SESSION['cache'][$tag])!=NULL||trim($_SESSION['cache'][$tag])?true:false);
	}

}
