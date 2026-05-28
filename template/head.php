<!DOCTYPE html>
<html <?php echo $zulu->template->website_html['html_tag']; ?>>
<head>
	<?php echo $zulu->template->website_html['head_open']; ?>
	<?php include(dirname(__FILE__)."/meta.php"); ?>
	<!-- Tabler Icons -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
	<!-- Adobe Fonts (Proxima Nova + Trajan Pro) -->
	<link rel="stylesheet" href="https://use.typekit.net/krm5fua.css">
	<meta name="theme-color" content="#0D2B6B">
	<link rel="icon" type="image/svg+xml" href="/template/default/images/favicon.svg">
	<link rel="alternate icon" href="/template/default/images/favicon.ico">
	<?php echo $zulu->template->website_html['head_close']; ?>
	<!-- HarnessBid refresh CSS loaded last so profile/theme CSS cannot override the new components. -->
	<link rel="stylesheet" href="/template/default/style.css?v=20260528-marketplace-4">
</head>
<?php
$host = $_SERVER['HTTP_HOST'] ?? '';
$is_harnesslink_context = ($host === 'bid.harnesslink.com');
$body_context_class = $is_harnesslink_context ? 'harnesslink-context' : 'harnessbid-context';
$zulu->template->body_class[] = $body_context_class;
?>
<body class="<?php echo implode(" ",$zulu->template->body_class); ?>">

<?php if($zulu->template->script_body != NULL) { ?>
<?php echo $zulu->template->script_body; ?>
<!--end script body-->
<?php } ?>

<?php if($zulu->template->site_status != 1 || $zulu->template->error_screen) { ?>
<?php include(dirname(__FILE__)."/maintenance.php"); ?>

<?php } else { ?>

<?php if(SITE_status != 1) { ?>
<div class="zulu-global global-danger">
    <i class="ti ti-alert-triangle"></i> Site is in '<?php echo $class_website->config->status_type[SITE_status]; ?>' mode.
</div>
<?php } ?>

<?php if(WEBSITE_dev) { ?>
<div class="zulu-global global-danger">
    <i class="ti ti-alert-triangle"></i> Site is in development mode (see define.php).
</div>
<?php } ?>

<?php
//-- Load relevant HEADER file
if(file_exists(MAIN_path.FE_crm.$class_user->authorised->file_web_path.'header.tpl')) {
    $class_cache->save('ws_header_file', MAIN_path.FE_crm.$class_user->authorised->file_web_path.'header.tpl');
} else {
    $class_cache->save('ws_header_file', dirname(__FILE__)."/default/header.tpl");
}
include($class_cache->load('ws_header_file'));
?>

<?php } ?>

<?php if(isset($header_slider['success']) && $header_slider['success']) { ?>
<!--Banner-->
<div class="banner">
<?php echo $header_slider['html']; ?>
<div class="clearfix"></div>
</div>
<?php } ?>

<!--Content-->
<div class="body <?php echo implode(" ",(isset($content_class) && is_array($content_class) ? $content_class : [])); ?>">

    <?php if(count($zulu->template->html_body_open) > 0) { ?>
    <?php foreach($zulu->template->html_body_open as $html) { ?>
    <?php echo $html; ?>
    <?php } ?>
    <!--end html body-->
    <?php } ?>

    <div class="page-wrapper">
        
