	</div>

	<?php if(count($zulu->template->html_body_close) > 0) { ?>
	<?php foreach($zulu->template->html_body_close as $html) { ?>
	<?php echo $html; ?>
	<?php } ?>
	<!--end html body-->
	<?php } ?>

</div>

<?php

//-- Load relevant FOOTER file
if(file_exists(MAIN_path.FE_crm.$class_user->authorised->file_web_path.'footer.tpl')) {
	$class_cache->save('ws_footer_file',MAIN_path.FE_crm.$class_user->authorised->file_web_path.'footer.tpl');
} else {
	$class_cache->save('ws_footer_file',dirname(__FILE__)."/default/footer.tpl");
}
include($class_cache->load('ws_footer_file'));

?>

<?php if(file_exists(FE_abs."template/default/assets/fonts/stylesheet.css")) { ?>
<link type="text/css" rel="stylesheet" href="<?php echo FE_tpl_rel; ?>assets/fonts/stylesheet.css"/>
<?php } ?>

<?php if(count($zulu->template->html_foot) > 0) { ?>
<?php foreach($zulu->template->html_foot as $html) { ?>
<?php echo $html; ?>
<?php } ?>
<!--end html foot-->
<?php } ?>

<?php if($zulu->template->script_foot != NULL) { ?>
<?php echo $zulu->template->script_foot; ?>
<!--end script foot-->
<?php } ?>

<?php include dirname(__FILE__)."/js.php"; ?>

<?php if(defined('DEBUG_mode') && DEBUG_mode) { echo $debugbarRenderer->render(); } ?>

</body>
</html>
