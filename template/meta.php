<title><?php echo (count($META_title_item)>0?implode(" - ",$META_title_item).(!$META_title_hide?" - ":NULL):NULL); ?><?php echo (!$META_title_hide?($post->title!=NULL?stripslashes($post->title)." - ":NULL)." ".META_title:NULL); ?></title>

<?php if($META_keyword!=NULL) { ?>
<meta name="keywords" content="<?php echo $META_keyword; ?>" />
<?php } ?>
<?php if($META_description!=NULL) { ?>
<meta name="description" content="<?php echo $META_description; ?>" />
<?php } ?>

<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
<!--enable mobile scaling-->

<?php if($setting['ws_tpl_script_favicon']) { ?>
<?php echo $setting['ws_tpl_script_favicon']; ?>
<!--end favicons -->
<?php } ?>


<link href="<?php echo FE_rel; ?>template/default/style.connect.css" type="text/css" rel="stylesheet" />
<link href="<?php echo FE_rel; ?>template/default/assets/bootstrap/bootstrap-grid.min.css" type="text/css" rel="stylesheet" />

<?php if(count($zulu->template->css)>0) { ?>
<style>
	<?php foreach($zulu->template->css as $css) { ?>
    <?php echo $css; ?>
    <?php } ?>
</style>
<!--end custom css-->
<?php } ?>

<?php if(count($zulu->template->css_file)>0) { ?>
<?php foreach($zulu->template->css_file as $file) {
    $config = [];
    if(is_array($file)) {
        $config = $file;
        $file = $file['src'];
    } ?>
<link href="<?php echo $file; ?>" type="text/css" rel="stylesheet"<?php echo (isset($config['param'])?" ".implode(' ',$config['param']):null); ?> />
<?php } ?>
<!--end custom css-->
<?php } ?>

<?php if($zulu->template->script_head!=NULL) { ?>
<?php echo $zulu->template->script_head; ?>
<!--end script head-->
<?php } ?>

<?php if($setting['ws_module_google_gatag_profile']!=NULL) { ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $setting['ws_module_google_gatag_profile']; ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '<?php echo $setting['ws_module_google_gatag_profile']; ?>');
</script>
<?php } ?>

<?php if(KEY_google_analytic!=NULL) { ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo KEY_google_analytic; ?>"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?php echo KEY_google_analytic; ?>');
</script>
<!--end ga-->
<?php } ?>

<?php if(defined('DEBUG_mode') && DEBUG_mode) { echo $debugbarRenderer->renderHead(); } ?>
