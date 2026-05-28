<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Define
$TPL_head_ovr = ($TPL_head_ovr!=NULL?$TPL_head_ovr:($zulu->template->head_file!=NULL?$zulu->template->head_file:"head.php"));
$TPL_body_ovr = ($TPL_body_ovr!=NULL?$TPL_body_ovr:($zulu->template->body_file!=NULL?$zulu->template->body_file:"body.php"));

define(TPL_head,TPL_root.$TPL_head_ovr);
define(TPL_body,TPL_root.$TPL_body_ovr);
global $output;
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo PAGE_title; ?><?php echo MAIN_name; ?></title>
	 
	<?php include(TPL_head); ?>
    <?php echo $zulu->template->head; ?>

</head>

<body class="theme-<?php echo CRM_theme; ?> mode-<?php echo MASTER_mode; ?> <?php echo implode(" ",$zulu->template->body_class); ?>">

	<?php include(TPL_body); ?>

</body>

</html>
