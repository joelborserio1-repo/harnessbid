<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Set MASTER Definitions
define('MASTER_section',"admin");

//-- Data Loader
include("../includes/loader.php");

//-- Theme Template Loader
include(TPL_root."index.php");

//-- Close Connection
mysqli_close($mysqli);

?>
